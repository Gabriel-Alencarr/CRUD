<?php
declare(strict_types=1);

// Todas as respostas deste CRUD saem em JSON.
header('Content-Type: application/json; charset=utf-8');

// Dados de conexao do banco.
// Ajuste usuario e senha conforme o seu ambiente.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'crud_db';
const DB_USER = 'root';
const DB_PASS = '';

// Envia a resposta JSON e encerra a execucao.
function sendJsonResponse(array $data, int $statusCode): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Cria uma conexao PDO com erros lancados como excecao.
function buildPdo(string $dsn): PDO
{
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

// Retorna a conexao e garante que o banco e a tabela existam.
function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        // Primeiro conecta apenas no servidor para criar o banco, se necessario.
        $serverPdo = buildPdo('mysql:host=' . DB_HOST . ';charset=utf8mb4');
        $serverPdo->exec(
            'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );

        // Agora conecta no banco do CRUD.
        $pdo = buildPdo('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

        // Cria a tabela usuarios caso ela ainda nao exista.
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                email VARCHAR(180) NOT NULL UNIQUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        return $pdo;
    } catch (PDOException $exception) {
        sendJsonResponse([
            'sucesso' => false,
            'mensagem' => 'Erro ao conectar ao banco de dados.',
        ], 500);
    }
}

// Lê dados enviados por JSON ou por formulario.
function getRequestData(): array
{
    $rawInput = file_get_contents('php://input');

    if ($rawInput !== false && trim($rawInput) !== '') {
        $decodedData = json_decode($rawInput, true);

        if (is_array($decodedData)) {
            return $decodedData;
        }
    }

    return $_POST;
}