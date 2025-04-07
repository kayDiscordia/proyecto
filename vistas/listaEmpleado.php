<?php
session_start();
// Incluir el archivo de la clase Empleado
require_once '../controladores/controladorEmpleado.php';
require_once '../modelos/Database.php';
require_once '../login/functionLogin.php';

// Verificar si el usuario está logueado
 // Asegúrate de iniciar la sesión
$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('Location: index.php');
    exit();
}

// Crear una instancia de la clase Empleado
$empleado = new controladorEmpleado();

try {
    // Obtener la lista de empleados
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
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer>
    class EmpleadoSearch {
        constructor(inputId, tableId) {
            this.input = document.getElementById(inputId);
            this.table = document.getElementById(tableId);
            this.rows = this.table.querySelectorAll('tbody tr');
            this.init();
        }

        init() {
            this.input.addEventListener('keyup', () => this.buscarEmpleado());
        }

        buscarEmpleado() {
            const query = this.input.value.toLowerCase();
            this.rows.forEach(row => {
                const nombres = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const apellidos = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                if (nombres.includes(query) || apellidos.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }

    // Inicializar la funcionalidad de búsqueda
    document.addEventListener('DOMContentLoaded', () => {
        new EmpleadoSearch('searchInput', 'employeeTable');
    });
</script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>
        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            <h2 class="text-2xl font-semibold mb-4">Lista de Empleados</h2>
            <div class="mb-4">
                <input type="text" id="searchInput" onkeyup="buscarEmpleado()" placeholder="Buscar empleado por nombre o apellido" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <table id="employeeTable" class="w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700 hidden">ID</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Nombres</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Apellidos</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Cédula</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Cargo</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Departamento</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($listaEmpleados)) { ?>
                            <tr>
                                <td colspan="7" class="p-3 text-center text-gray-700">No hay empleados registrados.</td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($listaEmpleados as $datos) { ?>
                                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='verReportes.php?idEmpleado=<?= $datos['idEmpleado'] ?>'">
                                    <td class="p-3 text-sm text-gray-700 hidden"><?= htmlspecialchars($datos['idEmpleado'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['nombres'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['apellidos'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cedula'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cargo_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['departamento_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <a href="verReportes.php?idEmpleado=<?= $datos['idEmpleado'] ?>" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-alt"></i> Ver Reporte </a>
                                       
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
                <br>
                 <div class="flex justify-between col-span-2">
                    <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Salir
                    </a>
            </div>
        </main>
    </div>
</body>
</html>