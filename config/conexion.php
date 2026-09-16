<?php
class Conexion {
    private $host = "127.0.0.1;port=5555";
    private $db = "control_de_pedidos";
    private $user = "root";
    private $password = "";
    private $pdo;

    public function conectar() {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db};charset=utf8", $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>