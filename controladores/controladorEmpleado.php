<?php
require_once '../modelos/modeloEmpleado.php';

class controladorEmpleado {
    private $modelo;

    public function __construct() {
        $this->modelo = new modeloEmpleado();
    }

   public function insercionEmpleado() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'cedula' => $_POST['cedula'],
            'idCargo' => $_POST['idCargo'],
            'idDepartamento' => $_POST['idDepartamento'],
            'usuarioEmpleado' => $_POST['usuarioEmpleado'],
            'contrasena' => $_POST['contrasena'],
            'idRol' => 2, // Rol fijo para empleados
            'idEstado' => 1 // Estado Activo por defecto
        ];
        
        $resultado = $this->modelo->crearEmpleado($data);

        if ($resultado === true) {
            header('Location: ../vistas/verEmpleado.php');
        } else {
            echo '<div class="error">' . htmlspecialchars($resultado) . '</div>';
        }
    }
}


    public function obtenerEmpleados() {
        try {
            return $this->modelo->obtenerEmpleados();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function obtenerCargos() {
        try {
            return $this->modelo->obtenerCargos();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function obtenerDepartamentos() {
        try {
            return $this->modelo->obtenerDepartamentos();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function obtenerEmpleadoPorId($idEmpleado) {
        try {
            return $this->modelo->obtenerEmpleadoPorId($idEmpleado);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    

    public function actualizarEmpleado($data) {
        try {
            return $this->modelo->actualizarEmpleado($data);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    public function obtenerEstadosEmpleados() {
    try {
        return $this->modelo->obtenerEstadosEmpleados();
    } catch (Exception $e) {
        error_log("Error al obtener estados de empleados: " . $e->getMessage());
        return [];
    }
}
    public function obtenerActividadesPorEmpleado($idEmpleado, $filtroEstado = 'todos', $fechaInicio = null, $fechaFin = null) {
    try {
        if (!is_numeric($idEmpleado)) {
            throw new Exception("ID de empleado no válido");
        }

        // Obtener información básica del empleado
        $empleado = $this->modelo->obtenerEmpleadoPorId($idEmpleado);
        
        // Validar y procesar parámetros de filtrado
        $filtros = array();
        
        // Filtro por estado
        if ($filtroEstado !== 'todos' && in_array($filtroEstado, array('Completada', 'En progreso', 'Cancelada'))) {
            $filtros['estado'] = $filtroEstado;
        }
        
        // Filtro por rango de fechas
        if (!empty($fechaInicio)) {
            if (!DateTime::createFromFormat('Y-m-d', $fechaInicio)) {
                throw new Exception("Formato de fecha inicio no válido");
            }
            $filtros['fecha_inicio'] = $fechaInicio;
        }
        
        if (!empty($fechaFin)) {
            if (!DateTime::createFromFormat('Y-m-d', $fechaFin)) {
                throw new Exception("Formato de fecha fin no válido");
            }
            $filtros['fecha_fin'] = $fechaFin;
        }
        
        // Validar que fecha fin no sea menor que fecha inicio
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            if (strtotime($filtros['fecha_fin']) < strtotime($filtros['fecha_inicio'])) {
                throw new Exception("La fecha fin no puede ser anterior a la fecha inicio");
            }
        }
        
        // Obtener actividades con filtros aplicados
        $actividades = $this->modelo->obtenerActividadesPorEmpleado($idEmpleado, $filtros);
        
        return array(
            'empleado' => $empleado,
            'actividades' => $actividades
        );
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        throw new Exception("Error al obtener actividades del empleado: " . $e->getMessage());
    }
}

    public function verificarCedulaAjax() {
        if (isset($_POST['cedula'])) {
            $cedula = $_POST['cedula'];
            try {
                $existe = $this->verificarCedula($cedula);
                echo json_encode(['existe' => $existe]);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'No se proporcionó una cédula.']);
        }
    }
    
    private function verificarCedula($cedula) {
        try {
            $resultado = $this->modelo->verificarCedula($cedula);
            return $resultado['count'] > 0;
        } catch (Exception $e) {
            throw new Exception("Error al verificar la cédula: " . $e->getMessage());
        }
    }
}