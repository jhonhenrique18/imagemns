/**
 * Carrinho de compras para Grãos S.A.
 * Otimizado para uso em dispositivos móveis
 */

// Estrutura do carrinho
// {
//     itens: [
//         {
//             id: Number,
//             nome: String,
//             preco: Number,
//             preco_atacado: Number,
//             quantidade_atacado: Number,
//             quantidade: Number,
//             peso: Number,
//             imagem: String
//         }
//     ],
//     totalItens: Number,
//     valorProdutos: Number,
//     valorFrete: Number,
//     valorTotal: Number
// }

// Constantes Globais (devem ser definidas no config.php ou similar e passadas para o JS)
const VALOR_FRETE_POR_KG = typeof VALOR_FRETE_POR_KG_CONFIG !== 'undefined' ? VALOR_FRETE_POR_KG_CONFIG : 1500;
const VALOR_FRETE_MINIMO = typeof VALOR_FRETE_MINIMO_CONFIG !== 'undefined' ? VALOR_FRETE_MINIMO_CONFIG : 0;
const VALOR_COMPRA_FLETE_GRATIS = typeof VALOR_COMPRA_FLETE_GRATIS_CONFIG !== 'undefined' ? VALOR_COMPRA_FLETE_GRATIS_CONFIG : Infinity;
const SITE_NOME = typeof SITE_NOME_CONFIG !== 'undefined' ? SITE_NOME_CONFIG : 'Grãos S.A.';
const WHATSAPP_NUMERO = typeof WHATSAPP_NUMERO_CONFIG !== 'undefined' ? WHATSAPP_NUMERO_CONFIG : ''; // Número do WhatsApp para pedidos

/**
 * Inicializa o carrinho se não existir
 */
function inicializarCarrinho() {
    if (!localStorage.getItem('carrinho')) {
        const carrinhoVazio = {
            itens: [],
            totalItens: 0,
            valorProdutos: 0,
            valorFrete: 0,
            valorTotal: 0
        };
        localStorage.setItem('carrinho', JSON.stringify(carrinhoVazio));
    }
}

/**
 * Obtém o carrinho atual
 * @returns {Object} Objeto do carrinho
 */
function obterCarrinho() {
    inicializarCarrinho();
    return JSON.parse(localStorage.getItem('carrinho'));
}

/**
 * Salva o carrinho no localStorage
 * @param {Object} carrinho - Objeto do carrinho a ser salvo
 */
function salvarCarrinho(carrinho) {
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    atualizarContadorCarrinho();
    if (document.querySelector('.carrinho-page')) {
        atualizarEstadoBotaoFinalizar();
    }
}

/**
 * Adiciona um produto ao carrinho
 * @param {Object} produto - Dados do produto a ser adicionado
 * @param {Number} quantidade_adicionar - Quantidade a ser adicionada (default 1)
 */
function adicionarAoCarrinho(produto, quantidade_adicionar = 1) {
    const carrinho = obterCarrinho();
    const itemIndex = carrinho.itens.findIndex(item => item.id === produto.id);
    
    if (itemIndex > -1) {
        carrinho.itens[itemIndex].quantidade += quantidade_adicionar;
    } else {
        carrinho.itens.push({
            id: produto.id,
            nome: produto.nome,
            preco: parseFloat(produto.preco),
            preco_atacado: parseFloat(produto.preco_atacado) || 0,
            quantidade_atacado: parseInt(produto.quantidade_atacado) || 10,
            quantidade: quantidade_adicionar,
            peso: parseFloat(produto.peso) || 0,
            imagem: produto.imagem
        });
    }
    
    atualizarTotaisCarrinho(carrinho);
    salvarCarrinho(carrinho);
    exibirNotificacao('Producto agregado al carrito', 'success');
}

/**
 * Remove um item do carrinho
 * @param {Number} produtoId - ID do produto a ser removido
 */
function removerDoCarrinho(produtoId) {
    const carrinho = obterCarrinho();
    carrinho.itens = carrinho.itens.filter(item => item.id !== produtoId);
    atualizarTotaisCarrinho(carrinho);
    salvarCarrinho(carrinho);
    if (document.querySelector('.carrinho-lista')) {
        renderizarCarrinho();
    }
    exibirNotificacao('Producto eliminado del carrito');
}

/**
 * Atualiza a quantidade de um item no carrinho
 * @param {Number} produtoId - ID do produto
 * @param {Number} quantidade - Nova quantidade
 */
