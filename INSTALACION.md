# Cómo instalar el Sistema de Ramos Florales

Esta guía está pensada para alguien que **nunca ha instalado un programa técnico**. Sigue los pasos en orden, sin saltarte ninguno. Si algo no funciona, ve al final (Sección 6: "Si algo sale mal").

Tiempo estimado: **30 minutos**.

---

## Lo que vas a necesitar

- Un PC con **Windows 10 u 11**.
- **Conexión a internet** (solo para instalar, después ya no hace falta).
- La carpeta **`flores`** del sistema (te la entregan por USB o por descarga).

---

## Paso 1 — Instalar Laragon

Laragon es el programa que hace funcionar el sistema por dentro. Solo se instala **una vez**.

1. Abre el navegador y entra a **https://laragon.org/download/**
2. Haz clic en el botón grande que dice **"Download Laragon - Full"**.
3. Cuando termine la descarga, haz **doble clic** en el archivo descargado.
4. Si Windows pregunta "¿Desea permitir que esta app haga cambios?" → **Sí**.
5. En la ventana de instalación, **deja todo como está** y haz clic en **Next** hasta que aparezca **Install**.
6. Espera a que termine (unos 2 minutos) y haz clic en **Finish**.

Al terminar se abre solo el panel de Laragon. Ya puedes cerrarlo por ahora.

---

## Paso 2 — Copiar la carpeta del sistema

1. Abre el **Explorador de Archivos** (la carpeta amarilla de la barra de tareas).
2. En la barra de arriba, escribe esta ruta exacta y presiona Enter:
   ```
   C:\laragon\www
   ```
3. **Copia ahí dentro** la carpeta `flores` que te entregaron.

Debe quedar así: `C:\laragon\www\flores`

> ⚠️ **Importante:** la carpeta tiene que llamarse exactamente `flores`, todo en minúsculas.

---

## Paso 3 — Crear la base de datos

Aquí es donde se guardarán los datos de las solicitudes.

1. Abre **Laragon** (busca el ícono verde con forma de rana en el escritorio o el menú inicio).
2. Haz clic en el botón grande que dice **"Start All"**. Espera 5 segundos hasta que los textos se pongan en **verde**.
3. Haz clic en **"Menu"** (abajo a la derecha) → **MySQL** → **phpMyAdmin**.
4. Se abre el navegador con una página azul. Haz clic en la pestaña **"SQL"** (arriba al centro).
5. En otra ventana, abre la carpeta `C:\laragon\www\flores\sql` y abre el archivo `instalar_db.sql` con el **Bloc de notas** (clic derecho → Abrir con → Bloc de notas).
6. Selecciona **todo el texto** (Ctrl + A), **cópialo** (Ctrl + C).
7. Vuelve a la pestaña SQL de phpMyAdmin y **pégalo** ahí dentro (Ctrl + V).
8. Haz clic en el botón **"Continuar"** (esquina inferior derecha).

Si aparece un mensaje verde diciendo "Su consulta SQL se ha ejecutado con éxito" → ✅ listo.

---

## Paso 4 — Entrar al sistema por primera vez

1. Abre el navegador (Chrome, Edge, lo que uses).
2. Escribe esta dirección en la barra de arriba y presiona Enter:
   ```
   http://localhost:8001/login
   ```
3. Aparece la pantalla de inicio de sesión. Entra con:
   - **Usuario:** `admin`
   - **Contraseña:** `admin`

> 🔒 **Lo primero que debes hacer:** cambiar esa contraseña por una tuya. Ve a tu perfil (arriba a la derecha) → Cambiar contraseña.

---

## Paso 5 — Crear un acceso directo en el escritorio

Para no tener que escribir la dirección cada vez:

1. Abre el Explorador de Archivos y entra a `C:\laragon\www\flores`.
2. Busca el archivo **`iniciar.bat`** (es el que tiene engranajes como ícono).
3. **Clic derecho** sobre él → **Enviar a** → **Escritorio (crear acceso directo)**.
4. En el escritorio, **clic derecho** sobre el nuevo acceso → **Cambiar nombre** → escribe **"Sistema de Ramos"**.

Ahora, con **doble clic** en ese ícono, se abre el sistema directamente.

### ¿Quieres modo pantalla completa (kiosco)?

Si este PC se va a usar **solo para el sistema** (por ejemplo, en una recepción), usa el archivo `iniciar_kiosco.bat` en lugar de `iniciar.bat`. Se abre a pantalla completa sin barras del navegador.

Para salir del modo kiosco: presiona **Alt + F4**.

---

## Paso 6 — Que arranque solo al prender el PC (opcional)

Para que no tengas que abrir Laragon manualmente cada vez:

1. Abre Laragon.
2. Haz clic en el ícono de **engranaje** (arriba a la derecha).
3. En la pestaña **General**, marca estas dos casillas:
   - ✅ Auto-start Laragon when Windows boots
   - ✅ Start All when Laragon starts
4. Clic en **OK**.

Listo. La próxima vez que prendas el PC, el sistema estará funcionando solo.

---

## Hacer copias de seguridad (¡importante!)

**Una vez por semana** como mínimo, haz una copia de la base de datos:

1. Abre Laragon → Menu → MySQL → **phpMyAdmin**.
2. En el panel de la izquierda, haz clic en **`flores_db`**.
3. Pestaña **"Exportar"** (arriba) → botón **"Continuar"**.
4. Se descarga un archivo `.sql`. **Guárdalo en un USB o en la nube**.

Si algún día el PC falla, con ese archivo puedes restaurar todo.

---

## Si algo sale mal

| Qué pasa | Qué hacer |
|---|---|
| "No se puede acceder a localhost:8001" | Abre Laragon y haz clic en **Start All**. Espera a que se ponga verde. |
| Al entrar dice "Database not found" | Repite el **Paso 3** (crear la base de datos). |
| El acceso directo no abre nada | Asegúrate de que Laragon esté abierto y en verde antes de hacer doble clic. |
| Otros PCs de la red no pueden entrar | Llama al soporte técnico — hay que abrir un puerto en el firewall. |
| Olvidé la contraseña de admin | Llama al soporte técnico. |

---

## Soporte

Si nada de lo anterior funciona, contacta al soporte técnico con esta información:

- **Qué hiciste** justo antes de que fallara.
- **Mensaje de error** completo (puedes hacer captura de pantalla con la tecla `Impr Pant`).
- **Versión de Windows** (Win + R → escribe `winver` → Enter).
