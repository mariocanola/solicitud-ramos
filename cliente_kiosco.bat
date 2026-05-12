@echo off
REM ============================================================
REM Cliente del kiosco — para PCs distintos al servidor
REM ============================================================
REM Este archivo solo abre el navegador apuntando al servidor del
REM Sistema de Ramos. NO necesita Laragon ni servicios locales: el
REM PC servidor (10.10.90.104) es quien aloja Apache, PHP y MySQL.
REM
REM Si cambia la IP o el puerto del servidor, edite la variable
REM SERVIDOR en este archivo y en cliente_admin.bat
REM ============================================================

setlocal
title Kiosco Tandil

REM === Configuracion: IP y puerto del servidor ===
set "SERVIDOR=http://10.10.90.104:8001"
set "URL=%SERVIDOR%/solicitudes/nueva"

REM === Rutas de navegadores soportados ===
set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"
set "CHROME_X86=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
set "EDGE=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

REM === Abrir en modo kiosco (pantalla completa) ===
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

REM Fallback: navegador por defecto
start "" "%URL%"

:end
endlocal
exit /b 0
