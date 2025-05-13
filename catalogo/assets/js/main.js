/**
 * Script principal para Grãos S.A.
 * Otimizado para uso em dispositivos móveis
 */

// Configuração global do site
const SITE = {
    nome: 'Grãos S.A.',
    url: window.location.origin,
    whatsapp: '595990000000' // Número do WhatsApp da loja
};

// Verificar se o dispositivo é móvel
const isMobile = window.innerWidth <= 768;

/**
 * Função executada quando o DOM estiver pronto
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os componentes
    inicializarComponentes();
    
    // Adiciona eventos específicos para cada página
    setupPaginaAtual();
    
    // Configurações para dispositivos móveis
    if (isMobile) {
        setupMobile();
    }
    
    // Animações e melhorias visuais
    aplicarEfeitosVisuais();
});

/**
 * Inicializa componentes comuns do site
 */
function inicializarComponentes() {
    // Tooltips do Bootstrap
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Inicializa os carousels
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carouselEl => {
        new bootstrap.Carousel(carouselEl, {
            interval: 5000,
            touch: true
        });
    });
    
    // Adiciona evento para o botão de subir ao topo
    const btnTopo = document.querySelector('.btn-topo');
    if (btnTopo) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                btnTopo.classList.add('show');
            } else {
                btnTopo.classList.remove('show');
            }
        });
        
        btnTopo.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Carregamento progressivo de imagens
    const imagens = document.querySelectorAll('.img-fluid:not(.loaded)');
    imagens.forEach(img => {
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
        
        // Se a imagem já estiver carregada
        if (img.complete) {
            img.classList.add('loaded');
        }
    });
}

/**
 * Configura eventos específicos para a página atual
 */
function setupPaginaAtual() {
    // Página inicial
    if (document.querySelector('.home-page')) {
        setupPaginaInicial();
    }
    
    // Página de listagem de produtos
    if (document.querySelector('.produtos-page')) {
        setupPaginaProdutos();
    }
    
    // Página de detalhes do produto
    if (document.querySelector('.produto-detalhe-page')) {
        setupPaginaProdutoDetalhe();
    }
    
    // Página de carrinho
    if (document.querySelector('.carrinho-page')) {
        setupPaginaCarrinho();
    }
    
    // Página de checkout
    if (document.querySelector('.checkout-page')) {
        setupPaginaCheckout();
    }
}

/**
 * Configura a página inicial
 */
function setupPaginaInicial() {
    // Banners principais
    const bannerPrincipal = document.querySelector('.banner-principal');
    if (bannerPrincipal) {
        // Pode-se adicionar eventos específicos para o banner
    }
    
    // Produtos em destaque
    const produtosDestaque = document.querySelectorAll('.produto-destaque');
    produtosDestaque.forEach(produto => {
        // Evento para o botão de adicionar ao carrinho
        const btnAdicionar = produto.querySelector('.btn-adicionar');
        if (btnAdicionar) {
            btnAdicionar.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Obtém os dados do produto pelo dataset
                const produtoId = parseInt(this.dataset.id);
                const produtoNome = this.dataset.nome;
                const produtoPreco = parseFloat(this.dataset.preco);
                const produtoPrecoAtacado = parseFloat(this.dataset.precoAtacado) || 0;
                const produtoQuantidadeAtacado = parseInt(this.dataset.quantidadeAtacado) || 10;
                const produtoPeso = parseFloat(this.dataset.peso);
                const produtoImagem = this.dataset.imagem;
                
                // Adiciona ao carrinho
                adicionarAoCarrinho({
                    id: produtoId,
                    nome: produtoNome,
                    preco: produtoPreco,
                    preco_atacado: produtoPrecoAtacado,
                    quantidade_atacado: produtoQuantidadeAtacado,
                    peso: produtoPeso,
                    imagem: produtoImagem
                });
            });
        }
    });
}

/**
 * Configura a página de listagem de produtos
 */
