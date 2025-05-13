<?php
// Configurações da página
$titulo_pagina = 'Gerenciar Categorias';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? sanitizar($_POST['nome']) : '';
    $slug = isset($_POST['slug']) ? sanitizar($_POST['slug']) : '';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Gerar slug se estiver vazio
    if (empty($slug)) {
        $slug = gerarSlug($nome);
    }
    
    // Validar dados
    if (empty($nome)) {
        $_SESSION['mensagem'] = 'Por favor, preencha o nome da categoria.';
        $_SESSION['mensagem_tipo'] = 'danger';
    } else {
        // Processar imagem, se houver
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem = uploadImagem($_FILES['imagem'], 'categorias');
            
            if (!$imagem) {
                $_SESSION['mensagem'] = 'Erro ao fazer upload da imagem.';
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        }
        
        try {
            // Preparar dados
            $dados = [
                'nome' => $nome,
                'slug' => $slug,
                'ativo' => $ativo
            ];
            
            // Adicionar imagem se foi enviada
            if ($imagem) {
                $dados['imagem'] = $imagem;
            }
            
            // Inserir ou atualizar
            if ($id > 0) {
                // Atualizar categoria existente
                if (atualizarRegistro('categorias', $dados, $id)) {
                    $_SESSION['mensagem'] = 'Categoria atualizada com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao atualizar a categoria.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } else {
                // Inserir nova categoria
                if (inserirRegistro('categorias', $dados)) {
                    $_SESSION['mensagem'] = 'Categoria adicionada com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao adicionar a categoria.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro no banco de dados: ' . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: categorias.php');
    exit;
}

// Ação para exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    try {
        // Verificar se há produtos nesta categoria
        $count = contarRegistros('produtos', "categoria_id = {$id}");
        
        if ($count > 0) {
            $_SESSION['mensagem'] = "Não é possível excluir a categoria pois existem {$count} produtos associados a ela.";
            $_SESSION['mensagem_tipo'] = 'warning';
        } else {
            // Obter a categoria para excluir a imagem
            $categoria = buscarRegistro('categorias', $id);
            
            // Excluir a categoria
            if (excluirRegistro('categorias', $id)) {
                // Excluir a imagem do servidor
                if ($categoria['imagem'] && file_exists("../assets/img/categorias/{$categoria['imagem']}")) {
                    unlink("../assets/img/categorias/{$categoria['imagem']}");
                }
                
                $_SESSION['mensagem'] = 'Categoria excluída com sucesso!';
                $_SESSION['mensagem_tipo'] = 'success';
            } else {
                $_SESSION['mensagem'] = 'Erro ao excluir a categoria.';
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro ao excluir a categoria: ' . $e->getMessage();
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar
    header('Location: categorias.php');
    exit;
}

// Ação para edição
$categoria_edit = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $categoria_edit = buscarRegistro('categorias', $id);
}

// Buscar todas as categorias
$categorias = buscarRegistros('categorias', '', 'nome ASC');

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="row">
    <!-- Formulário de cadastro/edição -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><?php echo $categoria_edit ? 'Editar Categoria' : 'Nova Categoria'; ?></h5>
            </div>
            <div class="card-body">
                <form action="categorias.php" method="post" enctype="multipart/form-data">
                    <!-- ID oculto para edição -->
                    <?php if ($categoria_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $categoria_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Nome da categoria -->
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $categoria_edit ? $categoria_edit['nome'] : ''; ?>" required>
                    </div>
                    
                    <!-- Slug (opcional) -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $categoria_edit ? $categoria_edit['slug'] : ''; ?>" placeholder="Gerado automaticamente se vazio">
                        <div class="form-text">URL amigável para a categoria (sem espaços, acentos ou caracteres especiais).</div>
                    </div>
                    
                    <!-- Imagem -->
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Imagem</label>
                        <input type="file" class="form-control input-image" id="imagem" name="imagem" accept="image/*">
                        
                        <?php if ($categoria_edit && $categoria_edit['imagem']): ?>
                        <div class="mt-2">
                            <p class="mb-1">Imagem atual:</p>
                            <div class="position-relative d-inline-block">
                                <img src="../assets/img/categorias/<?php echo $categoria_edit['imagem']; ?>" alt="<?php echo $categoria_edit['nome']; ?>" class="img-thumbnail" style="max-width: 150px;">
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mt-2 d-none">
                            <p class="mb-1">Pré-visualização:</p>
                            <img src="#" alt="Pré-visualização" class="img-thumbnail image-preview" style="max-width: 150px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo (!$categoria_edit || $categoria_edit['ativo']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Categoria ativa</label>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> <?php echo $categoria_edit ? 'Atualizar Categoria' : 'Salvar Categoria'; ?>
                        </button>
                        
                        <?php if ($categoria_edit): ?>
                        <a href="categorias.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-1"></i> Nova Categoria
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Lista de categorias -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Categorias</h5>
                <span class="badge bg-primary"><?php echo count($categorias); ?> categorias</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($categorias) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 80px;">Imagem</th>
                                <th>Nome</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Produtos</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?php echo $categoria['id']; ?></td>
                                <td>
                                    <?php if ($categoria['imagem']): ?>
                                    <img src="../assets/img/categorias/<?php echo $categoria['imagem']; ?>" alt="<?php echo $categoria['nome']; ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $categoria['nome']; ?></td>
                                <td><small class="text-muted"><?php echo $categoria['slug']; ?></small></td>
                                <td>
                                    <?php if ($categoria['ativo']): ?>
                                    <span class="badge bg-success">Ativa</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $total_produtos = contarRegistros('produtos', "categoria_id = {$categoria['id']}");
                                    echo "<span class='badge bg-info'>{$total_produtos}</span>";
                                    ?>
                                </td>
                                <td>
                                    <a href="categorias.php?editar=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($total_produtos == 0): ?>
                                    <a href="categorias.php?excluir=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-danger btn-excluir" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-danger" disabled title="Não é possível excluir categorias com produtos">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Nenhuma categoria encontrada.</p>
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