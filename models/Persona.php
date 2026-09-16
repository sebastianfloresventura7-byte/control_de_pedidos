<?php
abstract class Persona {
    protected $id;
    protected $nombre;
    protected $telefono;

    public function __construct($id, $nombre, $telefono) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->telefono = $telefono;
    }

    abstract public function obtenerRol();
}
?>