function atualizarQuantidade(produtoId, quantidade) {
    const carrinho = obterCarrinho();
    const itemIndex = carrinho.itens.findIndex(item => item.id === produtoId);
    
    if (itemIndex > -1) {
        quantidade = Math.max(1, quantidade);
        carrinho.itens[itemIndex].quantidade = quantidade;
        atualizarTotaisCarrinho(carrinho);
        salvarCarrinho(carrinho);
        if (document.querySelector('.carrinho-lista')) {
            atualizarSubtotalItem(produtoId);
            atualizarResumoCarrinho();
        }
    }
}

/**
 * Limpa completamente o carrinho
 */
function limparCarrinho() {
    const carrinhoVazio = {
        itens: [],
        totalItens: 0,
        valorProdutos: 0,
        valorFrete: 0,
        valorTotal: 0
    };
    salvarCarrinho(carrinhoVazio);
    if (document.querySelector('.carrinho-lista')) {
        renderizarCarrinho();
    }
    exibirNotificacao('Carrito vaciado');
}

/**
 * Calcula e atualiza os totais do carrinho
 * @param {Object} carrinho - Objeto do carrinho
 */
function atualizarTotaisCarrinho(carrinho) {
    let totalItens = 0;
    let valorProdutos = 0;
    let pesoTotal = 0;
    
    carrinho.itens.forEach(item => {
        totalItens += item.quantidade;
        const precoUnitario = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 
            ? item.preco_atacado 
            : item.preco;
        valorProdutos += precoUnitario * item.quantidade;
        pesoTotal += (item.peso || 0) * item.quantidade;
    });
    
    let valorFreteCalculado = pesoTotal * VALOR_FRETE_POR_KG;
    if (valorProdutos >= VALOR_COMPRA_FLETE_GRATIS) {
        valorFreteCalculado = 0;
    } else if (valorFreteCalculado < VALOR_FRETE_MINIMO && valorProdutos > 0) {
        valorFreteCalculado = VALOR_FRETE_MINIMO;
    } else if (valorProdutos === 0) {
        valorFreteCalculado = 0;
    }
    
    carrinho.totalItens = totalItens;
    carrinho.valorProdutos = valorProdutos;
    carrinho.valorFrete = valorFreteCalculado;
    carrinho.valorTotal = valorProdutos + valorFreteCalculado;
}

/**
 * Atualiza o contador do carrinho no menu
 */
function atualizarContadorCarrinho() {
    const carrinho = obterCarrinho();
    const contadores = document.querySelectorAll('.carrinho-contador');
    contadores.forEach(contador => {
        contador.textContent = carrinho.totalItens;
        contador.classList.toggle('d-none', carrinho.totalItens === 0);
    });
}

/**
 * Renderiza o carrinho na página
 */
