<?php

session_start();

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");


// =====================================================
// VERIFICAR LOGIN
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
// FINALIZAR PEDIDO
// =====================================================

if ($acao === "finalizar") {

    try {

        $conexao->begin_transaction();


        // =================================================
        // 1. LOCALIZAR ENDEREÇO DO USUÁRIO
        // =================================================

        $sql = "
            SELECT id_endereco
            FROM enderecos
            WHERE id_usuario = ?
            ORDER BY principal DESC, id_endereco DESC
            LIMIT 1
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "Erro ao preparar consulta do endereço."
            );
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

            throw new Exception(
                "Você precisa cadastrar um endereço antes de finalizar a compra."
            );
        }

        $endereco =
            $resultado->fetch_assoc();

        $id_endereco =
            (int) $endereco["id_endereco"];

        $stmt->close();


        // =================================================
        // 2. LOCALIZAR CARRINHO
        // =================================================

        $sql = "
            SELECT id_carrinho
            FROM carrinhos
            WHERE id_usuario = ?
            LIMIT 1
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "Erro ao localizar carrinho."
            );
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

            throw new Exception(
                "Carrinho não encontrado."
            );
        }

        $carrinho =
            $resultado->fetch_assoc();

        $id_carrinho =
            (int) $carrinho["id_carrinho"];

        $stmt->close();


        // =================================================
        // 3. PEGAR ITENS E PREÇOS DIRETAMENTE DO MYSQL
        // =================================================

        $sql = "
            SELECT
                ic.id_produto,
                ic.quantidade,
                p.preco_produto,
                p.estoque_produto

            FROM itens_carrinho ic

            INNER JOIN produtos p
                ON p.id_produto = ic.id_produto

            WHERE ic.id_carrinho = ?

            FOR UPDATE
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                "Erro ao consultar produtos do carrinho."
            );
        }

        $stmt->bind_param(
            "i",
            $id_carrinho
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();

        if ($resultado->num_rows === 0) {

            $stmt->close();

            throw new Exception(
                "Seu carrinho está vazio."
            );
        }


        // =================================================
        // 4. CALCULAR TOTAL PELO MYSQL
        // =================================================

        $itens = [];

        $subtotalPedido = 0;

        while (
            $item =
            $resultado->fetch_assoc()
        ) {

            $id_produto =
                (int) $item["id_produto"];

            $quantidade =
                (int) $item["quantidade"];

            $estoque =
                (int) $item["estoque_produto"];

            $preco =
                (float) $item["preco_produto"];


            if ($quantidade <= 0) {

                throw new Exception(
                    "Quantidade inválida no carrinho."
                );
            }


            if ($quantidade > $estoque) {

                throw new Exception(
                    "Um dos produtos não possui estoque suficiente."
                );
            }


            $subtotalItem =
                $preco * $quantidade;


            $subtotalPedido +=
                $subtotalItem;


            $itens[] = [

                "id_produto" =>
                    $id_produto,

                "quantidade" =>
                    $quantidade,

                "preco" =>
                    $preco,

                "subtotal" =>
                    $subtotalItem
            ];
        }

        $stmt->close();


        // =================================================
        // 5. VALORES DO PEDIDO
        // =================================================

        $frete = 0.00;

        $desconto = 0.00;

        $total =
            $subtotalPedido
            + $frete
            - $desconto;

        $status =
            "aguardando_pagamento";


        // =================================================
        // 6. CRIAR PEDIDO
        // =================================================

        $sql = "
            INSERT INTO pedidos
            (
                id_usuario,
                id_endereco,
                status_pedido,
                subtotal,
                frete,
                desconto,
                total
            )

            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar criação do pedido."
            );
        }


        $stmt->bind_param(
            "iisdddd",
            $id_usuario,
            $id_endereco,
            $status,
            $subtotalPedido,
            $frete,
            $desconto,
            $total
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Não foi possível criar o pedido."
            );
        }


        $id_pedido =
            (int) $stmt->insert_id;

        $stmt->close();


        // =================================================
        // 7. SALVAR ITENS DO PEDIDO
        // =================================================

        $sql = "
            INSERT INTO itens_pedido
            (
                id_pedido,
                id_produto,
                quantidade,
                preco_unitario,
                subtotal
            )

            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar os itens do pedido."
            );
        }


        foreach ($itens as $item) {

            $id_produto =
                $item["id_produto"];

            $quantidade =
                $item["quantidade"];

            $preco =
                $item["preco"];

            $subtotalItem =
                $item["subtotal"];


            $stmt->bind_param(
                "iiidd",
                $id_pedido,
                $id_produto,
                $quantidade,
                $preco,
                $subtotalItem
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    "Não foi possível salvar um produto do pedido."
                );
            }
        }

        $stmt->close();


        // =================================================
        // 8. DIMINUIR ESTOQUE
        // =================================================

        $sql = "
            UPDATE produtos

            SET estoque_produto =
                estoque_produto - ?

            WHERE id_produto = ?
            AND estoque_produto >= ?
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar atualização do estoque."
            );
        }


        foreach ($itens as $item) {

            $quantidade =
                $item["quantidade"];

            $id_produto =
                $item["id_produto"];


            $stmt->bind_param(
                "iii",
                $quantidade,
                $id_produto,
                $quantidade
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    "Erro ao atualizar estoque."
                );
            }


            if ($stmt->affected_rows !== 1) {

                throw new Exception(
                    "O estoque de um produto mudou durante a compra."
                );
            }
        }

        $stmt->close();


        // =================================================
        // 9. LIMPAR CARRINHO
        // =================================================

        $sql = "
            DELETE FROM itens_carrinho
            WHERE id_carrinho = ?
        ";

        $stmt =
            $conexao->prepare($sql);

        if (!$stmt) {

            throw new Exception(
                "Erro ao preparar limpeza do carrinho."
            );
        }

        $stmt->bind_param(
            "i",
            $id_carrinho
        );

        if (!$stmt->execute()) {

            throw new Exception(
                "Não foi possível limpar o carrinho."
            );
        }

        $stmt->close();


        // =================================================
        // 10. CONFIRMAR TODAS AS ALTERAÇÕES
        // =================================================

        $conexao->commit();


        echo json_encode([

            "sucesso" => true,

            "mensagem" =>
                "Pedido realizado com sucesso!",

            "id_pedido" =>
                $id_pedido,

            "total" =>
                $total
        ]);

        exit;


    } catch (Throwable $erro) {

        $conexao->rollback();


        echo json_encode([

            "sucesso" => false,

            "mensagem" =>
                $erro->getMessage()
        ]);

        exit;
    }
}


