<?php
// Configurações da página
$titulo_pagina = 'Cadastrar Novo Produto';
$botao_voltar = 'produtos.php';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar dados do formulário
    $nome = isset($_POST['nome']) ? sanitizar($_POST['nome']) : '';
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
    $slug = isset($_POST['slug']) ? sanitizar($_POST['slug']) : '';
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
    $preco = isset($_POST['preco']) ? str_replace(',', '.', $_POST['preco']) : 0;
    $preco_atacado = isset($_POST['preco_atacado']) ? str_replace(',', '.', $_POST['preco_atacado']) : 0;
    $quantidade_atacado = isset($_POST['quantidade_atacado']) ? (int)$_POST['quantidade_atacado'] : 10;
    $peso = isset($_POST['peso']) ? str_replace(',', '.', $_POST['peso']) : 0;
    $estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    
    // Gerar slug se estiver vazio
    if (empty($slug)) {
        $slug = gerarSlug($nome);
    }
    
    // Validação básica
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'O nome do produto é obrigatório.';
    }
    
    if ($categoria_id <= 0) {
        $erros[] = 'Selecione uma categoria válida.';
    }
    
    if ($preco <= 0) {
        $erros[] = 'O preço deve ser maior que zero.';
    }
    
    if ($peso <= 0) {
        $erros[] = 'O peso deve ser maior que zero.';
    }
    
    if (empty($erros)) {
        try {
            // Processar imagem principal
            $imagem_principal = null;
            if (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] === UPLOAD_ERR_OK) {
                $imagem_principal = uploadImagem($_FILES['imagem_principal'], 'produtos');
                
                if (!$imagem_principal) {
                    $_SESSION['mensagem'] = 'Erro ao fazer upload da imagem principal.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                    
                    // Manter dados para preencher o formulário
                    $_SESSION['form_produto'] = $_POST;
                    
                    header('Location: produto-cadastrar.php');
                    exit;
                }
            }
            
            // Preparar dados para inserção
            $dados = [
                'nome' => $nome,
                'categoria_id' => $categoria_id,
                'slug' => $slug,
                'descricao' => $descricao,
                'preco' => $preco,
                'preco_atacado' => $preco_atacado > 0 ? $preco_atacado : null,
                'quantidade_atacado' => $quantidade_atacado,
                'peso' => $peso,
                'estoque' => $estoque,
                'ativo' => $ativo,
                'destaque' => $destaque
            ];
            
            // Adicionar imagem principal, se houver
            if ($imagem_principal) {
                $dados['imagem_principal'] = $imagem_principal;
            }
            
            // Inserir produto
            $produto_id = inserirRegistro('produtos', $dados);
            
            if ($produto_id) {
                $_SESSION['mensagem'] = 'Produto cadastrado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'success';
                
                // Redirecionar para a página de imagens se tiver sucesso
                header('Location: produto-imagens.php?id=' . $produto_id);
                exit;
            } else {
                $_SESSION['mensagem'] = 'Erro ao cadastrar o produto.';
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro ao cadastrar o produto: ' . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } else {
        // Exibir erros
        $_SESSION['mensagem'] = 'Erros encontrados:<br>' . implode('<br>', $erros);
        $_SESSION['mensagem_tipo'] = 'danger';
        
        // Manter dados para preencher o formulário
        $_SESSION['form_produto'] = $_POST;
    }
    
    // Redirecionar para a mesma página
    header('Location: produto-cadastrar.php');
    exit;
}

// Recuperar dados do formulário em caso de erro
$form_data = isset($_SESSION['form_produto']) ? $_SESSION['form_produto'] : [];
unset($_SESSION['form_produto']); // Limpar após usar

// Buscar categorias para o select
$categorias = buscarRegistros('categorias', 'ativo = 1', 'nome ASC');

// Incluir o cabeçalho
include 'includes/header.php';
?>

