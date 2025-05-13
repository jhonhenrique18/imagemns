<?php
// Incluir arquivo de configuração
require_once 'config.php';

// Função para verificar e criar diretórios
function verificarCriarDiretorio($caminho) {
    if (!file_exists($caminho)) {
        mkdir($caminho, 0755, true);
        echo "<div class='alert alert-success'>Diretório criado: {$caminho}</div>";
    }
}

// Função para corrigir caminho de imagens nos produtos
function corrigirImagensProdutos() {
    global $pdo;
    
    try {
        // Verificar se existem produtos com imagens
        $stmt = $pdo->query("SELECT COUNT(*) FROM produtos WHERE imagem_principal IS NOT NULL AND imagem_principal != ''");
        $count = $stmt->fetchColumn();
        
        echo "<h3>Produtos com imagens: {$count}</h3>";
        
        if ($count > 0) {
            // Verificar se as imagens existem fisicamente
            $stmt = $pdo->query("SELECT id, nome, imagem_principal FROM produtos WHERE imagem_principal IS NOT NULL AND imagem_principal != ''");
            $produtos = $stmt->fetchAll();
            
            $correcoes = [];
            
            foreach ($produtos as $produto) {
                $caminho_img = 'assets/img/' . $produto['imagem_principal'];
                $caminho_img_alternativo = 'assets/img/produtos/' . basename($produto['imagem_principal']);
                
                // Verificar se a imagem existe no caminho original
                if (!file_exists($caminho_img)) {
                    echo "<div class='alert alert-warning'>Imagem não encontrada: {$caminho_img} (Produto: {$produto['nome']})</div>";
                    
                    // Verificar se existe no caminho alternativo
                    if (file_exists($caminho_img_alternativo)) {
                        echo "<div class='alert alert-info'>Encontrada no caminho alternativo: {$caminho_img_alternativo}</div>";
                        
                        // Corrigir o caminho no banco
                        $correcoes[] = [
                            'id' => $produto['id'],
                            'caminho_atual' => $produto['imagem_principal'],
                            'novo_caminho' => 'produtos/' . basename($produto['imagem_principal'])
                        ];
                    } else {
                        echo "<div class='alert alert-danger'>Imagem não encontrada em caminhos alternativos.</div>";
                    }
                } else {
                    echo "<div class='alert alert-success'>Imagem OK: {$caminho_img} (Produto: {$produto['nome']})</div>";
                }
            }
            
            // Aplicar correções
            if (!empty($correcoes)) {
                echo "<h3>Aplicando correções de caminho:</h3>";
                
                foreach ($correcoes as $correcao) {
                    $stmt = $pdo->prepare("UPDATE produtos SET imagem_principal = :novo_caminho WHERE id = :id");
                    $stmt->bindParam(':novo_caminho', $correcao['novo_caminho']);
                    $stmt->bindParam(':id', $correcao['id']);
                    
                    if ($stmt->execute()) {
                        echo "<div class='alert alert-success'>Caminho corrigido para produto ID {$correcao['id']}: {$correcao['caminho_atual']} -> {$correcao['novo_caminho']}</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Erro ao corrigir caminho para produto ID {$correcao['id']}</div>";
                    }
                }
            }
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao corrigir imagens: {$e->getMessage()}</div>";
    }
}

// Função para corrigir permissões
function corrigirPermissoes() {
    $diretorios = [
        'assets/img',
        'assets/img/produtos',
        'assets/img/categorias',
        'assets/img/banners'
    ];
    
    foreach ($diretorios as $dir) {
        if (file_exists($dir) && is_dir($dir)) {
            if (chmod($dir, 0755)) {
                echo "<div class='alert alert-success'>Permissões corrigidas para: {$dir}</div>";
            } else {
                echo "<div class='alert alert-warning'>Não foi possível corrigir permissões para: {$dir}</div>";
            }
        }
    }
}

// Função para adicionar imagens de exemplo se não houver nenhuma
function adicionarImagensExemplo() {
    global $pdo;
    
    try {
        // Verificar se existem produtos sem imagens
        $stmt = $pdo->query("SELECT COUNT(*) FROM produtos WHERE imagem_principal IS NULL OR imagem_principal = ''");
        $count = $stmt->fetchColumn();
        
        echo "<h3>Produtos sem imagens: {$count}</h3>";
        
        if ($count > 0) {
            // Criar imagem de exemplo
            $img_exemplo = criarImagemExemplo();
            
            if ($img_exemplo) {
                // Atualizar produtos sem imagem
                $stmt = $pdo->prepare("UPDATE produtos SET imagem_principal = :imagem WHERE imagem_principal IS NULL OR imagem_principal = ''");
                $caminho_imagem = 'produtos/' . $img_exemplo;
                $stmt->bindParam(':imagem', $caminho_imagem);
                
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>{$count} produtos atualizados com imagem de exemplo</div>";
                } else {
                    echo "<div class='alert alert-danger'>Erro ao atualizar produtos com imagem de exemplo</div>";
                }
            }
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao adicionar imagens de exemplo: {$e->getMessage()}</div>";
    }
}

// Função para criar uma imagem de exemplo
function criarImagemExemplo() {
    $diretorio = 'assets/img/produtos';
    verificarCriarDiretorio($diretorio);
    
    $filename = 'produto_exemplo.jpg';
    $caminho_completo = $diretorio . '/' . $filename;
    
    // Criar uma imagem básica
    $img = imagecreatetruecolor(400, 400);
    $bg_color = imagecolorallocate($img, 255, 255, 255); // Branco
    $text_color = imagecolorallocate($img, 0, 0, 0); // Preto
    
    // Preencher o fundo
    imagefilledrectangle($img, 0, 0, 399, 399, $bg_color);
    
    // Desenhar uma borda
    imagerectangle($img, 0, 0, 399, 399, $text_color);
    
    // Adicionar texto
    imagestring($img, 5, 150, 190, 'Produto Exemplo', $text_color);
    
    // Salvar a imagem
    imagejpeg($img, $caminho_completo, 90);
    imagedestroy($img);
    
    if (file_exists($caminho_completo)) {
        echo "<div class='alert alert-success'>Imagem de exemplo criada: {$caminho_completo}</div>";
        return $filename;
    }
    
    echo "<div class='alert alert-danger'>Erro ao criar imagem de exemplo</div>";
    return false;
}

// Função para verificar e criar o logo
function verificarCriarLogo() {
    $logo_path = 'assets/img/logo.png';
    
    if (!file_exists($logo_path)) {
        // Criar um logo básico
        $img = imagecreatetruecolor(300, 100);
        $bg_color = imagecolorallocate($img, 25, 135, 84); // Verde
        $text_color = imagecolorallocate($img, 255, 255, 255); // Branco
        
        // Preencher o fundo
        imagefilledrectangle($img, 0, 0, 299, 99, $bg_color);
        
        // Adicionar texto
        imagestring($img, 5, 80, 40, 'Graos S.A.', $text_color);
        
        // Salvar a imagem
        imagepng($img, $logo_path);
        imagedestroy($img);
        
        if (file_exists($logo_path)) {
            echo "<div class='alert alert-success'>Logo criado: {$logo_path}</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao criar logo</div>";
        }
    } else {
        echo "<div class='alert alert-info'>Logo já existe: {$logo_path}</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrigir Imagens - <?php echo SITE_NOME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Correção de Imagens do Sistema</h1>
        <p class="text-center mb-4">Esta ferramenta corrige problemas comuns com imagens.</p>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cogs me-2"></i> Ações de Correção
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="verificar_diretorios" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-folder-plus me-2"></i> Verificar/Criar Diretórios
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="corrigir_imagens" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-image me-2"></i> Corrigir Caminhos de Imagens
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="corrigir_permissoes" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-key me-2"></i> Corrigir Permissões
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="adicionar_imagens" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-plus-circle me-2"></i> Adicionar Imagens de Exemplo
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="verificar_logo" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-image me-2"></i> Verificar/Criar Logo
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="acao" value="executar_tudo" class="btn btn-danger w-100 mb-2">
                                <i class="fas fa-play-circle me-2"></i> Executar Todas as Ações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tasks me-2"></i> Resultados
            </div>
            <div class="card-body">
                <?php
                // Processamento das ações
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
                    $acao = $_POST['acao'];
                    
                    echo "<div class='alert alert-primary'>Executando ação: {$acao}</div>";
                    
                    if ($acao === 'verificar_diretorios' || $acao === 'executar_tudo') {
                        echo "<h3>Verificando diretórios</h3>";
                        verificarCriarDiretorio('assets/img');
                        verificarCriarDiretorio('assets/img/produtos');
                        verificarCriarDiretorio('assets/img/categorias');
                        verificarCriarDiretorio('assets/img/banners');
                    }
                    
                    if ($acao === 'corrigir_imagens' || $acao === 'executar_tudo') {
                        echo "<h3>Corrigindo caminhos de imagens</h3>";
                        corrigirImagensProdutos();
                    }
                    
                    if ($acao === 'corrigir_permissoes' || $acao === 'executar_tudo') {
                        echo "<h3>Corrigindo permissões</h3>";
                        corrigirPermissoes();
                    }
                    
                    if ($acao === 'adicionar_imagens' || $acao === 'executar_tudo') {
                        echo "<h3>Adicionando imagens de exemplo</h3>";
                        adicionarImagensExemplo();
                    }
                    
                    if ($acao === 'verificar_logo' || $acao === 'executar_tudo') {
                        echo "<h3>Verificando logo</h3>";
                        verificarCriarLogo();
                    }
                    
                    echo "<div class='alert alert-success mt-3'>Ações concluídas!</div>";
                } else {
                    echo "<div class='alert alert-info'>Selecione uma ação para executar.</div>";
                }
                ?>
            </div>
        </div>
        
        <div class="text-center mt-3 mb-5">
            <a href="diagnostico.php" class="btn btn-primary">
                <i class="fas fa-stethoscope me-1"></i> Voltar para o Diagnóstico
            </a>
            <a href="index.php" class="btn btn-secondary ms-2">
                <i class="fas fa-home me-1"></i> Voltar para a página inicial
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>