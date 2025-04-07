<?php
class Conexion {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'proyecto';
    public $conexion;

    public function __construct() {
        $this->conexion = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conexion->connect_error) {
            die("Conexión fallida: " . $this->conexion->connect_error);
        }
    }

    public function query($sql) {
        return $this->conexion->query($sql);
    }

    public function prepare($sql) {
        return $this->conexion->prepare($sql);
    }

    public function fetch_object($result) {
        return $result->fetch_object();
    }

    public function error(): string {
        return $this->conexion->error;
    }

    public function close() {
        $this->conexion->close();
    }
}
?>