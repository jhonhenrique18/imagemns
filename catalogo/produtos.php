<?php
// Configurações da página
$titulo_pagina = 'Productos';

// Incluir arquivo de conexão
require_once 'includes/conexao.php'; // Assegura que $pdo e funções como buscarRegistros estão disponíveis

// Pegar parâmetros da URL
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busca = isset($_GET['busca']) && function_exists('sanitizar') ? sanitizar($_GET['busca']) : (isset($_GET['busca']) ? $_GET['busca'] : '');
$ordem = isset($_GET['ordem']) && function_exists('sanitizar') ? sanitizar($_GET['ordem']) : (isset($_GET['ordem']) ? $_GET['ordem'] : 'nome_asc');

// Definir condições de busca
$condicao = 'ativo = 1';

// Filtrar por categoria
if ($categoria_id > 0) {
    $condicao .= " AND categoria_id = {$categoria_id}"; // Vulnerável a SQLi se $categoria_id não for (int)
}

// Busca por termo
if ($busca !== '') {
    // A função sanitizar deve proteger contra SQLi, mas idealmente usaríamos prepared statements
    $busca_segura = str_replace(['%', '_'], ['\%', '\_'], $busca); // Escapar wildcards para LIKE
    $condicao .= " AND (nome LIKE '%{$busca_segura}%' OR descricao LIKE '%{$busca_segura}%')";
}

// Definir ordenação
switch ($ordem) {
    case 'preco_asc':
        $ordenacao_sql = 'preco ASC';
        break;
    case 'preco_desc':
        $ordenacao_sql = 'preco DESC';
        break;
    case 'nome_desc':
        $ordenacao_sql = 'nome DESC';
        break;
    case 'mais_recentes':
        $ordenacao_sql = 'id DESC';
        break;
    default:
        $ordenacao_sql = 'nome ASC';
}

// Buscar produtos
$produtos = buscarRegistros('produtos', $condicao, $ordenacao_sql);

// Buscar todas as categorias para o filtro
$categorias_filtro = buscarRegistros('categorias', 'ativo = 1', 'nome ASC');

