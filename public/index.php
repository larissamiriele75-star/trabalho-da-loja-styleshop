<?php

session_start();

require_once "../src/php/conexao.php";

$produtosBanco = [];

$sql = "SELECT * FROM produtos";
$resultado = $conexao->query($sql);

if (!$resultado) {
  die("Erro ao buscar produtos: " . $conexao->error);
}

while ($produto = $resultado->fetch_assoc()) {

  $produtosBanco[] = [
    'id' => (int)$produto['id_produto'],
    'nome' => $produto['nome_produto'],
    'categoria' => $produto['colecao_produto'],
    'preco' => (float)$produto['preco_produto'],
    'img' => '../src/assets/img/' . $produto['img_produto'],
    'desc' => 'Produto StyleShop',
    'tamanho' => $produto['tamanho_produto'],
    'cor' => $produto['cor_produto'],
    'estoque' => (int)$produto['estoque_produto'],
    'avaliacao' => (float)$produto['avaliacao_produto'],
    'destaque' => (int)$produto['destaque_produto']
  ];
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StyleShop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class'
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../src/assets/css/style.css">

  <style>

/* =========================================================
   STYLES HOP - RESPONSIVIDADE
   CELULAR E TABLET
   ========================================================= */


/* =========================================================
   TABLET
   Até 1024px
   ========================================================= */

@media (max-width: 1024px) {

    /* ---------- CABEÇALHO ---------- */

    #nomeLoja {
        display: none !important;
    }

    #nomeUsuario {
        display: none !important;
    }

    header {
        padding: 10px 14px !important;
        gap: 8px !important;
    }

    header > div:first-child {
        gap: 8px !important;
    }

    header > div:first-child img {
        width: 36px !important;
        height: 36px !important;
    }

    #navFoto {
        width: 34px !important;
        height: 34px !important;
    }

    header button,
    header a {
        font-size: 13px !important;
    }


    /* ---------- MODAL DO PERFIL ---------- */

    #modalPerfil > div {
        width: calc(100% - 32px) !important;
        max-width: 700px !important;
        max-height: 92vh !important;
    }

    #modalPerfil > div > div:first-child {
        padding: 12px 16px !important;
    }

    #modalPerfil .flex-1.overflow-hidden {
        flex-direction: column !important;
    }

    #modalPerfil nav {
        width: 100% !important;
        border-right: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 8px !important;
        flex-direction: row !important;
        overflow-x: auto !important;
        gap: 5px !important;
    }

    #modalPerfil nav button {
        white-space: nowrap !important;
        font-size: 12px !important;
        padding: 7px 9px !important;
    }

    #modalPerfil .flex-1.overflow-y-auto {
        padding: 16px !important;
    }
}


/* =========================================================
   CELULAR
   Até 767px
   ========================================================= */

