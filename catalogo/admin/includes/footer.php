</div><!-- Fim do main-content -->
            
            <!-- Footer -->
            <footer class="mt-4 text-center text-muted small py-3">
                <p class="mb-1">&copy; <?php echo date('Y'); ?> <?php echo SITE_NOME; ?>. Todos os direitos reservados.</p>
                <p class="mb-0">Painel Administrativo v1.0</p>
            </footer>
        </div><!-- Fim do content -->
        
        <!-- Overlay para o sidebar em telas pequenas -->
        <div class="overlay"></div>
    </div><!-- Fim do wrapper -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- JavaScript personalizado -->
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('.overlay').toggleClass('active');
            });
            
            // Fecha o sidebar ao clicar no overlay
            $('.overlay').on('click', function() {
                $('#sidebar').removeClass('active');
                $('.overlay').removeClass('active');
            });
            
            // Fecha alertas após 5 segundos
            window.setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
            
            // Máscaras para inputs
            if ($('.money-mask').length) {
                $('.money-mask').on('input', function() {
                    let value = $(this).val().replace(/\D/g, '');
                    value = (parseInt(value) / 100).toFixed(2).replace('.', ',');
                    $(this).val(value);
                });
            }
            
            // Confirm antes de excluir
            $('.btn-excluir').on('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir este item?')) {
                    e.preventDefault();
                }
            });
            
            // Preview de imagem
            $('.input-image').on('change', function() {
                const input = this;
                const preview = $(this).next('.image-preview');
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result);
                        preview.parent().removeClass('d-none');
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            // Datepicker inicialização (se existir)
            if ($('.datepicker').length) {
                $('.datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    language: 'pt-BR',
                    autoclose: true
                });
            }
        });
    </script>
</body>
</html>