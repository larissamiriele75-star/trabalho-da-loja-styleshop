<?php

session_start();

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");


// =====================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// =====================================================

if (!isset($_SESSION["id_usuario"])) {

    echo json_encode([
        "sucesso" => false,
        "login" => false,
        "mensagem" => "Você precisa estar logado."
    ]);

    exit;
}


$id_usuario = (int) $_SESSION["id_usuario"];


// =====================================================
// RECEBER AÇÃO
// =====================================================

$acao = $_POST["acao"] ?? $_GET["acao"] ?? "";


// =====================================================
// FUNÇÃO: PEGAR OU CRIAR O CARRINHO DO USUÁRIO
// =====================================================

function pegarCarrinho($conexao, $id_usuario)
{
    // Primeiro procura se o usuário já possui carrinho

    $sql = "
        SELECT id_carrinho
        FROM carrinhos
        WHERE id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "i",
        $id_usuario
    );

    $stmt->execute();

    $resultado = $stmt->get_result();


    // Se encontrou, retorna o carrinho existente

    if ($resultado->num_rows > 0) {

        $carrinho = $resultado->fetch_assoc();

        $id_carrinho =
            (int) $carrinho["id_carrinho"];

        $stmt->close();

        return $id_carrinho;
    }


    $stmt->close();


    // Se não encontrou, cria um carrinho novo

    $sql = "
        INSERT INTO carrinhos
        (id_usuario)
        VALUES (?)
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "i",
        $id_usuario
    );

    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }

    $id_carrinho =
        (int) $stmt->insert_id;

    $stmt->close();

    return $id_carrinho;
}


// =====================================================
// PEGAR O CARRINHO
// =====================================================

$id_carrinho = pegarCarrinho(
    $conexao,
    $id_usuario
);


if ($id_carrinho <= 0) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível localizar o carrinho."
    ]);

    exit;
}


// =====================================================
// ADICIONAR PRODUTO
// =====================================================

if ($acao === "adicionar") {

    $id_produto =
        (int) ($_POST["id_produto"] ?? 0);


    if ($id_produto <= 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto inválido."
        ]);

        exit;
    }


    // -------------------------------------------------
    // BUSCAR PRODUTO E VERIFICAR ESTOQUE
    // -------------------------------------------------

    $sql = "
        SELECT estoque_produto
        FROM produtos
        WHERE id_produto = ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao consultar produto."
        ]);

        exit;
    }


    $stmt->bind_param(
        "i",
        $id_produto
    );

    $stmt->execute();

    $resultado = $stmt->get_result();


    if ($resultado->num_rows === 0) {

        $stmt->close();

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto não encontrado."
        ]);

        exit;
    }


    $produto =
        $resultado->fetch_assoc();

    $estoque =
        (int) $produto["estoque_produto"];

    $stmt->close();


    if ($estoque <= 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto esgotado."
        ]);

        exit;
    }


    // -------------------------------------------------
    // VERIFICAR SE O PRODUTO JÁ ESTÁ NO CARRINHO
    // -------------------------------------------------

    $sql = "
        SELECT
            id_item_carrinho,
            quantidade

        FROM itens_carrinho

        WHERE id_carrinho = ?
        AND id_produto = ?

        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao consultar o carrinho."
        ]);

        exit;
    }


    $stmt->bind_param(
        "ii",
        $id_carrinho,
        $id_produto
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();


    // -------------------------------------------------
    // SE O PRODUTO JÁ EXISTE, AUMENTA A QUANTIDADE
    // -------------------------------------------------

    if ($resultado->num_rows > 0) {

        $item =
            $resultado->fetch_assoc();

        $quantidadeAtual =
            (int) $item["quantidade"];

        $stmt->close();


        if ($quantidadeAtual >= $estoque) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" =>
                    "Quantidade máxima disponível em estoque atingida."
            ]);

            exit;
        }


        $novaQuantidade =
            $quantidadeAtual + 1;


        $sql = "
            UPDATE itens_carrinho

            SET quantidade = ?

            WHERE id_carrinho = ?
            AND id_produto = ?
        ";

        $stmt =
            $conexao->prepare($sql);


        if (!$stmt) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao atualizar o carrinho."
            ]);

            exit;
        }


        $stmt->bind_param(
            "iii",
            $novaQuantidade,
            $id_carrinho,
            $id_produto
        );


        if (!$stmt->execute()) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível atualizar o produto."
            ]);

            exit;
        }


        $stmt->close();

    } else {

        // -------------------------------------------------
        // SE NÃO EXISTE, INSERE O PRODUTO
        // -------------------------------------------------

        $stmt->close();

        $quantidade = 1;


        $sql = "
            INSERT INTO itens_carrinho
            (
                id_carrinho,
                id_produto,
                quantidade
            )

            VALUES (?, ?, ?)
        ";

        $stmt =
            $conexao->prepare($sql);


        if (!$stmt) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao adicionar produto."
            ]);

            exit;
        }


        $stmt->bind_param(
            "iii",
            $id_carrinho,
            $id_produto,
            $quantidade
        );


        if (!$stmt->execute()) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível adicionar o produto."
            ]);

            exit;
        }


        $stmt->close();
    }


    echo json_encode([
        "sucesso" => true,
        "mensagem" =>
            "Produto adicionado ao carrinho."
    ]);

    exit;
}


