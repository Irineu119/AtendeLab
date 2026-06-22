<?php

class TipoAtendimentosController
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

        $sql = "SELECT id, tipo
                FROM tipos_atendimentos
				WHERE status != 'inativo'
                ORDER BY id DESC";
        
        $stmt = $this->pdo->query($sql);
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($tipos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = "SELECT id, tipo
                FROM tipos_atendimentos
                WHERE id = :id
				AND status != 'inativo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo)
        {
            http_response_code(404);
            echo json_encode(["erro" => "Tipo de atendimento não encontrado."], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $tipo = $_POST["tipo"] ?? "";
		$status = $_POST["status"] ?? "ativo";

        if ($tipo === "")
        {
            http_response_code(400);
            echo json_encode(["erro" => "Tipo de atendimento é obrigatório."]);
            return;
        }

		if (!in_array($status, ["ativo", "inativo"], true))
        {
            http_response_code(400);
            echo json_encode(["erro" => "Status inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "INSERT INTO tipos_atendimentos (tipo)
                    VALUES (:tipo)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":tipo", $tipo);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                "mensagem" => "Tipo de atendimento cadastrado com sucesso.",
                "id" => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar tipo de atendimento."], JSON_UNESCAPED_UNICODE);
        }
    }

    public function atualizar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
        $tipo = $_POST["tipo"];
		$status = $_POST["status"] ?? "ativo";

        if (!$id || $tipo === "")
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID e tipo são obrigatórios."], JSON_UNESCAPED_UNICODE);
            return;
        }

		if (!in_array($status, ["ativo", "inativo"], true))
        {
            http_response_code(400);
            echo json_encode(["erro" => "Status inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "UPDATE tipos_atendimentos
                    SET tipo = :tipo
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":tipo", $tipo);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["mensagem" => "Tipo de atendimento atualizado com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar tipo de atendimeto."], JSON_UNESCAPED_UNICODE);
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
            $sql = "UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["mensagem" => "Tipo de atendimento excluído com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir tipo de atendimento."], JSON_UNESCAPED_UNICODE);
        }
    }
}