// =====================================================
// LISTAR PEDIDOS DO USUÁRIO
// =====================================================

if ($acao === "listar") {

    $sql = "
        SELECT
            id_pedido,
            status_pedido,
            subtotal,
            frete,
            desconto,
            total,
            criado_em

        FROM pedidos

       WHERE id_usuario = ?
AND status_pedido <> 'cancelado'

ORDER BY id_pedido DESC
    ";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" =>
                "Erro ao carregar pedidos."
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


    $pedidos = [];


    while (
        $pedido =
        $resultado->fetch_assoc()
    ) {

        $id_pedido =
            (int) $pedido["id_pedido"];


        // PEGAR PRODUTOS DO PEDIDO

        $sqlItens = "
            SELECT
                ip.id_produto,
                ip.quantidade,
                ip.preco_unitario,
                ip.subtotal,
                p.nome_produto,
                p.img_produto

            FROM itens_pedido ip

            INNER JOIN produtos p
                ON p.id_produto =
                   ip.id_produto

            WHERE ip.id_pedido = ?
        ";


        $stmtItens =
            $conexao->prepare(
                $sqlItens
            );

        $stmtItens->bind_param(
            "i",
            $id_pedido
        );

        $stmtItens->execute();

        $resultadoItens =
            $stmtItens->get_result();


        $itens = [];


        while (
            $item =
            $resultadoItens
                ->fetch_assoc()
        ) {

            $itens[] = [

                "id" =>
                    (int)
                    $item["id_produto"],

                "nome" =>
                    $item["nome_produto"],

                "qtd" =>
                    (int)
                    $item["quantidade"],

                "preco" =>
                    (float)
                    $item["preco_unitario"],

                "subtotal" =>
                    (float)
                    $item["subtotal"],

                "img" =>
                    "../src/assets/img/" .
                    $item["img_produto"]
            ];
        }


        $stmtItens->close();


        $pedidos[] = [

            "id" =>
                $id_pedido,

            "status" =>
                $pedido["status_pedido"],

            "subtotal" =>
                (float)
                $pedido["subtotal"],

            "frete" =>
                (float)
                $pedido["frete"],

            "desconto" =>
                (float)
                $pedido["desconto"],

            "total" =>
                (float)
                $pedido["total"],

            "data" =>
                $pedido["criado_em"],

            "itens" =>
                $itens
        ];
    }


    $stmt->close();


    echo json_encode([

        "sucesso" => true,

        "pedidos" =>
            $pedidos
    ]);

    exit;
}


