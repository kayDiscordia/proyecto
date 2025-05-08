<?php
session_start();
require_once '../controladores/controladorActividad.php';

$controlador = new controladorActividad();

// Obtener fechas del formulario si existen
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';

// Obtener datos para la gráfica
$datosGrafica = $controlador->obtenerDatosGraficaTrimestral($fechaInicio, $fechaFin);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Trimestral de Actividades</title>
    <link rel="stylesheet" href="CSS/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .grafica-container {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        
        .resumen-table th, .resumen-table td {
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
    </style>
</head>
<body class="bg-[#E8EEFF]">
    <div class="flex h-screen" x-data="{ isCollapsed: false }">
        <!-- Sidebar -->
        <?php include 'modulos/sidebar.php' ?>
        
        <!-- Main container -->
        <main class="flex-1 p-6 overflow-y-auto bg-e8eeff">
            <h2 class="text-2xl font-semibold mb-4">Reporte Trimestral de Actividades</h2>
            
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filtrar por Trimestre</h3>
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
                    
                    <div class="md:col-span-3 flex justify-end space-x-3">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <button type="button" id="btnTrimestreActual" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300 ease-in-out flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>Trimestre Actual
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

            <!-- Resumen trimestral -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Resumen Trimestral</h3>
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
                                    <th>Trimestre</th>
                                    <th>Completadas</th>
                                    <th>Canceladas</th>
                                    <th>En Progreso</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosGrafica as $trimestre): ?>
                                    <tr>
                                        <td class="font-medium"><?= htmlspecialchars($trimestre['trimestre']) ?></td>
                                        <td>
                                            <span class="badge badge-completed">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <?= htmlspecialchars($trimestre['Completada']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-cancelled">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                <?= htmlspecialchars($trimestre['Cancelada']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-progress">
                                                <i class="fas fa-spinner mr-1"></i>
                                                <?= htmlspecialchars($trimestre['En progreso']) ?>
                                            </span>
                                        </td>
                                        <td class="font-medium"><?= htmlspecialchars($trimestre['total']) ?></td>
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
                    <h3 class="text-lg font-medium text-gray-900">Estadísticas Trimestrales</h3>
                </div>
                
                <canvas id="graficaActividades"></canvas>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración del botón de trimestre actual
            document.getElementById('btnTrimestreActual').addEventListener('click', function() {
                const now = new Date(); // Fecha actual
                const firstMonth = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1); // Primer día del trimestre actual
                const lastMonth = new Date(firstMonth.getFullYear(), firstMonth.getMonth() + 2, 1); // Primer día del último mes del trimestre
                const lastDay = new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0); // Último día del trimestre
                
                // Si el usuario no ha ingresado una fecha de inicio, usar el inicio del trimestre actual
                if (!document.getElementById('fechaInicio').value) {
                    document.getElementById('fechaInicio').valueAsDate = firstMonth; // Fecha de inicio del trimestre actual
                }
                
                // Fecha de fin siempre será el fin del trimestre actual
                document.getElementById('fechaFin').valueAsDate = lastDay;
                
                // Enviar el formulario automáticamente
                document.querySelector('form').submit();
            });

            // Configurar botón para limpiar el filtro
            document.getElementById('btnLimpiarFiltro').addEventListener('click', function() {
                document.getElementById('fechaInicio').value = '';
                document.getElementById('fechaFin').value = '';
                document.querySelector('form').submit();
            });

            // Configurar gráfica
            const ctx = document.getElementById('graficaActividades').getContext('2d');
            const datosGrafica = <?= json_encode(isset($datosGrafica['error']) ? [] : $datosGrafica) ?>;
            
            let chart; // Variable para almacenar la gráfica
            if (datosGrafica.length > 0) {
                const labels = datosGrafica.map(item => item.trimestre);
                const completadas = datosGrafica.map(item => item.Completada);
                const canceladas = datosGrafica.map(item => item.Cancelada);
                const enProgreso = datosGrafica.map(item => item['En progreso']);
                
                const totalData = [
                    completadas.reduce((a, b) => a + b, 0),
                    canceladas.reduce((a, b) => a + b, 0),
                    enProgreso.reduce((a, b) => a + b, 0),
                ];

                chart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Completadas', 'Canceladas', 'En Progreso'],
                        datasets: [
                            {
                                data: totalData,
                                backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
                                borderColor: ['#047857', '#B91C1C', '#B45309'],
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });

                // Configurar botón para exportar el PDF
                document.getElementById('exportarPDF').addEventListener('click', function() {
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF();

                    const fechaInicio = document.getElementById('fechaInicio').value || 'N/A';
                    const fechaFin = document.getElementById('fechaFin').value || 'N/A';

                    // Título del reporte
                    pdf.setFontSize(16);
                    pdf.text('Reporte Trimestral de Actividades', 10, 10);

                    // Fechas del reporte
                    pdf.setFontSize(12);
                    pdf.text(`Fecha Inicio: ${fechaInicio}`, 10, 20);
                    pdf.text(`Fecha Fin: ${fechaFin}`, 10, 30);

                    // Resumen Trimestral
                    pdf.text('Resumen Trimestral', 10, 40);

                    const resumenTable = document.getElementById('resumenTable');
                    const resumenRows = [...resumenTable.rows].map(row => [...row.cells].map(cell => cell.innerText));
                    pdf.autoTable({
                        startY: 45,
                        head: [resumenRows[0]],
                        body: resumenRows.slice(1),
                        styles: {
                            halign: 'center',
                        },
                        columnStyles: {
                            1: { fillColor: [220, 252, 231] }, // Verde para completadas
                            2: { fillColor: [254, 226, 226] }, // Rojo para canceladas
                            3: { fillColor: [254, 249, 195] }, // Amarillo para en progreso
                        },
                    });

                    // Agregar gráfica inmediatamente después de la tabla
                    const finalY = pdf.lastAutoTable.finalY + 10; // Espacio después de la tabla
                    if (chart) {
                        const chartImage = chart.toBase64Image();
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const imgWidth = 150; // Ancho máximo de la gráfica (más grande)
                        const imgHeight = (chart.canvas.height / chart.canvas.width) * imgWidth; // Altura proporcional
                        const centerX = (pageWidth - imgWidth) / 2; // Centrar horizontalmente
                        pdf.addImage(chartImage, 'PNG', centerX, finalY, imgWidth, imgHeight);
                    }

                    pdf.save('reporte_trimestral.pdf');
                });
            } else {
                ctx.font = '16px Arial';
                ctx.fillStyle = '#6B7280';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos para mostrar', ctx.canvas.width / 2, ctx.canvas.height / 2);
            }
        });
    </script>
</body>
</html>