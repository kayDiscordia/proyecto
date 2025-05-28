<?php
require_once 'Database.php';

class modeloCargos
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function insertarCargo($nombreCargo, $limiteActividades, $descripcionCargo)
    {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO cargos (nombreCargo, limiteActividades, descripcionCargo) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sis", $nombreCargo, $limiteActividades, $descripcionCargo);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar cargo: " . $stmt->error);
        }
        $stmt->close();
        return true;
    }
}