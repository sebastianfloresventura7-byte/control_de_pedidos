<?php
require_once __DIR__ . "/../config/conexion.php";

class PedidoController {
    private $db;

    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    public function mostrarDashboard() {
        $stmt = $this->db->prepare("SELECT p.id_pedido, c.nombre AS cliente, p.total, p.estado, p.fecha 
                                    FROM pedidos p 
                                    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                                    ORDER BY p.id_pedido DESC");
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once "views/dashboard.php";
    }

    public function mostrarFormularioNuevo() {
        $stmt = $this->db->prepare("SELECT * FROM productos");
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once "views/nuevo_pedido.php";
    }

    public function guardarPedido() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombreCliente = $_POST['cliente'];
            $telefono = $_POST['telefono'];
            $idProducto = $_POST['producto'];

            $stmtCliente = $this->db->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
            $stmtCliente->execute([$nombreCliente, $telefono]);
            $idCliente = $this->db->lastInsertId();

            $stmtProd = $this->db->prepare("SELECT precio FROM productos WHERE id_producto = ?");
            $stmtProd->execute([$idProducto]);
            $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);
            $total = $producto['precio'];

            $stmtPedido = $this->db->prepare("INSERT INTO pedidos (id_cliente, total, estado) VALUES (?, ?, 'Pendiente')");
            $stmtPedido->execute([$idCliente, $total]);

            header("Location: index.php");
        }
    }
}
?>