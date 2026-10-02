<?php

session_start();

require_once "../src/php/conexao.php";

/*
|--------------------------------------------------------------------------
| STYLES HOP - PAINEL ADMINISTRATIVO
|--------------------------------------------------------------------------
| Este arquivo substitui:
| - admin.php
| - admin_usuarios.php
|
| O painel utiliza as tabelas existentes no banco StyleShop.
|--------------------------------------------------------------------------
*/


/* =========================================================
   CONFIGURAÇÕES
========================================================= */

$lojaNome = "StyleShop";

$urlLoja = "../index.php";


/* =========================================================
   VERIFICAR LOGIN
========================================================= */

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../index.php");
    exit;
}


/* =========================================================
   VERIFICAR ADMINISTRADOR
========================================================= */

if (($_SESSION["tipo_usuario"] ?? "") !== "admin") {

    http_response_code(403);
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0">

        <title>Acesso negado - StyleShop</title>

        <script src="https://cdn.tailwindcss.com"></script>

    </head>

    <body class="bg-gray-100 min-h-screen flex items-center justify-center">

        <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full mx-4 text-center">

            <div class="text-6xl mb-5">
                🚫
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-3">
                Acesso negado
            </h1>

            <p class="text-gray-600 mb-6">
                Você não possui permissão para acessar a área administrativa.
            </p>

            <a
                href="../index.php"
                class="inline-block bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-xl font-semibold transition">

                ← Voltar para a loja

            </a>

        </div>

    </body>

    </html>

    <?php

    exit;
}


/* =========================================================
   PROTEÇÃO CSRF
========================================================= */

if (empty($_SESSION["admin_csrf"])) {

    $_SESSION["admin_csrf"] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION["admin_csrf"];


/* =========================================================
   FUNÇÕES AUXILIARES
========================================================= */

function e($valor)
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}


function dinheiro($valor)
{
    return "R$ " . number_format(
        (float)$valor,
        2,
        ",",
        "."
    );
}


function cargoNome($cargo)
{
    $nomes = [

        "admin"     => "Administrador",
        "developer" => "Desenvolvedor",
        "support"   => "Suporte",
        "moderator" => "Moderador",
        "manager"   => "Gerente",
        "financial" => "Financeiro",
        "logistics" => "Logística",
        "customer"  => "Cliente"

    ];

    return $nomes[$cargo] ?? $cargo;
}


function statusPedidoNome($status)
{
    $nomes = [

        "aguardando_pagamento" => "Aguardando pagamento",
        "pago"                 => "Pago",
        "processando"          => "Processando",
        "enviado"              => "Enviado",
        "entregue"             => "Entregue",
        "cancelado"            => "Cancelado"

    ];

    return $nomes[$status] ?? $status;
}


function statusPagamentoNome($status)
{
    $nomes = [

        "pendente"   => "Pendente",
        "aprovado"   => "Aprovado",
        "recusado"   => "Recusado",
        "cancelado"  => "Cancelado",
        "estornado"  => "Estornado"

    ];

    return $nomes[$status] ?? $status;
}


function metodoPagamentoNome($metodo)
{
    $nomes = [

        "pix"            => "PIX",
        "cartao_credito" => "Cartão de crédito",
        "cartao_debito"  => "Cartão de débito",
        "boleto"         => "Boleto"

    ];

    return $nomes[$metodo] ?? $metodo;
}


/* =========================================================
   SEÇÃO ATUAL
========================================================= */

$secao = $_GET["secao"] ?? "dashboard";

$secoesPermitidas = [

    "dashboard",
    "usuarios",
    "produtos",
    "pedidos",
    "categorias",
    "cupons",
    "clientes",
    "relatorios",
    "configuracoes"

];

if (!in_array($secao, $secoesPermitidas, true)) {

    $secao = "dashboard";
}


/* =========================================================
   MENSAGENS
========================================================= */

$mensagem = "";
$erro = "";


