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
    public function obtenerDatosGraficaTrimestral($fechaInicio, $fechaFin)
    {
        try {
            // Validar fechas
            if (empty($fechaInicio) || empty($fechaFin)) {
                // Si no se proporcionan fechas, usar el trimestre actual
                $fechaInicio = date('Y-m-01', strtotime('-2 months'));
                $fechaFin = date('Y-m-t');
            }

            return $this->modelo->obtenerEstadisticasTrimestrales($fechaInicio, $fechaFin);
        } catch (Exception $e) {
            error_log("Error al   datos para gráfica trimestral: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    public function obtenerDetallesActividad($idActividad)
    {
        try {
            return $this->modelo->obtenerDetallesActividad($idActividad);
        } catch (Exception $e) {
            throw new Exception("Error al obtener los detalles de la actividad: " . $e->getMessage());
        }
    }

    public function obtenerActividadesParaCalendario()
    {
        try {
            $actividades = $this->modelo->obtenerActividadesParaCalendario();

            // Formatear para FullCalendar
            $eventos = [];
            foreach ($actividades as $actividad) {
                $evento = [
                    'id' => $actividad['idActividad'],
                    'title' => $actividad['title'],
                    'start' => $actividad['start'],
                    'end' => $actividad['end'],
                    'extendedProps' => [
                        'empleado' => $actividad['empleado'],
                        'categoria' => $actividad['categoria'],
                        'description' => $actividad['description'],
                        'estado' => $actividad['estado']
                    ]
                ];

                // Asignar clase CSS según estado
                switch ($actividad['estado']) {
                    case 'Completada':
                        $evento['className'] = 'event-completada';
                        $evento['color'] = '#10B981';
                        break;
                    case 'Cancelada':
                        $evento['className'] = 'event-cancelada';
                        $evento['color'] = '#EF4444';
                        break;
                    default: // En progreso
                        $evento['className'] = 'event-en-progreso';
                        $evento['color'] = '#F59E0B';
                }

                $eventos[] = $evento;
            }

            return $eventos;
        } catch (Exception $e) {
            error_log("Error al obtener actividades para calendario: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function manejarInsercionActividad()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
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

                // Validar límite
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
                    unset($_SESSION['form_data']);
                    header('Location: ../vistas/verActividades.php?mensaje=Actividad registrada exitosamente');
                    exit();
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
    public function manejarSolicitudesAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
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
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
                exit();
            }
        }
    }
    public function obtenerActividadesFiltradas($estado = 'todos', $fechaInicio = '', $fechaFin = '', $categoria = 'todas')
    {
        try {
            // Actualizar estados primero
            $this->actualizarEstadosActividades();

            error_log("Parámetros recibidos para filtros: Estado: $estado, Fecha Inicio: $fechaInicio, Fecha Fin: $fechaFin, Categoría: $categoria");
            $actividades = $this->modelo->obtenerActividadesFiltradas($estado, $fechaInicio, $fechaFin, $categoria);
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

    public function obtenerHistorialActividad($idActividad)
    {
        try {
            return $this->modelo->obtenerHistorialActividad($idActividad);
        } catch (Exception $e) {
            throw new Exception("Error al obtener el historial de la actividad: " . $e->getMessage());
        }
    }
}

// Al final del archivo controladorActividad.php
if (isset($_GET['action'])) {
    $controller = new controladorActividad();
    $controller->manejarSolicitudesAjax();
    exit();
}
