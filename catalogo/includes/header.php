<?php
// Verificar se o arquivo de conexão foi incluído
if (!function_exists("buscarRegistros")) {
    require_once __DIR__ . "/conexao.php";
}

// Buscar todas as categorias ativas para o menu
$categorias = buscarRegistros("categorias", "ativo = 1", "nome ASC");

// Definir SITE_NOME e SITE_DESCRICAO se não estiverem definidos (apenas para evitar erros se config.php não for carregado)
if (!defined("SITE_NOME")) {
    define("SITE_NOME", "Grãos S.A.");
}
if (!defined("SITE_DESCRICAO")) {
    define("SITE_DESCRICAO", "Sua loja de grãos e produtos naturais.");
}
if (!defined("SITE_URL")) {
    define("SITE_URL", "."); // Usar caminho relativo como fallback
}
if (!defined("WHATSAPP")) {
    define("WHATSAPP", "+55 11 99999-9999"); // Número de exemplo
}

$titulo_pagina_seguro = isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . " - " . htmlspecialchars(SITE_NOME) : htmlspecialchars(SITE_NOME);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars(SITE_DESCRICAO); ?>">
    <title><?php echo $titulo_pagina_seguro; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(SITE_URL); ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo htmlspecialchars(SITE_URL); ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Meta tags para redes sociais -->
    <meta property="og:title" content="<?php echo htmlspecialchars(SITE_NOME); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(SITE_DESCRICAO); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars(SITE_URL); ?>/assets/img/logo-social.jpg">
    <meta property="og:url" content="<?php echo htmlspecialchars(SITE_URL); ?>">
</head>
<body>
    <!-- Cabeçalho -->
    <header class="sticky-top">
        <!-- Barra superior com informações de contato -->
        <div class="bg-success text-white py-1">
            <div class="container d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center text-center text-sm-start">
                <div class="mb-1 mb-sm-0">
                    <small><i class="fab fa-whatsapp me-1"></i> <?php echo htmlspecialchars(WHATSAPP); ?></small>
                </div>
                <div>
                    <small><a href="#" class="text-white text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalIdioma"><i class="fas fa-globe me-1"></i> Español</a></small>
                </div>
            </div>
        </div>
        
        <!-- Navbar principal -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand" href="<?php echo htmlspecialchars(SITE_URL); ?>">
                    <img src="<?php echo htmlspecialchars(SITE_URL); ?>/assets/img/logo.png" alt="<?php echo htmlspecialchars(SITE_NOME); ?>" height="40">
                </a>
                
                <!-- Botão de carrinho para mobile -->
                <div class="d-flex d-lg-none">
                    <a href="<?php echo htmlspecialchars(SITE_URL); ?>/carrinho.php" class="btn btn-outline-success position-relative me-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger carrinho-contador">0</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                
                <!-- Menu de navegação -->
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo htmlspecialchars(SITE_URL); ?>">Inicio</a>
                        </li>
                        
                        <!-- Menu de categorias -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Categorías
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($categorias as $categoria): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php?categoria=<?php echo htmlspecialchars($categoria["id"]); ?>">
                                        <?php echo htmlspecialchars($categoria["nome"]); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php">Todos los Productos</a>
                        </li>
                    </ul>
                    
                    <!-- Formulário de busca -->
                    <form class="d-flex me-auto" action="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar productos..." name="busca">
                            <button class="btn btn-outline-success" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Botão de carrinho para desktop -->
                    <div class="d-none d-lg-block ms-2">
                        <a href="<?php echo htmlspecialchars(SITE_URL); ?>/carrinho.php" class="btn btn-outline-success position-relative">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger carrinho-contador">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Conteúdo principal -->
    <main class="py-4">
        <div class="container">