/* =========================================================
   PROCESSAR AÇÕES
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tokenRecebido = $_POST["csrf_token"] ?? "";

    if (!hash_equals($csrf, $tokenRecebido)) {

        $erro = "A sessão de segurança expirou. Atualize a página e tente novamente.";

    } else {

        $acao = $_POST["acao"] ?? "";


        /* =================================================
           ALTERAR CARGO DO USUÁRIO
        ================================================= */

        if ($acao === "alterar_cargo") {

            $idUsuario = (int)($_POST["id_usuario"] ?? 0);

            $novoCargo = $_POST["tipo_usuario"] ?? "";

            $cargosPermitidos = [

                "admin",
                "developer",
                "support",
                "moderator",
                "manager",
                "financial",
                "logistics",
                "customer"

            ];

            if ($idUsuario <= 0 || !in_array($novoCargo, $cargosPermitidos, true)) {

                $erro = "Dados inválidos para alteração do cargo.";

            } elseif ($idUsuario === (int)$_SESSION["id_usuario"] && $novoCargo !== "admin") {

                $erro = "Você não pode retirar o próprio cargo de administrador.";

            } else {

                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET tipo_usuario = ?
                     WHERE id_usuario = ?"
                );

                $stmt->bind_param(
                    "si",
                    $novoCargo,
                    $idUsuario
                );

                if ($stmt->execute()) {

                    $mensagem = "Cargo do usuário atualizado com sucesso.";

                } else {

                    $erro = "Não foi possível alterar o cargo.";
                }

                $stmt->close();
            }

            $secao = "usuarios";
        }


        /* =================================================
           ATUALIZAR PRODUTO
        ================================================= */

        elseif ($acao === "atualizar_produto") {

            $idProduto = (int)($_POST["id_produto"] ?? 0);

            $preco = (float)($_POST["preco_produto"] ?? 0);

            $estoque = (int)($_POST["estoque_produto"] ?? 0);

            $destaque = isset($_POST["destaque_produto"]) ? 1 : 0;

            if ($idProduto <= 0 || $preco < 0 || $estoque < 0) {

                $erro = "Dados inválidos para atualizar o produto.";

            } else {

                $stmt = $conexao->prepare(
                    "UPDATE produtos
                     SET preco_produto = ?,
                         estoque_produto = ?,
                         destaque_produto = ?
                     WHERE id_produto = ?"
                );

                $stmt->bind_param(
                    "diii",
                    $preco,
                    $estoque,
                    $destaque,
                    $idProduto
                );

                if ($stmt->execute()) {

                    $mensagem = "Produto atualizado com sucesso.";

                } else {

                    $erro = "Não foi possível atualizar o produto.";
                }

                $stmt->close();
            }

            $secao = "produtos";
        }


        /* =================================================
           EXCLUIR PRODUTO
        ================================================= */

        elseif ($acao === "excluir_produto") {

            $idProduto = (int)($_POST["id_produto"] ?? 0);

            if ($idProduto <= 0) {

                $erro = "Produto inválido.";

            } else {

                /*
                 * O banco possui FK em itens_pedido e itens_carrinho.
                 * Por isso verificamos antes se o produto já foi utilizado.
                 */

                $stmt = $conexao->prepare(
                    "SELECT
                        (SELECT COUNT(*)
                         FROM itens_pedido
                         WHERE id_produto = ?) +
                        (SELECT COUNT(*)
                         FROM itens_carrinho
                         WHERE id_produto = ?) AS total"
                );

                $stmt->bind_param(
                    "ii",
                    $idProduto,
                    $idProduto
                );

                $stmt->execute();

                $resultadoVerificacao = $stmt->get_result();

                $dadosVerificacao = $resultadoVerificacao->fetch_assoc();

                $stmt->close();


                if ((int)$dadosVerificacao["total"] > 0) {

                    $erro = "Este produto já possui registros de pedidos ou carrinhos e não pode ser excluído.";

                } else {

                    $stmt = $conexao->prepare(
                        "DELETE FROM produtos
                         WHERE id_produto = ?"
                    );

                    $stmt->bind_param(
                        "i",
                        $idProduto
                    );

                    if ($stmt->execute()) {

                        $mensagem = "Produto excluído com sucesso.";

                    } else {

                        $erro = "Não foi possível excluir o produto.";
                    }

                    $stmt->close();
                }
            }

            $secao = "produtos";
        }


        /* =================================================
           CADASTRAR PRODUTO
        ================================================= */

        elseif ($acao === "cadastrar_produto") {

            $nome = trim($_POST["nome_produto"] ?? "");

            $colecao = trim($_POST["colecao_produto"] ?? "");

            $descricao = trim($_POST["descricao_produto"] ?? "");

            $tamanho = trim($_POST["tamanho_produto"] ?? "");

            $preco = (float)($_POST["preco_produto"] ?? 0);

            $estoque = (int)($_POST["estoque_produto"] ?? 0);

            $cor = trim($_POST["cor_produto"] ?? "");

            $imagem = trim($_POST["img_produto"] ?? "");

            $avaliacao = (float)($_POST["avaliacao_produto"] ?? 0);

            $marca = trim($_POST["marca_produto"] ?? "StyleShop");

            $destaque = isset($_POST["destaque_produto"]) ? 1 : 0;


            if (
                $nome === "" ||
                $colecao === "" ||
                $tamanho === "" ||
                $cor === "" ||
                $imagem === ""
            ) {

                $erro = "Preencha os campos obrigatórios do produto.";

            } elseif ($preco < 0 || $estoque < 0) {

                $erro = "Preço e estoque não podem ser negativos.";

            } elseif ($avaliacao < 0 || $avaliacao > 5) {

                $erro = "A avaliação deve estar entre 0 e 5.";

            } else {

                $stmt = $conexao->prepare(
                    "INSERT INTO produtos
                    (
                        img_produto,
                        colecao_produto,
                        avaliacao_produto,
                        nome_produto,
                        descricao_produto,
                        tamanho_produto,
                        preco_produto,
                        estoque_produto,
                        cor_produto,
                        destaque_produto,
                        marca_produto
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssdsssdisss",
                    $imagem,
                    $colecao,
                    $avaliacao,
                    $nome,
                    $descricao,
                    $tamanho,
                    $preco,
                    $estoque,
                    $cor,
                    $destaque,
                    $marca
                );

                /*
                 * A string acima corresponde aos tipos:
                 * s = imagem
                 * s = coleção
                 * d = avaliação
                 * s = nome
                 * s = descrição
                 * s = tamanho
                 * d = preço
                 * i = estoque
                 * s = cor
                 * i = destaque
                 * s = marca
                 */

                if ($stmt->execute()) {

                    $mensagem = "Produto cadastrado com sucesso.";

                } else {

                    $erro = "Não foi possível cadastrar o produto.";
                }

                $stmt->close();
            }

            $secao = "produtos";
        }


        /* =================================================
           ALTERAR STATUS DO PEDIDO
        ================================================= */

        elseif ($acao === "alterar_status_pedido") {

            $idPedido = (int)($_POST["id_pedido"] ?? 0);

            $novoStatus = $_POST["status_pedido"] ?? "";

            $statusPermitidos = [

                "aguardando_pagamento",
                "pago",
                "processando",
                "enviado",
                "entregue",
                "cancelado"

            ];

            if (
                $idPedido <= 0 ||
                !in_array($novoStatus, $statusPermitidos, true)
            ) {

                $erro = "Status de pedido inválido.";

            } else {

                $stmt = $conexao->prepare(
                    "UPDATE pedidos
                     SET status_pedido = ?
                     WHERE id_pedido = ?"
                );

                $stmt->bind_param(
                    "si",
                    $novoStatus,
                    $idPedido
                );

                if ($stmt->execute()) {

                    $mensagem = "Status do pedido atualizado.";

                } else {

                    $erro = "Não foi possível atualizar o pedido.";
                }

                $stmt->close();
            }

            $secao = "pedidos";
        }
    }
}


