<?php

class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . "/../../config/database.php";
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $sql = "SELECT id, nome, cpf
                FROM pessoas
                ORDER BY id DESC";
        
        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

        if (!$id)
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $sql = "SELECT id, nome, cpf
                FROM pessoas
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa)
        {
            http_response_code(404);
            echo json_encode(["erro" => "Pessoa não encontrada."], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $cpf = trim($_POST["cpf"] ?? "");

        if ($nome === "" || $email === "" || $cpf === "")
        {
            http_response_code(400);
            echo json_encode(["erro" => "Nome, e-mail e CPF são obrigatórios."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "INSERT INTO pessoas (nome, email, cpf)
                    VALUES (:nome, :email, :cpf)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":nome", $nome);
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":cpf", $cpf);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                "mensagem" => "Pessoa cadastrada com sucesso.",
                "id" => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar pessoa."], JSON_UNESCAPED_UNICODE);
        }
    }

    public function atualizar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
        $nome = trim($_POST["nome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $cpf = trim($_POST["cpf"] ?? "");

        if (!$id || $nome === "" || $email === "")
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID, nome e e-mail são obrigatórios."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "UPDATE pessoas
                    SET nome = :nome,
                        email = :email,
                        cpf = :cpf
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":nome", $nome);
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":cpf", $cpf);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["mensagem" => "Pessoa atualizada com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar pessoa."], JSON_UNESCAPED_UNICODE);
        }
    }

    public function excluir(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

        if (!$id)
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "DELETE FROM pessoas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["mensagem" => "Pessoa excluída com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir pessoa."], JSON_UNESCAPED_UNICODE);
        }
    }
}