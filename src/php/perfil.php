<?php

session_start();
require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "sucesso" => false,
        "login" => false,
        "mensagem" => "Você precisa estar logado."
    ]);
    exit;
}

$id_usuario = (int) $_SESSION["id_usuario"];
$acao = $_POST["acao"] ?? $_GET["acao"] ?? "";


// =====================================================
// BUSCAR PERFIL
// =====================================================

if ($acao === "buscar") {

    $sql = "
        SELECT
            id_usuario,
            nome,
            nome_social,
            cpf,
            email,
            telefone,
            foto_perfil,
            nascimento,
            pronomes,
            tipo_usuario
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário não encontrado."
        ]);
        exit;
    }

    $usuario = $resultado->fetch_assoc();
    $stmt->close();


    // ENDEREÇO PRINCIPAL
    $sql = "
        SELECT
            id_endereco,
            nome_endereco,
            cep,
            estado,
            cidade,
            bairro,
            rua,
            numero,
            complemento,
            referencia,
            principal
        FROM enderecos
        WHERE id_usuario = ?
        ORDER BY principal DESC, id_endereco DESC
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $endereco = null;

    if ($resultado->num_rows > 0) {
        $endereco = $resultado->fetch_assoc();
    }

    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "usuario" => $usuario,
        "endereco" => $endereco
    ]);

    exit;
}


// =====================================================
// SALVAR DADOS PESSOAIS
// =====================================================

if ($acao === "salvar_dados") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $nascimento = trim($_POST["nascimento"] ?? "");
    $cpf = preg_replace(
        "/\D/",
        "",
        $_POST["cpf"] ?? ""
    );
    $pronomes = trim($_POST["pronomes"] ?? "");

    if (
        $nome === "" ||
        $email === "" ||
        $nascimento === "" ||
        $cpf === ""
    ) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha os campos obrigatórios."
        ]);
        exit;
    }


    // VERIFICAR EMAIL DUPLICADO
    $sql = "
        SELECT id_usuario
        FROM usuarios
        WHERE email = ?
        AND id_usuario <> ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param(
        "si",
        $email,
        $id_usuario
    );
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está sendo usado."
        ]);
        exit;
    }

    $stmt->close();


    $sql = "
        UPDATE usuarios
        SET
            nome = ?,
            email = ?,
            telefone = ?,
            nascimento = ?,
            cpf = ?,
            pronomes = ?
        WHERE id_usuario = ?
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "ssssssi",
        $nome,
        $email,
        $telefone,
        $nascimento,
        $cpf,
        $pronomes,
        $id_usuario
    );

    if (!$stmt->execute()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível atualizar o perfil."
        ]);
        exit;
    }

    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Perfil atualizado com sucesso."
    ]);

    exit;
}


// =====================================================
// SALVAR FOTO
// =====================================================

if ($acao === "salvar_foto") {

    $foto = $_POST["foto"] ?? "";

    if ($foto === "") {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Nenhuma foto recebida."
        ]);
        exit;
    }

    $sql = "
        UPDATE usuarios
        SET foto_perfil = ?
        WHERE id_usuario = ?
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "si",
        $foto,
        $id_usuario
    );

    if (!$stmt->execute()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível salvar a foto."
        ]);
        exit;
    }

    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Foto atualizada."
    ]);

    exit;
}


// =====================================================
// SALVAR ENDEREÇO
// =====================================================

