<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se já está logado
if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

// Incluir arquivo de configuração
// É esperado que este ficheiro defina $pdo, SITE_NOME e a função sanitizar()
require_once "../config.php"; 

// Processar login
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST["email"]) ? sanitizar($_POST["email"]) : ""; // Assumindo que sanitizar() existe em config.php
    $senha = isset($_POST["senha"]) ? $_POST["senha"] : "";
    
    if (empty($email) || empty($senha)) {
        $error = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Buscar usuário pelo email
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch();
            
            // !!! ALERTA DE SEGURANÇA CRÍTICO !!!
            // A verificação de senha abaixo está HARDCODED e é EXTREMAMENTE INSEGURA.
            // NUNCA use senhas diretamente no código em produção.
            // A senha no banco de dados deve ser armazenada como um HASH (ex: usando password_hash()).
            // A verificação deve ser feita usando password_verify($senha, $usuario["senha_hash_do_banco"]).
            // Esta é uma solução TEMPORÁRIA e DEVE ser corrigida urgentemente.
            if ($usuario && ($senha == "jhonatan2727A@")) { // ESTA LINHA É INSEGURA
                // Login bem-sucedido
                $_SESSION["admin_logged_in"] = true;
                $_SESSION["admin_id"] = $usuario["id"];
                $_SESSION["admin_nome"] = $usuario["nome"];
                $_SESSION["admin_email"] = $usuario["email"];
                
                // Registrar login (data do último acesso)
                $stmt_update = $pdo->prepare("UPDATE usuarios SET updated_at = NOW() WHERE id = :id");
                $stmt_update->bindParam(":id", $usuario["id"]);
                $stmt_update->execute();
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Email ou senha incorretos.";
            }
        } catch (PDOException $e) {
            $error = "Erro ao processar o login. Por favor, tente novamente.";
            error_log("Erro de login no admin/index.php: " . $e->getMessage());
        }
    }
}

// Definir SITE_NOME com fallback caso não venha do config.php
if (!defined("SITE_NOME")) {
    define("SITE_NOME", "Meu Catálogo");
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - <?php echo htmlspecialchars(SITE_NOME); ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personalizado -->
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .login-container {
            max-width: 420px; /* Ligeiramente maior para melhor espaçamento */
            width: 100%;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem; /* Bootstrap 5 usa .75rem para card-border-radius */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); /* Sombra padrão do Bootstrap */
        }
        
        .card-header {
            background-color: #198754; /* Cor de sucesso do Bootstrap */
            color: white;
            text-align: center;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1.5rem;
        }
        
        .login-logo {
            max-height: 70px; /* Ajustado para melhor proporção */
            margin-bottom: 1rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem; /* Melhor preenchimento */
            border-radius: 0.375rem; /* Bootstrap 5 usa .375rem */
        }
        
        .btn-success {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            background-color: #198754;
            border-color: #198754;
        }
        
        .btn-success:hover {
            background-color: #157347; /* Cor de hover do Bootstrap */
            border-color: #146c43;
        }
        .input-group-text {
            background-color: #e9ecef; /* Cor padrão do Bootstrap */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <?php 
                // Tentar carregar o logo, com fallback se não existir
                $logo_path = "../assets/img/logo.png";
                if (file_exists($logo_path)): 
                ?>
                <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo <?php echo htmlspecialchars(SITE_NOME); ?>" class="login-logo">
                <?php else: ?>
                <h3 class="mb-3"><?php echo htmlspecialchars(SITE_NOME); ?></h3>
                <?php endif; ?>
                <h4 class="mb-0 fw-normal">Painel Administrativo</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Seu email" required value="<?php echo isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : ""; ?>">
                            <div class="invalid-feedback">
                                Por favor, insira um email válido.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                            <div class="invalid-feedback">
                                Por favor, insira a sua senha.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg">Entrar</button>
                    </div>
                    
                    <div class="text-center">
                        <!-- O link "Esqueceu a senha?" deve apontar para uma funcionalidade real ou ser removido -->
                        <a href="#" class="text-decoration-none small">Esqueceu a senha?</a>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <a href="../index.php" class="text-decoration-none text-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar para o site
                </a>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(SITE_NOME); ?>. Todos os direitos reservados.
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ativar validação Bootstrap
        (function () {
            "use strict";
            var forms = document.querySelectorAll(".needs-validation");
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener("submit", function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add("was-validated");
                    }, false);
                });
        })();
    </script>
</body>
</html>
