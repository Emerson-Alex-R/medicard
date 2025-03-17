<!doctype html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>MEDICARD - Plano Medicale Saúde</title>
        <meta name=description content="Saúde de qualidade para todos">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">

        <!-- External Css -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/line-awesome.min.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/owl.carousel.min.css">

        <!-- Custom Css --> 
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-1.css">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- Favicon -->
        <link rel="icon" href="<?= BASE_URL ?>/assets/images/favicon.png">
        <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?= BASE_URL ?>/assets/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?= BASE_URL ?>/assets/images/icon-114x114.png">
    </head>
    <body>
        <div class="ugf-wrapper flat-grey-bg">
            <div class="ugf-content-block">
                <div class="logo">
                    <a href="<?= BASE_URL ?>">
                        <img class="" src="<?= BASE_URL ?>/assets/images/logo-dark2.png" alt="">
                    </a>
                </div>
                <div class="container-md">
                    <div class="row">
                        <div class="col-lg-7 offset-lg-5 p-sm-0">
                            <div class="ugf-content pt150">
                                <div class="pricing-container">
                                    <h2 class="pricing-title">Escolha seu plano</h2>
                                    <div class="pricing-grid">
                                        <?php foreach ($planos as $plano): ?>
                                            <div class="pricing-card <?= $plano['nome'] === 'Plano Pro' ? 'recommended' : '' ?>">
                                                <?php if ($plano['nome'] === 'Plano Pro'): ?>
                                                    <div class="badge">Recomendado</div>
                                                <?php endif; ?>
                                                
                                                <h3><?= str_replace('Plano ', '', $plano['nome']) ?></h3>
                                                <div class="price">
                                                    <span class="currency">R$</span>
                                                    <span class="amount"><?= number_format($plano['valor'], 2, ',', '.') ?></span>
                                                    <span class="period">/Mês</span>
                                                </div>
                                                
                                                <p class="description">
                                                    *Nessa modalidade 
                                                    <?php if ($plano['nome'] === 'Plano Pro'): ?>
                                                        <b><i>não debitamos</i></b> do seu limite do cartão de crédito.
                                                    <?php else: ?>
                                                        <b><i>será utilizado</i></b> seu limite do cartão de crédito.
                                                    <?php endif; ?>
                                                </p>

                                                <form action="<?= BASE_URL ?>/cadastro" method="POST">
                                                    <input type="hidden" name="plano_id" value="<?= $plano['id'] ?>">
                                                    <button type="submit" class="select-btn <?= $plano['nome'] === 'Plano Pro' ? 'dark' : '' ?>">
                                                        Quero este <i class="fa-solid fa-arrow-right"></i>
                                                    </button>
                                                </form>

                                                <ul class="features">
                                                    <li>
                                                        <i class="fa-solid fa-check"></i> 
                                                        R$ <?= $plano['nome'] === 'Plano Pro' ? '24,90' : '19,90' ?> por dependente
                                                    </li>
                                                    <li><i class="fa-solid fa-check"></i> Consulta com especialista a partir de R$ 150</li>
                                                    <li><i class="fa-solid fa-check"></i> PA 24h por R$ 50</li>
                                                </ul>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ugf-sidebar flex-bottom ugf-sidebar-bg-4 sidebar-steps">
                <div class="steps">          
                    <div class="step step-onprocess">
                        <span>1</span>
                        <p>Escolha o plano</p>
                    </div>
                    <div class="step">
                        <span>2</span>
                        <p>Dados pessoais</p>
                    </div>                    
                    <div class="step">
                        <span>3</span>
                        <p>Aquisição</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/popper.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/bootstrap.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/owl.carousel.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/countrySelect.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/jquery.validate.min.js"></script>
        <script src="<?= BASE_URL ?>/assets/js/custom.js"></script>
    </body>
</html>