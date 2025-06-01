<?php
session_start();
require_once '../controladores/controladorActividad.php';

require_once '../login/functionLogin.php';

// Verificar si el usuario está logueado
// Asegúrate de iniciar la sesión
$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
    $idDepartamentoUsuario = $_SESSION['idDepartamento'] ?? null;
} else {
    header('location: ../index.php');
}
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
    <link rel="stylesheet" href="CSS/fontawesome.css">
    <link rel="stylesheet" href="CSS/flatpicker.css">
    <script defer src="JS/alpine.js"></script>
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <div class="w-full max-w-4xl mx-auto">
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">Historial de Actividad</h1>
                        <div class="flex space-x-2">
                            <a href="verActividades.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                            <?php if (!empty($historial)): ?>
                                <button id="exportarPDF" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 ease-in-out flex items-center">
                                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($historial)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">No hay historial</h3>
                            <p class="mt-1 text-gray-500">No se encontraron eventos registrados para esta actividad.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <ol class="relative border-s border-gray-200 ms-6">
                                <?php foreach ($historial as $evento): ?>
                                    <li class="mb-10 ms-6">
                                        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white">
                                            <i class="fas fa-history text-blue-800 text-xs"></i>
                                        </span>
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                            <h3 class="mb-1 text-lg font-semibold text-gray-900">
                                                <?= htmlspecialchars($evento['evento']) ?>
                                            </h3>
                                            <time class="block mb-2 text-sm font-normal text-gray-500">
                                                <i class="far fa-clock mr-1"></i><?= date('d-m-Y H:i:s', strtotime($evento['fecha'])) ?>
                                            </time>
                                            <div class="space-y-2">
                                                <p class="text-base font-normal text-gray-700">
                                                    <?= nl2br(htmlspecialchars($evento['detalles'])) ?>
                                                </p>
                                                <?php if (!empty($evento['comentarios'])): ?>
                                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                                                        <p class="text-sm text-blue-800">
                                                            <i class="fas fa-comment-dots mr-2"></i>
                                                            <?= nl2br(htmlspecialchars($evento['comentarios'])) ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts para PDF -->
    <script src="JS/jspdf.js"></script>
    <script src="JS/jspdf-autotable.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Exportar a PDF
            document.getElementById('exportarPDF')?.addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Título del documento
                doc.setFontSize(18);
                doc.text('Historial de Actividad', 10, 10);
                doc.setFontSize(12);
                doc.text('ID de Actividad: <?= $idActividad ?>', 10, 20);
                doc.text('Generado el: <?= date("Y-m-d H:i:s") ?>', 10, 30);
                
                // Preparar datos para la tabla 
                const headers = ["Evento", "Fecha", "Detalles"]; 
                const rows = [];
                
                <?php foreach ($historial as $evento): ?>
                    rows.push([
                        "<?= addslashes($evento['evento']) ?>",
                        "<?= date('d-m-Y H:i:s', strtotime($evento['fecha'])) ?>",
                        "<?= addslashes(str_replace(["\r\n", "\r", "\n"], ' ', $evento['detalles'])) ?>"
                        // Se eliminó el campo de comentarios
                    ]);
                <?php endforeach; ?>
                
                // Configuración de la tabla
                doc.autoTable({
                    head: [headers],
                    body: rows,
                    startY: 40,
                    theme: 'striped',
                    headStyles: {
                        fillColor: [41, 128, 185]
                    },
                    columnStyles: {
                        0: { cellWidth: 40 },
                        1: { cellWidth: 30 },
                        2: { cellWidth: 80 } 
                    },
                    styles: {
                        fontSize: 10,
                        cellPadding: 3,
                        overflow: 'linebreak'
                    },
                    margin: { top: 40 }
                });
                
                // Guardar el PDF
                doc.save('historial_actividad_<?= $idActividad ?>.pdf');
            });
        });
    </script>
</body>

</html>