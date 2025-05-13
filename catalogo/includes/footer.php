</div>
    </main>
    
    <!-- Rodapé -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row g-4">
                <!-- Informações da empresa -->
                <div class="col-12 col-md-4">
                    <h5 class="mb-3"><?php echo htmlspecialchars(defined("SITE_NOME") ? SITE_NOME : "Grãos S.A."); ?></h5>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Asunción, Paraguay</p>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars(defined("WHATSAPP") ? WHATSAPP : "+55 11 99999-9999"); ?></p>
                    <p class="mb-3"><i class="fas fa-envelope me-2"></i> info@graossa.com</p>
                    
                    <!-- Redes sociais -->
                    <div class="d-flex gap-2 social-icons">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
                
                <!-- Links rápidos -->
                <div class="col-12 col-md-4">
                    <h5 class="mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>" class="text-decoration-none text-white">Inicio</a></li>
                        <li><a href="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>/produtos.php" class="text-decoration-none text-white">Productos</a></li>
                        <li><a href="#" class="text-decoration-none text-white">Sobre Nosotros</a></li>
                        <li><a href="#" class="text-decoration-none text-white">Términos y Condiciones</a></li>
                        <li><a href="#" class="text-decoration-none text-white">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="col-12 col-md-4">
                    <h5 class="mb-3">Reciba Nuestras Novedades</h5>
                    <p class="mb-3">Suscríbase para recibir información sobre nuevos productos y ofertas especiales.</p>
                    <form class="mb-3" action="#" method="post"> <!-- Added action and method for completeness -->
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Su correo electrónico" name="email_newsletter" required> <!-- Added name and required -->
                            <button class="btn btn-success" type="submit">Suscribir</button>
                        </div>
                    </form>
                    <p class="small text-muted">Al suscribirse, acepta nuestra política de privacidad.</p>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-top border-secondary pt-3 mt-3 text-center">
                <p class="small mb-0">&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(defined("SITE_NOME") ? SITE_NOME : "Grãos S.A."); ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    <!-- Modal para selecionar idioma -->
    <div class="modal fade" id="modalIdioma" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Idioma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <img src="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>/assets/img/flag-es.png" width="20" class="me-2" alt="Español">
                            Español
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <img src="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>/assets/img/flag-pt.png" width="20" class="me-2" alt="Português">
                            Português
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personalizado -->
    <script src="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>/assets/js/main.js"></script>
    <script src="<?php echo htmlspecialchars(defined("SITE_URL") ? SITE_URL : "."); ?>/assets/js/carrinho.js"></script>
    
    <script>
        // Inicializar o contador do carrinho
        if (typeof atualizarContadorCarrinho === "function") { // Check if function exists
            atualizarContadorCarrinho();
        }
    </script>
</body>
</html>
