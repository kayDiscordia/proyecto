<?php
require_once '../modelos/modeloActividad.php';

class controladorActividad
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new modeloActividad();
    }

    /**
     * Obtiene datos para la gráfica trimestral.
     */


     public function obtenerDetallesActividad($idActividad)
{
    try {
        // Obtener los detalles básicos de la actividad
        $detalles = $this->modelo->obtenerDetallesActividad($idActividad);
        
        // Obtener los archivos adjuntos
        $archivos = $this->modelo->obtenerArchivosPorActividad($idActividad);
        
        // Agregar los archivos a los detalles
        $detalles['archivosAdjuntos'] = $archivos;
        
        return $detalles;
    } catch (Exception $e) {
        throw new Exception("Error al obtener los detalles de la actividad: " . $e->getMessage());
    }
}

    public function obtenerDatosGraficaSemanalMensual($fechaInicio, $fechaFin, $idDepartamento = null)
    {
        try {
            // Validar fechas
            if (empty($fechaInicio)) { // <-- Paréntesis corregido aquí
                $fechaInicio = date('Y-m-01', strtotime('-1 month'));
            }
            if (empty($fechaFin)) {
                $fechaFin = date('Y-m-t');
            }

            return $this->modelo->obtenerEstadisticasSemanalesMensuales($fechaInicio, $fechaFin, $idDepartamento);
        } catch (Exception $e) {
            error_log("Error al obtener datos para gráfica semanal/mensual: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function manejarInsercionActividad() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Obtener datos del formulario
                $nombreActividad = trim($_POST['nombreActividad']);
                $descripcionActividad = trim($_POST['descripcionActividad']);
                $fechaInicio = $_POST['fechaInicio'];
                $fechaCulminacion = $_POST['fechaCulminacion'];
                $idEmpleado = (int)$_POST['idEmpleado'];
                $idCategoria = (int)$_POST['idCategoria'];
    
                // Validaciones básicas
                if (empty($nombreActividad) || empty($descripcionActividad) || empty($fechaInicio) || empty($fechaCulminacion)) {
                    throw new Exception("Todos los campos son obligatorios");
                }
                if (strlen($nombreActividad) > 40) {
                    throw new Exception("El nombre de la actividad no puede exceder los 40 caracteres");
                }
                if ($idCategoria <= 0) throw new Exception("Debe seleccionar una categoría válida");
                if ($idEmpleado <= 0) throw new Exception("Debe seleccionar un empleado válido");
                if (strtotime($fechaCulminacion) < strtotime($fechaInicio)) {
                    throw new Exception("La fecha de culminación no puede ser anterior a la fecha de inicio");
                }
    
                // Validar límite de actividades
                $limite = $this->modelo->obtenerLimiteActividadesPorEmpleado($idEmpleado);
                $actividadesActuales = $this->modelo->contarActividadesActivasPorEmpleado($idEmpleado);
    
                $_SESSION['form_data'] = $_POST;
    
                if ($actividadesActuales >= $limite) {
                    throw new Exception("Este empleado ya tiene el máximo de actividades asignadas ($limite). No se puede asignar más.");
                }
    
                // Insertar la actividad
                $resultado = $this->modelo->insertarActividad(
                    $nombreActividad,
                    $descripcionActividad,
                    $fechaInicio,
                    $fechaCulminacion,
                    $idEmpleado,
                    $idCategoria
                );
    
                if ($resultado === true) {
                    $idActividad = $this->modelo->obtenerUltimoIdInsertado();
                    
                    // Procesar archivos adjuntos solo si se subieron
                    if (isset($_FILES['archivosAdjuntos']) && !empty($_FILES['archivosAdjuntos']['name'][0])) {
                        $this->procesarArchivosAdjuntos($idActividad);
                    }
                    
                    unset($_SESSION['form_data']);
                    header('Location: ../vistas/verActividades.php?mensaje=Actividad registrada exitosamente');
                    exit();
                } else {
                    throw new Exception("Error al insertar la actividad en la base de datos");
                }
                
            } catch (Exception $e) {
                error_log("Error al insertar actividad: " . $e->getMessage());
                header('Location: ../vistas/registrarActividades.php?error=' . urlencode($e->getMessage()));
                exit();
            }
        }
    }
    public function obtenerActividades()
    {
        try {
            return $this->modelo->obtenerActividades();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function cancelarActividad($idActividad, $descripcionCancelacion)
    {
        try {
            return $this->modelo->cancelarActividad($idActividad, $descripcionCancelacion);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria)
    {
        $modeloActividad = new modeloActividad();
        return $modeloActividad->editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria);
    }

    public function culminarActividad($idActividad, $descripcionCulminacion)
    {
        try {
            return $this->modelo->culminarActividad($idActividad, $descripcionCulminacion);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function obtenerCategoriasParaFormulario()
    {
        try {
            return $this->modelo->obtenerTodasCategorias();
        } catch (Exception $e) {
            // Loggear el error si es necesario
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene categorías por departamento para el endpoint AJAX
     */
    public function obtenerCategoriasPorDepartamento($idDepartamento)
    {
        try {
            // Validación básica
            if (!is_numeric($idDepartamento)) {
                throw new Exception("ID de departamento no válido");
            }

            return $this->modelo->obtenerCategoriasPorDepartamento((int)$idDepartamento);
        } catch (Exception $e) {
            error_log("Error en controlador: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Método para manejar la solicitud AJAX de categorías
     */
    public function manejarSolicitudCategorias()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['idDepartamento'])) {
            header('Content-Type: application/json');
            echo json_encode($this->obtenerCategoriasPorDepartamento($_GET['idDepartamento']));
            exit();
        }
    }

    public function obtenerDatosGraficaTrimestral($fechaInicio, $fechaFin, $idDepartamento = null)
    {
        try {
            // Validar fechas
            if (empty($fechaInicio)) {
                $fechaInicio = date('Y-m-01', strtotime('-3 months'));
            }
            if (empty($fechaFin)) {
                $fechaFin = date('Y-m-t');
            }

            return $this->modelo->obtenerEstadisticasTrimestrales($fechaInicio, $fechaFin, $idDepartamento);
        } catch (Exception $e) {
            error_log("Error al obtener datos para gráfica trimestral: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function actualizarEstadosActividades()
    {
        try {
            return $this->modelo->actualizarEstadosActividades();
        } catch (Exception $e) {
            error_log("Error al actualizar estados de actividades: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerInfoLimiteActividades($idEmpleado)
    {
        try {
            if (!is_numeric($idEmpleado)) {
                throw new Exception("ID de empleado no válido");
            }

            $limite = $this->modelo->obtenerLimiteActividadesPorEmpleado($idEmpleado);
            $actividadesActuales = $this->modelo->contarActividadesActivasPorEmpleado($idEmpleado);

            return [
                'limite' => $limite,
                'actividadesActuales' => $actividadesActuales,
                'disponibles' => $limite - $actividadesActuales
            ];
        } catch (Exception $e) {
            error_log("Error al obtener límite de actividades: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // Y modifica el método manejarSolicitudCategorias para manejar también solicitudes de límite
    public function obtenerActividadesFiltradas($estado = 'todos', $fechaInicio = '', $fechaFin = '', $categoria = 'todas')
    {
        try {
            // Actualizar estados primero
            $this->actualizarEstadosActividades();

            // Obtener el ID del departamento del usuario de la sesión
            $idDepartamento = $_SESSION['idDepartamento'] ?? null;

            error_log("Parámetros recibidos para filtros: Estado: $estado, Fecha Inicio: $fechaInicio, Fecha Fin: $fechaFin, Categoría: $categoria");

            $actividades = $this->modelo->obtenerActividadesFiltradas(
                $estado,
                $fechaInicio,
                $fechaFin,
                $categoria,
                $idDepartamento
            );

            error_log("Actividades obtenidas del modelo: " . print_r($actividades, true));
            return $actividades;
        } catch (Exception $e) {
            $this->registrarError($e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    /**
     * Obtiene todas las categorías para el filtro o formulario
     */
    public function obtenerCategoriasActividades()
    {
        try {
            return $this->modelo->obtenerTodasCategorias();
        } catch (Exception $e) {
            $this->registrarError($e->getMessage());
            return [];
        }
    }

    /**
     * Método privado para registrar errores en el log
     */
    private function registrarError($mensaje)
    {
        error_log("Error en controladorActividad: " . $mensaje);
    }

    public function obtenerDepartamentos()
    {
        try {
            return $this->modelo->obtenerDepartamentos();
        } catch (Exception $e) {
            error_log("Error al obtener departamentos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEstadisticasActividades($idDepartamento = null)
    {
        try {
            // Actualizar estados primero
            $this->actualizarEstadosActividades();

            $estadisticas = $this->modelo->obtenerEstadisticasActividades($idDepartamento);

            return [
                'total' => $estadisticas['total'] ?? 0,
                'completadas' => $estadisticas['Completada'] ?? 0,
                'en_progreso' => $estadisticas['En progreso'] ?? 0,
                'por_iniciar' => $estadisticas['Por Iniciar'] ?? 0,
                'retraso' => $estadisticas['Retraso'] ?? 0,
                'canceladas' => $estadisticas['Cancelada'] ?? 0
            ];
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'completadas' => 0,
                'en_progreso' => 0,
                'por_iniciar' => 0,
                'retraso' => 0,
                'canceladas' => 0
            ];
        }
    }

    // Actualiza el método manejarSolicitudesAjax para incluir las nuevas acciones
    public function manejarSolicitudesAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'obtenerActividadesCalendario':
                            $idDepartamento = isset($_GET['idDepartamento']) ? (int)$_GET['idDepartamento'] : null;
                            echo json_encode($this->obtenerActividadesParaCalendario($idDepartamento));
                            exit();

                        case 'obtenerDetallesActividad':
                            if (!isset($_GET['idActividad'])) {
                                throw new Exception("ID de actividad no proporcionado");
                            }
                            $idActividad = (int)$_GET['idActividad'];
                            echo json_encode($this->obtenerDetallesActividad($idActividad));
                            exit();

                        case 'obtenerHistorialActividad':
                            if (!isset($_GET['idActividad'])) {
                                throw new Exception("ID de actividad no proporcionado");
                            }
                            $idActividad = (int)$_GET['idActividad'];
                            echo json_encode($this->obtenerHistorialActividad($idActividad));
                            exit();

                        case 'obtenerEstadisticasActividades':
                            $idDepartamento = isset($_GET['idDepartamento']) ? (int)$_GET['idDepartamento'] : null;
                            echo json_encode($this->obtenerEstadisticasActividades($idDepartamento));
                            exit();

                        case 'obtenerCategorias':
                            if (isset($_GET['idDepartamento'])) {
                                echo json_encode($this->obtenerCategoriasPorDepartamento($_GET['idDepartamento']));
                                exit();
                            }
                            break;

                        case 'obtenerLimiteActividades':
                            if (isset($_GET['idEmpleado'])) {
                                $idEmpleado = (int)$_GET['idEmpleado'];
                                $limite = $this->modelo->obtenerLimiteActividadesPorEmpleado($idEmpleado);
                                $actividadesActuales = $this->modelo->contarActividadesActivasPorEmpleado($idEmpleado);

                                echo json_encode([
                                    'limite' => $limite,
                                    'actividadesActuales' => $actividadesActuales,
                                    'disponibles' => $limite - $actividadesActuales
                                ]);
                                exit();
                            }
                            break;
                    }
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
                exit();
            }
        }
    }

    public function obtenerActividadesParaCalendario($idDepartamento = null)
    {
        try {
            $eventos = $this->modelo->obtenerActividadesParaCalendario($idDepartamento);

            // Asignar color y clase según estado
            foreach ($eventos as &$evento) {
                $estado = isset($evento['extendedProps']['estado']) ? trim($evento['extendedProps']['estado']) : '';
                switch ($estado) {
                    case 'Completada':
                        $evento['className'] = 'event-completada';
                        $evento['color'] = '#10B981';
                        break;
                    case 'Cancelada':
                        $evento['className'] = 'event-cancelada';
                        $evento['color'] = '#EF4444';
                        break;
                    case 'Retraso':
                        $evento['className'] = 'event-retraso';
                        $evento['color'] = '#3B82F6';
                        break;
                    case 'Por Iniciar':
                        $evento['className'] = 'event-por-iniciar';
                        $evento['color'] = '#FBBF24';
                        break;
                    case 'En progreso':
                        $evento['className'] = 'event-en-progreso';
                        $evento['color'] = '#F97316';
                        break;
                    default:
                        $evento['className'] = 'event-otro';
                        $evento['color'] = '#6B7280';
                }
            }
            unset($evento); // Buenas prácticas

            return $eventos;
        } catch (Exception $e) {
            error_log("Error al obtener actividades para calendario: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function obtenerHistorialActividad($idActividad)
    {
        try {
            return $this->modelo->obtenerHistorialActividad($idActividad);
        } catch (Exception $e) {
            throw new Exception("Error al obtener el historial de la actividad: " . $e->getMessage());
        }
    }

    /**
     * Procesa los archivos adjuntos subidos y los asocia a la actividad.
     */
    private function procesarArchivosAdjuntos($idActividad)
    {
        $rutaDestino = '../archivos_adjuntos/';
        if (!is_dir($rutaDestino)) {
            mkdir($rutaDestino, 0777, true);
        }

        foreach ($_FILES['archivosAdjuntos']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $nombreArchivo = basename($_FILES['archivosAdjuntos']['name'][$key]);
                $tipoArchivo = $_FILES['archivosAdjuntos']['type'][$key];
                $tamanoArchivo = $_FILES['archivosAdjuntos']['size'][$key];
                $rutaArchivo = $rutaDestino . uniqid() . '_' . $nombreArchivo;

                if (move_uploaded_file($tmp_name, $rutaArchivo)) {
                    // Guarda la referencia en la base de datos
                    $this->modelo->guardarArchivoActividad(
                        $idActividad,
                        $nombreArchivo,
                        $tipoArchivo,
                        $tamanoArchivo,
                        $rutaArchivo
                    );
                }
            }
        }
    }

    
}

// Al final del archivo controladorActividad.php
if (isset($_GET['action'])) {
    $controller = new controladorActividad();
    $controller->manejarSolicitudesAjax();
    exit();
}
