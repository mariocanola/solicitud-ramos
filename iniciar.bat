@echo off
REM ============================================================
REM Sistema de Solicitud de Bockets - Lanzador General
REM ============================================================
REM Levanta el servidor PHP built-in y abre el navegador.
REM Para cerrar el servidor, cierra la ventana que queda abierta
REM con el titulo "Servidor PHP - Bockets".
REM ============================================================

setlocal
title Sistema de Solicitud de Bockets

set "PUERTO=8001"
set "URL=http://localhost:%PUERTO%"
set "DIRECTORIO=%~dp0public"

echo.
echo  ============================================================
echo   Sistema de Solicitud de Bockets - Flores El Tandil
echo  ============================================================
echo.

REM Verificar si ya hay algo corriendo en el puerto
netstat -ano | find ":%PUERTO% " | find "LISTENING" >NUL 2>&1
if not errorlevel 1 (
    echo  El puerto %PUERTO% ya esta en uso. Abriendo el navegador...
    echo  URL: %URL%
    start "" "%URL%"
    goto :end
)

REM Verificar que php.exe este disponible
where php >NUL 2>&1
if errorlevel 1 (
    echo  ERROR: No se encontro PHP en el PATH del sistema.
    echo  Asegurese de que PHP este instalado y agregado al PATH.
    pause
    exit /b 1
)

echo  Iniciando servidor PHP en %URL% ...
echo  (Deje esta ventana abierta mientras usa el sistema)
echo.

REM Lanzar el servidor PHP en segundo plano en una ventana separada
start "Servidor PHP - Bockets" /MIN php -S localhost:%PUERTO% -t "%DIRECTORIO%"

REM Esperar a que el servidor arranque
timeout /t 2 /nobreak >NUL

echo  Abriendo el sistema en el navegador...
start "" "%URL%"

:end
endlocal
exit /b 0
