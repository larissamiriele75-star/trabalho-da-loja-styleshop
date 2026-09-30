<?php

session_start();

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");


// =====================================================
// VERIFICAR MÉTODO
// =====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}


// =====================================================
// RECEBER DADOS
// =====================================================

$email =
    trim($_POST["email"] ?? "");

$senha =
    $_POST["senha"] ?? "";


// =====================================================
// VALIDAR CAMPOS
// =====================================================

if (
    $email === "" ||
    $senha === ""
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha e-mail e senha."
    ]);

    exit;
}


// =====================================================
// BUSCAR USUÁRIO
// =====================================================

$sql = "
    SELECT
        id_usuario,
        nome,
        email,
        senha_segura,
        tipo_usuario
    FROM usuarios
    WHERE email = ?
    LIMIT 1
";


$stmt =
    $conexao->prepare(
        $sql
    );


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao preparar o login."
    ]);

    exit;
}


$stmt->bind_param(
    "s",
    $email
);


$stmt->execute();


$resultado =
    $stmt->get_result();


// =====================================================
// VERIFICAR USUÁRIO
// =====================================================

if (
    $resultado->num_rows === 0
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "E-mail não cadastrado."
    ]);

    exit;
}


$usuario =
    $resultado->fetch_assoc();


// =====================================================
// VERIFICAR SENHA
// =====================================================

if (
    !password_verify(
        $senha,
        $usuario["senha_segura"]
    )
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Senha incorreta."
    ]);

    exit;
}


// =====================================================
// REGENERAR ID DA SESSÃO
// =====================================================

session_regenerate_id(true);


// =====================================================
// SALVAR DADOS NA SESSÃO
// =====================================================

$_SESSION["id_usuario"] =
    $usuario["id_usuario"];

$_SESSION["nome"] =
    $usuario["nome"];

$_SESSION["email"] =
    $usuario["email"];

$_SESSION["tipo_usuario"] =
    $usuario["tipo_usuario"];


// =====================================================
// RESPONDER AO JAVASCRIPT
// =====================================================

echo json_encode([
    "sucesso" => true,

    "usuario" => [

        "id_usuario" =>
            $usuario["id_usuario"],

        "nome" =>
            $usuario["nome"],

        "email" =>
            $usuario["email"],

        "tipo_usuario" =>
            $usuario["tipo_usuario"]
    ]
]);

exit;

?>