<?php
require_once __DIR__ . '/../models/Assinatura.php';
require_once __DIR__ . '/../models/Pagamento.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../../config/config.php';

class PagamentoController
{
    public function mostrarCheckout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cliente']['cliente_id']) || !isset($_SESSION['plano_id'])) {
            header('Location: ' . BASE_URL . '/cadastro');
            exit;
        }

        $clienteId = $_SESSION['cliente']['cliente_id'];
        $planoId = $_SESSION['plano_id'];
        $valorTotal = $_SESSION['valor_total'];

        $assinaturaModel = new Assinatura();

        // Verifica se já existe uma assinatura para este cliente
        if (!isset($_SESSION['assinatura_id'])) {
            $assinaturaExistente = $assinaturaModel->buscarPorCliente($clienteId);
            
            if ($assinaturaExistente) {
                $_SESSION['assinatura_id'] = $assinaturaExistente['id'];
                error_log("🔄 Assinatura já existente encontrada. ID: " . $_SESSION['assinatura_id']);
            } else {
                // Criar assinatura se não existir
                $_SESSION['assinatura_id'] = $assinaturaModel->criar($clienteId, $planoId, $valorTotal);
                error_log("✅ Nova assinatura criada. ID: " . $_SESSION['assinatura_id']);
            }
        }

        require_once ROOT_PATH . '/app/views/pagamento/checkout.php';
    }

    public function processar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log("🔍 Sessão atual: " . print_r($_SESSION, true));

        if (!isset($_SESSION['cliente']['cliente_id'], $_SESSION['plano_id'], $_SESSION['valor_total'], $_SESSION['assinatura_id'])) {
            error_log("❌ Dados da sessão faltando no processamento do pagamento");
            $_SESSION['erro_pagamento'] = "Dados incompletos. Por favor, comece novamente.";
            header('Location: ' . BASE_URL . '/planos');
            exit;
        }

        try {
            $assinaturaId = $_SESSION['assinatura_id'];
            $valorTotal = $_SESSION['valor_total'];
            $formaPagamento = $_POST['forma_pagamento'];

            error_log("📝 Processando pagamento - Assinatura: $assinaturaId, Valor: $valorTotal, Forma: $formaPagamento");

            $asaasResponse = $this->enviarPagamentoParaAsaas($_POST);
            
            if (!isset($asaasResponse['id'])) {
                throw new Exception("Resposta inválida do Asaas");
            }

            // Salvar pagamento no banco
            $pagamentoModel = new Pagamento();
            $pagamentoId = $pagamentoModel->criar($assinaturaId, [
                'valor' => $valorTotal,
                'forma_pagamento' => $formaPagamento,
                'asaas_payment_id' => $asaasResponse['id']
            ]);

            if (!$pagamentoId) {
                throw new Exception("Erro ao salvar pagamento no banco de dados");
            }

            error_log("✅ Pagamento registrado com sucesso. ID: $pagamentoId");

            if ($formaPagamento === 'pix') {
                $_SESSION['pix_data'] = $asaasResponse;
                header('Location: ' . BASE_URL . '/pagamento/pix');
            } else {
                if ($asaasResponse['status'] === 'CONFIRMED') {
                    header('Location: ' . BASE_URL . '/pagamento-aprovado');
                } else {
                    header('Location: ' . BASE_URL . '/pagamento-recusado');
                }
            }
            exit;

        } catch (Exception $e) {
            error_log("❌ Erro no processamento do pagamento: " . $e->getMessage());
            $_SESSION['erro_pagamento'] = "Erro ao processar pagamento: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/pagamento');
            exit;
        }
    }

    private function enviarPagamentoParaAsaas($dados)
    {
        $formaPagamento = $dados['forma_pagamento'];
        
        if ($formaPagamento === 'cartao') {
            return $this->processarCartao($dados);
        } elseif ($formaPagamento === 'pix') {
            return $this->processarPix();
        } else {
            throw new Exception("Forma de pagamento inválida");
        }
    }

    private function processarCartao($dados)
    {
        $required_fields = ['numero_cartao', 'titular_cartao', 'validade_cartao', 'ccv_cartao', 'cpf_titular_cartao'];
        foreach ($required_fields as $field) {
            if (empty($dados[$field])) {
                throw new Exception("Campo $field é obrigatório");
            }
        }

        $validade = explode('/', $dados['validade_cartao']);
        if (count($validade) !== 2) {
            throw new Exception("Data de validade inválida");
        }

        $tokenizeData = [
            'creditCard' => [
                'holderName' => $dados['titular_cartao'],
                'number' => preg_replace('/\D/', '', $dados['numero_cartao']),
                'expiryMonth' => $validade[0],
                'expiryYear' => $validade[1],
                'ccv' => $dados['ccv_cartao'],
            ],
            'creditCardHolderInfo' => [
                'name' => $dados['titular_cartao'],
                'cpfCnpj' => preg_replace('/\D/', '', $dados['cpf_titular_cartao']),
            ],
            'customer' => $_SESSION['cliente']['asaas_id']
        ];

        $creditCardToken = $this->tokenizeCreditCard($tokenizeData);

        $paymentData = [
            'customer' => $_SESSION['cliente']['asaas_id'],
            'billingType' => 'CREDIT_CARD',
            'value' => $_SESSION['valor_total'],
            'dueDate' => date('Y-m-d'),
            'description' => 'Assinatura Medicard',
            'creditCardToken' => $creditCardToken
        ];

        return $this->enviarRequestAsaas('/payments', $paymentData);
    }

    private function processarPix()
    {
        $paymentData = [
            'customer' => $_SESSION['cliente']['asaas_id'],
            'billingType' => 'PIX',
            'value' => $_SESSION['valor_total'],
            'dueDate' => date('Y-m-d', strtotime('+1 day')),
            'description' => 'Assinatura Medicard'
        ];

        $payment = $this->enviarRequestAsaas('/payments', $paymentData);
        
        if (!isset($payment['id'])) {
            throw new Exception("Erro ao criar pagamento PIX");
        }

        // Buscar QR Code
        $qrCode = $this->obterQrCodePix($payment['id']);
        
        if (!isset($qrCode['encodedImage'])) {
            throw new Exception("Erro ao obter QR Code PIX");
        }

        $payment['qrCode'] = $qrCode;
        return $payment;
    }

    private function tokenizeCreditCard($data)
    {
        $response = $this->enviarRequestAsaas('/creditCard/tokenize', $data);
        
        if (!isset($response['creditCardToken'])) {
            throw new Exception("Erro na tokenização do cartão");
        }

        return $response['creditCardToken'];
    }

    private function enviarRequestAsaas($endpoint, $data = [], $method = 'POST')
    {
        $curl = curl_init();

        $url = ASAAS_API_URL . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . ASAAS_ACCESS_TOKEN
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($method === 'POST' && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception("Erro na requisição Asaas: " . $err);
        }

        $result = json_decode($response, true);
        
        if (isset($result['errors'])) {
            throw new Exception("Erro Asaas: " . json_encode($result['errors']));
        }

        return $result;
    }

    private function criarQrCodePix()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api-sandbox.asaas.com/v3/pix/qrCodes/static",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'addressKey' => '.',
                'description' => '.',
                'value' => 0
            ]),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "access_token: " . ASAAS_ACCESS_TOKEN 
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #: " . $err);
        } else {
            return json_decode($response, true);
        }
    }

    private function obterQrCodePix($paymentId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api-sandbox.asaas.com/v3/payments/$paymentId/pixQrCode",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "access_token: " . ASAAS_ACCESS_TOKEN // Add your Asaas access token here
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #: " . $err);
        } else {
            return json_decode($response, true);
        }
    }

    public function verificarStatusPix()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['pix_data']['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do pagamento PIX não encontrado']);
            return;
        }

        try {
            $paymentId = $_SESSION['pix_data']['id'];
            $payment = $this->enviarRequestAsaas("/payments/$paymentId", [], 'GET');

            if (!isset($payment['status'])) {
                throw new Exception("Status não encontrado na resposta");
            }

            $status = $this->mapearStatus($payment['status']);

            if ($status === 'aprovado') {
                $pagamentoModel = new Pagamento();
                $pagamentoModel->atualizarStatus($paymentId, 'aprovado');
            }

            echo json_encode([
                'status' => $status,
                'raw_status' => $payment['status']
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    

    private function mapearStatus($statusAsaas)
    {
        $mapeamento = [
            'PENDING' => 'pendente',
            'RECEIVED' => 'aprovado',
            'CONFIRMED' => 'aprovado',
            'OVERDUE' => 'atrasado',
            'REFUNDED' => 'reembolsado',
            'RECEIVED_IN_CASH' => 'aprovado',
            'REFUND_REQUESTED' => 'reembolso_solicitado',
            'CHARGEBACK_REQUESTED' => 'chargeback_solicitado',
            'CHARGEBACK_DISPUTE' => 'chargeback_disputa',
            'AWAITING_CHARGEBACK_REVERSAL' => 'aguardando_reversao_chargeback',
            'DUNNING_REQUESTED' => 'cobranca_solicitada',
            'DUNNING_RECEIVED' => 'cobranca_recebida',
            'AWAITING_RISK_ANALYSIS' => 'analise_risco'
        ];

        return $mapeamento[$statusAsaas] ?? 'desconhecido';
    }

    public function pagamentoAprovado()
    {
        require_once ROOT_PATH . '/app/views/pagamento/aprovado.php';
    }

    public function pagamentoRecusado()
    {
        require_once ROOT_PATH . '/app/views/pagamento/recusado.php';
    }
}
