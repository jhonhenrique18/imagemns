<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incluir arquivo de configuração se não estiver incluído
if (!defined('SITE_NOME')) {
    require_once '../config.php';
}

// Verificar se a conexão com o banco de dados está ativa
if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NOME . ';charset=' . DB_CHARSET,
            DB_USUARIO,
            DB_SENHA,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die('Erro de conexão com o banco de dados: ' . $e->getMessage());
    }
}

// Incluir arquivo de funções se não estiver incluído
if (!function_exists('buscarRegistros')) {
    require_once '../includes/conexao.php';
}

/**
 * Verifica se a senha atual do usuário é válida
 * @param int $usuario_id ID do usuário
 * @param string $senha Senha a verificar
 * @return bool
 */
function verificarSenha($usuario_id, $senha) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        return true;
    }
    
    return false;
}

/**
 * Altera a senha do usuário
 * @param int $usuario_id ID do usuário
 * @param string $nova_senha Nova senha
 * @return bool
 */
function alterarSenha($usuario_id, $nova_senha) {
    global $pdo;
    
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha, updated_at = NOW() WHERE id = :id");
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Registra uma ação no sistema
 * @param string $acao Descrição da ação
 * @param string $tabela Nome da tabela (opcional)
 * @param int $registro_id ID do registro (opcional)
 */
function registrarAcao($acao, $tabela = '', $registro_id = 0) {
    global $pdo;
    
    $usuario_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $pdo->prepare("INSERT INTO log_acoes (usuario_id, acao, tabela, registro_id, ip, created_at) VALUES (:usuario_id, :acao, :tabela, :registro_id, :ip, NOW())");
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindParam(':acao', $acao);
    $stmt->bindParam(':tabela', $tabela);
    $stmt->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
    $stmt->bindParam(':ip', $ip);
    
    return $stmt->execute();
}

/**
 * Verifica se o usuário tem permissão para a ação
 * @param string $permissao Nome da permissão
 * @return bool
 */
function temPermissao($permissao) {
    // Para simplificar, assumimos que todos os usuários admin têm todas as permissões
    // Em um sistema mais complexo, você implementaria um controle de permissões
    return true;
}

/**
 * Retorna a URL base do admin
 * @return string
 */
function adminUrl($path = '') {
    $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    
    if ($path) {
        return $base_url . '/' . ltrim($path, '/');
    }
    
    return $base_url;
}

/**
 * Formata uma data para exibição
 * @param string $data Data no formato do banco de dados
 * @param bool $mostrar_hora Mostrar a hora junto com a data
 * @return string
 */
function formatarData($data, $mostrar_hora = false) {
    if (!$data) return '';
    
    $timestamp = strtotime($data);
    
    if ($mostrar_hora) {
        return date('d/m/Y H:i', $timestamp);
    }
    
    return date('d/m/Y', $timestamp);
}

/**
 * Limita um texto a um número máximo de caracteres
 * @param string $texto Texto a ser limitado
 * @param int $limite Número máximo de caracteres
 * @param string $sufixo Sufixo a ser adicionado quando o texto for limitado
 * @return string
 */
function limitarTexto($texto, $limite, $sufixo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    return substr($texto, 0, $limite) . $sufixo;
}