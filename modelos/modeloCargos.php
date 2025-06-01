<?php
require_once 'Database.php';

class modeloCargos
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function insertarCargo($nombreCargo, $limiteActividades, $descripcionCargo, $idDepartamento)
    {
        $sql = "INSERT INTO cargos (nombreCargo, limiteActividades, descripcionCargo, idDepartamento) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("sisi", $nombreCargo, $limiteActividades, $descripcionCargo, $idDepartamento);
        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Cargo registrado correctamente.']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Error al registrar el cargo.']);
        }
    }
}
