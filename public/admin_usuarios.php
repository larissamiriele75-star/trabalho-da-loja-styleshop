<?php

session_start();

require_once "../src/php/conexao.php";

// =====================================================
// VERIFICAR SE ESTÁ LOGADO
// =====================================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

// =====================================================
// VERIFICAR SE É ADMIN
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
                Você não possui permissão para acessar esta área.
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
// BUSCAR USUÁRIOS
// =====================================================

$sql = "
    SELECT
        id_usuario,
        nome,
        email,
        tipo_usuario
    FROM usuarios
    ORDER BY id_usuario ASC
";

$resultado = $conexao->query($sql);

if (!$resultado) {
    die("Erro ao buscar usuários: " . $conexao->error);
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Gerenciar usuários - StyleShop</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <!-- =====================================================
         CONTEÚDO
    ====================================================== -->

    <main class="max-w-6xl mx-auto p-6">

        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="p-6 border-b">

                <h2 class="text-xl font-bold text-gray-800">
                    Usuários cadastrados
                </h2>

                <p class="text-gray-500 text-sm mt-1">
                    Lista de usuários registrados no StyleShop.
                </p>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-gray-100">

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
                                Cargo
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($usuario = $resultado->fetch_assoc()): ?>

                            <tr class="border-t hover:bg-gray-50">

                                <td class="p-4">
                                    <?= htmlspecialchars($usuario["id_usuario"]) ?>
                                </td>

                                <td class="p-4 font-semibold">
                                    <?= htmlspecialchars($usuario["nome"]) ?>
                                </td>

                                <td class="p-4">
                                    <?= htmlspecialchars($usuario["email"]) ?>
                                </td>

                                <td class="p-4">

                                    <?php
                                    $cargo = $usuario["tipo_usuario"];

                                    $nomesCargos = [
                                        "admin"     => "Administrador",
                                        "developer" => "Desenvolvedor",
                                        "support"   => "Suporte",
                                        "moderator" => "Moderador",
                                        "manager"   => "Gerente",
                                        "financial" => "Financeiro",
                                        "logistics" => "Logística",
                                        "customer"  => "Cliente"
                                    ];

                                    echo htmlspecialchars(
                                        $nomesCargos[$cargo] ?? $cargo
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</body>

</html>