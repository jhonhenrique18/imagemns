<?php
// Configurações da página
$titulo_pagina = 'Configurações do Sistema';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Mensagem para informação
$mensagem_info = '';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $acao = $_POST['acao'];
        
        // Atualizar configurações gerais
        if ($acao === 'atualizar_geral') {
            $site_nome = isset($_POST['site_nome']) ? sanitizar($_POST['site_nome']) : '';
            $site_descricao = isset($_POST['site_descricao']) ? sanitizar($_POST['site_descricao']) : '';
            $whatsapp = isset($_POST['whatsapp']) ? sanitizar($_POST['whatsapp']) : '';
            $valor_frete = isset($_POST['valor_frete']) ? (int)$_POST['valor_frete'] : 1500;
            
            // Atualizar arquivo de configuração
            try {
                $config_file = '../config.php';
                $config_content = file_get_contents($config_file);
                
                // Substituir valores
                $config_content = preg_replace("/define\('SITE_NOME', '.*?'\);/", "define('SITE_NOME', '$site_nome');", $config_content);
                $config_content = preg_replace("/define\('SITE_DESCRICAO', '.*?'\);/", "define('SITE_DESCRICAO', '$site_descricao');", $config_content);
                $config_content = preg_replace("/define\('WHATSAPP', '.*?'\);/", "define('WHATSAPP', '$whatsapp');", $config_content);
                $config_content = preg_replace("/define\('VALOR_FRETE_POR_KG', \d+\);/", "define('VALOR_FRETE_POR_KG', $valor_frete);", $config_content);
                
                // Verificar se o arquivo é gravável
                if (is_writable($config_file)) {
                    file_put_contents($config_file, $config_content);
                    
                    $_SESSION['mensagem'] = 'Configurações gerais atualizadas com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                    
                    // Recarregar as constantes para refletir as mudanças
                    define('SITE_NOME', $site_nome);
                    define('SITE_DESCRICAO', $site_descricao);
                    define('WHATSAPP', $whatsapp);
                    define('VALOR_FRETE_POR_KG', $valor_frete);
                } else {
                    $mensagem_info = 'O arquivo config.php não tem permissão de escrita. Você precisará atualizar as configurações manualmente editando o arquivo.';
                    
                    $_SESSION['mensagem'] = 'Erro ao atualizar o arquivo de configuração. Verifique as permissões do arquivo.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } catch (Exception $e) {
                $_SESSION['mensagem'] = 'Erro ao atualizar as configurações: ' . $e->getMessage();
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        }
        
        // Atualizar logo
        if ($acao === 'atualizar_logo') {
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logo_path = '../assets/img/logo.png';
                
                // Verificar tipo de arquivo
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['logo']['tmp_name']);
                
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
                
                if (in_array($mime, $allowed_types)) {
                    // Mover o arquivo para o destino
                    try {
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                            $_SESSION['mensagem'] = 'Logo atualizado com sucesso!';
                            $_SESSION['mensagem_tipo'] = 'success';
                        } else {
                            $_SESSION['mensagem'] = 'Erro ao mover o arquivo. Verifique as permissões do diretório.';
                            $_SESSION['mensagem_tipo'] = 'danger';
                        }
                    } catch (Exception $e) {
                        $_SESSION['mensagem'] = 'Erro ao atualizar o logo: ' . $e->getMessage();
                        $_SESSION['mensagem_tipo'] = 'danger';
                    }
                } else {
                    $_SESSION['mensagem'] = 'Tipo de arquivo não permitido. Utilize imagens PNG ou JPG.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } else {
                $_SESSION['mensagem'] = 'Selecione uma imagem para o logo.';
                $_SESSION['mensagem_tipo'] = 'warning';
            }
        }
        
        // Atualizar banco de dados
        if ($acao === 'atualizar_banco') {
            $db_host = isset($_POST['db_host']) ? sanitizar($_POST['db_host']) : '';
            $db_usuario = isset($_POST['db_usuario']) ? sanitizar($_POST['db_usuario']) : '';
            $db_senha = isset($_POST['db_senha']) ? $_POST['db_senha'] : '';
            $db_nome = isset($_POST['db_nome']) ? sanitizar($_POST['db_nome']) : '';
            
            // Atualizar arquivo de configuração
            try {
                $config_file = '../config.php';
                $config_content = file_get_contents($config_file);
                
                // Substituir valores
                $config_content = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '$db_host');", $config_content);
                $config_content = preg_replace("/define\('DB_USUARIO', '.*?'\);/", "define('DB_USUARIO', '$db_usuario');", $config_content);
                $config_content = preg_replace("/define\('DB_SENHA', '.*?'\);/", "define('DB_SENHA', '$db_senha');", $config_content);
                $config_content = preg_replace("/define\('DB_NOME', '.*?'\);/", "define('DB_NOME', '$db_nome');", $config_content);
                
                // Verificar se o arquivo é gravável
                if (is_writable($config_file)) {
                    // Testar a conexão com os novos dados
                    try {
                        $test_pdo = new PDO(
                            "mysql:host=$db_host;dbname=$db_nome;charset=utf8mb4",
                            $db_usuario,
                            $db_senha,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false
                            ]
                        );
                        
                        // Se chegou até aqui, a conexão foi bem-sucedida
                        file_put_contents($config_file, $config_content);
                        
                        $_SESSION['mensagem'] = 'Configurações do banco de dados atualizadas com sucesso!';
                        $_SESSION['mensagem_tipo'] = 'success';
                        
                        // Atualizar a conexão atual para usar os novos dados
                        $pdo = $test_pdo;
                    } catch (PDOException $e) {
                        $_SESSION['mensagem'] = 'Erro ao conectar ao banco de dados com as novas configurações: ' . $e->getMessage();
                        $_SESSION['mensagem_tipo'] = 'danger';
                    }
                } else {
                    $mensagem_info = 'O arquivo config.php não tem permissão de escrita. Você precisará atualizar as configurações manualmente editando o arquivo.';
                    
                    $_SESSION['mensagem'] = 'Erro ao atualizar o arquivo de configuração. Verifique as permissões do arquivo.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } catch (Exception $e) {
                $_SESSION['mensagem'] = 'Erro ao atualizar as configurações: ' . $e->getMessage();
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        }
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: configuracoes.php');
    exit;
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<!-- Mensagem de informação se houver -->
<?php if ($mensagem_info): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> <?php echo $mensagem_info; ?>
</div>
<?php endif; ?>

