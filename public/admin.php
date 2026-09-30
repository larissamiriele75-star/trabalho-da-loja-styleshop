<?php

session_start();

// =====================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// =====================================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

// =====================================================
// VERIFICAR CARGO
// =====================================================

if ($_SESSION["tipo_usuario"] !== "admin") {
    http_response_code(403);
    ?>
    
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acesso negado - StyleShop</title>

        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-gray-100 min-h-screen flex items-center justify-center">

        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4 text-center">

            <div class="text-6xl mb-4">
                🚫
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-3">
                Acesso negado
            </h1>

            <p class="text-gray-600 mb-6">
                Você não possui permissão para acessar a área administrativa.
            </p>

            <a
                href="index.php"
                class="inline-block bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold">
                Voltar para a loja
            </a>

        </div>

    </body>
    </html>

    <?php
    exit;
}

// =====================================================
// ADMINISTRADOR AUTORIZADO
// =====================================================

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Administração - StyleShop</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">

    <header class="bg-gray-800 text-white p-4">

        <div class="max-w-6xl mx-auto flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-bold">
                    ⚙️ Administração
                </h1>

                <p class="text-sm text-gray-300">
                    StyleShop
                </p>
            </div>

            <a
                href="index.php"
                class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg">
                ← Voltar para loja
            </a>

        </div>

    </header>


    <main class="max-w-6xl mx-auto p-6">

        <div class="bg-white rounded-2xl shadow p-6 mb-6">

            <h2 class="text-2xl font-bold mb-2">
                Olá, <?= htmlspecialchars($_SESSION["nome"]) ?>! 👋
            </h2>

            <p class="text-gray-600">
                Você está acessando o painel administrativo.
            </p>

            <p class="mt-2 text-sm">
                Cargo:
                <strong class="text-pink-600">
                    <?= htmlspecialchars($_SESSION["tipo_usuario"]) ?>
                </strong>
            </p>

        </div>


        <!-- CARDS DO PAINEL -->

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white rounded-2xl shadow p-6">

                <div class="text-4xl mb-3">
                    👥
                </div>

                <h3 class="text-lg font-bold">
                    Usuários
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Gerenciar usuários
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow p-6">

                <div class="text-4xl mb-3">
                    📦
                </div>

                <h3 class="text-lg font-bold">
                    Produtos
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Gerenciar produtos
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow p-6">

                <div class="text-4xl mb-3">
                    🛒
                </div>

                <h3 class="text-lg font-bold">
                    Pedidos
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Visualizar pedidos
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow p-6">

                <div class="text-4xl mb-3">
                    📊
                </div>

                <h3 class="text-lg font-bold">
                    Relatórios
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Informações da loja
                </p>

            </div>

        </div>

    </main>

</body>

</html>