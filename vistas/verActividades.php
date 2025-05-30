<?php
session_start();
require_once '../controladores/controladorActividad.php';
require_once '../controladores/controladorEmpleado.php';
require '../login/functionLogin.php';

// Verificar sesión
$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
    $idDepartamentoUsuario = $_SESSION['idDepartamento'] ?? null;
} else {
    header('location: ../index.php');
    exit;
}

// Crear instancia del controlador
$actividadController = new controladorActividad();
$empleadoController = new controladorEmpleado();

// Obtener filtros desde la URL (primero definimos las variables)
$categoriaFiltro = $_GET['categoria'] ?? 'todas';
$estadoFiltro = $_GET['estado'] ?? 'todos';
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';

// Determinar si se están aplicando filtros explícitos
$filtrosAplicados = !empty($_GET);

try {
    // Actualizar estados de las actividades según las fechas
    $actividadController->actualizarEstadosActividades();

    // Obtener categorías solo del departamento del usuario
    $categorias = $actividadController->obtenerCategoriasPorDepartamento($idDepartamentoUsuario);
    $empleados = $empleadoController->obtenerEmpleados();

    // Obtener actividades filtradas
    $actividades = $actividadController->obtenerActividadesFiltradas(
        $estadoFiltro,
        $fechaInicio,
        $fechaFin,
        $categoriaFiltro,
        // $filtrosAplicados ? null : $idDepartamentoUsuario // Solo filtrar por departamento si no hay otros filtros
    );
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Manejar el envío del formulario POST para edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActividad = $_POST['idActividad'] ?? null;
    $descripcionActividad = $_POST['descripcionActividad'] ?? '';
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaCulminacion = $_POST['fechaCulminacion'] ?? '';
    $idEmpleado = $_POST['idEmpleado'] ?? null;
    $idCategoria = $_POST['idCategoria'] ?? null;

    try {
        $actividadController->editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria);
        echo "<script>alert('Actividad actualizada exitosamente');</script>";
        header('Location: verActividades.php');
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error al actualizar la actividad: " . addslashes($e->getMessage()) . "');</script>";
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
    <link rel="stylesheet" href="CSS/datatableJquery.css">
    <link rel="stylesheet" href="CSS/datatable.css">
    <link rel="stylesheet" href="CSS/fontawesome.css">
    <link rel="stylesheet" href="CSS/flatpicker.css">
    <script defer src="JS/alpine.js"></script>

    <style>
        /* Estilo para el fondo del modal */
        #modalDetalles,
        #modalCulminar,
        #modalCancelar,
        #modalEditar {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.5);
            /* Fondo semitransparente */
            z-index: 50;
        }

        /* Ocultar los modales por defecto */
        #modalDetalles.hidden,
        #modalCulminar.hidden,
        #modalCancelar.hidden,
        #modalEditar.hidden {
            display: none;
        }

        /* Estilo para el contenido del modal */
        .modal-content {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 50%;
            /* Ancho del modal */
            max-width: 600px;
        }

        /* Botón de cerrar */
        .modal-close {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #e5e7eb;
            /* Gris claro */
            color: #374151;
            /* Gris oscuro */
            border-radius: 0.375rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-close:hover {
            background-color: #d1d5db;
            /* Gris más claro */
        }

        /* DATATABLE */
        Estilos para DataTables */ #tablaActividades {
            border-collapse: separate;
            border-spacing: 0;
        }

        #tablaActividades thead th {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            z-index: 10;
        }

        #tablaActividades tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Estilos para los botones de exportación */
        .dt-buttons .btn-export {
            transition: all 0.3s ease;
        }

        .dt-buttons .btn-export:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para la paginación */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.25rem 0.75rem;
            margin: 0 0.125rem;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3b82f6;
            color: white !important;
            border-color: #3b82f6;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e2e8f0;
            border-color: #cbd5e0;
        }

        /* Estilos para el buscador */
        .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
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
            <?php /*>
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
                            <option value="Retraso" <?= $estadoFiltro === 'Retraso' ? 'selected' : '' ?>>Retraso</option>
                            <option value="Por iniciar" <?= $estadoFiltro === 'Por iniciar' ? 'selected' : '' ?>>Por iniciar</option>
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
<?php */ ?>
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
                    <div class="rounded-lg shadow bg-white" style="max-width: 100%;">
                        <div style="max-height: 420px; overflow-y: auto;">
                            <table id="tablaActividades" class="min-w-full text-xs">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($actividades as $index => $actividad): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= $index + 1 ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($actividad['categoriaActividad']) ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($actividad['nombreActividad']) ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($actividad['nombreEmpleado']) ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= date('d-m-Y', strtotime($actividad['fechaInicio'])) ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= date('d-m-Y', strtotime($actividad['fechaCulminacion'])) ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <?php
                                                $estadoClases = [
                                                    'En progreso' => 'bg-yellow-100 text-yellow-800',
                                                    'Cancelada' => 'bg-red-100 text-red-800',
                                                    'Completada' => 'bg-green-100 text-green-800',
                                                    'Retraso' => 'bg-orange-100 text-orange-800',
                                                    'Por Iniciar' => 'bg-blue-100 text-blue-800'
                                                ];
                                                $clase = $estadoClases[$actividad['estadoActividad'] ?? 'bg-gray-100 text-gray-800'];
                                                ?>
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $clase ?>">
                                                    <?= htmlspecialchars($actividad['estadoActividad']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-center">
                                                <div class="flex justify-center space-x-1">
                                                    <!-- Botón Detalles -->
                                                    <button onclick="mostrarDetalles(<?= htmlspecialchars(json_encode($actividad)) ?>)"
                                                        class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50"
                                                        title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <!-- Botón Historial -->
                                                    <a href="verHistorial.php?id=<?= htmlspecialchars($actividad['idActividad']) ?>"
                                                        class="text-gray-600 hover:text-gray-900 p-1 rounded-full hover:bg-gray-50"
                                                        title="Historial">
                                                        <i class="fas fa-history"></i>
                                                    </a>

                                                    <?php if ($actividad['estadoActividad'] !== 'Completada' && $actividad['estadoActividad'] !== 'Cancelada'): ?>
                                                        <!-- Botón Editar -->
                                                        <button onclick="mostrarEditar(<?= htmlspecialchars(json_encode($actividad)) ?>)"
                                                            class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <!-- Botón Culminar -->
                                                        <button onclick="mostrarCulminar(<?= htmlspecialchars(json_encode($actividad)) ?>)"
                                                            class="text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-50"
                                                            title="Culminar">
                                                            <i class="fas fa-check"></i>
                                                        </button>

                                                        <!-- Botón Cancelar -->
                                                        <button onclick="mostrarCancelar(<?= htmlspecialchars(json_encode($actividad)) ?>)"
                                                            class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-50"
                                                            title="Cancelar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal para Ver Detalles -->
            <div id="modalDetalles" class="fixed inset-0 items-center justify-center hidden">
                <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                    <h2 class="text-xl font-semibold mb-4">Detalles de la Actividad</h2>
                    <p><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
                    <br>
                    <p><strong>Fecha Inicio:</strong> <span id="detalleFechaInicio"></span></p>
                    <p><strong>Fecha Culminación:</strong> <span id="detalleFechaCulminacion"></span></p>
                    <p><strong>Empleado:</strong> <span id="detalleEmpleado"></span></p>
                    <p><strong>Categoría:</strong> <span id="detalleCategoria"></span></p>
                    <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                    <p id="detalleDescripcionEstado" class="hidden"><strong>Descripción del Estado:</strong> <span></span></p>
                    <button onclick="cerrarModal('modalDetalles')"
                        class="mt-4 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cerrar
                    </button>
                </div>
            </div>

            <!-- Modal para Editar -->
            <div id="modalEditar" class="fixed inset-0  items-center justify-center hidden">
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

            <!-- Modal para Culminar -->
            <div id="modalCulminar" class="fixed inset-0  items-center justify-center hidden">
                <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                    <h2 class="text-xl font-semibold mb-4">Culminar Actividad</h2>
                    <form id="formCulminar" method="POST" action="formularioCulminar.php">
                        <input type="hidden" id="culminarIdActividad" name="idActividad">

                        <label for="culminarDescripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción de Culminación</label>
                        <textarea id="culminarDescripcion" name="descripcionCulminacion" rows="4" class="w-full p-2 border rounded-md mb-4" required></textarea>

                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="cerrarModal('modalCulminar')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Culminar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal para Cancelar -->
            <div id="modalCancelar" class="fixed inset-0  items-center justify-center hidden">
                <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                    <h2 class="text-xl font-semibold mb-4">Cancelar Actividad</h2>
                    <form id="formCancelar" method="POST" action="formularioCancelar.php">
                        <input type="hidden" id="cancelarIdActividad" name="idActividad">

                        <label for="cancelarDescripcion" class="block text-sm font-medium text-gray-700 mb-1">Motivo de Cancelación</label>
                        <textarea id="cancelarDescripcion" name="descripcionCancelacion" rows="4" class="w-full p-2 border rounded-md mb-4" required></textarea>

                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="cerrarModal('modalCancelar')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Confirmar Cancelación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <!-- Scripts -->
    <script src="JS/jquery.js"></script>
    <script src="JS/datatablejquery.js"></script>
    <script src="JS/datatable-buttons.js"></script>
    <script src="JS/ajax.js"></script>
    <script src="JS/datatable-buttons-net.js"></script>
    <script src="JS/flatpicker.js"></script>
    <script src="JS/jspdf-autotable.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
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

        // DATATABLES
        $(document).ready(function() {
            $('#tablaActividades').DataTable({
                dom: '<"flex justify-between items-center mb-4"<"flex items-center"l><"flex items-center"fB>>rt<"flex justify-between items-center mt-4"<"flex items-center"i><"flex items-center"p>>',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-2"></i> Excel',
                        className: 'bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm font-medium',
                        title: 'Reporte de Actividades - <?= date("d-m-Y") ?>',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6] // Excluye la columna de acciones
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
                        className: 'bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium',
                        title: 'Reporte de Actividades - <?= date("d-m-Y") ?>',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6] // Excluye la columna de acciones
                        },
                        customize: function(doc) {

                            var logoBase64 = 'data:image/png;base64,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';
                            // Membrete con logo e información
                            doc.content.splice(0, 0, {
                                columns: [{
                                        image: logoBase64,
                                        width: 60,
                                        margin: [0, 0, 10, 0]
                                    },
                                    {
                                        text: 'Mi Empresa - Reporte de Actividades',
                                        fontSize: 16,
                                        alignment: 'left',
                                        margin: [10, 15, 0, 12]
                                    }
                                ]
                            });

                            // Pie de página
                            doc['footer'] = function(currentPage, pageCount) {
                                return {
                                    columns: [{
                                            text: 'Generado por Mi Sistema',
                                            alignment: 'left',
                                            margin: [40, 0, 0, 0]
                                        },
                                        {
                                            text: 'Página ' + currentPage.toString() + ' de ' + pageCount,
                                            alignment: 'right',
                                            margin: [0, 0, 40, 0]
                                        }
                                    ],
                                    fontSize: 9
                                }
                            };
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [7], // Columna de acciones no ordenable
                        className: 'dt-center' // Centra el contenido de la columna
                    },
                    {
                        responsivePriority: 1,
                        targets: [0, 2, 6] // Prioridad para columnas importantes en modo responsive
                    }
                ],
                initComplete: function() {
                    // Personalización adicional después de inicializar
                    $('.dt-buttons button').removeClass('dt-button');
                }
            });
        });

        // MODALES (jaja chiste)
        function mostrarDetalles(actividad) {
            document.getElementById('detalleDescripcion').textContent = actividad.descripcionActividad;
            document.getElementById('detalleFechaInicio').textContent = actividad.fechaInicio;
            document.getElementById('detalleFechaCulminacion').textContent = actividad.fechaCulminacion;
            document.getElementById('detalleEmpleado').textContent = actividad.nombreEmpleado;
            document.getElementById('detalleCategoria').textContent = actividad.categoriaActividad;
            document.getElementById('detalleEstado').textContent = actividad.estadoActividad;

            const descripcionEstado = document.getElementById('detalleDescripcionEstado');
            if (actividad.estadoActividad === 'Cancelada') {
                descripcionEstado.classList.remove('hidden');
                descripcionEstado.querySelector('span').textContent = actividad.descripcionCancelacion || 'No se proporcionó una descripción.';
            } else if (actividad.estadoActividad === 'Completada') {
                descripcionEstado.classList.remove('hidden');
                descripcionEstado.querySelector('span').textContent = actividad.descripcionCulminacion || 'No se proporcionó una descripción.';
            } else {
                descripcionEstado.classList.add('hidden');
                descripcionEstado.querySelector('span').textContent = '';
            }

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
            for (let option of empleadoSelect.options) {
                if (option.value == actividad.idEmpleado) {
                    option.selected = true;
                    break;
                }
            }

            // Seleccionar la categoría correspondiente
            const categoriaSelect = document.getElementById('editarCategoria');
            for (let option of categoriaSelect.options) {
                if (option.value == actividad.idCategoria) {
                    option.selected = true;
                    break;
                }
            }

            // Mostrar el modal
            document.getElementById('modalEditar').classList.remove('hidden');
        }

        function mostrarCulminar(actividad) {
            document.getElementById('culminarIdActividad').value = actividad.idActividad;
            document.getElementById('modalCulminar').classList.remove('hidden');
        }

        function mostrarCancelar(actividad) {
            document.getElementById('cancelarIdActividad').value = actividad.idActividad;
            document.getElementById('modalCancelar').classList.remove('hidden');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</body>

</html>