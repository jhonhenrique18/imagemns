<?php
// Configurações da página
$titulo_pagina = 'Finalizar Compra';

// Incluir arquivo de conexão
require_once 'includes/conexao.php';

// Incluir o cabeçalho
include 'includes/header.php';
?>

<!-- Classe da página para JavaScript -->
<div class="checkout-page">
    
    <!-- Título da página -->
    <div class="mb-4">
        <h1>Finalizar Compra</h1>
    </div>
    
    <!-- Conteúdo do checkout -->
    <div class="row g-4">
        <!-- Formulário de checkout -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">1. Datos de Entrega y Contacto</h5>
                </div>
                <div class="card-body">
                    <form id="checkoutForm" class="needs-validation" novalidate>
                        <!-- Dados pessoais -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                                <div class="invalid-feedback">
                                    Por favor, ingrese su nombre completo.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <div class="invalid-feedback">
                                    Por favor, ingrese un correo electrónico válido.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Ejemplo: 0991 234567" required>
                                <div class="invalid-feedback">
                                    Por favor, ingrese su número de teléfono.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Endereço de entrega -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="endereco" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Calle, número, barrio" required>
                                <div class="invalid-feedback">
                                    Por favor, ingrese su dirección.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="cidade" class="form-label">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cidade" name="cidade" required>
                                <div class="invalid-feedback">
                                    Por favor, ingrese su ciudad.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Punto de referencia cercano">
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="mb-4">
                            <label for="observacoes" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Información adicional para la entrega"></textarea>
                        </div>
                        
                        <!-- Método de pagamento -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">2. Forma de Pago</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="formaPagamento" id="pagamentoTransferencia" value="transferencia" checked required>
                                    <label class="form-check-label" for="pagamentoTransferencia">
                                        <i class="fas fa-exchange-alt me-2"></i> Transferencia bancaria
                                    </label>
                                    <div class="form-text mt-1">
                                        Se le proporcionarán los datos bancarios por WhatsApp después de finalizar el pedido.
                                    </div>
                                </div>
                                <!-- Outras formas de pagamento removidas conforme solicitado -->
                            </div>
                        </div>
                        
                        <!-- Termos e condições -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="aceitarTermos" required>
                            <label class="form-check-label" for="aceitarTermos">
                                He leído y acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#modalTermos">términos y condiciones</a> <span class="text-danger">*</span>
                            </label>
                            <div class="invalid-feedback">
                                Debe aceptar los términos y condiciones.
                            </div>
                        </div>

                        <!-- Botão de finalizar compra movido para dentro do form -->
                        <button type="submit" id="btnFinalizarPedido" class="btn btn-success w-100 btn-lg">
                            <i class="fab fa-whatsapp me-1"></i> Finalizar Pedido por WhatsApp
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Botões de navegação -->
            <div class="d-flex mt-3">
                <a href="carrinho.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Carrito
                </a>
            </div>
        </div>
        
        <!-- Resumo do pedido -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;"> <!-- Ajustado o top -->
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="carrinho-resumo mb-3">
                        <!-- Itens do resumo (serão preenchidos por JS) -->
                        <div class="carrinho-resumo-itens-lista mb-2">
                            <!-- Exemplo de item (remover ou substituir por JS) -->
                            <!-- 
                            <div class="d-flex justify-content-between small">
                                <span>Produto Exemplo x 2</span>
                                <span>G$ 20.000</span>
                            </div>
                            -->
                        </div>
                        <hr>
                        <!-- Subtotal -->
                        <div class="carrinho-resumo-linha">
                            <span>Subtotal:</span>
                            <span class="carrinho-resumo-subtotal">G$ 0</span>
                        </div>
                        
                        <!-- Frete -->
                        <div class="carrinho-resumo-linha">
                            <span>Flete:</span>
                            <span class="carrinho-resumo-frete">G$ 0</span>
                        </div>
                        
                        <!-- Total -->
                        <div class="carrinho-resumo-linha carrinho-resumo-total">
                            <span>Total:</span>
                            <span class="carrinho-resumo-total-valor fw-bold">G$ 0</span>
                        </div>
                    </div>
                    
                    <!-- Informação de frete -->
                    <div class="small text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i> El flete se calcula a <?php echo htmlspecialchars(formatarPreco(VALOR_FRETE_POR_KG_CONFIG)); ?> por kg.
                        Mínimo: <?php echo htmlspecialchars(formatarPreco(VALOR_FRETE_MINIMO_CONFIG)); ?>.
                        Gratis para compras superiores a <?php echo htmlspecialchars(formatarPreco(VALOR_COMPRA_FLETE_GRATIS_CONFIG)); ?> (Asunción y Gran Asunción).
                    </div>
                                        
                    <!-- Pagamento e segurança -->
                    <div class="text-center small">
                        <p class="mb-1">Método de pago seleccionado:</p>
                        <p class="fw-bold"><i class="fas fa-exchange-alt me-1"></i> Transferencia Bancaria</p>
                        <div class="text-muted mt-2">
                            <i class="fas fa-lock me-1"></i> Compra 100% segura
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal de Termos e Condições -->
<div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTermosLabel">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Pedidos y Entregas</h5>
                <p>Los pedidos se realizarán a través de WhatsApp y serán confirmados por nuestro equipo. Las entregas se realizan en un plazo de 24 a 48 horas hábiles, dependiendo de la ubicación y disponibilidad de stock.</p>
                
                <h5>2. Pagos</h5>
                <p>El método de pago aceptado es Transferencia Bancaria. Los datos para la transferencia serán proporcionados por WhatsApp una vez finalizado el pedido en esta página. El pedido será procesado y enviado una vez confirmada la recepción de la transferencia.</p>
                
                <h5>3. Flete</h5>
                <p>El costo del flete se calcula a <?php echo htmlspecialchars(formatarPreco(VALOR_FRETE_POR_KG_CONFIG)); ?> por kilogramo, con un mínimo de <?php echo htmlspecialchars(formatarPreco(VALOR_FRETE_MINIMO_CONFIG)); ?>. Para compras superiores a <?php echo htmlspecialchars(formatarPreco(VALOR_COMPRA_FLETE_GRATIS_CONFIG)); ?>, el flete es gratuito dentro de Asunción y Gran Asunción.</p>
                
                <h5>4. Devoluciones</h5>
                <p>Aceptamos devoluciones de productos en un plazo de 7 días desde la recepción, siempre que se encuentren en su empaque original y sin haber sido utilizados. Los costos de envío por devolución corren por cuenta del cliente, a menos que el producto presente defectos de fábrica.</p>
                
                <h5>5. Privacidad</h5>
                <p>Los datos proporcionados serán utilizados únicamente para procesar su pedido y no serán compartidos con terceros sin su consentimiento expreso, conforme a nuestra política de privacidad.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const carrinho = obterCarrinho(); // Função de carrinho.js

    if (!carrinho || carrinho.itens.length === 0) {
        alert("Su carrito está vacío. Será redirigido a la página de productos.");
        window.location.href = "produtos.php";
        return;
    }

    // Função para popular o resumo do pedido
    function popularResumoPedido() {
        const resumoItensLista = document.querySelector(".carrinho-resumo-itens-lista");
        const resumoSubtotalEl = document.querySelector(".carrinho-resumo-subtotal");
        const resumoFreteEl = document.querySelector(".carrinho-resumo-frete");
        const resumoTotalEl = document.querySelector(".carrinho-resumo-total-valor");

        if (!resumoItensLista || !resumoSubtotalEl || !resumoFreteEl || !resumoTotalEl) return;

        let itensHtml = "";
        carrinho.itens.forEach(item => {
            const precoItem = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
            itensHtml += `
                <div class="d-flex justify-content-between small mb-1">
                    <span>${htmlspecialchars(item.nome)} x ${item.quantidade}</span>
                    <span>${formatarPreco(precoItem * item.quantidade)}</span>
                </div>`;
        });
        resumoItensLista.innerHTML = itensHtml;

        resumoSubtotalEl.textContent = formatarPreco(carrinho.valorProdutos);
        resumoFreteEl.textContent = formatarPreco(carrinho.valorFrete);
        resumoTotalEl.textContent = formatarPreco(carrinho.valorTotal);
    }

    popularResumoPedido();

    const form = document.getElementById("checkoutForm");
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        form.classList.add("was-validated"); // Para mostrar feedback de sucesso se tudo estiver ok

        // Coletar dados do formulário
        const nome = document.getElementById("nome").value;
        const email = document.getElementById("email").value;
        const telefone = document.getElementById("telefone").value;
        const endereco = document.getElementById("endereco").value;
        const cidade = document.getElementById("cidade").value;
        const referencia = document.getElementById("referencia").value;
        const observacoes = document.getElementById("observacoes").value;
        const formaPagamento = "Transferencia Bancaria"; // Fixo conforme solicitado

        // Montar mensagem para WhatsApp
        let mensagem = `*Nuevo Pedido - ${SITE_NOME_CONFIG}*
-------------------------------------
`;
        mensagem += `*Cliente:* ${nome}
`;
        mensagem += `*Teléfono:* ${telefone}
`;
        if (email) mensagem += `*Email:* ${email}
`;
        mensagem += `*Dirección:* ${endereco}, ${cidade}
`;
        if (referencia) mensagem += `*Referencia:* ${referencia}
`;
        if (observacoes) mensagem += `*Observaciones:* ${observacoes}
`;
        mensagem += `*Forma de Pago:* ${formaPagamento}
`;
        mensagem += "-------------------------------------\n*Productos:*