@media (max-width: 767px) {

    /* ---------- CABEÇALHO ---------- */

    header {
        padding: 8px 10px !important;
        min-height: 56px !important;
        align-items: center !important;
    }

    header > div:first-child {
        gap: 5px !important;
        flex-shrink: 0 !important;
    }

    header > div:first-child img {
        width: 32px !important;
        height: 32px !important;
    }

    #nomeLoja {
        display: none !important;
    }

    #nomeUsuario {
        display: none !important;
    }

    header > div:last-child {
        gap: 4px !important;
        flex-wrap: nowrap !important;
        justify-content: flex-end !important;
    }

    #navFoto {
        width: 30px !important;
        height: 30px !important;
        font-size: 11px !important;
        flex-shrink: 0 !important;
    }

    #btnAuth,
    #btnSair,
    #btnAdmin {
        font-size: 11px !important;
        padding: 5px 7px !important;
        white-space: nowrap !important;
    }

    #temaBtn {
        font-size: 14px !important;
        padding: 5px 7px !important;
        min-width: 32px !important;
    }

    #abrirCarrinho {
        font-size: 12px !important;
        padding: 6px 8px !important;
        white-space: nowrap !important;
    }


    /* ---------- MODAL DO PERFIL ---------- */

    #modalPerfil {
        padding: 8px !important;
    }

    #modalPerfil > div {
        width: 100% !important;
        max-width: none !important;
        max-height: 94vh !important;
        margin: 0 !important;
        border-radius: 16px !important;
    }

    /* Cabeçalho rosa do perfil */

    #modalPerfil > div > div:first-child {
        padding: 10px 12px !important;
    }

    #modalPerfil #avatarCircle {
        width: 42px !important;
        height: 42px !important;
        font-size: 14px !important;
    }

    #perfilNomeHeader {
        font-size: 14px !important;
    }

    #modalPerfil > div > div:first-child p.text-xs {
        font-size: 10px !important;
    }

    #modalPerfil > div > div:first-child button {
        font-size: 22px !important;
    }


    /* Área principal do perfil */

    #modalPerfil .flex-1.overflow-hidden {
        flex-direction: column !important;
        min-height: 0 !important;
    }


    /* Menu das abas */

    #modalPerfil nav {
        width: 100% !important;
        flex-direction: row !important;
        border-right: none !important;
        border-bottom: 1px solid #374151 !important;
        padding: 6px !important;
        gap: 4px !important;
        overflow-x: auto !important;
        flex-shrink: 0 !important;
        justify-content: space-between;
    }

    #modalPerfil nav button:not(.icon-modal-perfil){
      font-size: 0 !important;
      width: 50px;
      text-align: center;
    }

    #modalPerfil nav button {
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        font-size: 11px !important;
        padding: 7px 9px !important;
    }

    #modalPerfil nav button span.icon-modal-perfil{
      font-size: 11px;
    }

    /* Conteúdo */

    #modalPerfil .flex-1.overflow-y-auto {
        padding: 12px !important;
        min-width: 0 !important;
    }

    #modalPerfil .flex-1.overflow-y-auto h3 {
        font-size: 16px !important;
        margin-bottom: 12px !important;
    }


    /* Campos do perfil */

    #modalPerfil input,
    #modalPerfil select,
    #modalPerfil textarea {
        font-size: 13px !important;
        padding: 8px 10px !important;
    }

    #modalPerfil label {
        font-size: 12px !important;
    }


    /* Botões */

    #modalPerfil form button[type="submit"] {
        width: 100% !important;
        padding: 9px 12px !important;
        font-size: 13px !important;
    }
}

</style>
<script>
    const produtos = <?php echo json_encode(
      $produtosBanco,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ); ?>;
  </script>
  <script src="../src/assets/js/script.js?v=2" defer></script>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
  <!-- ═══════════════════════════════════════════
     MODAL AUTH (login/cadastro)
