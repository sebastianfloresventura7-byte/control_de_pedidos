<?php
require_once "Persona.php";

class Cliente extends Persona {
    private $direccion;

    public function __construct($id, $nombre, $telefono, $direccion) {
        parent::__construct($id, $nombre, $telefono);
        $this->direccion = $direccion;
    }

    public function obtenerRol() {
        return "Cliente Regular";
    }

    public function getDireccion() {
        return $this->direccion;
    }
}
?>