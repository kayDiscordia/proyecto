<?php
require_once '../modelos/modeloActividad.php';

$idActividad = $_POST['idActividad'] ?? $_GET['idActividad'] ?? null;
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descripcionCulminacion'])) {
    $descripcionCulminacion = $_POST['descripcionCulminacion'];
    $modeloActividad = new modeloActividad();

    try {
        $modeloActividad->culminarActividad($idActividad, $descripcionCulminacion);
        header('Location: verActividades.php?mensaje=Culminada');
        exit();
    } catch (Exception $e) {
        $mensaje = "Error al culminar la actividad: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Culminar Actividad</title>
    <link rel="stylesheet" href="CSS/output.css">
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-semibold mb-6">Culminar Actividad</h2>
            <?php if ($mensaje): ?>
                <div class="mb-4 text-red-600"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="hidden" name="idActividad" value="<?= htmlspecialchars($idActividad) ?>">
                <div class="mb-4">
                    <label for="descripcionCulminacion" class="block text-sm font-medium text-gray-700">Descripción de Culminación</label>
                    <textarea id="descripcionCulminacion" name="descripcionCulminacion" rows="4" class="mt-1 p-2 w-full border rounded-md" required></textarea>
                </div>
                <div class="flex justify-end">
                    <a href="verActividades.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</a>
                    <button type="submit" class="ml-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>