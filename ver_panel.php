<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Panel del Operador</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Verificación del Panel del Operador</h1>
    
    <div class="info">
        <h3>📋 Información de Acceso</h3>
        <p><strong>URL del Panel:</strong> <a href="/flores/solicitudes/nueva" class="btn">/flores/solicitudes/nueva</a></p>
        <p><strong>URL de Prueba:</strong> <a href="/flores/test_panel.php" class="btn">/flores/test_panel.php</a></p>
        <p><strong>URL de Login:</strong> <a href="/flores/login" class="btn">/flores/login</a></p>
    </div>

    <div class="info">
        <h3>🔧 Pasos para Verificar</h3>
        <ol>
            <li><strong>Iniciar sesión:</strong> Accede a <code>/flores/login</code> con credenciales de operador</li>
            <li><strong>Acceder al panel:</strong> Una vez logueado, ve a <code>/flores/solicitudes/nueva</code></li>
            <li><strong>Verificar vista:</strong> Deberías ver el panel con scanner, formulario y diseño moderno</li>
        </ol>
    </div>

    <div class="success">
        <h3>✅ Características Implementadas</h3>
        <ul>
            <li>✅ Scanner de documentos con input grande</li>
            <li>✅ Modal de registro sin campo estado</li>
            <li>✅ Formulario de solicitud optimizado</li>
            <li>✅ Diseño moderno con CSS corporativo</li>
            <li>✅ JavaScript completo para el flujo</li>
            <li>✅ Validaciones y AJAX</li>
        </ul>
    </div>

    <div class="info">
        <h3>🐛 Si no funciona, verificar:</h3>
        <ul>
            <li>¿Estás logueado como operador?</li>
            <li>¿Estás accediendo a la URL correcta?</li>
            <li>¿Hay errores en la consola del navegador?</li>
            <li>¿El servidor web está corriendo?</li>
        </ul>
    </div>

    <div class="error">
        <h3>⚠️ Posibles Problemas</h3>
        <p><strong>Si ves pantalla en blanco:</strong> Revisa el log de errores de PHP</p>
        <p><strong>Si ves error 404:</strong> Verifica que el servidor esté configurado para /flores</p>
        <p><strong>Si ves error 500:</strong> Revisa los requires y permisos de archivos</p>
    </div>

    <script>
        // Verificar si hay errores de JavaScript
        window.onerror = function(msg, url, line, col, error) {
            console.error('Error detectado:', msg);
            document.body.innerHTML += '<div class="error"><h3>❌ Error JavaScript:</h3><p>' + msg + '</p></div>';
        };
    </script>
</body>
</html>
