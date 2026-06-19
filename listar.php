<?php
declare(strict_types=1);

// Lista todos os usuarios cadastrados.
require_once __DIR__ . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido.',
    ], 500);
}

try {
    $pdo = getDatabaseConnection();

    // Busca todos os usuarios ordenados pelo ID.
    $statement = $pdo->query('SELECT id, nome, email FROM usuarios ORDER BY id ASC');
    $usuarios = $statement->fetchAll();

    sendJsonResponse([
        'sucesso' => true,
        'total' => count($usuarios),
        'dados' => $usuarios,
    ], 200);
} catch (PDOException $exception) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Erro ao listar usuarios.',
    ], 500);
}