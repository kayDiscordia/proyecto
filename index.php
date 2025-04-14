<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'login/functionLogin.php';

if (isset($_SESSION['id'])) {
    header('location: vistas/home.php');
    exit();
}

$iniciosesion = new Login();

if (isset($_POST['submit'])) {
    $usuario = trim($_POST['Usuario']);
    $contrasena = trim($_POST['Contrasena']);

    if (!empty($usuario) && !empty($contrasena)) {
        $resultado = $iniciosesion->IniciarSesion($usuario, $contrasena);

        if ($resultado == 1) {
            $_SESSION['iniciosesion'] = true;
            $_SESSION['id'] = $iniciosesion->IdUsuario();
        
            // Obtener el cargo del empleado y guardarlo en la sesión
            $cargo = $iniciosesion->obtenerCargoPorId($_SESSION['id']);
            $_SESSION['cargo'] = $cargo;
        
            // Establecer los datos de nombre y apellido en la sesión
            $iniciosesion->establecerDatosSesion($_SESSION['id']);
        
            header('location: vistas/home.php');
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="vistas/CSS/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy-blue': '#0A1E5F',
                    }
                }
            }
        }
    </script>
</head>
<body class="relative min-h-screen flex items-center justify-center bg-[#E8EEFF]">
    <!-- Imagen de fondo difuminada -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('vistas/CSS/DeWatermark.ai_1743707779701.png'); filter: blur(1px); -webkit-filter: blur(1px); z-index: -1;"></div>

    <!-- Contenedor principal -->
    <div class="w-full max-w-md p-8 rounded-lg bg-[#89C0E9] bg-opacity-90 border-gray-300 shadow-lg relative z-10">
        <?php if (isset($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="flex justify-center text-gray-800 font-bold">
                SISTEMA DE GESTION DE ACTIVIDADES
            </div>
            <div class="flex justify-center">
                <img src="vistas/CSS/Logo.png" alt="Logo" class="w-26 h-26 mb-4">
            </div>
            <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Iniciar Sesión</h1>

            <!-- Usuario -->
            <div class="mb-4">
                <label for="Usuario" class="block text-sm font-medium text-gray-800 mb-1">
                    <i class="fa-regular fa-user"></i> Usuario
                </label>
                <input
                    type="text"
                    id="Usuario"
                    name="Usuario"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nombre de usuario"
                    required
                />
            </div>
            
            <!-- Contraseña -->
            <div class="mb-6">
                <label for="Contrasena" class="block text-sm font-medium text-gray-800 mb-1">
                    <i class="fa-solid fa-key"></i> Contraseña
                </label>
                <input
                    type="password"
                    id="Contrasena"
                    name="Contrasena"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="••••••••"
                    required
                />
            </div>
            
            <button
                type="submit"
                name="submit"
                class="w-full bg-[#0A1E5F] text-white font-medium py-2 px-4 rounded-md hover:bg-blue-500 transition-colors"
            >
                Iniciar Sesión
            </button>
        </form>
    </div>
</body>
</html>