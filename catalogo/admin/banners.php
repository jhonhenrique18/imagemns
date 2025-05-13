<?php
// Configurações da página
$titulo_pagina = 'Gerenciar Banners';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? sanitizar($_POST['titulo']) : '';
    $link = isset($_POST['link']) ? sanitizar($_POST['link']) : '';
    $ordem = isset($_POST['ordem']) ? (int)$_POST['ordem'] : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validar dados
    if (empty($titulo)) {
        $_SESSION['mensagem'] = 'Por favor, preencha o título do banner.';
        $_SESSION['mensagem_tipo'] = 'danger';
    } else {
        try {
            // Preparar dados
            $dados = [
                'titulo' => $titulo,
                'link' => $link,
                'ordem' => $ordem,
                'ativo' => $ativo
            ];
            
            // Processar imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $imagem = uploadImagem($_FILES['imagem'], 'banners');
                
                if ($imagem) {
                    $dados['imagem'] = $imagem;
                } else {
                    $_SESSION['mensagem'] = 'Erro ao fazer upload da imagem.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                    header('Location: banners.php');
                    exit;
                }
            } elseif ($id == 0) { // Nova inserção sem imagem
                $_SESSION['mensagem'] = 'Por favor, selecione uma imagem para o banner.';
                $_SESSION['mensagem_tipo'] = 'danger';
                header('Location: banners.php');
                exit;
            }
            
            // Inserir ou atualizar
            if ($id > 0) {
                // Atualizar banner existente
                if (atualizarRegistro('banners', $dados, $id)) {
                    $_SESSION['mensagem'] = 'Banner atualizado com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao atualizar o banner.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } else {
                // Inserir novo banner
                if (inserirRegistro('banners', $dados)) {
                    $_SESSION['mensagem'] = 'Banner adicionado com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao adicionar o banner.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro no banco de dados: ' . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: banners.php');
    exit;
}

// Ação para exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    try {
        // Obter o banner para excluir a imagem
        $banner = buscarRegistro('banners', $id);
        
        // Excluir o banner
        if ($banner && excluirRegistro('banners', $id)) {
            // Excluir a imagem do servidor
            if ($banner['imagem'] && file_exists("../assets/img/banners/{$banner['imagem']}")) {
                unlink("../assets/img/banners/{$banner['imagem']}");
            }
            
            $_SESSION['mensagem'] = 'Banner excluído com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao excluir o banner.';
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro ao excluir o banner: ' . $e->getMessage();
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar
    header('Location: banners.php');
    exit;
}

// Ação para edição
$banner_edit = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $banner_edit = buscarRegistro('banners', $id);
}

// Buscar todos os banners
$banners = buscarRegistros('banners', '', 'ordem ASC, id DESC');

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="row">
    <!-- Formulário de cadastro/edição -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><?php echo $banner_edit ? 'Editar Banner' : 'Novo Banner'; ?></h5>
            </div>
            <div class="card-body">
                <form action="banners.php" method="post" enctype="multipart/form-data">
                    <!-- ID oculto para edição -->
                    <?php if ($banner_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $banner_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Título do banner -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $banner_edit ? $banner_edit['titulo'] : ''; ?>" required>
                    </div>
                    
                    <!-- Link (opcional) -->
                    <div class="mb-3">
                        <label for="link" class="form-label">Link</label>
                        <input type="text" class="form-control" id="link" name="link" value="<?php echo $banner_edit ? $banner_edit['link'] : ''; ?>" placeholder="URL para redirecionamento (opcional)">
                    </div>
                    
                    <!-- Ordem -->
                    <div class="mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" value="<?php echo $banner_edit ? $banner_edit['ordem'] : '0'; ?>" min="0">
                        <div class="form-text">Define a ordem de exibição (crescente). Banners com o mesmo valor de ordem são ordenados por ID.</div>
                    </div>
                    
                    <!-- Imagem -->
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Imagem <?php echo $banner_edit ? '' : '<span class="text-danger">*</span>'; ?></label>
                        <input type="file" class="form-control input-image" id="imagem" name="imagem" accept="image/*" <?php echo $banner_edit ? '' : 'required'; ?>>
                        <div class="form-text">Recomendação: imagem com 1200x400 pixels. Formatos aceitos: JPG, PNG.</div>
                        
                        <?php if ($banner_edit && $banner_edit['imagem']): ?>
                        <div class="mt-2">
                            <p class="mb-1">Imagem atual:</p>
                            <div class="position-relative d-inline-block">
                                <img src="../assets/img/banners/<?php echo $banner_edit['imagem']; ?>" alt="<?php echo $banner_edit['titulo']; ?>" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="mt-2 d-none">
                            <p class="mb-1">Pré-visualização:</p>
                            <img src="#" alt="Pré-visualização" class="img-thumbnail image-preview" style="max-width: 100%; max-height: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo (!$banner_edit || $banner_edit['ativo']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Banner ativo</label>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> <?php echo $banner_edit ? 'Atualizar Banner' : 'Salvar Banner'; ?>
                        </button>
                        
                        <?php if ($banner_edit): ?>
                        <a href="banners.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-1"></i> Novo Banner
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Lista de banners -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Banners</h5>
                <span class="badge bg-primary"><?php echo count($banners); ?> banners</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($banners) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 150px;">Imagem</th>
                                <th>Título</th>
                                <th>Link</th>
                                <th>Ordem</th>
                                <th>Status</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banners as $banner): ?>
                            <tr>
                                <td><?php echo $banner['id']; ?></td>
                                <td>
                                    <?php if ($banner['imagem']): ?>
                                    <img src="../assets/img/banners/<?php echo $banner['imagem']; ?>" alt="<?php echo $banner['titulo']; ?>" class="img-thumbnail" style="max-width: 120px; max-height: 60px;">
                                    <?php else: ?>
                                    <div class="bg-light text-center" style="width: 120px; height: 60px; line-height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $banner['titulo']; ?></td>
                                <td>
                                    <?php if ($banner['link']): ?>
                                    <a href="<?php echo $banner['link']; ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;"><?php echo $banner['link']; ?></a>
                                    <?php else: ?>
                                    <span class="text-muted">Sem link</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $banner['ordem']; ?></td>
                                <td>
                                    <?php if ($banner['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="banners.php?editar=<?php echo $banner['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="banners.php?excluir=<?php echo $banner['id']; ?>" class="btn btn-sm btn-danger btn-excluir" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este banner?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Nenhum banner encontrado.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Instruções para banners -->
<div class="mt-4">
    <div class="alert alert-info" role="alert">
        <h5><i class="fas fa-info-circle me-2"></i> Dicas para banners eficientes</h5>
        <ul class="mb-0">
            <li>Use imagens de alta qualidade, preferencialmente na resolução 1200x400 pixels.</li>
            <li>Textos na imagem devem ser grandes e legíveis em dispositivos móveis.</li>
            <li>Mantenha o tamanho dos arquivos abaixo de 300KB para carregamento rápido.</li>
            <li>Use a opção de "ordem" para controlar a sequência de exibição dos banners.</li>
            <li>Banners inativos não são exibidos no site, mas permanecem salvos para uso futuro.</li>
        </ul>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>