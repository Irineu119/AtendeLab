<?php

require_once __DIR__ . "/app/Controllers/UsuarioController.php";
require_once __DIR__ . "/app/Controllers/PessoasController.php";
require_once __DIR__ . "/app/Controllers/AuthController.php";

$controllerName = $_GET["controller"] ?? "auth";
$action = $_GET["action"] ?? "login";
$controller = null;

switch ($controllerName)
{
    case "usuarios":
        $controller = new UsuariosController();
        break;
    
    case "pessoas":
        $controller = new PessoasController();
        break;
    
    case "auth":
        $controller = new AuthController();
        break;

    default:
        $controller = null;
        break;
}

if ($controller !== null)
{
    if (method_exists($controller, $action))
        $controller->$action();
    else
        echo "Ação \"$action\" de \"$controllerName\" não encontrada.";
}
else
{
    echo "<h1>AtendeLab</h1>";
    echo "<p>Projeto em execução. Use ?controller=usuarios&action=listar para testar.</p>";
}