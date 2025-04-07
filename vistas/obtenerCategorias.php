<?php
require_once __DIR__ . '/../controladores/controladorActividad.php';

header('Content-Type: application/json');

// Verificar si se recibió el parámetro
if (!isset($_GET['idDepartamento'])) {
    echo json_encode(['error' => 'Parámetro idDepartamento faltante']);
    exit();
}

// Validar que sea numérico
$idDepartamento = $_GET['idDepartamento'];
if (!is_numeric($idDepartamento)) {
    echo json_encode(['error' => 'El idDepartamento debe ser numérico']);
    exit();
}

// Convertir a entero
$idDepartamento = (int) $idDepartamento;

// Obtener las categorías
try {
    $controlador = new controladorActividad();
    $categorias = $controlador->obtenerCategoriasPorDepartamento($idDepartamento);
    echo json_encode($categorias);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>