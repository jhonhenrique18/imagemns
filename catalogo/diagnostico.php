<?php
// Incluir arquivo de configuração
require_once 'config.php';

// Função para verificar se um diretório existe e tem permissão de escrita
function verificarDiretorio($caminho) {
    if (!file_exists($caminho)) {
        return [
            'existe' => false,
            'gravavel' => false,
            'mensagem' => 'O diretório não existe.'
        ];
    } elseif (!is_dir($caminho)) {
        return [
            'existe' => true,
            'gravavel' => false,
            'mensagem' => 'O caminho existe, mas não é um diretório.'
        ];
    } elseif (!is_writable($caminho)) {
        return [
            'existe' => true,
            'gravavel' => false,
            'mensagem' => 'O diretório existe, mas não tem permissão de escrita.'
        ];
    } else {
        return [
            'existe' => true,
            'gravavel' => true,
            'mensagem' => 'O diretório existe e tem permissão de escrita.'
        ];
    }
}

// Função para listar arquivos em um diretório
function listarArquivos($caminho, $extensoes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!is_dir($caminho)) {
        return [];
    }
    
    $arquivos = [];
    
    if ($handle = opendir($caminho)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $extensoes)) {
                    $arquivos[] = $file;
                }
            }
        }
        closedir($handle);
    }
    
    return $arquivos;
}

// Diretórios a verificar
$diretorios = [
    'assets/img' => verificarDiretorio('assets/img'),
    'assets/img/produtos' => verificarDiretorio('assets/img/produtos'),
    'assets/img/categorias' => verificarDiretorio('assets/img/categorias'),
    'assets/img/banners' => verificarDiretorio('assets/img/banners')
];

// Listar arquivos
$arquivos_produtos = listarArquivos('assets/img/produtos');
$arquivos_categorias = listarArquivos('assets/img/categorias');
$arquivos_banners = listarArquivos('assets/img/banners');

// Verificar a conexão com o banco de dados
try {
    $pdo->query("SELECT 1");
    $db_status = [
        'conectado' => true,
        'mensagem' => 'Conexão com o banco de dados estabelecida com sucesso.'
    ];
} catch (PDOException $e) {
    $db_status = [
        'conectado' => false,
        'mensagem' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()
    ];
}

// Verificar as tabelas do banco de dados
$tabelas = [
    'produtos' => false,
    'categorias' => false,
    'produto_imagens' => false,
    'pedidos' => false,
    'pedido_itens' => false,
    'banners' => false,
    'usuarios' => false
];

if ($db_status['conectado']) {
    try {
        $result = $pdo->query("SHOW TABLES");
        $tabelas_existentes = $result->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tabelas as $tabela => $valor) {
            $tabelas[$tabela] = in_array($tabela, $tabelas_existentes);
        }
    } catch (PDOException $e) {
        // Ignorar erro
    }
}

// Verificar os produtos no banco de dados
$produtos_lista = [];
if ($db_status['conectado'] && $tabelas['produtos']) {
    try {
        $stmt = $pdo->query("SELECT id, nome, imagem_principal FROM produtos LIMIT 10");
        $produtos_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ignorar erro
    }
}

// Verificar as categorias no banco de dados
$categorias_lista = [];
if ($db_status['conectado'] && $tabelas['categorias']) {
    try {
        $stmt = $pdo->query("SELECT id, nome, imagem FROM categorias");
        $categorias_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ignorar erro
    }
}

// Verificar os caminhos das imagens
function verificarCaminhoImagem($caminho_base, $nome_arquivo) {
    if (empty($nome_arquivo)) {
        return [
            'status' => 'warning',
            'mensagem' => 'Imagem não definida'
        ];
    }
    
    $caminho_completo = $caminho_base . '/' . $nome_arquivo;
    
    if (!file_exists($caminho_completo)) {
        return [
            'status' => 'error',
            'mensagem' => 'Arquivo não encontrado'
        ];
    }
    
    if (!is_readable($caminho_completo)) {
        return [
            'status' => 'warning',
            'mensagem' => 'Arquivo não tem permissão de leitura'
        ];
    }
    
    return [
        'status' => 'success',
        'mensagem' => 'OK'
    ];
}

// Informações do PHP
$phpinfo = [
    'Versão PHP' => phpversion(),
    'Sistema Operacional' => php_uname(),
    'Servidor Web' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido',
    'Extensões Habilitadas' => implode(', ', get_loaded_extensions())
];

