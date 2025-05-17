<?php
$rol = isset($_SESSION['idRol']) ? $_SESSION['idRol'] : null;
$nombre = isset($_SESSION['nombres']) ? $_SESSION['nombres'] : null;
$apellido = isset($_SESSION['apellidos']) ? $_SESSION['apellidos'] : null;
$departamento = isset($_SESSION['idDepartamento']) ? $_SESSION['idDepartamento'] : null;
?>
<link rel="stylesheet" href="CSS/output.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    /* Nuevas animaciones para el sidebar */
    .sidebar-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .menu-item {
        transition: all 0.2s ease;
        transform-origin: left center;
    }
    
    .menu-item:hover {
        transform: translateX(5px);
    }
    
    .submenu {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .logo-animation {
        transition: all 0.3s ease;
    }
    
    .collapsed-logo {
        transform: scale(0.8);
        opacity: 0.8;
    }
    
    .nav-link {
        position: relative;
        overflow: hidden;
    }
    
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #3B82F6;
        transition: width 0.3s ease;
    }
    
    .nav-link:hover::after {
        width: 100%;
    }
    
    .rotate-chevron {
        transition: transform 0.3s ease;
    }
    
    .user-info {
        transition: all 0.3s ease;
    }
</style>

<div :class="{'w-64': !isCollapsed, 'w-16': isCollapsed}"
    class="bg-[#89C0E9] h-full shadow-lg flex flex-col sidebar-transition"
    x-data="{
        isCollapsed: false,
        openMenu: null,
        toggleMenu(menu) {
            if (this.openMenu === menu) {
                this.openMenu = null;
            } else if (this.openMenu !== null) {
                this.openMenu = null;
                setTimeout(() => { this.openMenu = menu }, 250);
            } else {
                this.openMenu = menu;
            }
        }
    }">
    <!-- Botón de colapsar -->
    <div class="flex justify-end p-2">
        <button @click="isCollapsed = !isCollapsed" 
                class="text-gray-500 hover:text-gray-600 transition-colors duration-200"
                x-tooltip="isCollapsed ? 'Expandir menú' : 'Colapsar menú'">
            <svg x-show="!isCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <svg x-show="isCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>
    
    <!-- Logo y usuario -->
    <div class="p-4 text-center bg-[#89c0e9] rounded-lg mb-4" 
         x-show="!isCollapsed" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :class="{'logo-animation': true, 'collapsed-logo': isCollapsed}">
        <img src="../vistas/CSS/Logo.png" alt="Logo" class="w-16 h-16 mx-auto mb-2 transition-all duration-300">
        <h1 class="text-xl font-bold text-gray-900 transition-all duration-300">Sistema de Gestión</h1>
        <p class="text-lg font-bold text-gray-900 transition-all duration-300">
            Bienvenido, <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>!
        </p>
    </div>
    
    <!-- Menú principal -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-4" x-show="!isCollapsed" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-[-20px]"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-[-20px]">
            <div class="space-y-2">
                <!-- Panel de Control -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button onclick="location.href='../vistas/home.php'" 
                            class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                        <i class="fa-solid fa-table-columns p-2 text-blue-500"></i>
                        <span class="ml-2">Panel de Control</span>
                    </button>
                </div>
                
                <?php if ($rol == 1): ?>
                <!-- Gestionar Departamento -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button @click="toggleMenu(1)" 
                            class="flex justify-between items-center w-full text-sm font-medium nav-link">
                        <span class="flex items-center">
                            <i class="fa-solid fa-building p-2 text-blue-500"></i>
                            <span class="ml-2">Gestionar Departamento</span>
                        </span>
                        <svg :class="{'rotate-chevron rotate-180': openMenu === 1}" 
                             class="w-4 h-4 transition-transform duration-300" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div x-show="openMenu === 1"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-40"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-40"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-2 overflow-hidden submenu">
                        <button onclick="location.href='../vistas/registrarCargo.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Registrar Cargo
                        </button>
                        <button onclick="location.href='../vistas/registrarCategoria.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Registrar Categoria
                        </button>
                    </div>
                </div>
                <?php endif; ?>
               
                <?php if ($rol == 1): ?>
                <!-- Gestionar Actividades (Admin) -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button @click="toggleMenu(2)" 
                            class="flex justify-between items-center w-full text-sm font-medium nav-link">
                        <span class="flex items-center">
                            <i class="fa-solid fa-clipboard p-2 text-green-500"></i>
                            <span class="ml-2">Gestionar Actividades</span>
                        </span>
                        <svg :class="{'rotate-chevron rotate-180': openMenu === 2}" 
                             class="w-4 h-4 transition-transform duration-300" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div x-show="openMenu === 2"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-40"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-40"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-2 overflow-hidden submenu">
                        <button onclick="location.href='../vistas/registrarActividades.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Registrar Actividades
                        </button>
                        <button onclick="location.href='../vistas/verActividades.php?idEmpleado=<?php echo $_SESSION['id']; ?>'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Ver Actividades
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($rol == 2): ?>
                <!-- Gestionar Actividades (Empleado) -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button @click="toggleMenu(3)" 
                            class="flex justify-between items-center w-full text-sm font-medium nav-link">
                        <span class="flex items-center">
                            <i class="fa-solid fa-clipboard p-2 text-green-500"></i>
                            <span class="ml-2">Gestionar Actividades</span>
                        </span>
                        <svg :class="{'rotate-chevron rotate-180': openMenu === 3}" 
                             class="w-4 h-4 transition-transform duration-300" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div x-show="openMenu === 3"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-40"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-40"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-2 overflow-hidden submenu">
                        <button onclick="location.href='../vistas/verActividades.php?idEmpleado=<?php echo $_SESSION['id']; ?>'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Ver Actividades
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($rol == 1): ?>
                <!-- Gestionar empleados -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button @click="toggleMenu(4)" 
                            class="flex justify-between items-center w-full text-sm font-medium nav-link">
                        <span class="flex items-center">
                            <i class="fa-solid fa-users p-2 text-purple-500"></i>
                            <span class="ml-2">Gestionar empleados</span>
                        </span>
                        <svg :class="{'rotate-chevron rotate-180': openMenu === 4}" 
                             class="w-4 h-4 transition-transform duration-300" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div x-show="openMenu === 4"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-40"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-40"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-2 overflow-hidden submenu">
                        <button onclick="location.href='../vistas/registrarEmpleado.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Registrar empleado
                        </button>
                        <button onclick="location.href='../vistas/verEmpleado.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Ver Empleado
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Gestionar Reportes -->
                <div class="p-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-300 menu-item">
                    <button @click="toggleMenu(5)" 
                            class="flex justify-between items-center w-full text-sm font-medium nav-link">
                        <span class="flex items-center">
                            <i class="fa-solid fa-chart-bar p-2 text-orange-500"></i>
                            <span class="ml-2">Gestionar Reportes</span>
                        </span>
                        <svg :class="{'rotate-chevron rotate-180': openMenu === 5}" 
                             class="w-4 h-4 transition-transform duration-300" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div x-show="openMenu === 5"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-40"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-40"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="mt-2 space-y-2 overflow-hidden submenu">
                        <button onclick="location.href='../vistas/graficaSemanalMensual.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Reportes por semana
                        </button>
                        <button onclick="location.href='../vistas/graficaTrimestral.php'" 
                                class="w-full text-left px-6 py-1 text-sm hover:bg-gray-100 rounded flex items-center nav-link">
                            Reportes por trimestre
                        </button>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cerrar sesión -->
    <div class="p-4 border-t border-blue-200">
        <a href="../Login/Logout.php" 
           class="w-full flex items-center justify-start text-red-600 hover:text-red-700 hover:bg-red-100 px-2 py-1 rounded transition-colors duration-200 nav-link">
            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span x-show="!isCollapsed" 
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 translate-x-[-10px]"
                  x-transition:enter-end="opacity-100 translate-x-0"
                  x-transition:leave="transition ease-in duration-200"
                  x-transition:leave-start="opacity-100 translate-x-0"
                  x-transition:leave-end="opacity-0 translate-x-[-10px]">
                Cerrar Sesión
            </span>
        </a>
    </div>
</div>