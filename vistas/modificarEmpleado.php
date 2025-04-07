<?php

// Incluir archivos necesarios
require_once '../modelos/modeloEmpleado.php';

// Crear una instancia de la clase Empleado
$empleado = new modeloEmpleado();

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['id'])) {
    header('location: ../index.php');
    exit();
}

// Verificar si se ha enviado un ID de empleado
if (isset($_GET['idEmpleado'])) {
    $idEmpleado = (int) $_GET['idEmpleado'];

    try {
        // Consultar los datos del empleado
        $empleadoData = $empleado->obtenerEmpleadoPorId($idEmpleado);
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
        exit;
    }

} else {
    echo "<p>ID de empleado no proporcionado.</p>";
    exit;
}

// Procesar la modificación al enviar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombres' => htmlspecialchars(trim($_POST['nombres'])),
        'apellidos' => htmlspecialchars(trim($_POST['apellidos'])),
    //    'cargo' => htmlspecialchars(trim($_POST['cargo'])), 
        'cedula' => htmlspecialchars(trim($_POST['cedula'])),
        'usuarioEmpleado' => htmlspecialchars(trim($_POST['usuarioEmpleado'])),
        'contrasena' => htmlspecialchars(trim($_POST['contrasena'])),
        'idEmpleado' => $idEmpleado
    ];

    try {
        $empleado->actualizarEmpleado($data);
        echo "<p>Empleado modificado correctamente.</p>";
        header("Location: verEmpleado.php");
        exit;
    } catch (Exception $e) {
        echo "<p>Error al modificar el empleado: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Empleado</title>
    <link rel="stylesheet" href="CSS/output.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <main class="flex-1 p-6 overflow-y-auto">
        <h1 class="text-2xl font-semibold mb-4 text-center">Modificar Empleado</h1>
        <div class="w-full max-w-2xl mx-auto">
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <form action="" method="POST" id="employeeForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($empleadoData->nombres); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="space-y-2">
                            <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($empleadoData->apellidos); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula</label>
                        <input readonly type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($empleadoData->cedula); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                   <div class="space-y-2">
<!--                   <label for="idCargo" class="block text-sm font-medium text-gray-700">Cargo</label>
         <select id="idCargo" name="idCargo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un cargo</option>
                                <?php // foreach ($cargos as $cargo): ?>
                                    <option value="<?php // echo $cargo['idCargo']; ?>">
                                        <?php // echo $cargo['nombreCargo']; ?>
                                    </option>
                                <?php // endforeach; ?>
                            </select>               
Arreglar el campo de Cargos--->     </div>
                    <div class="space-y-2">
                        <label for="usuarioEmpleado" class="block text-sm font-medium text-gray-700">Usuario</label>
                        <input type="text" id="usuarioEmpleado" name="usuarioEmpleado" value="<?= htmlspecialchars($empleadoData->usuarioEmpleado); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="space-y-2">
                        <label for="contrasena" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" value="<?= htmlspecialchars($empleadoData->contrasena); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex justify-between">
                        <button onclick="location.href='verEmpleado.php'" type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                            Salir
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Modificar Empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>