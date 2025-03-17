<?php
class Assinatura
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function criar($clienteId, $planoId, $valorTotal)
    {
        try {
            // ✅ Log para verificar os dados antes da inserção
            error_log("📌 Criando assinatura para Cliente: $clienteId | Plano: $planoId | Valor: $valorTotal");

            $sql = "INSERT INTO assinaturas 
                    (cliente_id, plano_id, valor_total, data_inicio, data_fim, status) 
                    VALUES 
                    (:cliente_id, :plano_id, :valor_total, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'pendente')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $clienteId,
                ':plano_id' => $planoId,
                ':valor_total' => $valorTotal
            ]);

            $assinaturaId = $this->db->lastInsertId();

            error_log("✅ Assinatura criada com sucesso! ID: " . $assinaturaId);
            return $assinaturaId;
        } catch (PDOException $e) {
            error_log("❌ ERRO ao criar assinatura: " . $e->getMessage());
            throw new Exception("Erro ao criar assinatura: " . $e->getMessage());
        }
    }


    public function buscarPorCliente($clienteId)
    {
        try {
            $sql = "SELECT * FROM assinaturas WHERE cliente_id = :cliente_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar assinatura: " . $e->getMessage());
        }
    }

}
