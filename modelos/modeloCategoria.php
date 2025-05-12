<?php
require_once 'Database.php';

class modeloCategoria
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }
    
    public function insertarCategoria($nombreCategoria, $idDepartamento, $descripcionCategoria)
    {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO categoriasactividades (nombreCategoria, idDepartamento, descripcionCategoria) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sis", $nombreCategoria, $idDepartamento, $descripcionCategoria);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar categoría: " . $stmt->error);
        }
        $stmt->close();
        return true;
    }
}