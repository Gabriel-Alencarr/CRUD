<?php
declare(strict_types=1);

require_once 'config.php';

function h(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(): void
{
    header('Location: index.php');
    exit;
}

$mensagem = '';
$clienteEdicao = [
    'id' => '',
    'nome' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($nome === '') {
        $mensagem = 'Nome obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'E-mail inválido';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE clientes SET nome = :nome, email = :email WHERE id = :id');
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':id' => $id,
            ]);
            redirecionar();
        }

        $stmt = $pdo->prepare('INSERT INTO clientes (nome, email) VALUES (:nome, :email)');
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
        ]);
        redirecionar();
    }
}

if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    redirecionar();
}

if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];

    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id, nome, email FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            $clienteEdicao = $cliente;
        }
    }
}

$clientes = $pdo->query('SELECT id, nome, email, criado_em FROM clientes ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD simples</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; padding: 32px; color: #1f2937; }
        .container { max-width: 960px; margin: 0 auto; }
        .card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.08); margin-bottom: 24px; }
        h1 { margin-top: 0; }
        .mensagem { margin-bottom: 16px; color: #b91c1c; }
        .grid { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        input { padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 16px; }
        button, .link { display: inline-block; border: 0; border-radius: 10px; padding: 10px 14px; text-decoration: none; font-size: 14px; cursor: pointer; }
        button { background: #2563eb; color: #fff; }
        .link { background: #e5e7eb; color: #111827; }
        .danger { background: #dc2626; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .acoes { display: flex; gap: 8px; }
        @media (max-width: 720px) {
            body { padding: 16px; }
            .grid { grid-template-columns: 1fr; }
            .acoes { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>CRUD de Clientes</h1>
            <p>Simples, direto e bom para nível júnior.</p>

            <?php if ($mensagem !== ''): ?>
                <div class="mensagem"><?php echo h($mensagem); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="id" value="<?php echo h((string) $clienteEdicao['id']); ?>">

                <div class="grid">
                    <div class="field">
                        <label for="nome">Nome</label>
                        <input id="nome" name="nome" type="text" required value="<?php echo h((string) $clienteEdicao['nome']); ?>">
                    </div>

                    <div class="field">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email" required value="<?php echo h((string) $clienteEdicao['email']); ?>">
                    </div>
                </div>

                <div style="margin-top: 16px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="submit"><?php echo $clienteEdicao['id'] ? 'Atualizar' : 'Salvar'; ?></button>
                    <?php if ($clienteEdicao['id']): ?>
                        <a class="link" href="index.php">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Clientes cadastrados</h2>

            <?php if (count($clientes) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo h((string) $cliente['id']); ?></td>
                                <td><?php echo h((string) $cliente['nome']); ?></td>
                                <td><?php echo h((string) $cliente['email']); ?></td>
                                <td><?php echo h(date('d/m/Y H:i', strtotime((string) $cliente['criado_em']))); ?></td>
                                <td>
                                    <div class="acoes">
                                        <a class="link" href="?editar=<?php echo h((string) $cliente['id']); ?>">Editar</a>
                                        <a class="link danger" href="?excluir=<?php echo h((string) $cliente['id']); ?>" onclick="return confirm('Deseja excluir este cliente?');">Excluir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum cliente cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
