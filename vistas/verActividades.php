<?php
session_start();
// Incluir el archivo de la clase Empleado
require_once '../controladores/controladorActividad.php';
require_once '../modelos/Database.php';
require_once '../login/functionLogin.php';

// Verificar si el usuario está logueado
$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('location: index.php');
    exit();
}

try {
    // Crear una instancia de Database y obtener la conexión
    $database = new Database();
    $conn = $database->getConnection();

    // Crear una instancia de Actividad
    $actividad = new controladorActividad($conn);

    // Obtener la lista de actividades
    $listaActividades = $actividad->obtenerActividades();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
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
            <h2 class="text-2xl font-semibold mb-4">Lista de Actividades</h2>
            <div class="bg-white p-6 rounded-lg shadow">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">N°</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Categoría</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Descripción</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Inicio</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Fecha Culminación</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Empleado</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                            <th class="p-3 text-center text-sm font-semibold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($listaActividades)) { ?>
                            <tr>
                                <td colspan="8" class="p-3 text-center text-gray-700">No hay actividades registradas.</td>
                            </tr>
                        <?php } else { ?>
                            <?php $contador = 1; ?>
                            <?php foreach ($listaActividades as $actividad) { ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($contador++ ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['categoriaActividad'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['descripcionActividad'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['fechaInicio'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['fechaCulminacion'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($actividad['nombreEmpleado'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <span class="<?= $actividad['estadoActividad'] == 'En progreso' ? 'bg-yellow-100 text-yellow-800' : ($actividad['estadoActividad'] == 'Cancelada' ? 'bg-red-100 text-red-800' : ($actividad['estadoActividad'] == 'Completada' ? 'bg-green-100 text-green-800' : '')) ?> px-2 py-1 rounded-lg">
                                            <?= htmlspecialchars($actividad['estadoActividad'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-700 flex space-x-2 justify-center"> <!-- Centrado -->
                                        <?php if ($actividad['estadoActividad'] !== 'Completada' && $actividad['estadoActividad'] !== 'Cancelada') { ?>
                                            <!-- Botón para Cancelar -->
                                            <a href="formularioCancelar.php?idActividad=<?= $actividad['idActividad'] ?>" 
                                               class="text-red-600 hover:text-red-800 bg-red-100 px-3 py-1 rounded-md flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Cancelar
                                            </a>
                                            <!-- Botón para Culminar -->
                                            <a href="formularioCulminar.php?idActividad=<?= $actividad['idActividad'] ?>" 
                                               class="text-green-600 hover:text-green-800 bg-green-100 px-3 py-1 rounded-md flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Culminar
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
                <br>
                <div class="flex justify-between">
                    <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Salir
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>