// Buscar a categoria atual se estiver filtrado
$categoria_atual_info = null;
if ($categoria_id > 0) {
    $categoria_atual_info_array = buscarRegistros('categorias', "id = {$categoria_id} AND ativo = 1");
    if (!empty($categoria_atual_info_array)) {
        $categoria_atual_info = $categoria_atual_info_array[0];
    }
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="produtos-page">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <?php if ($categoria_atual_info && isset($categoria_atual_info['nome'])): ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($categoria_atual_info['nome']); ?></li>
            <?php else: ?>
            <li class="breadcrumb-item active" aria-current="page">Todos los Productos</li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <?php if ($categoria_atual_info && isset($categoria_atual_info['nome'])): ?>
                <?php echo htmlspecialchars($categoria_atual_info['nome']); ?>
            <?php elseif ($busca): ?>
                Resultados para: "<?php echo htmlspecialchars($busca); ?>"
            <?php else: ?>
                Todos los Productos
            <?php endif; ?>
        </h1>
    </div>

    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="produtos.php" class="btn btn-outline-secondary filtro-categoria <?php echo $categoria_id === 0 ? 'active' : ''; ?>">
                Todos
            </a>
            <?php if (!empty($categorias_filtro)): foreach ($categorias_filtro as $cat_filtro): ?>
            <a href="produtos.php?categoria=<?php echo (int)$cat_filtro['id']; ?>" class="btn btn-outline-secondary filtro-categoria <?php echo $categoria_id === (int)$cat_filtro['id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat_filtro['nome']); ?>
            </a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <?php if (!empty($produtos)): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
        <?php foreach ($produtos as $produto_item): ?>
        <div class="col">
            <div class="card h-100 produto-card">
                <?php if (isset($produto_item['preco_atacado']) && $produto_item['preco_atacado'] > 0): ?>
                <div class="position-absolute end-0 top-0 p-2">
                    <span class="badge bg-warning text-dark">Mayorista</span>
                </div>
                <?php endif; ?>
                
                <a href="produto-detalhe.php?id=<?php echo (int)$produto_item['id']; ?>">
                    <?php 
                    $imagem_url = 'assets/img/placeholder.png'; // Placeholder padrão
                    if (!empty($produto_item['imagem_principal'])) {
                        // O campo imagem_principal já contém 'produtos/nome_arquivo.ext'
                        $caminho_no_servidor = 'assets/img/' . $produto_item['imagem_principal'];
                        if (file_exists($caminho_no_servidor)) {
                            $imagem_url = $caminho_no_servidor;
                        }
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($imagem_url); ?>?t=<?php echo time(); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produto_item['nome']); ?>" style="height: 180px; object-fit: cover;">
                </a>
                
                <div class="card-body d-flex flex-column">
                    <a href="produto-detalhe.php?id=<?php echo (int)$produto_item['id']; ?>" class="text-decoration-none">
                        <h5 class="card-title"><?php echo htmlspecialchars($produto_item['nome']); ?></h5>
                    </a>
                    
                    <div class="mt-auto">
                        <div class="card-preco mb-2">
                            <span class="fs-5 fw-bold text-success"><?php echo isset($produto_item['preco']) && function_exists('formatarPreco') ? htmlspecialchars(formatarPreco($produto_item['preco'])) : (isset($produto_item['preco']) ? htmlspecialchars($produto_item['preco']) : 'N/D'); ?></span>
                            
                            <?php if (isset($produto_item['preco_atacado']) && $produto_item['preco_atacado'] > 0 && isset($produto_item['quantidade_atacado'])):
                            $preco_atacado_formatado = function_exists('formatarPreco') ? htmlspecialchars(formatarPreco($produto_item['preco_atacado'])) : htmlspecialchars($produto_item['preco_atacado']);
                            ?>
                            <small class="d-block text-muted">
                                <?php echo (int)$produto_item['quantidade_atacado']; ?>+ und: <?php echo $preco_atacado_formatado; ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center mb-2">
                            <div class="input-group input-group-sm me-sm-2 mb-2 mb-sm-0" style="max-width: 120px;">
                                <button class="btn btn-outline-secondary btn-qtd-diminuir" type="button" data-id="<?php echo (int)$produto_item['id']; ?>">-</button>
                                <input type="number" class="form-control text-center input-qtd" value="1" min="1" data-id="<?php echo (int)$produto_item['id']; ?>" aria-label="Quantidade">
                                <button class="btn btn-outline-secondary btn-qtd-aumentar" type="button" data-id="<?php echo (int)$produto_item['id']; ?>">+</button>
                            </div>
                            
                            <button class="btn btn-success btn-sm flex-grow-1 btn-adicionar-direto" 
                                    data-id="<?php echo (int)$produto_item['id']; ?>"
                                    data-nome="<?php echo htmlspecialchars($produto_item['nome']); ?>"
                                    data-preco="<?php echo isset($produto_item['preco']) ? htmlspecialchars($produto_item['preco']) : 0; ?>"
                                    data-preco-atacado="<?php echo isset($produto_item['preco_atacado']) ? htmlspecialchars($produto_item['preco_atacado']) : 0; ?>"
                                    data-quantidade-atacado="<?php echo isset($produto_item['quantidade_atacado']) ? (int)$produto_item['quantidade_atacado'] : 0; ?>"
                                    data-peso="<?php echo isset($produto_item['peso']) ? htmlspecialchars($produto_item['peso']) : 0; ?>"
                                    data-imagem="<?php echo !empty($produto_item['imagem_principal']) ? htmlspecialchars($produto_item['imagem_principal']) : ''; ?>">
                                <i class="fas fa-cart-plus me-1"></i> Agregar
                            </button>
                        </div>
                        
                        <a href="produto-detalhe.php?id=<?php echo (int)$produto_item['id']; ?>" class="btn btn-outline-secondary btn-sm w-100">
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
    
    <?php 
    // Verifica se a constante WHATSAPP está definida e não está vazia
    if (defined('WHATSAPP') && WHATSAPP):
    ?> 
    <div class="bg-light p-4 rounded shadow-sm text-center mb-4 animate-on-scroll">
        <h3>¿No encontró lo que buscaba?</h3>
        <p class="mb-3">Contáctenos y le ayudaremos a encontrar el producto que necesita.</p>
        <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', WHATSAPP)); ?>" target="_blank" class="btn btn-success">
            <i class="fab fa-whatsapp me-2"></i> Enviar mensaje
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-qtd-aumentar').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            if (input) {
                input.value = parseInt(input.value) + 1;
            }
        });
    });
    
    document.querySelectorAll('.btn-qtd-diminuir').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    document.querySelectorAll('.btn-adicionar-direto').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            const input = document.querySelector(`.input-qtd[data-id="${produtoId}"]`);
            const quantidade = input ? (parseInt(input.value) || 1) : 1;
            
            const produto = {
                id: produtoId,
                nome: this.dataset.nome,
                preco: parseFloat(this.dataset.preco) || 0,
                preco_atacado: parseFloat(this.dataset.precoAtacado) || 0,
                quantidade_atacado: parseInt(this.dataset.quantidadeAtacado) || 0,
                peso: parseFloat(this.dataset.peso) || 0,
                imagem: this.dataset.imagem
            };
            
            if (typeof adicionarAoCarrinho === 'function') {
                 adicionarAoCarrinho(produto, quantidade);
            } else {
                console.error('Função adicionarAoCarrinho não definida.');
                alert('Erro ao adicionar ao carrinho. Tente novamente.');
            }
            
            if (input) {
                input.value = 1;
            }
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
