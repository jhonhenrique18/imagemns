<?php
// Configurações da página
$titulo_pagina = 'Gerenciar Pedidos';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Atualização de status
if (isset($_GET['atualizar_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = sanitizar($_GET['status']);
    
    $status_validos = ['pendente', 'confirmado', 'enviado', 'entregue', 'cancelado'];
    
    if (in_array($status, $status_validos)) {
        try {
            $dados = ['status' => $status];
            
            if (atualizarRegistro('pedidos', $dados, $id)) {
                $_SESSION['mensagem'] = 'Status do pedido atualizado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'success';
            } else {
                $_SESSION['mensagem'] = 'Erro ao atualizar o status do pedido.';
                $_SESSION['mensagem_tipo'] = 'danger';
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro ao atualizar o status: ' . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } else {
        $_SESSION['mensagem'] = 'Status inválido.';
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar para a lista de pedidos
    header('Location: pedidos.php');
    exit;
}

// Parâmetros de filtro e paginação
$status = isset($_GET['status']) ? sanitizar($_GET['status']) : '';
$data_inicio = isset($_GET['data_inicio']) ? sanitizar($_GET['data_inicio']) : '';
$data_fim = isset($_GET['data_fim']) ? sanitizar($_GET['data_fim']) : '';
$busca = isset($_GET['busca']) ? sanitizar($_GET['busca']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Construir condição de busca
$condicao = '1=1';

if ($status) {
    $condicao .= " AND status = '{$status}'";
}

if ($data_inicio) {
    $data_inicio_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_inicio)));
    $condicao .= " AND DATE(created_at) >= '{$data_inicio_formatada}'";
}

if ($data_fim) {
    $data_fim_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_fim)));
    $condicao .= " AND DATE(created_at) <= '{$data_fim_formatada}'";
}

if ($busca) {
    $condicao .= " AND (nome LIKE '%{$busca}%' OR email LIKE '%{$busca}%' OR telefone LIKE '%{$busca}%')";
}

// Obter número total de pedidos
$total_pedidos = contarRegistros('pedidos', $condicao);

// Calcular total de páginas
$total_paginas = ceil($total_pedidos / $por_pagina);

// Ajustar página atual
$pagina = max(1, min($pagina, $total_paginas));

// Calcular offset para a consulta
$offset = ($pagina - 1) * $por_pagina;

// Buscar pedidos com paginação
$pedidos = buscarRegistros('pedidos', $condicao, 'created_at DESC', "{$offset}, {$por_pagina}");

// Incluir o cabeçalho
include 'includes/header.php';
?>

<!-- Botões de ação -->
<div class="mb-4">
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Imprimir
    </button>
    
    <!-- Botão de atualizar página -->
    <a href="pedidos.php" class="btn btn-outline-secondary ms-2">
        <i class="fas fa-sync-alt me-1"></i> Atualizar
    </a>
</div>

