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
                            <input type="text" id="nombreCargo" name="nombreCargo" maxlength="255"
                                value="<?php echo isset($formData['nombreCargo']) ? htmlspecialchars($formData['nombreCargo']) : ''; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Ingrese el nombre del cargo" required>
                        </div>
                        <!-- Límite de actividades -->
                        <div class="space-y-2">
                            <label for="limiteActividades" class="block text-sm font-medium text-gray-700">Límite de Actividades</label>
                            <input type="number" id="limiteActividades" name="limiteActividades" min="1" max="99"
                                value="<?php echo isset($formData['limiteActividades']) ? htmlspecialchars($formData['limiteActividades']) : ''; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Ingrese el límite de actividades" required>
                        </div>
                        <!-- Descripción del cargo -->
                        <div class="space-y-2">
                            <label for="descripcionCargo" class="block text-sm font-medium text-gray-700">Descripción del Cargo</label>
                            <textarea id="descripcionCargo" name="descripcionCargo" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Ingrese la descripción del cargo"><?php echo isset($formData['descripcionCargo']) ? htmlspecialchars($formData['descripcionCargo']) : ''; ?></textarea>
                        </div>
                        <br>
                        <div class="flex justify-between">
                            <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                Salir
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Cargo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>