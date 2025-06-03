<?php
require_once 'Database.php';
require_once 'modeloActividad.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    $idActividad = $_POST['idActividad'] ?? null;
    $descripcionCulminacion = $_POST['descripcionCulminacion'] ?? null;

    if (!$idActividad || !$descripcionCulminacion) {
        throw new Exception("Datos incompletos.");
    }

    $database = new Database();
    $conn = $database->getConnection();

    $actividad = new modeloActividad($conn);
    $actividad->culminarActividad($idActividad, $descripcionCulminacion);

    header("Location:  ../vistas/verActividades.php?mensaje=Actividad culminada correctamente");
    exit();
} catch (Exception $e) {
    header("Location:  ../vistas/verActividades.php?error=" . urlencode($e->getMessage()));
    exit();
}