# Manual de instalación — Sistema de Solicitud de Ramos Florales

Este documento describe paso a paso cómo instalar el sistema en un PC nuevo con Windows, desde cero.

---

## 1. Requisitos del equipo

| Componente | Mínimo | Recomendado |
|---|---|---|
| Sistema operativo | Windows 10 64-bit | Windows 11 64-bit |
| Procesador | Dual Core 2 GHz | Quad Core 2.5 GHz |
| Memoria RAM | 4 GB | 8 GB |
| Disco duro | 10 GB libres | 20 GB libres en SSD |
| Conexión a internet | Solo para instalar | No requerida después |
| Navegador | Edge (incluido en Windows) | Chrome última versión |

---

## 2. Software a instalar

### 2.1 Laragon Full (Apache + PHP + MySQL en un solo paquete)

1. Descargar desde **https://laragon.org/download/** la versión **Laragon Full** para Windows.
2. Ejecutar el instalador. Aceptar la ruta por defecto `C:\laragon`.
3. Al final pedirá que configure preferencias — dejar todo por defecto.

**Verificación rápida:** abrir Laragon → debería verse el panel principal con botones "Start All" y "Menu".

### 2.2 Composer (gestor de dependencias PHP)

Laragon Full ya incluye Composer. Para verificar:
1. Abrir Laragon.
2. Menú → **Terminal** → escribir `composer --version`.
3. Debe responder la versión de Composer.

### 2.3 Git (opcional pero recomendado)

Si quieres actualizar el sistema con futuras versiones:
1. Descargar desde **https://git-scm.com/download/win**.
2. Instalar con opciones por defecto.

---

## 3. Instalación del sistema

### 3.1 Copiar el proyecto

1. Obtener la carpeta `flores` (por USB, clonación de git o descarga).
2. Copiarla a **`C:\laragon\www\flores`**. La estructura debe quedar así:
   ```
   C:\laragon\www\flores\
       app\
       public\
       sql\
       vendor\           ← si no existe, ver paso 3.2
       composer.json
       iniciar.bat
       ...
   ```

### 3.2 Instalar dependencias (solo si no existe la carpeta `vendor`)

1. En Laragon → Menú → **Terminal**.
2. Ejecutar:
   ```
   cd C:\laragon\www\flores
   composer install
   ```
3. Esperar a que termine (puede tomar 2-3 minutos la primera vez).

### 3.3 Crear y poblar la base de datos

**Opción A — desde phpMyAdmin (más visual):**
1. Iniciar Laragon → click en **Start All**.
2. Click en **Menu** → **MySQL** → **phpMyAdmin** (se abre en el navegador).
3. Click en pestaña **SQL**.
4. Abrir el archivo `C:\laragon\www\flores\sql\instalar_db.sql`, copiar todo el contenido y pegarlo.
5. Click en **Continuar / Go**.

**Opción B — desde terminal (más rápido):**
1. Laragon → Menu → Terminal.
2. Ejecutar:
   ```
   cd C:\laragon\www\flores\sql
   mysql -u root < instalar_db.sql
   ```
3. Si pide contraseña, presionar Enter (Laragon viene sin password por defecto).

### 3.4 Configurar el puerto de Apache

Por defecto el sistema usa el puerto `8001`. Si tu Laragon usa otro:

1. Laragon → Menu → Apache → **httpd.conf**.
2. Buscar la línea `Listen 80` y cambiar a `Listen 8001`.
3. Buscar `<VirtualHost ...>` y ajustar también el puerto si aparece.
4. Reiniciar Laragon (botón **Stop All** y luego **Start All**).

**Si vas a permitir acceso desde otras máquinas de la red**, cambiar `Listen 127.0.0.1:8001` por `Listen 0.0.0.0:8001` y abrir el puerto 8001 en el Firewall de Windows.

---

## 4. Primera prueba

1. Iniciar Laragon → **Start All**.
2. Abrir el navegador en **http://localhost:8001/login**.
3. Ingresar con las credenciales de fábrica:
   - Usuario: `admin`
   - Contraseña: `admin`
