<?php
declare(strict_types=1);

// Cadastra um novo usuario.
require_once __DIR__ . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Metodo nao permitido.',
    ], 500);
}

$dados = getRequestData();
$nome = trim((string) ($dados['nome'] ?? ''));
$email = trim((string) ($dados['email'] ?? ''));

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

    // Insere o novo usuario usando Prepared Statement.
    $statement = $pdo->prepare('INSERT INTO usuarios (nome, email) VALUES (:nome, :email)');
    $statement->execute([
        'nome' => $nome,
        'email' => $email,
    ]);

    $id = (int) $pdo->lastInsertId();

    sendJsonResponse([
        'sucesso' => true,
        'mensagem' => 'Usuario cadastrado com sucesso.',
        'dados' => [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
        ],
    ], 201);
} catch (PDOException $exception) {
    sendJsonResponse([
        'sucesso' => false,
        'mensagem' => 'Erro ao cadastrar usuario.',
    ], 500);
}