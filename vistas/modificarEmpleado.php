<?php
require_once '../modelos/modeloEmpleado.php';

$empleado = new modeloEmpleado();

session_start();
if (!isset($_SESSION['id'])) {
    header('location: ../index.php');
    exit();
}

if (isset($_GET['idEmpleado'])) {
    $idEmpleado = (int) $_GET['idEmpleado'];

    try {
        $empleadoData = (array) $empleado->obtenerEmpleadoPorId($idEmpleado);
        $estadosDisponibles = $empleado->obtenerEstadosEmpleados();
    } catch (Exception $e) {
        echo "<p class='text-red-500 p-4'>Error: " . $e->getMessage() . "</p>";
        exit;
    }
} else {
    echo "<p class='text-red-500 p-4'>ID de empleado no proporcionado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombres' => htmlspecialchars(trim($_POST['nombres'])),
        'apellidos' => htmlspecialchars(trim($_POST['apellidos'])),
        'cedula' => htmlspecialchars(trim($_POST['cedula'])),
        'usuarioEmpleado' => htmlspecialchars(trim($_POST['usuarioEmpleado'])),
        'contrasena' => htmlspecialchars(trim($_POST['contrasena'])),
        'idEstado' => (int) $_POST['idEstado'],
        'idEmpleado' => $idEmpleado
    ];

    try {
        $empleado->actualizarEmpleado($data);
        $_SESSION['success_message'] = "Empleado modificado correctamente.";
        header("Location: verEmpleado.php");
        exit;
    } catch (Exception $e) {
        echo "<p class='text-red-500 p-4'>Error al modificar: " . $e->getMessage() . "</p>";
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-[#89C0E9] px-6 py-4">
                    <h1 class="text-xl font-semibold text-white">Modificar Empleado</h1>
                </div>
                
                <div class="p-6">
                    <form method="POST" class="space-y-4">
                        <!-- Campos en 2 columnas -->
                        <div class="grid grid-cols-1 gap-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                                    <input type="text" name="nombres" value="<?= htmlspecialchars($empleadoData['nombres'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                                    <input type="text" name="apellidos" value="<?= htmlspecialchars($empleadoData['apellidos'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                                <input type="text" name="cedula" value="<?= htmlspecialchars($empleadoData['cedula'] ?? '') ?>" readonly
                                       class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="idEstado" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <?php foreach ($estadosDisponibles as $estado): ?>
                                        <option value="<?= $estado['idEstado'] ?>" 
                                            <?= ($estado['idEstado'] == ($empleadoData['idEstado'] ?? '')) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($estado['nombreEstado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                                <input type="text" name="usuarioEmpleado" value="<?= htmlspecialchars($empleadoData['usuarioEmpleado'] ?? '') ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                                <input type="password" name="contrasena" value="<?= htmlspecialchars($empleadoData['contrasena'] ?? '') ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="verEmpleado.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-[#89C0E9] text-white rounded-md text-sm hover:bg-blue-700">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>