<!-- Filtros de busca -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Filtros</h5>
    </div>
    <div class="card-body">
        <form action="pedidos.php" method="get" class="row g-3">
            <!-- Busca por cliente -->
            <div class="col-md-4">
                <label for="busca" class="form-label">Busca</label>
                <input type="text" class="form-control" id="busca" name="busca" value="<?php echo $busca; ?>" placeholder="Nome, email ou telefone">
            </div>
            
            <!-- Filtro por status -->
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo $status == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="confirmado" <?php echo $status == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="enviado" <?php echo $status == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                    <option value="entregue" <?php echo $status == 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                    <option value="cancelado" <?php echo $status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <!-- Filtro por data inicial -->
            <div class="col-md-3">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            
            <!-- Filtro por data final -->
            <div class="col-md-3">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            
            <!-- Botões de ação do filtro -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filtrar
                </button>
                <a href="pedidos.php" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Limpar Filtros
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de pedidos -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pedidos</h5>
        <span class="badge bg-primary"><?php echo $total_pedidos; ?> pedidos encontrados</span>
    </div>
    <div class="card-body p-0">
        <?php if (count($pedidos) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Cliente</th>
                        <th>Contato</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo $pedido['id']; ?></td>
                        <td><?php echo $pedido['nome']; ?></td>
                        <td>
                            <small>
                                <?php if ($pedido['email']): ?>
                                <div><i class="fas fa-envelope me-1"></i> <?php echo $pedido['email']; ?></div>
                                <?php endif; ?>
                                <div><i class="fas fa-phone me-1"></i> <?php echo $pedido['telefone']; ?></div>
                            </small>
                        </td>
                        <td><?php echo formatarPreco($pedido['valor_total']); ?></td>
                        <td>
                            <?php
                            $status_class = '';
                            switch ($pedido['status']) {
                                case 'pendente':
                                    $status_class = 'warning';
                                    break;
                                case 'confirmado':
                                    $status_class = 'info';
                                    break;
                                case 'enviado':
                                    $status_class = 'primary';
                                    break;
                                case 'entregue':
                                    $status_class = 'success';
                                    break;
                                case 'cancelado':
                                    $status_class = 'danger';
                                    break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($pedido['status']); ?></span>
                        </td>
                        <td><?php echo formatarData($pedido['created_at'], true); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="pedido-detalhe.php?id=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-info" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Atualizar Status">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Atualizar Status</h6></li>
                                    <li><a class="dropdown-item" href="pedidos.php?atualizar_status&id=<?php echo $pedido['id']; ?>&status=pendente">Pendente</a></li>
                                    <li><a class="dropdown-item" href="pedidos.php?atualizar_status&id=<?php echo $pedido['id']; ?>&status=confirmado">Confirmado</a></li>
                                    <li><a class="dropdown-item" href="pedidos.php?atualizar_status&id=<?php echo $pedido['id']; ?>&status=enviado">Enviado</a></li>
                                    <li><a class="dropdown-item" href="pedidos.php?atualizar_status&id=<?php echo $pedido['id']; ?>&status=entregue">Entregue</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="pedidos.php?atualizar_status&id=<?php echo $pedido['id']; ?>&status=cancelado">Cancelar</a></li>
                                </ul>
                                <a href="https://wa.me/<?php echo str_replace(['+', ' ', '-', '(', ')'], '', $pedido['telefone']); ?>" target="_blank" class="btn btn-sm btn-success" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_paginas > 1): ?>
        <!-- Paginação -->
        <div class="d-flex justify-content-center py-3">
            <nav aria-label="Paginação">
                <ul class="pagination">
                    <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busca=<?php echo $busca; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&busca=<?php echo $busca; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busca=<?php echo $busca; ?>&status=<?php echo $status; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-4">
            <p class="text-muted mb-0">Nenhum pedido encontrado.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo de pedidos -->
<div class="row mt-4 g-4">
    <!-- Total por status -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Pedidos por Status</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <?php
                            $status_list = ['pendente', 'confirmado', 'enviado', 'entregue', 'cancelado'];
                            $status_labels = [
                                'pendente' => 'Pendentes',
                                'confirmado' => 'Confirmados',
                                'enviado' => 'Enviados',
                                'entregue' => 'Entregues',
                                'cancelado' => 'Cancelados'
                            ];
                            $status_colors = [
                                'pendente' => 'warning',
                                'confirmado' => 'info',
                                'enviado' => 'primary',
                                'entregue' => 'success',
                                'cancelado' => 'danger'
                            ];
                            
                            foreach ($status_list as $s):
                                $count = contarRegistros('pedidos', "status = '{$s}'");
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo $status_colors[$s]; ?> me-2">&nbsp;</span>
                                    <?php echo $status_labels[$s]; ?>
                                </td>
                                <td class="text-end"><?php echo $count; ?></td>
                                <td>
                                    <a href="pedidos.php?status=<?php echo $s; ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-light">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong><?php echo contarRegistros('pedidos'); ?></strong></td>
                                <td>
                                    <a href="pedidos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Últimos 7 dias -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Pedidos dos Últimos 7 Dias</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <?php
                            for ($i = 0; $i < 7; $i++) {
                                $date = date('Y-m-d', strtotime("-{$i} days"));
                                $count = contarRegistros('pedidos', "DATE(created_at) = '{$date}'");
                                $formatted_date = date('d/m/Y', strtotime($date));
                                
                                echo "<tr>";
                                echo "<td>{$formatted_date}</td>";
                                echo "<td class='text-end'>{$count}</td>";
                                echo "<td><a href='pedidos.php?data_inicio={$date}&data_fim={$date}' class='btn btn-sm btn-outline-secondary'>Ver</a></td>";
                                echo "</tr>";
                            }
                            
                            // Total dos últimos 7 dias
                            $week_ago = date('Y-m-d', strtotime("-6 days"));
                            $today = date('Y-m-d');
                            $total_week = contarRegistros('pedidos', "DATE(created_at) BETWEEN '{$week_ago}' AND '{$today}'");
                            
                            echo "<tr class='table-light'>";
                            echo "<td><strong>Total (7 dias)</strong></td>";
                            echo "<td class='text-end'><strong>{$total_week}</strong></td>";
                            echo "<td><a href='pedidos.php?data_inicio={$week_ago}&data_fim={$today}' class='btn btn-sm btn-outline-primary'>Ver todos</a></td>";
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>