═══════════════════════════════════════════ -->
  <div id="modalAuth" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">

    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-md mx-4">

      <button
        type="button"
        onclick="fecharModalAuth()"
        
        aria-label="Fechar" class="css-inline-1">
        ×
      </button>

      <h2 id="authTitulo" class="text-2xl font-bold mb-6 text-center">
        Entrar na conta
      </h2>

      <!-- LOGIN -->
      <div id="formLogin">

        <input
          id="loginEmail"
          type="email"
          placeholder="E-mail"
          class="mb-3 w-full p-2 border rounded-lg text-black">

        <input
          id="loginSenha"
          type="password"
          placeholder="Senha"
          class="mb-1 w-full p-2 border rounded-lg text-black">

        <p class="text-right text-xs mb-4">
          <span
            class="text-pink-600 cursor-pointer hover:underline font-semibold"
            onclick="abrirRecuperarSenha()">
            Esqueci minha senha
          </span>
        </p>

        <p
          id="erroLogin"
          class="text-red-500 text-sm mb-3 hidden"></p>

        <button
          type="button"
          onclick="fazerLogin()"
          class="w-full bg-pink-600 hover:bg-pink-700 text-white py-2 rounded-lg font-semibold">
          Entrar
        </button>

        <p class="text-center mt-4 text-sm">
          Não tem conta?

          <span
            class="text-pink-600 cursor-pointer font-semibold"
            onclick="mostrarCadastro()">
            Cadastre-se
          </span>
        </p>

      </div>

      <!-- CADASTRO -->
      <form
        id="formCadastro"
        class="hidden"
        action="/styleshop/src/php/cadastro.php"
        method="POST">

        <!-- NOME COMPLETO -->
        <input
          id="cadNome"
          name="nome"
          type="text"
          placeholder="Nome completo"
          class="mb-3 w-full p-2 border rounded-lg text-black"
          required>

        <!-- E-MAIL -->
        <input
          id="cadEmail"
          name="email"
          type="email"
          placeholder="E-mail"
          class="mb-3 w-full p-2 border rounded-lg text-black"
          required>

        <!-- CPF -->
        <input
          id="cadCpf"
          name="cpf"
          type="text"
          placeholder="CPF"
          maxlength="14"
          class="mb-3 w-full p-2 border rounded-lg text-black"
          required>

        <!-- DATA DE NASCIMENTO -->
        <label
          for="cadNascimento"
          class="block text-sm mb-1">
          Data de nascimento
        </label>

        <input
          id="cadNascimento"
          name="nascimento"
          type="date"
          class="mb-3 w-full p-2 border rounded-lg text-black"
          required>

        <!-- SENHA -->
        <input
          id="cadSenha"
          name="senha"
          type="password"
          placeholder="Senha forte (mín. 8 caracteres)"
          class="mb-1 w-full p-2 border rounded-lg text-black"
          oninput="avaliarForca(this.value)"
          minlength="8"
          required>

        <!-- BARRA DE FORÇA DA SENHA -->
        <div
          class="w-full h-2 rounded-full bg-gray-200 mb-1 css-inline-2"
          >
          <div
            id="forcaSenhaFill"
             class="css-inline-3"></div>
        </div>

        <!-- MENSAGEM DA FORÇA DA SENHA -->
        <p
          id="forcaSenhaMsg"
          class="text-xs mb-3 text-gray-400">
          Digite uma senha para ver a força
        </p>

        <!-- ERRO -->
        <p
          id="erroCadastro"
          class="text-red-500 text-sm mb-3 hidden"></p>

        <!-- CRIAR CONTA -->
        <button
          type="submit"
          class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold py-2 rounded-lg">
          Criar conta
        </button>

        <!-- VOLTAR PARA LOGIN -->
        <p class="text-center mt-4 text-sm">
          Já tem conta?

          <button
            type="button"
            onclick="mostrarLogin()"
            class="text-pink-600 font-semibold">
            Entrar
          </button>
        </p>

      </form>

    </div>
  </div>

  <!-- ═══════════════════════════════════════════
     MODAL DE RECUPERAÇÃO DE SENHA
═══════════════════════════════════════════ -->
      <div id="modalRecuperar" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-md mx-4">
          <button type="button" onclick="fecharModalRecuperar()" class="absolute top-4 right-5 text-gray-400 hover:text-gray-700 text-2xl">×</button>

          <h2 class="text-2xl font-bold mb-3 text-center">Recuperar senha</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-5">Digite seu e-mail cadastrado para receber as instruções de recuperação.</p>

          <input id="recuperarEmail" type="email" placeholder="Seu e-mail cadastrado" class="w-full p-2 border rounded-lg mb-4 text-black">
          <p id="msgRecuperar" class="text-sm mb-3 hidden"></p>

          <button onclick="enviarRecuperacao()" class="w-full bg-pink-600 hover:bg-pink-700 text-white py-2 rounded-lg font-semibold">Enviar link</button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════
     MODAL DETALHES DO PRODUTO
