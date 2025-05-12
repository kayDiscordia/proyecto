<?php
require '../login/functionLogin.php';
require '../controladores/controladorActividad.php';

$select = new Login();
$controladorActividad = new controladorActividad();

if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('location: ../index.php');
    exit;
}

// Obtener actividades para el calendario
$eventosCalendario = $controladorActividad->obtenerActividadesParaCalendario();

// Obtener estadísticas y listado de actividades
$actividades = $controladorActividad->obtenerActividades();

if (!is_array($actividades)) {
    die('Error: La función obtenerActividades() no devolvió un array.');
}

// Inicializar contadores
$porIniciar = $enProceso = $retraso = $canceladas = $culminadas = $pendientes = 0;
$totalActividades = count($actividades);
$actividadesListado = [];

foreach ($actividades as $actividad) {
    // Verificar y asignar valores por defecto si las claves no existen
    $nombre = $actividad['nombreActividad'] ?? 'Sin nombre';
    $descripcion = $actividad['descripcionActividad'] ?? 'Sin descripción';
    $fechaInicio = $actividad['fechaInicio'] ?? date('Y-m-d');
    $fechaFin = $actividad['fechaCulminacion'] ?? date('Y-m-d');
    $estado = isset($actividad['estadoActividad']) ? trim($actividad['estadoActividad']) : 'Desconocido';

    // Para estadísticas
    switch ($estado) {
        case 'Por Iniciar':
            $porIniciar++;
            $color = '#FBBF24'; // Amarillo
            $icono = '⏱️'; // Icono de reloj
            break;
        case 'Retraso':
            $retraso++;
            $color = '#60A5FA'; // Azul
            $icono = '📅'; // Icono de calendario
            break;
        case 'En progreso':
            $enProceso++;
            $color = '#F97316'; // Naranja
            $icono = '🚧'; // Icono de construcción
            break;
        case 'Cancelada':
            $canceladas++;
            $color = '#EF4444'; // Rojo
            $icono = '❌'; // Icono de cancelación
            break;
        case 'Completada':
            $culminadas++;
            $color = '#10B981'; // Verde
            $icono = '✅'; // Icono de completado
            break;
        default:
            $color = '#94A3B8'; // Gris por defecto
            $icono = '❓'; // Icono de desconocido
            break;
    }

    // Para listado de actividades
    $actividadesListado[] = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'fechaInicio' => $fechaInicio,
        'fechaFin' => $fechaFin,
        'estado' => $estado,
        'color' => $color,
        'icono' => $icono
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="CSS/animaciones.css">
    <!-- SweetAlert para mostrar detalles -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false, activeTab: 'calendario' }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4">Panel de Actividades</h1>
            <!-- Contenido de pestañas -->
            <div>
                <!-- Grid de estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Recuadro Total -->
                    <div class="status-card bg-indigo-100 text-indigo-800">
                        <h2 class="text-lg font-semibold mb-2">Total Actividades</h2>
                        <p class="text-2xl font-bold"><?= $totalActividades ?></p>
                    </div>

                    <!-- Recuadro Por Iniciar -->
                    <div class="status-card bg-yellow-100 text-yellow-800">
                        <h2 class="text-lg font-semibold mb-2">Por Iniciar</h2>
                        <p class="text-2xl font-bold"><?= $porIniciar ?></p>
                    </div>

                    <!-- Recuadro En Proceso -->
                    <div class="status-card bg-orange-100 text-orange-800">
                        <h2 class="text-lg font-semibold mb-2">En Proceso</h2>
                        <p class="text-2xl font-bold"><?= $enProceso ?></p>
                    </div>

                    <!-- Recuadro Pendientes -->
                    <div class="status-card bg-blue-100 text-blue-800">
                        <h2 class="text-lg font-semibold mb-2">Retrasadas</h2>
                        <p class="text-2xl font-bold"><?= $retraso ?></p>
                    </div>

                    <!-- Recuadro Canceladas -->
                    <div class="status-card bg-red-100 text-red-800">
                        <h2 class="text-lg font-semibold mb-2">Canceladas</h2>
                        <p class="text-2xl font-bold"><?= $canceladas ?></p>
                    </div>

                    <!-- Recuadro Culminadas -->
                    <div class="status-card bg-green-100 text-green-800">
                        <h2 class="text-lg font-semibold mb-2">Culminadas</h2>
                        <p class="text-2xl font-bold"><?= $culminadas ?></p>
                    </div>
                </div>
            </div>
            <!-- Pestañas -->
            <div class="tabs">
                <div class="tab" :class="{ 'active': activeTab === 'calendario' }" @click="activeTab = 'calendario'">
                    <i class="fas fa-calendar-alt mr-2"></i> Calendario
                </div>
                <div class="tab" :class="{ 'active': activeTab === 'listado' }" @click="activeTab = 'listado'">
                    <i class="fas fa-list-ul mr-2"></i> Listado
                </div>
            </div>

            <div class="tab-content" :class="{ 'active': activeTab === 'calendario' }">
                <!-- Calendario -->
                <div id="calendar" class="mb-6"></div>
            </div>

            <div class="tab-content" :class="{ 'active': activeTab === 'listado' }">
                <!-- Listado de actividades con iconos -->
                <h2 class="text-xl font-semibold mb-4">Listado de Actividades</h2>
                <div class="actividades-container">
                    <?php foreach ($actividadesListado as $actividad): ?>
                        <div class="actividad-card" style="border-left-color: <?= $actividad['color'] ?>;">
                            <div class="actividad-header">
                                <div class="actividad-icono"><?= $actividad['icono'] ?></div>
                                <h3 class="actividad-nombre"><?= htmlspecialchars($actividad['nombre']) ?></h3>
                            </div>
                            <span class="actividad-estado" style="background-color: <?= $actividad['color'] ?>20; color: <?= $actividad['color'] ?>;">
                                <?= $actividad['estado'] ?>
                            </span>
                            <div class="actividad-fechas">
                                <div><i class="far fa-calendar-alt mr-1"></i> Inicio: <?= date('d/m/Y', strtotime($actividad['fechaInicio'])) ?></div>
                                <div><i class="far fa-calendar-check mr-1"></i> Fin: <?= date('d/m/Y', strtotime($actividad['fechaFin'])) ?></div>
                            </div>
                            <p class="actividad-descripcion"><?= htmlspecialchars($actividad['descripcion']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?= json_encode($eventosCalendario) ?>,
                eventClick: function(info) {
                    const event = info.event;
                    const descripcion = event.extendedProps.description || 'Sin descripción';
                    const estado = event.extendedProps.estado || 'Estado desconocido';
                    const empleado = event.extendedProps.empleado || 'No asignado';
                    const categoria = event.extendedProps.categoria || 'Sin categoría';

                    Swal.fire({
                        title: event.title,
                        html: `
                            <div class="text-left">
                                <p><strong>Estado:</strong> ${estado}</p>
                                <p><strong>Empleado:</strong> ${empleado}</p>
                                <p><strong>Categoría:</strong> ${categoria}</p>
                                <p><strong>Fecha Inicio:</strong> ${event.start ? event.start.toLocaleDateString() : 'No especificada'}</p>
                                ${event.end ? `<p><strong>Fecha Fin:</strong> ${event.end.toLocaleDateString()}</p>` : ''}
                                <p class="mt-2"><strong>Descripción:</strong></p>
                                <p class="text-gray-700">${descripcion}</p>
                            </div>
                        `,
                        confirmButtonText: 'Cerrar',
                        width: '600px',
                        background: '#ffffff',
                        backdrop: `
                            rgba(0,0,0,0.5)
                            url("/images/nyan-cat.gif")
                            left top
                            no-repeat
                        `
                    });
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>