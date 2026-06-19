<?php
declare(strict_types=1);

// Busca um usuario pelo ID enviado na query string.
require_once __DIR__ . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido.',
    ], 500);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === null || $id === false) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Informe um ID valido.',
    ], 404);
}

try {
    $pdo = getDatabaseConnection();

    // Prepara a consulta para evitar injecao de SQL.
    $statement = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $usuario = $statement->fetch();

    if (!$usuario) {
        sendJsonResponse([
            'sucesso' => false,
            'mensagem' => 'Usuario nao encontrado.',
        ], 404);
    }

    sendJsonResponse([
        'sucesso' => true,
        'dados' => $usuario,
    ], 200);
} catch (PDOException $exception) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Erro ao buscar usuario.',
    ], 500);
}