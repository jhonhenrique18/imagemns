<?php
// Configurações da página
$titulo_pagina = 'Detalhes do Pedido';

// Incluir arquivo de autenticação
require_once 'includes/auth.php'; // Garante $pdo, sanitizar(), formatarPreco(), formatarData()

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = 'ID do pedido não fornecido.';
    $_SESSION['mensagem_tipo'] = 'danger';
    header('Location: pedidos.php');
    exit;
}

$pedido_id = (int)$_GET['id'];

// Buscar dados do pedido
$pedido = buscarRegistro("pedidos", $pedido_id);
if (!$pedido) {
    $_SESSION['mensagem'] = 'Pedido não encontrado.';
    $_SESSION['mensagem_tipo'] = 'danger';
    header('Location: pedidos.php');
    exit;
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="pedidos.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar para Pedidos
        </a>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir Pedido
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h4 class="mb-0">Detalhes do Pedido #<?php echo $pedido['id']; ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Coluna da Esquerda: Informações do Cliente e Entrega -->
            <div class="col-md-6 mb-4 mb-md-0">
                <h5>Informações do Cliente</h5>
                <p>
                    <strong>Nome:</strong> <?php echo htmlspecialchars($pedido['nome']); ?><br>
                    <?php if (!empty($pedido['email'])): ?>
                        <strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?><br>
                    <?php endif; ?>
                    <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['telefone']); ?>
                </p>

                <h5>Endereço de Entrega</h5>
                <p>
                    <strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['endereco']); ?><br>
                    <strong>Cidade:</strong> <?php echo htmlspecialchars($pedido['cidade']); ?><br>
                    <?php if (!empty($pedido['referencia'])): ?>
                        <strong>Referência:</strong> <?php echo htmlspecialchars($pedido['referencia']); ?><br>
                    <?php endif; ?>
                </p>

                <?php if (!empty($pedido['observacoes'])):
                ?>
                <h5>Observações</h5>
                <p><?php echo nl2br(htmlspecialchars($pedido['observacoes'])); ?></p>
                <?php endif; ?>
            </div>

            <!-- Coluna da Direita: Detalhes do Pedido e Financeiro -->
            <div class="col-md-6">
                <h5>Detalhes do Pedido</h5>
                <p>
                    <strong>Data do Pedido:</strong> <?php echo formatarData($pedido['created_at'], true); ?><br>
                    <strong>Status:</strong> 
                    <?php
                    $status_class = '';
                    switch ($pedido['status']) {
                        case 'pendente': $status_class = 'warning'; break;
                        case 'confirmado': $status_class = 'info'; break;
                        case 'enviado': $status_class = 'primary'; break;
                        case 'entregue': $status_class = 'success'; break;
                        case 'cancelado': $status_class = 'danger'; break;
                    }
                    ?>
                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($pedido['status'])); ?></span><br>
                    <strong>Forma de Pagamento:</strong> <?php echo ucfirst(htmlspecialchars($pedido['forma_pagamento'])); ?>
                </p>

                <h5>Resumo Financeiro</h5>
                <p>
                    <strong>Valor dos Produtos:</strong> <?php echo formatarPreco($pedido['valor_produtos']); ?><br>
                    <strong>Valor do Frete:</strong> <?php echo formatarPreco($pedido['valor_frete']); ?><br>
                    <strong>Valor Total do Pedido:</strong> <strong class="fs-5"><?php echo formatarPreco($pedido['valor_total']); ?></strong>
                </p>
            </div>
        </div>

        <hr class="my-4">

        <h5>Itens do Pedido</h5>
        <?php if (!empty($pedido['itens_pedido'])):
            // O campo itens_pedido já vem formatado do checkout.php, com quebras de linha.
            // Apenas garantimos a segurança com htmlspecialchars e usamos nl2br para as quebras de linha.
        ?>
            <div class="bg-light p-3 rounded">
                <pre style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit; font-size: inherit;"><?php echo nl2br(htmlspecialchars($pedido['itens_pedido'])); ?></pre>
            </div>
        <?php else: ?>
            <p>Nenhum item detalhado encontrado para este pedido.</p>
        <?php endif; ?>

    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>

