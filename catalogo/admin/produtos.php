<?php
// Configurações da página
$titulo_pagina = 'Productos';

// Incluir arquivo de conexão
require_once 'includes/conexao.php';

// Pegar parâmetros da URL
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busca = isset($_GET['busca']) ? sanitizar($_GET['busca']) : '';
$ordem = isset($_GET['ordem']) ? sanitizar($_GET['ordem']) : 'nome_asc';

// Definir condições de busca
$condicao_array = ['ativo = 1'];
$params_sql = [];

// Filtrar por categoria
if ($categoria_id > 0) {
    $condicao_array[] = "categoria_id = :categoria_id";
    $params_sql[':categoria_id'] = $categoria_id;
}

// Busca por termo
if ($busca !== '') {
    $condicao_array[] = "(nome LIKE :busca OR descricao LIKE :busca_desc)";
    $params_sql[':busca'] = '%' . $busca . '%';
    $params_sql[':busca_desc'] = '%' . $busca . '%';
}

$condicao_final_sql = implode(' AND ', $condicao_array);

// Definir ordenação
switch ($ordem) {
    case 'preco_asc':
        $ordenacao = 'preco ASC';
        break;
    case 'preco_desc':
        $ordenacao = 'preco DESC';
        break;
    case 'nome_desc':
        $ordenacao = 'nome DESC';
        break;
    case 'mais_recentes':
        $ordenacao = 'id DESC';
        break;
    default:
        $ordenacao = 'nome ASC';
}

// Buscar produtos - Assumindo que buscarRegistros foi atualizada para usar prepared statements
$produtos = buscarRegistros('produtos', $condicao_final_sql, $params_sql, $ordenacao);

// Buscar todas as categorias para o filtro
$categorias = buscarRegistros('categorias', 'ativo = 1', [], 'nome ASC');

