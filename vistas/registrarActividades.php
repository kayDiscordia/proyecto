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

require_once '../controladores/controladorActividad.php';
require_once '../controladores/controladorEmpleado.php';

$controladorEmpleado = new controladorEmpleado();
$empleados = $controladorEmpleado->obtenerEmpleados();
$departamentos = $controladorEmpleado->obtenerDepartamentos();

$controlador = new controladorActividad();

// Manejar mensajes y datos del formulario
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$mensaje = isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : null;
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->manejarInsercionActividad();
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener la fecha actual en formato YYYY-MM-DD
            const today = new Date().toISOString().split('T')[0];
            
            // Establecer el atributo min en los campos de fecha
            document.getElementById('fechaInicio').min = today;
            document.getElementById('fechaCulminacion').min = today;
            
            // Validar que la fecha de culminación no sea anterior a la de inicio
            document.getElementById('fechaInicio').addEventListener('change', function() {
                const fechaInicio = this.value;
                document.getElementById('fechaCulminacion').min = fechaInicio;
            });

            const departamentoSelect = document.getElementById('idDepartamento');
            const categoriaSelect = document.getElementById('idCategoria');

            departamentoSelect.addEventListener('change', async function() {
                const selectedDepartamento = departamentoSelect.value;
                categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';

                if (!selectedDepartamento) {
                    categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                    return;
                }

                try {
                    const response = await fetch(`obtenerCategorias.php?idDepartamento=${selectedDepartamento}`);
                    if (!response.ok) {
                        throw new Error('Error al cargar categorías');
                    }
                    const categorias = await response.json();

                    categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                    
                    categorias.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.idCategoria;
                        option.text = categoria.nombreCategoria;
                        categoriaSelect.add(option);
                    });

                    // Restaurar selección previa si existe
                    <?php if (!empty($formData['idCategoria'])): ?>
                        if (categoriaSelect.querySelector(`option[value="<?php echo $formData['idCategoria']; ?>"]`)) {
                            categoriaSelect.value = "<?php echo $formData['idCategoria']; ?>";
                        }
                    <?php endif; ?>
                } catch (error) {
                    console.error('Error al cargar categorías:', error);
                    categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                }
            });

            // Disparar el evento change si ya hay un departamento seleccionado
            <?php if (!empty($formData['idDepartamento'])): ?>
                departamentoSelect.value = "<?php echo $formData['idDepartamento']; ?>";
                departamentoSelect.dispatchEvent(new Event('change'));
            <?php endif; ?>
        });
    </script>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4 text-center">Registrar Actividad</h1>
            
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
                    <form action="" method="POST" id="activityForm" class="space-y-4">
                        <!-- Select para departamentos -->
                        <div class="space-y-2">
                            <label for="idDepartamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                            <select id="idDepartamento" name="idDepartamento" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['idDepartamentos']; ?>" 
                                        <?php echo (!empty($formData['idDepartamento']) && $formData['idDepartamento'] == $departamento['idDepartamentos'] ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($departamento['nombreDepartamentos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Select para categorías de actividades -->
                        <div class="space-y-2">
                            <label for="idCategoria" class="block text-sm font-medium text-gray-700">Categoría de la Actividad</label>
                            <select id="idCategoria" name="idCategoria" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un departamento primero</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="space-y-2">
                                <label for="descripcionActividad" class="block text-sm font-medium text-gray-700">Descripción de la Actividad</label>
                                <textarea id="descripcionActividad" name="descripcionActividad" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Ingrese la descripción de la actividad" required><?php echo isset($formData['descripcionActividad']) ? htmlspecialchars($formData['descripcionActividad']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="space-y-2">
                                <label for="fechaInicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                <input type="date" id="fechaInicio" name="fechaInicio" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo isset($formData['fechaInicio']) ? htmlspecialchars($formData['fechaInicio']) : ''; ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                            <div class="space-y-2">
                                <label for="fechaCulminacion" class="block text-sm font-medium text-gray-700">Fecha de Culminación</label>
                                <input type="date" id="fechaCulminacion" name="fechaCulminacion" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo isset($formData['fechaCulminacion']) ? htmlspecialchars($formData['fechaCulminacion']) : ''; ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            </div>
                        </div>
                        
                        <!-- Select para empleados -->
                        <div class="space-y-2">
                            <label for="idEmpleado" class="block text-sm font-medium text-gray-700">Empleado Receptor</label>
                            <select id="idEmpleado" name="idEmpleado" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccione un empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['idEmpleado']; ?>" 
                                        <?php echo (!empty($formData['idEmpleado']) && $formData['idEmpleado'] == $empleado['idEmpleado'] ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <br>
                        <div class="flex justify-between">
                            <a href="home.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                                Salir
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Registrar Actividad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>