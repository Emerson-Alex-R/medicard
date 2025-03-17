<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Plano.php';
require_once ROOT_PATH . '/app/models/Cliente.php';
require_once ROOT_PATH . '/app/controllers/ClienteController.php';
require_once ROOT_PATH . '/app/controllers/PlanoController.php';
require_once ROOT_PATH . '/app/controllers/PagamentoController.php';

// Estabelece a conexão com o banco de dados
$database = Database::getInstance();
$dbConnection = $database->getConnection();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/medicard/public', '', $uri);
if (empty($uri)) {
    $uri = '/';
}

switch ($uri) {
    case '/':
        error_log("🔄 Redirecionando para /planos");
        header('Location: /medicard/public/planos');
        exit;

    case '/planos':
        error_log("🔄 Carregando página de planos");
        $planoModel = new Plano();
        $planos = $planoModel->buscarTodos();
        require_once ROOT_PATH . '/app/views/planos/planos.php';
        break;

    case '/cadastro':
        error_log("🔄 Carregando página de cadastro");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['plano_id'])) {
                error_log("❌ Plano ID não definido, redirecionando para /planos");
                header('Location: /medicard/public/planos');
                exit;
            }
            $_SESSION['plano_id'] = $_POST['plano_id'];
        }

        $planoId = $_SESSION['plano_id'] ?? null;
        if (!$planoId) {
            error_log("❌ Plano ID não encontrado na sessão, redirecionando para /planos");
            header('Location: /medicard/public/planos');
            exit;
        }

        // Se existir um cliente ID na sessão, busca os dados deste cliente
        $clienteId = $_SESSION['cliente']['cliente_id'] ?? null;
        $clienteModel = new Cliente();
        $cliente = $clienteId ? $clienteModel->buscarPorId($clienteId) : null;

        // Passa os dados para a view cadastro.php
        require_once ROOT_PATH . '/app/views/cliente/cadastro.php';

        // Limpa a sessão depois de carregar a página
        unset($_SESSION['erro_cadastro'], $_SESSION['dados_form']);
        break;

    case '/salvar-cliente':
        error_log("🔄 Salvando cliente");
        $clienteController = new ClienteController();
        $clienteController->salvarOuAtualizarCliente();
        break;

    case '/pagamento':
        error_log("🔄 Carregando página de pagamento");
        if (!isset($_SESSION['cliente']['cliente_id'])) {
            error_log("❌ Cliente ID não encontrado na sessão, redirecionando para /cadastro");
            header('Location: /medicard/public/cadastro');
            exit;
        }

        $clienteId = $_SESSION['cliente']['cliente_id'];

        echo "<script>alert('✅ Cliente ID: $clienteId confirmado! Redirecionando para pagamento...');</script>";

        require_once ROOT_PATH . '/app/views/pagamento/checkout.php';
        break;

    case '/pagamento-aprovado':
        error_log("🔄 Pagamento aprovado");
        $pagamentoController = new PagamentoController();
        $pagamentoController->pagamentoAprovado();
        break;

    case '/pagamento-recusado':
        error_log("🔄 Pagamento recusado");
        $pagamentoController = new PagamentoController();
        $pagamentoController->pagamentoRecusado();
        break;

    case '/processar-pagamento':
        error_log("🔄 Processando pagamento");
        $pagamentoController = new PagamentoController();
        $pagamentoController->processar();
        break;

    case '/verificar-status-pix':
        error_log("🔄 Verificando status do PIX");
        $pagamentoController = new PagamentoController();
        $pagamentoController->verificarStatusPix();
        break;

    case '/pagamento/pix':
        error_log("🔄 Carregando página de pagamento PIX");
        if (!isset($_SESSION['pix_data'])) {
            error_log("❌ Dados do PIX não encontrados na sessão, redirecionando para /pagamento");
            header('Location: ' . BASE_URL . '/pagamento');
            exit;
        }
        require_once ROOT_PATH . '/app/views/pagamento/pix.php';
        break;

    default:
        error_log("❌ Página não encontrada: " . $uri);
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página não encontrada (URI: " . $uri . ")";
        break;
}
?>