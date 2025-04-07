<?php
require_once 'Database.php';

class modeloEmpleado {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function crearEmpleado($data) {
        $sql = "INSERT INTO empleados (nombres, apellidos, cedula, idCargo, idDepartamento, usuarioEmpleado, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("sssisss", $data['nombres'], $data['apellidos'], $data['cedula'], $data['idCargo'], $data['idDepartamento'], $data['usuarioEmpleado'], $data['contrasena']);
        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Error al crear empleado: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerEmpleados() {
        $query = "SELECT e.idEmpleado, e.nombres, e.apellidos, e.cedula, e.usuarioEmpleado, e.contrasena, 
                         c.nombreCargo AS cargo_nombre, d.nombreDepartamentos AS departamento_nombre
                  FROM empleados e
                  LEFT JOIN cargos c ON e.idCargo = c.idCargo
                  LEFT JOIN departamentos d ON e.idDepartamento = d.idDepartamentos";
        $resultado = $this->db->getConnection()->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Error al obtener empleados: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerCargos() {
        $query = "SELECT * FROM cargos";
        $resultado = $this->db->getConnection()->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Error al obtener cargos: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerDepartamentos() {
        $query = "SELECT * FROM departamentos";
        $resultado = $this->db->getConnection()->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Error al obtener departamentos: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerEmpleadoPorId($idEmpleado) {
        $query = "SELECT * FROM empleados WHERE idEmpleado = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            return $resultado->fetch_object();
        } else {
            throw new Exception("No se encontró el empleado.");
        }
    }

    public function actualizarEmpleado($data) {
        $sql = "UPDATE empleados SET nombres = ?, apellidos = ?, cedula = ?, usuarioEmpleado = ?, contrasena = ? WHERE idEmpleado = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("sssssi", $data['nombres'], $data['apellidos'], $data['cedula'], $data['usuarioEmpleado'], $data['contrasena'], $data['idEmpleado']);
        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Error al actualizar empleado: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerActividadesPorEmpleado($idEmpleado) {
        try {
            $query = "SELECT 
                        a.idActividad,
                        a.descripcionActividad,
                        DATE_FORMAT(a.fechaInicio, '%d/%m/%Y') as fechaInicio,
                        DATE_FORMAT(a.fechaCulminacion, '%d/%m/%Y') as fechaCulminacion,
                        ea.nombreEstado as estado,
                        ca.nombreCategoria,
                        d.nombreDepartamentos as departamento
                     FROM actividades a
                     JOIN estadoActividad ea ON a.idEstado = ea.idEstado
                     JOIN categoriasactividades ca ON a.idCategoria = ca.idCategoria
                     JOIN departamentos d ON ca.idDepartamento = d.idDepartamentos
                     WHERE a.idEmpleado = ?
                     ORDER BY a.fechaInicio DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bind_param("i", $idEmpleado);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $actividades = [];
            while ($row = $resultado->fetch_assoc()) {
                $actividades[] = $row;
            }
            
            return $actividades;
        } catch (Exception $e) {
            error_log("Error en modeloEmpleado: " . $e->getMessage());
            throw new Exception("No se pudieron obtener las actividades del empleado");
        }
    }

    public function verificarCedula($cedula) {
        $query = "SELECT COUNT(*) AS count FROM empleados WHERE cedula = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado) {
            return $resultado->fetch_assoc();
        } else {
            throw new Exception("Error al verificar la cédula: " . $this->db->getConnection()->error);
        }
    }

}
?>