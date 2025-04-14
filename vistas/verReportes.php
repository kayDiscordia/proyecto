<?php
session_start();
require_once '../controladores/controladorEmpleado.php';
require_once '../modelos/Database.php';
require_once '../login/functionLogin.php';

// Verificar sesión
$select = new Login();
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$user = $select->SelectuserByuser($_SESSION['id']);

// Verificar ID de empleado
if (!isset($_GET['idEmpleado']) || !is_numeric($_GET['idEmpleado'])) {
    die("ID de empleado no válido.");
}

$idEmpleado = (int)$_GET['idEmpleado'];
$empleadoController = new controladorEmpleado();

// Obtener parámetros de filtro
$filtroEstado = $_GET['estado'] ?? 'todos';
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

try {
    // Obtener datos del empleado y sus actividades con filtros
    $datos = $empleadoController->obtenerActividadesPorEmpleado($idEmpleado, $filtroEstado, $fechaInicio, $fechaFin);
    $infoEmpleado = $datos['empleado'];
    $actividades = $datos['actividades'];
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes del Empleado</title>
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
            <div class="flex justify-between items-center mb-6">
                <a href="listaEmpleado.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver a la lista
                </a>
                
                <div class="text-right">
                    <h2 class="text-xl font-semibold"><?= htmlspecialchars($infoEmpleado->nombres . ' ' . $infoEmpleado->apellidos) ?></h2>
                    <div class="flex gap-4 justify-end mt-1 text-sm">
                        <span class="text-gray-600">
                            <i class="fas fa-id-card mr-1"></i> <?= htmlspecialchars($infoEmpleado->cedula) ?>
                        </span>
                        <span class="text-gray-600">
                            <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($infoEmpleado->usuarioEmpleado) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filtrar Actividades</h3>
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="idEmpleado" value="<?= $idEmpleado ?>">
                    
                    <!-- Filtro por estado -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="estado" name="estado" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos" <?= $filtroEstado === 'todos' ? 'selected' : '' ?>>Todos los estados</option>
                            <option value="Completada" <?= $filtroEstado === 'Completada' ? 'selected' : '' ?>>Completadas</option>
                            <option value="En progreso" <?= $filtroEstado === 'En progreso' ? 'selected' : '' ?>>En progreso</option>
                            <option value="Cancelada" <?= $filtroEstado === 'Cancelada' ? 'selected' : '' ?>>Canceladas</option>
                        </select>
                    </div>
                    
                    <!-- Filtro por rango de fechas -->
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>" 
                               class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>" 
                               class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="md:col-span-3 flex justify-end space-x-3">
                        <button type="submit" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <button type="button" id="exportarPDF" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 ease-in-out flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Resultados -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Actividades Asignadas</h3>
                    <span class="text-sm text-gray-500">
                        <?= count($actividades) ?> actividad(es) encontrada(s)
                    </span>
                </div>
                
                <?php if (empty($actividades)): ?>
                    <div class="text-center py-2">
                        <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500">No hay actividades registradas para este empleado con los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table id="tablaActividades" class="w-full border-collapse">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">N°</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Descripción</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Categoría</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Departamento</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Inicio</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Fin</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php $contador = 1; ?>
                                <?php foreach ($actividades as $actividad): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-700"><?= $contador++ ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['descripcionActividad']) ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['nombreCategoria']) ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['departamento']) ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= $actividad['fechaInicio'] ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= $actividad['fechaCulminacion'] ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <span class="<?= 
                                            $actividad['estado'] == 'En progreso' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($actividad['estado'] == 'Cancelada' ? 'bg-red-100 text-red-800' : 
                                            ($actividad['estado'] == 'Completada' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))
                                        ?> px-2 py-1 rounded-lg text-xs">
                                            <?= htmlspecialchars($actividad['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Scripts para mejor manejo de fechas -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Inicializar datepickers
        flatpickr("#fecha_inicio", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        
        flatpickr("#fecha_fin", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
//exportar los datos filtrados de la tabla a PDF utilizando la biblioteca jsPDF
        // Exportar datos a PDF
       document.getElementById('exportarPDF').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Título
    doc.setFontSize(18);
    doc.text('Reporte de Actividades del Empleado', 10, 10);
    doc.setFontSize(12);
    doc.text(`Empleado: <?= htmlspecialchars($infoEmpleado->nombres . ' ' . $infoEmpleado->apellidos) ?>`, 10, 20);
    doc.text('Generado el: <?= date("Y-m-d H:i:s") ?>', 10, 30);

    // Obtener datos de la tabla
    const headers = ["N°", "Descripción", "Categoría", "Departamento", "Fecha Inicio", "Fecha Fin", "Estado"];
    const rows = [];
    const tableRows = document.querySelectorAll('#tablaActividades tbody tr');

    tableRows.forEach((row, index) => {
        const cells = row.querySelectorAll('td');
        const rowData = Array.from(cells).map(cell => cell.textContent.trim());
        
        const datosFila = rowData.slice(1); 
        
        // Agregamos el índice correcto (index + 1) y los demás datos
        rows.push([index + 1, ...datosFila]);
    });

    if (rows.length === 0) {
        alert('No hay datos para exportar.');
        return;
    }

    // Agregar la tabla al PDF
    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 40,
        theme: 'striped',
        headStyles: { fillColor: [22, 160, 133] },
        styles: { fontSize: 10 },
    });

    // Descargar el archivo PDF
    doc.save('reporte_actividades_empleado.pdf');
});
    </script>
</body>
</html>