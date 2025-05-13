<?php
// Configurações do Site e Banco de Dados

// --- CONFIGURAÇÕES DO SITE ---
// URL Base do Site. Se o catálogo está em graosfoz.com.br/catalogo/, então '.' é geralmente correto.
// Se estiver num subdomínio ou diretório diferente, ajuste conforme necessário (ex: 'https://seusite.com/loja') .
define('SITE_URL', '.');

// Nome do Site que aparecerá em títulos e rodapés.
define('SITE_NOME_CONFIG', 'Grãos S.A.'); // Altere para o nome da sua loja se desejar.

// --- CONFIGURAÇÕES DO WHATSAPP ---
// Número do WhatsApp para onde os pedidos serão enviados (APENAS NÚMEROS, sem '+', espaços ou traços).
define('WHATSAPP_NUMERO_CONFIG', '5545998259993'); // !!! IMPORTANTE: VERIFIQUE E COLOQUE O SEU NÚMERO CORRETO AQUI !!!

// Número do WhatsApp formatado para exibição no cabeçalho da página.
define('WHATSAPP_DISPLAY', '+55 45 99825-9993'); // !!! IMPORTANTE: VERIFIQUE E COLOQUE O SEU NÚMERO FORMATADO CORRETO AQUI !!!

// --- CONFIGURAÇÕES DE FRETE (valores em Guaranis, como números inteiros, sem pontos ou vírgulas) ---
// Valor do frete por KG.
define('VALOR_FRETE_POR_KG_CONFIG', 1500);

// Valor mínimo do frete, mesmo que o cálculo por peso seja menor.
define('VALOR_FRETE_MINIMO_CONFIG', 3000);

// Valor da compra a partir do qual o frete é grátis.
define('VALOR_COMPRA_FLETE_GRATIS_CONFIG', 200000);

// --- CONFIGURAÇÕES DO BANCO DE DADOS ---
// Host do banco de dados (geralmente 'localhost').
define('DB_HOST', 'localhost');

// Nome do banco de dados.
define('DB_NAME', 'lollad10_catalogo1'); // Confirmado anteriormente como o seu banco de dados.

// Usuário do banco de dados.
define('DB_USER', 'lollad10_jhonatan'); // Confirmado anteriormente.

// Senha do banco de dados.
define('DB_PASS', 'jhonatan2727A@');    // Confirmado anteriormente.

// Charset do banco de dados.
define('DB_CHARSET', 'utf8mb4');

// --- CONEXÃO PDO COM O BANCO DE DADOS (não altere esta parte a menos que saiba o que está a fazer) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Em um ambiente de produção, você deve logar o erro e mostrar uma mensagem genérica.
    // error_log($e->getMessage());
    // die('Erro ao conectar ao banco de dados. Por favor, tente mais tarde.');
    // Para desenvolvimento, pode ser útil mostrar o erro. CUIDADO em produção.
    die('Erro de conexão com o banco de dados: ' . $e->getMessage());
}

// --- CONFIGURAÇÕES DE ERRO PHP (APENAS PARA DESENVOLVIMENTO) ---
// Estas linhas ajudam a mostrar erros PHP no ecrã, o que é útil durante o desenvolvimento.
// Comente ou remova estas três linhas quando o site estiver em produção!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
