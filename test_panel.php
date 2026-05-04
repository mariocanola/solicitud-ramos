<?php
// Archivo de prueba para verificar el panel del operador
session_start();

// Configuración básica
define('BASE_URL', '/flores');
define('BASE_PATH', __DIR__);

// Simular variables necesarias para la vista
$flash = '';
$flashTipo = '';
$hoy = date('Y-m-d');
$csrfField = '<input type="hidden" name="_csrf_token" value="test">';
$pageTitle = 'Panel del Operador';

// Simular datos de sedes y motivos
$sedes = [
    ['id' => 1, 'nombre' => 'Primavera'],
    ['id' => 2, 'nombre' => 'Verano']
];

$motivos = [
    ['id' => 1, 'nombre' => 'Aniversario', 'requiere_detalle' => 0],
    ['id' => 2, 'nombre' => 'Cumpleaños', 'requiere_detalle' => 1]
];

// Simular usuario autenticado
$_SESSION['user'] = ['nombre' => 'Operador Test'];

// Funciones auxiliares simuladas
function Auth() {
    return (object)[
        'user' => function() {
            return $_SESSION['user'] ?? ['nombre' => 'Test'];
        }
    ];
}

function Session() {
    return (object)[
        'getCsrfToken' => function() { return 'test_token'; }
    ];
}

// Cargar la vista del panel del operador
include __DIR__ . '/app/views/solicitudes/formulario_operador.php';
?>