<!-- Guias de configuração -->
<ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral" type="button" role="tab" aria-controls="geral" aria-selected="true">
            <i class="fas fa-cog me-1"></i> Configurações Gerais
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab" aria-controls="logo" aria-selected="false">
            <i class="fas fa-image me-1"></i> Logo
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab" aria-controls="database" aria-selected="false">
            <i class="fas fa-database me-1"></i> Banco de Dados
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="sistema-tab" data-bs-toggle="tab" data-bs-target="#sistema" type="button" role="tab" aria-controls="sistema" aria-selected="false">
            <i class="fas fa-info-circle me-1"></i> Informações do Sistema
        </button>
    </li>
</ul>

<!-- Conteúdo das guias -->
<div class="tab-content" id="configTabsContent">
    <!-- Configurações Gerais -->
    <div class="tab-pane fade show active" id="geral" role="tabpanel" aria-labelledby="geral-tab">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Configurações Gerais</h5>
            </div>
            <div class="card-body">
                <form action="configuracoes.php" method="post">
                    <input type="hidden" name="acao" value="atualizar_geral">
                    
                    <!-- Nome do site -->
                    <div class="mb-3">
                        <label for="site_nome" class="form-label">Nome do Site</label>
                        <input type="text" class="form-control" id="site_nome" name="site_nome" value="<?php echo SITE_NOME; ?>" required>
                    </div>
                    
                    <!-- Descrição do site -->
                    <div class="mb-3">
                        <label for="site_descricao" class="form-label">Descrição do Site</label>
                        <input type="text" class="form-control" id="site_descricao" name="site_descricao" value="<?php echo SITE_DESCRICAO; ?>">
                        <div class="form-text">Utilizado para SEO e meta descrição.</div>
                    </div>
                    
                    <!-- WhatsApp -->
                    <div class="mb-3">
                        <label for="whatsapp" class="form-label">Número do WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="<?php echo WHATSAPP; ?>" placeholder="595990000000">
                        </div>
                        <div class="form-text">Número completo com código do país (sem o +). Ex: 595990000000</div>
                    </div>
                    
                    <!-- Valor do frete -->
                    <div class="mb-3">
                        <label for="valor_frete" class="form-label">Valor do Frete por KG (Guaranis)</label>
                        <div class="input-group">
                            <span class="input-group-text">G$</span>
                            <input type="number" class="form-control" id="valor_frete" name="valor_frete" value="<?php echo VALOR_FRETE_POR_KG; ?>" min="0">
                        </div>
                        <div class="form-text">Valor em Guaranis por quilograma para cálculo de frete.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Salvar Configurações Gerais
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Logo -->
    <div class="tab-pane fade" id="logo" role="tabpanel" aria-labelledby="logo-tab">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Logo do Site</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Logo Atual</h6>
                        <div class="mb-4 p-3 bg-light text-center rounded">
                            <?php if (file_exists('../assets/img/logo.png')): ?>
                            <img src="../assets/img/logo.png?v=<?php echo time(); ?>" alt="Logo atual" class="img-fluid" style="max-height: 100px;">
                            <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i> Logo não encontrado.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Atualizar Logo</h6>
                        <form action="configuracoes.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="acao" value="atualizar_logo">
                            
                            <div class="mb-3">
                                <label for="logo" class="form-label">Selecione uma nova imagem</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg" required>
                                <div class="form-text">Formatos aceitos: PNG, JPG. Tamanho recomendado: 300x100px.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload me-1"></i> Atualizar Logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Banco de Dados -->
    <div class="tab-pane fade" id="database" role="tabpanel" aria-labelledby="database-tab">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Configurações do Banco de Dados</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Atenção!</strong> Alterar estas configurações pode fazer com que o site deixe de funcionar. Só modifique se souber o que está fazendo.
                </div>
                
                <form action="configuracoes.php" method="post">
                    <input type="hidden" name="acao" value="atualizar_banco">
                    
                    <!-- Host do banco -->
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="<?php echo DB_HOST; ?>" required>
                    </div>
                    
                    <!-- Nome do banco -->
                    <div class="mb-3">
                        <label for="db_nome" class="form-label">Nome do Banco</label>
                        <input type="text" class="form-control" id="db_nome" name="db_nome" value="<?php echo DB_NOME; ?>" required>
                    </div>
                    
                    <!-- Usuário do banco -->
                    <div class="mb-3">
                        <label for="db_usuario" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="db_usuario" name="db_usuario" value="<?php echo DB_USUARIO; ?>" required>
                    </div>
                    
                    <!-- Senha do banco -->
                    <div class="mb-3">
                        <label for="db_senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="db_senha" name="db_senha" value="<?php echo DB_SENHA; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Salvar Configurações do Banco
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Informações do Sistema -->
    <div class="tab-pane fade" id="sistema" role="tabpanel" aria-labelledby="sistema-tab">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Informações do Sistema</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">Versão do PHP</th>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <th>Sistema Operacional</th>
                            <td><?php echo php_uname(); ?></td>
                        </tr>
                        <tr>
                            <th>Versão do MySQL</th>
                            <td>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT VERSION() as version");
                                    $version = $stmt->fetch();
                                    echo $version['version'];
                                } catch (PDOException $e) {
                                    echo 'Não foi possível obter a versão do MySQL';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Diretório do Site</th>
                            <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                        </tr>
                        <tr>
                            <th>URL do Site</th>
                            <td><?php echo SITE_URL; ?></td>
                        </tr>
                        <tr>
                            <th>Extensões PHP</th>
                            <td>
                                <?php
                                $extensions = get_loaded_extensions();
                                $important_ext = ['pdo', 'pdo_mysql', 'gd', 'exif', 'json', 'mbstring', 'fileinfo'];
                                
                                foreach ($important_ext as $ext) {
                                    $status = in_array($ext, $extensions) ? 'text-success' : 'text-danger';
                                    $icon = in_array($ext, $extensions) ? 'fa-check' : 'fa-times';
                                    echo "<span class='{$status}'><i class='fas {$icon} me-1'></i>{$ext}</span><br>";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Versão do Sistema</th>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <th>Desenvolvido por</th>
                            <td>Grãos S.A. &copy; <?php echo date('Y'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-3 text-center">
                    <a href="../" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Visitar o Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>