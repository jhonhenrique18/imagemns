<?php
// Incluir o arquivo de configuração
require_once __DIR__ . '/../config.php';

// A conexão já foi estabelecida no arquivo config.php
// Este arquivo existe apenas para facilitar a inclusão em outros arquivos

// Função para buscar um único registro
function buscarRegistro($tabela, $id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM {$tabela} WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Função para buscar vários registros
function buscarRegistros($tabela, $condicao = '', $ordem = '', $limite = '') {
    global $pdo;
    
    $sql = "SELECT * FROM {$tabela}";
    
    if ($condicao) {
        $sql .= " WHERE {$condicao}";
    }
    
    if ($ordem) {
        $sql .= " ORDER BY {$ordem}";
    }
    
    if ($limite) {
        $sql .= " LIMIT {$limite}";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Função para inserir registro
function inserirRegistro($tabela, $dados) {
    global $pdo;
    
    $campos = implode(', ', array_keys($dados));
    $valores = ':' . implode(', :', array_keys($dados));
    
    $sql = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($dados as $campo => $valor) {
        $stmt->bindValue(":{$campo}", $valor);
    }
    
    if ($stmt->execute()) {
        return $pdo->lastInsertId();
    }
    
    return false;
}

// Função para atualizar registro
function atualizarRegistro($tabela, $dados, $id) {
    global $pdo;
    
    $sets = [];
    
    foreach (array_keys($dados) as $campo) {
        $sets[] = "{$campo} = :{$campo}";
    }
    
    $sql = "UPDATE {$tabela} SET " . implode(', ', $sets) . " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    foreach ($dados as $campo => $valor) {
        $stmt->bindValue(":{$campo}", $valor);
    }
    
    return $stmt->execute();
}

// Função para excluir registro
function excluirRegistro($tabela, $id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

// Função para contar registros
function contarRegistros($tabela, $condicao = '') {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM {$tabela}";
    
    if ($condicao) {
        $sql .= " WHERE {$condicao}";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchColumn();
}

// Função para buscar produtos por categoria
function buscarProdutosPorCategoria($categoria_id, $limite = '') {
    global $pdo;
    
    $sql = "SELECT * FROM produtos WHERE categoria_id = :categoria_id AND ativo = 1";
    
    if ($limite) {
        $sql .= " LIMIT {$limite}";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Função para buscar produtos em destaque
function buscarProdutosDestaque($limite = 8) {
    global $pdo;
    
    $sql = "SELECT * FROM produtos WHERE destaque = 1 AND ativo = 1 ORDER BY RAND() LIMIT :limite";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Função para buscar imagens adicionais de um produto
function buscarImagensProduto($produto_id) {
    global $pdo;
    
    $sql = "SELECT * FROM produto_imagens WHERE produto_id = :produto_id ORDER BY ordem ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Função para buscar banners ativos
function buscarBannersAtivos() {
    global $pdo;
    
    $sql = "SELECT * FROM banners WHERE ativo = 1 ORDER BY ordem ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll();
}