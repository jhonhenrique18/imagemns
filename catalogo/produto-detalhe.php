<?php
// Incluir arquivo de conexão
require_once "includes/conexao.php";

// Obter o ID do produto da URL
$produto_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

// Buscar o produto
$produto = buscarRegistro("produtos", $produto_id);

// Se o produto não existir ou não estiver ativo, redireciona para a página de produtos
if (!$produto || !$produto["ativo"]) {
    header("Location: produtos.php");
    exit;
}

// Buscar imagens adicionais do produto
$imagens = buscarImagensProduto($produto_id);

// Buscar a categoria do produto
$categoria = $produto["categoria_id"] ? buscarRegistro("categorias", $produto["categoria_id"]) : null;

// Buscar produtos relacionados (mesma categoria)
$produtos_relacionados = $produto["categoria_id"] ? buscarProdutosPorCategoria($produto["categoria_id"], 4, $produto_id) : []; // Adicionado $produto_id para excluir o próprio produto

// Configurações da página
$titulo_pagina = htmlspecialchars($produto["nome"]);

// Incluir o cabeçalho
include "includes/header.php";

// Definições de fallback para constantes, caso não existam
if (!defined("SITE_URL")) {
    define("SITE_URL", ".");
}
if (!defined("WHATSAPP")) {
    define("WHATSAPP", "+5511999999999"); // Exemplo
}
if (!defined("VALOR_FRETE_POR_KG")) {
    define("VALOR_FRETE_POR_KG", 0); // Exemplo
}

?>

