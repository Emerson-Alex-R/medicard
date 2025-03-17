<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['pix_data'])) {
    header('Location: ' . BASE_URL . '/pagamento');
    exit;
}

$pixData = $_SESSION['pix_data'];
$valorTotal = $_SESSION['valor_total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDICARD - Pagamento PIX</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-1.css">

    <style>
        .pix-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .qr-code-container {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code-container img {
            max-width: 300px;
            height: auto;
        }
        .pix-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            margin: 20px 0;
        }
        .copy-button {
            width: 100%;
            margin: 10px 0;
        }
        .status-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="ugf-wrapper flat-grey-bg">
        <div class="ugf-content-block">
            <div class="logo">
                <a href="<?= BASE_URL ?>">
                    <img src="<?= BASE_URL ?>/assets/images/logo-dark2.png" alt="">
                </a>
            </div>
            
            <div class="container">
                <div class="pix-container">
                    <h2 class="text-center mb-4">Pagamento via PIX</h2>
                    
                    <div class="alert alert-info">
                        <p class="mb-0">Valor a pagar: <strong>R$ <?= number_format($valorTotal, 2, ',', '.') ?></strong></p>
                        <small>O QR Code expira em 24 horas</small>
                    </div>

                    <?php if (isset($pixData['qrCode']['encodedImage'])): ?>
                        <div class="qr-code-container">
                            <img src="data:image/png;base64,<?= $pixData['qrCode']['encodedImage'] ?>" 
                                 alt="QR Code PIX">
                        </div>
                    <?php endif; ?>

                    <?php if (isset($pixData['qrCode']['payload'])): ?>
                        <div class="pix-code-section">
                            <label>Código PIX:</label>
                            <div class="pix-code" id="pixCode">
                                <?= $pixData['qrCode']['payload'] ?>
                            </div>
                            <button class="btn btn-primary copy-button" onclick="copyPixCode()">
                                <i class="las la-copy"></i> Copiar código PIX
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="status-container">
                        <div class="alert alert-warning" id="statusMessage">
                            <i class="las la-clock"></i> Aguardando pagamento...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>
    <script>
        function copyPixCode() {
            const pixCode = document.getElementById('pixCode').innerText;
            navigator.clipboard.writeText(pixCode).then(() => {
                const copyBtn = document.querySelector('.copy-button');
                copyBtn.innerHTML = '<i class="las la-check"></i> Código copiado!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="las la-copy"></i> Copiar código PIX';
                }, 2000);
            });
        }

        // Verificar status do pagamento a cada 5 segundos
        setInterval(function() {
            $.get('<?= BASE_URL ?>/verificar-status-pix', function(response) {
                const statusElement = document.getElementById('statusMessage');
                
                if (response.status === 'aprovado') {
                    statusElement.className = 'alert alert-success';
                    statusElement.innerHTML = '<i class="las la-check-circle"></i> Pagamento aprovado! Redirecionando...';
                    setTimeout(() => {
                        window.location.href = '<?= BASE_URL ?>/pagamento-aprovado';
                    }, 2000);
                } else if (response.status === 'pendente') {
                    statusElement.className = 'alert alert-warning';
                    statusElement.innerHTML = '<i class="las la-clock"></i> Aguardando pagamento...';
                } else {
                    statusElement.className = 'alert alert-info';
                    statusElement.innerHTML = `<i class="las la-info-circle"></i> Status: ${response.status}`;
                }
            });
        }, 5000);
    </script>
</body>
</html>
