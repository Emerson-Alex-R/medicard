<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erroCadastro = $_SESSION['erro_cadastro'] ?? null;
$dadosForm = $_SESSION['dados_form'] ?? [];
$planoId = $_SESSION['plano_id'] ?? null;

if (!$planoId) {
    header('Location: /medicard/public/planos');
    exit;
}

require_once ROOT_PATH . '/app/models/plano.php';
$planoModel = new Plano();
$planoSelecionado = $planoModel->buscarPorId($planoId);
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MEDICARD - Plano Medicale Saúde</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">

    <!-- External Css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/owl.carousel.min.css">

    <!-- Custom Css -->
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/assets/css/theme-1.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">




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
                            <a href="<?= BASE_URL ?>/planos" class="prev-page">Voltar</a>
                            <h2>Faça seu cadastro</h2>
                            <p>Por favor, insira suas informações para realizarmos seu cadastro para o
                                <b><i><?= htmlspecialchars($planoSelecionado['nome']) ?></i></b>
                            </p>

                            <?php if (isset($erroCadastro) && !empty($erroCadastro)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($erroCadastro) ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>


                            <!-- FORMULÁRIO DE CADASTRO DE CLIENTE NO PLANO MEDICALE QUE DIRECIONA PARA A VIEW CHECKOUT.PHP (OS VALUES NOS INPUTS SERVEM PARA CARREGAR
                             OS DADOS PREENCHIDOS ANTERIORMENTE CASO ELE VOLTE PARA ESTA TELA) -->

                            <form action="<?= BASE_URL ?>/salvar-cliente" method="POST" class="account-form"
                                id="commentForm">
                                <input type="hidden" name="plano_id" value="<?= htmlspecialchars($planoId) ?>">
                                <input type="hidden" name="cliente_id"
                                    value="<?= htmlspecialchars($cliente['id'] ?? $_SESSION['cliente']['cliente_id'] ?? '') ?>">


                                <div class="row">
                                    <div class="col-sm-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputFname">Insira seu nome</label>
                                            <input type="text" name="nome_user" placeholder="Ex: Maria do Carmo"
                                                class="form-control" id="inputFname" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['nome_user'] ?? $dadosForm['nome_user'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputLname">Insira seu e-mail</label>
                                            <input type="email" name="email_user" placeholder="Ex: maria@gmail.com"
                                                class="form-control" id="inputLname" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['email_user'] ?? $dadosForm['email_user'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputPhone">Insira seu telefone</label>
                                            <input type="text" name="tel_user" placeholder="Ex: (43) 9999-9999"
                                                class="form-control" id="inputPhone" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['tel_user'] ?? $dadosForm['tel_user'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputCPF">Insira seu CPF</label>
                                            <input type="text" name="cpf_user" placeholder="Ex: 123.456.789-00"
                                                class="form-control" id="inputCPF" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['cpf_user'] ?? $dadosForm['cpf_user'] ?? '') ?>">
                                            <div id="cpfError" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group check-gender">
                                    <div class="custom-radio">
                                        <input type="radio" name="gender" value="M" class="custom-control-input"
                                            id="sexo_user_mas" required <?= ($_SESSION['cliente']['gender'] ?? $dadosForm['gender'] ?? '') === 'M' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="sexo_user_mas">Masculino</label>
                                    </div>
                                    <div class="custom-radio">
                                        <input type="radio" name="gender" value="F" class="custom-control-input"
                                            id="sexo_user_fem" required <?= ($_SESSION['cliente']['gender'] ?? $dadosForm['gender'] ?? '') === 'F' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="sexo_user_fem">Feminino</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 p-sm-0">
                                        <div class="form-group country-select">
                                            <label for="inputDependentes">Seu plano terá dependentes?</label>
                                            <div class="select-input choose-country">
                                                <select id="inputDependentes" class="form-control" name="depend_user"
                                                    required>
                                                    <option value="1" <?= ($_SESSION['cliente']['depend_user'] ?? $dadosForm['depend_user'] ?? '1') == '1' ? 'selected' : '' ?>>Não
                                                    </option>
                                                    <option value="2" <?= ($_SESSION['cliente']['depend_user'] ?? $dadosForm['depend_user'] ?? '') == '2' ? 'selected' : '' ?>>Sim
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 p-sm-0">
                                        <div class="form-group country-select">
                                            <label for="inputQtdadeDependentes">Selecione a quantidade</label>
                                            <div class="select-input choose-country">
                                                <select id="inputQtdadeDependentes" class="form-control"
                                                    name="qtdade_depend" required disabled>
                                                    <option value="1" <?= ($_SESSION['cliente']['qtdade_depend'] ?? $dadosForm['qtdade_depend'] ?? '') == '1' ? 'selected' : '' ?>>1
                                                        dependente</option>
                                                    <option value="2" <?= ($_SESSION['cliente']['qtdade_depend'] ?? $dadosForm['qtdade_depend'] ?? '') == '2' ? 'selected' : '' ?>>2
                                                        dependentes</option>
                                                    <option value="3" <?= ($_SESSION['cliente']['qtdade_depend'] ?? $dadosForm['qtdade_depend'] ?? '') == '3' ? 'selected' : '' ?>>3
                                                        dependentes</option>
                                                    <option value="4" <?= ($_SESSION['cliente']['qtdade_depend'] ?? $dadosForm['qtdade_depend'] ?? '') == '4' ? 'selected' : '' ?>>4
                                                        dependentes
                                                    </option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputCidade">Insira sua cidade</label>
                                            <input type="text" name="cidade" placeholder="Ex: Londrina"
                                                class="form-control" id="inputCidade" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['cidade'] ?? $dadosForm['cidade'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-sm-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputCep">Insira seu CEP</label>
                                            <input type="text" name="cep" placeholder="Ex: 00000-000"
                                                class="form-control" id="inputCep" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['cep'] ?? $dadosForm['cep'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-sm-6 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputRua">Insira sua rua</label>
                                            <input type="text" name="rua" placeholder="Ex: Rua Almirante Cruz"
                                                class="form-control" id="inputRua" required
                                                value="<?= htmlspecialchars($_SESSION['cliente']['rua'] ?? $dadosForm['rua'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-sm-3 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputNumero">Insira o número</label>
                                            <input type="text" name="numero" placeholder="Ex: 356 - Ap 1205"
                                                class="form-control" id="inputNumero"
                                                value="<?= htmlspecialchars($_SESSION['cliente']['numero'] ?? $dadosForm['numero'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-sm-3 p-sm-0">
                                        <div class="form-group">
                                            <label for="inputComplemento">Complemento</label>
                                            <input type="text" name="complemento" placeholder="Ex: Sala 904"
                                                class="form-control" id="inputComplemento"
                                                value="<?= htmlspecialchars($_SESSION['cliente']['complemento'] ?? $dadosForm['complemento'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <button class="btn"><span>Próximo</span> <i class="las la-arrow-right"></i></button>
                            </form>

                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    let clienteId = sessionStorage.getItem("cliente_id");
                                    if (clienteId) {
                                        document.getElementById("cliente_id").value = clienteId;
                                        alert("🔍 Cliente encontrado! ID = " + clienteId);
                                    }
                                });
                            </script>





                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ugf-sidebar flex-bottom ugf-sidebar-bg-4 sidebar-steps">
            <div class="steps">
                <div class="step complete-step">
                    <span>1</span>
                    <p>Escolha o plano</p>
                </div>
                <div class="step step-onprocess">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


    <script>
        $(document).ready(function () {
            // Garante que o campo de quantidade de dependentes inicie desabilitado
            $("#inputQtdadeDependentes").prop("disabled", true);

            // Evento de mudança no select de dependentes
            $("#inputDependentes").change(function () {
                if ($(this).val() == "2") {
                    $("#inputQtdadeDependentes").prop("disabled", false);
                } else {
                    $("#inputQtdadeDependentes").prop("disabled", true).val(""); // Reseta o valor
                }
            });

            // Se já estava "Sim" no carregamento, habilita o select de quantidade
            if ($("#inputDependentes").val() == "2") {
                $("#inputQtdadeDependentes").prop("disabled", false);
            }
        });
    </script>



    <script>
        $(document).ready(function () {
            // Função para validar CPF
            function validarCPF(cpf) {
                cpf = cpf.replace(/[^\d]/g, '');

                if (cpf.length !== 11) return false;

                // Verifica se todos os dígitos são iguais
                if (/^(\d)\1+$/.test(cpf)) return false;

                // Validação do primeiro dígito
                let soma = 0;
                for (let i = 0; i < 9; i++) {
                    soma += parseInt(cpf.charAt(i)) * (10 - i);
                }
                let dv1 = 11 - (soma % 11);
                if (dv1 > 9) dv1 = 0;
                if (dv1 !== parseInt(cpf.charAt(9))) return false;

                // Validação do segundo dígito
                soma = 0;
                for (let i = 0; i < 10; i++) {
                    soma += parseInt(cpf.charAt(i)) * (11 - i);
                }
                let dv2 = 11 - (soma % 11);
                if (dv2 > 9) dv2 = 0;
                if (dv2 !== parseInt(cpf.charAt(10))) return false;

                return true;
            }

            // Máscara do CPF com validação
            $('#inputCPF').mask('000.000.000-00', {
                onComplete: function (cpf) {
                    const cpfNumerico = cpf.replace(/[^\d]/g, '');
                    if (!validarCPF(cpfNumerico)) {
                        $('#inputCPF').addClass('is-invalid');
                        $('#cpfError').show().text('CPF inválido');
                    } else {
                        $('#inputCPF').removeClass('is-invalid').addClass('is-valid');
                        $('#cpfError').hide();
                    }
                },
                onChange: function (cpf) {
                    const cpfNumerico = cpf.replace(/[^\d]/g, '');
                    if (cpfNumerico.length < 11) {
                        $('#inputCPF').addClass('is-invalid');
                        $('#cpfError').show().text('Digite o CPF completo');
                    }
                }
            });

            // Validação do formulário
            $("#commentForm").validate({
                rules: {
                    cpf_user: {
                        required: true,
                        minlength: 14
                    }
                },
                messages: {
                    cpf_user: {
                        required: "Por favor, digite o CPF completo",
                        minlength: "O CPF deve ter 11 dígitos"
                    }
                },
                submitHandler: function (form) {
                    const cpf = $('#inputCPF').val().replace(/[^\d]/g, '');
                    if (cpf.length !== 11) {
                        alert("Por favor, preencha o CPF completo");
                        return false;
                    }
                    if (!validarCPF(cpf)) {
                        alert("Por favor, insira um CPF válido");
                        return false;
                    }
                    form.submit();
                }
            });

            // Bloqueio do envio se CPF estiver incompleto ou inválido
            $('#commentForm').on('submit', function (e) {
                const cpf = $('#inputCPF').val().replace(/[^\d]/g, '');
                if (cpf.length !== 11 || !validarCPF(cpf)) {
                    e.preventDefault();
                    if (cpf.length !== 11) {
                        alert("Por favor, preencha o CPF completo");
                    } else {
                        alert("Por favor, insira um CPF válido");
                    }
                    return false;
                }
            });


        });

    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            $("#inputCep").on("blur", function () {
                var cep = $(this).val().replace(/\D/g, ''); // Remove caracteres não numéricos

                if (cep.length === 8) { // Confere se o CEP tem 8 dígitos
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/", function (dados) {
                        if (!("erro" in dados)) {
                            // Preenche apenas o campo da rua
                            $("#inputRua").val(dados.logradouro);
                        } else {
                            alert("CEP não encontrado. Verifique e tente novamente.");
                        }
                    });
                } else {
                    alert("Formato de CEP inválido.");
                }
            });
        });
    </script>




    <!-- Primeiro jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Depois jQuery Mask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script src="<?= BASE_URL ?>/assets/js/popper.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/owl.carousel.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/countrySelect.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/jquery.validate.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/custom.js"></script>

    <?php
    // Apenas limpa a sessão se o usuário passou na validação.
    if (!isset($_SESSION['erro_cadastro'])) {
        unset($_SESSION['dados_form']);
    }
    ?>

</body>

</html>