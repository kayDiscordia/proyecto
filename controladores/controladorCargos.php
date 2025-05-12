<?php
require_once '../modelos/modeloCargos.php';

class controladorCargos
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new modeloCargos();
    }

    public function insertarCargo($nombreCargo, $limiteActividades, $descripcionCargo)
    {
        try {
            $this->modelo->insertarCargo($nombreCargo, $limiteActividades, $descripcionCargo);
            return json_encode(array("status" => "success", "message" => "Cargo insertado correctamente."));
        } catch (Exception $e) {
            return json_encode(array("status" => "error", "message" => $e->getMessage()));
        }
    }
}