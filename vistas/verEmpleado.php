<?php
require_once '../controladores/controladorEmpleado.php';
require_once '../modelos/Database.php';
require_once '../login/functionLogin.php';

$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('location: index.php');
}

$empleado = new controladorEmpleado();

try {
    $listaEmpleados = $empleado->obtenerEmpleados();
} catch (Exception $e) {
    die("Error al obtener empleados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>

        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            <h2 class="text-2xl font-semibold mb-4">Lista de Empleados</h2>
            <div class="bg-white p-6 rounded-lg shadow">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Nombres</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Apellidos</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Cédula</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Cargo</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Departamento</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($listaEmpleados)) { ?>
                            <tr>
                                <td colspan="7" class="p-3 text-center text-gray-700">No hay empleados registrados.</td>
                            </tr>
                        <?php } else { 
                            // Ordenar por estado: Activo(1) -> Permisado(2) -> Suspendido(3)
                            usort($listaEmpleados, function($a, $b) {
                                $orden = ['Activo' => 1, 'Permisado' => 2, 'Suspendido' => 3];
                                $aOrden = $orden[$a['estado_nombre']] ?? 4;
                                $bOrden = $orden[$b['estado_nombre']] ?? 4;
                                return $aOrden - $bOrden;
                            });
                            
                            foreach ($listaEmpleados as $datos) { ?>
                                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='modificarEmpleado.php?idEmpleado=<?= $datos['idEmpleado'] ?>'">
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['nombres'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['apellidos'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cedula'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cargo_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['departamento_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?= 
                                                ($datos['estado_nombre'] == 'Activo') ? 'bg-green-100 text-green-800' : 
                                                (($datos['estado_nombre'] == 'Suspendido') ? 'bg-red-100 text-red-800' : 
                                                'bg-yellow-100 text-yellow-800') 
                                            ?>">
                                            <?= htmlspecialchars($datos['estado_nombre'] ?? 'No definido') ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <a href="modificarEmpleado.php?idEmpleado=<?= $datos['idEmpleado'] ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-6.036a2.5 2.5 0 113.536 3.536L7.5 20.5H3v-4.5L16.732 3.732z" />
                                            </svg>
                                            <span class="text-sm font-medium">Modificar</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php } 
                        } ?>
                    </tbody>
                </table>
                <br>
                <div class="flex justify-between col-span-2">
                    <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Salir
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>