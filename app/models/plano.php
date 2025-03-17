<?php
class Plano {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarTodos() {
        try {
            $sql = "SELECT * FROM planos ORDER BY valor ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar planos: " . $e->getMessage());
        }
    }

    public function buscarPorId($id) {
        try {
            $sql = "SELECT * FROM planos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Erro ao buscar plano: " . $e->getMessage());
        }
    }
}