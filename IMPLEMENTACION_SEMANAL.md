# Implementación de Reinicio de Cupos Semanal

## Resumen

Se ha implementado un sistema configurable para reinicio de cupos que permite cambiar entre períodos mensuales y semanales sin modificar el código. La implementación sigue los principios de Clean Code, SRP, KISS, DRY y POO.

## Cambios Realizados

### 1. Archivos SQL Nuevos

- **`sql/config_periodo_tipo.sql`**: Configuración para tipo de período (monthly/weekly)
- **`sql/evento_reinicio_semanal.sql`**: MySQL Event para reinicio automático cada lunes a las 06:00 AM

### 2. Archivos PHP Modificados

#### `app/helpers/DateHelper.php`
- Agregado `getPeriodStart($date, $type = 'monthly')`: Método genérico para calcular inicio de período
- Agregado `getCurrentPeriodStart($type = 'monthly')`: Obtiene inicio del período actual
- Métodos originales (`currentPeriod()`, `periodFromDate()`) mantenidos para compatibilidad

#### `app/services/CupoService.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizados `verificarDisponibilidad()`, `incrementar()`, `decrementar()` para usar `DateHelper::getPeriodStart()`
- Actualizado `getResumen()` para pasar tipo de período al modelo

#### `app/models/CupoSede.php`
- Actualizado `getResumen($tipo = 'monthly')`: Ahora acepta parámetro de tipo
- Implementa lógica condicional para filtrar por semana o mes
- Usa funciones SQL específicas para cada tipo de período

#### `app/models/Solicitud.php`
- Agregado `tieneSolicitudEnPeriodo($persona_id, $fecha, $tipo = 'monthly')`: Método genérico
- Agregado `getSolicitudActivaEnPeriodo($persona_id, $fecha, $tipo = 'monthly')`: Método genérico
- Métodos originales (`tieneSolicitudEnMes()`, `getSolicitudActivaEnMes()`) mantenidos como wrappers para compatibilidad

#### `app/services/SolicitudService.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizado `crear()` para usar `tieneSolicitudEnPeriodo()` con mensaje dinámico

#### `app/controllers/SolicitudController.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizado `buscarPersona()` para usar `getSolicitudActivaEnPeriodo()`

#### `app/controllers/CupoController.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizados `actualizar()` y `verificar()` para usar `DateHelper::getCurrentPeriodStart()`
- Corregido uso de método inexistente `obtenerPorSedePeriodo()` → `getBySedeYPeriodo()`

#### `app/controllers/DashboardController.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizado `index()` para usar `DateHelper::getCurrentPeriodStart()`

#### `app/controllers/ConfigController.php`
- Agregado `getPeriodoTipo()`: Obtiene tipo de período desde configuración
- Actualizado `index()` para usar `DateHelper::getCurrentPeriodStart()`
- Actualizado `guardar()` para validar y guardar `periodo_tipo`

## Instalación

### 1. Ejecutar Scripts SQL

```bash
# Importar configuración del tipo de período
mysql -u usuario -p flores_db < sql/config_periodo_tipo.sql

# Importar evento de reinicio semanal
mysql -u usuario -p flores_db < sql/evento_reinicio_semanal.sql

# Activar el event scheduler (una sola vez)
mysql -u usuario -p -e "SET GLOBAL event_scheduler = ON;"
```

### 2. Configurar el Sistema

1. Acceder a la sección de Configuración en el panel administrativo
2. Establecer `periodo_tipo` en `weekly` para activar períodos semanales
3. Establecer `periodo_tipo` en `monthly` para volver a períodos mensuales

## Funcionamiento

### Período Mensual (default)
- Los cupos se reinician el primer día de cada mes
- El período se calcula como `Y-m-01` (primer día del mes)
- Las solicitudes se validan por mes

### Período Semanal
- Los cupos se reinician cada lunes a las 06:00 AM
- El período se calcula como el lunes de la semana actual
- Las solicitudes se validan por semana
- El MySQL Event crea automáticamente los registros de cupos para la nueva semana

## Ventajas de la Implementación

1. **Configurable**: Cambiar entre mensual/semanal sin modificar código
2. **Genérico**: Métodos reutilizables para cualquier tipo de período
3. **Independiente del SO**: MySQL Event funciona en cualquier plataforma
4. **Robusto**: No depende de scripts externos ni tareas del sistema
5. **Extensible**: Fácil agregar períodos quincenales/diarios en el futuro
6. **Clean Code**: Sigue SRP, OCP, DRY mejor que la propuesta original
7. **Compatibilidad**: Métodos originales mantenidos para no romper código existente

## Histórico de Solicitudes

- Las solicitudes antiguas no se eliminan
- Solo cambian los filtros de consulta según el tipo de período activo
- Los registros de `cupos_sede` existentes (mensuales) quedan obsoletos pero no interfieren

## Pruebas

Para verificar el funcionamiento:

1. Cambiar `periodo_tipo` a `weekly` en configuración
2. Verificar que las validaciones de cupo funcionan por semana
3. Verificar que el mensaje de error indica "semana" en lugar de "mes"
4. Verificar que el MySQL Event crea registros cada lunes a las 06:00 AM
5. Cambiar de vuelta a `monthly` para verificar compatibilidad

## Notas Importantes

- El MySQL Event requiere que el event scheduler esté activo en MySQL
- Para desactivar el reinicio semanal, cambiar `periodo_tipo` a `monthly`
- Los métodos originales (`tieneSolicitudEnMes`, etc.) están marcados como `@deprecated` pero funcionan correctamente