═══════════════════════════════════════════ -->
      <div id="modalProduto" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-lg mx-4 relative">
          <button onclick="fecharModalProduto()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
          <img id="detImg" src="" alt="" class="w-full h-64 object-cover rounded-xl mb-4">
          <h2 id="detNome" class="text-2xl font-bold mb-1"></h2>
          <p id="detCategoria" class="text-sm text-gray-500 mb-2"></p>
          <p id="detDesc" class="text-gray-600 dark:text-gray-300 text-sm mb-3"></p>
          <p id="detPreco" class="text-pink-600 text-2xl font-bold mb-4"></p>
          <button onclick="adicionarDoDetalhe()" class="w-full bg-pink-600 hover:bg-pink-700 text-white py-2 rounded-lg font-semibold">Adicionar ao carrinho</button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════
     CHECKOUT / FINALIZAR COMPRA
═══════════════════════════════════════════ -->
      <div id="modalFinalizar" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-4 relative max-h-[90vh] overflow-y-auto">

          <button
            onclick="fecharFinalizar()"
            class="absolute top-4 right-5 text-gray-400 hover:text-gray-700 text-2xl"
            aria-label="Fechar">
            &times;
          </button>

          <!-- TÍTULO -->
          <h2 id="checkoutTitulo" class="text-2xl font-bold mb-5 text-center">
          Finaliza compra
          </h2>

          <!-- INDICADOR DE ETAPAS -->
          <div class="flex items-center justify-center gap-2 mb-6 text-sm">
            <span id="etapa1Titulo" class="font-bold text-pink-600">1. Endereço</span>
            <span class="text-gray-400">→</span>
            <span id="etapa2Titulo" class="text-gray-400">2. Pagamento</span>
            <span class="text-gray-400">→</span>
            <span id="etapa3Titulo" class="text-gray-400">3. Revisão</span>
          </div>

          <!-- ═════════════════════════════
         ETAPA 1 - ENDEREÇO
    ═════════════════════════════ -->
          <div id="checkoutEtapa1">

            <h3 class="text-lg font-bold mb-4">📍 Endereço de entrega</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">
                  Nome do destinatário
                </label>
                <input id="checkoutNome" type="text"
                  placeholder="Nome de quem vai receber"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">CEP</label>
                <input id="checkoutCep" type="text"
                  placeholder="00000-000"
                  maxlength="9"
                  oninput="mascaraCep(this)"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">Estado</label>
                <input id="checkoutEstado" type="text"
                  placeholder="Ex: SP"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">Cidade</label>
                <input id="checkoutCidade" type="text"
                  placeholder="Sua cidade"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">Bairro</label>
                <input id="checkoutBairro" type="text"
                  placeholder="Seu bairro"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">
                  Rua / Avenida
                </label>
                <input id="checkoutRua" type="text"
                  placeholder="Nome da rua"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">Número</label>
                <input id="checkoutNumero" type="text"
                  placeholder="Ex: 123"
                  class="w-full p-2 border rounded-lg">
              </div>

              <div>
                <label class="block text-sm font-semibold mb-1">
                  Complemento
                </label>
                <input id="checkoutComp" type="text"
                  placeholder="Apto, bloco... (opcional)"
                  class="w-full p-2 border rounded-lg">
              </div>

            </div>

            <p id="erroCheckoutEndereco"
              class="text-red-500 text-sm mt-3 hidden"></p>

            <button
              onclick="irParaPagamento()"
              class="w-full bg-pink-600 hover:bg-pink-700 text-white py-3 rounded-lg font-semibold mt-5">
              Continuar para pagamento
            </button>

          </div>

          <!-- ═════════════════════════════
         ETAPA 2 - PAGAMENTO
    ═════════════════════════════ -->
          <div id="checkoutEtapa2" class="hidden">

            <h3 class="text-lg font-bold mb-4">💳 Forma de pagamento</h3>

            <div class="space-y-3">

              <label class="flex items-center gap-3 border rounded-xl p-4 cursor-pointer hover:border-pink-500">
                <input type="radio"
                  name="formaPagamento"
                  value="Pix"
                  class="w-4 h-4">
                <div>
                  <p class="font-semibold">💠 Pix</p>
                  <p class="text-sm text-gray-500">
                    Pagamento via Pix
                  </p>
                </div>
              </label>

              <label class="flex items-center gap-3 border rounded-xl p-4 cursor-pointer hover:border-pink-500">
                <input type="radio"
                  name="formaPagamento"
                  value="Cartão"
                  class="w-4 h-4">
                <div>
                  <p class="font-semibold">💳 Cartão</p>
                  <p class="text-sm text-gray-500">
                    Crédito ou débito
                  </p>
                </div>
              </label>

              <label class="flex items-center gap-3 border rounded-xl p-4 cursor-pointer hover:border-pink-500">
                <input type="radio"
                  name="formaPagamento"
                  value="Boleto"
                  class="w-4 h-4">
                <div>
                  <p class="font-semibold">🧾 Boleto</p>
                  <p class="text-sm text-gray-500">
                    Pagamento por boleto
                  </p>
                </div>
              </label>

            </div>

            <p id="erroCheckoutPagamento"
              class="text-red-500 text-sm mt-3 hidden"></p>

            <div class="flex gap-3 mt-5">

              <button
                onclick="voltarParaEndereco()"
                class="w-1/3 border border-gray-300 py-3 rounded-lg font-semibold">
                Voltar
              </button>

              <button
                onclick="irParaRevisao()"
                class="flex-1 bg-pink-600 hover:bg-pink-700 text-white py-3 rounded-lg font-semibold">
                Revisar pedido
              </button>

            </div>

          </div>

          <!-- ═════════════════════════════
         ETAPA 3 - REVISÃO
    ═════════════════════════════ -->
          <div id="checkoutEtapa3" class="hidden">

            <h3 class="text-lg font-bold mb-4">🧾 Revisar pedido</h3>

            <div class="border rounded-xl p-4 mb-4">
              <h4 class="font-bold mb-2">📍 Endereço de entrega</h4>
              <p id="revisaoEndereco" class="text-sm text-gray-600 dark:text-gray-300"></p>
            </div>

            <div class="border rounded-xl p-4 mb-4">
              <h4 class="font-bold mb-2">💳 Pagamento</h4>
              <p id="revisaoPagamento"
                class="text-sm text-gray-600 dark:text-gray-300"></p>
            </div>

            <div class="border rounded-xl p-4 mb-4">
              <h4 class="font-bold mb-3">🛍️ Produtos</h4>
              <div id="revisaoItens" class="space-y-2"></div>

              <div class="border-t mt-3 pt-3 flex justify-between">
                <span class="font-bold">Total</span>
                <span id="revisaoTotal"
                  class="font-bold text-pink-600"></span>
              </div>
            </div>

            <div class="flex gap-3">

              <button
                onclick="voltarParaPagamento()"
                class="w-1/3 border border-gray-300 py-3 rounded-lg font-semibold">
                Voltar
              </button>

              <button
                onclick="confirmarPedido()"
                class="flex-1 bg-pink-600 hover:bg-pink-700 text-white py-3 rounded-lg font-semibold">
                Confirmar pedido
              </button>

            </div>

          </div>

          <!-- ═════════════════════════════
         CONFIRMAÇÃO
    ═════════════════════════════ -->
          <div id="checkoutConfirmacao" class="hidden text-center">

            <div class="text-6xl mb-4">✅</div>

            <h2 class="text-2xl font-bold mb-2">
              Pedido confirmado!
            </h2>

            <p id="resumoPedido"
              class="text-gray-500 dark:text-gray-300 text-sm mb-6">
            </p>

            <button
              onclick="fecharFinalizar()"
              class="bg-pink-600 hover:bg-pink-700 text-white px-8 py-2 rounded-lg font-semibold">
              Voltar à loja
            </button>

          </div>

        </div>
      </div>

      <!-- ═══════════════════════════════════════════
     MODAL PERFIL
