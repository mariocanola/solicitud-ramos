@echo off
REM ============================================================
REM Sistema de Solicitud de Bockets - Modo Kiosco
REM ============================================================
REM Variante del lanzador que abre el navegador en PANTALLA COMPLETA
REM sin barras ni controles, ideal para un PC dedicado al kiosco.
REM
REM Funciona con Chrome o Edge. Si no detecta ninguno, abre el
REM navegador por defecto en una pestana normal.
REM ============================================================

setlocal
title Kiosco Bockets - Flores El Tandil

set "URL=http://localhost:8001/solicitudes/nueva"
set "LARAGON=C:\laragon\laragon.exe"
set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"
set "CHROME_X86=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
set "EDGE=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

REM Iniciar Laragon si no esta arriba
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I "httpd.exe" >NUL
if errorlevel 1 (
    if exist "%LARAGON%" (
        echo Iniciando Laragon...
        start "" "%LARAGON%" -autostart
        timeout /t 8 /nobreak >NUL
    )
)

REM Verificar MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if errorlevel 1 (
    timeout /t 5 /nobreak >NUL
)

REM Abrir en modo kiosco con Chrome o Edge
if exist "%CHROME%" (
    start "" "%CHROME%" --kiosk --no-first-run --disable-translate "%URL%"
    goto :end
)
if exist "%CHROME_X86%" (
    start "" "%CHROME_X86%" --kiosk --no-first-run --disable-translate "%URL%"
    goto :end
)
if exist "%EDGE%" (
    start "" "%EDGE%" --kiosk --no-first-run --disable-translate "%URL%" --edge-kiosk-type=fullscreen
    goto :end
)

REM Fallback: navegador por defecto en ventana normal
start "" "%URL%"

:end
endlocal
exit /b 0
