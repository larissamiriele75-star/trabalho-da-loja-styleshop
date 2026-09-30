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

$nome =
    trim($_POST["nome"] ?? "");

$email =
    trim($_POST["email"] ?? "");

$cpf =
    trim($_POST["cpf"] ?? "");

$nascimento =
    $_POST["nascimento"] ?? "";

$senha =
    $_POST["senha"] ?? "";


// =====================================================
// LIMPAR CPF
// =====================================================

$cpf =
    preg_replace("/\D/", "", $cpf);


// =====================================================
// VALIDAR CAMPOS
// =====================================================

if (
    $nome === "" ||
    $email === "" ||
    $cpf === "" ||
    $nascimento === "" ||
    $senha === ""
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos."
    ]);

    exit;
}


// =====================================================
// VALIDAR NOME
// =====================================================

if (
    count(
        array_filter(
            explode(" ", $nome)
        )
    ) < 2
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Informe nome e sobrenome."
    ]);

    exit;
}


// =====================================================
// VALIDAR E-MAIL
// =====================================================

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "E-mail inválido."
    ]);

    exit;
}


// =====================================================
// VALIDAR CPF
// =====================================================

if (
    strlen($cpf) !== 11
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "CPF inválido."
    ]);

    exit;
}


// =====================================================
// VALIDAR SENHA
// =====================================================

if (
    strlen($senha) < 8
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "A senha deve ter pelo menos 8 caracteres."
    ]);

    exit;
}


// =====================================================
// VERIFICAR SE E-MAIL JÁ EXISTE
// =====================================================

$sqlVerificar = "
    SELECT id_usuario
    FROM usuarios
    WHERE email = ?
    LIMIT 1
";

$stmtVerificar =
    $conexao->prepare(
        $sqlVerificar
    );


if (!$stmtVerificar) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao verificar o e-mail."
    ]);

    exit;
}


$stmtVerificar->bind_param(
    "s",
    $email
);


$stmtVerificar->execute();


$resultadoVerificar =
    $stmtVerificar->get_result();


if (
    $resultadoVerificar->num_rows > 0
) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Este e-mail já está cadastrado."
    ]);

    exit;
}


$stmtVerificar->close();


// =====================================================
// CRIPTOGRAFAR SENHA
// =====================================================

$senhaSegura =
    password_hash(
        $senha,
        PASSWORD_DEFAULT
    );


// =====================================================
// INSERIR USUÁRIO
// =====================================================

$sql = "
    INSERT INTO usuarios
    (
        nome,
        cpf,
        email,
        nascimento,
        senha_segura
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )
";


$stmt =
    $conexao->prepare(
        $sql
    );


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao preparar o cadastro."
    ]);

    exit;
}


$stmt->bind_param(
    "sssss",
    $nome,
    $cpf,
    $email,
    $nascimento,
    $senhaSegura
);


// =====================================================
// EXECUTAR CADASTRO
// =====================================================

if (!$stmt->execute()) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível criar a conta."
    ]);

    exit;
}


// =====================================================
// PEGAR ID DO USUÁRIO
// =====================================================

$idUsuario =
    $stmt->insert_id;


// =====================================================
// CRIAR SESSÃO
// =====================================================

$_SESSION["id_usuario"] =
    $idUsuario;

$_SESSION["nome"] =
    $nome;

$_SESSION["email"] =
    $email;


// =====================================================
// RESPOSTA FINAL
// =====================================================

echo json_encode([
    "sucesso" => true,
    "mensagem" => "Conta criada com sucesso.",
    "usuario" => [
        "id_usuario" => $idUsuario,
        "nome" => $nome,
        "email" => $email
    ]
]);

exit;

?>