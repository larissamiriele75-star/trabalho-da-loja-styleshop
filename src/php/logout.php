<?php

session_start();

/*
|--------------------------------------------------------------------------
| STYLES HOP - LOGOUT
|--------------------------------------------------------------------------
| Encerra a sessão do usuário e retorna para a página inicial da loja.
|--------------------------------------------------------------------------
*/


// Limpa todas as informações da sessão
$_SESSION = [];


// Remove o cookie da sessão, se estiver sendo utilizado
if (ini_get("session.use_cookies")) {

    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}


// Destrói a sessão
session_destroy();


// Volta para a página inicial da StyleShop
header("Location: ../../public/index.php");
exit;