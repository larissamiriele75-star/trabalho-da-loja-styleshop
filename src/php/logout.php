<?php

session_start();

// Encerrar todas as informações da sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

// Responder em JSON
header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "sucesso" => true,
    "mensagem" => "Logout realizado com sucesso."
]);

exit;
?>