═══════════════════════════════════════════ -->
      <div id="modalPerfil" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl mx-4 overflow-hidden css-inline-4" >

          <!-- Header do perfil -->
          <div class="bg-pink-600 text-white px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center gap-3">
              <label for="inputFotoPerfil" class="cursor-pointer relative group" title="Clique para alterar sua foto">
                <div id="avatarCircle" class="w-12 h-12 rounded-full bg-white bg-opacity-30 flex items-center justify-center font-bold text-lg overflow-hidden border-2 border-white border-opacity-60 transition-all group-hover:border-opacity-100">
                </div>
                <div class="absolute inset-0 rounded-full bg-black bg-opacity-0 group-hover:bg-opacity-40 flex items-center justify-center transition-all">
                  <span class="text-white text-xs opacity-0 group-hover:opacity-100 font-semibold text-center leading-tight">📷<br>Alterar</span>
                </div>
                <input type="file" id="inputFotoPerfil" accept="image/*" class="hidden">
              </label>
              <div>
                <p id="perfilNomeHeader" class="font-bold leading-tight"></p>
                <p class="text-xs text-pink-100">Clique na foto para alterar</p>
              </div>
            </div>
            <button onclick="fecharPerfil()" class="text-white text-2xl hover:text-pink-200">&times;</button>
          </div>

          <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar de abas -->
            <nav class="w-44 flex-shrink-0 border-r border-gray-100 dark:border-gray-700 p-3 flex flex-col gap-1 bg-gray-50 dark:bg-gray-900">
             <button
  id="btn-aba-dados"
  class="perfil-tab active text-left px-3 py-2 rounded-lg text-sm font-semibold">
  <span class="icon-modal-perfil">👤</span> Meus dados
