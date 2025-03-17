<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../../config/config.php';

class ClienteController
{
    private $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    public function salvarOuAtualizarCliente()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("🔍 Dados recebidos no POST: " . print_r($_POST, true)); 
    
                $dados = [
                    'cliente_id' => $_POST['cliente_id'] ?? null,
                    'nome_user' => $_POST['nome_user'],
                    'email_user' => $_POST['email_user'],
                    'tel_user' => $_POST['tel_user'],
                    'cpf_user' => $_POST['cpf_user'],
                    'gender' => $_POST['gender'] ?? null,
                    'depend_user' => $_POST['depend_user'] ?? 'nao',
                    'qtdade_depend' => $_POST['qtdade_depend'] ?? 0,
                    'cidade' => $_POST['cidade'],
                    'cep' => $_POST['cep'],
                    'rua' => $_POST['rua'],
                    'numero' => $_POST['numero'],
                    'complemento' => $_POST['complemento'] ?? null
                ];
    
                // Validação de dados
                if (empty($dados['nome_user']) || empty($dados['cpf_user']) || empty($dados['email_user']) || empty($dados['tel_user'])) {
                    $_SESSION['erro_cadastro'] = 'Todos os campos são obrigatórios.';
                    header('Location: ' . BASE_URL . '/cadastro');
                    exit;
                }
    
                // Primeiro, salvar no MySQL
                $clienteId = $this->clienteModel->salvarOuAtualizar($dados);
                
                if (!$clienteId) {
                    $_SESSION['erro_cadastro'] = 'Erro ao salvar dados do cliente.';
                    header('Location: ' . BASE_URL . '/cadastro');
                    exit;
                }
    
                // Depois, cadastrar no Asaas
                $clienteResponseArray = $this->cadastrarCliente(
                    ASAAS_ACCESS_TOKEN,
                    $dados['nome_user'],
                    $dados['cpf_user'],
                    $dados['email_user']
                );
    
                if ($clienteResponseArray[3]) { // Se houve erro
                    error_log("❌ Erro ao cadastrar no Asaas: " . $clienteResponseArray[1]);
                    $_SESSION['erro_cadastro'] = 'Erro ao cadastrar no sistema de pagamento.';
                    header('Location: ' . BASE_URL . '/cadastro');
                    exit;
                }
    
                $response = json_decode($clienteResponseArray[1], true);
                
                if (!isset($response['id'])) {
                    error_log("❌ ID do Asaas não encontrado na resposta");
                    $_SESSION['erro_cadastro'] = 'Erro na resposta do sistema de pagamento.';
                    header('Location: ' . BASE_URL . '/cadastro');
                    exit;
                }
    
                // Atualizar o registro MySQL com o ID do Asaas
                $this->clienteModel->atualizarAsaasId($clienteId, $response['id']);
                
                // Configurar cobrança recorrente no Asaas
                $cobrancaResponse = $this->configurarCobrancaRecorrente(
                    ASAAS_ACCESS_TOKEN,
                    $response['id'],
                    $dados['qtdade_depend']
                );

                if (isset($cobrancaResponse['errors'])) {
                    error_log("❌ Erro ao configurar cobrança recorrente no Asaas: " . json_encode($cobrancaResponse['errors']));
                    $_SESSION['erro_cadastro'] = 'Erro ao configurar cobrança recorrente no sistema de pagamento.';
                    header('Location: ' . BASE_URL . '/cadastro');
                    exit;
                }

                // Configurar sessão
                $_SESSION['cliente'] = [
                    'cliente_id' => $clienteId,
                    'asaas_id' => $response['id'],
                    'nome' => $dados['nome_user'],
                    'email' => $dados['email_user']
                ];
    
                error_log("✅ Cliente cadastrado com sucesso! ID MySQL: $clienteId, ID Asaas: {$response['id']}");
                error_log("🔍 Sessão atualizada: " . print_r($_SESSION, true));
                
                // Redirecionar para a página de aquisição
                error_log("🔄 Redirecionando para a página de pagamento...");
                header('Location: ' . BASE_URL . '/pagamento');
                exit;
            }
        } catch (Exception $e) {
            error_log("❌ Erro no Controller: " . $e->getMessage());
            $_SESSION['erro_cadastro'] = "Erro inesperado: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/cadastro');
            exit;
        }
    }

    private function cadastrarCliente($apiKey, $name, $cpfCnpj, $email)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => ASAAS_API_URL . "/customers",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'name' => $name,
                'cpfCnpj' => $cpfCnpj,
                'email' => $email,
            ]),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "User-Agent: " . ASAAS_USER_AGENT,
                "access_token: " . $apiKey,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        error_log("🔍 Resposta do Asaas: " . $response);
        error_log("🔍 Código HTTP do Asaas: " . $httpCode);
        error_log("🔍 Erro do cURL: " . $err);

        return [$err, $response, $httpCode, $err ? true : false];
    }

    private function configurarCobrancaRecorrente($apiKey, $customerId, $qtdadeDepend)
    {
        $curl = curl_init();

        $valorMensal = 100; // Valor base do plano
        $valorMensal += $qtdadeDepend * 50; // Adiciona valor por dependente

        curl_setopt_array($curl, [
            CURLOPT_URL => ASAAS_API_URL . "/subscriptions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'customer' => $customerId,
                'billingType' => 'CREDIT_CARD',
                'nextDueDate' => date('Y-m-d', strtotime('+1 month')),
                'value' => $valorMensal,
                'cycle' => 'MONTHLY',
                'description' => 'Plano de Saúde',
                'endDate' => date('Y-m-d', strtotime('+1 year'))
            ]),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "User-Agent: " . ASAAS_USER_AGENT,
                "access_token: " . $apiKey,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        error_log("🔍 Resposta do Asaas (cobrança recorrente): " . $response);
        error_log("🔍 Código HTTP do Asaas (cobrança recorrente): " . $httpCode);
        error_log("🔍 Erro do cURL (cobrança recorrente): " . $err);

        if ($err) {
            throw new Exception("Erro ao configurar cobrança recorrente: " . $err);
        }

        return json_decode($response, true);
    }
}
?>