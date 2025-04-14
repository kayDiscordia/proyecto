<?php
$rol = isset($_SESSION['idRol']) ? $_SESSION['idRol'] : null;
$nombre = isset($_SESSION['nombres']) ? $_SESSION['nombres'] : null;
$apellido = isset($_SESSION['apellidos']) ? $_SESSION['apellidos'] : null;
$departamento = isset($_SESSION['idDepartamento']) ? $_SESSION['idDepartamento'] : null;

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div :class="{'w-64': !isCollapsed, 'w-16': isCollapsed}" 
    class="bg-[#89C0E9] h-full shadow-lg flex flex-col transition-all duration-300" x-data="{ isCollapsed: false }">
    <div class="flex justify-end p-2">
        <button @click="isCollapsed = !isCollapsed" class="text-gray-500 hover:text-gray-600">
            <svg x-show="!isCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            <svg x-show="isCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </div>
    <div class="p-4 text-center bg-[#89c0e9] rounded-lg mb-4" x-show="!isCollapsed" x-transition>
            <img src="../vistas/CSS/Logo.png" alt="Logo" class="w-16 h-16 mx-auto mb-2">
            <h1 class="text-xl font-bold text-gray-900">Sistema de Gestión</h1>
            <p class="text-lg font-bold text-gray-900">
                Bienvenido, <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>!
            </p>
          <!--  <p class="text-lg font-bold text-gray-900">
                <?php // echo htmlspecialchars($departamento); ?>
            </p> -->
        </div>
    <div class="flex-1 overflow-y-auto">
        <div class="p-4" x-show="!isCollapsed" x-transition>
            <div class="space-y-4">
                <div class="p-2 bg-white rounded-lg">
                    <button onclick=location.href="../vistas/home.php" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center">
                    <i class="fa-solid fa-table-columns p-2"></i>                
                        Dashboard
                    </button>
                </div>
                <?php if ($rol == 1): ?>
                <div class="p-2 bg-white rounded-lg shadow">
                    <div x-data="{ open: false }">
                    
                        <button @click="open = !open" class="flex justify-between items-center w-full text-sm font-medium">
                            <span><i class="fa-solid fa-clipboard p-2"></i>Gestionar Actividades</span>
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div x-show="open" x-transition class="mt-2 space-y-2">
                            <button onclick=location.href="../vistas/registrarActividades.php" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center">
                                Registrar Actividades
                            </button>
                            <button onclick=location.href="../vistas/verActividades.php?idEmpleado=<?php echo $_SESSION['id']; ?>" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center">
                                Ver Actividades
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($rol == 1): ?>
                <div class="p-2 bg-white rounded-lg shadow">
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-sm font-medium">
                            <span><i class="fa-duotone fa-solid fa-circle-user p-2"></i>Gestionar empleados</span>
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div x-show="open" x-transition class="mt-2 space-y-2">
                            <button onclick=location.href="../vistas/registrarEmpleado.php" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center evaluar-empleado">
                                Registrar empleado
                            </button>
                            <button onclick=location.href="../vistas/verEmpleado.php" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center evaluar-empleado">
                                Ver Empleado
                            </button>                                
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="p-2 bg-white rounded-lg shadow">
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-sm font-medium">
                            <span><i class="fa-solid fa-address-book p-2"></i>Gestionar Reportes</span>
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div x-show="open" x-transition class="mt-2 space-y-2">
                            <button onclick=location.href="../vistas/listaEmpleado.php" class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center evaluar-empleado">
                                Reportes por Empleado
                            </button>
                            <button class="w-full text-left px-2 py-1 text-sm hover:bg-gray-200 rounded flex items-center evaluar-empleado">
                                Reportes Trimestrales
                            </button>                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="p-4 border-t border-blue-200">
        <a href="../Login/Logout.php" class="w-full flex items-center justify-start text-red-600 hover:text-red-700 hover:bg-red-100 px-2 py-1 rounded">
            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span x-show="!isCollapsed" x-transition>Cerrar Sesión</span>
        </a>
    </div>
</div>