</button>

<button
  id="btn-aba-endereco"
  class="perfil-tab text-left px-3 py-2 rounded-lg text-sm font-semibold">
  <span class="icon-modal-perfil">📍</span> Endereço
</button>

<button
  id="btn-aba-pedidos"
  class="perfil-tab text-left px-3 py-2 rounded-lg text-sm font-semibold">
  <span class="icon-modal-perfil">📦</span> Meus pedidos
</button>

<button
  id="btn-aba-senha"
  class="perfil-tab text-left px-3 py-2 rounded-lg text-sm font-semibold">
  <span class="icon-modal-perfil">🔒</span> Senha
</button>

<button
  class="perfil-tab text-left px-3 py-2 rounded-lg text-sm font-semibold"
  onclick="abrirWhatsApp()">
  <span class="icon-modal-perfil">💬</span> Falar com loja
</button>
            </nav>

            <!-- Conteúdo das abas -->
            <div class="flex-1 overflow-y-auto p-5">

              <!-- ABA: MEUS DADOS -->
              <div id="aba-dados" class="perfil-conteudo">
                <h3 class="text-lg font-bold mb-4">Meus dados</h3>
                <form onsubmit="salvarDadosPessoais(event)">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label class="block text-sm font-semibold mb-1">Nome completo</label>
                      <input id="pNome" type="text" required>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">E-mail</label>
                      <input id="pEmail" type="email" required>
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Telefone / WhatsApp</label>
                      <input id="pTel" type="tel" placeholder="(00) 00000-0000">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Data de nascimento</label>
                      <input id="pNasc" type="date">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">CPF</label>
                      <input id="pCpf" type="text" placeholder="000.000.000-00" maxlength="14" oninput="mascaraCpf(this)">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Gênero</label>
                      <select id="pGenero">
                        <option value="">Prefiro não informar</option>
                        <option value="F">Feminino</option>
                        <option value="M">Masculino</option>
                        <option value="O">Outro</option>
                      </select>
                    </div>
                  </div>
                  <p id="erroDados" class="text-red-500 text-sm mb-2 hidden"></p>
                  <p id="msgDados" class="text-green-600 text-sm mb-3 hidden">✔ Dados salvos com sucesso!</p>
                  <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-lg font-semibold text-sm">Salvar alterações</button>
                </form>
              </div>

              <!-- ABA: ENDEREÇO -->
              <div id="aba-endereco" class="perfil-conteudo hidden">
                <h3 class="text-lg font-bold mb-4">Endereço de entrega</h3>
                <form onsubmit="salvarEndereco(event)">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div class="sm:col-span-2">
                      <label class="block text-sm font-semibold mb-1">Nome do destinatário</label>
                      <input id="eNome" type="text" placeholder="Nome de quem vai receber">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">CEP</label>
                      <input id="eCep" type="text" placeholder="00000-000" maxlength="9" oninput="mascaraCep(this);buscarCep(this.value)">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Estado</label>
                      <input id="eEstado" type="text" placeholder="Ex: SP">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Cidade</label>
                      <input id="eCidade" type="text" placeholder="Sua cidade">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Bairro</label>
                      <input id="eBairro" type="text" placeholder="Seu bairro">
                    </div>
                    <div class="sm:col-span-2">
                      <label class="block text-sm font-semibold mb-1">Rua / Avenida</label>
                      <input id="eRua" type="text" placeholder="Nome da rua">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Número</label>
                      <input id="eNumero" type="text" placeholder="Ex: 123">
                    </div>
                    <div>
                      <label class="block text-sm font-semibold mb-1">Complemento</label>
                      <input id="eComp" type="text" placeholder="Apto, bloco... (opcional)">
                    </div>
                  </div>
                  <p id="erroEndereco" class="text-red-500 text-sm mb-2 hidden"></p>
                  <p id="msgEndereco" class="text-green-600 text-sm mb-3 hidden">✔ Endereço salvo com sucesso!</p>
                  <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-lg font-semibold text-sm">Salvar endereço</button>
                </form>
              </div>

              <!-- ABA: PEDIDOS -->
              <div id="aba-pedidos" class="perfil-conteudo hidden">
                <h3 class="text-lg font-bold mb-4">Meus pedidos</h3>
                <div id="listaPedidos"></div>
              </div>

              <!-- ABA: SENHA -->
              <div id="aba-senha" class="perfil-conteudo hidden">
                <h3 class="text-lg font-bold mb-4">Alterar senha</h3>
                <form onsubmit="alterarSenha(event)"  class="css-inline-5">
                  <label class="block text-sm font-semibold mb-1">Senha atual</label>
                  <input id="senhaAtual" type="password" placeholder="Sua senha atual" class="mb-3">
                  <label class="block text-sm font-semibold mb-1">Nova senha</label>
                  <input id="senhaNova" type="password" placeholder="Nova senha (mín. 6 caracteres)" class="mb-3">
                  <label class="block text-sm font-semibold mb-1">Confirmar nova senha</label>
                  <input id="senhaConf" type="password" placeholder="Repita a nova senha" class="mb-3">
                  <p id="erroSenha" class="text-red-500 text-sm mb-3 hidden"></p>
                  <p id="okSenha" class="text-green-600 text-sm mb-3 hidden">✔ Senha alterada com sucesso!</p>
                  <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-lg font-semibold text-sm">Alterar senha</button>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════
     CABEÇALHO
