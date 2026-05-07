<?php
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/models/Database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class MaestroImportService
{
    // Aceptamos dos formatos:
    // (A) Plantilla: tipo_documento, documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, telefono, sede
    // (B) Maestro original: tipo documento, numero documento, nombre, telefono movil, sede
    // Los nombres de columnas se normalizan: minusculas, sin acentos, espacios -> "_".
    const COLUMNAS_TODAS = [
        'tipo_documento', 'documento',
        'primer_nombre', 'segundo_nombre',
        'primer_apellido', 'segundo_apellido',
        'telefono', 'sede',
    ];

    // Sinonimos aceptados por columna (lado izquierdo = nombre canonico)
    const SINONIMOS = [
        'tipo_documento'   => ['tipo_documento', 'tipo_de_documento', 'tipodocumento'],
        'documento'        => ['documento', 'numero_documento', 'numero_de_documento', 'no_documento', 'cedula', 'identificacion'],
        'primer_nombre'    => ['primer_nombre'],
        'segundo_nombre'   => ['segundo_nombre'],
        'primer_apellido'  => ['primer_apellido'],
        'segundo_apellido' => ['segundo_apellido'],
        'nombre_completo'  => ['nombre', 'nombre_completo', 'nombres_y_apellidos', 'nombres'],
        'sede'             => ['sede', 'lugar_trabajo', 'ubicacion'],
    ];

    // Columnas de telefono detectadas en orden de prioridad: se usa la primera no vacia.
    // El maestro empresarial usa la columna 'TELEFONO FIJO' para los numeros, por eso va primero.
    const TELEFONO_HEADERS = [
        'telefono_fijo', 'telefono_residencial',
        'telefono_movil', 'telefono_celular', 'celular', 'movil',
        'telefono',
    ];

    const TIPOS_VALIDOS = ['CC', 'CE', 'TI', 'PA', 'NIT', 'PT'];

    private $db;
    private $sedesPorCodigo = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->cargarSedes();
    }

    private function cargarSedes()
    {
        $stmt = $this->db->query("SELECT id, nombre, codigo FROM sedes WHERE activo = 1");
        foreach ($stmt->fetchAll() as $s) {
            if (!empty($s['codigo'])) {
                $this->sedesPorCodigo[strtoupper(trim($s['codigo']))] = $s;
            }
        }
    }

    /**
     * Lee el archivo XLSX/CSV y devuelve filas asociativas.
     * Soporta dos formatos: (A) plantilla con columnas separadas, (B) maestro con
     * columna unica "nombre" que se parsea automaticamente.
     * Tambien busca la fila de encabezados aunque no este en la primera linea
     * (por reportes que tienen titulo en filas 1-2).
     */
    public function leerArchivo($rutaArchivo)
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            throw new Exception('El archivo esta vacio.');
        }

        // Buscar fila de encabezados: la primera fila que contenga al menos
        // documento + (nombre o primer_nombre).
        $filaHeader = -1;
        $mapeo = [];
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $candidato = $this->mapearEncabezados($rows[$i]);
            if (isset($candidato['documento']) &&
                (isset($candidato['primer_nombre']) || isset($candidato['nombre_completo']))) {
                $filaHeader = $i;
                $mapeo = $candidato;
                break;
            }
        }
        if ($filaHeader === -1) {
            throw new Exception(
                'No se encontro la fila de encabezados. El archivo debe contener una fila con: ' .
                'documento, y nombre (o primer_nombre/primer_apellido), tipo_documento, sede.'
            );
        }

        // Validar columnas minimas
        $obligatorias = ['documento', 'sede'];
        foreach ($obligatorias as $req) {
            if (!isset($mapeo[$req])) {
                throw new Exception("Falta columna obligatoria: '$req'.");
            }
        }
        $tieneNombreCompleto = isset($mapeo['nombre_completo']);
        $tieneNombrePartido  = isset($mapeo['primer_nombre']) && isset($mapeo['primer_apellido']);
        if (!$tieneNombreCompleto && !$tieneNombrePartido) {
            throw new Exception(
                'Falta el nombre. Use una columna "nombre" (con nombre completo) ' .
                'o las columnas "primer_nombre" y "primer_apellido" separadas.'
            );
        }

        $filas = [];
        for ($i = $filaHeader + 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            if ($this->filaVacia($r)) continue;

            $fila = [];
            foreach (self::COLUMNAS_TODAS as $col) {
                $fila[$col] = $this->valorEnFila($r, $mapeo, $col);
            }
            // Telefono: probar todas las columnas detectadas y usar el primer valor no vacio.
            if (!empty($mapeo['_telefonos'])) {
                foreach ($mapeo['_telefonos'] as $idxTel) {
                    $val = isset($r[$idxTel]) ? trim((string)$r[$idxTel]) : '';
                    if ($val !== '') {
                        // Excel puede entregar numeros como "3134060326.0" — quitar el ".0" final.
                        $val = preg_replace('/\.0+$/', '', $val);
                        $fila['telefono'] = $val;
                        break;
                    }
                }
            }
            $fila['_nombre_parseado'] = false;
            if ($tieneNombreCompleto && empty($fila['primer_nombre']) && empty($fila['primer_apellido'])) {
                $nc = $this->valorEnFila($r, $mapeo, 'nombre_completo');
                if ($nc !== '') {
                    $parsed = self::parsearNombreCompleto($nc);
                    $fila['primer_nombre']    = $parsed['primer_nombre'];
                    $fila['segundo_nombre']   = $parsed['segundo_nombre'];
                    $fila['primer_apellido']  = $parsed['primer_apellido'];
                    $fila['segundo_apellido'] = $parsed['segundo_apellido'];
                    $fila['_nombre_parseado'] = true;
                    $fila['_nombre_ambiguo']  = $parsed['ambiguo'];
                    $fila['_nombre_original'] = $nc;
                }
            }

            // Normalizar la sede: si viene "TN-PM" o "TN-TN" extraer el sufijo despues del guion.
            if (!empty($fila['sede']) && strpos($fila['sede'], '-') !== false) {
                $partes = explode('-', $fila['sede']);
                $fila['sede'] = trim(end($partes));
            }

            $fila['_linea_excel'] = $i + 1;
            $filas[] = $fila;
        }

        return $filas;
    }

    /**
     * Toma una fila de encabezados y devuelve [columna_canonica => indice]
     * mapeando sinonimos. Normaliza: lower, sin acentos, espacios y caracteres
     * especiales -> "_".
     */
    private function mapearEncabezados(array $headerRow)
    {
        $sinonimoIndex = [];
        foreach (self::SINONIMOS as $canonico => $variantes) {
            foreach ($variantes as $v) {
                $sinonimoIndex[$v] = $canonico;
            }
        }
        $mapeo = [];
        $telefonosEncontrados = [];
        foreach ($headerRow as $idx => $h) {
            $clave = $this->normalizarCabecera($h);
            if ($clave === '') continue;
            if (isset($sinonimoIndex[$clave]) && !isset($mapeo[$sinonimoIndex[$clave]])) {
                $mapeo[$sinonimoIndex[$clave]] = $idx;
            }
            if (in_array($clave, self::TELEFONO_HEADERS, true)) {
                $telefonosEncontrados[$clave] = $idx;
            }
        }
        // Ordenar columnas de telefono por la prioridad de TELEFONO_HEADERS y guardar lista de indices.
        $mapeo['_telefonos'] = [];
        foreach (self::TELEFONO_HEADERS as $h) {
            if (isset($telefonosEncontrados[$h])) {
                $mapeo['_telefonos'][] = $telefonosEncontrados[$h];
            }
        }
        // Compatibilidad: si hay telefonos detectados, exponer el primero como 'telefono'
        if (!empty($mapeo['_telefonos']) && !isset($mapeo['telefono'])) {
            $mapeo['telefono'] = $mapeo['_telefonos'][0];
        }
        return $mapeo;
    }

    private function normalizarCabecera($h)
    {
        $s = strtolower(trim((string)$h));
        $s = strtr($s, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n',
        ]);
        $s = preg_replace('/[^a-z0-9]+/', '_', $s);
        return trim($s, '_');
    }

    private function valorEnFila(array $row, array $mapeo, $col)
    {
        if (!isset($mapeo[$col])) return '';
        $idx = $mapeo[$col];
        return isset($row[$idx]) ? trim((string)$row[$idx]) : '';
    }

    /**
     * Parsea un nombre completo segun la convencion colombiana:
     *   APELLIDO1 [APELLIDO2] NOMBRE1 [NOMBRE2 ...]
     * Heuristica:
     *   - 2 palabras: apellido1 + nombre1
     *   - 3 palabras: apellido1 + apellido2 + nombre1
     *   - 4 palabras: apellido1 + apellido2 + nombre1 + nombre2
     *   - 5+ palabras: apellido1 + apellido2 + nombre1 + (resto unido) -> ambiguo=true
     * Maneja particulas (DE, DEL, LA, LAS, LOS, SAN, SANTA) uniendolas a la palabra siguiente.
     */
    public static function parsearNombreCompleto($nombre)
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'san', 'santa', 'mc', 'di', 'da', 'do'];
        $crudo = preg_split('/\s+/', trim($nombre));
        $crudo = array_values(array_filter($crudo, function ($w) { return $w !== ''; }));

        // Unir particulas con la palabra siguiente: "DE LA CRUZ" -> "DE_LA_CRUZ"
        $tokens = [];
        $buffer = '';
        foreach ($crudo as $w) {
            if (in_array(mb_strtolower($w), $particulas, true)) {
                $buffer .= ($buffer === '' ? '' : ' ') . $w;
            } else {
                $tokens[] = $buffer === '' ? $w : ($buffer . ' ' . $w);
                $buffer = '';
            }
        }
        if ($buffer !== '' && !empty($tokens)) {
            $tokens[count($tokens) - 1] .= ' ' . $buffer;
        }

        $n = count($tokens);
        $r = ['primer_nombre' => '', 'segundo_nombre' => '', 'primer_apellido' => '', 'segundo_apellido' => '', 'ambiguo' => false];

        if ($n === 0) return $r;
        if ($n === 1) {
            $r['primer_nombre'] = $tokens[0];
        } elseif ($n === 2) {
            $r['primer_apellido'] = $tokens[0];
            $r['primer_nombre']   = $tokens[1];
        } elseif ($n === 3) {
            $r['primer_apellido'] = $tokens[0];
            $r['segundo_apellido']= $tokens[1];
            $r['primer_nombre']   = $tokens[2];
        } elseif ($n === 4) {
            $r['primer_apellido'] = $tokens[0];
            $r['segundo_apellido']= $tokens[1];
            $r['primer_nombre']   = $tokens[2];
            $r['segundo_nombre']  = $tokens[3];
        } else {
            $r['primer_apellido']  = $tokens[0];
            $r['segundo_apellido'] = $tokens[1];
            $r['primer_nombre']    = $tokens[2];
            $r['segundo_nombre']   = implode(' ', array_slice($tokens, 3));
            $r['ambiguo']          = true;
        }
        // Truncar a 50 chars (limite BD)
        foreach (['primer_nombre','segundo_nombre','primer_apellido','segundo_apellido'] as $k) {
            if (mb_strlen($r[$k]) > 50) $r[$k] = mb_substr($r[$k], 0, 50);
        }
        return $r;
    }

    private function filaVacia(array $r)
    {
        foreach ($r as $v) {
            if ($v !== null && trim((string)$v) !== '') return false;
        }
        return true;
    }

    /**
     * Valida y clasifica cada fila contra la BD.
     * Devuelve: ['nuevas', 'a_actualizar', 'a_reactivar', 'sin_cambios', 'errores', 'resumen'].
     */
    public function clasificar(array $filas)
    {
        $documentos = [];
        foreach ($filas as $f) {
            if (!empty($f['documento'])) $documentos[] = $f['documento'];
        }

        $existentes = $this->buscarExistentes($documentos);

        $nuevas = $aActualizar = $aReactivar = $sinCambios = $errores = [];

        foreach ($filas as $f) {
            $err = $this->validarFila($f);
            if ($err) {
                $f['_error'] = $err;
                $errores[] = $f;
                continue;
            }

            // Normalizar
            $f['tipo_documento'] = strtoupper($f['tipo_documento'] ?: 'CC');
            $f['_id_sede']       = $this->sedesPorCodigo[strtoupper(trim($f['sede']))]['id'];
            $f['_sede_nombre']   = $this->sedesPorCodigo[strtoupper(trim($f['sede']))]['nombre'];

            $existente = $existentes[$f['documento']] ?? null;
            if (!$existente) {
                $nuevas[] = $f;
                continue;
            }

            $cambios = $this->compararCambios($existente, $f);
            $estabaInactivo = (int)$existente['activo'] === 0;

            if ($estabaInactivo) {
                $f['_id'] = $existente['id'];
                $f['_cambios'] = $cambios;
                $aReactivar[] = $f;
            } elseif (!empty($cambios)) {
                $f['_id'] = $existente['id'];
                $f['_cambios'] = $cambios;
                $aActualizar[] = $f;
            } else {
                $sinCambios[] = $f;
            }
        }

        return [
            'nuevas'       => $nuevas,
            'a_actualizar' => $aActualizar,
            'a_reactivar'  => $aReactivar,
            'sin_cambios'  => $sinCambios,
            'errores'      => $errores,
            'resumen'      => [
                'total'        => count($filas),
                'nuevas'       => count($nuevas),
                'a_actualizar' => count($aActualizar),
                'a_reactivar'  => count($aReactivar),
                'sin_cambios'  => count($sinCambios),
                'errores'      => count($errores),
            ],
        ];
    }

    private function buscarExistentes(array $documentos)
    {
        if (empty($documentos)) return [];
        $documentos = array_values(array_unique($documentos));
        $placeholders = implode(',', array_fill(0, count($documentos), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, tipo_documento, documento, primer_nombre, segundo_nombre,
                    primer_apellido, segundo_apellido, telefono, id_sede, activo
             FROM personas WHERE documento IN ($placeholders)"
        );
        $stmt->execute($documentos);
        $map = [];
        foreach ($stmt->fetchAll() as $p) {
            $map[$p['documento']] = $p;
        }
        return $map;
    }

    private function validarFila(array $f)
    {
        if (empty($f['documento'])) return 'Documento vacio';
        if (!preg_match('/^\d{4,20}$/', $f['documento'])) {
            return 'Documento invalido (debe ser numerico, 4-20 digitos)';
        }
        if (empty($f['primer_nombre'])) return 'Falta primer_nombre';
        if (empty($f['primer_apellido'])) return 'Falta primer_apellido';
        if (empty($f['sede'])) return 'Falta sede';

        $tipo = strtoupper($f['tipo_documento'] ?: 'CC');
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
            return "tipo_documento invalido: {$f['tipo_documento']} (validos: " . implode(',', self::TIPOS_VALIDOS) . ')';
        }

        $codigoSede = strtoupper(trim($f['sede']));
        if (!isset($this->sedesPorCodigo[$codigoSede])) {
            return "Sede no encontrada: {$f['sede']} (validas: " . implode(',', array_keys($this->sedesPorCodigo)) . ')';
        }

        foreach (['primer_nombre','segundo_nombre','primer_apellido','segundo_apellido'] as $c) {
            if (mb_strlen($f[$c] ?? '') > 50) return "Campo $c excede 50 caracteres";
        }
        if (mb_strlen($f['telefono'] ?? '') > 20) return 'Telefono excede 20 caracteres';

        return null;
    }

    private function compararCambios(array $existente, array $nuevo)
    {
        $cambios = [];
        $map = [
            'tipo_documento'   => $nuevo['tipo_documento'],
            'primer_nombre'    => $nuevo['primer_nombre'],
            'segundo_nombre'   => $nuevo['segundo_nombre'] ?: null,
            'primer_apellido'  => $nuevo['primer_apellido'],
            'segundo_apellido' => $nuevo['segundo_apellido'] ?: null,
            'telefono'         => $nuevo['telefono'] ?: null,
            'id_sede'          => (int)$nuevo['_id_sede'],
        ];
        foreach ($map as $campo => $valorNuevo) {
            $valorActual = $existente[$campo] ?? null;
            if ($campo === 'id_sede') {
                $valorActual = (int)$valorActual;
            }
            if ((string)$valorActual !== (string)$valorNuevo) {
                $cambios[$campo] = ['antes' => $valorActual, 'despues' => $valorNuevo];
            }
        }
        return $cambios;
    }

    /**
     * Ejecuta INSERT/UPDATE en transaccion. Devuelve contadores reales aplicados.
     */
    public function ejecutar(array $clasificacion)
    {
        $insertadas = 0; $actualizadas = 0; $reactivadas = 0;

        $this->db->beginTransaction();
        try {
            $stmtIns = $this->db->prepare(
                "INSERT INTO personas (tipo_documento, documento, primer_nombre, segundo_nombre,
                 primer_apellido, segundo_apellido, telefono, id_sede, activo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            foreach ($clasificacion['nuevas'] as $f) {
                $stmtIns->execute([
                    $f['tipo_documento'],
                    $f['documento'],
                    $f['primer_nombre'],
                    $f['segundo_nombre'] ?: null,
                    $f['primer_apellido'],
                    $f['segundo_apellido'] ?: null,
                    $f['telefono'] ?: null,
                    (int)$f['_id_sede'],
                ]);
                $insertadas++;
            }

            $stmtUpd = $this->db->prepare(
                "UPDATE personas SET tipo_documento = ?, primer_nombre = ?, segundo_nombre = ?,
                 primer_apellido = ?, segundo_apellido = ?, telefono = ?, id_sede = ?
                 WHERE id = ?"
            );
            foreach ($clasificacion['a_actualizar'] as $f) {
                $stmtUpd->execute([
                    $f['tipo_documento'],
                    $f['primer_nombre'],
                    $f['segundo_nombre'] ?: null,
                    $f['primer_apellido'],
                    $f['segundo_apellido'] ?: null,
                    $f['telefono'] ?: null,
                    (int)$f['_id_sede'],
                    (int)$f['_id'],
                ]);
                $actualizadas++;
            }

            $stmtReact = $this->db->prepare(
                "UPDATE personas SET tipo_documento = ?, primer_nombre = ?, segundo_nombre = ?,
                 primer_apellido = ?, segundo_apellido = ?, telefono = ?, id_sede = ?, activo = 1
                 WHERE id = ?"
            );
            foreach ($clasificacion['a_reactivar'] as $f) {
                $stmtReact->execute([
                    $f['tipo_documento'],
                    $f['primer_nombre'],
                    $f['segundo_nombre'] ?: null,
                    $f['primer_apellido'],
                    $f['segundo_apellido'] ?: null,
                    $f['telefono'] ?: null,
                    (int)$f['_id_sede'],
                    (int)$f['_id'],
                ]);
                $reactivadas++;
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [
            'insertadas'   => $insertadas,
            'actualizadas' => $actualizadas,
            'reactivadas'  => $reactivadas,
        ];
    }
}
