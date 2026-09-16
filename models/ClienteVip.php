<?php
require_once "Cliente.php";

class ClienteVip extends Cliente {
    private $descuento = 0.10;

    public function obtenerRol() {
        return "Cliente VIP (10% Descuento)";
    }

    public function calcularTotalConDescuento($monto) {
        return $monto - ($monto * $this->descuento);
    }
}
?>