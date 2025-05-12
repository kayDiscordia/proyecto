<?php
require_once '../controladores/controladorActividad.php';

$actividadController = new controladorActividad();

try {
    // Llama al método que actualiza los estados de las actividades
    $actividadController->actualizarEstadosActividades();
    echo "[" . date('d-m-Y H:i:s') . "] Estados actualizados correctamente.\n";
} catch (Exception $e) {
    echo "[" . date('d-m-Y H:i:s') . "] Error: " . $e->getMessage() . "\n";
}
?>