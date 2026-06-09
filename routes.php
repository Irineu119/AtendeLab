<?php

require_once __DIR__ . "/app/Controllers/UsuarioController.php";
require_once __DIR__ . "/app/Controllers/PessoasController.php";

$controllerName = $_GET["controller"] ?? "home";
$action = $_GET["action"] ?? "index";
$controller = null;

switch ($controllerName)
{
    case "usuarios":
        $controller = new UsuariosController();
        break;
    
    case "pessoas":
        $controller = new PessoasController();
        break;

    default:
        $controller = null;
        break;
}

if ($controller !== null)
{
    switch ($action)
    {
        case "listar":
            $controller->listar();
            break;

        case "buscar":
            $controller->buscarPorId();
            break;

        case "criar":
            $controller->criar();
            break;

        case "atualizar":
            $controller->atualizar();
            break;

        case "excluir":
            $controller->excluir();
            break;

        default:
            echo "Ação \"$action\" de \"$controllerName\" não encontrada.";
            break;
    }
}
else
{
    echo "<h1>AtendeLab</h1>";
    echo "<p>Projeto em execução. Use ?controller=usuarios&action=listar para testar.</p>";
}