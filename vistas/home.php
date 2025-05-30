<?php
require '../login/functionLogin.php';
require '../controladores/controladorActividad.php';

$select = new Login();
$controladorActividad = new controladorActividad();

if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
    $idDepartamentoUsuario = $_SESSION['idDepartamento'] ?? null;
} else {
    header('location: ../index.php');
}

// Obtener departamentos para el filtro
$departamentos = $controladorActividad->obtenerDepartamentos();

// Determinar departamento a filtrar (si no se seleccionó, usar el del usuario)
$idDepartamentoFiltro = $_GET['idDepartamento'] ?? $idDepartamentoUsuario;

// Obtener estadísticas
$estadisticas = $controladorActividad->obtenerEstadisticasActividades($idDepartamentoFiltro);

// Obtener actividades para el calendario
$eventosCalendario = $controladorActividad->obtenerActividadesParaCalendario($idDepartamentoFiltro);
$eventosJson = json_encode($eventosCalendario);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Actividades</title>
    <!-- FullCalendar CSS -->
    <link href='CSS/calendar.css' rel='stylesheet' />


    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="CSS/fontawesome.css">
    <link rel="stylesheet" href="CSS/output.css">
    <style>
        /* Estilos para colores según estado */
        .bg-blue-100 {
            background-color: #DBEAFE;
        }

        .text-blue-800 {
            color: #1E40AF;
        }

        .bg-yellow-100 {
            background-color: #FEF3C7;
        }

        .text-yellow-800 {
            color: #92400E;
        }

        .bg-orange-100 {
            background-color: #FFEDD5;
        }

        .text-orange-800 {
            color: #9A3412;
        }

        .bg-red-100 {
            background-color: #FEE2E2;
        }

        .text-red-800 {
            color: #B91C1C;
        }

        .bg-green-100 {
            background-color: #D1FAE5;
        }

        .text-green-800 {
            color: #065F46;
        }

        .bg-indigo-100 {
            background-color: #E0E7FF;
        }

        .text-indigo-800 {
            color: #3730A3;
        }

        /* Estilos para los recuadros de estado */
        .status-card {
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: transform 0.2s;
        }

        .status-card:hover {
            transform: translateY(-2px);
        }

        /* Estilos para el calendario */
        #calendar {
            width: 100%;
            min-width: 320px;
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 1rem;
            overflow-x: auto;
        }

        .fc .fc-daygrid-day-frame {
            overflow-x: auto !important;
            word-break: break-word;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 0.25rem;
            padding: 0.1rem 0.25rem;
            font-size: 0.85rem;
            white-space: normal !important;
            word-break: break-word;
            max-width: 100%;
            overflow-wrap: break-word;
        }

        .fc-daygrid-event-dot {
            display: none;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 0.25rem;


        }

        #calendar {
            min-height: 600px;
        }
    </style>
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>

        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4">Calendario de Actividades</h1>

            <!-- Contadores -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <a href="verActividades.php" class="block status-card bg-[#6D28D9] text-white hover:shadow-lg hover:scale-105 transition cursor-pointer">
                    <h2 class="text-lg font-semibold mb-2">Total Actividades</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['total'] ?></p>
                </a>
                <a href="verActividades.php?estado=Por%20Iniciar" class="block status-card" style="background-color:#FACC15; color:#fff;">
                    <h2 class="text-lg font-semibold mb-2">Por Iniciar</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['por_iniciar'] ?></p>
                </a>
                <a href="verActividades.php?estado=Retraso" class="block status-card" style="background-color:#1D4ED8; color:#fff;">
                    <h2 class="text-lg font-semibold mb-2">Retraso</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['retraso'] ?? 0 ?></p>
                </a>
                <a href="verActividades.php?estado=En%20progreso" class="block status-card" style="background-color:#F97316; color:#fff;">
                    <h2 class="text-lg font-semibold mb-2">En Proceso</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['en_progreso'] ?></p>
                </a>
                <a href="verActividades.php?estado=Cancelada" class="block status-card" style="background-color:#EF4444; color:#fff;">
                    <h2 class="text-lg font-semibold mb-2">Canceladas</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['canceladas'] ?></p>
                </a>
                <a href="verActividades.php?estado=Completada" class="block status-card" style="background-color:#10B981; color:#fff;">
                    <h2 class="text-lg font-semibold mb-2">Completadas</h2>
                    <p class="text-2xl font-bold"><?= $estadisticas['completadas'] ?></p>
                </a>
            </div>

            <!-- Calendario -->
            <div class="w-full overflow-x-auto">
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- FullCalendar JS -->
    <script src='JS/calendar-main.js'></script>
    <script src='JS/calendar-local.js'></script>
    <!-- SweetAlert para modales -->
    <script src="JS/sweetalert.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventos = <?php echo $eventosJson; ?>;

            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: eventos,
                dayMaxEvents: 3, // Muestra máximo 3 eventos, el resto aparece como "+X más"
                eventClick: function(info) {
                    const event = info.event;
                    Swal.fire({
                        title: event.title,
                        html: `
                <div class="text-left">
                    <p><strong>Estado:</strong> ${event.extendedProps.estado}</p>
                    <p><strong>Empleado:</strong> ${event.extendedProps.empleado}</p>
                    <p><strong>Categoría:</strong> ${event.extendedProps.categoria}</p>
                    <p><strong>Fecha Inicio:</strong> ${event.start.toLocaleDateString()}</p>
                    ${event.end ? `<p><strong>Fecha Fin:</strong> ${event.end.toLocaleDateString()}</p>` : ''}
                    <p><strong>Descripción:</strong> ${event.extendedProps.description}</p>
                </div>
            `,
                        confirmButtonText: 'Cerrar'
                    });
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>