";

        carrinho.itens.forEach(item => {
            const precoUnitario = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
            mensagem += `- ${item.nome} (x${item.quantidade}) - ${formatarPreco(precoUnitario * item.quantidade)}
`;
        });

        mensagem += "-------------------------------------\n";
        mensagem += `*Subtotal:* ${formatarPreco(carrinho.valorProdutos)}
`;
        mensagem += `*Flete:* ${formatarPreco(carrinho.valorFrete)}
`;
        mensagem += `*TOTAL DEL PEDIDO:* *${formatarPreco(carrinho.valorTotal)}*
`;
        mensagem += "-------------------------------------\n";
        mensagem += "Aguardamos su contacto para confirmar los datos de la transferencia.";

        const whatsappNumber = "<?php echo defined("WHATSAPP_NUMERO_CONFIG") ? htmlspecialchars(WHATSAPP_NUMERO_CONFIG) : ""; ?>";
        if (whatsappNumber) {
            const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(mensagem)}`;
            
            // Ação futura: Enviar dados para o admin/pedidos.php via AJAX antes de redirecionar
            // Por agora, apenas redireciona para o WhatsApp e limpa o carrinho

            // Limpar carrinho após enviar
            limparCarrinhoCompleto(); // Assume que esta função existe em carrinho.js e limpa localStorage
            
            alert("Será redirigido a WhatsApp para finalizar su pedido. Luego de enviar el mensaje, aguarde nuestras instrucciones para la transferencia.");
            window.open(whatsappUrl, "_blank");
            
            // Redirecionar para uma página de agradecimento ou para a home
            // window.location.href = "obrigado.php"; 
        } else {
            alert("Error: Número de WhatsApp no configurado.");
        }
    });

    // Função auxiliar para escapar HTML (usar com cuidado, idealmente feito no servidor)
    function htmlspecialchars(str) {
        if (typeof str !== "string") return "";
        return str.replace(/[&<>'"/]/g, function (s) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                "'": "&#39;",
                "\"": "&quot;",
                "/": "&#x2F;"
            }[s];
        });
    }
    
    // Função para limpar o carrinho (precisa estar em carrinho.js ou global)
    function limparCarrinhoCompleto() {
        if (typeof limparCarrinho === 'function') { // Verifica se a função de carrinho.js está disponível
            limparCarrinho(); 
        } else {
            localStorage.removeItem('carrinho');
            console.log("Carrinho limpo diretamente do checkout.js");
        }
        atualizarContadorCarrinho(); // Atualiza o contador no header
    }
});

</script>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>