function setupPaginaProdutos() {
    // Filtros de categoria
    const filtrosCategorias = document.querySelectorAll('.filtro-categoria');
    filtrosCategorias.forEach(filtro => {
        filtro.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove a classe ativa de todos os filtros
            filtrosCategorias.forEach(f => f.classList.remove('active'));
            
            // Adiciona a classe ativa ao filtro clicado
            this.classList.add('active');
            
            // Obtém o ID da categoria
            const categoriaId = this.dataset.categoria;
            
            // Atualiza a URL com o parâmetro da categoria
            const urlAtual = new URL(window.location.href);
            
            if (categoriaId === 'todos') {
                urlAtual.searchParams.delete('categoria');
            } else {
                urlAtual.searchParams.set('categoria', categoriaId);
            }
            
            window.location.href = urlAtual.toString();
        });
    });
    
    // Ordenação de produtos
    const selectOrdenacao = document.querySelector('#ordenacao');
    if (selectOrdenacao) {
        selectOrdenacao.addEventListener('change', function() {
            const urlAtual = new URL(window.location.href);
            urlAtual.searchParams.set('ordem', this.value);
            window.location.href = urlAtual.toString();
        });
    }
    
    // Botões de adicionar ao carrinho
    const botoesAdicionar = document.querySelectorAll('.btn-adicionar');
    botoesAdicionar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Obtém os dados do produto pelo dataset
            const produtoId = parseInt(this.dataset.id);
            const produtoNome = this.dataset.nome;
            const produtoPreco = parseFloat(this.dataset.preco);
            const produtoPrecoAtacado = parseFloat(this.dataset.precoAtacado) || 0;
            const produtoQuantidadeAtacado = parseInt(this.dataset.quantidadeAtacado) || 10;
            const produtoPeso = parseFloat(this.dataset.peso);
            const produtoImagem = this.dataset.imagem;
            
            // Adiciona ao carrinho
            adicionarAoCarrinho({
                id: produtoId,
                nome: produtoNome,
                preco: produtoPreco,
                preco_atacado: produtoPrecoAtacado,
                quantidade_atacado: produtoQuantidadeAtacado,
                peso: produtoPeso,
                imagem: produtoImagem
            });
        });
    });
}

/**
 * Configura a página de detalhes do produto
 */
function setupPaginaProdutoDetalhe() {
    // Galeria de imagens
    const imagemPrincipal = document.querySelector('.produto-imagem-principal');
    const miniaturas = document.querySelectorAll('.galeria-miniatura');
    
    if (imagemPrincipal && miniaturas.length > 0) {
        miniaturas.forEach(miniatura => {
            miniatura.addEventListener('click', function() {
                // Remove a classe ativa de todas as miniaturas
                miniaturas.forEach(m => m.classList.remove('active'));
                
                // Adiciona a classe ativa à miniatura clicada
                this.classList.add('active');
                
                // Atualiza a imagem principal
                imagemPrincipal.src = this.src;
                imagemPrincipal.alt = this.alt;
            });
        });
    }
    
    // Controles de quantidade
    const inputQtd = document.querySelector('.produto-qtd');
    const btnDiminuir = document.querySelector('.btn-diminuir');
    const btnAumentar = document.querySelector('.btn-aumentar');
    
    if (inputQtd && btnDiminuir && btnAumentar) {
        btnDiminuir.addEventListener('click', function() {
            let qtd = parseInt(inputQtd.value);
            if (qtd > 1) {
                inputQtd.value = qtd - 1;
                atualizarPrecoTotal();
            }
        });
        
        btnAumentar.addEventListener('click', function() {
            let qtd = parseInt(inputQtd.value);
            inputQtd.value = qtd + 1;
            atualizarPrecoTotal();
        });
        
        inputQtd.addEventListener('change', function() {
            let qtd = parseInt(this.value);
            if (isNaN(qtd) || qtd < 1) {
                this.value = 1;
            }
            atualizarPrecoTotal();
        });
    }
    
    // Atualiza o preço total baseado na quantidade
    function atualizarPrecoTotal() {
        const qtd = parseInt(inputQtd.value);
        const precoElement = document.querySelector('.produto-preco');
        const precoTotalElement = document.querySelector('.produto-preco-total');
        
        if (precoElement && precoTotalElement) {
            const precoUnitario = parseFloat(precoElement.dataset.preco);
            const precoAtacado = parseFloat(precoElement.dataset.precoAtacado) || 0;
            const qtdAtacado = parseInt(precoElement.dataset.qtdAtacado) || 10;
            
            // Verifica se é preço normal ou atacado
            let precoFinal = precoUnitario;
            if (qtd >= qtdAtacado && precoAtacado > 0) {
                precoFinal = precoAtacado;
                document.querySelector('.badge-atacado').classList.remove('d-none');
            } else {
                document.querySelector('.badge-atacado').classList.add('d-none');
            }
            
            const total = precoFinal * qtd;
            precoTotalElement.textContent = formatarPreco(total);
            
            // Atualiza o texto de economia se for preço de atacado
            const economiaElement = document.querySelector('.produto-economia');
            if (economiaElement && qtd >= qtdAtacado && precoAtacado > 0) {
                const economia = (precoUnitario - precoAtacado) * qtd;
                economiaElement.textContent = `¡Ahorra ${formatarPreco(economia)}!`;
                economiaElement.classList.remove('d-none');
            } else if (economiaElement) {
                economiaElement.classList.add('d-none');
            }
        }
    }
    
    // Botão de adicionar ao carrinho
    const btnAdicionarCarrinho = document.querySelector('.btn-adicionar-carrinho');
    if (btnAdicionarCarrinho) {
        btnAdicionarCarrinho.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Obtém a quantidade selecionada
            const qtd = parseInt(inputQtd.value);
            
            // Obtém os dados do produto pelo dataset
            const produtoId = parseInt(this.dataset.id);
            const produtoNome = this.dataset.nome;
            const produtoPreco = parseFloat(this.dataset.preco);
            const produtoPrecoAtacado = parseFloat(this.dataset.precoAtacado) || 0;
            const produtoQuantidadeAtacado = parseInt(this.dataset.quantidadeAtacado) || 10;
            const produtoPeso = parseFloat(this.dataset.peso);
            const produtoImagem = this.dataset.imagem;
            
            // Adiciona ao carrinho com a quantidade selecionada
            for (let i = 0; i < qtd; i++) {
                adicionarAoCarrinho({
                    id: produtoId,
                    nome: produtoNome,
                    preco: produtoPreco,
                    preco_atacado: produtoPrecoAtacado,
                    quantidade_atacado: produtoQuantidadeAtacado,
                    peso: produtoPeso,
                    imagem: produtoImagem
                });
            }
        });
    }
}

