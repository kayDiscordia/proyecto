<?php
session_start();
require_once '../controladores/controladorActividad.php';

if (!isset($_GET['id'])) {
    die("ID de actividad no proporcionado.");
}

$idActividad = intval($_GET['id']);
$actividadController = new controladorActividad();

try {
    $historial = $actividadController->obtenerHistorialActividad($idActividad);
} catch (Exception $e) {
    die("Error al obtener el historial: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Actividades</title>
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>

        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            <div class="container mx-auto p-6">
                <h1 class="text-2xl font-bold mb-4">Historial de Actividad</h1>
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-3 border border-gray-300">Evento</th>
                            <th class="p-3 border border-gray-300">Fecha</th>
                            <th class="p-3 border border-gray-300">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $evento): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 border border-gray-300"><?= htmlspecialchars($evento['evento']) ?></td>
                                <td class="p-3 border border-gray-300"><?= htmlspecialchars($evento['fecha']) ?></td>
                                <td class="p-3 border border-gray-300"><?= htmlspecialchars($evento['detalles']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between col-span-2">
                <a href="verActividades.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    Salir
                </a>
            </div>
        </main>
    </div>

</body>

</html>