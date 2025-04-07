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

try {
    // Obtener datos del empleado y sus actividades
    $datos = $empleadoController->obtenerActividadesPorEmpleado($idEmpleado);
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
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actividades Asignadas</h3>
                
                <?php if (empty($actividades)): ?>
                    <div class="text-center py-8">
                        <!-- cara insana -->
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500">No hay actividades registradas para este empleado.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
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
</body>
</html>