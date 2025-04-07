<?php

// Incluir archivos necesarios
require_once '../login/conexion.php';
require_once '../login/functionLogin.php';
require_once '../login/claseSelect.php'; // Asegúrate de que el archivo tenga la clase SelectEmpleado
require_once '../controladores/controladorEmpleado.php';

// Crear una instancia de la clase Conexion
$conexion = new Conexion();

// Crear una instancia de la clase SelectEmpleado
$selectEmpleado = new SelectEmpleado($conexion->conexion);

// Verificar si el usuario está logueado
if (isset($_SESSION['id'])) {
    $user = $selectEmpleado->SelectuserByuser($_SESSION['id']);
} else {
    header('location: ../index.php');
    exit();
}
$controlador = new controladorEmpleado();

// Manejar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'verificarCedula') {
    $controlador->verificarCedulaAjax();
    exit(); // Detener la ejecución para evitar cargar el resto de la página
}

$controlador->insercionEmpleado();

// Obtener los cargos y departamentos
$cargos = $controlador->obtenerCargos();
$departamentos = $controlador->obtenerDepartamentos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);

    // Validar cédula
    if (strlen($cedula) > 8) {
        die('La cédula no puede tener más de 8 caracteres.');
    }

    // Validar nombres y apellidos
    $nombreRegex = '/^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)+$/';
    if (!preg_match($nombreRegex, $nombres)) {
        die('El nombre debe tener al menos 2 palabras y cada palabra debe comenzar con mayúscula.');
    }

    if (!preg_match($nombreRegex, $apellidos)) {
        die('El apellido debe tener al menos 2 palabras y cada palabra debe comenzar con mayúscula.');
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
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4 text-center">Registro de Empleados</h1>
            <div class="w-full max-w-2xl mx-auto">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <form action="" method="POST" id="employeeForm" class="space-y-4">
                        <!-- Campos del formulario -->
                        <div class="space-y-2">
                            <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula</label>
                            <input type="text" id="cedula" name="cedula" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)" 
                                onblur="verificarCedula()" 
                                maxlength="8" 
                                required>
                            <p id="mensajeError" style="color: red; display: none;"></p>
                        </div>

                        <div class="space-y-2">
                            <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input type="text" id="nombres" name="nombres" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ' ]/g, '').replace(/\s{2,}/g, ' ').trimStart().replace(/(.*\s.*)\s/, '$1')" 
                                pattern="^[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+$" 
                                title="Debe contener dos palabras, cada una comenzando con mayúscula, y no se permiten caracteres especiales (excepto acentos y apóstrofes)." 
                                required>
                            <p id="nombreError" style="color: red; display: none;"></p>
                        </div>

                        <div class="space-y-2">
                            <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ' ]/g, '').replace(/\s{2,}/g, ' ').trimStart().replace(/(.*\s.*)\s/, '$1')" 
                                pattern="^[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+$" 
                                title="Debe contener dos palabras, cada una comenzando con mayúscula, y no se permiten caracteres especiales (excepto acentos y apóstrofes)." 
                                required>
                            <p id="apellidoError" style="color: red; display: none;"></p>
                        </div>

                        <!-- Select para cargos -->
                        <div class="space-y-2">
                            <label for="idCargo" class="block text-sm font-medium text-gray-700">Cargo</label>
                            <select id="idCargo" name="idCargo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un cargo</option>
                                <?php foreach ($cargos as $cargo): ?>
                                    <option value="<?php echo $cargo['idCargo']; ?>">
                                        <?php echo $cargo['nombreCargo']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Select para departamentos -->
                        <div class="space-y-2">
                            <label for="idDepartamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                            <select id="idDepartamento" name="idDepartamento" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['idDepartamentos']; ?>">
                                        <?php echo $departamento['nombreDepartamentos']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Resto del formulario -->
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
    function verificarCedula() {
        const cedula = document.getElementById('cedula').value;

        // Crear un objeto FormData para enviar la cédula
        const formData = new FormData();
        formData.append('cedula', cedula);
        formData.append('accion', 'verificarCedula'); // Identificador para la acción

        // Realizar la solicitud AJAX
        fetch('', { // La solicitud se envía al mismo archivo PHP
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


        // Validar que solo contenga números
        const cedulaRegex = /^[0-9]+$/;
        if (!cedulaRegex.test(cedula)) {
            mensajeError.style.display = 'block';
            mensajeError.textContent = 'La cédula solo puede contener números.';
            btnRegistrar.disabled = true;
            return;
        }

        // Validar que no tenga más de 8 caracteres
        if (cedula.length > 8) {
            mensajeError.style.display = 'block';
            mensajeError.textContent = 'La cédula no puede tener más de 8 caracteres.';
            btnRegistrar.disabled = true;
            return;
        }

        mensajeError.style.display = 'none';
        btnRegistrar.disabled = false;
    }

    function validarNombresApellidos() {
        const nombres = document.getElementById('nombres').value;
        const apellidos = document.getElementById('apellidos').value;

        const nombreError = document.getElementById('nombreError');
        const apellidoError = document.getElementById('apellidoError');

        // Validar que no contengan caracteres especiales (excepto acentos y apóstrofes)
        const nombreRegex = /^[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+(\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+)+$/;
        const apellidoRegex = /^[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+(\s[A-ZÁÉÍÓÚÑ][a-záéíóúñ']+)+$/;

        if (!nombreRegex.test(nombres)) {
            nombreError.style.display = 'block';
            nombreError.textContent = 'El nombre debe tener al menos 2 palabras, comenzar con mayúscula y no contener caracteres especiales.';
            document.getElementById('btnRegistrar').disabled = true;
        } else {
            nombreError.style.display = 'none';
        }

        if (!apellidoRegex.test(apellidos)) {
            apellidoError.style.display = 'block';
            apellidoError.textContent = 'El apellido debe tener al menos 2 palabras, comenzar con mayúscula y no contener caracteres especiales.';
            document.getElementById('btnRegistrar').disabled = true;
        } else {
            apellidoError.style.display = 'none';
        }

        // Habilitar el botón si ambos campos son válidos
        if (nombreRegex.test(nombres) && apellidoRegex.test(apellidos)) {
            document.getElementById('btnRegistrar').disabled = false;
        }
    }
</script>
</body>
</html>