<!-- Classe da página para JavaScript -->
<div class="produto-detalhe-page">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars(SITE_URL); ?>/index.php">Inicio</a></li>
            <?php if ($categoria): ?>
            <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars(SITE_URL); ?>/produtos.php?categoria=<?php echo htmlspecialchars($categoria["id"]); ?>"><?php echo htmlspecialchars($categoria["nome"]); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($produto["nome"]); ?></li>
        </ol>
    </nav>
    
    <!-- Detalhes do produto -->
    <div class="row g-lg-5 mb-5">
        <!-- Imagens do produto -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="produto-imagens animate-on-scroll">
                <?php 
                $imagem_principal_path = "assets/img/produto_placeholder.jpg"; // Placeholder padrão
                if (!empty($produto["imagem_principal"])) {
                    $test_paths = [
                        "assets/img/" . $produto["imagem_principal"],
                        "assets/img/produtos/" . basename($produto["imagem_principal"])
                    ];
                    foreach ($test_paths as $path) {
                        if (file_exists($path)) {
                            $imagem_principal_path = $path;
                            break;
                        }
                    }
                }
                // Criar placeholder se não existir e for o caminho padrão
                if ($imagem_principal_path === "assets/img/produto_placeholder.jpg" && !file_exists($imagem_principal_path)) {
                    $placeholder_dir = "assets/img";
                    if (!is_dir($placeholder_dir)) {
                        mkdir($placeholder_dir, 0755, true);
                    }
                    $img_placeholder = imagecreatetruecolor(600, 600);
                    $bg_color_placeholder = imagecolorallocate($img_placeholder, 240, 240, 240);
                    $text_color_placeholder = imagecolorallocate($img_placeholder, 150, 150, 150);
                    imagefilledrectangle($img_placeholder, 0, 0, 599, 599, $bg_color_placeholder);
                    imagestring($img_placeholder, 5, 180, 290, "Imagem Indisponível", $text_color_placeholder);
                    imagejpeg($img_placeholder, $imagem_principal_path, 90);
                    imagedestroy($img_placeholder);
                }
                ?>
                <img src="<?php echo htmlspecialchars($imagem_principal_path); ?>" alt="<?php echo htmlspecialchars($produto["nome"]); ?>" class="produto-imagem-principal img-fluid mb-3 rounded shadow-sm" style="max-height: 500px; width: 100%; object-fit: contain;">
                
                <?php if (count($imagens) > 0): ?>
                <div class="galeria-miniaturas d-flex flex-wrap gap-2">
                    <!-- Miniatura da imagem principal -->
                    <img src="<?php echo htmlspecialchars($imagem_principal_path); ?>" alt="<?php echo htmlspecialchars($produto["nome"]); ?>" class="galeria-miniatura active" style="width: 70px; height: 70px; object-fit: cover; cursor: pointer; border: 2px solid var(--primary-color); border-radius: 4px;">
                    
                    <!-- Miniaturas das imagens adicionais -->
                    <?php foreach ($imagens as $imagem_adicional): ?>
                    <?php
                    $imagem_adicional_path = "assets/img/produto_placeholder.jpg"; // Placeholder padrão
                    if (!empty($imagem_adicional["imagem"])){
                        $test_paths_adicional = [
                            "assets/img/" . $imagem_adicional["imagem"],
                            "assets/img/produtos/" . basename($imagem_adicional["imagem"])
                        ];
                        foreach ($test_paths_adicional as $path_adicional) {
                            if (file_exists($path_adicional)) {
                                $imagem_adicional_path = $path_adicional;
                                break;
                            }
                        }
                    }
                    if ($imagem_adicional_path !== "assets/img/produto_placeholder.jpg" || file_exists($imagem_adicional_path)) : // Só mostra se existir ou não for o placeholder default
                    ?>
                    <img src="<?php echo htmlspecialchars($imagem_adicional_path); ?>" alt="<?php echo htmlspecialchars($produto["nome"]); ?>" class="galeria-miniatura" style="width: 70px; height: 70px; object-fit: cover; cursor: pointer; border: 2px solid transparent; border-radius: 4px;">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Informações do produto -->
        <div class="col-lg-6">
            <div class="animate-on-scroll">
                <h1 class="mb-3 h2"><?php echo htmlspecialchars($produto["nome"]); ?></h1>
                
                <!-- Preço -->
                <div class="mb-3">
                    <div class="produto-preco h3 text-success" 
                         data-preco="<?php echo htmlspecialchars($produto["preco"]); ?>" 
                         data-preco-atacado="<?php echo htmlspecialchars($produto["preco_atacado"] ?? 0); ?>" 
                         data-qtd-atacado="<?php echo htmlspecialchars($produto["quantidade_atacado"] ?? 1); ?>">
                        <?php echo formatarPreco($produto["preco"]); ?>
                        <span class="badge bg-warning text-dark badge-atacado ms-2 d-none">MAYORISTA</span>
                    </div>
                    
                    <?php if (!empty($produto["preco_atacado"]) && $produto["preco_atacado"] > 0): ?>
                    <div class="mb-2">
                        <small class="text-muted">Precio mayorista: <?php echo formatarPreco($produto["preco_atacado"]); ?> (a partir de <?php echo htmlspecialchars($produto["quantidade_atacado"]); ?> unidades)</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="produto-economia text-success fw-bold d-none mt-1"></div>
                </div>
                
                <!-- Descrição do produto -->
                <div class="mb-4">
                    <p><?php echo nl2br(htmlspecialchars($produto["descricao"] ?: "Sin descripción disponible.")); ?></p>
                </div>
                
                <!-- Quantidade e Total -->
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-5 col-md-4">
                            <label for="quantidade" class="form-label fw-bold">Cantidad:</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-diminuir" type="button"><i class="fas fa-minus"></i></button>
                                <input type="number" class="form-control text-center produto-qtd" id="quantidade" min="1" value="1">
                                <button class="btn btn-outline-secondary btn-aumentar" type="button"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-sm-7 col-md-8 text-sm-end">
                            <label class="form-label fw-bold">Total:</label>
                            <div class="h4 produto-preco-total mb-0"><?php echo formatarPreco($produto["preco"]); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Botão de adicionar ao carrinho -->
                <div class="mb-4 d-grid">
                    <button class="btn btn-success btn-lg btn-adicionar-carrinho-detalhes" 
                            data-id="<?php echo htmlspecialchars($produto["id"]); ?>"
                            data-nome="<?php echo htmlspecialchars($produto["nome"]); ?>"
                            data-preco="<?php echo htmlspecialchars($produto["preco"]); ?>"
                            data-preco-atacado="<?php echo htmlspecialchars($produto["preco_atacado"] ?? 0); ?>"
                            data-quantidade-atacado="<?php echo htmlspecialchars($produto["quantidade_atacado"] ?? 1); ?>"
                            data-peso="<?php echo htmlspecialchars($produto["peso"] ?? 0); ?>"
                            data-imagem="<?php echo htmlspecialchars($imagem_principal_path); ?>">
                        <i class="fas fa-cart-plus me-2"></i> Agregar al Carrito
                    </button>
                </div>
                
                <!-- Informações adicionais -->
                <div class="small text-muted">
                    <?php if (!empty($produto["peso"]) && $produto["peso"] > 0): ?>
                    <p class="mb-1"><i class="fas fa-box me-2"></i> Peso: <?php echo number_format($produto["peso"], 3, ",", "."); ?> kg</p>
                    <p class="mb-1"><i class="fas fa-truck me-2"></i> Flete: <?php echo formatarPreco(VALOR_FRETE_POR_KG * $produto["peso"]); ?> por unidad</p>
                    <?php endif; ?>
                    <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Consulte por disponibilidad en mayorista</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA WhatsApp para consultas -->
    <div class="bg-light p-4 rounded shadow-sm text-center mb-5 animate-on-scroll">
        <h4>¿Tiene preguntas sobre este producto?</h4>
        <p class="mb-3">Contáctenos y le atenderemos lo más pronto posible.</p>
        <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', WHATSAPP)); ?>?text=<?php echo rawurlencode("Hola, me gustaría saber más sobre el producto: " . $produto["nome"]); ?>" target="_blank" class="btn btn-success">
            <i class="fab fa-whatsapp me-2"></i> Consultar por WhatsApp
        </a>
    </div>
    
    <!-- Produtos relacionados -->
    <?php if (count($produtos_relacionados) > 0): ?>
    <section class="mb-5">
        <h3 class="mb-4">Productos Relacionados</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php foreach ($produtos_relacionados as $prod_rel): ?>
            <div class="col">
                <div class="card h-100 produto-card animate-on-scroll">
                    <?php if (!empty($prod_rel["preco_atacado"]) && $prod_rel["preco_atacado"] > 0): ?>
                    <div class="position-absolute end-0 top-0 p-2">
                        <span class="badge bg-warning text-dark">Mayorista</span>
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo htmlspecialchars(SITE_URL); ?>/produto-detalhe.php?id=<?php echo htmlspecialchars($prod_rel["id"]); ?>">
                        <?php 
                        $imagem_rel_path = "assets/img/produto_placeholder.jpg";
                        if (!empty($prod_rel["imagem_principal"])) {
                            $test_paths_rel = [
                                "assets/img/" . $prod_rel["imagem_principal"],
                                "assets/img/produtos/" . basename($prod_rel["imagem_principal"])
                            ];
                            foreach ($test_paths_rel as $path_rel) {
                                if (file_exists($path_rel)) {
                                    $imagem_rel_path = $path_rel;
                                    break;
                                }
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagem_rel_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod_rel["nome"]); ?>" style="height: 180px; object-fit: cover;">
                    </a>
                    
                    <div class="card-body d-flex flex-column">
                        <a href="<?php echo htmlspecialchars(SITE_URL); ?>/produto-detalhe.php?id=<?php echo htmlspecialchars($prod_rel["id"]); ?>" class="text-decoration-none">
                            <h5 class="card-title"><?php echo htmlspecialchars($prod_rel["nome"]); ?></h5>
                        </a>
                        
                        <div class="mt-auto">
                            <div class="card-preco mb-2">
                                <span class="fs-5 fw-bold text-success"><?php echo formatarPreco($prod_rel["preco"]); ?></span>
                                <?php if (!empty($prod_rel["preco_atacado"]) && $prod_rel["preco_atacado"] > 0): ?>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($prod_rel["quantidade_atacado"]); ?>+ und: <?php echo formatarPreco($prod_rel["preco_atacado"]); ?></small>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo htmlspecialchars(SITE_URL); ?>/produto-detalhe.php?id=<?php echo htmlspecialchars($prod_rel["id"]); ?>" class="btn btn-outline-secondary btn-sm w-100">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const imagemPrincipal = document.querySelector(".produto-imagem-principal");
    const miniaturas = document.querySelectorAll(".galeria-miniatura");
    
    if (imagemPrincipal && miniaturas.length > 0) {
        miniaturas.forEach(miniatura => {
            miniatura.addEventListener("click", function() {
                miniaturas.forEach(m => {
                    m.classList.remove("active");
                    m.style.borderColor = "transparent";
                });
                this.classList.add("active");
                this.style.borderColor = "var(--primary-color)";
                imagemPrincipal.src = this.src;
            });
        });
    }
    
    const inputQtd = document.querySelector(".produto-qtd");
    const btnDiminuir = document.querySelector(".btn-diminuir");
    const btnAumentar = document.querySelector(".btn-aumentar");
    const precoElement = document.querySelector(".produto-preco");
    const precoTotalElement = document.querySelector(".produto-preco-total");
    const badgeAtacado = document.querySelector(".badge-atacado");
    const economiaElement = document.querySelector(".produto-economia");

    function formatarMoeda(valor) {
        return parseFloat(valor).toLocaleString("es-PY", { style: "currency", currency: "PYG", minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function atualizarPrecoTotal() {
        if (!inputQtd || !precoElement || !precoTotalElement) return;

        let qtd = parseInt(inputQtd.value);
        if (isNaN(qtd) || qtd < 1) {
            qtd = 1;
            inputQtd.value = qtd;
        }

        const precoBase = parseFloat(precoElement.dataset.preco);
        const precoAtacado = parseFloat(precoElement.dataset.precoAtacado);
        const qtdAtacado = parseInt(precoElement.dataset.qtdAtacado);
        let precoFinalUnitario = precoBase;
        let economiaTotal = 0;

        if (precoAtacado > 0 && qtd >= qtdAtacado) {
            precoFinalUnitario = precoAtacado;
            if(badgeAtacado) badgeAtacado.classList.remove("d-none");
            if(economiaElement) {
                economiaTotal = (precoBase - precoAtacado) * qtd;
                economiaElement.textContent = `¡Ahorras ${formatarMoeda(economiaTotal)}!`;
                economiaElement.classList.remove("d-none");
            }
        } else {
            if(badgeAtacado) badgeAtacado.classList.add("d-none");
            if(economiaElement) economiaElement.classList.add("d-none");
        }
        
        precoTotalElement.textContent = formatarMoeda(precoFinalUnitario * qtd);
    }

    if (inputQtd) {
        inputQtd.addEventListener("change", atualizarPrecoTotal);
        inputQtd.addEventListener("input", atualizarPrecoTotal); // Para cobrir setas do input number
    }
    if (btnDiminuir) {
        btnDiminuir.addEventListener("click", () => {
            let qtd = parseInt(inputQtd.value);
            if (qtd > 1) inputQtd.value = qtd - 1;
            atualizarPrecoTotal();
        });
    }
    if (btnAumentar) {
        btnAumentar.addEventListener("click", () => {
            let qtd = parseInt(inputQtd.value);
            inputQtd.value = qtd + 1;
            atualizarPrecoTotal();
        });
    }

    const btnAdicionarCarrinhoDetalhes = document.querySelector(".btn-adicionar-carrinho-detalhes");
    if (btnAdicionarCarrinhoDetalhes) {
        btnAdicionarCarrinhoDetalhes.addEventListener("click", function() {
            const produto = {
                id: this.dataset.id,
                nome: this.dataset.nome,
                preco: parseFloat(this.dataset.preco),
                preco_atacado: parseFloat(this.dataset.precoAtacado),
                quantidade_atacado: parseInt(this.dataset.quantidadeAtacado),
                peso: parseFloat(this.dataset.peso),
                imagem: this.dataset.imagem.startsWith("assets/img/") ? this.dataset.imagem.substring("assets/img/".length) : this.dataset.imagem // Ajustar caminho da imagem
            };
            const quantidade = parseInt(inputQtd.value) || 1;
            
            // Adicionar ao carrinho a quantidade especificada
            for (let i = 0; i < quantidade; i++) {
                 if (typeof adicionarAoCarrinho === "function") {
                    adicionarAoCarrinho(produto, 1); // Adiciona 1 por vez para lógica de atacado no carrinho.js
                } else {
                    console.error("Função adicionarAoCarrinho não definida.");
                }
            }
            // Poderia resetar a quantidade para 1, mas talvez o usuário queira adicionar mais
            // inputQtd.value = 1;
            // atualizarPrecoTotal();
        });
    }
    
    // Inicializar preço total ao carregar a página
    atualizarPrecoTotal();
});
</script>

<?php
// Incluir o rodapé
include "includes/footer.php";
?>