// =====================================================
// DIMINUIR QUANTIDADE
// =====================================================

if ($acao === "diminuir") {

    $id_produto =
        (int) ($_POST["id_produto"] ?? 0);


    if ($id_produto <= 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto inválido."
        ]);

        exit;
    }


    $sql = "
        SELECT quantidade

        FROM itens_carrinho

        WHERE id_carrinho = ?
        AND id_produto = ?

        LIMIT 1
    ";

    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao consultar o carrinho."
        ]);

        exit;
    }


    $stmt->bind_param(
        "ii",
        $id_carrinho,
        $id_produto
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();


    if ($resultado->num_rows === 0) {

        $stmt->close();

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Produto não está no carrinho."
        ]);

        exit;
    }


    $item =
        $resultado->fetch_assoc();

    $quantidade =
        (int) $item["quantidade"];

    $stmt->close();


    // -------------------------------------------------
    // SE TEM APENAS 1, REMOVE O ITEM
    // -------------------------------------------------

    if ($quantidade <= 1) {

        $sql = "
            DELETE FROM itens_carrinho

            WHERE id_carrinho = ?
            AND id_produto = ?
        ";

        $stmt =
            $conexao->prepare($sql);

        $stmt->bind_param(
            "ii",
            $id_carrinho,
            $id_produto
        );

        $stmt->execute();

        $stmt->close();

    } else {

        // -------------------------------------------------
        // SENÃO DIMINUI UMA UNIDADE
        // -------------------------------------------------

        $novaQuantidade =
            $quantidade - 1;


        $sql = "
            UPDATE itens_carrinho

            SET quantidade = ?

            WHERE id_carrinho = ?
            AND id_produto = ?
        ";

        $stmt =
            $conexao->prepare($sql);

        $stmt->bind_param(
            "iii",
            $novaQuantidade,
            $id_carrinho,
            $id_produto
        );

        $stmt->execute();

        $stmt->close();
    }


    echo json_encode([
        "sucesso" => true,
        "mensagem" =>
            "Carrinho atualizado."
    ]);

    exit;
}


// =====================================================
// REMOVER PRODUTO COMPLETAMENTE
// =====================================================

if ($acao === "remover") {

    $id_produto =
        (int) ($_POST["id_produto"] ?? 0);


    if ($id_produto <= 0) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto inválido."
        ]);

        exit;
    }


    $sql = "
        DELETE FROM itens_carrinho

        WHERE id_carrinho = ?
        AND id_produto = ?
    ";

    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Erro ao remover produto."
        ]);

        exit;
    }


    $stmt->bind_param(
        "ii",
        $id_carrinho,
        $id_produto
    );


    if (!$stmt->execute()) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Não foi possível remover o produto."
        ]);

        exit;
    }


    $stmt->close();


    echo json_encode([
        "sucesso" => true,
        "mensagem" =>
            "Produto removido do carrinho."
    ]);

    exit;
}


// =====================================================
// LISTAR PRODUTOS DO CARRINHO
// =====================================================

if ($acao === "listar") {

    $sql = "
        SELECT

            p.id_produto,
            p.nome_produto,
            p.preco_produto,
            p.img_produto,
            p.estoque_produto,

            ic.quantidade

        FROM itens_carrinho ic

        INNER JOIN produtos p
            ON p.id_produto = ic.id_produto

        WHERE ic.id_carrinho = ?

        ORDER BY ic.id_item_carrinho DESC
    ";

    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Erro ao carregar o carrinho."
        ]);

        exit;
    }


    $stmt->bind_param(
        "i",
        $id_carrinho
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();


    $itens = [];


    while ($item =
        $resultado->fetch_assoc()) {

        $itens[] = [

            "id" =>
                (int) $item["id_produto"],

            "nome" =>
                $item["nome_produto"],

            "preco" =>
                (float) $item["preco_produto"],

            "img" =>
                "../src/assets/img/" .
                $item["img_produto"],

            "estoque" =>
                (int) $item["estoque_produto"],

            "qtd" =>
                (int) $item["quantidade"]
        ];
    }


    $stmt->close();


    echo json_encode([
        "sucesso" => true,
        "itens" => $itens
    ]);

    exit;
}

// =====================================================
// LIMPAR CARRINHO APÓS FINALIZAR PEDIDO
// =====================================================

if ($acao === "limpar") {

    $sql = "
        DELETE FROM itens_carrinho
        WHERE id_carrinho = ?
    ";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao preparar a limpeza do carrinho."
        ]);

        exit;
    }

    $stmt->bind_param(
        "i",
        $id_carrinho
    );

    if (!$stmt->execute()) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível limpar o carrinho."
        ]);

        $stmt->close();

        exit;
    }

    $stmt->close();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Carrinho limpo com sucesso."
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