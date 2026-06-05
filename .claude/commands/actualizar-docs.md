# Skill: Actualizar Documentación Técnica

## Cuándo usar este skill
Invocar con `/actualizar-docs` después de implementar una mejora, corrección o nueva funcionalidad en el sistema. El skill detecta los cambios realizados y actualiza tanto el HTML como el Word de la documentación.

## Procedimiento

### Paso 1 — Identificar los cambios
Ejecuta los siguientes comandos para entender qué cambió:
```
git diff HEAD~1 --stat
git log -1 --format="%s"
```
Si el usuario describe verbalmente la mejora, usa esa descripción directamente.

### Paso 2 — Mapear cambios a secciones del documento

| Tipo de cambio | Sección(es) a actualizar |
|---|---|
| Nueva funcionalidad / módulo | 1.3.1 Funcionalidades, 3.2.1 RF, 5.2.1 Cronograma |
| Corrección de error conocido | 6.2.1 Registro de errores (marcar como Corregido) |
| Nueva mejora detectada en pruebas | 6.2.2 Mejoras Implementadas |
| Cambio en base de datos (tabla/campo nuevo) | 4.2.2 Diccionario de Datos + Tabla correspondiente |
| Cambio en arquitectura / componente | 4.1.1 Modelo Arquitectónico, 4.1.2 Componentes |
| Nueva regla de negocio / validación | 3.2.1 RF, 3.3.1 Casos de Uso |
| Nueva restricción o limitación identificada | 1.3.3 Limitaciones o Restricciones |
| Nueva tecnología / dependencia agregada | 2.3.1–2.3.3 Tecnologías, 5.3.1 Herramientas |
| Cambio en proceso operativo | 3.1.1 Procesos AS-IS/TO-BE, 3.1.2 BPMN |

### Paso 3 — Actualizar los archivos fuente

Los archivos a editar son:
- `c:\laragon\www\flores\docs\documentacion_tecnica.html` — versión HTML (abrir en navegador)
- `c:\laragon\www\flores\docs\gen_content.py` — capítulos 1–2 del Word
- `c:\laragon\www\flores\docs\gen_content2.py` — capítulos 3–8 del Word

**Reglas de redacción obligatorias:**
- Tercera persona impersonal: "El sistema permite...", "Se implementa...", nunca "yo", "nosotros"
- Tiempo presente para lo que el sistema hace; pasado para el proceso de desarrollo
- Primeros párrafos de cada sección: siempre sin sangría (parámetro `first=True`)
- Tablas: usar la función `apa_table(num, titulo, headers, rows, note=None)`
- Figuras pendientes: usar `figure_ph(num, titulo)`

**Numeración de tablas y figuras:** verificar el número de la última tabla/figura en el documento antes de agregar nuevas, para no repetir ni saltar números.

### Paso 4 — Regenerar el archivo Word

**Antes de ejecutar: cerrar `documentacion_tecnica.docx` en Word si está abierto** (de lo contrario Windows bloquea la escritura).

```
cd c:\laragon\www\flores\docs
python build_docx.py
```

El script sobreescribe el mismo archivo `documentacion_tecnica.docx`. Verificar que termina sin errores y confirma la ruta del archivo generado.

### Paso 5 — Confirmar con el usuario

Reportar:
1. Qué secciones fueron modificadas y por qué
2. Qué tabla o figura nueva se agregó (con su número)
3. Si hay algún diagrama (BPMN, ER, UML) que deba actualizarse manualmente en herramienta gráfica

---

## Contexto del proyecto

- **Sistema:** Sistema de Solicitud de Ramos — Flores el Tandil
- **Autor:** Mario Alexander Cañola Cano
- **Archivos de documentación:** `c:\laragon\www\flores\docs\`
  - `documentacion_tecnica.html` — versión web con estilos APA
  - `documentacion_tecnica.docx` — versión Word generada por Python
  - `gen_docx.py` — setup del documento + helpers
  - `gen_content.py` — capítulos 1–2
  - `gen_content2.py` — capítulos 3–8
  - `build_docx.py` — script de construcción (ejecutar para regenerar)

## Actores del sistema (definición correcta)

| Actor | Rol |
|---|---|
| Empleado / Operador | El propio empleado beneficiario que se presenta en el punto de solicitud y digita su número de cédula de manera autónoma en el panel táctil |
| Administrador | Persona que gestiona el sistema: aprueba solicitudes, configura cupos, genera reportes, administra beneficiarios y catálogos |
| Área de elaboración | Receptor del reporte PDF; no interactúa con el sistema directamente |
| Desarrollador (Cañola Cano) | Soporte técnico y mantenimiento |

## Normas APA 7 a mantener

- Tablas: etiqueta `Tabla N` en negrita encima, título en cursiva debajo, sin líneas verticales
- Figuras: `Figura N.` en negrita + título en cursiva en la misma línea, debajo de la figura
- Referencias: sangría francesa, orden alfabético
- Citas en texto: (Apellido, año) o (Nombre del sitio, año) para fuentes web
