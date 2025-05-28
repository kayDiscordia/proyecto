<?php
session_start();
require_once '../controladores/controladorActividad.php';

$controlador = new controladorActividad();

// Obtener fechas y departamento del formulario si existen
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';
$idDepartamento = $_GET['idDepartamento'] ?? ($_SESSION['idDepartamento'] ?? null); // Usar departamento de sesión si no se especifica

// Obtener datos para la gráfica
$datosGrafica = $controlador->obtenerDatosGraficaSemanalMensual($fechaInicio, $fechaFin, $idDepartamento);
$actividades = $controlador->obtenerActividadesFiltradas(
    'todos', // estado
    $fechaInicio,
    $fechaFin,
    'todas', // categoría
    $idDepartamento
);

// Obtener el historial de cada actividad
$historialActividades = [];
foreach ($actividades as $actividad) {
    $historialActividades[$actividad['idActividad']] = $controlador->obtenerHistorialActividad($actividad['idActividad']);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semanal/Mensual de Actividades</title>
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="CSS/fontawesome.css">
    <script src="JS/chart.js"></script>
    <script src="JS/jspdf.js"></script>
    <script src="JS/jspdf-autotable.js"></script>
    <script defer src="JS/alpine.js"></script>
    <style>
        .grafica-container {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        #graficaActividades {
            display: block;
            max-height: 400px;
            width: 100%;
        }

        .resumen-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
            margin-top: 1rem;
        }

        .resumen-table th,
        .resumen-table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            background: white;
        }

        .resumen-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-progress {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .badge-init {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-delay {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }

        .tab-container {
            margin-bottom: 1rem;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            background-color: #f3f4f6;
            border: none;
            cursor: pointer;
            margin-right: 0.5rem;
            border-radius: 0.25rem;
        }

        .tab-button.active {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>

<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false, tipoGrafica: 'semanal' }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>

        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            <h2 class="text-2xl font-semibold mb-4">Reporte Semanal/Mensual de Actividades</h2>

            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filtrar por Período</h3>
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="fechaInicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" id="fechaInicio" name="fechaInicio" value="<?= htmlspecialchars($fechaInicio) ?>"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="fechaFin" class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input type="date" id="fechaFin" name="fechaFin" value="<?= htmlspecialchars($fechaFin) ?>"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>

                    </div>

                    <div class="md:col-span-3 flex justify-end space-x-3">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <button type="button" id="btnMesActual" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>Mes Actual
                        </button>
                        <button type="button" id="btnSemanaActual" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out flex items-center">
                            <i class="fas fa-calendar-week mr-2"></i>Semana Actual
                        </button>
                        <button type="button" id="btnLimpiarFiltro" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-eraser mr-2"></i>Limpiar Filtro
                        </button>
                        <button type="button" id="exportarPDF" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 ease-in-out flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen semanal/mensual -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Resumen de Actividades</h3>
                    <div class="tab-container">
                        <button @click="tipoGrafica = 'semanal'" :class="{ 'active': tipoGrafica === 'semanal' }" class="tab-button">
                            <i class="fas fa-calendar-week mr-1"></i> Semanal
                        </button>
                        <button @click="tipoGrafica = 'mensual'" :class="{ 'active': tipoGrafica === 'mensual' }" class="tab-button">
                            <i class="fas fa-calendar-alt mr-1"></i> Mensual
                        </button>
                    </div>
                </div>

                <?php if (isset($datosGrafica['error'])): ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500"><?= htmlspecialchars($datosGrafica['error']) ?></p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="resumen-table" id="resumenTable">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th>Completadas</th>
                                    <th>Canceladas</th>
                                    <th>En Progreso</th>
                                    <th>Por Iniciar</th>
                                    <th>En Retraso</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosGrafica as $periodo): ?>
                                    <tr>
                                        <td class="font-medium"><?= htmlspecialchars($periodo['periodo'] ?? '') ?></td>
                                        <td>
                                            <span class="badge badge-completed">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <?= htmlspecialchars($periodo['Completada'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-cancelled">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                <?= htmlspecialchars($periodo['Cancelada'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-progress">
                                                <i class="fas fa-spinner mr-1"></i>
                                                <?= htmlspecialchars($periodo['En progreso'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-init">
                                                <i class="fas fa-hourglass-start mr-1"></i>
                                                <?= htmlspecialchars($periodo['Por iniciar'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-delay">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <?= htmlspecialchars($periodo['En retraso'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="font-medium"><?= htmlspecialchars($periodo['total'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Gráfica -->
            <div class="grafica-container">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Estadísticas de Actividades</h3>
                </div>

                <canvas id="graficaActividades"></canvas>
            </div>
        </main>
    </div>
    <script>
        const actividades = <?= json_encode($actividades) ?>;
        const historialActividades = <?= json_encode($historialActividades) ?>;
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración del botón de mes actual
            document.getElementById('btnMesActual').addEventListener('click', function() {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

                document.getElementById('fechaInicio').valueAsDate = firstDay;
                document.getElementById('fechaFin').valueAsDate = lastDay;
                document.querySelector('form').submit();
            });

            // Configuración del botón de semana actual
            document.getElementById('btnSemanaActual').addEventListener('click', function() {
                const now = new Date();
                const firstDay = new Date(now);
                firstDay.setDate(now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1)); // Lunes de esta semana
                const lastDay = new Date(firstDay);
                lastDay.setDate(firstDay.getDate() + 6); // Domingo de esta semana

                document.getElementById('fechaInicio').valueAsDate = firstDay;
                document.getElementById('fechaFin').valueAsDate = lastDay;
                document.querySelector('form').submit();
            });

            // Configurar botón para limpiar el filtro
            document.getElementById('btnLimpiarFiltro').addEventListener('click', function() {
                document.getElementById('fechaInicio').value = '';
                document.getElementById('fechaFin').value = '';
                document.getElementById('idDepartamento').value = '';
                document.querySelector('form').submit();
            });

            // Configurar gráfica
            const ctx = document.getElementById('graficaActividades').getContext('2d');
            const datosGrafica = <?= json_encode(isset($datosGrafica['error']) ? [] : $datosGrafica) ?>;

            let chart; // Variable para almacenar la gráfica

            function renderChart(tipo) {
                if (chart) {
                    chart.destroy();
                }

                if (datosGrafica.length === 0) {
                    ctx.font = '16px Arial';
                    ctx.fillStyle = '#6B7280';
                    ctx.textAlign = 'center';
                    ctx.fillText('No hay datos para mostrar', ctx.canvas.width / 2, ctx.canvas.height / 2);
                    return;
                }

                const labels = datosGrafica.map(item => item.periodo);
                const datasets = [{
                        label: 'Completadas',
                        data: datosGrafica.map(item => item.Completada || 0),
                        backgroundColor: '#10B981',
                        borderColor: '#047857',
                        borderWidth: 1
                    },
                    {
                        label: 'Canceladas',
                        data: datosGrafica.map(item => item.Cancelada || 0),
                        backgroundColor: '#EF4444',
                        borderColor: '#B91C1C',
                        borderWidth: 1
                    },
                    {
                        label: 'En Progreso',
                        data: datosGrafica.map(item => item['En progreso'] || 0),
                        backgroundColor: '#F59E0B',
                        borderColor: '#B45309',
                        borderWidth: 1
                    },
                    {
                        label: 'Por Iniciar',
                        data: datosGrafica.map(item => item['Por iniciar'] || 0),
                        backgroundColor: '#3B82F6',
                        borderColor: '#1E40AF',
                        borderWidth: 1
                    },
                    {
                        label: 'En Retraso',
                        data: datosGrafica.map(item => item['En retraso'] || 0),
                        backgroundColor: '#F87171',
                        borderColor: '#B91C1C',
                        borderWidth: 1
                    }
                ];

                chart = new Chart(ctx, {
                    type: tipo === 'semanal' ? 'bar' : 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                stacked: tipo === 'semanal' ? false : true,
                                title: {
                                    display: true,
                                    text: tipo === 'semanal' ? 'Semanas' : 'Meses'
                                }
                            },
                            y: {
                                stacked: tipo === 'semanal' ? false : true,
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Cantidad de Actividades'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });
            }

            // Renderizar gráfica inicial
            renderChart('semanal');

            // Escuchar cambios en las pestañas
            document.addEventListener('alpine:init', () => {
                Alpine.data('graficaData', () => ({
                    tipoGrafica: 'semanal',
                    init() {
                        this.$watch('tipoGrafica', (value) => {
                            renderChart(value);
                        });
                    }
                }));
            });

            // Configurar botón para exportar el PDF
            document.getElementById('exportarPDF').addEventListener('click', function() {
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF();

                let fechaInicio = document.getElementById('fechaInicio').value;
                let fechaFin = document.getElementById('fechaFin').value;
                let departamentoSelect = document.getElementById('idDepartamento');
                let departamentoTexto = departamentoSelect ? departamentoSelect.options[departamentoSelect.selectedIndex].text : 'Todos';

                // Si no hay filtro, calcular fechas del mes actual
                if (!fechaInicio || !fechaFin) {
                    const hoy = new Date();
                    const mes = hoy.getMonth();
                    const anio = hoy.getFullYear();
                    const primerDia = new Date(anio, mes, 1);
                    const ultimoDia = new Date(anio, mes + 1, 0);

                    const pad = n => n < 10 ? '0' + n : n;
                    fechaInicio = `${primerDia.getFullYear()}-${pad(primerDia.getMonth() + 1)}-${pad(primerDia.getDate())}`;
                    fechaFin = `${ultimoDia.getFullYear()}-${pad(ultimoDia.getMonth() + 1)}-${pad(ultimoDia.getDate())}`;
                }

                // Título del reporte
                pdf.setFontSize(16);
                pdf.text('Reporte de Actividades', 10, 10);

                // Fechas y departamento del reporte
                pdf.setFontSize(12);
                pdf.text(`Fecha Inicio: ${fechaInicio}`, 10, 20);
                pdf.text(`Fecha Fin: ${fechaFin}`, 10, 30);
                pdf.text(`Departamento: ${departamentoTexto || 'Todos'}`, 10, 40);

                // Resumen
                pdf.text('Resumen de Actividades', 10, 50);

                const resumenTable = document.getElementById('resumenTable');
                const resumenRows = [...resumenTable.rows].map(row => [...row.cells].map(cell => cell.innerText));
                pdf.autoTable({
                    startY: 55,
                    head: [resumenRows[0]],
                    body: resumenRows.slice(1),
                    styles: {
                        halign: 'center'
                    },
                    columnStyles: {
                        1: {
                            fillColor: [220, 252, 231]
                        }, // Verde para completadas
                        2: {
                            fillColor: [254, 226, 226]
                        }, // Rojo para canceladas
                        3: {
                            fillColor: [254, 249, 195]
                        }, // Amarillo para en progreso
                        4: {
                            fillColor: [219, 234, 254]
                        }, // Azul para por iniciar
                        5: {
                            fillColor: [254, 226, 226]
                        }, // Rojo claro para en retraso
                    },
                });

                // Tabla de actividades
                let y = pdf.lastAutoTable ? pdf.lastAutoTable.finalY + 10 : 70;
                if (actividades.length > 0) {
                    pdf.text('Detalle de Actividades', 10, y);
                    y += 5;
                    pdf.autoTable({
                        startY: y,
                        head: [
                            ["#", "Categoría", "Descripción", "Empleado", "Fecha Inicio", "Fecha Fin", "Estado"]
                        ],
                        body: actividades.map((a, i) => [
                            i + 1,
                            a.categoriaActividad,
                            a.descripcionActividad,
                            a.nombreEmpleado,
                            a.fechaInicio,
                            a.fechaCulminacion,
                            a.estadoActividad
                        ]),
                        styles: {
                            fontSize: 9
                        },
                        headStyles: {
                            fillColor: [59, 130, 246]
                        }
                    });
                    y = pdf.lastAutoTable.finalY + 10;
                }

                if (chart) {
                    pdf.addPage();
                    pdf.text('Gráfica de Actividades', 10, 10);
                    const chartImage = chart.toBase64Image();
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const margin = 10;
                    const imgWidth = pageWidth - margin * 2;
                    const imgHeight = (chart.canvas.height / chart.canvas.width) * imgWidth;
                    pdf.addImage(chartImage, 'PNG', margin, 20, imgWidth, imgHeight);
                }

                // Historial de actividades
                if (Object.keys(historialActividades).length > 0) {
                    pdf.addPage();
                    pdf.text('Historial de Actividades', 10, 10);
                    let yHist = 20;
                    actividades.forEach((a, i) => {
                        const historial = historialActividades[a.idActividad] || [];
                        pdf.setFontSize(11);
                        pdf.text(`${i + 1}. ${a.descripcionActividad} (${a.nombreEmpleado})`, 10, yHist);
                        yHist += 6;
                        if (historial.length > 0) {
                            pdf.autoTable({
                                startY: yHist,
                                head: [
                                    ["Fecha", "Evento", "Detalles"]
                                ],
                                body: historial.map(h => [
                                    h.fecha,
                                    h.evento,
                                    h.detalles
                                ]),
                                styles: {
                                    fontSize: 8
                                },
                                headStyles: {
                                    fillColor: [16, 185, 129]
                                }
                            });
                            yHist = pdf.lastAutoTable.finalY + 8;
                        } else {
                            pdf.setFontSize(9);
                            pdf.text('Sin historial.', 12, yHist);
                            yHist += 8;
                        }
                        if (yHist > 260) {
                            pdf.addPage();
                            yHist = 10;
                        }
                    });
                }
                pdf.save('reporte_actividades.pdf');
            });
        });
    </script>
</body>

</html>