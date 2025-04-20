<?php
require '../login/functionLogin.php';
require '../controladores/controladorActividad.php';

$select = new Login();
$controladorActividad = new controladorActividad();

if (isset($_SESSION['id'])) {
    $user = $select->SelectuserByuser($_SESSION['id']);
} else {
    header('location: ../index.php');
}

// Obtener actividades para el calendario
$eventosCalendario = $controladorActividad->obtenerActividadesParaCalendario();

// Obtener estadísticas
$actividades = $controladorActividad->obtenerActividades();
$enProceso = $canceladas = $culminadas = 0;

foreach ($actividades as $actividad) {
    switch ($actividad['estadoActividad']) {
        case 'En progreso': $enProceso++; break;
        case 'Cancelada': $canceladas++; break;
        case 'Completada': $culminadas++; break;
    }
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
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="CSS/output.css">
  <!-- Reemplaza la sección de estilos de tu home.php con esto: -->
<style>
    /* Estilos para colores según estado */
    .bg-yellow-100 { background-color: #FEF3C7; }
    .text-yellow-800 { color: #92400E; }
    .bg-red-100 { background-color: #FEE2E2; }
    .text-red-800 { color: #B91C1C; }
    .bg-green-100 { background-color: #D1FAE5; }
    .text-green-800 { color: #065F46; }
    
    /* Estilos para el calendario - Versión corregida */
    #calendar {
        max-width: 1100px;
        margin: 0 auto;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    .fc {
        font-family: inherit;
    }
    
    .fc-header-toolbar {
        margin-bottom: 1em;
    }
    
    .fc-daygrid-day {
        overflow: hidden;
    }
    
    .fc-event {
        cursor: pointer;
        font-size: 0.75em;
        padding: 1px 3px;
        margin: 1px 2px;
        border-radius: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }
    
    .fc-daygrid-event-harness {
        margin: 1px 0;
    }
    
    .fc-event-main {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Colores de eventos según estado - Versión corregida */
    .event-en-progreso {
        background-color: rgba(245, 158, 11, 0.8);
        border-color: rgba(245, 158, 11, 0.9);
        color: #92400E;
    }
    
    .event-cancelada {
        background-color: rgba(239, 68, 68, 0.8);
        border-color: rgba(239, 68, 68, 0.9);
        color: #B91C1C;
    }
    
    .event-completada {
        background-color: rgba(16, 185, 129, 0.8);
        border-color: rgba(16, 185, 129, 0.9);
        color: #065F46;
    }
    
    .fc-daygrid-day-frame {
        min-height: 100px;
        overflow: hidden;
    }
    
    .fc-scrollgrid-sync-table {
        overflow: hidden;
    }
    
    /* Modal (mantener igual) */
    .modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 0.5rem;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
</style>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-semibold mb-4">Panel de Control</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 rounded-lg shadow bg-yellow-100 text-yellow-800">
                    <h2 class="text-lg font-semibold mb-2">En Proceso</h2>
                    <p><?= $enProceso ?> actividades</p>
                </div>
                <div class="p-4 rounded-lg shadow bg-red-100 text-red-800">
                    <h2 class="text-lg font-semibold mb-2">Canceladas</h2>
                    <p><?= $canceladas ?> actividades</p>
                </div>
                <div class="p-4 rounded-lg shadow bg-green-100 text-green-800">
                    <h2 class="text-lg font-semibold mb-2">Culminadas</h2>
                    <p><?= $culminadas ?> actividades</p>
                </div>
            </div>
            
            <!-- Sección del Calendario -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h2 class="text-xl font-semibold mb-4">Calendario de Actividades</h2>
                <div id="calendar"></div>
            </div>
            
            <!-- Modal para detalles -->
            <div id="eventModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 class="text-xl font-semibold mb-4" id="modalTitle"></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Fecha Inicio:</strong> <span id="modalStartDate"></span></p>
                            <p><strong>Fecha Fin:</strong> <span id="modalEndDate"></span></p>
                            <p><strong>Estado:</strong> <span id="modalStatus"></span></p>
                        </div>
                        <div>
                            <p><strong>Empleado:</strong> <span id="modalEmployee"></span></p>
                            <p><strong>Categoría:</strong> <span id="modalCategory"></span></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p><strong>Descripción:</strong></p>
                        <p id="modalDescription" class="mt-2"></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js'></script>
    
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const modal = document.getElementById('eventModal');
        const span = document.getElementsByClassName('close')[0];
        
        // Función para formatear fecha sin hora
        function formatDate(dateStr) {
            if (!dateStr) return 'No especificada';
            
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            
            // Opción 1: Formato numérico (DD/MM/YYYY)
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
            
            /* Opción 2: Formato con nombre de mes
            const options = { day: '2-digit', month: 'long', year: 'numeric' };
            return date.toLocaleDateString('es-ES', options);
            */
        }

        // Configurar el calendario
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: <?= json_encode($eventosCalendario) ?>,
            eventClick: function(info) {
                const event = info.event;
                
                // Llenar el modal con los datos del evento
                document.getElementById('modalTitle').textContent = event.title;
                document.getElementById('modalStartDate').textContent = formatDate(event.start);
                document.getElementById('modalEndDate').textContent = formatDate(event.end);
                document.getElementById('modalStatus').textContent = event.extendedProps.estado || 'No especificado';
                document.getElementById('modalEmployee').textContent = event.extendedProps.empleado || 'No asignado';
                document.getElementById('modalCategory').textContent = event.extendedProps.categoria || 'Sin categoría';
                document.getElementById('modalDescription').textContent = event.extendedProps.description || 'Sin descripción';
                
                // Mostrar el modal
                modal.style.display = 'block';
            }
        });
        
        calendar.render();
        
        // Cerrar el modal al hacer clic en la X
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    });
</script>
</body>
</html>