// Constantes do sistema
$constantes = [
    'SITE_NOME' => SITE_NOME,
    'SITE_URL' => SITE_URL,
    'WHATSAPP' => WHATSAPP,
    'VALOR_FRETE_POR_KG' => VALOR_FRETE_POR_KG,
    'MOEDA_SIMBOLO' => MOEDA_SIMBOLO
];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema - <?php echo SITE_NOME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-success {
            color: #28a745;
        }
        .status-warning {
            color: #ffc107;
        }
        .status-error {
            color: #dc3545;
        }
        .directory-path {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            font-size: 14px;
        }
        .file-list {
            height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Diagnóstico do Sistema</h1>
        <p class="text-center mb-4">Use esta página para diagnosticar problemas no sistema.</p>
        
        <div class="row">
            <div class="col-md-6">
                <!-- Status do Banco de Dados -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-database me-2"></i> Banco de Dados
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <?php if ($db_status['conectado']): ?>
                            <span class="status-success"><i class="fas fa-check-circle"></i> Conectado</span>
                            <?php else: ?>
                            <span class="status-error"><i class="fas fa-times-circle"></i> Erro</span>
                            <div class="mt-2 text-danger"><?php echo $db_status['mensagem']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <strong>Tabelas:</strong>
                            <ul class="list-unstyled mt-2">
                                <?php foreach ($tabelas as $tabela => $existe): ?>
                                <li>
                                    <?php if ($existe): ?>
                                    <span class="status-success"><i class="fas fa-check-circle"></i></span>
                                    <?php else: ?>
                                    <span class="status-error"><i class="fas fa-times-circle"></i></span>
                                    <?php endif; ?>
                                    <?php echo $tabela; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Diretórios -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-folder me-2"></i> Diretórios
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($diretorios as $dir => $status): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="directory-path"><?php echo $dir; ?></span>
                                <?php if ($status['existe'] && $status['gravavel']): ?>
                                <span class="badge bg-success">OK</span>
                                <?php elseif ($status['existe']): ?>
                                <span class="badge bg-warning">Sem permissão de escrita</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Não existe</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Constantes do Sistema -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-cog me-2"></i> Configurações do Sistema
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($constantes as $const => $valor): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo $const; ?></span>
                                <span class="badge bg-secondary"><?php echo $valor; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Produtos no Banco de Dados -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-box me-2"></i> Produtos no Banco de Dados
                    </div>
                    <div class="card-body">
                        <?php if (empty($produtos_lista)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i> Nenhum produto encontrado no banco de dados.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Imagem</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos_lista as $produto): ?>
                                    <tr>
                                        <td><?php echo $produto['id']; ?></td>
                                        <td><?php echo $produto['nome']; ?></td>
                                        <td class="text-truncate" style="max-width: 150px;"><?php echo $produto['imagem_principal'] ?: 'Não definida'; ?></td>
                                        <td>
                                            <?php 
                                            $status = verificarCaminhoImagem('assets/img/produtos', $produto['imagem_principal']);
                                            if ($status['status'] === 'success'): 
                                            ?>
                                            <span class="status-success"><i class="fas fa-check-circle"></i></span>
                                            <?php elseif ($status['status'] === 'warning'): ?>
                                            <span class="status-warning"><i class="fas fa-exclamation-circle"></i></span>
                                            <?php else: ?>
                                            <span class="status-error"><i class="fas fa-times-circle"></i></span>
                                            <?php endif; ?>
                                            <?php echo $status['mensagem']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Arquivos de Imagens -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-images me-2"></i> Arquivos de Imagens
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Produtos</h6>
                                <div class="file-list">
                                    <?php if (empty($arquivos_produtos)): ?>
                                    <div class="text-muted">Nenhum arquivo encontrado</div>
                                    <?php else: ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($arquivos_produtos as $arquivo): ?>
                                        <li><?php echo $arquivo; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Categorias</h6>
                                <div class="file-list">
                                    <?php if (empty($arquivos_categorias)): ?>
                                    <div class="text-muted">Nenhum arquivo encontrado</div>
                                    <?php else: ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($arquivos_categorias as $arquivo): ?>
                                        <li><?php echo $arquivo; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Banners</h6>
                                <div class="file-list">
                                    <?php if (empty($arquivos_banners)): ?>
                                    <div class="text-muted">Nenhum arquivo encontrado</div>
                                    <?php else: ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($arquivos_banners as $arquivo): ?>
                                        <li><?php echo $arquivo; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do PHP -->
                <div class="card">
                    <div class="card-header">
                        <i class="fab fa-php me-2"></i> Informações do PHP
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($phpinfo as $info => $valor): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <span><?php echo $info; ?></span>
                                <span class="text-muted text-truncate" style="max-width: 300px;"><?php echo $valor; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 mb-5">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home me-1"></i> Voltar para a página inicial
            </a>
            <a href="admin/" class="btn btn-secondary ms-2">
                <i class="fas fa-cog me-1"></i> Acessar o painel administrativo
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>