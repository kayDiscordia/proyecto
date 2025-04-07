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
                'contrasena' => $_POST['contrasena']
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

    public function obtenerActividadesPorEmpleado($idEmpleado) {
        try {
            // Validar el ID
            if (!is_numeric($idEmpleado)) {
                throw new Exception("ID de empleado no válido");
            }
    
            // Obtener información del empleado
            $empleado = $this->modelo->obtenerEmpleadoPorId($idEmpleado);
            
            // Obtener actividades
            $actividades = $this->modelo->obtenerActividadesPorEmpleado($idEmpleado);
            
            return [
                'empleado' => $empleado,
                'actividades' => $actividades
            ];
        } catch (Exception $e) {
            error_log("Error en controlador: " . $e->getMessage());
            throw new Exception("Error al obtener actividades del empleado");
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
            return $resultado['count'] > 0; // Devuelve `true` si la cédula ya existe
        } catch (Exception $e) {
            throw new Exception("Error al verificar la cédula: " . $e->getMessage());
        }
    }
}
?>