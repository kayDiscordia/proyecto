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
    $empleados = $empleadoController->obtenerEmpleadosPorDepartamento($idDepartamentoUsuario);
    

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
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

                                                    <?php
                                                        $puedeGestionar = (
                                                            ($_SESSION['id'] == $actividad['idEmpleado']) // Es el empleado asignado
                                                            || (isset($_SESSION['idRol']) && $_SESSION['idRol'] == 1) // Es administrador
                                                        );
                                                        $esAdmin = (isset($_SESSION['idRol']) && $_SESSION['idRol'] == 1);
                                                    ?>
                                                    <?php if ($actividad['estadoActividad'] !== 'Completada' && $actividad['estadoActividad'] !== 'Cancelada'): ?>
                                                        <?php if ($esAdmin): ?>
                                                            <!-- Botón Editar solo para administradores -->
                                                            <button onclick="mostrarEditar(<?= htmlspecialchars(json_encode($actividad)) ?>)"
                                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50"
                                                                title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($puedeGestionar): ?>
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
        <?php if ($empleado['estado_nombre'] === 'Activo'): ?>
            
            <option value="<?= htmlspecialchars($empleado['idEmpleado']) ?>">
                <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?>
                <?php if (!empty($empleado['nombreCargo'])): ?>
                    (<?= htmlspecialchars($empleado['nombreCargo']) ?>)
                <?php endif; ?>
            </option>
        <?php endif; ?>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="JS/flatpicker.js"></script>
    <script src="JS/jspdf-autotable.js"></script>
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