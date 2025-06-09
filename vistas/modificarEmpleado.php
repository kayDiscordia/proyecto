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
    // Verificar si es una solicitud AJAX para validar usuario
    if (isset($_POST['accion']) && $_POST['accion'] === 'verificarUsuario') {
        $usuario = trim($_POST['usuarioEmpleado']);
        $existe = $empleado->verificarUsuarioExistente($usuario, $idEmpleado);
        echo json_encode(['existe' => $existe]);
        exit;
    }

    $data = [
        'nombres' => htmlspecialchars(trim($_POST['nombres'])),
        'apellidos' => htmlspecialchars(trim($_POST['apellidos'])),
        'cedula' => htmlspecialchars(trim($_POST['cedula'])),
        'usuarioEmpleado' => htmlspecialchars(trim($_POST['usuarioEmpleado'])),
        'contrasena' => htmlspecialchars(trim($_POST['contrasena'])),
        'idEstado' => (int) $_POST['idEstado'],
        'idEmpleado' => $idEmpleado
    ];

    // Validación de campos (igual que en registrar)
    $nombreRegex = '/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+){0,3}$/';
    if (!preg_match($nombreRegex, $data['nombres']) || strlen($data['nombres']) > 25) {
        $_SESSION['error_message'] = "El nombre debe contener un máximo de 25 caracteres, hasta 3 espacios, y no puede incluir caracteres especiales.";
        header("Location: modificarEmpleado.php?idEmpleado=$idEmpleado");
        exit;
    }

    if (!preg_match($nombreRegex, $data['apellidos']) || strlen($data['apellidos']) > 25) {
        $_SESSION['error_message'] = "El apellido debe contener un máximo de 25 caracteres, hasta 3 espacios, y no puede incluir caracteres especiales.";
        header("Location: modificarEmpleado.php?idEmpleado=$idEmpleado");
        exit;
    }

    if (strlen($data['cedula']) > 8 || !ctype_digit($data['cedula'])) {
        $_SESSION['error_message'] = "La cédula solo puede contener números y tener un máximo de 8 caracteres.";
        header("Location: modificarEmpleado.php?idEmpleado=$idEmpleado");
        exit;
    }

    // Verificar si el usuario ya existe (excluyendo al empleado actual)
    if ($empleado->verificarUsuarioExistente($data['usuarioEmpleado'], $idEmpleado)) {
        $_SESSION['error_message'] = "El nombre de usuario ya está en uso por otro empleado.";
        header("Location: modificarEmpleado.php?idEmpleado=$idEmpleado");
        exit;
    }

    try {
        $empleado->actualizarEmpleado($data);
        $_SESSION['success_message'] = "Empleado modificado correctamente.";
        header("Location: verEmpleado.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al modificar: " . $e->getMessage();
        header("Location: modificarEmpleado.php?idEmpleado=$idEmpleado");
        exit;
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
    <script src="CSS/output.css"></script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $_SESSION['error_message'] ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-[#89C0E9] px-6 py-4">
                    <h1 class="text-xl font-semibold text-white">Modificar Empleado</h1>
                </div>
                
                <div class="p-6">
                    <form method="POST" class="space-y-4" id="employeeForm">
                        <!-- Campos en 2 columnas -->
                        <div class="grid grid-cols-1 gap-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                                    <input type="text" name="nombres" value="<?= htmlspecialchars($empleadoData['nombres'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 25); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                                    <input type="text" name="apellidos" value="<?= htmlspecialchars($empleadoData['apellidos'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 25); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                                <input type="text" name="cedula" value="<?= htmlspecialchars($empleadoData['cedula'] ?? '') ?>" readonly
                                       class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md text-sm"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
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
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                       id="usuarioEmpleado"
                                       required>
                                <p id="mensajeUsuarioError" class="text-red-500 text-xs mt-1" style="display: none;"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                                <input type="password" name="contrasena" value="<?= htmlspecialchars($empleadoData['contrasena'] ?? '') ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                       required>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="verEmpleado.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">
                                Cancelar
                            </a>
                            <button type="submit" id="btnGuardar" class="px-4 py-2 bg-[#89C0E9] text-white rounded-md text-sm hover:bg-blue-700">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('usuarioEmpleado').addEventListener('input', verificarUsuario);

        function verificarUsuario() {
            const usuario = document.getElementById('usuarioEmpleado').value.trim();
            const formData = new FormData();
            formData.append('usuarioEmpleado', usuario);
            formData.append('accion', 'verificarUsuario');
            formData.append('idEmpleado', <?= $idEmpleado ?>);

            fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const mensajeUsuarioError = document.getElementById('mensajeUsuarioError');
                    const btnGuardar = document.getElementById('btnGuardar');

                    if (data.existe) {
                        mensajeUsuarioError.style.display = 'block';
                        mensajeUsuarioError.textContent = 'Ese nombre de usuario ya está ocupado.';
                        btnGuardar.disabled = true;
                    } else {
                        mensajeUsuarioError.style.display = 'none';
                        btnGuardar.disabled = false;
                    }
                })
                .catch(error => console.error('Error en la solicitud:', error));
        }
    </script>
</body>
</html>