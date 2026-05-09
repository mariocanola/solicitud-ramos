<?php
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/models/Database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class MaestroImportService
{
    // ============================================================
    // CONSTANTES
    // ============================================================

    const FORMATO_TANDIL = 'TANDIL';
    const FORMATO_CREOS  = 'CREOS';

    const TIPOS_VALIDOS = ['CC', 'CE', 'TI', 'PA', 'NIT', 'PT'];

    // Sinonimos de columnas. Lado izquierdo = nombre canonico interno.
    const SINONIMOS = [
        'tipo_documento'   => ['tipo_documento', 'tipo_de_documento', 'tipodocumento', 'cod', 'codigo'],
        'documento'        => ['documento', 'numero_documento', 'numero_de_documento', 'no_documento', 'cedula', 'identificacion'],
        'primer_nombre'    => ['primer_nombre', '1_nombre'],
        'segundo_nombre'   => ['segundo_nombre', '2_nombre'],
        'primer_apellido'  => ['primer_apellido', '1_apellido'],
        'segundo_apellido' => ['segundo_apellido', '2_apellido'],
        'nombre_completo'  => ['nombre', 'nombre_completo', 'nombres_y_apellidos', 'nombres'],
        'sede'             => ['sede', 'lugar_trabajo', 'ubicacion', 'area'],
        'estado'           => ['estado', 'estado_persona', 'situacion'],
    ];

    // Columnas de telefono detectadas en orden de prioridad: se usa la primera no vacia.
    // 'TELEFONO FIJO' del maestro Tandil tiene los celulares reales, por eso va primero.
    const TELEFONO_HEADERS = [
        'telefono_fijo', 'telefono_residencial',
        'telefono_movil', 'telefono_celular', 'celular', 'movil',
        'telefono',
    ];

    // Tabla de mapeo para los valores literales que pueden aparecer en la columna sede.
    const SEDE_ALIASES = [
        'TN'                => 'TN',
        'TANDIL'            => 'TN',
        'FLORES EL TANDIL'  => 'TN',
        'PM'                => 'PM',
        'PRIMAVERA'         => 'PM',
    ];

    // ============================================================
    // ESTADO
    // ============================================================

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

    // ============================================================
    // LECTURA Y DETECCION DE FORMATO
    // ============================================================

    /**
     * Lee el archivo y devuelve filas asociativas en formato canonico.
     * Detecta automaticamente si es maestro Tandil o Creos y aplica el adaptador.
     * Retorna array con clave especial 'formato' al inicio.
     */
    public function leerArchivo($rutaArchivo)
    {
        $rows = $this->cargarFilasCrudas($rutaArchivo);

        $deteccion = $this->detectarFormato($rows);
        if ($deteccion === null) {
            throw new Exception(
                'No se pudo detectar el formato del archivo. Debe ser el maestro Tandil ' .
                '(con columnas TIPO DOCUMENTO, NOMBRE, SEDE) o el maestro CREOS ' .
                '(con columnas Cod, Documento, 1. Apellido, 1. Nombre, Area).'
            );
        }

        [$formato, $filaHeader, $mapeo] = $deteccion;
        $filas = $this->extraerFilas($rows, $filaHeader, $mapeo, $formato);

        return ['formato' => $formato, 'filas' => $filas];
    }

    private function cargarFilasCrudas($ruta)
    {
        $spreadsheet = IOFactory::load($ruta);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);
        if (empty($rows)) {
            throw new Exception('El archivo esta vacio.');
        }
        return $rows;
    }

    /**
     * Recorre las primeras filas buscando una fila de encabezados que permita
     * identificar el formato. Retorna [formato, indiceFila, mapeo] o null.
     */
    private function detectarFormato(array $rows)
    {
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $mapeo = $this->mapearEncabezados($rows[$i]);
            $formato = $this->clasificarFormato($mapeo);
            if ($formato !== null) {
                return [$formato, $i, $mapeo];
            }
        }
        return null;
    }

    private function clasificarFormato(array $mapeo)
    {
        // Maestro Creos: columnas separadas (1./2. apellido, 1./2. nombre) + estado
        $tieneSeparado = isset($mapeo['primer_apellido']) && isset($mapeo['primer_nombre']);
        if ($tieneSeparado && isset($mapeo['documento'])) {
            return self::FORMATO_CREOS;
        }
        // Maestro Tandil: columna unica 'nombre' que se parsea
        if (isset($mapeo['nombre_completo']) && isset($mapeo['documento']) && isset($mapeo['sede'])) {
            return self::FORMATO_TANDIL;
        }
        return null;
    }

    /**
     * Convierte el array de filas crudas en filas canonicas listas para clasificar.
     * El parametro $formato decide la empresa por defecto y si se respeta el campo estado.
     */
    private function extraerFilas(array $rows, $filaHeader, array $mapeo, $formato)
    {
        $empresaPorDefecto = ($formato === self::FORMATO_CREOS) ? 'CREOS' : 'TANDIL';
        $respetarEstado    = ($formato === self::FORMATO_CREOS);
        $filas = [];

        for ($i = $filaHeader + 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            if ($this->filaVacia($r)) continue;

            $fila = [
                'tipo_documento'   => $this->valorEnFila($r, $mapeo, 'tipo_documento'),
                'documento'        => $this->valorEnFila($r, $mapeo, 'documento'),
                'primer_nombre'    => $this->valorEnFila($r, $mapeo, 'primer_nombre'),
                'segundo_nombre'   => $this->valorEnFila($r, $mapeo, 'segundo_nombre'),
                'primer_apellido'  => $this->valorEnFila($r, $mapeo, 'primer_apellido'),
                'segundo_apellido' => $this->valorEnFila($r, $mapeo, 'segundo_apellido'),
                'telefono'         => '',
                'sede'             => $this->valorEnFila($r, $mapeo, 'sede'),
                'empresa'          => $empresaPorDefecto,
                'activo'           => 1,
                '_linea_excel'     => $i + 1,
                '_nombre_parseado' => false,
            ];

            // Telefono: primer valor no vacio entre todas las columnas detectadas.
            $fila['telefono'] = $this->primerTelefonoNoVacio($r, $mapeo);

            // Si trae una columna 'nombre' completa y faltan las separadas, parsearla.
            if (isset($mapeo['nombre_completo']) && $fila['primer_nombre'] === '' && $fila['primer_apellido'] === '') {
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

            $fila['sede'] = $this->normalizarSede($fila['sede']);

            if ($respetarEstado) {
                $estado = strtoupper($this->valorEnFila($r, $mapeo, 'estado'));
                $fila['activo'] = ($estado === '' || $estado === 'ACTIVO') ? 1 : 0;
            }

            $filas[] = $fila;
        }

        return $filas;
    }

    private function primerTelefonoNoVacio(array $r, array $mapeo)
    {
        if (empty($mapeo['_telefonos'])) return '';
        foreach ($mapeo['_telefonos'] as $idxTel) {
            $val = isset($r[$idxTel]) ? trim((string)$r[$idxTel]) : '';
            if ($val === '') continue;
            // Excel a veces devuelve numeros como "3134060326.0".
            return preg_replace('/\.0+$/', '', $val);
        }
        return '';
    }

    /**
     * Resuelve la sede del archivo a un codigo del sistema (TN/PM):
     *   - Soporta valores con guion ("TN-PM" -> "PM")
     *   - Mapea alias literales ("PRIMAVERA" -> "PM", "FLORES EL TANDIL" -> "TN")
     *   - Si no encuentra alias devuelve la cadena original normalizada.
     */
    private function normalizarSede($valor)
    {
        $s = strtoupper(trim((string)$valor));
        if ($s === '') return '';
        if (strpos($s, '-') !== false) {
            $partes = explode('-', $s);
            $s = trim(end($partes));
        }
        return self::SEDE_ALIASES[$s] ?? $s;
    }

    // ============================================================
    // MAPEO DE ENCABEZADOS
    // ============================================================

    /**
     * Toma una fila de encabezados y devuelve [columna_canonica => indice]
     * mapeando sinonimos. Tambien rellena la lista '_telefonos' en orden de prioridad.
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
        $mapeo['_telefonos'] = [];
        foreach (self::TELEFONO_HEADERS as $h) {
            if (isset($telefonosEncontrados[$h])) {
                $mapeo['_telefonos'][] = $telefonosEncontrados[$h];
            }
        }
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
        // Caracter de reemplazo Unicode (�) que aparece cuando hay encoding
        // corrupto en archivos .xls antiguos -> tratar como letra perdida.
        $s = str_replace("\xEF\xBF\xBD", '', $s);
        $s = preg_replace('/[^a-z0-9]+/', '_', $s);
        return trim($s, '_');
    }

    private function valorEnFila(array $row, array $mapeo, $col)
    {
        if (!isset($mapeo[$col])) return '';
        $idx = $mapeo[$col];
        return isset($row[$idx]) ? trim((string)$row[$idx]) : '';
    }

    private function filaVacia(array $r)
    {
        foreach ($r as $v) {
            if ($v !== null && trim((string)$v) !== '') return false;
        }
        return true;
    }

    // ============================================================
    // PARSER DE NOMBRE COMPLETO (CONVENCION COLOMBIANA)
    // ============================================================

    /**
     * Convencion: APELLIDO1 [APELLIDO2] NOMBRE1 [NOMBRE2 ...]
     *   - 2 palabras: apellido1 + nombre1
     *   - 3 palabras: apellido1 + apellido2 + nombre1
     *   - 4 palabras: apellido1 + apellido2 + nombre1 + nombre2
     *   - 5+ palabras: apellido1 + apellido2 + nombre1 + (resto unido) -> ambiguo
     * Maneja particulas (DE, DEL, LA, ...) uniendolas a la palabra siguiente.
     */
    public static function parsearNombreCompleto($nombre)
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'san', 'santa', 'mc', 'di', 'da', 'do'];
        $crudo = preg_split('/\s+/', trim($nombre));
        $crudo = array_values(array_filter($crudo, function ($w) { return $w !== ''; }));

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
        foreach (['primer_nombre','segundo_nombre','primer_apellido','segundo_apellido'] as $k) {
            if (mb_strlen($r[$k]) > 50) $r[$k] = mb_substr($r[$k], 0, 50);
        }
        return $r;
    }

    // ============================================================
    // CLASIFICACION
    // ============================================================

    /**
     * Recibe la salida de leerArchivo (con clave 'filas' y 'formato') y clasifica
     * cada fila en: nuevas, a_actualizar, a_reactivar, sin_cambios, errores.
     */
    public function clasificar(array $entrada)
    {
        $filas   = $entrada['filas'] ?? [];
        $formato = $entrada['formato'] ?? null;

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

            $f['tipo_documento'] = strtoupper($f['tipo_documento'] ?: 'CC');
            $sede = $this->sedesPorCodigo[strtoupper(trim($f['sede']))];
            $f['_id_sede']     = $sede['id'];
            $f['_sede_nombre'] = $sede['nombre'];

            $existente = $existentes[$f['documento']] ?? null;
            if (!$existente) {
                $nuevas[] = $f;
                continue;
            }

            $cambios = $this->compararCambios($existente, $f);
            $estabaInactivo = (int)$existente['activo'] === 0;
            $vaActivo       = (int)$f['activo'] === 1;

            if ($estabaInactivo && $vaActivo) {
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
            'formato'      => $formato,
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
                    primer_apellido, segundo_apellido, telefono, id_sede, empresa, activo
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
        $map = [
            'tipo_documento'   => $nuevo['tipo_documento'],
            'primer_nombre'    => $nuevo['primer_nombre'],
            'segundo_nombre'   => $nuevo['segundo_nombre'] ?: null,
            'primer_apellido'  => $nuevo['primer_apellido'],
            'segundo_apellido' => $nuevo['segundo_apellido'] ?: null,
            'telefono'         => $nuevo['telefono'] ?: null,
            'id_sede'          => (int)$nuevo['_id_sede'],
            'empresa'          => $nuevo['empresa'],
            'activo'           => (int)$nuevo['activo'],
        ];
        $cambios = [];
        foreach ($map as $campo => $valorNuevo) {
            $valorActual = $existente[$campo] ?? null;
            if ($campo === 'id_sede' || $campo === 'activo') {
                $valorActual = (int)$valorActual;
            }
            if ((string)$valorActual !== (string)$valorNuevo) {
                $cambios[$campo] = ['antes' => $valorActual, 'despues' => $valorNuevo];
            }
        }
        return $cambios;
    }

    // ============================================================
    // EJECUCION (INSERT / UPDATE / REACTIVAR)
    // ============================================================

    public function ejecutar(array $clasificacion)
    {
        $insertadas = 0; $actualizadas = 0; $reactivadas = 0;

        $this->db->beginTransaction();
        try {
            $stmtIns = $this->db->prepare(
                "INSERT INTO personas (tipo_documento, documento, primer_nombre, segundo_nombre,
                 primer_apellido, segundo_apellido, telefono, id_sede, empresa, activo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach ($clasificacion['nuevas'] as $f) {
                $stmtIns->execute($this->bindFila($f));
                $insertadas++;
            }

            $stmtUpd = $this->db->prepare(
                "UPDATE personas SET tipo_documento = ?, primer_nombre = ?, segundo_nombre = ?,
                 primer_apellido = ?, segundo_apellido = ?, telefono = ?, id_sede = ?, empresa = ?, activo = ?
                 WHERE documento = ?"
            );
            foreach ($clasificacion['a_actualizar'] as $f) {
                $stmtUpd->execute($this->bindFilaUpdate($f));
                $actualizadas++;
            }

            $stmtReact = $this->db->prepare(
                "UPDATE personas SET tipo_documento = ?, primer_nombre = ?, segundo_nombre = ?,
                 primer_apellido = ?, segundo_apellido = ?, telefono = ?, id_sede = ?, empresa = ?, activo = 1
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
                    $f['empresa'],
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

    private function bindFila(array $f)
    {
        return [
            $f['tipo_documento'],
            $f['documento'],
            $f['primer_nombre'],
            $f['segundo_nombre'] ?: null,
            $f['primer_apellido'],
            $f['segundo_apellido'] ?: null,
            $f['telefono'] ?: null,
            (int)$f['_id_sede'],
            $f['empresa'],
            (int)$f['activo'],
        ];
    }

    private function bindFilaUpdate(array $f)
    {
        return [
            $f['tipo_documento'],
            $f['primer_nombre'],
            $f['segundo_nombre'] ?: null,
            $f['primer_apellido'],
            $f['segundo_apellido'] ?: null,
            $f['telefono'] ?: null,
            (int)$f['_id_sede'],
            $f['empresa'],
            (int)$f['activo'],
            $f['documento'],
        ];
    }
}
