<?php

class AtendimentosController
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

        $sql = "SELECT id, status2, usuario_id, pessoa_id, tipos_atendimento_id
                FROM atendimentos
				WHERE status != 'inativo'
                ORDER BY id DESC";
        
        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = "SELECT id, status2, usuario_id, pessoa_id, tipos_atendimento_id
                FROM atendimentos
                WHERE id = :id
				AND status != 'inativo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento)
        {
            http_response_code(404);
            echo json_encode(["erro" => "Atendimento não encontrado."], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $usuario_id = filter_input(INPUT_POST, "usuario_id", FILTER_VALIDATE_INT);
		$pessoa_id = filter_input(INPUT_POST, "pessoa_id", FILTER_VALIDATE_INT);
		$tipos_atendimento_id = filter_input(INPUT_POST, "tipos_atendimento_id", FILTER_VALIDATE_INT);
		$status2 = $_POST["status2"] ?? "nao iniciado";
		$status = $_POST["status"] ?? "ativo";

        if (!$usuario_id || !$pessoa_id || !$tipos_atendimento_id)
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID do usuário, pessoa e tipo de atendimento são obrigatórios."]);
            return;
        }

		if (!in_array($status, ["ativo", "inativo"], true))
        {
            http_response_code(400);
            echo json_encode(["erro" => "Status inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

		if (!in_array($status2, ["nao iniciado", "em andamento", "concluido"], true))
        {
            http_response_code(400);
            echo json_encode(["erro" => "Status de atendimento inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try
        {
            $sql = "INSERT INTO atendimentos (usuario_id, pessoa_id, tipos_atendimento_id, status2, status)
                    VALUES (:usuario_id, :pessoa_id, :tipos_atendimento_id, :status2, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":usuario_id", $usuario_id);
			$stmt->bindValue(":pessoa_id", $pessoa_id);
			$stmt->bindValue(":tipos_atendimento_id", $tipos_atendimento_id);
			$stmt->bindValue(":status2", $status2);
			$stmt->bindValue(":status", $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                "mensagem" => "Atendimento cadastrado com sucesso.",
                "id" => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar atendimento."], JSON_UNESCAPED_UNICODE);
        }
    }

    public function alterarAndamento(): void
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
            $sql = "UPDATE atendimentos SET status2 = 'em andamento' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(["mensagem" => "Status do atendimento alterado com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao tentar alterar o status do atendimento."], JSON_UNESCAPED_UNICODE);
        }
	}

    public function concluirAtendimento(): void
    {
        header("Content-Type: application/json; charset=utf-8");

		$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
		$obsFinal = $_POST["observacao_final"] ?? "";

        if (!$id)
        {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido."], JSON_UNESCAPED_UNICODE);
            return;
        }

		try
        {
            $sql = "UPDATE atendimentos
					SET status2 = 'concluido',
						observacao_final = :obsFinal
					WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
			$stmt->bindValue(":obsFinal", $obsFinal);
            $stmt->execute();

            echo json_encode(["mensagem" => "Status do atendimento alterado com sucesso."], JSON_UNESCAPED_UNICODE);
        }
        catch (PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao tentar alterar o status do atendimento."], JSON_UNESCAPED_UNICODE);
        }
    }
}