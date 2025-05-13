<?php
// Configurações da página
$titulo_pagina = 'Gerenciar Usuários';

// Incluir arquivo de autenticação
require_once 'includes/auth.php';

// Processamento do formulário de novo usuário ou edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? sanitizar($_POST['nome']) : '';
    $email = isset($_POST['email']) ? sanitizar($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirma_senha = isset($_POST['confirma_senha']) ? $_POST['confirma_senha'] : '';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validação
    $erro = false;
    $mensagem_erro = '';
    
    if (empty($nome)) {
        $erro = true;
        $mensagem_erro .= 'O nome é obrigatório.<br>';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = true;
        $mensagem_erro .= 'E-mail inválido.<br>';
    }
    
    // Verifica se o e-mail já existe para outro usuário
    if (!$erro) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $erro = true;
            $mensagem_erro .= 'Este e-mail já está em uso por outro usuário.<br>';
        }
    }
    
    // Verifica senha para novos usuários ou quando está sendo alterada
    if ($id == 0 || !empty($senha)) {
        if (strlen($senha) < 6) {
            $erro = true;
            $mensagem_erro .= 'A senha deve ter pelo menos 6 caracteres.<br>';
        }
        
        if ($senha !== $confirma_senha) {
            $erro = true;
            $mensagem_erro .= 'As senhas não conferem.<br>';
        }
    }
    
    if (!$erro) {
        try {
            // Preparar dados
            $dados = [
                'nome' => $nome,
                'email' => $email,
                'ativo' => $ativo
            ];
            
            // Adiciona senha se estiver sendo definida/alterada
            if (!empty($senha)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $dados['senha'] = $senha_hash;
            }
            
            // Inserir ou atualizar usuário
            if ($id > 0) {
                // Atualizar usuário existente
                if (atualizarRegistro('usuarios', $dados, $id)) {
                    $_SESSION['mensagem'] = 'Usuário atualizado com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao atualizar o usuário.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            } else {
                // Inserir novo usuário
                if (inserirRegistro('usuarios', $dados)) {
                    $_SESSION['mensagem'] = 'Usuário adicionado com sucesso!';
                    $_SESSION['mensagem_tipo'] = 'success';
                } else {
                    $_SESSION['mensagem'] = 'Erro ao adicionar o usuário.';
                    $_SESSION['mensagem_tipo'] = 'danger';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro no banco de dados: ' . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } else {
        $_SESSION['mensagem'] = $mensagem_erro;
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: usuarios.php');
    exit;
}

// Ação para exclusão
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    // Não permitir excluir o próprio usuário
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['mensagem'] = 'Você não pode excluir seu próprio usuário.';
        $_SESSION['mensagem_tipo'] = 'warning';
        header('Location: usuarios.php');
        exit;
    }
    
    try {
        // Excluir o usuário
        if (excluirRegistro('usuarios', $id)) {
            $_SESSION['mensagem'] = 'Usuário excluído com sucesso!';
            $_SESSION['mensagem_tipo'] = 'success';
        } else {
            $_SESSION['mensagem'] = 'Erro ao excluir o usuário.';
            $_SESSION['mensagem_tipo'] = 'danger';
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro ao excluir o usuário: ' . $e->getMessage();
        $_SESSION['mensagem_tipo'] = 'danger';
    }
    
    // Redirecionar
    header('Location: usuarios.php');
    exit;
}

// Ação para edição
$usuario_edit = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $usuario_edit = buscarRegistro('usuarios', $id);
}

// Buscar todos os usuários
$usuarios = buscarRegistros('usuarios', '', 'nome ASC');

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="row">
    <!-- Formulário de cadastro/edição -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><?php echo $usuario_edit ? 'Editar Usuário' : 'Novo Usuário'; ?></h5>
            </div>
            <div class="card-body">
                <form action="usuarios.php" method="post">
                    <!-- ID oculto para edição -->
                    <?php if ($usuario_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $usuario_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Nome do usuário -->
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $usuario_edit ? $usuario_edit['nome'] : ''; ?>" required>
                    </div>
                    
                    <!-- E-mail -->
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario_edit ? $usuario_edit['email'] : ''; ?>" required>
                    </div>
                    
                    <!-- Senha -->
                    <div class="mb-3">
                        <label for="senha" class="form-label">
                            <?php echo $usuario_edit ? 'Nova senha (deixe em branco para manter a atual)' : 'Senha <span class="text-danger">*</span>'; ?>
                        </label>
                        <input type="password" class="form-control" id="senha" name="senha" <?php echo $usuario_edit ? '' : 'required'; ?>>
                        <?php if ($usuario_edit): ?>
                        <div class="form-text">Mínimo de 6 caracteres. Deixe em branco para manter a senha atual.</div>
                        <?php else: ?>
                        <div class="form-text">Mínimo de 6 caracteres.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Confirmar senha -->
                    <div class="mb-3">
                        <label for="confirma_senha" class="form-label">
                            <?php echo $usuario_edit ? 'Confirmar nova senha' : 'Confirmar senha <span class="text-danger">*</span>'; ?>
                        </label>
                        <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" <?php echo $usuario_edit ? '' : 'required'; ?>>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo (!$usuario_edit || $usuario_edit['ativo']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ativo">Usuário ativo</label>
                        </div>
                        <div class="form-text">Usuários inativos não podem acessar o painel administrativo.</div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> <?php echo $usuario_edit ? 'Atualizar Usuário' : 'Salvar Usuário'; ?>
                        </button>
                        
                        <?php if ($usuario_edit): ?>
                        <a href="usuarios.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-1"></i> Novo Usuário
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Lista de usuários -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Usuários</h5>
                <span class="badge bg-primary"><?php echo count($usuarios); ?> usuários</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($usuarios) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Último acesso</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo $usuario['nome']; ?></td>
                                <td><?php echo $usuario['email']; ?></td>
                                <td>
                                    <?php if ($usuario['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo formatarData($usuario['updated_at'], true); ?></small>
                                </td>
                                <td>
                                    <a href="usuarios.php?editar=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if ($usuario['id'] != $_SESSION['admin_id']): ?>
                                    <a href="usuarios.php?excluir=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger btn-excluir" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Você não pode excluir seu próprio usuário">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Nenhum usuário encontrado.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Instruções sobre usuários -->
<div class="mt-4">
    <div class="alert alert-info" role="alert">
        <h5><i class="fas fa-info-circle me-2"></i> Dicas sobre usuários</h5>
        <ul class="mb-0">
            <li>Crie contas apenas para pessoas que precisam acessar o painel administrativo.</li>
            <li>Senhas devem ter no mínimo 6 caracteres e combinar letras, números e caracteres especiais.</li>
            <li>Desative usuários que não precisam mais acessar o sistema em vez de excluí-los.</li>
            <li>Você não pode excluir sua própria conta de usuário.</li>
            <li>Cada usuário é responsável pelas alterações feitas no sistema.</li>
        </ul>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>