<form action="produto-cadastrar.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <div class="row">
        <!-- Dados principais do produto -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informações do Produto</h5>
                </div>
                <div class="card-body">
                    <!-- Nome do produto -->
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($form_data['nome']) ? $form_data['nome'] : ''; ?>" required>
                        <div class="invalid-feedback">
                            Por favor, informe o nome do produto.
                        </div>
                    </div>
                    
                    <!-- Slug (opcional) -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL amigável)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo isset($form_data['slug']) ? $form_data['slug'] : ''; ?>" placeholder="Gerado automaticamente se vazio">
                        <div class="form-text">
                            URL amigável para o produto. Deixe em branco para gerar automaticamente.
                        </div>
                    </div>
                    
                    <!-- Descrição -->
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="5"><?php echo isset($form_data['descricao']) ? $form_data['descricao'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Informações de preço e estoque -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Preços e Estoque</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Preço normal -->
                        <div class="col-md-4">
                            <label for="preco" class="form-label">Preço <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">G$</span>
                                <input type="text" class="form-control" id="preco" name="preco" value="<?php echo isset($form_data['preco']) ? $form_data['preco'] : ''; ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Informe um preço válido.
                            </div>
                        </div>
                        
                        <!-- Preço atacado -->
                        <div class="col-md-4">
                            <label for="preco_atacado" class="form-label">Preço de Atacado</label>
                            <div class="input-group">
                                <span class="input-group-text">G$</span>
                                <input type="text" class="form-control" id="preco_atacado" name="preco_atacado" value="<?php echo isset($form_data['preco_atacado']) ? $form_data['preco_atacado'] : ''; ?>">
                            </div>
                            <div class="form-text">
                                Preço para compras em quantidade. Deixe em branco para não usar.
                            </div>
                        </div>
                        
                        <!-- Quantidade atacado -->
                        <div class="col-md-4">
                            <label for="quantidade_atacado" class="form-label">Qtd. Mínima Atacado</label>
                            <input type="number" class="form-control" id="quantidade_atacado" name="quantidade_atacado" value="<?php echo isset($form_data['quantidade_atacado']) ? $form_data['quantidade_atacado'] : '10'; ?>" min="1">
                            <div class="form-text">
                                Quantidade mínima para preço de atacado.
                            </div>
                        </div>
                        
                        <!-- Peso -->
                        <div class="col-md-6">
                            <label for="peso" class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="peso" name="peso" value="<?php echo isset($form_data['peso']) ? $form_data['peso'] : ''; ?>" required>
                            <div class="form-text">
                                Peso em kg para cálculo de frete (ex: 0.5, 1, 2.5).
                            </div>
                            <div class="invalid-feedback">
                                Informe o peso do produto.
                            </div>
                        </div>
                        
                        <!-- Estoque -->
                        <div class="col-md-6">
                            <label for="estoque" class="form-label">Estoque</label>
                            <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo isset($form_data['estoque']) ? $form_data['estoque'] : '0'; ?>" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Imagem e informações adicionais -->
        <div class="col-md-4">
            <!-- Categoria e status -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Categoria e Status</h5>
                </div>
                <div class="card-body">
                    <!-- Categoria -->
                    <div class="mb-3">
                        <label for="categoria_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>" <?php echo (isset($form_data['categoria_id']) && $form_data['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                <?php echo $categoria['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Selecione uma categoria.
                        </div>
                        
                        <?php if (count($categorias) === 0): ?>
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle me-1"></i> Nenhuma categoria encontrada. <a href="categorias.php">Cadastre uma categoria</a> primeiro.
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo (!isset($form_data['ativo']) || $form_data['ativo']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Produto ativo</label>
                        </div>
                        <div class="form-text">
                            Produtos inativos não são exibidos no site.
                        </div>
                    </div>
                    
                    <!-- Destaque -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="destaque" name="destaque" <?php echo (isset($form_data['destaque']) && $form_data['destaque']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="destaque">Produto em destaque</label>
                        </div>
                        <div class="form-text">
                            Produtos em destaque aparecem na página inicial.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Imagem principal -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Imagem Principal</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="imagem_principal" class="form-label">Selecione uma imagem <span class="text-danger">*</span></label>
                        <input type="file" class="form-control input-image" id="imagem_principal" name="imagem_principal" accept="image/*" required>
                        <div class="form-text">
                            Imagem principal do produto. Recomendado: 800x800px.
                        </div>
                        
                        <!-- Pré-visualização -->
                        <div class="mt-3 d-none">
                            <p class="mb-1">Pré-visualização:</p>
                            <img src="#" alt="Pré-visualização" class="img-thumbnail image-preview" style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> Após salvar o produto, você poderá adicionar mais imagens.
                    </div>
                </div>
            </div>
            
            <!-- Botões de ação -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-1"></i> Salvar Produto
                </button>
                <a href="produtos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// Validação do formulário
(function() {
    'use strict';
    
    // Fetch all forms we want to apply validation styles to
    var forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();

// Preview de imagem
document.getElementById('imagem_principal').addEventListener('change', function() {
    const input = this;
    const preview = document.querySelector('.image-preview');
    const previewContainer = preview.parentElement;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
});

// Gerar slug automaticamente
document.getElementById('nome').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    
    // Só gera slug se o campo estiver vazio
    if (slugField.value === '' && this.value !== '') {
        // Função simples para gerar slug
        const slug = this.value
            .toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove caracteres especiais
            .replace(/\s+/g, '-') // Substitui espaços por hífens
            .replace(/--+/g, '-') // Remove hífens duplicados
            .trim(); // Remove espaços no início e fim
        
        slugField.value = slug;
    }
});
</script>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>