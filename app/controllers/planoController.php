<?php
require_once __DIR__ . '/../../config/config.php';

class PlanoController
{
    const PRECO_MENSAL_BASE_START = 29.9;
    const PRECO_MENSAL_BASE_PRO = 59.9;
    const TAXA_DEPENDENTE_MENSAL_START = 19.9;
    const TAXA_DEPENDENTE_MENSAL_PRO = 29.9;

    public function selecionarPlano()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $plano = $_POST['plano'] ?? '';
                $depend_user = $_POST['depend_user'] ?? 'nao';
                $qtdade_depend = $_POST['qtdade_depend'] ?? 0;

                $resultado = $this->calculateSubscriptionValue($plano, $depend_user, $qtdade_depend);
                
                $_SESSION['plano_id'] = $plano;
                $_SESSION['valor_total'] = $resultado['valor_assinatura'];
                $_SESSION['descricao_plano'] = $resultado['descricao_plano'];
                $_SESSION['dependentes'] = [
                    'tem_dependentes' => $depend_user === 'sim',
                    'quantidade' => (int)$qtdade_depend
                ];

                error_log("✅ Plano selecionado: {$resultado['descricao_plano']}, Valor: {$resultado['valor_assinatura']}");
                
                header('Location: ' . BASE_URL . '/cadastro');
                exit;
            } catch (Exception $e) {
                error_log("❌ Erro ao selecionar plano: " . $e->getMessage());
                $_SESSION['erro'] = $e->getMessage();
                header('Location: ' . BASE_URL . '/planos');
                exit;
            }
        }
    }

    private function calculateSubscriptionValue($plano, $depend_user, $qtdade_depend)
{
    $preco_base = 0;
    $taxa_dependente = 0;
    $descricao_plano = '';

    if ($plano === 'start') {
        $preco_base = self::PRECO_MENSAL_BASE_START;
        $taxa_dependente = self::TAXA_DEPENDENTE_MENSAL_START;
        $descricao_plano = 'Start';
    } elseif ($plano === 'pro') {
        $preco_base = self::PRECO_MENSAL_BASE_PRO;
        $taxa_dependente = self::TAXA_DEPENDENTE_MENSAL_PRO;
        $descricao_plano = 'Pro';
    } else {
        throw new Exception("Tipo de plano inválido.");
    }

    $numero_dependentes = ($depend_user === 'sim') ? intval($qtdade_depend) : 0;
    $valor_assinatura = $preco_base + ($numero_dependentes * $taxa_dependente);

    return [
        'valor_assinatura' => $valor_assinatura,
        'descricao_plano' => $descricao_plano,
    ];
}
}