// Buscar a categoria atual se estiver filtrado
$categoria_atual = null;
if ($categoria_id > 0) {
    $cat_data = buscarRegistro('categorias', $categoria_id);
    if ($cat_data) {
        $categoria_atual = $cat_data;
    }
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<!-- Classe da página para JavaScript -->
<div class="produtos-page">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <?php if ($categoria_atual): ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($categoria_atual['nome']); ?></li>
            <?php else: ?>
            <li class="breadcrumb-item active" aria-current="page">Todos los Productos</li>
            <?php endif; ?>
        </ol>
    </nav>
    
    <!-- Título da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <?php if ($categoria_atual): ?>
                <?php echo htmlspecialchars($categoria_atual['nome']); ?>
            <?php elseif ($busca): ?>
                Resultados para: "<?php echo htmlspecialchars($busca); ?>"
            <?php else: ?>
                Todos los Productos
            <?php endif; ?>
        </h1>
    </div>
    
    <!-- Filtros de categorias (versão horizontal/pills) -->
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="produtos.php" class="btn btn-outline-secondary filtro-categoria <?php echo $categoria_id === 0 ? 'active' : ''; ?>" data-categoria="todos">
                Todos
            </a>
            <?php if (!empty($categorias)): ?>
                <?php foreach ($categorias as $categoria): ?>
                <a href="produtos.php?categoria=<?php echo (int)$categoria['id']; ?>" class="btn btn-outline-secondary filtro-categoria <?php echo $categoria_id === (int)$categoria['id'] ? 'active' : ''; ?>" data-categoria="<?php echo (int)$categoria['id']; ?>">
                    <?php echo htmlspecialchars($categoria['nome']); ?>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Lista de produtos -->
    <?php if (!empty($produtos) && count($produtos) > 0): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
        <?php foreach ($produtos as $produto): ?>
        <div class="col">
            <div class="card h-100 produto-card shadow-sm">
                <?php if (isset($produto['preco_atacado']) && $produto['preco_atacado'] > 0): ?>
                <div class="position-absolute end-0 top-0 p-2 z-1">
                    <span class="badge bg-warning text-dark">Mayorista</span>
                </div>
                <?php endif; ?>
                
                <a href="produto-detalhe.php?id=<?php echo (int)$produto['id']; ?>">
                    <?php 
                    $imagem_nome_original = $produto['imagem_principal'] ?? 'default_placeholder.jpg';
                    $caminho_base_relativo = 'assets/img/';
                    $placeholder_default = $caminho_base_relativo . 'produto_placeholder.jpg';

                    $caminho_final_imagem = $placeholder_default;

                    // Tentativa 1: caminho completo como está no BD (se já tiver 'produtos/')
                    $teste_caminho_1 = $caminho_base_relativo . $imagem_nome_original;
                    // Tentativa 2: assumindo que imagem_principal é só o nome do arquivo dentro de 'produtos/'
                    $teste_caminho_2 = $caminho_base_relativo . 'produtos/' . basename($imagem_nome_original);
                    // Tentativa 3: assumindo que imagem_principal é só o nome do arquivo dentro de 'assets/img/'
                    $teste_caminho_3 = $caminho_base_relativo . basename($imagem_nome_original); 

                    if (file_exists($teste_caminho_1)) {
                        $caminho_final_imagem = $teste_caminho_1;
                    } elseif (file_exists($teste_caminho_2)) {
                        $caminho_final_imagem = $teste_caminho_2;
                    } elseif (file_exists($teste_caminho_3)) {
                        $caminho_final_imagem = $teste_caminho_3;
                    }

                    // Criar placeholder se não existir e for o caminho final
                    if ($caminho_final_imagem === $placeholder_default && !file_exists($placeholder_default)) {
                        $placeholder_dir = dirname($placeholder_default);
                        if (!is_dir($placeholder_dir)) {
                            mkdir($placeholder_dir, 0755, true);
                        }
                        $img_placeholder = imagecreatetruecolor(400, 400);
                        $bg_color_placeholder = imagecolorallocate($img_placeholder, 240, 240, 240);
                        $text_color_placeholder = imagecolorallocate($img_placeholder, 100, 100, 100);
                        imagefilledrectangle($img_placeholder, 0, 0, 399, 399, $bg_color_placeholder);
                        imagestring($img_placeholder, 5, 130, 190, 'Imagen no disponible', $text_color_placeholder);
                        imagejpeg($img_placeholder, $placeholder_default, 90);
                        imagedestroy($img_placeholder);
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($caminho_final_imagem); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produto['nome']); ?>" style="height: 180px; object-fit: cover;">
                </a>
                
                <div class="card-body d-flex flex-column">
                    <a href="produto-detalhe.php?id=<?php echo (int)$produto['id']; ?>" class="text-decoration-none text-dark">
                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                    </a>
                    
                    <div class="mt-auto">
                        <div class="card-preco mb-2">
                            <span class="fs-5 fw-bold text-success"><?php echo formatarPreco($produto['preco'] ?? 0); ?></span>
                            
                            <?php if (isset($produto['preco_atacado']) && $produto['preco_atacado'] > 0 && isset($produto['quantidade_atacado'])): ?>
                            <small class="d-block text-muted">
                                <?php echo (int)$produto['quantidade_atacado']; ?>+ und: <?php echo formatarPreco($produto['preco_atacado']); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center mb-2">
                            <div class="input-group input-group-sm me-sm-2 mb-2 mb-sm-0" style="max-width: 120px;">
                                <button class="btn btn-outline-secondary btn-qtd-diminuir" type="button" data-id="<?php echo (int)$produto['id']; ?>">-</button>
                                <input type="number" class="form-control text-center input-qtd" value="1" min="1" data-id="<?php echo (int)$produto['id']; ?>" aria-label="Quantidade">
                                <button class="btn btn-outline-secondary btn-qtd-aumentar" type="button" data-id="<?php echo (int)$produto['id']; ?>">+</button>
                            </div>
                            
                            <button class="btn btn-success btn-sm flex-grow-1 btn-adicionar-direto" 
                                    data-id="<?php echo (int)$produto['id']; ?>"
                                    data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                    data-preco="<?php echo htmlspecialchars($produto['preco'] ?? 0); ?>"
                                    data-preco-atacado="<?php echo htmlspecialchars($produto['preco_atacado'] ?? 0); ?>"
                                    data-quantidade-atacado="<?php echo htmlspecialchars($produto['quantidade_atacado'] ?? 0); ?>"
                                    data-peso="<?php echo htmlspecialchars($produto['peso'] ?? 0); ?>"
                                    data-imagem="<?php echo htmlspecialchars(basename($caminho_final_imagem)); ?>">
                                <i class="fas fa-cart-plus me-1"></i> Agregar
                            </button>
                        </div>
                        
                        <a href="produto-detalhe.php?id=<?php echo (int)$produto['id']; ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> Ver detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center p-5 mb-4">
        <i class="fas fa-search fa-3x mb-3"></i>
        <h4>No se encontraron productos</h4>
        <p>Intente con otros filtros o términos de búsqueda.</p>
        
        <?php if ($categoria_id > 0 || $busca): ?>
        <a href="produtos.php" class="btn btn-success mt-3">Ver todos los productos</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="bg-light p-4 rounded shadow-sm text-center mb-4 animate-on-scroll">
        <h3>¿No encontró lo que buscaba?</h3>
        <p class="mb-3">Contáctenos y le ayudaremos a encontrar el producto que necesita.</p>
        <a href="https://wa.me/<?php echo defined('WHATSAPP') ? htmlspecialchars(WHATSAPP) : ''; ?>" target="_blank" class="btn btn-success">
            <i class="fab fa-whatsapp me-2"></i> Enviar mensaje
        </a>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-qtd-aumentar').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = this.dataset.id;
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            if(input) input.value = parseInt(input.value) + 1;
        });
    });
    
    document.querySelectorAll('.btn-qtd-diminuir').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = this.dataset.id;
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            if(input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    document.querySelectorAll('.btn-adicionar-direto').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = this.dataset.id;
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            const quantidade = input ? parseInt(input.value) : 1;
            
            const produto = {
                id: parseInt(this.dataset.id),
                nome: this.dataset.nome,
                preco: parseFloat(this.dataset.preco),
                preco_atacado: parseFloat(this.dataset.precoAtacado) || 0,
                quantidade_atacado: parseInt(this.dataset.quantidadeAtacado) || 0,
                peso: parseFloat(this.dataset.peso) || 0,
                imagem: this.dataset.imagem
            };
            
            if (typeof adicionarAoCarrinho === 'function') {
                 for (let i = 0; i < quantidade; i++) {
                    adicionarAoCarrinho(produto);
                }
                if(input) input.value = 1; // Resetar quantidade
            } else {
                console.error('Função adicionarAoCarrinho não definida.');
                alert('Ocorreu um erro ao adicionar o produto ao carrinho.');
            }
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