function renderizarCarrinho() {
    const carrinho = obterCarrinho();
    const carrinhoLista = document.querySelector('.carrinho-lista');
    if (!carrinhoLista) return;

    const resumoContainer = document.querySelector('.carrinho-resumo-container');
    const modeloItemHTML = document.querySelector('.carrinho-item-modelo')?.innerHTML;
    const modeloVazioHTML = document.querySelector('.carrinho-vazio-modelo')?.innerHTML;

    if (carrinho.itens.length === 0) {
        if (modeloVazioHTML) {
            carrinhoLista.innerHTML = modeloVazioHTML;
        } else {
             carrinhoLista.innerHTML = `<div class="text-center py-5"><i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i><h4>Su carrito está vacío</h4><p>Añada productos para comenzar su compra</p><a href="produtos.php" class="btn btn-success mt-3">Ver Productos</a></div>`;
        }
        if (resumoContainer) resumoContainer.classList.add('d-none');
    } else {
        if (!modeloItemHTML) {
            console.error("Modelo de item do carrinho não encontrado!");
            carrinhoLista.innerHTML = '<p class="text-danger">Erro ao carregar itens do carrinho: modelo não encontrado.</p>';
            return;
        }
        let htmlItens = '';
        carrinho.itens.forEach(item => {
            const precoUnitario = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
            const subtotal = precoUnitario * item.quantidade;
            
            let itemHtmlAtual = modeloItemHTML;
            // Adiciona data-id ao elemento principal do modelo para fácil seleção posterior
            itemHtmlAtual = itemHtmlAtual.replace('<div class="row g-3 align-items-center">', `<div class="row g-3 align-items-center" data-id="${item.id}">`);

            itemHtmlAtual = itemHtmlAtual.replace('src="#"', `src="assets/img/${item.imagem || 'placeholder.png'}"`)
                                     .replace('alt="Imagem do Produto"', `alt="${htmlspecialchars(item.nome)}"`);
            itemHtmlAtual = itemHtmlAtual.replace('Nome do Produto', htmlspecialchars(item.nome))
                                     .replace('ID: #123', `ID: ${item.id}`);
            itemHtmlAtual = itemHtmlAtual.replace('value="1"', `value="${item.quantidade}"`);
            itemHtmlAtual = itemHtmlAtual.replace('G$ 0', formatarPreco(subtotal)); // Preço total do item
            
            // Adicionar data-id aos botões e input para fácil manipulação
            itemHtmlAtual = itemHtmlAtual.replace('btn-qtd-diminuir-modelo"', `btn-qtd-diminuir-modelo" data-id="${item.id}"`);
            itemHtmlAtual = itemHtmlAtual.replace('input-qtd-modelo"', `input-qtd-modelo" data-id="${item.id}"`);
            itemHtmlAtual = itemHtmlAtual.replace('btn-qtd-aumentar-modelo"', `btn-qtd-aumentar-modelo" data-id="${item.id}"`);
            itemHtmlAtual = itemHtmlAtual.replace('btn-remover-item-modelo"', `btn-remover-item-modelo" data-id="${item.id}"`);

            htmlItens += `<div class="carrinho-item-renderizado p-3 border-bottom">${itemHtmlAtual}</div>`;
        });
        carrinhoLista.innerHTML = htmlItens;
        if (resumoContainer) resumoContainer.classList.remove('d-none');
        adicionarEventListenersCarrinhoItens();
    }
    atualizarResumoCarrinho();
    atualizarEstadoBotaoFinalizar();
}

/**
 * Adiciona os event listeners para os botões dos itens renderizados no carrinho
 */
function adicionarEventListenersCarrinhoItens() {
    document.querySelectorAll('.carrinho-item-renderizado .btn-qtd-aumentar-modelo').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            const item = obterCarrinho().itens.find(i => i.id === produtoId);
            if (item) atualizarQuantidade(produtoId, item.quantidade + 1);
        });
    });
    document.querySelectorAll('.carrinho-item-renderizado .btn-qtd-diminuir-modelo').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            const item = obterCarrinho().itens.find(i => i.id === produtoId);
            if (item && item.quantidade > 1) atualizarQuantidade(produtoId, item.quantidade - 1);
        });
    });
    document.querySelectorAll('.carrinho-item-renderizado .input-qtd-modelo').forEach(input => {
        input.addEventListener('change', function() {
            const produtoId = parseInt(this.dataset.id);
            let novaQuantidade = parseInt(this.value);
            if (isNaN(novaQuantidade) || novaQuantidade < 1) {
                novaQuantidade = 1;
                this.value = novaQuantidade; 
            }
            atualizarQuantidade(produtoId, novaQuantidade);
        });
    });
    document.querySelectorAll('.carrinho-item-renderizado .btn-remover-item-modelo').forEach(btn => {
        btn.addEventListener('click', function() {
            const produtoId = parseInt(this.dataset.id);
            if (confirm('¿Está seguro que desea eliminar este producto del carrito?')) {
                removerDoCarrinho(produtoId);
            }
        });
    });
}

/**
 * Atualiza o subtotal de um item específico na UI (chamado por atualizarQuantidade)
 */
function atualizarSubtotalItem(produtoId) {
    const carrinho = obterCarrinho();
    const item = carrinho.itens.find(i => i.id === produtoId);
    // Seleciona o item renderizado pelo data-id no seu container principal
    const itemNode = document.querySelector(`.carrinho-item-renderizado .row[data-id='${produtoId}']`);

    if (item && itemNode) {
        const precoUnitario = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
        const subtotal = precoUnitario * item.quantidade;
        
        const subtotalEl = itemNode.querySelector('.carrinho-item-preco-modelo'); 
        if (subtotalEl) subtotalEl.textContent = formatarPreco(subtotal);
        
        const inputQtd = itemNode.querySelector('.input-qtd-modelo');
        if (inputQtd) inputQtd.value = item.quantidade;
    }
}

/**
 * Atualiza o resumo do carrinho na UI
 */
