<?php
class Cliente
{
    private $db;
    public $erros = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorId($clienteId)
    {
        try {
            $sql = "SELECT * FROM clientes WHERE id = :cliente_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cliente_id' => $clienteId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("❌ Erro ao buscar cliente por ID: " . $e->getMessage());
            return null;
        }
    }

    public function emailExiste($email, $clienteId = null)
    {
        $sql = "SELECT id FROM clientes WHERE email = :email";
        if ($clienteId) {
            $sql .= " AND id != :cliente_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':email' => $email];

        if ($clienteId) {
            $params[':cliente_id'] = $clienteId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() ? true : false;
    }

    public function cpfExiste($cpf, $clienteId = null)
    {
        $sql = "SELECT id FROM clientes WHERE cpf = :cpf";
        if ($clienteId) {
            $sql .= " AND id != :cliente_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':cpf' => $cpf];

        if ($clienteId) {
            $params[':cliente_id'] = $clienteId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() ? true : false;
    }

    public function atualizarAsaasId($clienteId, $asaasId)
    {
        try {
            $sql = "UPDATE clientes SET asaas_id = :asaas_id WHERE id = :cliente_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':asaas_id' => $asaasId,
                ':cliente_id' => $clienteId
            ]);
        } catch (Exception $e) {
            error_log("❌ Erro ao atualizar Asaas ID: " . $e->getMessage());
            return false;
        }
    }

    public function salvarOuAtualizar($dados)
    {
        try {
            error_log("🔍 Recebendo dados para salvar/atualizar: " . print_r($dados, true));

            $clienteId = $dados['cliente_id'] ?? null;

            // Map gender to enum values
            $sexo = 'O'; // Default to 'O' (Outro)
            if ($dados['gender'] === 'masculino') {
                $sexo = 'M';
            } elseif ($dados['gender'] === 'feminino') {
                $sexo = 'F';
            }

            if ($clienteId) {
                // Atualizar cliente existente
                $sql = "UPDATE clientes SET 
                        nome = :nome, 
                        email = :email, 
                        telefone = :telefone, 
                        cpf = :cpf, 
                        sexo = :sexo, 
                        tem_dependentes = :tem_dependentes, 
                        quantidade_dependentes = :quantidade_dependentes, 
                        cidade = :cidade, 
                        cep = :cep, 
                        rua = :rua, 
                        numero = :numero, 
                        complemento = :complemento,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = :cliente_id";

                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    ':cliente_id' => $clienteId,
                    ':nome' => $dados['nome_user'],
                    ':email' => $dados['email_user'],
                    ':telefone' => $dados['tel_user'],
                    ':cpf' => $dados['cpf_user'],
                    ':sexo' => $sexo,
                    ':tem_dependentes' => $dados['depend_user'] == '2' ? 1 : 0,
                    ':quantidade_dependentes' => ($dados['qtdade_depend'] > 3) ? 4 : (int)$dados['qtdade_depend'],
                    ':cidade' => $dados['cidade'],
                    ':cep' => $dados['cep'],
                    ':rua' => $dados['rua'],
                    ':numero' => $dados['numero'],
                    ':complemento' => $dados['complemento'] ?? null
                ]);

                if (!$success) {
                    error_log("❌ Erro ao atualizar cliente: " . print_r($stmt->errorInfo(), true));
                    throw new Exception("Erro ao atualizar cliente no banco de dados");
                }

                error_log("✅ Cliente atualizado com sucesso: ID = $clienteId");
                return $clienteId;
            } else {
                // Criar novo cliente
                $sql = "INSERT INTO clientes (
                    nome, email, telefone, cpf, sexo, tem_dependentes, 
                    quantidade_dependentes, cidade, cep, rua, numero, complemento
                ) VALUES (
                    :nome, :email, :telefone, :cpf, :sexo, :tem_dependentes, 
                    :quantidade_dependentes, :cidade, :cep, :rua, :numero, :complemento
                )";

                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    ':nome' => $dados['nome_user'],
                    ':email' => $dados['email_user'],
                    ':telefone' => $dados['tel_user'],
                    ':cpf' => $dados['cpf_user'],
                    ':sexo' => $sexo,
                    ':tem_dependentes' => $dados['depend_user'] == '2' ? 1 : 0,
                    ':quantidade_dependentes' => ($dados['qtdade_depend'] > 3) ? 4 : (int)$dados['qtdade_depend'],
                    ':cidade' => $dados['cidade'],
                    ':cep' => $dados['cep'],
                    ':rua' => $dados['rua'],
                    ':numero' => $dados['numero'],
                    ':complemento' => $dados['complemento'] ?? null
                ]);

                if (!$success) {
                    error_log("❌ Erro ao criar cliente: " . print_r($stmt->errorInfo(), true));
                    throw new Exception("Erro ao criar cliente no banco de dados");
                }

                $novoClienteId = $this->db->lastInsertId();
                error_log("✅ Novo cliente cadastrado! ID = $novoClienteId");
                return $novoClienteId;
            }
        } catch (Exception $e) {
            // Comentar para não aparecer na tela depois
            error_log("❌ Erro ao salvar ou atualizar cliente: " . $e->getMessage());
            throw new Exception("Erro ao salvar ou atualizar cliente: " . $e->getMessage());
        }
    }
}
?>
