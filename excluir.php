<?php
declare(strict_types=1);

// Exclui um usuario pelo ID.
require_once __DIR__ . '/config.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'DELETE';

if (!in_array($requestMethod, ['DELETE', 'POST'], true)) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido.',
    ], 500);
}

$dados = getRequestData();
$id = filter_var($dados['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);

// Valida o ID enviado.
if ($id === false || $id === null) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Informe um ID valido.',
    ], 404);
}

try {
    $pdo = getDatabaseConnection();

    // Verifica se o usuario existe antes de excluir.
    $checkStatement = $pdo->prepare('SELECT id FROM usuarios WHERE id = :id LIMIT 1');
    $checkStatement->execute(['id' => $id]);

    if (!$checkStatement->fetch()) {
        sendJsonResponse([
            'sucesso' => false,
            'mensagem' => 'Usuario nao encontrado.',
        ], 404);
    }

    // Remove o usuario usando Prepared Statement.
    $deleteStatement = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
    $deleteStatement->execute(['id' => $id]);

    sendJsonResponse([
        'sucesso' => true,
        'mensagem' => 'Usuario excluido com sucesso.',
    ], 200);
} catch (PDOException $exception) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Erro ao excluir usuario.',
    ], 500);
}