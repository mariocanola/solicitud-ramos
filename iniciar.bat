@echo off
REM ============================================================
REM Sistema de Solicitud de Bockets - Lanzador General
REM ============================================================
REM Verifica que Apache y MySQL esten corriendo. Si no, los inicia
REM via Laragon. Luego abre el navegador en el panel de admin.
REM ============================================================

setlocal
title Sistema de Solicitud de Bockets
echo.
echo  ============================================================
echo   Sistema de Solicitud de Bockets - Flores El Tandil
echo  ============================================================
echo.

REM Cambia esta URL si el sistema corre en otro puerto.
set "URL=http://localhost:8001"
set "LARAGON=C:\laragon\laragon.exe"

REM Verifica si Apache esta corriendo
echo  Verificando servicios...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I "httpd.exe" >NUL
if errorlevel 1 (
    echo  Apache no esta corriendo. Iniciando Laragon...
    if exist "%LARAGON%" (
        start "" "%LARAGON%" -autostart
    ) else (
        echo  ERROR: No se encontro Laragon en %LARAGON%
        echo  Verifique que Laragon este instalado o ajuste la ruta en este archivo.
        pause
        exit /b 1
    )
    echo  Esperando a que los servicios arranquen...
    timeout /t 8 /nobreak >NUL
) else (
    echo  Apache ya esta corriendo.
)

REM Verifica si MySQL esta corriendo
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if errorlevel 1 (
    echo  MySQL no esta corriendo. Esperando 5 segundos mas...
    timeout /t 5 /nobreak >NUL
) else (
    echo  MySQL ya esta corriendo.
)

echo.
echo  Abriendo el sistema en el navegador...
echo  URL: %URL%
echo.

REM Abre el navegador por defecto en la URL del sistema.
start "" "%URL%"

echo  Listo. Esta ventana se cerrara en 3 segundos.
timeout /t 3 /nobreak >NUL
endlocal
exit /b 0
