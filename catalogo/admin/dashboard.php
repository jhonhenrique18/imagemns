<?php
// Configurações da página
$titulo_pagina = "Dashboard";

// Incluir arquivo de autenticação (espera-se que defina $pdo e funções como contarRegistros, buscarRegistros, formatarPreco, formatarData)
require_once "includes/auth.php";

// Inicializar variáveis para evitar erros caso a busca falhe
$total_produtos = 0;
$total_categorias = 0;
$total_pedidos = 0;
$pedidos_pendentes = 0;
$ultimos_pedidos = [];
$produtos_destaque = [];
$produtos_estoque_baixo = [];

// Obter estatísticas
try {
    // Total de produtos
    $total_produtos = contarRegistros("produtos");
    
    // Total de categorias
    $total_categorias = contarRegistros("categorias");
    
    // Total de pedidos
    $total_pedidos = contarRegistros("pedidos");
    
    // Pedidos pendentes
    $pedidos_pendentes = contarRegistros("pedidos", "status = 'pendente'");
    
    // Últimos pedidos
    $ultimos_pedidos = buscarRegistros("pedidos", "", "created_at DESC", "5");
    
    // Produtos em destaque
    $produtos_destaque = buscarRegistros("produtos", "destaque = 1 AND ativo = 1", "nome ASC", "5"); // Adicionado ativo = 1
    
    // Produtos com estoque baixo (ex: menos de 10 unidades e mais que 0)
    $produtos_estoque_baixo = buscarRegistros("produtos", "estoque < 10 AND estoque > 0 AND ativo = 1", "estoque ASC", "5"); // Adicionado ativo = 1
    
} catch (PDOException $e) {
    // Armazenar mensagem de erro na sessão para ser exibida no header ou em local apropriado
    $_SESSION["mensagem"] = "Erro ao carregar os dados do dashboard: " . htmlspecialchars($e->getMessage());
    $_SESSION["mensagem_tipo"] = "danger";
    // Logar o erro para depuração interna
    error_log("Erro no Dashboard Admin: " . $e->getMessage());
}

// Incluir o cabeçalho (espera-se que exiba mensagens da sessão)
include "includes/header.php";
?>

<!-- Cards de estatísticas -->
<div class="row g-4 mb-4">
    <!-- Total de produtos -->
    <div class="col-sm-6 col-lg-3 mb-4 mb-lg-0">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-box fa-3x"></i>
                </div>
                <h5 class="card-title">Produtos</h5>
                <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($total_produtos); ?></p>
            </div>
            <div class="card-footer bg-light border-0">
                <a href="produtos.php" class="btn btn-sm btn-outline-success">Ver Todos</a>
            </div>
        </div>
    </div>
    
    <!-- Total de categorias -->
    <div class="col-sm-6 col-lg-3 mb-4 mb-lg-0">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-tags fa-3x"></i>
                </div>
                <h5 class="card-title">Categorias</h5>
                <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($total_categorias); ?></p>
            </div>
            <div class="card-footer bg-light border-0">
                <a href="categorias.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
        </div>
    </div>
    
    <!-- Total de pedidos -->
    <div class="col-sm-6 col-lg-3 mb-4 mb-sm-0">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                </div>
                <h5 class="card-title">Total de Pedidos</h5>
                <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($total_pedidos); ?></p>
            </div>
            <div class="card-footer bg-light border-0">
                <a href="pedidos.php" class="btn btn-sm btn-outline-info">Ver Todos</a>
            </div>
        </div>
    </div>
    
    <!-- Pedidos pendentes -->
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100 shadow-sm">
            <div class="card-body">
                <div class="text-warning mb-2">
                    <i class="fas fa-clock fa-3x"></i>
                </div>
                <h5 class="card-title">Pedidos Pendentes</h5>
                <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($pedidos_pendentes); ?></p>
            </div>
            <div class="card-footer bg-light border-0">
                <a href="pedidos.php?status=pendente" class="btn btn-sm btn-outline-warning">Ver Pendentes</a>
            </div>
        </div>
    </div>
</div>

