<?php
require_once '../login/conexion.php';
require_once '../login/functionLogin.php';
require_once '../login/claseSelect.php';
require_once '../controladores/controladorEmpleado.php';

$conexion = new Conexion();
$selectEmpleado = new SelectEmpleado($conexion->conexion);

if (isset($_SESSION['id'])) {
    $user = $selectEmpleado->SelectuserByuser($_SESSION['id']);
} else {
    header('location: ../index.php');
    exit();
}
$controlador = new controladorEmpleado();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'verificarCedula') {
    $controlador->verificarCedulaAjax();
    exit();
}

$controlador->insercionEmpleado();
$cargos = $controlador->obtenerCargos();
$departamentos = $controlador->obtenerDepartamentos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);

    if (strlen($cedula) > 8 || !ctype_digit($cedula)) {
        die('La cédula solo puede contener números y tener un máximo de 8 caracteres.');
    }

    $nombreRegex = '/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+){0,3}$/';
    if (!preg_match($nombreRegex, $nombres) || strlen($nombres) > 25) {
        die('El nombre debe contener un máximo de 25 caracteres, hasta 3 espacios, y no puede incluir caracteres especiales.');
    }

    if (!preg_match($nombreRegex, $apellidos) || strlen($apellidos) > 25) {
        die('El apellido debe contener un máximo de 25 caracteres, hasta 3 espacios, y no puede incluir caracteres especiales.');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="CSS/output.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <?php include 'modulos/sidebar.php' ?>
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4 text-center">Registro de Empleados</h1>
            <div class="w-full max-w-2xl mx-auto">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <form action="" method="POST" id="employeeForm" class="space-y-4">
                        <!-- Select para tipo de documento -->
                        <div class="space-y-2">
                            <label for="tipoDocumento" class="block text-sm font-medium text-gray-700">Tipo de Documento</label>
                            <select id="tipoDocumento" name="tipoDocumento" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="cedula">Cédula</option>
                                <option value="pasaporte">Pasaporte</option>
                            </select>
                        </div>

                        <!-- Campo de cédula -->
                        <div class="space-y-2">
                            <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula</label>
                            <input type="text" id="cedula" name="cedula" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)" 
                                maxlength="8" 
                                onblur="verificarCedula()" 
                                required>
                            <p id="mensajeError" style="color: red; display: none;"></p>
                        </div>

                        <!-- Campo de nombres  -->
                        <div class="space-y-2">
                            <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input type="text" id="nombres" name="nombres" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 25); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+){0,3}$" 
                                title="Debe contener un máximo de 25 caracteres, sin caracteres especiales, y hasta 3 espacios." 
                                required>
                        </div>

                        <!-- Campo de apellidos  -->
                        <div class="space-y-2">
                            <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 25); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+){0,3}$" 
                                title="Debe contener un máximo de 25 caracteres, sin caracteres especiales, y hasta 3 espacios." 
                                required>
                        </div>

                        <!-- Campos de cargo -->
                        <div class="space-y-2">
                            <label for="idCargo" class="block text-sm font-medium text-gray-700">Cargo</label>
                            <select id="idCargo" name="idCargo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un cargo</option>
                                <?php foreach ($cargos as $cargo): ?>
                                    <option value="<?php echo $cargo['idCargo']; ?>"><?php echo $cargo['nombreCargo']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Campos de departamento -->                
                        <div class="space-y-2">
                            <label for="idDepartamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                            <select id="idDepartamento" name="idDepartamento" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['idDepartamentos']; ?>"><?php echo $departamento['nombreDepartamentos']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Campos de usuario y contraseña -->
                        <div class="space-y-2">
                            <label for="usuarioEmpleado" class="block text-sm font-medium text-gray-700">Usuario de acceso para el empleado</label>
                            <input type="text" id="usuarioEmpleado" name="usuarioEmpleado" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="contrasena" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input type="password" id="contrasena" name="contrasena" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="flex justify-between">
                            <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                Salir
                            </a>
                            <button id="btnRegistrar" type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Empleado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
    // capitalizar automáticamente
    function capitalizeFirstLetters(input) {
        let words = input.value.split(' ');
        for (let i = 0; i < words.length; i++) {
            if (words[i].length > 0) {
                words[i] = words[i][0].toUpperCase() + words[i].substring(1).toLowerCase();
            }
        }
        input.value = words.join(' ');
    }

    // verificarCedula 
    function verificarCedula() {
        const cedula = document.getElementById('cedula').value;
        const formData = new FormData();
        formData.append('cedula', cedula);
        formData.append('accion', 'verificarCedula');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const mensajeError = document.getElementById('mensajeError');
            const btnRegistrar = document.getElementById('btnRegistrar');

            if (data.existe) {
                mensajeError.style.display = 'block';
                mensajeError.textContent = 'La cédula ya está registrada.';
                btnRegistrar.disabled = true;
            } else {
                mensajeError.style.display = 'none';
                btnRegistrar.disabled = false;
            }
        })
        .catch(error => console.error('Error en la solicitud:', error));

        const cedulaRegex = /^[0-9]+$/;
        if (!cedulaRegex.test(cedula)) {
            mensajeError.style.display = 'block';
            mensajeError.textContent = 'La cédula solo puede contener números.';
            btnRegistrar.disabled = true;
            return;
        }

        if (cedula.length > 8) {
            mensajeError.style.display = 'block';
            mensajeError.textContent = 'La cédula no puede tener más de 8 caracteres.';
            btnRegistrar.disabled = true;
            return;
        }

        mensajeError.style.display = 'none';
        btnRegistrar.disabled = false;
    }
    </script>
</body>
</html>