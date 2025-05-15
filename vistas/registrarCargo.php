<?php
session_start();
require '../login/functionLogin.php';
$select = new Login();
if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('location: ../index.php');
    exit;
}

require_once '../controladores/controladorCargos.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$mensaje = isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : null;
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCargo = trim($_POST['nombreCargo']);
    $limiteActividades = intval($_POST['limiteActividades']);
    $descripcionCargo = trim($_POST['descripcionCargo']);

    // Validación de datos
    $nombreRegex = '/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/';

    if (!preg_match($nombreRegex, $nombreCargo) || strlen($nombreCargo) > 50) {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCargo.php?error=' . urlencode('El nombre del cargo solo puede contener letras, espacios y debe tener un máximo de 50 caracteres.'));
        exit;
    }

    if (!preg_match($nombreRegex, $descripcionCargo) || strlen($descripcionCargo) > 255) {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCargo.php?error=' . urlencode('La descripción del cargo solo puede contener letras, espacios y debe tener un máximo de 255 caracteres.'));
        exit;
    }

    $controlador = new controladorCargos();
    $result = json_decode($controlador->insertarCargo($nombreCargo, $limiteActividades, $descripcionCargo), true);

    if ($result['status'] === 'success') {
        header('Location: registrarCargo.php?mensaje=' . urlencode($result['message']));
        exit;
    } else {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCargo.php?error=' . urlencode($result['message']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cargo</title>
    <link rel="stylesheet" href="CSS/output.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <?php include 'modulos/sidebar.php'; ?>
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4 text-center">Registrar Cargo</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $mensaje; ?></span>
                </div>
            <?php endif; ?>

            <div class="w-full max-w-2xl mx-auto">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <form action="" method="POST" id="cargoForm" class="space-y-4">
                        <!-- Nombre del cargo -->
                        <div class="space-y-2">
                            <label for="nombreCargo" class="block text-sm font-medium text-gray-700">Nombre del Cargo</label>
                            <input type="text" id="nombreCargo" name="nombreCargo" maxlength="50"
                                value="<?php echo isset($formData['nombreCargo']) ? htmlspecialchars($formData['nombreCargo']) : ''; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 50); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)"
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                title="El nombre solo puede contener letras y espacios (máximo 50 caracteres)"
                                placeholder="Ingrese el nombre del cargo" 
                                required>
                        </div>

                        <!-- Límite de actividades -->
                        <div class="space-y-2">
                            <label for="limiteActividades" class="block text-sm font-medium text-gray-700">Límite de Actividades</label>
                            <input type="number" id="limiteActividades" name="limiteActividades" min="1" max="99"
                                value="<?php echo isset($formData['limiteActividades']) ? htmlspecialchars($formData['limiteActividades']) : ''; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2)"
                                placeholder="Ingrese el límite de actividades" 
                                required>
                        </div>

                        <!-- Descripción del cargo -->
                        <div class="space-y-2">
                            <label for="descripcionCargo" class="block text-sm font-medium text-gray-700">Descripción del Cargo</label>
                            <textarea id="descripcionCargo" name="descripcionCargo" rows="4" maxlength="255"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').slice(0, 255); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)"
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                title="La descripción solo puede contener letras y espacios (máximo 255 caracteres)"
                                placeholder="Ingrese la descripción del cargo"><?php echo isset($formData['descripcionCargo']) ? htmlspecialchars($formData['descripcionCargo']) : ''; ?></textarea>
                        </div>
                        <br>
                        <div class="flex justify-between">
                            <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                Salir
                            </a>
                            <button type="submit" id="btnRegistrar" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Cargo
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

        // Validación en el cliente
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('cargoForm');
            const btnRegistrar = document.getElementById('btnRegistrar');
            
            form.addEventListener('submit', function(event) {
                const nombreCargo = document.getElementById('nombreCargo').value.trim();
                const descripcionCargo = document.getElementById('descripcionCargo').value.trim();
                const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

                if (!nombreRegex.test(nombreCargo) || nombreCargo.length > 50) {
                    alert('El nombre del cargo solo puede contener letras, espacios y debe tener un máximo de 50 caracteres.');
                    event.preventDefault();
                    return;
                }

                if (!nombreRegex.test(descripcionCargo) || descripcionCargo.length > 255) {
                    alert('La descripción del cargo solo puede contener letras, espacios y debe tener un máximo de 255 caracteres.');
                    event.preventDefault();
                    return;
                }
            });

            // Validación en tiempo real para el límite de actividades
            const limiteActividades = document.getElementById('limiteActividades');
            limiteActividades.addEventListener('input', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > 99) this.value = 99;
            });
        });
    </script>
</body>
</html>