if ($acao === "salvar_endereco") {

    $nomeEndereco =
        trim($_POST["nome_endereco"] ?? "Principal");

    $cep =
        preg_replace("/\D/", "", $_POST["cep"] ?? "");

    $estado =
        trim($_POST["estado"] ?? "");

    $cidade =
        trim($_POST["cidade"] ?? "");

    $bairro =
        trim($_POST["bairro"] ?? "");

    $rua =
        trim($_POST["rua"] ?? "");

    $numero =
        trim($_POST["numero"] ?? "");

    $complemento =
        trim($_POST["complemento"] ?? "");

    $referencia =
        trim($_POST["referencia"] ?? "");


    if (
        strlen($cep) !== 8 ||
        $estado === "" ||
        $cidade === "" ||
        $bairro === "" ||
        $rua === "" ||
        $numero === ""
    ) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha corretamente o endereço."
        ]);
        exit;
    }


    // VERIFICAR SE JÁ EXISTE ENDEREÇO
    $sql = "
        SELECT id_endereco
        FROM enderecos
        WHERE id_usuario = ?
        ORDER BY principal DESC, id_endereco DESC
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();


    if ($resultado->num_rows > 0) {

        // ATUALIZAR
        $endereco =
            $resultado->fetch_assoc();

        $id_endereco =
            (int) $endereco["id_endereco"];

        $stmt->close();

        $sql = "
            UPDATE enderecos
            SET
                nome_endereco = ?,
                cep = ?,
                estado = ?,
                cidade = ?,
                bairro = ?,
                rua = ?,
                numero = ?,
                complemento = ?,
                referencia = ?,
                principal = 1
            WHERE id_endereco = ?
            AND id_usuario = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssssssssii",
            $nomeEndereco,
            $cep,
            $estado,
            $cidade,
            $bairro,
            $rua,
            $numero,
            $complemento,
            $referencia,
            $id_endereco,
            $id_usuario
        );

    } else {

        // CRIAR PRIMEIRO ENDEREÇO
        $stmt->close();

        $sql = "
            INSERT INTO enderecos
            (
                id_usuario,
                nome_endereco,
                cep,
                estado,
                cidade,
                bairro,
                rua,
                numero,
                complemento,
                referencia,
                principal
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "isssssssss",
            $id_usuario,
            $nomeEndereco,
            $cep,
            $estado,
            $cidade,
            $bairro,
            $rua,
            $numero,
            $complemento,
            $referencia
        );
    }


    if (!$stmt->execute()) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível salvar o endereço."
        ]);
        exit;
    }

    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Endereço salvo com sucesso."
    ]);

    exit;
}

// =====================================================
// ALTERAR SENHA
// =====================================================

if ($acao === "alterar_senha") {

    $senhaAtual =
        $_POST["senha_atual"] ?? "";

    $senhaNova =
        $_POST["senha_nova"] ?? "";


    if (
        $senhaAtual === "" ||
        $senhaNova === ""
    ) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Informe a senha atual e a nova senha."
        ]);

        exit;
    }


    // BUSCAR A SENHA SEGURA NO MYSQL
    $sql = "
        SELECT senha_segura
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ";

    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Erro ao verificar a senha."
        ]);

        exit;
    }


    $stmt->bind_param(
        "i",
        $id_usuario
    );


    $stmt->execute();

    $resultado =
        $stmt->get_result();


    if ($resultado->num_rows === 0) {

        $stmt->close();

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Usuário não encontrado."
        ]);

        exit;
    }


    $usuario =
        $resultado->fetch_assoc();

    $stmt->close();


    // VERIFICAR SENHA ATUAL
    if (
        !password_verify(
            $senhaAtual,
            $usuario["senha_segura"]
        )
    ) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Senha atual incorreta."
        ]);

        exit;
    }


    // CRIAR HASH DA NOVA SENHA
    $novaSenhaSegura =
        password_hash(
            $senhaNova,
            PASSWORD_DEFAULT
        );


    // ATUALIZAR NO MYSQL
    $sql = "
        UPDATE usuarios
        SET senha_segura = ?
        WHERE id_usuario = ?
    ";

    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Erro ao preparar alteração da senha."
        ]);

        exit;
    }


    $stmt->bind_param(
        "si",
        $novaSenhaSegura,
        $id_usuario
    );


    if (!$stmt->execute()) {

        $stmt->close();

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Não foi possível alterar a senha."
        ]);

        exit;
    }


    $stmt->close();


    echo json_encode([
        "sucesso" => true,
        "mensagem" =>
            "Senha alterada com sucesso."
    ]);

    exit;
}

// =====================================================
// AÇÃO INVÁLIDA
// =====================================================

echo json_encode([
    "sucesso" => false,
    "mensagem" => "Ação inválida."
]);

?>