═══════════════════════════════════════════ -->
      <header class="bg-gray-800 text-white p-4 flex justify-between items-center sticky top-0 z-40">
        <div class="flex items-center gap-3">
          <img src="../src/assets/img/logo.png" alt="StyleShop" width="40" height="40" class="w-10 h-10 object-contain">
          <h1 id="nomeLoja" class="text-2xl font-bold">StyleShop</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <div
  id="navFoto"
  onclick="abrirPerfil()"
  title="Ver perfil"
  class="w-8 h-8 rounded-full bg-pink-500 text-white flex items-center justify-center font-bold text-sm cursor-pointer hover:ring-2 hover:ring-pink-300 transition-all overflow-hidden">
</div>

<span id="nomeUsuario" class="text-sm hidden text-gray-300"></span>
          </div>
          <!-- <button id="btnPerfil" onclick="abrirPerfil()" aria-label="Ver perfil" class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600 text-sm hidden">👤 Perfil</button> -->
          <button id="btnAuth" onclick="abrirModalAuth()" aria-label="Entrar" class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600 text-sm">Entrar</button>
          <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === "admin"): ?>
            <a
              href="admin.php"
              id="btnAdmin"
              class="bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded text-sm">
              ⚙️ Administração
            </a>
          <?php endif; ?>
          <button id="btnSair" onclick="fazerLogout()" aria-label="Sair" class="bg-gray-600 px-3 py-1 rounded hover:bg-gray-500 text-sm hidden">Sair</button>
          <button
            id="temaBtn"
            aria-label="Alternar modo escuro"
            class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600">
            🌙
          </button>
          <button id="abrirCarrinho" aria-label="Abrir carrinho" class="bg-pink-600 px-4 py-2 rounded">
            🛒 (<span id="contador">0</span>)
          </button>
        </div>
      </header>

      <!-- ═══════════════════════════════════════════
     CARRINHO LATERAL
