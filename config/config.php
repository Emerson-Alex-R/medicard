<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medicard');

// Configuração da API
define('ASAAS_ACCESS_TOKEN', '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjcwYThmMDg5LTQwMTYtNDA1Ni1iMzY0LTk5MTIxYjBmYzk5Zjo6JGFhY2hfOGU3Y2MyYTctZTUwNy00NzQ1LThkYzMtZmEwMzUwMjczNTE3');
define('ASAAS_API_URL', 'https://api-sandbox.asaas.com/v3');
define('ASAAS_USER_AGENT', 'Teste/2.0');

// URL Base do projeto
define('BASE_URL', 'http://localhost/medicard/public');

// Configurações de Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Charset
ini_set('default_charset', 'UTF-8');

// Configurações de Exibição de Erros (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);