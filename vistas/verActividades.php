<?php
session_start();
require_once '../controladores/controladorActividad.php';
require_once '../controladores/controladorEmpleado.php';

// Crear instancia del controlador
$actividadController = new controladorActividad();
$empleadoController = new controladorEmpleado();

// Obtener filtros desde la URL
$categoriaFiltro = $_GET['categoria'] ?? 'todas';
$estadoFiltro = $_GET['estado'] ?? 'todos';
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';

try {
    // Obtener categorías, empleados y actividades filtradas
    $categorias = $actividadController->obtenerCategoriasActividades();
    $empleados = $empleadoController->obtenerEmpleados();
    $actividades = $actividadController->obtenerActividadesFiltradas($estadoFiltro, $fechaInicio, $fechaFin, $categoriaFiltro);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActividad = $_POST['idActividad'];
    $descripcionActividad = $_POST['descripcionActividad'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaCulminacion = $_POST['fechaCulminacion'];
    $idEmpleado = $_POST['idEmpleado'];
    $idCategoria = $_POST['idCategoria'];

    try {
        $actividadController->editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria);
        echo "<script>alert('Actividad actualizada exitosamente');</script>";
        header('Location: verActividades.php');
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error al actualizar la actividad: " . $e->getMessage() . "');</script>";
        header('Location: verActividades.php');
        exit;
    }
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
    
    <style>
        /* Estilo para el fondo del modal */
    #modalDetalles, #modalEditar {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
        z-index: 50;
    }

    /* Ocultar los modales por defecto */
    #modalDetalles.hidden, #modalEditar.hidden {
        display: none;
    }

    /* Estilo para el contenido del modal */
    .modal-content {
        background-color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 50%; /* Ancho del modal */
        max-width: 600px;
    }

    /* Botón de cerrar */
    .modal-close {
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background-color: #e5e7eb; /* Gris claro */
        color: #374151; /* Gris oscuro */
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .modal-close:hover {
        background-color: #d1d5db; /* Gris más claro */
    }
    </style>

</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>
        
        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            
            <h2 class="text-2xl font-semibold mb-4">Lista de Actividades</h2>
            <!-- Filtros -->
             
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filtrar Actividades</h3>
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filtro por categoría -->
                    <div>
                        <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select id="categoria" name="categoria" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="todas">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['idCategoria']) ?>" <?= $categoriaFiltro == $categoria['idCategoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombreCategoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro por estado -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="estado" name="estado" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos los estados</option>
                            <option value="Completada" <?= $estadoFiltro === 'Completada' ? 'selected' : '' ?>>Completadas</option>
                            <option value="En progreso" <?= $estadoFiltro === 'En progreso' ? 'selected' : '' ?>>En progreso</option>
                            <option value="Cancelada" <?= $estadoFiltro === 'Cancelada' ? 'selected' : '' ?>>Canceladas</option>
                        </select>
                    </div>
                    
                    <!-- Filtro por rango de fechas -->
                    <div>
                        <label for="fechaInicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" id="fechaInicio" name="fechaInicio" value="<?= htmlspecialchars($fechaInicio) ?>" 
                               class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="fechaFin" class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input type="date" id="fechaFin" name="fechaFin" value="<?= htmlspecialchars($fechaFin) ?>" 
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
                    <h3 class="text-lg font-medium text-gray-900">Actividades</h3>
                    <span class="text-sm text-gray-500">
                        <?= count($actividades) ?> actividad(es) encontrada(s)
                    </span>
                </div>

                <?php if (empty($actividades)): ?>
                    <div class="text-center py-2">
                        <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500">No hay actividades registradas con los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table id="tablaActividades" class="w-full border-collapse">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">N°</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Categoría</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Descripción</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Empleado Responsable</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Inicio</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Fin</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                                    <th class="p-3 text-center text-sm font-semibold text-gray-700">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($actividades as $index => $actividad): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 text-sm text-gray-700"><?= $index + 1 ?></td>
                                        <td class="p-3 text-sm text-gray-700 hidden"><?= htmlspecialchars($actividad['idActividad']) ?></td>
                                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['categoriaActividad']) ?></td>
                                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['descripcionActividad']) ?></td>
                                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['nombreEmpleado']) ?></td>
                                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['fechaInicio']) ?></td>
                                        <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['fechaCulminacion']) ?></td>
                                        <td class="p-3 text-sm text-gray-700">
                                            <span class="<?= 
                                                $actividad['estadoActividad'] == 'En progreso' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($actividad['estadoActividad'] == 'Cancelada' ? 'bg-red-100 text-red-800' : 
                                                ($actividad['estadoActividad'] == 'Completada' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))
                                            ?> px-2 py-1 rounded-lg text-xs">
                                                <?= htmlspecialchars($actividad['estadoActividad']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-sm text-gray-700 flex space-x-2 justify-center">
                                            <?php if ($actividad['estadoActividad'] !== 'Completada' && $actividad['estadoActividad'] !== 'Cancelada'): ?>
                                                <!-- Botón para Cancelar -->
                                                <a href="formularioCancelar.php?idActividad=<?= $actividad['idActividad'] ?>" 
                                                   class="text-red-600 hover:text-red-800 bg-red-100 px-3 py-1 rounded-md flex items-center">
                                                    <i class="fas fa-times mr-1"></i>Cancelar
                                                </a>
                                                <!-- Botón para Culminar -->
                                                <a href="formularioCulminar.php?idActividad=<?= $actividad['idActividad'] ?>" 
                                                   class="text-green-600 hover:text-green-800 bg-green-100 px-3 py-1 rounded-md flex items-center">
                                                    <i class="fas fa-check mr-1"></i>Culminar
                                                </a>
                                            <?php endif; ?>
                                            <!-- Despues de Culminar o Cancelar, mostrar botón para ver detalles y editar -->
                                            <?php if ($actividad['estadoActividad'] == 'Completada' or $actividad['estadoActividad'] == 'Cancelada'): ?>
                                                <td class="p-3 text-sm text-gray-700 flex space-x-2 justify-center">
                                                    <!-- Botón para Ver Detalles -->
                                                    <button type="button" onclick="mostrarDetalles(<?= htmlspecialchars(json_encode($actividad)) ?>)" 
                                                            class="text-blue-600 hover:text-blue-800 bg-blue-100 px-3 py-1 rounded-md flex items-center">
                                                        <i class="fas fa-eye mr-1"></i>Detalles
                                                    </button>

                                                    <!-- Botón para Editar -->
                                                    <button type="button" onclick="mostrarEditar(<?= htmlspecialchars(json_encode($actividad)) ?>)" 
                                                            class="text-yellow-600 hover:text-yellow-800 bg-yellow-100 px-3 py-1 rounded-md flex items-center">
                                                        <i class="fas fa-edit mr-1"></i>Editar
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Modal para Ver Detalles -->
            <div id="modalDetalles" class="fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h2 class="text-xl font-semibold mb-4">Detalles de la Actividad</h2>
        <p><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
        <p><strong>Fecha Inicio:</strong> <span id="detalleFechaInicio"></span></p>
        <p><strong>Fecha Culminación:</strong> <span id="detalleFechaCulminacion"></span></p>
        <p><strong>Empleado:</strong> <span id="detalleEmpleado"></span></p>
        <p><strong>Categoría:</strong> <span id="detalleCategoria"></span></p>
        <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
        <button onclick="cerrarModal('modalDetalles')" 
                class="mt-4 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
            Cerrar
        </button>
    </div>
</div>

            <!-- Modal para Editar -->
            <div id="modalEditar" class="fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h2 class="text-xl font-semibold mb-4">Editar Actividad</h2>
        <form id="formEditar" method="POST">
            <input type="hidden" id="editarIdActividad" name="idActividad">
            
            <label>Descripción:</label>
            <input type="text" id="editarDescripcion" name="descripcionActividad" class="w-full p-2 border rounded mb-4" required>
            
            <label>Fecha Inicio:</label>
            <input type="date" id="editarFechaInicio" name="fechaInicio" class="w-full p-2 border rounded mb-4" required>
            
            <label>Fecha Culminación:</label>
            <input type="date" id="editarFechaCulminacion" name="fechaCulminacion" class="w-full p-2 border rounded mb-4" required>
            
            <label>Empleado:</label>
            <select id="editarEmpleado" name="idEmpleado" class="w-full p-2 border rounded mb-4" required>
                <option value="">Seleccione un empleado</option>
                <?php foreach ($empleados as $empleado): ?>
                    <option value="<?= htmlspecialchars($empleado['idEmpleado']) ?>">
                        <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label>Categoría:</label>
            <select id="editarCategoria" name="idCategoria" class="w-full p-2 border rounded mb-4" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= htmlspecialchars($categoria['idCategoria']) ?>">
                        <?= htmlspecialchars($categoria['nombreCategoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="cerrarModal('modalEditar')" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
        </main>
    </div>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Inicializar datepickers
        flatpickr("#fechaInicio", {
            dateFormat: "d-m-Y",
            allowInput: true
        });
        
        flatpickr("#fechaFin", {
            dateFormat: "d-m-Y",
            allowInput: true
        });

        // Exportar datos a PDF
        document.getElementById('exportarPDF').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Título
            doc.setFontSize(18);
            doc.text('Reporte de Actividades', 10, 10);
            doc.setFontSize(12);
            doc.text('Generado el: <?= date("Y-m-d H:i:s") ?>', 10, 20);

            // Obtener datos de la tabla
            const headers = ["N°", "Categoría", "Descripción", "Fecha Inicio", "Fecha Fin", "Estado"];
            const rows = [];
            const tableRows = document.querySelectorAll('#tablaActividades tbody tr');

            tableRows.forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                const rowData = Array.from(cells).map(cell => cell.textContent.trim());
                rows.push([index + 1, ...rowData.slice(1)]);
            });

            if (rows.length === 0) {
                alert('No hay datos para exportar.');
                return;
            }

            // Agregar la tabla al PDF
            doc.autoTable({
                head: [headers],
                body: rows,
                startY: 30,
                theme: 'striped',
                headStyles: { fillColor: [22, 160, 133] },
                styles: { fontSize: 10 },
            });

            // Descargar el archivo PDF
            doc.save('reporte_actividades.pdf');
        });
        
        // MODALES (jaja chiste)
            function mostrarDetalles(actividad) {
        document.getElementById('detalleDescripcion').textContent = actividad.descripcionActividad;
        document.getElementById('detalleFechaInicio').textContent = actividad.fechaInicio;
        document.getElementById('detalleFechaCulminacion').textContent = actividad.fechaCulminacion;
        document.getElementById('detalleEmpleado').textContent = actividad.nombreEmpleado;
        document.getElementById('detalleCategoria').textContent = actividad.categoriaActividad;
        document.getElementById('detalleEstado').textContent = actividad.estadoActividad;
        document.getElementById('modalDetalles').classList.remove('hidden');
    }

    function mostrarEditar(actividad) {
    // Rellenar los campos del modal
    document.getElementById('editarIdActividad').value = actividad.idActividad;
    document.getElementById('editarDescripcion').value = actividad.descripcionActividad;
    document.getElementById('editarFechaInicio').value = actividad.fechaInicio;
    document.getElementById('editarFechaCulminacion').value = actividad.fechaCulminacion;

    // Seleccionar el empleado correspondiente
    const empleadoSelect = document.getElementById('editarEmpleado');
    empleadoSelect.value = actividad.idEmpleado;

    // Seleccionar la categoría correspondiente
    const categoriaSelect = document.getElementById('editarCategoria');
    categoriaSelect.value = actividad.idCategoria;

    // Mostrar el modal
    document.getElementById('modalEditar').classList.remove('hidden');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
    </script>
</body>
</html>