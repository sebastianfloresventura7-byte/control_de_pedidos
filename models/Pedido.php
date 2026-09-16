<?php
class Pedido {
    private $idPedido;
    private $cliente;
    private $total;
    private $estado;

    public function __construct($idPedido, $cliente) {
        $this->idPedido = $idPedido;
        $this->cliente = $cliente;
        $this->total = 0.00;
        $this->estado = "Pendiente";
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($nuevoEstado) {
        $estadosValidos = ['Pendiente', 'En preparación', 'Listo', 'Entregado'];
        if (in_array($nuevoEstado, $estadosValidos)) {
            $this->estado = $nuevoEstado;
        }
    }

    public function getTotal() {
        return $this->total;
    }

    public function agregarMonto($monto) {
        if ($monto > 0) {
            $this->total += $monto;
        }
    }
}
?>