/* =========================================================
   ESTATÍSTICAS DO DASHBOARD
========================================================= */

$totalUsuarios = 0;
$totalProdutos = 0;
$totalPedidos = 0;
$totalClientes = 0;
$valorVendas = 0;
$produtosEstoqueBaixo = 0;


$resultado = $conexao->query(
    "SELECT COUNT(*) AS total
     FROM usuarios"
);

if ($resultado) {

    $totalUsuarios = (int)$resultado->fetch_assoc()["total"];
}


$resultado = $conexao->query(
    "SELECT COUNT(*) AS total
     FROM produtos"
);

if ($resultado) {

    $totalProdutos = (int)$resultado->fetch_assoc()["total"];
}


$resultado = $conexao->query(
    "SELECT COUNT(*) AS total
     FROM pedidos"
);

if ($resultado) {

    $totalPedidos = (int)$resultado->fetch_assoc()["total"];
}


$resultado = $conexao->query(
    "SELECT COUNT(*) AS total
     FROM usuarios
     WHERE tipo_usuario = 'customer'"
);

if ($resultado) {

    $totalClientes = (int)$resultado->fetch_assoc()["total"];
}


$resultado = $conexao->query(
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM pedidos
     WHERE status_pedido <> 'cancelado'"
);

if ($resultado) {

    $valorVendas = (float)$resultado->fetch_assoc()["total"];
}


$resultado = $conexao->query(
    "SELECT COUNT(*) AS total
     FROM produtos
     WHERE estoque_produto <= 3"
);

if ($resultado) {

    $produtosEstoqueBaixo = (int)$resultado->fetch_assoc()["total"];
}


/* =========================================================
   DADOS DA SEÇÃO USUÁRIOS
========================================================= */

$usuarios = [];

if ($secao === "usuarios") {

    $resultado = $conexao->query(
        "SELECT
            id_usuario,
            nome,
            nome_social,
            email,
            telefone,
            tipo_usuario,
            cargo_id
         FROM usuarios
         ORDER BY id_usuario ASC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $usuarios[] = $linha;
        }
    }
}


/* =========================================================
   DADOS DA SEÇÃO PRODUTOS
========================================================= */

$produtos = [];

if ($secao === "produtos") {

    $resultado = $conexao->query(
        "SELECT
            id_produto,
            img_produto,
            colecao_produto,
            avaliacao_produto,
            nome_produto,
            descricao_produto,
            tamanho_produto,
            preco_produto,
            estoque_produto,
            cor_produto,
            destaque_produto,
            marca_produto
         FROM produtos
         ORDER BY id_produto ASC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $produtos[] = $linha;
        }
    }
}


/* =========================================================
   DADOS DA SEÇÃO PEDIDOS
========================================================= */

$pedidos = [];

if ($secao === "pedidos") {

    $resultado = $conexao->query(
        "SELECT
            p.id_pedido,
            p.id_usuario,
            p.status_pedido,
            p.subtotal,
            p.frete,
            p.desconto,
            p.total,
            p.criado_em,
            u.nome,
            u.email
         FROM pedidos p
         LEFT JOIN usuarios u
            ON u.id_usuario = p.id_usuario
         ORDER BY p.id_pedido DESC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $pedidos[] = $linha;
        }
    }
}


/* =========================================================
   DADOS DA SEÇÃO CLIENTES
========================================================= */

$clientes = [];

if ($secao === "clientes") {

    $resultado = $conexao->query(
        "SELECT
            id_usuario,
            nome,
            email,
            telefone,
            nascimento,
            tipo_usuario
         FROM usuarios
         WHERE tipo_usuario = 'customer'
         ORDER BY nome ASC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $clientes[] = $linha;
        }
    }
}


/* =========================================================
   DADOS DA SEÇÃO CATEGORIAS
========================================================= */

$categorias = [];

if ($secao === "categorias") {

    $resultado = $conexao->query(
        "SELECT
            colecao_produto AS categoria,
            COUNT(*) AS quantidade
         FROM produtos
         GROUP BY colecao_produto
         ORDER BY colecao_produto ASC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $categorias[] = $linha;
        }
    }
}


/* =========================================================
   DADOS DOS RELATÓRIOS
========================================================= */

$relatorioPedidos = [];

$relatorioProdutos = [];

if ($secao === "relatorios") {

    $resultado = $conexao->query(
        "SELECT
            status_pedido,
            COUNT(*) AS quantidade,
            COALESCE(SUM(total), 0) AS valor
         FROM pedidos
         GROUP BY status_pedido
         ORDER BY quantidade DESC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $relatorioPedidos[] = $linha;
        }
    }


    $resultado = $conexao->query(
        "SELECT
            id_produto,
            nome_produto,
            estoque_produto,
            preco_produto
         FROM produtos
         WHERE estoque_produto <= 3
         ORDER BY estoque_produto ASC, nome_produto ASC"
    );

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $relatorioProdutos[] = $linha;
        }
    }
}


/* =========================================================
   NOME DO ADMIN
========================================================= */

$nomeAdmin = $_SESSION["nome"] ?? "Administrador";


