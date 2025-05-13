<?php
// Configurações da página
$titulo_pagina = 'Gerenciar Imagens do Produto';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem'] = 'ID do produto inválido.';
    $_SESSION['mensagem_tipo'] = 'danger';
    header('Location: produtos.php');
    exit;
}

$produto_id = (int)$_GET['id'];

// Buscar o produto
$produto = buscarRegistro('produtos', $produto_id);

if (!$produto) {
    $_SESSION['mensagem'] = 'Produto não encontrado.';
    $_SESSION['mensagem_tipo'] = 'danger';
    header('Location: produtos.php');
    exit;
}

// Botão de voltar para edição do produto
$botao_voltar = 'produto-editar.php?id=' . $produto_id;
if (!file_exists('produto-editar.php')) {
    $botao_voltar = 'produtos.php';
}

// Processamento do formulário de upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'upload') {
    // Verificar se há arquivos para upload
    if (isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
        $files = $_FILES['imagens'];
        $count = count($files['name']);
        $ordem = isset($_POST['ordem']) ? (int)$_POST['ordem'] : 0;
        
        // Contador de sucessos e erros
        $sucessos = 0;
        $erros = 0;
        
        // Processar cada arquivo
        for ($i = 0; $i < $count; $i++) {
            // Criar array de arquivo para a função de upload
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            // Verificar se não houve erro no upload
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Fazer upload da imagem
                $imagem = uploadImagem($file, 'produtos');
                
                if ($imagem) {
                    // Inserir no banco de dados
                    $dados = [
                        'produto_id' => $produto_id,
                        'imagem' => $imagem,
                        'ordem' => $ordem
                    ];
                    
                    if (inserirRegistro('produto_imagens', $dados)) {
                        $sucessos++;
                    } else {
                        $erros++;
                    }
                } else {
                    $erros++;
                }
            } else {
                $erros++;
            }
        }
        
        // Mensagem de resultado
        if ($sucessos > 0) {
            $_SESSION['mensagem'] = "{$sucessos} imagem(ns) adicionada(s) com sucesso!";
            $_SESSION['mensagem_tipo'] = 'success';
        }
        
        if ($erros > 0) {
            $_SESSION['mensagem'] = "Houve erro no upload de {$erros} imagem(ns).";
            $_SESSION['mensagem_tipo'] = 'warning';
        }
    } else {
        $_SESSION['mensagem'] = 'Selecione pelo menos uma imagem para upload.';
        $_SESSION['mensagem_tipo'] = 'warning';
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: produto-imagens.php?id=' . $produto_id);
    exit;
}

