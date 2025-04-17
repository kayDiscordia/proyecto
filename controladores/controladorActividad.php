<?php
require_once '../modelos/modeloActividad.php';

class controladorActividad {
    private $modelo;

    public function __construct() {
        $this->modelo = new modeloActividad();
    }

    public function manejarInsercionActividad() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar y sanitizar inputs
                $descripcionActividad = trim($_POST['descripcionActividad']);
                $fechaInicio = $_POST['fechaInicio'];
                $fechaCulminacion = $_POST['fechaCulminacion'];
                $idEmpleado = (int)$_POST['idEmpleado'];
                $idCategoria = (int)$_POST['idCategoria'];

                // Validaciones básicas
                if (empty($descripcionActividad) || empty($fechaInicio) || empty($fechaCulminacion)) {
                    throw new Exception("Todos los campos son obligatorios");
                }

                if ($idCategoria <= 0) {
                    throw new Exception("Debe seleccionar una categoría válida");
                }

                // Insertar la actividad
                $resultado = $this->modelo->insertarActividad(
                    $descripcionActividad,
                    $fechaInicio,
                    $fechaCulminacion,
                    $idEmpleado,
                    $idCategoria
                );

                if ($resultado === true) {
                    header('Location: ../vistas/verActividades.php?mensaje=Actividad registrada exitosamente');
                    exit();
                }
            } catch (Exception $e) {
                // Redirigir con el error
                header('Location: ../vistas/registrarActividades.php?error=' . urlencode($e->getMessage()));
                exit();
            }
        }
    }

    public function obtenerActividades() {
        try {
            return $this->modelo->obtenerActividades();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function cancelarActividad($idActividad, $descripcionCancelacion) {
        try {
            return $this->modelo->cancelarActividad($idActividad, $descripcionCancelacion);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function culminarActividad($idActividad, $descripcionCulminacion) {
        try {
            return $this->modelo->culminarActividad($idActividad, $descripcionCulminacion);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function obtenerCategoriasParaFormulario() {
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
    public function obtenerCategoriasPorDepartamento($idDepartamento) {
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
    public function manejarSolicitudCategorias() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['idDepartamento'])) {
            header('Content-Type: application/json');
            echo json_encode($this->obtenerCategoriasPorDepartamento($_GET['idDepartamento']));
            exit();
        }
    }
    /**
     * Obtiene actividades con filtros
     */
    public function obtenerActividadesFiltradas($estado = 'todos', $fechaInicio = '', $fechaFin = '', $categoria = 'todas') {
        try {
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
    public function obtenerCategoriasActividades() {
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
    private function registrarError($mensaje) {
        error_log("Error en controladorActividad: " . $mensaje);
    }
}