<?php
declare(strict_types=1);

// Atualiza um usuario existente.
require_once __DIR__ . '/config.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'PUT';

if (!in_array($requestMethod, ['PUT', 'POST'], true)) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido.',
    ], 500);
}

$dados = getRequestData();
$id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
$nome = trim((string) ($dados['nome'] ?? ''));
$email = trim((string) ($dados['email'] ?? ''));

// Valida o ID do usuario.
if ($id === false || $id === null) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Informe um ID valido.',
    ], 404);
}

// Valida o nome informado.
if ($nome === '') {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'O nome deve ser informado.',
    ], 500);
}

// Valida o email informado.
if ($email === '') {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'O email deve ser informado.',
    ], 500);
}

// Valida o formato do email.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'O email informado e invalido.',
    ], 500);
}

try {
    $pdo = getDatabaseConnection();

    // Verifica se o usuario existe antes de atualizar.
    $checkStatement = $pdo->prepare('SELECT id FROM usuarios WHERE id = :id LIMIT 1');
    $checkStatement->execute(['id' => $id]);

    if (!$checkStatement->fetch()) {
        sendJsonResponse([
            'sucesso' => false,
            'mensagem' => 'Usuario nao encontrado.',
        ], 404);
    }

    // Atualiza os dados usando Prepared Statement.
    $updateStatement = $pdo->prepare(
        'UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id'
    );
    $updateStatement->execute([
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
    ]);

    sendJsonResponse([
        'sucesso' => true,
        'mensagem' => 'Usuario atualizado com sucesso.',
        'dados' => [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
        ],
    ], 200);
} catch (PDOException $exception) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar usuario.',
    ], 500);
}