/* =========================================================
   HTML
========================================================= */

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Administração - <?= e($lojaNome) ?>
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .menu-link {
            transition: all .2s ease;
        }

        .menu-link:hover {
            transform: translateX(3px);
        }

        .card {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

    </style>

</head>


<body class="bg-gray-100 min-h-screen">


<!-- =====================================================
     CABEÇALHO
====================================================== -->

<header class="bg-gray-900 text-white shadow-lg">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h1 class="text-2xl sm:text-3xl font-bold">

                    ⚙️ StyleShop

                </h1>

                <p class="text-gray-400 text-sm">

                    Painel administrativo

                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <span class="bg-pink-600 px-4 py-2 rounded-xl text-sm font-semibold">

                    👑 <?= e($nomeAdmin) ?>

                </span>

                <a
                    href="index.php"
                    class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-xl text-sm transition">

                    🛍️ Loja

                </a>
                <a
                     href="../src/php/logout.php"
                     class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl text-sm transition">

                    Sair

                </a>

            </div>

        </div>

    </div>

</header>


<!-- =====================================================
     MENU
====================================================== -->

<nav class="bg-white border-b shadow-sm">

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="flex gap-2 overflow-x-auto py-3">

            <a
                href="admin.php?secao=dashboard"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "dashboard"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                📊 Dashboard

            </a>


            <a
                href="admin.php?secao=usuarios"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "usuarios"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                👥 Usuários

            </a>


            <a
                href="admin.php?secao=produtos"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "produtos"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                🛍️ Produtos

            </a>


            <a
                href="admin.php?secao=pedidos"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "pedidos"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                📦 Pedidos

            </a>


            <a
                href="admin.php?secao=categorias"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "categorias"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                🏷️ Categorias

            </a>


            <a
                href="admin.php?secao=cupons"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "cupons"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                🎟️ Cupons

            </a>


            <a
                href="admin.php?secao=clientes"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "clientes"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                👤 Clientes

            </a>


            <a
                href="admin.php?secao=relatorios"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "relatorios"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                📈 Relatórios

            </a>


            <a
                href="admin.php?secao=configuracoes"
                class="menu-link whitespace-nowrap px-4 py-2 rounded-xl
                <?= $secao === "configuracoes"
                    ? "bg-pink-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200" ?>">

                ⚙️ Configurações

            </a>

        </div>

    </div>

</nav>


<!-- =====================================================
     CONTEÚDO
====================================================== -->

<main class="max-w-7xl mx-auto p-4 sm:p-6">


    <?php if ($mensagem !== ""): ?>

        <div class="mb-6 bg-green-100 border border-green-300 text-green-800 px-5 py-4 rounded-2xl">

            ✅ <?= e($mensagem) ?>

        </div>

    <?php endif; ?>


    <?php if ($erro !== ""): ?>

        <div class="mb-6 bg-red-100 border border-red-300 text-red-800 px-5 py-4 rounded-2xl">

            ⚠️ <?= e($erro) ?>

        </div>

    <?php endif; ?>


<!-- =====================================================
     DASHBOARD
====================================================== -->

<?php if ($secao === "dashboard"): ?>

    <div class="mb-8">

        <h2 class="text-3xl font-bold text-gray-800">

            Olá, <?= e($nomeAdmin) ?>! 👋

        </h2>

        <p class="text-gray-500 mt-2">

            Bem-vindo ao painel administrativo do StyleShop.

        </p>

    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">


        <div class="card bg-white rounded-3xl shadow p-6">

            <div class="text-4xl mb-4">
                👥
            </div>

            <p class="text-gray-500 text-sm">
                Usuários
            </p>

            <p class="text-3xl font-bold text-gray-800 mt-1">
                <?= $totalUsuarios ?>
            </p>

            <a
                href="admin.php?secao=usuarios"
                class="text-pink-600 text-sm font-semibold inline-block mt-4">

                Gerenciar →

            </a>

        </div>


        <div class="card bg-white rounded-3xl shadow p-6">

            <div class="text-4xl mb-4">
                🛍️
            </div>

            <p class="text-gray-500 text-sm">
                Produtos
            </p>

            <p class="text-3xl font-bold text-gray-800 mt-1">
                <?= $totalProdutos ?>
            </p>

            <a
                href="admin.php?secao=produtos"
                class="text-pink-600 text-sm font-semibold inline-block mt-4">

                Gerenciar →

            </a>

        </div>


        <div class="card bg-white rounded-3xl shadow p-6">

            <div class="text-4xl mb-4">
                📦
            </div>

            <p class="text-gray-500 text-sm">
                Pedidos
            </p>

            <p class="text-3xl font-bold text-gray-800 mt-1">
                <?= $totalPedidos ?>
            </p>

            <a
                href="admin.php?secao=pedidos"
                class="text-pink-600 text-sm font-semibold inline-block mt-4">

                Ver pedidos →

            </a>

        </div>


        <div class="card bg-white rounded-3xl shadow p-6">

            <div class="text-4xl mb-4">
                👤
            </div>

            <p class="text-gray-500 text-sm">
                Clientes
            </p>

            <p class="text-3xl font-bold text-gray-800 mt-1">
                <?= $totalClientes ?>
            </p>

            <a
                href="admin.php?secao=clientes"
                class="text-pink-600 text-sm font-semibold inline-block mt-4">

                Ver clientes →

            </a>

        </div>

    </div>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">


        <div class="bg-white rounded-3xl shadow p-6">

            <h3 class="text-xl font-bold text-gray-800 mb-2">

                💰 Valor dos pedidos

            </h3>

            <p class="text-gray-500 text-sm mb-4">

                Total dos pedidos que não estão cancelados.

            </p>

            <p class="text-4xl font-bold text-pink-600">

                <?= dinheiro($valorVendas) ?>

            </p>

        </div>


        <div class="bg-white rounded-3xl shadow p-6">

            <h3 class="text-xl font-bold text-gray-800 mb-2">

                ⚠️ Estoque baixo

            </h3>

            <p class="text-gray-500 text-sm mb-4">

                Produtos com 3 unidades ou menos.

            </p>

            <p class="text-4xl font-bold text-orange-500">

                <?= $produtosEstoqueBaixo ?>

            </p>

            <a
                href="admin.php?secao=relatorios"
                class="inline-block mt-4 text-pink-600 font-semibold">

                Ver produtos →

            </a>

        </div>

    </div>


    <div class="bg-white rounded-3xl shadow p-6 mt-6">

        <h3 class="text-xl font-bold text-gray-800 mb-4">

            🚀 Acesso rápido

        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <a
                href="admin.php?secao=usuarios"
                class="p-5 rounded-2xl bg-gray-50 hover:bg-pink-50 transition text-center">

                <div class="text-3xl mb-2">👥</div>

                <span class="font-semibold">
                    Usuários
                </span>

            </a>


            <a
                href="admin.php?secao=produtos"
                class="p-5 rounded-2xl bg-gray-50 hover:bg-pink-50 transition text-center">

                <div class="text-3xl mb-2">🛍️</div>

                <span class="font-semibold">
                    Produtos
                </span>

            </a>


            <a
                href="admin.php?secao=pedidos"
                class="p-5 rounded-2xl bg-gray-50 hover:bg-pink-50 transition text-center">

                <div class="text-3xl mb-2">📦</div>

                <span class="font-semibold">
                    Pedidos
                </span>

            </a>


            <a
                href="admin.php?secao=relatorios"
                class="p-5 rounded-2xl bg-gray-50 hover:bg-pink-50 transition text-center">

                <div class="text-3xl mb-2">📈</div>

                <span class="font-semibold">
                    Relatórios
                </span>

            </a>

        </div>

    </div>


<!-- =====================================================
     USUÁRIOS
====================================================== -->

<?php elseif ($secao === "usuarios"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            👥 Usuários
        </h2>

        <p class="text-gray-500 mt-1">
            Gerencie os usuários cadastrados e seus cargos.
        </p>

    </div>


    <div class="bg-white rounded-3xl shadow overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[900px]">

                <thead class="bg-gray-900 text-white">

                    <tr>

                        <th class="text-left p-4">
                            ID
                        </th>

                        <th class="text-left p-4">
                            Nome
                        </th>

                        <th class="text-left p-4">
                            E-mail
                        </th>

                        <th class="text-left p-4">
                            Telefone
                        </th>

                        <th class="text-left p-4">
                            Cargo
                        </th>

                        <th class="text-left p-4">
                            Ação
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (count($usuarios) === 0): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="p-8 text-center text-gray-500">

                                Nenhum usuário encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach ($usuarios as $usuario): ?>

                        <tr class="border-t hover:bg-gray-50">

                            <td class="p-4 font-semibold">
                                #<?= e($usuario["id_usuario"]) ?>
                            </td>

                            <td class="p-4">

                                <div class="font-semibold text-gray-800">
                                    <?= e($usuario["nome"]) ?>
                                </div>

                                <?php if (!empty($usuario["nome_social"])): ?>

                                    <div class="text-xs text-gray-500">
                                        <?= e($usuario["nome_social"]) ?>
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td class="p-4">
                                <?= e($usuario["email"]) ?>
                            </td>

                            <td class="p-4">
                                <?= e($usuario["telefone"] ?: "Não informado") ?>
                            </td>

                            <td class="p-4">

                                <span class="inline-block bg-pink-100 text-pink-700 px-3 py-1 rounded-full text-sm font-semibold">

                                    <?= e(cargoNome($usuario["tipo_usuario"])) ?>

                                </span>

                            </td>

                            <td class="p-4">

                                <form
                                    method="POST"
                                    class="flex gap-2">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($csrf) ?>">

                                    <input
                                        type="hidden"
                                        name="acao"
                                        value="alterar_cargo">

                                    <input
                                        type="hidden"
                                        name="id_usuario"
                                        value="<?= e($usuario["id_usuario"]) ?>">


                                    <select
                                        name="tipo_usuario"
                                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm">

                                        <?php

                                        $listaCargos = [

                                            "admin"     => "Administrador",
                                            "developer" => "Desenvolvedor",
                                            "support"   => "Suporte",
                                            "moderator" => "Moderador",
                                            "manager"   => "Gerente",
                                            "financial" => "Financeiro",
                                            "logistics" => "Logística",
                                            "customer"  => "Cliente"

                                        ];

                                        foreach ($listaCargos as $valor => $nomeCargo):

                                        ?>

                                            <option
                                                value="<?= e($valor) ?>"
                                                <?= $usuario["tipo_usuario"] === $valor ? "selected" : "" ?>>

                                                <?= e($nomeCargo) ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>


                                    <button
                                        type="submit"
                                        class="bg-gray-900 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm">

                                        Salvar

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>


<!-- =====================================================
     PRODUTOS
====================================================== -->

<?php elseif ($secao === "produtos"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            🛍️ Produtos
        </h2>

        <p class="text-gray-500 mt-1">
            Cadastre, atualize e gerencie os produtos da StyleShop.
        </p>

    </div>


    <!-- CADASTRAR PRODUTO -->

    <div class="bg-white rounded-3xl shadow p-6 mb-6">

        <h3 class="text-xl font-bold text-gray-800 mb-5">

            ➕ Cadastrar produto

        </h3>


        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrf) ?>">

            <input
                type="hidden"
                name="acao"
                value="cadastrar_produto">


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Nome *
                    </label>

                    <input
                        type="text"
                        name="nome_produto"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="Nome do produto">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Categoria/Coleção *
                    </label>

                    <input
                        type="text"
                        name="colecao_produto"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="Ex.: moletom">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Imagem *
                    </label>

                    <input
                        type="text"
                        name="img_produto"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="moletons/moletom1.jpg">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Tamanho *
                    </label>

                    <input
                        type="text"
                        name="tamanho_produto"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="M">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Preço *
                    </label>

                    <input
                        type="number"
                        name="preco_produto"
                        step="0.01"
                        min="0"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="139.90">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Estoque *
                    </label>

                    <input
                        type="number"
                        name="estoque_produto"
                        min="0"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="10">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Cor *
                    </label>

                    <input
                        type="text"
                        name="cor_produto"
                        required
                        class="w-full border rounded-xl px-4 py-3"
                        placeholder="Preto">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Avaliação
                    </label>

                    <input
                        type="number"
                        name="avaliacao_produto"
                        min="0"
                        max="5"
                        step="0.1"
                        value="5"
                        class="w-full border rounded-xl px-4 py-3">

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-1">
                        Marca
                    </label>

                    <input
                        type="text"
                        name="marca_produto"
                        value="StyleShop"
                        class="w-full border rounded-xl px-4 py-3">

                </div>

            </div>


            <div class="mt-4">

                <label class="block text-sm font-semibold mb-1">
                    Descrição
                </label>

                <textarea
                    name="descricao_produto"
                    rows="3"
                    class="w-full border rounded-xl px-4 py-3"
                    placeholder="Descrição do produto"></textarea>

            </div>


            <label class="flex items-center gap-2 mt-4">

                <input
                    type="checkbox"
                    name="destaque_produto"
                    value="1"
                    class="w-4 h-4">

                <span class="text-sm font-semibold">
                    Produto em destaque
                </span>

            </label>


            <button
                type="submit"
                class="mt-5 bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-xl font-semibold">

                ➕ Cadastrar produto

            </button>

        </form>

    </div>


    <!-- LISTA DE PRODUTOS -->

    <div class="space-y-4">

        <?php if (count($produtos) === 0): ?>

            <div class="bg-white rounded-3xl shadow p-8 text-center text-gray-500">

                Nenhum produto encontrado.

            </div>

        <?php endif; ?>


        <?php foreach ($produtos as $produto): ?>

            <div class="bg-white rounded-3xl shadow p-5">

                <div class="flex flex-col lg:flex-row gap-5">


                    <div class="w-full lg:w-32">

                        <?php

                        $imagemProduto = "../src/assets/img/" . $produto["img_produto"];

                        ?>

                        <img
                            src="<?= e($imagemProduto) ?>"
                            alt="<?= e($produto["nome_produto"]) ?>"
                            class="w-full h-32 object-cover rounded-2xl bg-gray-100"
                            onerror="this.style.display='none';">

                    </div>


                    <div class="flex-1">

                        <div class="flex flex-wrap items-center gap-2 mb-2">

                            <h3 class="text-xl font-bold">
                                <?= e($produto["nome_produto"]) ?>
                            </h3>

                            <?php if ((int)$produto["destaque_produto"] === 1): ?>

                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-bold">
                                    ⭐ Destaque
                                </span>

                            <?php endif; ?>

                        </div>


                        <p class="text-sm text-gray-500 mb-2">

                            ID #<?= e($produto["id_produto"]) ?>

                            ·

                            <?= e($produto["colecao_produto"]) ?>

                            ·

                            <?= e($produto["cor_produto"]) ?>

                        </p>


                        <p class="text-gray-600 text-sm mb-4">

                            <?= e($produto["descricao_produto"] ?: "Sem descrição.") ?>

                        </p>


                        <form
                            method="POST"
                            class="flex flex-col sm:flex-row flex-wrap gap-3 items-start sm:items-end">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($csrf) ?>">

                            <input
                                type="hidden"
                                name="acao"
                                value="atualizar_produto">

                            <input
                                type="hidden"
                                name="id_produto"
                                value="<?= e($produto["id_produto"]) ?>">


                            <div>

                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Preço
                                </label>

                                <input
                                    type="number"
                                    name="preco_produto"
                                    step="0.01"
                                    min="0"
                                    value="<?= e($produto["preco_produto"]) ?>"
                                    class="border rounded-lg px-3 py-2 w-32">

                            </div>


                            <div>

                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Estoque
                                </label>

                                <input
                                    type="number"
                                    name="estoque_produto"
                                    min="0"
                                    value="<?= e($produto["estoque_produto"]) ?>"
                                    class="border rounded-lg px-3 py-2 w-28">

                            </div>


                            <label class="flex items-center gap-2 pb-2">

                                <input
                                    type="checkbox"
                                    name="destaque_produto"
                                    value="1"
                                    <?= (int)$produto["destaque_produto"] === 1 ? "checked" : "" ?>>

                                Destaque

                            </label>


                            <button
                                type="submit"
                                class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">

                                Salvar

                            </button>

                        </form>

                    </div>


                    <div class="flex lg:flex-col gap-3 lg:justify-center">

                        <form
                            method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($csrf) ?>">

                            <input
                                type="hidden"
                                name="acao"
                                value="excluir_produto">

                            <input
                                type="hidden"
                                name="id_produto"
                                value="<?= e($produto["id_produto"]) ?>">

                            <button
                                type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">

                                🗑️ Excluir

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>


<!-- =====================================================
     PEDIDOS
====================================================== -->

<?php elseif ($secao === "pedidos"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            📦 Pedidos
        </h2>

        <p class="text-gray-500 mt-1">
            Visualize e atualize os pedidos realizados.
        </p>

    </div>


    <div class="space-y-5">

        <?php if (count($pedidos) === 0): ?>

            <div class="bg-white rounded-3xl shadow p-8 text-center text-gray-500">

                Nenhum pedido encontrado.

            </div>

        <?php endif; ?>


        <?php foreach ($pedidos as $pedido): ?>

            <div class="bg-white rounded-3xl shadow p-6">

                <div class="flex flex-col lg:flex-row lg:justify-between gap-5">


                    <div>

                        <div class="flex flex-wrap items-center gap-3">

                            <h3 class="text-xl font-bold">
                                Pedido #<?= e($pedido["id_pedido"]) ?>
                            </h3>

                            <span class="bg-pink-100 text-pink-700 px-3 py-1 rounded-full text-sm font-semibold">

                                <?= e(statusPedidoNome($pedido["status_pedido"])) ?>

                            </span>

                        </div>


                        <p class="text-gray-700 mt-3">

                            👤 <?= e($pedido["nome"] ?? "Usuário") ?>

                        </p>


                        <p class="text-gray-500 text-sm">

                            <?= e($pedido["email"] ?? "") ?>

                        </p>


                        <p class="text-gray-500 text-sm mt-2">

                            <?= e($pedido["criado_em"]) ?>

                        </p>

                    </div>


                    <div class="lg:text-right">

                        <p class="text-gray-500 text-sm">
                            Total
                        </p>

                        <p class="text-2xl font-bold text-pink-600">

                            <?= dinheiro($pedido["total"]) ?>

                        </p>


                        <div class="mt-3">

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($csrf) ?>">

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="alterar_status_pedido">

                                <input
                                    type="hidden"
                                    name="id_pedido"
                                    value="<?= e($pedido["id_pedido"]) ?>">


                                <select
                                    name="status_pedido"
                                    onchange="this.form.submit()"
                                    class="border rounded-xl px-3 py-2 text-sm">

                                    <?php

                                    $statusLista = [

                                        "aguardando_pagamento" => "Aguardando pagamento",
                                        "pago"                 => "Pago",
                                        "processando"          => "Processando",
                                        "enviado"              => "Enviado",
                                        "entregue"             => "Entregue",
                                        "cancelado"            => "Cancelado"

                                    ];

                                    foreach ($statusLista as $valor => $nomeStatus):

                                    ?>

                                        <option
                                            value="<?= e($valor) ?>"
                                            <?= $pedido["status_pedido"] === $valor ? "selected" : "" ?>>

                                            <?= e($nomeStatus) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </form>

                        </div>

                    </div>

                </div>


                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t mt-5 pt-5">

                    <div>

                        <p class="text-gray-500 text-sm">
                            Subtotal
                        </p>

                        <p class="font-semibold">
                            <?= dinheiro($pedido["subtotal"]) ?>
                        </p>

                    </div>


                    <div>

                        <p class="text-gray-500 text-sm">
                            Frete
                        </p>

                        <p class="font-semibold">
                            <?= dinheiro($pedido["frete"]) ?>
                        </p>

                    </div>


                    <div>

                        <p class="text-gray-500 text-sm">
                            Desconto
                        </p>

                        <p class="font-semibold">
                            <?= dinheiro($pedido["desconto"]) ?>
                        </p>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>


<!-- =====================================================
     CATEGORIAS
====================================================== -->

<?php elseif ($secao === "categorias"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            🏷️ Categorias
        </h2>

        <p class="text-gray-500 mt-1">
            Categorias identificadas a partir das coleções cadastradas nos produtos.
        </p>

    </div>


    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6">

        <p class="text-blue-800">

            ℹ️ No banco atual não existe uma tabela separada chamada
            <strong>categorias</strong>. Por enquanto, o StyleShop utiliza o campo
            <strong>colecao_produto</strong> da tabela de produtos.

        </p>

    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <?php foreach ($categorias as $categoria): ?>

            <div class="bg-white rounded-3xl shadow p-6">

                <div class="text-4xl mb-3">
                    🏷️
                </div>

                <h3 class="text-xl font-bold text-gray-800">

                    <?= e(ucfirst($categoria["categoria"])) ?>

                </h3>

                <p class="text-gray-500 mt-2">

                    <?= e($categoria["quantidade"]) ?>

                    produto(s)

                </p>

            </div>

        <?php endforeach; ?>


        <?php if (count($categorias) === 0): ?>

            <div class="bg-white rounded-3xl shadow p-6">

                Nenhuma categoria encontrada.

            </div>

        <?php endif; ?>

    </div>


<!-- =====================================================
     CUPONS
====================================================== -->

<?php elseif ($secao === "cupons"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            🎟️ Cupons
        </h2>

        <p class="text-gray-500 mt-1">
            Área reservada para o gerenciamento de cupons.
        </p>

    </div>


    <div class="bg-white rounded-3xl shadow p-8 text-center">

        <div class="text-6xl mb-5">
            🎟️
        </div>

        <h3 class="text-2xl font-bold text-gray-800 mb-3">

            Sistema de cupons ainda não criado

        </h3>

        <p class="text-gray-500 max-w-xl mx-auto">

            O banco de dados atual do StyleShop não possui uma tabela
            <strong>cupons</strong>. Por isso, esta área foi deixada preparada,
            mas nenhum cupom falso será criado.

        </p>

    </div>


<!-- =====================================================
     CLIENTES
====================================================== -->

<?php elseif ($secao === "clientes"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            👤 Clientes
        </h2>

        <p class="text-gray-500 mt-1">
            Usuários que possuem o cargo de cliente.
        </p>

    </div>


    <div class="bg-white rounded-3xl shadow overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[800px]">

                <thead class="bg-gray-900 text-white">

                    <tr>

                        <th class="text-left p-4">
                            ID
                        </th>

                        <th class="text-left p-4">
                            Nome
                        </th>

                        <th class="text-left p-4">
                            E-mail
                        </th>

                        <th class="text-left p-4">
                            Telefone
                        </th>

                        <th class="text-left p-4">
                            Nascimento
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($clientes as $cliente): ?>

                        <tr class="border-t hover:bg-gray-50">

                            <td class="p-4 font-semibold">

                                #<?= e($cliente["id_usuario"]) ?>

                            </td>


                            <td class="p-4 font-semibold">

                                <?= e($cliente["nome"]) ?>

                            </td>


                            <td class="p-4">

                                <?= e($cliente["email"]) ?>

                            </td>


                            <td class="p-4">

                                <?= e($cliente["telefone"] ?: "Não informado") ?>

                            </td>


                            <td class="p-4">

                                <?= e($cliente["nascimento"]) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (count($clientes) === 0): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="p-8 text-center text-gray-500">

                                Nenhum cliente encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


<!-- =====================================================
     RELATÓRIOS
====================================================== -->

<?php elseif ($secao === "relatorios"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            📈 Relatórios
        </h2>

        <p class="text-gray-500 mt-1">
            Informações gerais da loja com base nos dados do banco.
        </p>

    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


        <div class="bg-white rounded-3xl shadow p-6">

            <p class="text-gray-500 text-sm">
                Usuários
            </p>

            <p class="text-3xl font-bold mt-2">
                <?= $totalUsuarios ?>
            </p>

        </div>


        <div class="bg-white rounded-3xl shadow p-6">

            <p class="text-gray-500 text-sm">
                Clientes
            </p>

            <p class="text-3xl font-bold mt-2">
                <?= $totalClientes ?>
            </p>

        </div>


        <div class="bg-white rounded-3xl shadow p-6">

            <p class="text-gray-500 text-sm">
                Produtos
            </p>

            <p class="text-3xl font-bold mt-2">
                <?= $totalProdutos ?>
            </p>

        </div>


        <div class="bg-white rounded-3xl shadow p-6">

            <p class="text-gray-500 text-sm">
                Pedidos
            </p>

            <p class="text-3xl font-bold mt-2">
                <?= $totalPedidos ?>
            </p>

        </div>

    </div>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">


        <div class="bg-white rounded-3xl shadow overflow-hidden">

            <div class="p-6 border-b">

                <h3 class="text-xl font-bold">
                    📦 Pedidos por status
                </h3>

            </div>


            <div class="p-6 space-y-4">

                <?php foreach ($relatorioPedidos as $item): ?>

                    <div class="flex items-center justify-between border-b pb-3">

                        <div>

                            <p class="font-semibold">

                                <?= e(statusPedidoNome($item["status_pedido"])) ?>

                            </p>

                            <p class="text-sm text-gray-500">

                                <?= e($item["quantidade"]) ?> pedido(s)

                            </p>

                        </div>


                        <p class="font-bold text-pink-600">

                            <?= dinheiro($item["valor"]) ?>

                        </p>

                    </div>

                <?php endforeach; ?>


                <?php if (count($relatorioPedidos) === 0): ?>

                    <p class="text-gray-500">
                        Nenhum dado encontrado.
                    </p>

                <?php endif; ?>

            </div>

        </div>


        <div class="bg-white rounded-3xl shadow overflow-hidden">

            <div class="p-6 border-b">

                <h3 class="text-xl font-bold">
                    ⚠️ Estoque baixo
                </h3>

            </div>


            <div class="p-6">

                <?php if (count($relatorioProdutos) === 0): ?>

                    <p class="text-green-600 font-semibold">

                        ✅ Nenhum produto com estoque baixo.

                    </p>

                <?php else: ?>

                    <div class="space-y-4">

                        <?php foreach ($relatorioProdutos as $item): ?>

                            <div class="flex justify-between items-center border-b pb-3">

                                <div>

                                    <p class="font-semibold">

                                        <?= e($item["nome_produto"]) ?>

                                    </p>

                                    <p class="text-sm text-gray-500">

                                        <?= dinheiro($item["preco_produto"]) ?>

                                    </p>

                                </div>


                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-bold text-sm">

                                    <?= e($item["estoque_produto"]) ?> un.

                                </span>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>


<!-- =====================================================
     CONFIGURAÇÕES
====================================================== -->

<?php elseif ($secao === "configuracoes"): ?>

    <div class="mb-6">

        <h2 class="text-3xl font-bold text-gray-800">
            ⚙️ Configurações
        </h2>

        <p class="text-gray-500 mt-1">
            Informações do painel administrativo.
        </p>

    </div>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">


        <div class="bg-white rounded-3xl shadow p-6">

            <h3 class="text-xl font-bold mb-5">
                🏪 Loja
            </h3>


            <div class="space-y-4">

                <div>

                    <p class="text-sm text-gray-500">
                        Nome da loja
                    </p>

                    <p class="font-semibold text-gray-800">
                        StyleShop
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Banco de dados
                    </p>

                    <p class="font-semibold text-gray-800">
                        styleshop
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Administrador conectado
                    </p>

                    <p class="font-semibold text-gray-800">

                        <?= e($nomeAdmin) ?>

                    </p>

                </div>

            </div>

        </div>


        <div class="bg-white rounded-3xl shadow p-6">

            <h3 class="text-xl font-bold mb-5">
                🔐 Acesso
            </h3>


            <div class="space-y-4">

                <div>

                    <p class="text-sm text-gray-500">
                        Cargo atual
                    </p>

                    <span class="inline-block mt-1 bg-pink-100 text-pink-700 px-3 py-1 rounded-full font-semibold">

                        Administrador

                    </span>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        ID do usuário
                    </p>

                    <p class="font-semibold">

                        #<?= e($_SESSION["id_usuario"]) ?>

                    </p>

                </div>


                <div class="bg-green-50 border border-green-200 rounded-xl p-4">

                    <p class="text-green-700 text-sm">

                        ✅ Você possui acesso administrativo completo.

                    </p>

                </div>

            </div>

        </div>

    </div>


<?php endif; ?>


</main>


<!-- =====================================================
     RODAPÉ
====================================================== -->

<footer class="text-center text-gray-500 text-sm py-8">

    <p>

        <?= e($lojaNome) ?> —
        Painel Administrativo

    </p>

</footer>


</body>

</html>