<!-- Conteúdo principal -->
<div class="row g-4">
    <!-- Últimos pedidos -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Últimos Pedidos</h5>
                <a href="pedidos.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($ultimos_pedidos) && count($ultimos_pedidos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos_pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido["id"]); ?></td>
                                <td><?php echo htmlspecialchars($pedido["nome"]); ?></td>
                                <td><?php echo htmlspecialchars(formatarPreco($pedido["valor_total"] ?? 0)); ?></td>
                                <td>
                                    <?php
                                    $status_class = "secondary"; // Default class
                                    $status_texto = ucfirst(htmlspecialchars($pedido["status"] ?? "desconhecido"));
                                    switch ($pedido["status"]) {
                                        case "pendente": $status_class = "warning"; break;
                                        case "confirmado": $status_class = "info"; break;
                                        case "enviado": $status_class = "primary"; break;
                                        case "entregue": $status_class = "success"; break;
                                        case "cancelado": $status_class = "danger"; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_texto; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars(formatarData($pedido["created_at"] ?? "", true)); ?></td>
                                <td class="text-end">
                                    <a href="pedido-detalhe.php?id=<?php echo htmlspecialchars($pedido["id"]); ?>" class="btn btn-sm btn-outline-secondary" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center p-4">
                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Nenhum pedido encontrado recentemente.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Produtos em destaque -->
    <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Produtos em Destaque</h5>
                <a href="produtos.php?destaque=1" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($produtos_destaque) && count($produtos_destaque) > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($produtos_destaque as $produto): ?>
                    <?php 
                        // Determinar o caminho da imagem com fallback
                        $imagem_path_destaque = "../assets/img/produto_placeholder.jpg"; // Placeholder padrão
                        if (!empty($produto["imagem_principal"])) {
                            $test_paths_destaque = [
                                "../assets/img/" . $produto["imagem_principal"],
                                "../assets/img/produtos/" . basename($produto["imagem_principal"])
                            ];
                            foreach ($test_paths_destaque as $path_destaque) {
                                if (file_exists($path_destaque)) {
                                    $imagem_path_destaque = $path_destaque;
                                    break;
                                }
                            }
                        }
                    ?>
                    <li class="list-group-item d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($imagem_path_destaque); ?>" alt="<?php echo htmlspecialchars($produto["nome"]); ?>" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-truncate" title="<?php echo htmlspecialchars($produto["nome"]); ?>"><?php echo htmlspecialchars($produto["nome"]); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars(formatarPreco($produto["preco"] ?? 0)); ?></small>
                        </div>
                        <a href="produto-editar.php?id=<?php echo htmlspecialchars($produto["id"]); ?>" class="btn btn-sm btn-outline-secondary ms-2" title="Editar Produto">
                            <i class="fas fa-edit"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="text-center p-4">
                    <i class="fas fa-star-half-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Nenhum produto em destaque no momento.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Produtos com estoque baixo -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-battery-quarter me-2 text-danger"></i>Produtos com Estoque Baixo</h5>
                <a href="produtos.php?estoque_baixo=1" class="btn btn-sm btn-outline-danger">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($produtos_estoque_baixo) && count($produtos_estoque_baixo) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Estoque</th>
                                <th>Preço</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos_estoque_baixo as $produto): ?>
                            <?php 
                                // Determinar o caminho da imagem com fallback
                                $imagem_path_estoque = "../assets/img/produto_placeholder.jpg"; // Placeholder padrão
                                if (!empty($produto["imagem_principal"])) {
                                    $test_paths_estoque = [
                                        "../assets/img/" . $produto["imagem_principal"],
                                        "../assets/img/produtos/" . basename($produto["imagem_principal"])
                                    ];
                                    foreach ($test_paths_estoque as $path_estoque) {
                                        if (file_exists($path_estoque)) {
                                            $imagem_path_estoque = $path_estoque;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($imagem_path_estoque); ?>" alt="<?php echo htmlspecialchars($produto["nome"]); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        <span class="text-truncate" title="<?php echo htmlspecialchars($produto["nome"]); ?>"><?php echo htmlspecialchars($produto["nome"]); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($produto["estoque"] ?? 0); ?> und</span>
                                </td>
                                <td><?php echo htmlspecialchars(formatarPreco($produto["preco"] ?? 0)); ?></td>
                                <td class="text-end">
                                    <a href="produto-editar.php?id=<?php echo htmlspecialchars($produto["id"]); ?>" class="btn btn-sm btn-outline-primary" title="Editar Produto">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center p-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-muted mb-0">Não há produtos com estoque baixo. Ótimo trabalho!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include "includes/footer.php";
?>
