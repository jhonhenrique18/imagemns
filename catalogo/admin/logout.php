<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir a sessão
session_unset();
session_destroy();

// Redirecionar para a página de login
header('Location: index.php');
exit;
?>