// =====================================================
// CANCELAR PEDIDO
// =====================================================

if ($acao === "cancelar") {

    $id_pedido = (int) ($_POST["id_pedido"] ?? 0);

    if ($id_pedido <= 0) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Pedido inválido."
        ]);
        exit;
    }

    try {

        $conexao->begin_transaction();

        // 1. LOCALIZAR E BLOQUEAR O PEDIDO
        $sql = "
            SELECT id_pedido, status_pedido
            FROM pedidos
            WHERE id_pedido = ?
              AND id_usuario = ?
            LIMIT 1
            FOR UPDATE
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao localizar o pedido.");
        }

        $stmt->bind_param("ii", $id_pedido, $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            $stmt->close();
            throw new Exception("Pedido não encontrado.");
        }

        $pedido = $resultado->fetch_assoc();
        $stmt->close();

        $statusAtual = $pedido["status_pedido"];

        if (
            $statusAtual !== "aguardando" &&
            $statusAtual !== "aguardando_pagamento"
        ) {
            throw new Exception("Este pedido não pode mais ser cancelado.");
        }

        // 2. BUSCAR OS ITENS DO PEDIDO
        $sql = "
            SELECT id_produto, quantidade
            FROM itens_pedido
            WHERE id_pedido = ?
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao consultar os itens do pedido.");
        }

        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $itens = [];

        while ($item = $resultado->fetch_assoc()) {
            $itens[] = [
                "id_produto" => (int) $item["id_produto"],
                "quantidade" => (int) $item["quantidade"]
            ];
        }

        $stmt->close();

        // 3. DEVOLVER OS ITENS AO ESTOQUE
        $sql = "
            UPDATE produtos
            SET estoque_produto = estoque_produto + ?
            WHERE id_produto = ?
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao preparar devolução do estoque.");
        }

        foreach ($itens as $item) {

            $quantidade = $item["quantidade"];
            $id_produto = $item["id_produto"];

            $stmt->bind_param(
                "ii",
                $quantidade,
                $id_produto
            );

            if (!$stmt->execute()) {
                throw new Exception("Não foi possível devolver o produto ao estoque.");
            }
        }

        $stmt->close();

        // 4. MARCAR O PEDIDO COMO CANCELADO
        $sql = "
            UPDATE pedidos
            SET status_pedido = 'cancelado'
            WHERE id_pedido = ?
              AND id_usuario = ?
        ";

        $stmt = $conexao->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao preparar cancelamento.");
        }

        $stmt->bind_param(
            "ii",
            $id_pedido,
            $id_usuario
        );

        if (!$stmt->execute()) {
            throw new Exception("Não foi possível cancelar o pedido.");
        }

        $stmt->close();

        $conexao->commit();

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Pedido cancelado com sucesso."
        ]);

        exit;

    } catch (Throwable $erro) {

        $conexao->rollback();

        echo json_encode([
            "sucesso" => false,
            "mensagem" => $erro->getMessage()
        ]);

        exit;
    }
}


// =====================================================
// AÇÃO INVÁLIDA
// =====================================================

echo json_encode([

    "sucesso" => false,

    "mensagem" =>
        "Ação inválida."
]);

?>