// Processamento de exclusão de imagem
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $imagem_id = (int)$_GET['excluir'];
    
    try {
        // Buscar a imagem para excluir o arquivo
        $imagem = buscarRegistro('produto_imagens', $imagem_id);
        
        if ($imagem && $imagem['produto_id'] == $produto_id) {
            // Excluir do banco de dados
            if (excluirRegistro('produto_imagens', $imagem_id)) {
                // Excluir o arquivo do servidor
                if ($imagem['imagem'] && file_exists("../assets/img/{$imagem['imagem']}")) {
                    unlink("../assets/img/{$imagem['imagem']}");
                }
                
                $_SESSION['mensagem'] = 'Imagem excluída com sucesso!';
                $_SESSION['mensagem_tipo'] = 'success';
            } else {
                $_SESSION['mensagem'] = 'Erro ao excluir a imagem.';
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        } else {
            $_SESSION['mensagem'] = 'Imagem não encontrada ou não pertence a este produto.';
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro ao excluir a imagem: ' . $e->getMessage();
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar
    header('Location: produto-imagens.php?id=' . $produto_id);
    exit;
}

// Processamento de atualização de ordem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_ordem') {
    if (isset($_POST['ordens']) && is_array($_POST['ordens'])) {
        $erros = 0;
        
        foreach ($_POST['ordens'] as $imagem_id => $ordem) {
            $imagem_id = (int)$imagem_id;
            $ordem = (int)$ordem;
            
            // Verificar se a imagem pertence ao produto
            $imagem = buscarRegistro('produto_imagens', $imagem_id);
            
            if ($imagem && $imagem['produto_id'] == $produto_id) {
                // Atualizar a ordem
                $dados = ['ordem' => $ordem];
                
                if (!atualizarRegistro('produto_imagens', $dados, $imagem_id)) {
                    $erros++;
                }
            }
        }
        
        if ($erros == 0) {
            $_SESSION['mensagem'] = 'Ordem das imagens atualizada com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Houve erros ao atualizar a ordem de algumas imagens.';
            $_SESSION['mensagem_tipo'] = 'warning';
        }
    }
    
    // Redirecionar
    header('Location: produto-imagens.php?id=' . $produto_id);
    exit;
}

// Buscar todas as imagens do produto
$imagens = buscarRegistros('produto_imagens', "produto_id = {$produto_id}", 'ordem ASC, id ASC');

// Incluir o cabeçalho
include 'includes/header.php';
?>

<!-- Informações do produto -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <?php if ($produto['imagem_principal']): ?>
            <img src="../assets/img/produtos/<?php echo $produto['imagem_principal']; ?>" alt="<?php echo $produto['nome']; ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
            <?php else: ?>
            <div class="bg-light text-center" style="width: 80px; height: 80px; line-height: 80px;">
                <i class="fas fa-image text-muted fa-2x"></i>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <h5 class="mb-1">Gerenciando imagens do produto: <?php echo $produto['nome']; ?></h5>
            <p class="mb-0">
                <span class="badge bg-primary me-2">ID: <?php echo $produto['id']; ?></span>
                <span class="badge bg-success me-2">Preço: <?php echo formatarPreco($produto['preco']); ?></span>
                
                <?php
                $categoria = buscarRegistro('categorias', $produto['categoria_id']);
                if ($categoria):
                ?>
                <span class="badge bg-info">Categoria: <?php echo $categoria['nome']; ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upload de novas imagens -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Adicionar Novas Imagens</h5>
            </div>
            <div class="card-body">
                <form action="produto-imagens.php?id=<?php echo $produto_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="upload">
                    
                    <!-- Seleção de imagens -->
                    <div class="mb-3">
                        <label for="imagens" class="form-label">Selecione as imagens</label>
                        <input type="file" class="form-control" id="imagens" name="imagens[]" accept="image/*" multiple required>
                        <div class="form-text">
                            Você pode selecionar várias imagens de uma vez. Formatos aceitos: JPG, PNG.
                        </div>
                    </div>
                    
                    <!-- Ordem -->
                    <div class="mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" value="0" min="0">
                        <div class="form-text">
                            Defina a ordem de exibição das imagens. Valores menores aparecem primeiro.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i> Fazer Upload
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Dicas para imagens -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Dicas para Imagens</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Use imagens de alta qualidade com boa iluminação.</li>
                    <li>Mantenha as dimensões padronizadas (800x800 pixels).</li>
                    <li>Mostre o produto de diferentes ângulos.</li>
                    <li>Otimize o tamanho das imagens para carregamento rápido.</li>
                    <li>Utilize fundo branco ou neutro para destacar o produto.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Lista de imagens -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Imagens do Produto</h5>
                <span class="badge bg-primary"><?php echo count($imagens); ?> imagens</span>
            </div>
            <div class="card-body">
                <?php if (count($imagens) > 0): ?>
                <form action="produto-imagens.php?id=<?php echo $produto_id; ?>" method="post">
                    <input type="hidden" name="acao" value="atualizar_ordem">
                    
                    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                        <?php foreach ($imagens as $imagem): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="position-relative">
                                    <img src="../assets/img/<?php echo $imagem['imagem']; ?>" class="card-img-top" alt="Imagem do produto" style="height: 180px; object-fit: cover;">
                                    <a href="produto-imagens.php?id=<?php echo $produto_id; ?>&excluir=<?php echo $imagem['id']; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta imagem?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label for="ordem_<?php echo $imagem['id']; ?>" class="form-label">Ordem:</label>
                                        <input type="number" class="form-control form-control-sm" id="ordem_<?php echo $imagem['id']; ?>" name="ordens[<?php echo $imagem['id']; ?>]" value="<?php echo $imagem['ordem']; ?>" min="0">
                                    </div>
                                    <small class="text-muted">ID: <?php echo $imagem['id']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Atualizar Ordem
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-images fa-4x text-muted"></i>
                    </div>
                    <h5>Nenhuma imagem adicional</h5>
                    <p class="text-muted">Este produto possui apenas a imagem principal. Adicione mais imagens para mostrar diferentes ângulos ou detalhes.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>