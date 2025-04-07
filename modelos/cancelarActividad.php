<?php
require_once 'Database.php';
require_once 'modeloActividad.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    $idActividad = $_POST['idActividad'] ?? null;
    $descripcionCancelacion = $_POST['descripcionCancelacion'] ?? null;

    if (!$idActividad || !$descripcionCancelacion) {
        throw new Exception("Datos incompletos.");
    }

    $database = new Database();
    $conn = $database->getConnection();

    $actividad = new modeloActividad($conn);
    $actividad->cancelarActividad($idActividad, $descripcionCancelacion);

    header("Location:  ../vistas/verActividades.php?mensaje=Actividad cancelada correctamente");
    exit();
} catch (Exception $e) {
    header("Location:  ../vistas/verActividades.php?error=" . urlencode($e->getMessage()));
    exit();
}