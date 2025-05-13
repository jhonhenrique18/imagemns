<?php
// Verificar autenticação
require_once 'auth.php';

// Definir título da página se não estiver definido
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Painel Administrativo';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - Admin <?php echo SITE_NOME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personalizado -->
    <style>
        :root {
            --bs-primary: #198754;
            --bs-primary-dark: #0f5132;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar {
            background-color: #212529;
            min-width: 250px;
            max-width: 250px;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 15px;
            background-color: #198754;
        }
        
        .sidebar-logo {
            max-height: 40px;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #198754;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            padding: 20px;
        }
        
        .main-content {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .navbar-toggler {
            padding: 4px 8px;
            font-size: 1.2rem;
        }
        
        .page-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .btn-primary:hover {
            background-color: var(--bs-primary-dark);
            border-color: var(--bs-primary-dark);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table {
            vertical-align: middle;
        }
        
        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        
        .form-control, .form-select {
            padding: 10px;
            border-radius: 5px;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Media queries para responsividade */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                min-height: 100vh;
                z-index: 999;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .content {
                width: 100%;
            }
            
            .overlay {
                display: none;
                position: fixed;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
                opacity: 0;
                transition: all 0.5s ease-in-out;
            }
            
            .overlay.active {
                display: block;
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper d-flex align-items-stretch">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logo.png" alt="<?php echo SITE_NOME; ?>" class="sidebar-logo">
            </div>
            
            <div class="p-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pedidos.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pedidos.php') ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="produtos.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'produtos.php') ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="categorias.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'categorias.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i> Categorias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="banners.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'banners.php') ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i> Banners
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="usuarios.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'usuarios.php') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="configuracoes.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'configuracoes.php') ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                    </li>
                </ul>
                
                <hr class="text-secondary my-4">
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="../" target="_blank" class="nav-link">
                            <i class="fas fa-external-link-alt"></i> Ver Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light mb-4">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-success">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-none d-sm-block me-2 text-end">
                                    <span class="d-block fw-bold"><?php echo $_SESSION['admin_nome']; ?></span>
                                    <small class="text-muted">Administrador</small>
                                </div>
                                <div class="avatar">
                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i> Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="alterar-senha.php"><i class="fas fa-key me-2"></i> Alterar Senha</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Título da página -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h4><?php echo $titulo_pagina; ?></h4>
                    
                    <?php if (isset($botao_voltar) && $botao_voltar): ?>
                    <a href="<?php echo $botao_voltar; ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mensagens de alerta -->
                <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="alert alert-<?php echo $_SESSION['mensagem_tipo']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['mensagem']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php 
                    unset($_SESSION['mensagem']);
                    unset($_SESSION['mensagem_tipo']);
                endif; 
                ?>