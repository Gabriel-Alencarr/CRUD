# CRUD em PHP

Projeto simples de CRUD com PHP + MySQL em uma única página para criar, editar, listar e excluir clientes.

## Arquivos

```
teste/
├── index.php
├── config.php
└── README.md
```

## Como usar

1. Ajuste as credenciais do banco em `config.php` se necessário.
2. Execute o projeto com o servidor do PHP.

```bash
php -S localhost:8000
```

3. Abra `http://localhost:8000` no navegador.

## O que o projeto faz

- Cria clientes
- Lista clientes
- Edita clientes
- Exclui clientes

## Banco de dados

O arquivo `config.php` cria automaticamente o banco `crud_db` e a tabela `clientes` na primeira execução, desde que o usuário do MySQL tenha permissão para criar banco de dados.

Se preferir usar um banco já existente, basta trocar o valor de `DB_NAME`.
