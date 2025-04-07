<?php
require_once('functionLogin.php');
require_once 'conexion.php';
class SelectEmpleado {
    private $conexion;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Método para obtener un usuario por su ID
    public function SelectuserByuser($id) {
        $query = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Método para obtener todos los cargos
    public function obtenerCargos() {
        $query = "SELECT idCargo, nombreCargo FROM cargos";
        $resultado = $this->conexion->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            return []; // Retorna un array vacío si hay un error
        }
    }

    // Método para obtener todos los departamentos
    public function obtenerDepartamentos() {
        $query = "SELECT idDepartamentos, nombreDepartamentos FROM departamentos"; // Corregido: idDepartamentos y nombreDepartamentos
        $resultado = $this->conexion->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            return []; // Retorna un array vacío si hay un error
        }
    }

    // Método para obtener todos los empleados
    public function obtenerEmpleados() {
        $query = "SELECT e.idEmpleado, e.nombres, e.apellidos, e.cedula, e.cargo, e.usuarioEmpleado, e.contrasena, 
                         c.nombreCargo AS cargo_nombre, d.nombreDepartamentos AS departamento_nombre
                  FROM empleados e
                  LEFT JOIN cargos c ON e.idCargo = c.idCargo
                  LEFT JOIN departamentos d ON e.idDepartamento = d.idDepartamentos";
        $resultado = $this->conexion->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            return []; // Retorna un array vacío si hay un error
        }
    }

    // Método para insertar un nuevo empleado
    public function insertarEmpleado($nombres, $apellidos, $cedula, $cargo, $usuarioEmpleado, $contrasena, $idCargo, $idDepartamento) {
        $query = "INSERT INTO empleados (nombres, apellidos, cedula, cargo, usuarioEmpleado, contrasena, idCargo, idDepartamento)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("ssssssii", $nombres, $apellidos, $cedula, $cargo, $usuarioEmpleado, $contrasena, $idCargo, $idDepartamento);
        return $stmt->execute();
    }
}
?>