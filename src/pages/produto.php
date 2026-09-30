<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../php/conexao.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$sql = "SELECT * FROM produtos WHERE id_produto = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
$produto = $resultado->fetch_assoc();

if (!$produto) {
    die("Produto não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($produto['nome_produto']); ?> - StyleShop</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">

    <!-- CABEÇALHO - MESMO PADRÃO VISUAL DA HOME -->
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center sticky top-0 z-40">

        <div class="flex items-center gap-3">
            <img
                src="../assets/img/logo.png"
                alt="StyleShop"
                width="40"
                height="40"
                class="w-10 h-10 object-contain">

            <h1 class="text-2xl font-bold">
                StyleShop
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">

            <div
                id="navFotoProduto"
                class="w-8 h-8 rounded-full bg-pink-500 text-white flex items-center justify-center font-bold text-sm overflow-hidden hidden">
            </div>

            <span
                id="nomeUsuarioProduto"
                class="text-sm hidden text-gray-300">
            </span>

            <a
                href="../../public/index.php"
                class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600 text-sm">
                Voltar para a loja
            </a>

            <button
                id="btnSairProduto"
                type="button"
                onclick="sairProduto()"
                class="bg-gray-600 px-3 py-1 rounded hover:bg-gray-500 text-sm hidden">
                Sair
            </button>

            <button
                id="temaBtnProduto"
                type="button"
                aria-label="Alternar modo escuro"
                class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600">
                🌙
            </button>

            <button
                id="abrirCarrinhoProduto"
                type="button"
                class="bg-pink-600 px-4 py-2 rounded">
                🛒 (<span id="contadorProduto">0</span>)
            </button>

        </div>

    </header>


    <!-- PRODUTO -->
    <main class="max-w-6xl mx-auto px-4 py-10">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 grid md:grid-cols-2 gap-10">

            <div>
                <img
                    src="../assets/img/<?php echo htmlspecialchars($produto['img_produto']); ?>"
                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>"
                    class="w-full max-h-[550px] object-contain rounded-xl">
            </div>

            <div class="flex flex-col justify-center">

                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize mb-2">
                    <?php echo htmlspecialchars($produto['colecao_produto']); ?>
                </p>

                <h2 class="text-3xl font-bold mb-4">
                    <?php echo htmlspecialchars($produto['nome_produto']); ?>
                </h2>

                <p class="text-3xl font-bold text-pink-600 mb-4">
                    R$
                    <?php
                    echo number_format(
                        $produto['preco_produto'],
                        2,
                        ',',
                        '.'
                    );
                    ?>
                </p>

                <?php if ($produto['estoque_produto'] > 0): ?>

                    <p class="text-green-600 font-semibold mb-4">
                        Em estoque:
                        <?php echo (int)$produto['estoque_produto']; ?> unidade(s)
                    </p>

                <?php else: ?>

                    <p class="text-red-600 font-bold mb-4">
                        Produto esgotado
                    </p>

                <?php endif; ?>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-5 space-y-3">

                    <p>
                        <strong>Tamanho:</strong>
                        <?php echo htmlspecialchars($produto['tamanho_produto']); ?>
                    </p>

                    <p>
                        <strong>Cor:</strong>
                        <?php echo htmlspecialchars($produto['cor_produto']); ?>
                    </p>

                    <p>
                        <strong>Avaliação:</strong>
                        ⭐ <?php echo htmlspecialchars($produto['avaliacao_produto']); ?>
                    </p>

                </div>

                <div class="mt-7 flex gap-3">

                    <?php if ($produto['estoque_produto'] > 0): ?>

                        <button
                            type="button"
                            onclick="adicionarProduto(false)"
                            class="w-1/2 bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-xl">
                            Adicionar ao carrinho
                        </button>

                        <button
                            type="button"
                            onclick="adicionarProduto(true)"
                            class="w-1/2 bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 rounded-xl">
                            Comprar agora
                        </button>

                    <?php else: ?>

                        <button
                            disabled
                            class="w-full bg-gray-400 text-white font-bold py-3 rounded-xl cursor-not-allowed">
                            Indisponível
                        </button>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mt-8">

            <h3 class="text-xl font-bold mb-3">
                Sobre o produto
            </h3>

            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                <?php
                echo nl2br(
                    htmlspecialchars(
                        $produto['descricao_produto']
                        ?: 'Descrição ainda não cadastrada.'
                    )
                );
                ?>
            </p>

        </section>

    </main>


    <!-- CARRINHO LATERAL -->
    <div
        id="carrinhoProduto"
        class="fixed top-0 right-0 w-80 h-full bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xl p-4 hidden overflow-y-auto z-50"
        style="display:none; flex-direction:column;">

        <div class="flex justify-between items-center mb-4">

            <h2 class="text-xl font-bold">
                🛒 Carrinho
            </h2>

            <button
                id="fecharCarrinhoProduto"
                type="button"
                class="text-gray-400 hover:text-gray-700 text-2xl">
                &times;
            </button>

        </div>

        <div
            id="itensCarrinhoProduto"
            class="flex-1">
        </div>

        <div class="border-t pt-4 mt-4">

            <p class="font-bold text-lg mb-3">
                Total:
                R$
                <span id="totalProduto">
                    0,00
                </span>
            </p>

            <a
                href="../../public/index.php"
                class="block text-center w-full bg-pink-600 hover:bg-pink-700 text-white py-2 rounded-lg font-semibold">
                Ir para a loja
            </a>

        </div>

    </div>


<script>

const produtoAtualPagina = {
    id: <?php echo (int)$produto['id_produto']; ?>,
    estoque: <?php echo (int)$produto['estoque_produto']; ?>
};

let carrinhoItensProduto = [];


// =====================================================
// USUÁRIO / CABEÇALHO
// =====================================================

const usuarioSessaoProduto = <?php echo json_encode([
    'logado' => isset($_SESSION['id_usuario']),
    'id_usuario' => $_SESSION['id_usuario'] ?? null,
    'nome' => $_SESSION['nome'] ?? '',
    'email' => $_SESSION['email'] ?? ''
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

function pegarUsuarioProduto() {
    if (!usuarioSessaoProduto.logado) {
        return null;
    }

    return usuarioSessaoProduto;
}


function atualizarCabecalhoProduto() {

    const usuario = pegarUsuarioProduto();

    const foto =
        document.getElementById('navFotoProduto');

    const nome =
        document.getElementById('nomeUsuarioProduto');

    const sair =
        document.getElementById('btnSairProduto');

    if (!usuario) {

        foto.classList.add('hidden');
        nome.classList.add('hidden');
        sair.classList.add('hidden');

        return;
    }

    foto.classList.remove('hidden');
    nome.classList.remove('hidden');
    sair.classList.remove('hidden');

    const nomeExibicao =
        usuario.nome_social ||
        usuario.nome ||
        'Usuário';

    nome.textContent =
        'Olá, ' + nomeExibicao + '!';

    const fotoUsuario =
        usuario.foto ||
        usuario.foto_perfil ||
        '';

    if (fotoUsuario) {

        foto.innerHTML = `
            <img
                src="${fotoUsuario}"
                alt="Foto de perfil"
                class="w-full h-full object-cover rounded-full">
        `;

    } else {

        foto.textContent =
            nomeExibicao
                .charAt(0)
                .toUpperCase();
    }
}


async function sairProduto() {

    try {

        await fetch(
            '/styleshop/src/php/logout.php'
        );

    } catch (erro) {

        console.error(
            'Erro ao sair:',
            erro
        );
    }

    localStorage.removeItem(
        'usuarioAtual'
    );

    window.location.href =
        '/styleshop/public/index.php';
}


// =====================================================
// TEMA
// =====================================================

function aplicarTemaProduto() {

    const temaSalvo =
        localStorage.getItem('tema');

    const escuro =
        temaSalvo === 'escuro';

    document.documentElement
        .classList
        .toggle('dark', escuro);

    const botao =
        document.getElementById(
            'temaBtnProduto'
        );

    botao.textContent =
        escuro ? '☀️' : '🌙';
}


document
    .getElementById('temaBtnProduto')
    .addEventListener(
        'click',
        () => {

            const estaEscuro =
                document.documentElement
                    .classList
                    .contains('dark');

            const novoTema =
                estaEscuro
                    ? 'claro'
                    : 'escuro';

            localStorage.setItem(
                'tema',
                novoTema
            );

            aplicarTemaProduto();
        }
    );


// =====================================================
// CARRINHO MYSQL
// =====================================================

async function carregarCarrinhoProduto() {

    const usuario =
        pegarUsuarioProduto();

    if (!usuario) {

        carrinhoItensProduto = [];

        renderizarCarrinhoProduto();

        return false;
    }

    try {

        const resposta =
            await fetch(
                '/styleshop/src/php/carrinho.php?acao=listar'
            );

        const resultado =
            await resposta.json();

        if (
            resultado.login === false ||
            !resultado.sucesso
        ) {

            carrinhoItensProduto = [];

            renderizarCarrinhoProduto();

            return false;
        }

        carrinhoItensProduto =
            Array.isArray(resultado.itens)
                ? resultado.itens
                : [];

        renderizarCarrinhoProduto();

        return true;

    } catch (erro) {

        console.error(
            'Erro ao carregar carrinho:',
            erro
        );

        return false;
    }
}


// =====================================================
// ADICIONAR / COMPRAR AGORA
// =====================================================

async function adicionarProduto(
    comprarAgora = false
) {

    if (
        Number(produtoAtualPagina.estoque)
        <= 0
    ) {

        alert(
            'Este produto está esgotado.'
        );

        return;
    }

    const usuario =
        pegarUsuarioProduto();

    if (!usuario) {

        localStorage.setItem(
            'redirecionarAposLogin',
            comprarAgora
                ? 'checkout'
                : 'carrinho'
        );

        localStorage.setItem(
            'produtoPendente',
            String(produtoAtualPagina.id)
        );

        alert(
            'Você precisa entrar na sua conta primeiro.'
        );

        window.location.href =
            '/styleshop/public/index.php';

        return;
    }

    try {

        const dados =
            new FormData();

        dados.append(
            'acao',
            'adicionar'
        );

        dados.append(
            'id_produto',
            produtoAtualPagina.id
        );

        const resposta =
            await fetch(
                '/styleshop/src/php/carrinho.php',
                {
                    method: 'POST',
                    body: dados
                }
            );

        const resultado =
            await resposta.json();

        if (resultado.login === false) {

            localStorage.removeItem(
                'usuarioAtual'
            );

            window.location.href =
                '/styleshop/public/index.php';

            return;
        }

        if (!resultado.sucesso) {

            alert(
                resultado.mensagem ||
                'Não foi possível adicionar o produto.'
            );

            return;
        }

        await carregarCarrinhoProduto();

        if (comprarAgora) {

            /*
             * O checkout atual do StyleShop fica na
             * página principal, no modalFinalizar.
             * Por isso voltamos para a home avisando
             * que ela deve abrir o checkout.
             */
            localStorage.setItem(
                'redirecionarAposLogin',
                'checkout'
            );

            window.location.href =
                '/styleshop/public/index.php';

            return;
        }

        abrirCarrinhoProduto();

    } catch (erro) {

        console.error(
            'Erro ao adicionar produto:',
            erro
        );

        alert(
            'Não foi possível adicionar o produto ao carrinho.'
        );
    }
}


// =====================================================
// RENDERIZAR CARRINHO
// =====================================================

function renderizarCarrinhoProduto() {

    const container =
        document.getElementById(
            'itensCarrinhoProduto'
        );

    const contador =
        document.getElementById(
            'contadorProduto'
        );

    const totalElemento =
        document.getElementById(
            'totalProduto'
        );

    const totalQtd =
        carrinhoItensProduto.reduce(
            (soma, item) =>
                soma +
                Number(
                    item.qtd ??
                    item.quantidade ??
                    0
                ),
            0
        );

    contador.textContent =
        totalQtd;

    if (!carrinhoItensProduto.length) {

        container.innerHTML = `
            <p class="text-gray-400 text-center py-10">
                Seu carrinho está vazio.
            </p>
        `;

        totalElemento.textContent =
            '0,00';

        return;
    }

    let total = 0;

    container.innerHTML =
        carrinhoItensProduto
            .map(item => {

                const nome =
                    item.nome ??
                    item.nome_produto ??
                    'Produto';

                const preco =
                    Number(
                        item.preco ??
                        item.preco_produto ??
                        0
                    );

                const qtd =
                    Number(
                        item.qtd ??
                        item.quantidade ??
                        0
                    );

                const img =
                    item.img ??
                    (
                        item.img_produto
                            ? '/styleshop/src/assets/img/' +
                              item.img_produto
                            : ''
                    );

                total +=
                    preco * qtd;

                return `
                    <div class="flex gap-3 border-b py-4">

                        <img
                            src="${img}"
                            alt="${nome}"
                            class="w-16 h-16 object-cover rounded-lg">

                        <div class="flex-1">

                            <p class="font-semibold">
                                ${nome}
                            </p>

                            <p class="text-pink-600 font-semibold">
                                R$ ${preco
                                    .toFixed(2)
                                    .replace('.', ',')}
                            </p>

                            <p class="text-sm text-gray-500">
                                Quantidade: ${qtd}
                            </p>

                        </div>

                    </div>
                `;
            })
            .join('');

    totalElemento.textContent =
        total
            .toFixed(2)
            .replace('.', ',');
}


// =====================================================
// ABRIR / FECHAR CARRINHO
// =====================================================

function abrirCarrinhoProduto() {

    const carrinho =
        document.getElementById(
            'carrinhoProduto'
        );

    carrinho.style.display =
        'flex';

    carrinho.classList.remove(
        'hidden'
    );
}


function fecharCarrinhoProduto() {

    const carrinho =
        document.getElementById(
            'carrinhoProduto'
        );

    carrinho.style.display =
        'none';

    carrinho.classList.add(
        'hidden'
    );
}


document
    .getElementById(
        'abrirCarrinhoProduto'
    )
    .addEventListener(
        'click',
        abrirCarrinhoProduto
    );


document
    .getElementById(
        'fecharCarrinhoProduto'
    )
    .addEventListener(
        'click',
        fecharCarrinhoProduto
    );


// =====================================================
// INICIALIZAÇÃO
// =====================================================

aplicarTemaProduto();
atualizarCabecalhoProduto();
carregarCarrinhoProduto();

</script>

</body>
</html>