function atualizarResumoCarrinho() {
    const carrinho = obterCarrinho();
    const resumoSubtotalEl = document.querySelector('.carrinho-resumo-subtotal');
    const resumoFreteEl = document.querySelector('.carrinho-resumo-frete');
    const resumoTotalEl = document.querySelector('.carrinho-resumo-total-valor');
    
    if (resumoSubtotalEl) resumoSubtotalEl.textContent = formatarPreco(carrinho.valorProdutos);
    if (resumoFreteEl) resumoFreteEl.textContent = formatarPreco(carrinho.valorFrete);
    if (resumoTotalEl) resumoTotalEl.textContent = formatarPreco(carrinho.valorTotal);

    atualizarEstadoBotaoFinalizar();
}

/**
 * Atualiza o estado (ativado/desativado) do botão de finalizar compra
 */
function atualizarEstadoBotaoFinalizar() {
    const carrinho = obterCarrinho();
    const btnFinalizar = document.querySelector('.btn-finalizar-compra-carrinho');
    if (btnFinalizar) {
        if (carrinho.itens.length > 0) {
            btnFinalizar.classList.remove('disabled');
            btnFinalizar.removeAttribute('aria-disabled');
        } else {
            btnFinalizar.classList.add('disabled');
            btnFinalizar.setAttribute('aria-disabled', 'true');
        }
    }
}

/**
 * Formata um valor para a moeda local (Guarani)
 * @param {Number} valor - Valor a ser formatado
 * @returns {String} Valor formatado
 */
