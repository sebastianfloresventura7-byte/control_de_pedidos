<?php
require_once __DIR__ . "/../config/conexion.php";

class Pedido {
    private $id;
    private $cliente;
    private $total;
    private $estado;
    private $fecha;

    public function __construct($cliente = "", $total = 0.0, $estado = "Pendiente", $id = null, $fecha = null) {
        $this->cliente = $cliente;
        $this->total = $total;
        $this->estado = $estado;
        $this->id = $id;
        $this->fecha = $fecha;
    }

    public function getId() { return $this->id; }
    public function getCliente() { return $this->cliente; }
    public function getTotal() { return $this->total; }
    public function getEstado() { return $this->estado; }
    public function getFecha() { return $this->fecha; }

    public function agregarMonto($monto) {
        if ($monto > 0) {
            $this->total += $monto;
        }
    }

    public function actualizarEstado($id, $nuevoEstado) {
        $db = (new Conexion())->conectar();
        $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $id
        ]);
    }
    public function eliminar($id) {
    $db = (new Conexion())->conectar();
    $sql = "DELETE FROM pedidos WHERE id_pedido = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([':id' => $id]);
    }
}