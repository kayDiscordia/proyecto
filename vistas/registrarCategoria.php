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

require_once '../controladores/controladorCategoria.php';

// Obtener departamentos para mostrar el nombre
require_once '../controladores/controladorEmpleado.php';
$controladorEmpleado = new controladorEmpleado();
$departamentos = $controladorEmpleado->obtenerDepartamentos();

$idDepartamento = isset($_SESSION['idDepartamento']) ? $_SESSION['idDepartamento'] : '';
$nombreDepartamento = 'No asignado';
if ($idDepartamento && $departamentos) {
    foreach ($departamentos as $dep) {
        if ($dep['idDepartamentos'] == $idDepartamento) {
            $nombreDepartamento = $dep['nombreDepartamentos'];
            break;
        }
    }
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$mensaje = isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : null;
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCategoria = trim($_POST['nombreCategoria']);
    $descripcionCategoria = trim($_POST['descripcionCategoria']);

    // Validación de datos
    $nombreRegex = '/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]+$/';

    if (!preg_match($nombreRegex, $nombreCategoria) || strlen($nombreCategoria) > 255) {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCategoria.php?error=' . urlencode('El nombre de la categoría solo puede contener letras, números, espacios, guiones y debe tener un máximo de 255 caracteres.'));
        exit;
    }

    if (!empty($descripcionCategoria) && (!preg_match($nombreRegex, $descripcionCategoria) || strlen($descripcionCategoria) > 500)) {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCategoria.php?error=' . urlencode('La descripción solo puede contener letras, números, espacios, guiones y debe tener un máximo de 500 caracteres.'));
        exit;
    }

    // El idDepartamento se toma de la sesión, no del formulario
    $controlador = new controladorCategoria();
    $result = json_decode($controlador->insertarCategoria($nombreCategoria, $idDepartamento, $descripcionCategoria), true);

    if ($result['status'] === 'success') {
        header('Location: registrarCategoria.php?mensaje=' . urlencode($result['message']));
        exit;
    } else {
        $_SESSION['form_data'] = $_POST;
        header('Location: registrarCategoria.php?error=' . urlencode($result['message']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Categoría</title>
    <link rel="stylesheet" href="CSS/output.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4 text-center">Registrar Categoría</h1>

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
                    <form action="" method="POST" id="categoriaForm" class="space-y-4">
                        <!-- Departamento (solo lectura) -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Departamento</label>
                            <input type="text" readonly
                                value="<?php echo htmlspecialchars($nombreDepartamento); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <input type="hidden" name="idDepartamento" value="<?php echo htmlspecialchars($idDepartamento); ?>">
                        </div>
                        
                        <!-- Nombre de la categoría -->
                        <div class="space-y-2">
                            <label for="nombreCategoria" class="block text-sm font-medium text-gray-700">Nombre de la Categoría</label>
                            <input type="text" id="nombreCategoria" name="nombreCategoria" maxlength="255"
                                value="<?php echo isset($formData['nombreCategoria']) ? htmlspecialchars($formData['nombreCategoria']) : ''; ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]/g, '').replace(/\s+/g, ' ').slice(0, 255); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)"
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]+$"
                                title="El nombre solo puede contener letras, números, espacios y guiones (máximo 255 caracteres)"
                                placeholder="Ingrese el nombre de la categoría" 
                                required>
                            <p class="text-xs text-gray-500">Máximo 255 caracteres (solo letras, números, espacios y guiones)</p>
                        </div>
                        
                        <!-- Descripción de la categoría -->
                        <div class="space-y-2">
                            <label for="descripcionCategoria" class="block text-sm font-medium text-gray-700">Descripción de la Categoría</label>
                            <textarea id="descripcionCategoria" name="descripcionCategoria" rows="4" maxlength="500"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]/g, '').replace(/\s+/g, ' ').slice(0, 500); this.value = this.value.replace(/(^|\s)\S/g, l => l.toUpperCase())" 
                                onblur="capitalizeFirstLetters(this)"
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]*$"
                                title="La descripción solo puede contener letras, números, espacios y guiones (máximo 500 caracteres)"
                                placeholder="Ingrese la descripción de la categoría"><?php echo isset($formData['descripcionCategoria']) ? htmlspecialchars($formData['descripcionCategoria']) : ''; ?></textarea>
                            <p class="text-xs text-gray-500">Máximo 500 caracteres (opcional)</p>
                        </div>
                        
                        <br>
                        <div class="flex justify-between">
                            <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                Salir
                            </a>
                            <button type="submit" id="btnRegistrar" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Categoría
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
            const form = document.getElementById('categoriaForm');
            const btnRegistrar = document.getElementById('btnRegistrar');
            
            form.addEventListener('submit', function(event) {
                const nombreCategoria = document.getElementById('nombreCategoria').value.trim();
                const descripcionCategoria = document.getElementById('descripcionCategoria').value.trim();
                const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]+$/;
                const descripcionRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]*$/; // permite vacío

                if (!nombreRegex.test(nombreCategoria)) {
                    alert('El nombre de la categoría solo puede contener letras, números, espacios y guiones.');
                    event.preventDefault();
                    return;
                }

                if (nombreCategoria.length > 255) {
                    alert('El nombre de la categoría no puede exceder los 255 caracteres.');
                    event.preventDefault();
                    return;
                }

                if (descripcionCategoria && (!descripcionRegex.test(descripcionCategoria) || descripcionCategoria.length > 500)) {
                    alert('La descripción solo puede contener letras, números, espacios y guiones, con un máximo de 500 caracteres.');
                    event.preventDefault();
                    return;
                }
            });
        });
    </script>
</body>
</html>