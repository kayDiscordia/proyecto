<?php
require_once '../modelos/modeloActividad.php';

$idActividad = $_POST['idActividad'];
$descripcionCancelacion = $_POST['descripcionCancelacion'];

$modeloActividad = new modeloActividad();

try {
    $modeloActividad->cancelarActividad($idActividad, $descripcionCancelacion);
    header('Location: verActividades.php?mensaje=Cancelada');
    exit();
} catch (Exception $e) {
    die("Error al cancelar la actividad: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Actividad</title>
    <link rel="stylesheet" href="CSS/output.css">
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-semibold mb-6">Cancelar Actividad</h2>
            <form action="../modelos/cancelarActividad.php" method="POST">
                <input type="hidden" name="idActividad" value="<?= htmlspecialchars($idActividad) ?>">
                <div class="mb-4">
                    <label for="descripcionCancelacion" class="block text-sm font-medium text-gray-700">Motivo de Cancelación</label>
                    <textarea id="descripcionCancelacion" name="descripcionCancelacion" rows="4" class="mt-1 p-2 w-full border rounded-md" required></textarea>
                </div>
                <div class="flex justify-end">
                    <a href="verActividades.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</a>
                    <button type="submit" class="ml-2 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>