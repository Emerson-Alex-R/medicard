<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['valor_total']) || !isset($_SESSION['cliente'])) {
    header('Location: ' . BASE_URL . '/planos');
    exit;
}

$valorTotal = $_SESSION['valor_total'];
$cliente = $_SESSION['cliente'];
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MEDICARD - Checkout</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-1.css">
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
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <div class="ugf-content pt150">
                            <h2>Pagamento</h2>
                            <p>Valor total: R$ <?= number_format($valorTotal, 2, ',', '.') ?></p>

                            <?php if (isset($_SESSION['erro_pagamento'])): ?>
                                <div class="alert alert-danger">
                                    <?= $_SESSION['erro_pagamento'] ?>
                                    <?php unset($_SESSION['erro_pagamento']); ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= BASE_URL ?>/processar-pagamento" method="POST" id="formPagamento">
                                <div class="form-group">
                                    <label>Forma de Pagamento</label>
                                    <select name="forma_pagamento" id="formaPagamento" class="form-control" required>
                                        <option value="">Selecione...</option>
                                        <option value="cartao">Cartão de Crédito</option>
                                        <option value="pix">PIX</option>
                                    </select>
                                </div>

                                <div id="cartaoFields" style="display: none;">
                                    <div class="form-group">
                                        <label>Número do Cartão</label>
                                        <input type="text" name="numero_cartao" class="form-control" placeholder="0000 0000 0000 0000">
                                    </div>
                                    <div class="form-group">
                                        <label>Nome no Cartão</label>
                                        <input type="text" name="titular_cartao" class="form-control" placeholder="Nome como está no cartão">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Validade</label>
                                                <input type="text" name="validade_cartao" class="form-control" placeholder="MM/AAAA">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>CVV</label>
                                                <input type="text" name="ccv_cartao" class="form-control" placeholder="123">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>CPF do Titular</label>
                                        <input type="text" name="cpf_titular_cartao" class="form-control" placeholder="000.000.000-00">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-4">
                                    Finalizar Pagamento
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Máscaras para os campos
            $('input[name="numero_cartao"]').mask('0000 0000 0000 0000');
            $('input[name="validade_cartao"]').mask('00/0000');
            $('input[name="ccv_cartao"]').mask('000');
            $('input[name="cpf_titular_cartao"]').mask('000.000.000-00');

            // Toggle dos campos do cartão
            $('#formaPagamento').change(function() {
                if ($(this).val() === 'cartao') {
                    $('#cartaoFields').slideDown();
                } else {
                    $('#cartaoFields').slideUp();
                }
            });

            // Validação do formulário
            $('#formPagamento').submit(function(e) {
                const formaPagamento = $('#formaPagamento').val();
                
                if (formaPagamento === 'cartao') {
                    const campos = [
                        'numero_cartao',
                        'titular_cartao',
                        'validade_cartao',
                        'ccv_cartao',
                        'cpf_titular_cartao'
                    ];

                    for (const campo of campos) {
                        if (!$(`input[name="${campo}"]`).val()) {
                            alert('Por favor, preencha todos os campos do cartão');
                            e.preventDefault();
                            return false;
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
