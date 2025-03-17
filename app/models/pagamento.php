<?php
class Pagamento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function criar($assinaturaId, $dadosPagamento) {
        try {
            error_log("📝 Criando pagamento para assinatura ID: $assinaturaId");
            error_log("Dados do pagamento: " . print_r($dadosPagamento, true));

            // Converter forma_pagamento para o formato do banco
            $formaPagamento = $dadosPagamento['forma_pagamento'] === 'cartao' ? 'credito' : 'debito';

            $sql = "INSERT INTO pagamentos 
                    (assinatura_id, valor, forma_pagamento, status, asaas_payment_id, data_pagamento) 
                    VALUES 
                    (:assinatura_id, :valor, :forma_pagamento, 'pendente', :asaas_payment_id, CURRENT_TIMESTAMP)";
                    
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':assinatura_id' => $assinaturaId,
                ':valor' => $dadosPagamento['valor'],
                ':forma_pagamento' => $formaPagamento,
                ':asaas_payment_id' => $dadosPagamento['asaas_payment_id']
            ]);

            if (!$success) {
                error_log("❌ Erro ao criar pagamento: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Erro ao criar pagamento no banco de dados");
            }

            $pagamentoId = $this->db->lastInsertId();
            error_log("✅ Pagamento criado com sucesso! ID: $pagamentoId");
            return $pagamentoId;

        } catch (PDOException $e) {
            error_log("❌ Erro ao criar pagamento: " . $e->getMessage());
            throw new Exception("Erro ao criar pagamento: " . $e->getMessage());
        }
    }

    public function atualizarStatus($asaasPaymentId, $status) {
        try {
            // Converter status do Asaas para o formato do banco
            $statusMapeado = $this->mapearStatus($status);

            $sql = "UPDATE pagamentos 
                    SET status = :status, 
                        updated_at = CURRENT_TIMESTAMP,
                        data_pagamento = CASE 
                            WHEN :status = 'aprovado' THEN CURRENT_TIMESTAMP 
                            ELSE data_pagamento 
                        END
                    WHERE asaas_payment_id = :asaas_payment_id";
                    
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':status' => $statusMapeado,
                ':asaas_payment_id' => $asaasPaymentId
            ]);

            if (!$success) {
                error_log("❌ Erro ao atualizar status do pagamento: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            error_log("✅ Status do pagamento atualizado: $asaasPaymentId -> $statusMapeado");
            return true;

        } catch (PDOException $e) {
            error_log("❌ Erro ao atualizar status do pagamento: " . $e->getMessage());
            return false;
        }
    }

    private function mapearStatus($statusAsaas) {
        $mapeamento = [
            'PENDING' => 'pendente',
            'RECEIVED' => 'aprovado',
            'CONFIRMED' => 'aprovado',
            'OVERDUE' => 'pendente',
            'REFUNDED' => 'cancelado',
            'RECEIVED_IN_CASH' => 'aprovado',
            'REFUND_REQUESTED' => 'pendente',
            'CHARGEBACK_REQUESTED' => 'pendente',
            'CHARGEBACK_DISPUTE' => 'pendente',
            'AWAITING_CHARGEBACK_REVERSAL' => 'pendente',
            'DUNNING_REQUESTED' => 'pendente',
            'DUNNING_RECEIVED' => 'aprovado',
            'AWAITING_RISK_ANALYSIS' => 'pendente'
        ];

        return $mapeamento[$statusAsaas] ?? 'pendente';
    }

    public function buscarPorAsaasId($asaasPaymentId) {
        try {
            $sql = "SELECT * FROM pagamentos WHERE asaas_payment_id = :asaas_payment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':asaas_payment_id' => $asaasPaymentId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Erro ao buscar pagamento: " . $e->getMessage());
            return false;
        }
    }
}
