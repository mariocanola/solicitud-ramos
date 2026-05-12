@echo off
REM ============================================================
REM Cliente Admin — para PCs distintos al servidor
REM ============================================================
REM Abre el navegador en la pagina de login del Sistema de Ramos.
REM NO necesita Laragon ni servicios locales: el PC servidor
REM (10.10.90.104) es quien aloja Apache, PHP y MySQL.
REM ============================================================

setlocal

REM === Configuracion: IP y puerto del servidor ===
set "SERVIDOR=http://10.10.90.104:8001"
set "URL=%SERVIDOR%/dashboard"

REM Abre la URL en el navegador por defecto
start "" "%URL%"

endlocal
exit /b 0
