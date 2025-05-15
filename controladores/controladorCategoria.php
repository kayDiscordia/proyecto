<?php
require_once '../modelos/modeloCategoria.php';

class controladorCategoria
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new modeloCategoria();
    }

    public function insertarCategoria($nombreCategoria, $idDepartamento, $descripcionCategoria)
    {
        try {
            $this->modelo->insertarCategoria($nombreCategoria, $idDepartamento, $descripcionCategoria);
            return json_encode(array("status" => "success", "message" => "Categoría insertada correctamente."));
        } catch (Exception $e) {
            return json_encode(array("status" => "error", "message" => $e->getMessage()));
        }
    }
}