4. **CAMBIAR INMEDIATAMENTE LA CONTRASEÑA** del usuario admin.

---

## 5. Configurar el "ejecutable"

El sistema incluye dos lanzadores en la raíz del proyecto:

| Archivo | Cuándo usar |
|---|---|
| **`iniciar.bat`** | Abre el navegador normal en la URL del kiosco. Ideal para uso diario. |
| **`iniciar_kiosco.bat`** | Abre Chrome/Edge en pantalla completa sin barras. Ideal para PC dedicado al kiosco. |

### 5.1 Crear accesos directos en el escritorio

1. Click derecho en `C:\laragon\www\flores\iniciar.bat` → **Enviar a** → **Escritorio (crear acceso directo)**.
2. Renombrar el acceso directo a **"Sistema de Ramos"**.
3. Click derecho en el acceso → **Propiedades** → **Cambiar icono** → elegir un icono apropiado (puedes usar el logo de Tandil convertido a `.ico`).

### 5.2 Auto-arranque con Windows (opcional)

Para que Laragon arranque solo al prender el PC:
1. Abrir Laragon → click en el ícono de **engranaje** (Preferences).
2. Pestaña **General** → marcar:
   - ✅ Auto-start Laragon when Windows boots
   - ✅ Start All when Laragon starts
3. Click en **OK**.

### 5.3 Auto-arranque del kiosco al iniciar sesión (opcional, modo kiosco dedicado)

1. Presionar **Win + R**, escribir `shell:startup`, Enter.
2. Copiar el acceso directo a `iniciar_kiosco.bat` dentro de esa carpeta.
3. Reiniciar Windows. Al iniciar sesión el kiosco aparecerá automáticamente a pantalla completa.

Para **salir** del modo kiosco en pantalla completa: presionar `Alt + F4`.

---

## 6. Convertir el `.bat` en `.exe` (opcional)

Si prefieres un archivo `.exe` con icono propio en lugar del `.bat`:

1. Descargar **Bat To Exe Converter** desde **https://www.f2ko.de/en/b2e.php** (gratis).
2. Abrir el programa.
3. **Batch file** → seleccionar `C:\laragon\www\flores\iniciar.bat`.
4. **Save as** → elegir el nombre y carpeta destino.
5. En la pestaña **Versioninformations** → poner nombre del producto, versión, autor.
6. En la pestaña **Icon** → cargar el icono `.ico` deseado.
7. Click en **Compile**.

Te queda un `.exe` que hace exactamente lo mismo que el `.bat` pero con apariencia de programa profesional.

---

## 7. Mantenimiento

### 7.1 Backup de la base de datos

Recomendado al menos semanal. Desde la terminal de Laragon:
```
mysqldump -u root flores_db > C:\backups\flores_%date:~-4%%date:~3,2%%date:~0,2%.sql
```

Puedes automatizarlo en el **Programador de tareas de Windows** ejecutando un `.bat` con esa línea.

### 7.2 Actualizar el sistema

Cuando lleguen nuevas versiones:
1. Hacer backup de la BD.
2. Reemplazar la carpeta `C:\laragon\www\flores` con la nueva versión, **conservando**:
   - El archivo `app/config/database.php` si tiene credenciales personalizadas.
   - La carpeta `storage/` si quieres conservar PDFs ya generados.
3. Aplicar las nuevas migraciones desde `sql/`.

---

## 8. Solución de problemas comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| "No se puede acceder a localhost:8001" | Laragon no está corriendo | Abrir Laragon → Start All |
| "Database not found" al loguear | BD no se importó | Repetir paso 3.3 |
| El navegador no abre desde el `.bat` | Sin navegador por defecto | Configurar Chrome/Edge como default |
| Otros PCs de la red no pueden entrar | Apache solo escucha localhost | Ver sección 3.4 (red) |
| El cron de cupos no se actualiza | Servicio MySQL caído | Reiniciar Laragon |

---

## 9. Contacto técnico

Mantener este documento accesible al usuario final y al equipo de soporte técnico de la organización.
