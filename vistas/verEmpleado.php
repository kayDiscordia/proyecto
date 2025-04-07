<?php
// Incluir el archivo de la clase Empleado
require_once '../controladores/controladorEmpleado.php';
require_once '../modelos/Database.php';
require_once '../login/functionLogin.php';
    $select = new Login();
    if (isset($_SESSION['id'])) {
        $user = $select->SelectuserByuser($_SESSION['id']);
    }
    else
    {
        header('location: index.php');
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
<!--                        <th class="p-3 text-left text-sm font-semibold text-gray-700">ID</th> -->
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
                            <?php foreach ($listaEmpleados as $key=>$datos) { ?>
                                <tr class="hover:bg-gray-50"> 
<!--                                <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($key +1 ?? '') ?></td> -->
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['nombres'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['apellidos'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cedula'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['cargo_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($datos['departamento_nombre'] ?? '') ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <a href="modificarEmpleado.php?idEmpleado=<?= $datos['idEmpleado'] ?>" class="text-blue-600 hover:text-blue-800">Modificar</a>
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