═══════════════════════════════════════════ -->
      <div id="carrinho" class="fixed top-0 right-0 w-80 h-full bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xl p-4 hidden overflow-y-auto z-50 css-inline-6" >
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">🛒 Carrinho</h2>
          <button id="fecharCarrinho" aria-label="Fechar carrinho" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="itensCarrinho" class="flex-1"></div>
        <div class="border-t pt-4 mt-4">
          <p class="font-bold text-lg mb-3">Total: R$ <span id="total">0,00</span></p>
          <button onclick="finalizarCompra()" class="w-full bg-pink-600 hover:bg-pink-700 text-white py-2 rounded-lg font-semibold">Finalizar compra</button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════
     FILTROS + BUSCA
═══════════════════════════════════════════ -->
      <div class="max-w-6xl mx-auto mt-6 px-4">
        <div class="flex flex-wrap gap-3 mb-4">
          <button class="filter-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg" onclick="filtrar('todos')">Todos</button>
          <button class="filter-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg" onclick="filtrar('calças')">Calças</button>
          <button class="filter-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg" onclick="filtrar('blusas')">Blusas</button>
          <button class="filter-btn bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg" onclick="filtrar('moletom')">Moletom</button>
          <button class="filter-btn bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg" onclick="filtrar('tênis')">Tênis</button>
        </div>
      </div>
      <div class="max-w-6xl mx-auto px-4 mt-2 mb-6">
        <input type="text" id="pesquisa" placeholder="🔍 Buscar produto..."
          class="w-full p-3 rounded-xl border border-gray-300 text-black focus:outline-none focus:ring-2 focus:ring-pink-500">
      </div>
      <!-- GALERIA -->
      <main id="galeria" class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 pb-16"></main>

      <!-- RODAPÉ -->
      <footer class="bg-gray-800 text-white text-center py-6">
        &copy; StyleShop — Todos os direitos reservados.
      </footer>

      <!-- Botão flutuante WhatsApp -->
      <a id="wppBtn" href="https://wa.me/556196027179?text=Olá,%20vim%20da%20loja%20StyleShop!" target="_blank" rel="noopener"
        class="fixed bottom-5 right-5 bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-full shadow-lg z-40 flex items-center gap-2 font-semibold text-sm">
        💬 Falar com a loja
      </a>
</body>

</html>