function formatarPreco(valor) {
    if (typeof valor !== 'number') valor = 0;
    return 'G$ ' + valor.toLocaleString('es-PY', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

/**
 * Escapa caracteres HTML para prevenir XSS
 * @param {String} text - Texto a ser escapado
 * @returns {String} Texto escapado
 */
function htmlspecialchars(text) {
    if (typeof text !== 'string') return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Exibe uma notificação toast
 */
function exibirNotificacao(mensagem, tipo = 'info') { 
    const toastContainer = document.querySelector('.toast-container.position-fixed.top-0.end-0.p-3') || (() => {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1090'; 
        document.body.appendChild(container);
        return container;
    })();

    const toastId = 'toast-' + Date.now();
    const toastEl = document.createElement('div');
    toastEl.id = toastId;
    let bgClass = 'bg-primary'; // default info
    if (tipo === 'success') bgClass = 'bg-success';
    if (tipo === 'error') bgClass = 'bg-danger';

    toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${htmlspecialchars(mensagem)}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);

    try {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    } catch (e) {
        console.error("Bootstrap Toast não pôde ser inicializado. Verifique se o Bootstrap JS está carregado.", e);
        // Fallback simples se o Bootstrap não estiver disponível
        setTimeout(() => {
            toastEl.remove();
        }, 3000);
    }
}

// Inicializa o carrinho e os listeners quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    inicializarCarrinho();
    atualizarContadorCarrinho();
    
    if (document.querySelector('.carrinho-page')) { 
        renderizarCarrinho(); 
        
        // Listener para o botão de limpar carrinho GERAL (fora dos itens)
        const btnLimparCarrinhoGeral = document.querySelector('.btn-limpar-carrinho');
        if (btnLimparCarrinhoGeral) {
            btnLimparCarrinhoGeral.addEventListener('click', () => {
                if (confirm('¿Está seguro que desea vaciar el carrito?')) {
                    limparCarrinho();
                }
            });
        }

        // Listener para o botão "Finalizar Compra" na página do carrinho
        const btnFinalizarCarrinhoPage = document.querySelector('.carrinho-page .btn-finalizar-compra-carrinho');
        if (btnFinalizarCarrinhoPage) {
            btnFinalizarCarrinhoPage.addEventListener('click', function(event) {
                if (this.classList.contains('disabled')) {
                    event.preventDefault(); 
                    exibirNotificacao('Su carrito está vacío. Añada productos para continuar.', 'error');
                }
                // Se não estiver desabilitado, o comportamento padrão do link (href) será seguido.
            });
        }
    }

    const formCheckout = document.getElementById('checkoutForm');
    if (formCheckout) {
        // Popular resumo do pedido na página de checkout
        if (typeof popularResumoPedidoCheckout === 'function') {
             popularResumoPedidoCheckout();
        } else {
            // Código para popular resumo no checkout se a função não existir separadamente
            const carrinhoCheckout = obterCarrinho();
            if (!carrinhoCheckout || carrinhoCheckout.itens.length === 0) {
                exibirNotificacao("Su carrito está vacío. Será redirigido a la página de productos.", "error");
                setTimeout(() => { window.location.href = "produtos.php"; }, 2000);
                return;
            }
            const resumoItensLista = formCheckout.closest('.checkout-page').querySelector(".carrinho-resumo-itens-lista");
            const resumoSubtotalEl = formCheckout.closest('.checkout-page').querySelector(".carrinho-resumo-subtotal");
            const resumoFreteEl = formCheckout.closest('.checkout-page').querySelector(".carrinho-resumo-frete");
            const resumoTotalEl = formCheckout.closest('.checkout-page').querySelector(".carrinho-resumo-total-valor");

            if (resumoItensLista && resumoSubtotalEl && resumoFreteEl && resumoTotalEl) {
                let itensHtml = "";
                carrinhoCheckout.itens.forEach(item => {
                    const precoItem = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
                    itensHtml += `<div class="d-flex justify-content-between small mb-1"><span>${htmlspecialchars(item.nome)} x ${item.quantidade}</span><span>${formatarPreco(precoItem * item.quantidade)}</span></div>`;
                });
                resumoItensLista.innerHTML = itensHtml;
                resumoSubtotalEl.textContent = formatarPreco(carrinhoCheckout.valorProdutos);
                resumoFreteEl.textContent = formatarPreco(carrinhoCheckout.valorFrete);
                resumoTotalEl.textContent = formatarPreco(carrinhoCheckout.valorTotal);
            }
        }

        formCheckout.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!formCheckout.checkValidity()) {
                formCheckout.classList.add('was-validated');
                return;
            }
            formCheckout.classList.add('was-validated');

            const dadosCliente = {
                nome: document.getElementById('nome').value,
                email: document.getElementById('email').value,
                telefone: document.getElementById('telefone').value,
                endereco: document.getElementById('endereco').value,
                cidade: document.getElementById('cidade').value,
                referencia: document.getElementById('referencia').value,
                observacoes: document.getElementById('observacoes').value
            };

            const carrinho = obterCarrinho();
            if (carrinho.itens.length === 0) {
                exibirNotificacao('Su carrito está vacío.', 'error');
                return;
            }

            let mensagem = `*Nuevo Pedido - ${SITE_NOME}*\n-------------------------------------\n`;
            mensagem += `*Cliente:* ${htmlspecialchars(dadosCliente.nome)}\n`;
            mensagem += `*Teléfono:* ${htmlspecialchars(dadosCliente.telefone)}\n`;
            if (dadosCliente.email) mensagem += `*Email:* ${htmlspecialchars(dadosCliente.email)}\n`;
            mensagem += `*Dirección:* ${htmlspecialchars(dadosCliente.endereco)}, ${htmlspecialchars(dadosCliente.cidade)}\n`;
            if (dadosCliente.referencia) mensagem += `*Referencia:* ${htmlspecialchars(dadosCliente.referencia)}\n`;
            if (dadosCliente.observacoes) mensagem += `*Observaciones:* ${htmlspecialchars(dadosCliente.observacoes)}\n`;
            mensagem += `*Forma de Pago:* Transferencia Bancaria\n`;
            mensagem += "-------------------------------------\n*Productos:*\n";

            carrinho.itens.forEach(item => {
                const precoUnitario = item.quantidade >= item.quantidade_atacado && item.preco_atacado > 0 ? item.preco_atacado : item.preco;
                mensagem += `${item.quantidade}x ${htmlspecialchars(item.nome)} - ${formatarPreco(precoUnitario)} = ${formatarPreco(precoUnitario * item.quantidade)}\n`;
            });

            mensagem += `\n*Subtotal:* ${formatarPreco(carrinho.valorProdutos)}\n`;
            mensagem += `*Flete:* ${formatarPreco(carrinho.valorFrete)}\n`;
            mensagem += `*TOTAL DEL PEDIDO:* ${formatarPreco(carrinho.valorTotal)}\n`;
            mensagem += "-------------------------------------\n";
            mensagem += `*Fecha:* ${new Date().toLocaleString('es-PY')}\n`;
            
            const whatsappUrl = `https://wa.me/${WHATSAPP_NUMERO}?text=${encodeURIComponent(mensagem)}`;
            
            exibirNotificacao('Pedido enviado por WhatsApp! Redirigiendo...', 'success');
            
            setTimeout(() => {
                 limparCarrinho(); 
                 window.open(whatsappUrl, '_blank');
                 // Opcional: redirecionar para uma página de "pedido enviado com sucesso"
                 // window.location.href = 'pedido-confirmado.php'; 
            }, 1000);
        });
    }
});

