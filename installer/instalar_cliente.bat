@echo off
setlocal EnableDelayedExpansion
title Instalador del Cliente Flores

echo.
echo ===============================================
echo   INSTALADOR DEL CLIENTE FLORES
echo ===============================================
echo.
echo Este script crea un acceso directo en el escritorio
echo de este equipo para conectarse al servidor Flores.
echo.

REM ---------- 1) IP del servidor ----------
set "SERVER_IP="
if exist "%USERPROFILE%\.flores_cliente_ip.txt" set /p SERVER_IP=<"%USERPROFILE%\.flores_cliente_ip.txt"
if "%SERVER_IP%"=="" set "SERVER_IP=10.10.90.104"

echo IP actual del servidor: %SERVER_IP%
set /p NUEVA_IP=Nueva IP del servidor (Enter para mantener %SERVER_IP%):
if not "%NUEVA_IP%"=="" set "SERVER_IP=%NUEVA_IP%"

echo %SERVER_IP%> "%USERPROFILE%\.flores_cliente_ip.txt"
echo   IP guardada: %SERVER_IP%

REM ---------- 2) Puerto del servidor ----------
set "SERVER_PORT="
if exist "%USERPROFILE%\.flores_cliente_port.txt" set /p SERVER_PORT=<"%USERPROFILE%\.flores_cliente_port.txt"
if "%SERVER_PORT%"=="" set "SERVER_PORT=8001"

echo.
echo Puerto actual del servidor: %SERVER_PORT%
set /p NUEVO_PORT=Nuevo puerto del servidor (Enter para mantener %SERVER_PORT%):
if not "%NUEVO_PORT%"=="" set "SERVER_PORT=%NUEVO_PORT%"

echo %SERVER_PORT%> "%USERPROFILE%\.flores_cliente_port.txt"
echo   Puerto guardado: %SERVER_PORT%

REM ---------- 3) Tipo de cliente ----------
echo.
echo Tipo de cliente:
echo   1) Kiosco (pantalla completa, ruta /solicitudes/nueva)
echo   2) Administrador (dashboard normal)
echo   3) Ambos
set /p TIPO=Elige (1/2/3):

REM ---------- 4) Carpeta destino para los .bat ----------
set "DEST=%USERPROFILE%\Flores"
if not exist "%DEST%" mkdir "%DEST%"

REM ---------- 4b) Generar icono de la aplicacion ----------
echo.
echo Generando icono de la aplicacion...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0generar_icono.ps1" -OutPath "%DEST%\flores.ico"
if exist "%DEST%\flores.ico" (
    set "ICO_PATH=%DEST%\flores.ico,0"
) else (
    echo   WARN: no se pudo generar el icono, se usara el icono por defecto
    set "ICO_PATH=imageres.dll,3"
)

REM ---------- 5) Generar .bat segun tipo ----------
if "%TIPO%"=="1" goto :kiosco
if "%TIPO%"=="2" goto :admin
if "%TIPO%"=="3" goto :ambos
echo Opcion invalida.
pause
exit /b 1

:kiosco
call :crear_kiosco
goto :fin

:admin
call :crear_admin
goto :fin

:ambos
call :crear_kiosco
call :crear_admin
goto :fin

:crear_kiosco
echo.
echo Creando lanzador del kiosco...
(
    echo @echo off
    echo set "URL=http://%SERVER_IP%:%SERVER_PORT%/solicitudes/nueva"
    echo where chrome.exe ^>nul 2^>^&1 ^&^& start "" chrome.exe --kiosk --new-window "%%URL%%" ^&^& exit
    echo where msedge.exe ^>nul 2^>^&1 ^&^& start "" msedge.exe --kiosk --new-window "%%URL%%" ^&^& exit
    echo start "" "%%URL%%"
) > "%DEST%\flores_kiosco.bat"

set "SHORTCUT=%USERPROFILE%\Desktop\Flores Kiosco.lnk"
powershell -NoProfile -Command "$s = (New-Object -ComObject WScript.Shell).CreateShortcut('%SHORTCUT%'); $s.TargetPath = '%DEST%\flores_kiosco.bat'; $s.IconLocation = '%ICO_PATH%'; $s.Description = 'Solicitudes de ramos florales (kiosco pantalla completa)'; $s.Save()"
echo   OK: acceso directo "Flores Kiosco" creado en escritorio
exit /b 0

:crear_admin
echo.
echo Creando lanzador del administrador...
(
    echo @echo off
    echo set "URL=http://%SERVER_IP%:%SERVER_PORT%/dashboard"
    echo where chrome.exe ^>nul 2^>^&1 ^&^& start "" chrome.exe --new-window "%%URL%%" ^&^& exit
    echo where msedge.exe ^>nul 2^>^&1 ^&^& start "" msedge.exe --new-window "%%URL%%" ^&^& exit
    echo start "" "%%URL%%"
) > "%DEST%\flores_admin.bat"

set "SHORTCUT=%USERPROFILE%\Desktop\Flores Admin.lnk"
powershell -NoProfile -Command "$s = (New-Object -ComObject WScript.Shell).CreateShortcut('%SHORTCUT%'); $s.TargetPath = '%DEST%\flores_admin.bat'; $s.IconLocation = '%ICO_PATH%'; $s.Description = 'Dashboard administrativo de Flores'; $s.Save()"
echo   OK: acceso directo "Flores Admin" creado en escritorio
exit /b 0

:fin
echo.
echo ===============================================
echo   CLIENTE INSTALADO
echo ===============================================
echo.
echo Servidor:    http://%SERVER_IP%:%SERVER_PORT%
echo Lanzadores:  %DEST%
echo Atajos:      escritorio
echo.
echo Si en el futuro cambia la IP del servidor, ejecuta
echo este instalador de nuevo con la IP nueva.
echo.
pause
