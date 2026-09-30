<?php

$servidor = "localhost";
$usuario = "root";
$senha = "senac";
$banco = "styleshop";

$conexao = new mysqli($servidor, $usuario, $senha, $banco, 3307);

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

?>