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
    <title>Historial de Actividad</title>
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <div class="container mx-auto p-6">
                <h1 class="text-2xl font-bold mb-6">Historial de Actividad</h1>

                <div class="mb-4 bg-e8eeff">
                    <?php if (empty($historial)): ?>
                        <div class="p-4 text-center text-gray-500">
                            No hay eventos registrados en el historial.
                        </div>
                    <?php else: ?>
                        <ol class="relative border-s border-gray-300 ms-6">
                            <?php foreach ($historial as $evento): ?>
                                <li class="mb-10 ms-6">
                                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white">
                                        <i class="fas fa-history text-blue-800 text-xs"></i>
                                    </span>
                                    <h3 class="mb-2 text-lg font-semibold text-gray-900">
                                        <?= htmlspecialchars($evento['evento']) ?>
                                    </h3>
                                    <time class="block mb-3 text-sm font-normal leading-none text-gray-500">
                                        <i class="far fa-clock mr-2"></i><?= date('d-m-Y H:i:s', strtotime($evento['fecha'])) ?>
                                    </time>
                                    <div class="space-y-3">
                                        <p class="text-base font-normal text-gray-600 hover:text-indigo-800">
                                            <?= nl2br(htmlspecialchars($evento['detalles'])) ?>
                                        </p>
                                        <?php if (!empty($evento['comentarios'])): ?>
                                            <div class="p-3 bg-blue-50 rounded-lg">
                                                <p class="text-sm text-blue-800">
                                                    <i class="fas fa-comment-dots mr-2"></i>
                                                    <?= nl2br(htmlspecialchars($evento['comentarios'])) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <div class="mt-8">
                        <a href="verActividades.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                            <i class="fas fa-arrow-left mr-2"></i>Volver a Actividades
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>