/**
 * Configura a página de carrinho
 */
function setupPaginaCarrinho() {
    // A funcionalidade do carrinho é gerenciada pelo arquivo carrinho.js
}

/**
 * Configura a página de checkout
 */
function setupPaginaCheckout() {
    // Aplicação de máscara para o telefone
    const inputTelefone = document.getElementById('telefone');
    if (inputTelefone) {
        inputTelefone.addEventListener('input', function() {
            // Remove tudo o que não for número
            let telefone = this.value.replace(/\D/g, '');
            
            // Aplica a máscara
            if (telefone.length > 0) {
                if (telefone.length <= 3) {
                    telefone = telefone;
                } else if (telefone.length <= 6) {
                    telefone = telefone.replace(/^(\d{3})(\d{0,3})/, '$1 $2');
                } else if (telefone.length <= 9) {
                    telefone = telefone.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1 $2 $3');
                } else {
                    telefone = telefone.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,3})/, '$1 $2 $3 $4');
                }
            }
            
            this.value = telefone;
        });
    }
    
    // Validação do formulário de checkout
    const formCheckout = document.getElementById('checkoutForm');
    if (formCheckout) {
        formCheckout.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Valida o formulário
            if (this.checkValidity()) {
                enviarPedidoWhatsApp();
            } else {
                // Exibe as mensagens de validação
                this.classList.add('was-validated');
            }
        });
    }
}

/**
 * Configura otimizações específicas para dispositivos móveis
 */
function setupMobile() {
    // Reduz a qualidade das imagens para economizar dados
    const imagensMobile = document.querySelectorAll('img:not(.logo)');
    imagensMobile.forEach(img => {
        // Adiciona a classe para carregamento progressivo
        img.classList.add('loading');
        
        // Carrega a imagem em baixa resolução primeiro
        if (img.dataset.src) {
            const srcOriginal = img.dataset.src;
            const srcLowRes = srcOriginal.replace(/\.(jpg|jpeg|png)$/i, '-low.$1');
            
            img.src = srcLowRes;
            
            // Carrega a versão de alta resolução depois
            const imgHighRes = new Image();
            imgHighRes.onload = function() {
                img.src = srcOriginal;
                img.classList.remove('loading');
                img.classList.add('loaded');
            };
            imgHighRes.src = srcOriginal;
        }
    });
    
    // Simplifica alguns elementos da UI para melhor desempenho
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        // Reduz o número de slides visíveis em carrosséis
        const carouselInner = carousel.querySelector('.carousel-inner');
        if (carouselInner && carouselInner.children.length > 3) {
            // Mantém apenas os 3 primeiros slides para economizar recursos
            const slidesToKeep = Array.from(carouselInner.children).slice(0, 3);
            carouselInner.innerHTML = '';
            slidesToKeep.forEach(slide => {
                carouselInner.appendChild(slide);
            });
        }
    });
}

/**
 * Aplica efeitos visuais e animações
 */
function aplicarEfeitosVisuais() {
    // Animação de fade-in para elementos principais
    const elementosAnimados = document.querySelectorAll('.animate-on-scroll');
    
    if (elementosAnimados.length > 0) {
        // Função para verificar se o elemento está visível na tela
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom >= 0
            );
        }
        
        // Função para animar elementos visíveis
        function animarElementosVisiveis() {
            elementosAnimados.forEach(el => {
                if (isElementInViewport(el) && !el.classList.contains('animated')) {
                    el.classList.add('fadeIn', 'animated');
                }
            });
        }
        
        // Executa a função ao carregar a página
        animarElementosVisiveis();
        
        // Executa a função ao rolar a página
        window.addEventListener('scroll', animarElementosVisiveis);
    }
    
    // Efeito de hover suave nos cartões de produtos
    const cardsProdutos = document.querySelectorAll('.card-produto');
    cardsProdutos.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('shadow-lg');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('shadow-lg');
        });
    });
}

/**
 * Formata um valor para a moeda local (Guarani)
 * @param {Number} valor - Valor a ser formatado
 * @returns {String} Valor formatado
 */
function formatarPreco(valor) {
    return 'G$ ' + valor.toLocaleString('es-PY');
}