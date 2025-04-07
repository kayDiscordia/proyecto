<?php
require_once '../login/conexion.php'; // Asegúrate de que la ruta sea correcta

class Database {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conexion;

        // Verifica si hay errores de conexión
        if (!$this->conn) {
            throw new Exception("Error de conexión: " . mysqli_connect_error());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>