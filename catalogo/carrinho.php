<?php
// Configurações da página
$titulo_pagina = "Carrito de Compras";

// Incluir arquivo de conexão
require_once "includes/conexao.php";

// Incluir o cabeçalho
include "includes/header.php";

// Definições de fallback para constantes, caso não existam
if (!defined("SITE_URL")) {
    define("SITE_URL", ".");
}
if (!defined("WHATSAPP")) {
    define("WHATSAPP", "+5511999999999"); // Exemplo
}

?>

<!-- Classe da página para JavaScript -->
<div class="carrinho-page">
    
    <!-- Título da página -->
    <div class="mb-4">
        <h1><?php echo htmlspecialchars($titulo_pagina); ?></h1>
    </div>
    
    <!-- Conteúdo do carrinho -->
    <div class="row g-4">
        <!-- Lista de itens -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Productos en tu carrito</h5>
                        <button class="btn btn-outline-danger btn-sm btn-limpar-carrinho">
                            <i class="fas fa-trash me-1"></i> Vaciar Carrito
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Itens do carrinho (preenchidos via JavaScript) -->
                    <div class="carrinho-lista">
                        <!-- Placeholder: Será preenchido via JavaScript -->
                        <div class="text-center p-4 p-md-5">
                            <div class="loading-spinner mx-auto mb-3"></div>
                            <p class="text-muted">Cargando productos de tu carrito...</p>
                        </div>
                        <!-- Modelo de item do carrinho (para JS, escondido por padrão) -->
                        <div class="carrinho-item-modelo d-none p-3 border-bottom">
                            <div class="row g-3 align-items-center">
                                <div class="col-2 col-md-1">
                                    <img src="#" alt="Imagem do Produto" class="img-fluid rounded carrinho-item-img-modelo" style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                                <div class="col-10 col-md-5">
                                    <h6 class="mb-0 carrinho-item-nome-modelo">Nome do Produto</h6>
                                    <small class="text-muted carrinho-item-id-modelo">ID: #123</small>
                                </div>
                                <div class="col-5 col-md-2">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary btn-qtd-diminuir-modelo" type="button">-</button>
                                        <input type="number" class="form-control text-center input-qtd-modelo" value="1" min="1">
                                        <button class="btn btn-outline-secondary btn-qtd-aumentar-modelo" type="button">+</button>
                                    </div>
                                </div>
                                <div class="col-5 col-md-2 text-end">
                                    <span class="fw-bold carrinho-item-preco-modelo">G$ 0</span>
                                </div>
                                <div class="col-2 col-md-2 text-end">
                                    <button class="btn btn-sm btn-outline-danger btn-remover-item-modelo"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                         <!-- Mensagem de carrinho vazio (para JS, escondido por padrão) -->
                        <div class="carrinho-vazio-modelo d-none text-center p-4 p-md-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="mb-3">Tu carrito está vacío</h5>
                            <p class="text-muted mb-4">Aún no has agregado productos a tu carrito. ¡Explora nuestros productos y encuentra algo que te guste!</p>
                            <a href="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php" class="btn btn-primary">Explorar Productos</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Continuar comprando -->
            <div class="d-flex justify-content-start mt-3">
                <a href="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Seguir Comprando
                </a>
            </div>
        </div>
        
        <!-- Resumo do pedido -->
        <div class="col-lg-4 carrinho-resumo-container">
            <div class="card shadow-sm sticky-top" style="top: 20px;"> <!-- Ajustado o top para melhor visualização com sticky navbar -->
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0">Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="carrinho-resumo mb-3">
                        <!-- Subtotal -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="carrinho-resumo-subtotal">G$ 0</span>
                        </div>
                        
                        <!-- Frete -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Flete:</span>
                            <span class="carrinho-resumo-frete">G$ 0</span>
                        </div>
                        
                        <!-- Total -->
                        <div class="d-flex justify-content-between fw-bold fs-5 pt-2 border-top">
                            <span>Total:</span>
                            <span class="carrinho-resumo-total-valor">G$ 0</span>
                        </div>
                    </div>
                    
                    <!-- Informação de frete -->
                    <div class="small text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i> El flete se calcula a G$ 1.500 por kg (ejemplo).
                    </div>
                    
                    <!-- Botão de finalizar compra -->
                    <div class="d-grid">
                        <a href="<?php echo htmlspecialchars(SITE_URL); ?>/checkout.php" class="btn btn-success btn-lg btn-finalizar-compra-carrinho disabled">
                            Finalizar Compra
                        </a>
                    </div>
                    
                    <!-- Pagamento e segurança -->
                    <div class="text-center small mt-3">
                        <p class="mb-2 text-muted">Métodos de pago disponibles:</p>
                        <div class="payment-methods mb-2">
                            <i class="fas fa-money-bill-wave fa-lg mx-1 text-success" title="Efectivo"></i>
                            <i class="fas fa-exchange-alt fa-lg mx-1 text-primary" title="Transferencia"></i>
                            <i class="fab fa-cc-visa fa-lg mx-1 text-info" title="Visa"></i>
                            <i class="fab fa-cc-mastercard fa-lg mx-1 text-warning" title="Mastercard"></i>
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-lock me-1"></i> Compra 100% segura
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Banner promocional -->
    <div class="bg-light p-4 rounded shadow-sm text-center mt-5 animate-on-scroll">
        <h4>¿Tiene dudas sobre algún producto?</h4>
        <p class="mb-3">Contáctenos directamente y lo ayudaremos con su pedido.</p>
        <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', WHATSAPP)); ?>?text=<?php echo rawurlencode("Hola, tengo una consulta sobre mi carrito de compras."); ?>" target="_blank" class="btn btn-success">
            <i class="fab fa-whatsapp me-2"></i> Hablar con un vendedor
        </a>
    </div>
    
</div>

<?php
// Incluir o rodapé
include "includes/footer.php";
?>
