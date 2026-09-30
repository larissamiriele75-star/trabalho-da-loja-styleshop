// ════════════════════════════════════════
// ESTADO GLOBAL
// ════════════════════════════════════════
let carrinhoItens = [];

// O usuário será carregado pela sessão PHP/MySQL
let usuarioAtual = null;

let produtoAtual = null;
let carrinhoAberto = false;

// ════════════════════════════════════════
// GALERIA
// ════════════════════════════════════════

function renderizarProdutos(lista) {

  const galeria = document.getElementById('galeria');

  galeria.innerHTML = '';

  if (!lista.length) {

    galeria.innerHTML =
      '<p class="col-span-3 text-center text-gray-400 py-16">Nenhum produto encontrado.</p>';

    return;
  }

  lista.forEach(p => {

    const esgotado = p.estoque <= 0;
    const destaque = p.destaque === 1;

    galeria.innerHTML += `
      <div class="card bg-white p-4 rounded-xl shadow hover:shadow-lg transition-shadow relative">

        ${destaque ? `
          <span class="absolute top-2 left-2 bg-yellow-400 text-black text-xs font-bold px-3 py-1 rounded-full z-10">
            ⭐ Destaque
          </span>
        ` : ''}

        ${esgotado ? `
          <span class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full z-10">
            Esgotado
          </span>
        ` : ''}

        <img
          src="${p.img}"
          alt="${p.nome}"
          class="rounded-lg mb-4 w-full h-48 object-cover cursor-pointer ${esgotado ? 'opacity-50' : ''}"
          onclick="window.location.href='../src/pages/produto.php?id=${p.id}'"
        >

        <h2 class="text-lg font-semibold">
          ${p.nome}
        </h2>

        <p class="text-sm text-gray-500 capitalize">
          ${p.categoria}
        </p>

        <p class="text-pink-600 text-lg font-bold mt-1">
          R$ ${p.preco.toFixed(2).replace('.', ',')}
        </p>

        <p class="text-sm mt-1 ${esgotado ? 'text-red-600 font-bold' : 'text-green-600'}">
          ${esgotado ? 'Sem estoque' : `Estoque: ${p.estoque}`}
        </p>

        <div class="flex gap-2 mt-3">

          <button
            onclick="window.location.href='../src/pages/produto.php?id=${p.id}'"
            class="flex-1 border border-pink-600 text-pink-600 py-2 rounded-lg text-sm hover:bg-pink-50"
          >
            Ver mais
          </button>

          <button
            ${esgotado
              ? 'disabled'
              : `onclick="adicionarAoCarrinho(${p.id})"`
            }
            class="flex-1 py-2 rounded-lg text-sm text-white
              ${esgotado
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-pink-600 hover:bg-pink-700'
              }"
          >
            ${esgotado ? 'Indisponível' : 'Comprar'}
          </button>

        </div>

      </div>
    `;
  });
}


renderizarProdutos(produtos);


// ════════════════════════════════════════
// FILTROS
// ════════════════════════════════════════

function filtrar(cat) {

  const texto =
    document
      .getElementById('pesquisa')
      .value
      .toLowerCase();

  renderizarProdutos(
    produtos.filter(
      p =>
        (cat === 'todos' || p.categoria === cat) &&
        p.nome.toLowerCase().includes(texto)
    )
  );
}


document
  .getElementById('pesquisa')
  .addEventListener('keyup', function () {

    renderizarProdutos(
      produtos.filter(
        p =>
          p.nome
            .toLowerCase()
            .includes(
              this.value.toLowerCase()
            )
      )
    );

  });


// ════════════════════════════════════════
// DETALHES DO PRODUTO
// ════════════════════════════════════════

function abrirDetalhe(id) {

  produtoAtual =
    produtos.find(p => p.id === id);

  if (!produtoAtual) {
    return;
  }

  document.getElementById('detImg').src =
    produtoAtual.img;

  document.getElementById('detImg').alt =
    produtoAtual.nome;

  document.getElementById('detNome').textContent =
    produtoAtual.nome;

  document.getElementById('detCategoria').textContent =
    produtoAtual.categoria;

  document.getElementById('detDesc').textContent =
    produtoAtual.desc;

  document.getElementById('detPreco').textContent =
    'R$ ' +
    produtoAtual.preco
      .toFixed(2)
      .replace('.', ',');

  document
    .getElementById('modalProduto')
    .classList
    .remove('hidden');
}


function fecharModalProduto() {

  document
    .getElementById('modalProduto')
    .classList
    .add('hidden');

  produtoAtual = null;
}


function adicionarDoDetalhe() {

  if (!produtoAtual) {
    return;
  }

  if (Number(produtoAtual.estoque) <= 0) {

    alert('Este produto está esgotado.');

    return;
  }

  adicionarAoCarrinho(
    produtoAtual.id
  );

  fecharModalProduto();
}


// ════════════════════════════════════════
// CARRINHO — MYSQL
// ════════════════════════════════════════

async function adicionarAoCarrinho(id) {

  const produto =
    produtos.find(p => p.id === id);

  if (!produto) {

    alert('Produto não encontrado.');

    return;
  }

  if (Number(produto.estoque) <= 0) {

    alert('Este produto está esgotado.');

    return;
  }

  try {

    const dados = new FormData();

    dados.append(
      'acao',
      'adicionar'
    );

    dados.append(
      'id_produto',
      id
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


    // USUÁRIO NÃO ESTÁ LOGADO
    if (resultado.login === false) {

      localStorage.setItem(
        'redirecionarAposLogin',
        'carrinho'
      );

      alert(
        'Faça login para adicionar produtos ao carrinho.'
      );

      abrirModalAuth();

      return;
    }


    // ERRO DO PHP
    if (!resultado.sucesso) {

      alert(
        resultado.mensagem
      );

      return;
    }


    // RECARREGA O ESTADO REAL DO MYSQL
    await carregarCarrinhoBanco();

    renderizarCarrinho();

    abrirCarrinhoLateral();

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


// ════════════════════════════════════════
// CARREGAR CARRINHO DO MYSQL
// ════════════════════════════════════════

async function carregarCarrinhoBanco() {

  try {

    const resposta =
      await fetch(
        '/styleshop/src/php/carrinho.php?acao=listar'
      );

    const resultado =
      await resposta.json();


    // NÃO ESTÁ LOGADO
    if (resultado.login === false) {

      carrinhoItens = [];

      const contador =
        document.getElementById(
          'contador'
        );

      if (contador) {
        contador.textContent = '0';
      }

      return;
    }


    if (!resultado.sucesso) {

      console.error(
        'Erro ao carregar carrinho:',
        resultado.mensagem
      );

      return;
    }


    // O ARRAY AGORA VEM DO MYSQL
    carrinhoItens =
      resultado.itens || [];


    // ATUALIZA CONTADOR
    const quantidadeTotal =
      carrinhoItens.reduce(
        (soma, item) =>
          soma +
          Number(item.qtd || 0),
        0
      );


    const contador =
      document.getElementById(
        'contador'
      );


    if (contador) {

      contador.textContent =
        String(quantidadeTotal);
    }


  } catch (erro) {

    console.error(
      'Erro ao carregar carrinho:',
      erro
    );
  }
}


// ════════════════════════════════════════
// DIMINUIR PRODUTO
// ════════════════════════════════════════

async function removerDoCarrinho(id) {

  try {

    const dados =
      new FormData();

    dados.append(
      'acao',
      'diminuir'
    );

    dados.append(
      'id_produto',
      id
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

      alert(
        'Faça login para alterar o carrinho.'
      );

      abrirModalAuth();

      return;
    }


    if (!resultado.sucesso) {

      alert(
        resultado.mensagem
      );

      return;
    }


    await carregarCarrinhoBanco();

    renderizarCarrinho();


  } catch (erro) {

    console.error(
      'Erro ao diminuir produto:',
      erro
    );

    alert(
      'Não foi possível atualizar o carrinho.'
    );
  }
}


// ════════════════════════════════════════
// RENDERIZAR CARRINHO
// ════════════════════════════════════════

function renderizarCarrinho() {

  const container =
    document.getElementById(
      'itensCarrinho'
    );

  const contador =
    document.getElementById(
      'contador'
    );

  const totalEl =
    document.getElementById(
      'total'
    );


  // MANTÉM SOMENTE PRODUTOS VÁLIDOS
  carrinhoItens =
    carrinhoItens.filter(item => {

      const produtoBanco =
        produtos.find(
          p => p.id === item.id
        );


      if (!produtoBanco) {
        return false;
      }


      return (
        Number(produtoBanco.estoque) > 0
      );

    });


  // CONTADOR
  const quantidadeTotal =
    carrinhoItens.reduce(
      (soma, item) =>
        soma +
        Number(item.qtd || 0),
      0
    );


  if (contador) {

    contador.textContent =
      String(quantidadeTotal);
  }


  if (!container) {
    return;
  }


  // CARRINHO VAZIO
  if (!carrinhoItens.length) {

    container.innerHTML =
      '<p class="text-gray-400 text-sm text-center py-8">Seu carrinho está vazio.</p>';


    if (totalEl) {
      totalEl.textContent =
        '0,00';
    }


    return;
  }


  // PRODUTOS DO CARRINHO
  let total = 0;


  container.innerHTML =
    carrinhoItens
      .map(item => {

        const qtd =
          Number(
            item.qtd || 0
          );

        const preco =
          Number(
            item.preco || 0
          );


        total +=
          preco * qtd;


        return `
          <div class="flex items-center gap-3 mb-4 border-b pb-3">

            <img
              src="${item.img}"
              alt="${item.nome}"
              class="w-14 h-14 object-cover rounded-lg"
            >

            <div class="flex-1">

              <p class="font-semibold text-sm">
                ${item.nome}
              </p>

              <p class="text-pink-600 text-sm">
                R$ ${preco
                  .toFixed(2)
                  .replace('.', ',')}
              </p>


              <div class="flex items-center gap-2 mt-1">

                <button
                  onclick="removerDoCarrinho(${item.id})"
                  class="w-6 h-6 bg-gray-200 rounded text-sm font-bold hover:bg-gray-300"
                >
                  −
                </button>


                <span class="text-sm font-semibold">
                  ${qtd}
                </span>


                <button
                  onclick="adicionarAoCarrinho(${item.id})"
                  class="w-6 h-6 bg-gray-200 rounded text-sm font-bold hover:bg-gray-300"
                >
                  +
                </button>

              </div>

            </div>

          </div>
        `;
      })
      .join('');


  if (totalEl) {

    totalEl.textContent =
      total
        .toFixed(2)
        .replace('.', ',');
  }
}


// ════════════════════════════════════════
// ABRIR CARRINHO
// ════════════════════════════════════════

function abrirCarrinhoLateral() {

  const el =
    document.getElementById(
      'carrinho'
    );

  if (!el) {
    return;
  }

  el.style.display =
    'flex';

  carrinhoAberto =
    true;
}


const botaoAbrirCarrinho =
  document.getElementById('abrirCarrinho');

if (botaoAbrirCarrinho) {

  botaoAbrirCarrinho.addEventListener(
    'click',
    () => {

      const el =
        document.getElementById(
          'carrinho'
        );

      if (!el) {
        return;
      }

      carrinhoAberto =
        !carrinhoAberto;

      el.style.display =
        carrinhoAberto
          ? 'flex'
          : 'none';
    }
  );
}

// ════════════════════════════════════════
// FECHAR CARRINHO
// ════════════════════════════════════════

const botaoFecharCarrinho =
  document.getElementById('fecharCarrinho');

if (botaoFecharCarrinho) {

  botaoFecharCarrinho.addEventListener(
    'click',
    () => {

      const carrinho =
        document.getElementById('carrinho');

      if (carrinho) {

        carrinho.style.display =
          'none';
      }

      carrinhoAberto =
        false;
    }
  );
}


// ════════════════════════════════════════
// INICIAR CARRINHO
// ════════════════════════════════════════

async function iniciarCarrinho() {

  await carregarCarrinhoBanco();

  renderizarCarrinho();
}


// ════════════════════════════════════════
// FINALIZAR COMPRA
// ════════════════════════════════════════

function finalizarCompra() {

  if (!usuarioAtual) {

    localStorage.setItem(
      'redirecionarAposLogin',
      'checkout'
    );

    alert(
      'Faça login para finalizar a compra!'
    );

    abrirModalAuth();

    return;
  }


  if (!carrinhoItens.length) {

    alert(
      'Seu carrinho está vazio!'
    );

    return;
  }


  const e =
    usuarioAtual.endereco || {};


  const checkoutNome =
    document.getElementById(
      'checkoutNome'
    );

  const checkoutCep =
    document.getElementById(
      'checkoutCep'
    );

  const checkoutEstado =
    document.getElementById(
      'checkoutEstado'
    );

  const checkoutCidade =
    document.getElementById(
      'checkoutCidade'
    );

  const checkoutBairro =
    document.getElementById(
      'checkoutBairro'
    );

  const checkoutRua =
    document.getElementById(
      'checkoutRua'
    );

  const checkoutNumero =
    document.getElementById(
      'checkoutNumero'
    );

  const checkoutComp =
    document.getElementById(
      'checkoutComp'
    );


  if (checkoutNome) {

    checkoutNome.value =
      e.nome || '';
  }


  if (checkoutCep) {

    checkoutCep.value =
      e.cep || '';
  }


  if (checkoutEstado) {

    checkoutEstado.value =
      e.estado || '';
  }


  if (checkoutCidade) {

    checkoutCidade.value =
      e.cidade || '';
  }


  if (checkoutBairro) {

    checkoutBairro.value =
      e.bairro || '';
  }


  if (checkoutRua) {

    checkoutRua.value =
      e.rua || '';
  }


  if (checkoutNumero) {

    checkoutNumero.value =
      e.numero || '';
  }


  if (checkoutComp) {

    checkoutComp.value =
      e.comp || '';
  }


  const etapa1 =
    document.getElementById(
      'checkoutEtapa1'
    );

  const etapa2 =
    document.getElementById(
      'checkoutEtapa2'
    );

  const etapa3 =
    document.getElementById(
      'checkoutEtapa3'
    );

  const etapa4 =
    document.getElementById(
      'checkoutEtapa4'
    );

  const confirmacao =
    document.getElementById(
      'checkoutConfirmacao'
    );

  const erroEndereco =
    document.getElementById(
      'erroCheckoutEndereco'
    );

  const erroPagamento =
    document.getElementById(
      'erroCheckoutPagamento'
    );

  const modalFinalizar =
    document.getElementById(
      'modalFinalizar'
    );


  if (etapa1) {

    etapa1.classList.remove(
      'hidden'
    );
  }


  if (etapa2) {

    etapa2.classList.add(
      'hidden'
    );
  }


  if (etapa3) {

    etapa3.classList.add(
      'hidden'
    );
  }


  if (etapa4) {

    etapa4.classList.add(
      'hidden'
    );
  }


  if (confirmacao) {

    confirmacao.classList.add(
      'hidden'
    );
  }


  if (erroEndereco) {

    erroEndereco.classList.add(
      'hidden'
    );
  }


  if (erroPagamento) {

    erroPagamento.classList.add(
      'hidden'
    );
  }


  if (modalFinalizar) {

    modalFinalizar.classList.remove(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// ENDEREÇO DO CHECKOUT — MYSQL
// ════════════════════════════════════════

async function irParaPagamento() {

  const campoNome =
    document.getElementById(
      'checkoutNome'
    );

  const campoCep =
    document.getElementById(
      'checkoutCep'
    );

  const campoEstado =
    document.getElementById(
      'checkoutEstado'
    );

  const campoCidade =
    document.getElementById(
      'checkoutCidade'
    );

  const campoBairro =
    document.getElementById(
      'checkoutBairro'
    );

  const campoRua =
    document.getElementById(
      'checkoutRua'
    );

  const campoNumero =
    document.getElementById(
      'checkoutNumero'
    );

  const campoComp =
    document.getElementById(
      'checkoutComp'
    );

  const erro =
    document.getElementById(
      'erroCheckoutEndereco'
    );


  if (
    !campoNome ||
    !campoCep ||
    !campoEstado ||
    !campoCidade ||
    !campoBairro ||
    !campoRua ||
    !campoNumero
  ) {

    console.error(
      'Um ou mais campos do checkout não foram encontrados.'
    );

    return;
  }


  const nome =
    campoNome.value.trim();

  const cep =
    campoCep.value.trim();

  const estado =
    campoEstado.value.trim();

  const cidade =
    campoCidade.value.trim();

  const bairro =
    campoBairro.value.trim();

  const rua =
    campoRua.value.trim();

  const numero =
    campoNumero.value.trim();

  const comp =
    campoComp
      ? campoComp.value.trim()
      : '';


  if (erro) {

    erro.classList.add(
      'hidden'
    );
  }


  // ════════════════════════════════════════
  // VALIDAÇÕES DO ENDEREÇO
  // ════════════════════════════════════════

  if (!nome) {

    if (erro) {

      erro.textContent =
        'Informe o nome do destinatário.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (
    !cep ||
    cep.replace(/\D/g, '').length !== 8
  ) {

    if (erro) {

      erro.textContent =
        'Informe um CEP válido.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!estado) {

    if (erro) {

      erro.textContent =
        'Informe o estado.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!cidade) {

    if (erro) {

      erro.textContent =
        'Informe a cidade.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!bairro) {

    if (erro) {

      erro.textContent =
        'Informe o bairro.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!rua) {

    if (erro) {

      erro.textContent =
        'Informe a rua ou avenida.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!numero) {

    if (erro) {

      erro.textContent =
        'Informe o número.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  // ════════════════════════════════════════
  // SALVAR ENDEREÇO NO MYSQL
  // ════════════════════════════════════════

  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'salvar_endereco'
    );


    dados.append(
      'nome_endereco',
      nome
    );


    dados.append(
      'cep',
      cep
    );


    dados.append(
      'estado',
      estado
    );


    dados.append(
      'cidade',
      cidade
    );


    dados.append(
      'bairro',
      bairro
    );


    dados.append(
      'rua',
      rua
    );


    dados.append(
      'numero',
      numero
    );


    dados.append(
      'complemento',
      comp
    );


    dados.append(
      'referencia',
      ''
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // SESSÃO EXPIRADA
    if (resultado.login === false) {

      alert(
        'Sua sessão expirou. Faça login novamente.'
      );

      usuarioAtual =
        null;

      atualizarUI();

      abrirModalAuth();

      return;
    }


    // ERRO AO SALVAR
    if (!resultado.sucesso) {

      if (erro) {

        erro.textContent =
          resultado.mensagem ||
          'Não foi possível salvar o endereço.';

        erro.classList.remove(
          'hidden'
        );
      }

      return;
    }


    // RECARREGAR PERFIL DO MYSQL
    await carregarPerfilBanco();


    // IR PARA PAGAMENTO
    const etapa1 =
      document.getElementById(
        'checkoutEtapa1'
      );

    const etapa2 =
      document.getElementById(
        'checkoutEtapa2'
      );


    if (etapa1) {

      etapa1.classList.add(
        'hidden'
      );
    }


    if (etapa2) {

      etapa2.classList.remove(
        'hidden'
      );
    }


  } catch (erroFetch) {

    console.error(
      'Erro ao salvar endereço:',
      erroFetch
    );


    if (erro) {

      erro.textContent =
        'Não foi possível salvar o endereço. Tente novamente.';

      erro.classList.remove(
        'hidden'
      );
    }
  }
}


// ════════════════════════════════════════
// FECHAR CHECKOUT
// ════════════════════════════════════════

function fecharFinalizar() {

  const modal =
    document.getElementById(
      'modalFinalizar'
    );


  if (modal) {

    modal.classList.add(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// VOLTAR PARA ENDEREÇO
// ════════════════════════════════════════

function voltarParaEndereco() {

  const etapa1 =
    document.getElementById(
      'checkoutEtapa1'
    );

  const etapa2 =
    document.getElementById(
      'checkoutEtapa2'
    );


  if (etapa2) {

    etapa2.classList.add(
      'hidden'
    );
  }


  if (etapa1) {

    etapa1.classList.remove(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// IR PARA REVISÃO
// ════════════════════════════════════════

function irParaRevisao() {

  const pagamento =
    document.querySelector(
      'input[name="formaPagamento"]:checked'
    );


  const erro =
    document.getElementById(
      'erroCheckoutPagamento'
    );


  if (erro) {

    erro.classList.add(
      'hidden'
    );
  }


  if (!pagamento) {

    if (erro) {

      erro.textContent =
        'Escolha uma forma de pagamento.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (
    !usuarioAtual ||
    !usuarioAtual.endereco
  ) {

    alert(
      'Não foi possível carregar o endereço.'
    );

    voltarParaEndereco();

    return;
  }


  const e =
    usuarioAtual.endereco;


  // ENDEREÇO
  const revisaoEndereco =
    document.getElementById(
      'revisaoEndereco'
    );


  if (revisaoEndereco) {

    revisaoEndereco.innerHTML = `

      <strong>
        ${e.nome || ''}
      </strong>

      <br>

      ${e.rua || ''}, ${e.numero || ''}
      ${e.comp ? ' - ' + e.comp : ''}

      <br>

      ${e.bairro || ''} -
      ${e.cidade || ''}/${e.estado || ''}

      <br>

      CEP: ${e.cep || ''}
    `;
  }


  // PAGAMENTO
  const revisaoPagamento =
    document.getElementById(
      'revisaoPagamento'
    );


  if (revisaoPagamento) {

    revisaoPagamento.textContent =
      pagamento.value;
  }


  // PRODUTOS
  let total = 0;


  const revisaoItens =
    document.getElementById(
      'revisaoItens'
    );


  if (revisaoItens) {

    revisaoItens.innerHTML =
      carrinhoItens
        .map(item => {

          const preco =
            Number(
              item.preco || 0
            );

          const qtd =
            Number(
              item.qtd || 0
            );

          const subtotal =
            preco * qtd;


          total +=
            subtotal;


          return `

            <div class="flex justify-between text-sm">

              <span>
                ${item.nome} × ${qtd}
              </span>

              <strong>
                R$ ${subtotal
                  .toFixed(2)
                  .replace('.', ',')}
              </strong>

            </div>

          `;

        })
        .join('');
  }


  // TOTAL
  const revisaoTotal =
    document.getElementById(
      'revisaoTotal'
    );


  if (revisaoTotal) {

    revisaoTotal.textContent =
      'R$ ' +
      total
        .toFixed(2)
        .replace('.', ',');
  }


  // TROCAR ETAPA
  const etapa2 =
    document.getElementById(
      'checkoutEtapa2'
    );

  const etapa3 =
    document.getElementById(
      'checkoutEtapa3'
    );


  if (etapa2) {

    etapa2.classList.add(
      'hidden'
    );
  }


  if (etapa3) {

    etapa3.classList.remove(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// VOLTAR PARA PAGAMENTO
// ════════════════════════════════════════

function voltarParaPagamento() {

  const etapa2 =
    document.getElementById(
      'checkoutEtapa2'
    );

  const etapa3 =
    document.getElementById(
      'checkoutEtapa3'
    );


  if (etapa3) {

    etapa3.classList.add(
      'hidden'
    );
  }


  if (etapa2) {

    etapa2.classList.remove(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// CONFIRMAR PEDIDO — MYSQL
// ════════════════════════════════════════

async function confirmarPedido() {

  // VERIFICAR LOGIN
  if (!usuarioAtual) {

    alert(
      'Você precisa estar logado.'
    );

    abrirModalAuth();

    return;
  }


  // VERIFICAR CARRINHO
  if (!carrinhoItens.length) {

    alert(
      'Seu carrinho está vazio.'
    );

    return;
  }


  // PEGAR FORMA DE PAGAMENTO
  const pagamento =
    document.querySelector(
      'input[name="formaPagamento"]:checked'
    );


  if (!pagamento) {

    alert(
      'Escolha uma forma de pagamento.'
    );

    voltarParaPagamento();

    return;
  }


  let botao = null;


  try {

    // BLOQUEAR BOTÃO PARA EVITAR PEDIDO DUPLICADO
    botao =
      document.querySelector(
        '[onclick="confirmarPedido()"]'
      );


    if (botao) {

      botao.disabled =
        true;

      botao.textContent =
        'Processando...';
    }


    // ENVIAR PEDIDO PARA PHP / MYSQL
    const dados =
      new FormData();


    dados.append(
      'acao',
      'finalizar'
    );


    dados.append(
      'forma_pagamento',
      pagamento.value
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/pedido.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // LOGIN EXPIRADO
    if (resultado.login === false) {

      usuarioAtual =
        null;

      atualizarUI();

      alert(
        'Sua sessão expirou. Faça login novamente.'
      );

      abrirModalAuth();

      return;
    }


    // ERRO AO CRIAR PEDIDO
    if (!resultado.sucesso) {

      alert(
        resultado.mensagem ||
        'Não foi possível finalizar o pedido.'
      );

      return;
    }


    console.log(
      'Pedido criado:',
      resultado.id_pedido
    );


    // O pedido.php limpa o carrinho no MySQL.
    // Agora buscamos novamente o estado real.
    await carregarCarrinhoBanco();

    renderizarCarrinho();


    // GARANTIR CONTADOR ATUALIZADO
    const contador =
      document.getElementById(
        'contador'
      );


    if (contador) {

      const quantidadeTotal =
        carrinhoItens.reduce(
          (soma, item) =>
            soma +
            Number(item.qtd || 0),
          0
        );

      contador.textContent =
        String(quantidadeTotal);
    }


    // RESUMO DO PEDIDO
    const resumoPedido =
      document.getElementById(
        'resumoPedido'
      );


    if (resumoPedido) {

      const total =
        Number(
          resultado.total || 0
        );


      resumoPedido.textContent =
        `Pedido #${resultado.id_pedido} realizado com sucesso! ` +
        `Total: R$ ${total
          .toFixed(2)
          .replace('.', ',')}`;
    }


    // FECHAR CARRINHO
    const carrinho =
      document.getElementById(
        'carrinho'
      );


    if (carrinho) {

      carrinho.style.display =
        'none';
    }


    carrinhoAberto =
      false;


    // ESCONDER ETAPAS
    [
      'checkoutEtapa1',
      'checkoutEtapa2',
      'checkoutEtapa3'
    ].forEach(id => {

      const etapa =
        document.getElementById(id);


      if (etapa) {

        etapa.classList.add(
          'hidden'
        );
      }
    });


    // MOSTRAR CONFIRMAÇÃO
    const etapa4 =
      document.getElementById(
        'checkoutEtapa4'
      );


    if (etapa4) {

      etapa4.classList.remove(
        'hidden'
      );
    }


    // ATUALIZAR MEUS PEDIDOS
    if (
      typeof carregarPedidosBanco ===
      'function'
    ) {

      await carregarPedidosBanco();
    }


    if (
      typeof renderizarPedidos ===
      'function'
    ) {

      renderizarPedidos();
    }


  } catch (erro) {

    console.error(
      'Erro ao finalizar pedido:',
      erro
    );


    alert(
      'Não foi possível finalizar a compra. Verifique a conexão e tente novamente.'
    );


  } finally {

    // REATIVAR BOTÃO
    if (!botao) {

      botao =
        document.querySelector(
          '[onclick="confirmarPedido()"]'
        );
    }


    if (botao) {

      botao.disabled =
        false;

      botao.textContent =
        'Confirmar pedido';
    }
  }
}


// ════════════════════════════════════════
// AUTH — LOGIN E CADASTRO
// ════════════════════════════════════════

function abrirModalAuth() {

  mostrarLogin();


  const modal =
    document.getElementById(
      'modalAuth'
    );


  if (modal) {

    modal.classList.remove(
      'hidden'
    );
  }
}


function fecharModalAuth() {

  const modal =
    document.getElementById(
      'modalAuth'
    );


  if (modal) {

    modal.classList.add(
      'hidden'
    );
  }
}


function mostrarLogin() {

  const titulo =
    document.getElementById(
      'authTitulo'
    );

  const formLogin =
    document.getElementById(
      'formLogin'
    );

  const formCadastro =
    document.getElementById(
      'formCadastro'
    );

  const erroLogin =
    document.getElementById(
      'erroLogin'
    );


  if (titulo) {

    titulo.textContent =
      'Entrar na conta';
  }


  if (formLogin) {

    formLogin.classList.remove(
      'hidden'
    );
  }


  if (formCadastro) {

    formCadastro.classList.add(
      'hidden'
    );
  }


  if (erroLogin) {

    erroLogin.classList.add(
      'hidden'
    );
  }
}


function mostrarCadastro() {

  const titulo =
    document.getElementById(
      'authTitulo'
    );

  const formLogin =
    document.getElementById(
      'formLogin'
    );

  const formCadastro =
    document.getElementById(
      'formCadastro'
    );

  const erroCadastro =
    document.getElementById(
      'erroCadastro'
    );


  if (titulo) {

    titulo.textContent =
      'Criar conta';
  }


  if (formLogin) {

    formLogin.classList.add(
      'hidden'
    );
  }


  if (formCadastro) {

    formCadastro.classList.remove(
      'hidden'
    );
  }


  if (erroCadastro) {

    erroCadastro.classList.add(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// VALIDAÇÕES
// ════════════════════════════════════════

function validarEmail(email) {

  return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(
    email
  );
}


function validarSenhaForte(senha) {

  const erros = [];


  if (senha.length < 8) {

    erros.push(
      'mínimo 8 caracteres'
    );
  }


  if (!/[A-Z]/.test(senha)) {

    erros.push(
      'uma letra maiúscula'
    );
  }


  if (!/[a-z]/.test(senha)) {

    erros.push(
      'uma letra minúscula'
    );
  }


  if (!/[0-9]/.test(senha)) {

    erros.push(
      'um número'
    );
  }


  return erros;
}


// ════════════════════════════════════════
// FORÇA DA SENHA
// ════════════════════════════════════════

function avaliarForca(senha) {

  const fill =
    document.getElementById(
      'forcaSenhaFill'
    );


  const msg =
    document.getElementById(
      'forcaSenhaMsg'
    );


  if (!fill || !msg) {
    return;
  }


  const temMaius =
    /[A-Z]/.test(senha);


  const temMinus =
    /[a-z]/.test(senha);


  const temNum =
    /[0-9]/.test(senha);


  const temEsp =
    /[^A-Za-z0-9]/.test(senha);


  const comprimento =
    senha.length >= 8;


  const pontos = [

    temMaius,
    temMinus,
    temNum,
    temEsp,
    comprimento

  ].filter(Boolean).length;


  const niveis = [

    {
      pct: '0%',
      cor: '#e5e7eb',
      txt: ''
    },

    {
      pct: '20%',
      cor: '#ef4444',
      txt: '❌ Muito fraca'
    },

    {
      pct: '40%',
      cor: '#f97316',
      txt: '⚠️ Fraca'
    },

    {
      pct: '60%',
      cor: '#eab308',
      txt: '🟡 Média'
    },

    {
      pct: '80%',
      cor: '#22c55e',
      txt: '✅ Forte'
    },

    {
      pct: '100%',
      cor: '#10b981',
      txt: '💪 Muito forte'
    }

  ];


  const n =
    niveis[pontos];


  fill.style.width =
    n.pct;


  fill.style.background =
    n.cor;


  msg.textContent =
    n.txt;


  msg.style.color =
    n.cor;
}


// ════════════════════════════════════════
// VALIDAÇÃO DE CPF
// ════════════════════════════════════════

function validarCpf(cpf) {

  cpf =
    cpf.replace(
      /\D/g,
      ''
    );


  if (
    cpf.length !== 11 ||
    /^(\d)\1{10}$/.test(cpf)
  ) {

    return false;
  }


  let soma = 0;


  for (
    let i = 0;
    i < 9;
    i++
  ) {

    soma +=
      parseInt(cpf[i]) *
      (10 - i);
  }


  let r =
    (soma * 10) % 11;


  if (
    r === 10 ||
    r === 11
  ) {

    r = 0;
  }


  if (
    r !==
    parseInt(cpf[9])
  ) {

    return false;
  }


  soma = 0;


  for (
    let i = 0;
    i < 10;
    i++
  ) {

    soma +=
      parseInt(cpf[i]) *
      (11 - i);
  }


  r =
    (soma * 10) % 11;


  if (
    r === 10 ||
    r === 11
  ) {

    r = 0;
  }


  return (
    r ===
    parseInt(cpf[10])
  );
}


// ════════════════════════════════════════
// MENSAGENS DE ERRO
// ════════════════════════════════════════

function mostrarErro(id, msg) {

  const el =
    document.getElementById(id);


  if (!el) {
    return;
  }


  el.textContent =
    msg;


  el.classList.remove(
    'hidden'
  );
}


function ocultarErro(id) {

  const el =
    document.getElementById(id);


  if (el) {

    el.classList.add(
      'hidden'
    );
  }
}

// ════════════════════════════════════════
// CADASTRO
// ════════════════════════════════════════

async function fazerCadastro() {

  const nome =
    document
      .getElementById('cadNome')
      .value
      .trim();


  const email =
    document
      .getElementById('cadEmail')
      .value
      .trim();


  const senha =
    document
      .getElementById('cadSenha')
      .value;

      const cpf =
  document
    .getElementById('cadCpf')
    .value
    .trim();

const nascimento =
  document
    .getElementById('cadNascimento')
    .value; 

  const erro =
    document.getElementById(
      'erroCadastro'
    );


  erro.classList.add(
    'hidden'
  );


  // CAMPOS OBRIGATÓRIOS
  if (
    !nome ||
    !email ||
    !senha
  ) {

    erro.textContent =
      'Preencha todos os campos.';

    erro.classList.remove(
      'hidden'
    );

    return;
  }


  // NOME E SOBRENOME
  if (
    nome.split(' ').filter(Boolean).length < 2
  ) {

    erro.textContent =
      'Informe nome e sobrenome.';

    erro.classList.remove(
      'hidden'
    );

    return;
  }


  // EMAIL
  if (
    !validarEmail(email)
  ) {

    erro.textContent =
      'Formato de e-mail inválido.';

    erro.classList.remove(
      'hidden'
    );

    return;
  }


  // SENHA
  const errosSenha =
    validarSenhaForte(
      senha
    );


  if (
    errosSenha.length > 0
  ) {

    erro.textContent =
      'Senha fraca. Adicione: ' +
      errosSenha.join(', ') +
      '.';


    erro.classList.remove(
      'hidden'
    );

    return;
  }


  // ════════════════════════════════════════
  // ENVIAR CADASTRO PARA PHP / MYSQL
  // ════════════════════════════════════════

  try {

    const dados =
      new FormData();


    dados.append(
      'nome',
      nome
    );


    dados.append(
      'email',
      email
    );


    dados.append(
      'senha',
      senha
    );

    dados.append(
  'cpf',
  cpf
);

dados.append(
  'nascimento',
  nascimento
);


    const resposta =
      await fetch(
        '/styleshop/src/php/cadastro.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    if (
      !resultado.sucesso
    ) {

      erro.textContent =
        resultado.mensagem ||
        'Não foi possível criar a conta.';


      erro.classList.remove(
        'hidden'
      );


      return;
    }


    // O CADASTRO FOI FEITO NO MYSQL.
    // O PHP TAMBÉM DEVE TER CRIADO A SESSÃO.
    if (resultado.usuario) {

      await logar(
        resultado.usuario
      );

    } else {

      // Se o PHP não devolver o objeto usuario,
      // recuperamos a sessão diretamente do MySQL/PHP.
      const sessaoValida =
        await verificarSessaoUsuario();


      if (sessaoValida) {

        atualizarUI();

        await carregarCarrinhoBanco();

        renderizarCarrinho();

        fecharModalAuth();

      } else {

        mostrarLogin();

        erro.textContent =
          'Cadastro realizado. Agora faça login.';

        erro.classList.remove(
          'hidden'
        );
      }
    }


  } catch (error) {

    console.error(
      'Erro no cadastro:',
      error
    );


    erro.textContent =
      'Não foi possível criar a conta.';


    erro.classList.remove(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// LOGIN
// ════════════════════════════════════════

async function fazerLogin() {

  const campoEmail =
    document.getElementById(
      'loginEmail'
    );

  const campoSenha =
    document.getElementById(
      'loginSenha'
    );

  const erro =
    document.getElementById(
      'erroLogin'
    );


  if (
    !campoEmail ||
    !campoSenha
  ) {

    console.error(
      'Campos de login não encontrados.'
    );

    return;
  }


  const email =
    campoEmail.value.trim();


  const senha =
    campoSenha.value;


  if (erro) {

    erro.classList.add(
      'hidden'
    );
  }


  if (
    !email ||
    !senha
  ) {

    if (erro) {

      erro.textContent =
        'Preencha todos os campos.';


      erro.classList.remove(
        'hidden'
      );
    }


    return;
  }


  try {

    const dados =
      new FormData();


    dados.append(
      'email',
      email
    );


    dados.append(
      'senha',
      senha
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/login.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    if (
      !resultado.sucesso
    ) {

      if (erro) {

        erro.textContent =
          resultado.mensagem ||
          'E-mail ou senha incorretos.';


        erro.classList.remove(
          'hidden'
        );
      }


      return;
    }


    await logar(
      resultado.usuario
    );


  } catch (error) {

    console.error(
      'Erro no login:',
      error
    );


    if (erro) {

      erro.textContent =
        'Não foi possível realizar o login.';


      erro.classList.remove(
        'hidden'
      );
    }
  }
}


// ════════════════════════════════════════
// LOGAR USUÁRIO
// ════════════════════════════════════════

async function logar(usuario) {

  usuarioAtual =
    usuario || null;


  // A sessão verdadeira está no PHP/MySQL.
  // Recarregamos o perfil para garantir
  // que o JS tenha os dados atualizados.
  const sessaoCarregada =
    await verificarSessaoUsuario();


  if (!sessaoCarregada) {

    usuarioAtual =
      usuario || null;
  }


  fecharModalAuth();

  atualizarUI();


  // Depois do login, recarrega o carrinho
  // pertencente ao usuário no MySQL.
  await carregarCarrinhoBanco();

  renderizarCarrinho();


  const destino =
    localStorage.getItem(
      'redirecionarAposLogin'
    );


  // ════════════════════════════════════════
  // USUÁRIO TENTOU FINALIZAR COMPRA
  // ANTES DE FAZER LOGIN
  // ════════════════════════════════════════

  if (
    destino === 'checkout'
  ) {

    localStorage.removeItem(
      'redirecionarAposLogin'
    );


    finalizarCompra();

    return;
  }


  // ════════════════════════════════════════
  // USUÁRIO TENTOU ADICIONAR PRODUTO
  // ANTES DE FAZER LOGIN
  // ════════════════════════════════════════

  if (
    destino === 'carrinho'
  ) {

    localStorage.removeItem(
      'redirecionarAposLogin'
    );


    abrirCarrinhoLateral();

    return;
  }
}


// ════════════════════════════════════════
// LOGOUT
// ════════════════════════════════════════

async function fazerLogout() {

  try {

    const resposta =
      await fetch(
        '/styleshop/src/php/logout.php',
        {
          method: 'POST',
          cache: 'no-store'
        }
      );


    if (!resposta.ok) {

      console.warn(
        'O PHP respondeu com status:',
        resposta.status
      );
    }


  } catch (erro) {

    console.error(
      'Erro ao encerrar sessão:',
      erro
    );
  }


  // Limpa somente os dados da interface.
  // Não apagamos manualmente o carrinho
  // do MySQL, pois ele pertence ao usuário.
  usuarioAtual =
    null;


  carrinhoItens =
    [];


  localStorage.removeItem(
    'redirecionarAposLogin'
  );


  renderizarCarrinho();

  atualizarUI();


  const carrinho =
    document.getElementById(
      'carrinho'
    );


  if (carrinho) {

    carrinho.style.display =
      'none';
  }


  carrinhoAberto =
    false;
}


// ════════════════════════════════════════
// ATUALIZAR INTERFACE
// ════════════════════════════════════════

function atualizarUI() {

  const logado =
    !!usuarioAtual;


  const btnAuth =
    document.getElementById(
      'btnAuth'
    );


  const btnSair =
    document.getElementById(
      'btnSair'
    );


  if (btnAuth) {

    btnAuth
      .classList
      .toggle(
        'hidden',
        logado
      );
  }


  if (btnSair) {

    btnSair
      .classList
      .toggle(
        'hidden',
        !logado
      );
  }


  const nomeSpan =
    document.getElementById(
      'nomeUsuario'
    );


  if (logado) {

    if (nomeSpan) {

      const nomeUsuario =
        usuarioAtual.nome || 'Usuário';


      nomeSpan.textContent =
        'Olá, ' +
        nomeUsuario
          .split(' ')[0] +
        '!';


      nomeSpan.classList.remove(
        'hidden'
      );
    }


   if (
  usuarioAtual &&
  (
    usuarioAtual.foto_perfil ||
    usuarioAtual.foto
  )
) {

  atualizarPreviewFoto(
    usuarioAtual.foto_perfil ||
    usuarioAtual.foto
  );
}


  } else {

    if (nomeSpan) {

      nomeSpan.textContent =
        '';


      nomeSpan.classList.add(
        'hidden'
      );
    }


    const navFoto =
      document.getElementById(
        'navFoto'
      );


    if (navFoto) {

      navFoto.innerHTML =
        '👤';


      navFoto.style.display =
        'none';
    }


    const avatarCircle =
      document.getElementById(
        'avatarCircle'
      );


    if (avatarCircle) {

      avatarCircle.innerHTML =
        '👤';
    }
  }
}


// ════════════════════════════════════════
// VERIFICAR SESSÃO DO USUÁRIO
// ════════════════════════════════════════

async function verificarSessaoUsuario() {

  try {

    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php?acao=buscar',
        {
          method: 'GET',
          cache: 'no-store'
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // NÃO EXISTE SESSÃO PHP ATIVA
    if (
      resultado.login === false ||
      !resultado.sucesso
    ) {

      usuarioAtual =
        null;


      atualizarUI();

      return false;
    }


    const u =
      resultado.usuario;


    const e =
      resultado.endereco;


    if (!u) {

      usuarioAtual =
        null;


      atualizarUI();

      return false;
    }


    // USUÁRIO VEM DO MYSQL
    usuarioAtual = {

      id:
        Number(
          u.id_usuario
        ),

      id_usuario:
        Number(
          u.id_usuario
        ),

      nome:
        u.nome || '',

      nome_social:
        u.nome_social || '',

      email:
        u.email || '',

      tel:
        u.telefone || '',

      nasc:
        u.nascimento || '',

      cpf:
        u.cpf || '',

      genero:
        u.pronomes || '',

      foto:
        u.foto_perfil || '',

      tipo:
        u.tipo_usuario ||
        'customer',

      endereco:
        e
          ? {

              id:
                Number(
                  e.id_endereco
                ),

              nome:
                e.nome_endereco || '',

              cep:
                e.cep || '',

              estado:
                e.estado || '',

              cidade:
                e.cidade || '',

              bairro:
                e.bairro || '',

              rua:
                e.rua || '',

              numero:
                e.numero || '',

              comp:
                e.complemento || '',

              referencia:
                e.referencia || ''

            }
          : {}
    };


    atualizarUI();

    return true;


  } catch (erro) {

    console.error(
      'Erro ao verificar sessão:',
      erro
    );


    usuarioAtual =
      null;


    atualizarUI();

    return false;
  }
}


// ════════════════════════════════════════
// CARREGAR PERFIL DO MYSQL
// ════════════════════════════════════════

async function carregarPerfilBanco() {

  try {

    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php?acao=buscar',
        {
          method: 'GET',
          cache: 'no-store'
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    if (
      resultado.login === false
    ) {

      usuarioAtual =
        null;


      atualizarUI();

      return false;
    }


    if (
      !resultado.sucesso
    ) {

      console.error(
        'Erro ao carregar perfil:',
        resultado.mensagem
      );

      return false;
    }


    const u =
      resultado.usuario;


    const e =
      resultado.endereco;


    if (!u) {

      console.error(
        'O PHP não retornou os dados do usuário.'
      );

      return false;
    }


    usuarioAtual = {

      ...usuarioAtual,

      id:
        Number(
          u.id_usuario
        ),

      id_usuario:
        Number(
          u.id_usuario
        ),

      nome:
        u.nome || '',

      nome_social:
        u.nome_social || '',

      email:
        u.email || '',

      tel:
        u.telefone || '',

      nasc:
        u.nascimento || '',

      cpf:
        u.cpf || '',

      genero:
        u.pronomes || '',

      foto:
        u.foto_perfil || '',

      tipo:
        u.tipo_usuario ||
        'customer',

      endereco:
        e
          ? {

              id:
                Number(
                  e.id_endereco
                ),

              nome:
                e.nome_endereco || '',

              cep:
                e.cep || '',

              estado:
                e.estado || '',

              cidade:
                e.cidade || '',

              bairro:
                e.bairro || '',

              rua:
                e.rua || '',

              numero:
                e.numero || '',

              comp:
                e.complemento || '',

              referencia:
                e.referencia || ''

            }
          : {}
    };


    atualizarUI();

    return true;


  } catch (erro) {

    console.error(
      'Erro ao buscar perfil no MySQL:',
      erro
    );


    return false;
  }
}


// ════════════════════════════════════════
// PERFIL
// ════════════════════════════════════════

async function abrirPerfil() {

  if (!usuarioAtual) {

    abrirModalAuth();

    return;
  }


  const perfilCarregado =
    await carregarPerfilBanco();


  if (!perfilCarregado) {

    alert(
      'Não foi possível carregar seu perfil.'
    );

    return;
  }


  if (
    typeof carregarPedidosBanco ===
    'function'
  ) {

    await carregarPedidosBanco();


    if (
      typeof renderizarPedidos ===
      'function'
    ) {

      renderizarPedidos();
    }
  }


  // ════════════════════════════════════════
  // DADOS PESSOAIS
  // ════════════════════════════════════════

  const pNome =
    document.getElementById(
      'pNome'
    );

  const pEmail =
    document.getElementById(
      'pEmail'
    );

  const pTel =
    document.getElementById(
      'pTel'
    );

  const pNasc =
    document.getElementById(
      'pNasc'
    );

  const pCpf =
    document.getElementById(
      'pCpf'
    );

  const pGenero =
    document.getElementById(
      'pGenero'
    );


  if (pNome) {

    pNome.value =
      usuarioAtual.nome || '';
  }


  if (pEmail) {

    pEmail.value =
      usuarioAtual.email || '';
  }


  if (pTel) {

    pTel.value =
      usuarioAtual.tel || '';
  }


  if (pNasc) {

    pNasc.value =
      usuarioAtual.nasc || '';
  }


  if (pCpf) {

    pCpf.value =
      usuarioAtual.cpf || '';
  }


  if (pGenero) {

    pGenero.value =
      usuarioAtual.genero || '';
  }


  // ════════════════════════════════════════
  // ENDEREÇO
  // ════════════════════════════════════════

  const e =
    usuarioAtual.endereco || {};


  const eNome =
    document.getElementById(
      'eNome'
    );

  const eCep =
    document.getElementById(
      'eCep'
    );

  const eEstado =
    document.getElementById(
      'eEstado'
    );

  const eCidade =
    document.getElementById(
      'eCidade'
    );

  const eBairro =
    document.getElementById(
      'eBairro'
    );

  const eRua =
    document.getElementById(
      'eRua'
    );

  const eNumero =
    document.getElementById(
      'eNumero'
    );

  const eComp =
    document.getElementById(
      'eComp'
    );


  if (eNome) {

    eNome.value =
      e.nome || '';
  }


  if (eCep) {

    eCep.value =
      e.cep || '';
  }


  if (eEstado) {

    eEstado.value =
      e.estado || '';
  }


  if (eCidade) {

    eCidade.value =
      e.cidade || '';
  }


  if (eBairro) {

    eBairro.value =
      e.bairro || '';
  }


  if (eRua) {

    eRua.value =
      e.rua || '';
  }


  if (eNumero) {

    eNumero.value =
      e.numero || '';
  }


  if (eComp) {

    eComp.value =
      e.comp || '';
  }


  // FOTO
if (usuarioAtual.foto) {
  atualizarPreviewFoto(
    usuarioAtual.foto
  );
}


  // NOME NO CABEÇALHO DO PERFIL
  const perfilNome =
    document.getElementById(
     'perfilNomeHeader'
    );


  if (perfilNome) {

    perfilNome.textContent =
      usuarioAtual.nome || '';
  }


  // ABRIR MODAL/PÁGINA DO PERFIL
  const modalPerfil =
    document.getElementById(
      'modalPerfil'
    );


  if (modalPerfil) {

    modalPerfil.classList.remove(
      'hidden'
    );
  }
}

// ════════════════════════════════════════
// FECHAR PERFIL
// ════════════════════════════════════════

function fecharPerfil() {

  const modalPerfil =
    document.getElementById(
      'modalPerfil'
    );


  if (modalPerfil) {

    modalPerfil.classList.add(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// SALVAR DADOS PESSOAIS
// ════════════════════════════════════════

async function salvarDadosPessoais(e) {

  // Evita que o formulário recarregue a página
  if (
    e &&
    typeof e.preventDefault === 'function'
  ) {

    e.preventDefault();
  }


  if (!usuarioAtual) {

    alert(
      'Você precisa estar logado.'
    );

    abrirModalAuth();

    return;
  }


  const campoNome =
    document.getElementById(
      'pNome'
    );

  const campoEmail =
    document.getElementById(
      'pEmail'
    );

  const campoTel =
    document.getElementById(
      'pTel'
    );

  const campoNasc =
    document.getElementById(
      'pNasc'
    );

  const campoCpf =
    document.getElementById(
      'pCpf'
    );

  const campoGenero =
    document.getElementById(
      'pGenero'
    );


  const errEl =
    document.getElementById(
      'erroPerfilDados'
    );


  const sucessoEl =
    document.getElementById(
      'sucessoPerfilDados'
    );


  if (errEl) {

    errEl.classList.add(
      'hidden'
    );

    errEl.textContent =
      '';
  }


  if (sucessoEl) {

    sucessoEl.classList.add(
      'hidden'
    );

    sucessoEl.textContent =
      '';
  }


  if (
    !campoNome ||
    !campoEmail
  ) {

    console.error(
      'Campos obrigatórios do perfil não foram encontrados.'
    );

    return;
  }


  const nome =
    campoNome.value.trim();


  const email =
    campoEmail.value.trim();


  const telefone =
    campoTel
      ? campoTel.value.trim()
      : '';


  const nascimento =
    campoNasc
      ? campoNasc.value
      : '';


  const cpf =
    campoCpf
      ? campoCpf.value.trim()
      : '';


  const pronomes =
    campoGenero
      ? campoGenero.value.trim()
      : '';


  // ════════════════════════════════════════
  // VALIDAR NOME
  // ════════════════════════════════════════

  if (!nome) {

    if (errEl) {

      errEl.textContent =
        'Nome obrigatório.';

      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Nome obrigatório.'
      );
    }

    return;
  }


  if (
    nome
      .split(' ')
      .filter(Boolean)
      .length < 2
  ) {

    if (errEl) {

      errEl.textContent =
        'Informe nome e sobrenome.';

      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Informe nome e sobrenome.'
      );
    }

    return;
  }


  // ════════════════════════════════════════
  // VALIDAR EMAIL
  // ════════════════════════════════════════

  if (!email) {

    if (errEl) {

      errEl.textContent =
        'E-mail obrigatório.';

      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'E-mail obrigatório.'
      );
    }

    return;
  }


  if (!validarEmail(email)) {

    if (errEl) {

      errEl.textContent =
        'Informe um e-mail válido.';

      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Informe um e-mail válido.'
      );
    }

    return;
  }


  // ════════════════════════════════════════
  // VALIDAR CPF
  // ════════════════════════════════════════

  if (
    cpf &&
    !validarCpf(cpf)
  ) {

    if (errEl) {

      errEl.textContent =
        'Informe um CPF válido.';

      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Informe um CPF válido.'
      );
    }

    return;
  }


  // ════════════════════════════════════════
  // VALIDAR TELEFONE
  // ════════════════════════════════════════

  if (telefone) {

    const telefoneNumeros =
      telefone.replace(
        /\D/g,
        ''
      );


    if (
      telefoneNumeros.length < 10 ||
      telefoneNumeros.length > 11
    ) {

      if (errEl) {

        errEl.textContent =
          'Informe um telefone válido.';

        errEl.classList.remove(
          'hidden'
        );

      } else {

        alert(
          'Informe um telefone válido.'
        );
      }

      return;
    }
  }


  // ════════════════════════════════════════
  // VALIDAR DATA DE NASCIMENTO
  // ════════════════════════════════════════

  if (nascimento) {

    const dataNascimento =
      new Date(
        nascimento + 'T00:00:00'
      );


    const hoje =
      new Date();


    if (
      Number.isNaN(
        dataNascimento.getTime()
      )
    ) {

      if (errEl) {

        errEl.textContent =
          'Informe uma data de nascimento válida.';

        errEl.classList.remove(
          'hidden'
        );
      }

      return;
    }


    if (
      dataNascimento > hoje
    ) {

      if (errEl) {

        errEl.textContent =
          'A data de nascimento não pode estar no futuro.';

        errEl.classList.remove(
          'hidden'
        );
      }

      return;
    }
  }


  // ════════════════════════════════════════
  // ENVIAR PARA O MYSQL
  // ════════════════════════════════════════

  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'atualizar_dados'
    );


    dados.append(
      'nome',
      nome
    );


    dados.append(
      'email',
      email
    );


    dados.append(
      'telefone',
      telefone
    );


    dados.append(
      'nascimento',
      nascimento
    );


    dados.append(
      'cpf',
      cpf
    );


    dados.append(
      'pronomes',
      pronomes
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // ════════════════════════════════════════
    // SESSÃO EXPIRADA
    // ════════════════════════════════════════

    if (
      resultado.login === false
    ) {

      usuarioAtual =
        null;


      atualizarUI();


      alert(
        'Sua sessão expirou. Faça login novamente.'
      );


      fecharPerfil();

      abrirModalAuth();

      return;
    }


    // ════════════════════════════════════════
    // ERRO RETORNADO PELO PHP
    // ════════════════════════════════════════

    if (
      !resultado.sucesso
    ) {

      if (errEl) {

        errEl.textContent =
          resultado.mensagem ||
          'Não foi possível atualizar seus dados.';


        errEl.classList.remove(
          'hidden'
        );

      } else {

        alert(
          resultado.mensagem ||
          'Não foi possível atualizar seus dados.'
        );
      }

      return;
    }


    // ════════════════════════════════════════
    // RECARREGAR DADOS DO MYSQL
    // ════════════════════════════════════════

    const perfilAtualizado =
      await carregarPerfilBanco();


    if (!perfilAtualizado) {

      throw new Error(
        'Os dados foram salvos, mas não foi possível recarregar o perfil.'
      );
    }


    // ════════════════════════════════════════
    // ATUALIZAR CAMPOS DA TELA
    // ════════════════════════════════════════

    if (campoNome) {

      campoNome.value =
        usuarioAtual.nome || '';
    }


    if (campoEmail) {

      campoEmail.value =
        usuarioAtual.email || '';
    }


    if (campoTel) {

      campoTel.value =
        usuarioAtual.tel || '';
    }


    if (campoNasc) {

      campoNasc.value =
        usuarioAtual.nasc || '';
    }


    if (campoCpf) {

      campoCpf.value =
        usuarioAtual.cpf || '';
    }


    if (campoGenero) {

      campoGenero.value =
        usuarioAtual.genero || '';
    }


    atualizarUI();


    // ════════════════════════════════════════
    // MENSAGEM DE SUCESSO
    // ════════════════════════════════════════

    if (sucessoEl) {

      sucessoEl.textContent =
        'Dados atualizados com sucesso!';


      sucessoEl.classList.remove(
        'hidden'
      );


      setTimeout(
        () => {

          sucessoEl.classList.add(
            'hidden'
          );

        },
        3000
      );

    } else {

      alert(
        'Dados atualizados com sucesso!'
      );
    }


  } catch (erro) {

    console.error(
      'Erro ao salvar dados pessoais:',
      erro
    );


    if (errEl) {

      errEl.textContent =
        'Não foi possível salvar seus dados. Tente novamente.';


      errEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Não foi possível salvar seus dados. Tente novamente.'
      );
    }
  }
}


// ════════════════════════════════════════
// SALVAR ENDEREÇO DO PERFIL
// ════════════════════════════════════════

async function salvarEndereco(e) {

  if (
    e &&
    typeof e.preventDefault === 'function'
  ) {

    e.preventDefault();
  }


  if (!usuarioAtual) {

    alert(
      'Você precisa estar logado.'
    );

    abrirModalAuth();

    return;
  }


  const campoNome =
    document.getElementById(
      'eNome'
    );

  const campoCep =
    document.getElementById(
      'eCep'
    );

  const campoEstado =
    document.getElementById(
      'eEstado'
    );

  const campoCidade =
    document.getElementById(
      'eCidade'
    );

  const campoBairro =
    document.getElementById(
      'eBairro'
    );

  const campoRua =
    document.getElementById(
      'eRua'
    );

  const campoNumero =
    document.getElementById(
      'eNumero'
    );

  const campoComp =
    document.getElementById(
      'eComp'
    );


  const erroEl =
    document.getElementById(
      'erroPerfilEndereco'
    );


  const sucessoEl =
    document.getElementById(
      'sucessoPerfilEndereco'
    );


  if (erroEl) {

    erroEl.classList.add(
      'hidden'
    );

    erroEl.textContent =
      '';
  }


  if (sucessoEl) {

    sucessoEl.classList.add(
      'hidden'
    );

    sucessoEl.textContent =
      '';
  }


  if (
    !campoNome ||
    !campoCep ||
    !campoEstado ||
    !campoCidade ||
    !campoBairro ||
    !campoRua ||
    !campoNumero
  ) {

    console.error(
      'Campos do endereço não encontrados.'
    );

    return;
  }


  const nome =
    campoNome.value.trim();


  const cep =
    campoCep.value.trim();


  const estado =
    campoEstado.value
      .trim()
      .toUpperCase();


  const cidade =
    campoCidade.value.trim();


  const bairro =
    campoBairro.value.trim();


  const rua =
    campoRua.value.trim();


  const numero =
    campoNumero.value.trim();


  const complemento =
    campoComp
      ? campoComp.value.trim()
      : '';


  // ════════════════════════════════════════
  // VALIDAÇÕES
  // ════════════════════════════════════════

  if (!nome) {

    if (erroEl) {

      erroEl.textContent =
        'Informe um nome para o endereço.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (
    !cep ||
    cep.replace(/\D/g, '').length !== 8
  ) {

    if (erroEl) {

      erroEl.textContent =
        'Informe um CEP válido com 8 números.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (
    !estado ||
    estado.length !== 2
  ) {

    if (erroEl) {

      erroEl.textContent =
        'Informe a UF com 2 letras. Exemplo: DF.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!cidade) {

    if (erroEl) {

      erroEl.textContent =
        'Informe a cidade.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!bairro) {

    if (erroEl) {

      erroEl.textContent =
        'Informe o bairro.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!rua) {

    if (erroEl) {

      erroEl.textContent =
        'Informe a rua ou avenida.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  if (!numero) {

    if (erroEl) {

      erroEl.textContent =
        'Informe o número do endereço.';

      erroEl.classList.remove(
        'hidden'
      );
    }

    return;
  }


  // ════════════════════════════════════════
  // SALVAR NO MYSQL
  // ════════════════════════════════════════

  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'salvar_endereco'
    );


    dados.append(
      'nome_endereco',
      nome
    );


    dados.append(
      'cep',
      cep
    );


    dados.append(
      'estado',
      estado
    );


    dados.append(
      'cidade',
      cidade
    );


    dados.append(
      'bairro',
      bairro
    );


    dados.append(
      'rua',
      rua
    );


    dados.append(
      'numero',
      numero
    );


    dados.append(
      'complemento',
      complemento
    );


    dados.append(
      'referencia',
      ''
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // SESSÃO EXPIRADA
    if (
      resultado.login === false
    ) {

      usuarioAtual =
        null;


      atualizarUI();


      alert(
        'Sua sessão expirou. Faça login novamente.'
      );


      fecharPerfil();

      abrirModalAuth();

      return;
    }


    // ERRO DO PHP
    if (
      !resultado.sucesso
    ) {

      if (erroEl) {

        erroEl.textContent =
          resultado.mensagem ||
          'Não foi possível salvar o endereço.';


        erroEl.classList.remove(
          'hidden'
        );

      } else {

        alert(
          resultado.mensagem ||
          'Não foi possível salvar o endereço.'
        );
      }

      return;
    }


    // RECARREGAR PERFIL
    await carregarPerfilBanco();


    // ATUALIZAR CAMPOS
    const enderecoAtual =
      usuarioAtual &&
      usuarioAtual.endereco
        ? usuarioAtual.endereco
        : {};


    campoNome.value =
      enderecoAtual.nome || '';


    campoCep.value =
      enderecoAtual.cep || '';


    campoEstado.value =
      enderecoAtual.estado || '';


    campoCidade.value =
      enderecoAtual.cidade || '';


    campoBairro.value =
      enderecoAtual.bairro || '';


    campoRua.value =
      enderecoAtual.rua || '';


    campoNumero.value =
      enderecoAtual.numero || '';


    if (campoComp) {

      campoComp.value =
        enderecoAtual.comp || '';
    }


    // SUCESSO
    if (sucessoEl) {

      sucessoEl.textContent =
        'Endereço salvo com sucesso!';


      sucessoEl.classList.remove(
        'hidden'
      );


      setTimeout(
        () => {

          sucessoEl.classList.add(
            'hidden'
          );

        },
        3000
      );

    } else {

      alert(
        'Endereço salvo com sucesso!'
      );
    }


  } catch (erro) {

    console.error(
      'Erro ao salvar endereço:',
      erro
    );


    if (erroEl) {

      erroEl.textContent =
        'Não foi possível salvar o endereço. Tente novamente.';


      erroEl.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Não foi possível salvar o endereço.'
      );
    }
  }
}


// ════════════════════════════════════════
// FORMATAR CEP
// ════════════════════════════════════════

function formatarCep(valor) {

  const numeros =
    String(valor || '')
      .replace(
        /\D/g,
        ''
      )
      .slice(
        0,
        8
      );


  if (
    numeros.length <= 5
  ) {

    return numeros;
  }


  return (
    numeros.slice(0, 5) +
    '-' +
    numeros.slice(5)
  );
}


// ════════════════════════════════════════
// FORMATAR CPF
// ════════════════════════════════════════

function formatarCpf(valor) {

  let numeros =
    String(valor || '')
      .replace(
        /\D/g,
        ''
      )
      .slice(
        0,
        11
      );


  numeros =
    numeros.replace(
      /(\d{3})(\d)/,
      '$1.$2'
    );


  numeros =
    numeros.replace(
      /(\d{3})(\d)/,
      '$1.$2'
    );


  numeros =
    numeros.replace(
      /(\d{3})(\d{1,2})$/,
      '$1-$2'
    );


  return numeros;
}


// ════════════════════════════════════════
// FORMATAR TELEFONE
// ════════════════════════════════════════

function formatarTelefone(valor) {

  const numeros =
    String(valor || '')
      .replace(
        /\D/g,
        ''
      )
      .slice(
        0,
        11
      );


  if (
    numeros.length <= 2
  ) {

    return numeros;
  }


  if (
    numeros.length <= 6
  ) {

    return (
      '(' +
      numeros.slice(0, 2) +
      ') ' +
      numeros.slice(2)
    );
  }


  if (
    numeros.length <= 10
  ) {

    return (
      '(' +
      numeros.slice(0, 2) +
      ') ' +
      numeros.slice(2, 6) +
      '-' +
      numeros.slice(6)
    );
  }


  return (
    '(' +
    numeros.slice(0, 2) +
    ') ' +
    numeros.slice(2, 7) +
    '-' +
    numeros.slice(7)
  );
}


// ════════════════════════════════════════
// APLICAR MÁSCARAS
// ════════════════════════════════════════

function configurarMascaras() {

  const camposCep = [

    document.getElementById(
      'eCep'
    ),

    document.getElementById(
      'checkoutCep'
    )

  ];


  camposCep.forEach(
    campo => {

      if (!campo) {
        return;
      }


      campo.addEventListener(
        'input',
        function () {

          this.value =
            formatarCep(
              this.value
            );
        }
      );
    }
  );


  const campoCpf =
    document.getElementById(
      'pCpf'
    );


  if (campoCpf) {

    campoCpf.addEventListener(
      'input',
      function () {

        this.value =
          formatarCpf(
            this.value
          );
      }
    );
  }


  const campoTelefone =
    document.getElementById(
      'pTel'
    );


  if (campoTelefone) {

    campoTelefone.addEventListener(
      'input',
      function () {

        this.value =
          formatarTelefone(
            this.value
          );
      }
    );
  }
}

// ════════════════════════════════════════
// BUSCAR CEP
// ════════════════════════════════════════

async function buscarCep(cep) {

  cep =
    String(cep || '')
      .replace(
        /\D/g,
        ''
      );


  if (
    cep.length !== 8
  ) {

    return;
  }


  try {

    const resposta =
      await fetch(
        'https://viacep.com.br/ws/' +
        cep +
        '/json/'
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro ao consultar CEP.'
      );
    }


    const dados =
      await resposta.json();


    if (dados.erro) {

      return;
    }


    const campoRua =
      document.getElementById(
        'eRua'
      );

    const campoBairro =
      document.getElementById(
        'eBairro'
      );

    const campoCidade =
      document.getElementById(
        'eCidade'
      );

    const campoEstado =
      document.getElementById(
        'eEstado'
      );


    if (campoRua) {

      campoRua.value =
        dados.logradouro || '';
    }


    if (campoBairro) {

      campoBairro.value =
        dados.bairro || '';
    }


    if (campoCidade) {

      campoCidade.value =
        dados.localidade || '';
    }


    if (campoEstado) {

      campoEstado.value =
        dados.uf || '';
    }


  } catch (erro) {

    console.error(
      'Erro ao buscar CEP:',
      erro
    );

    // O usuário ainda pode preencher
    // o endereço manualmente.
  }
}


// ════════════════════════════════════════
// ALTERAR SENHA — MYSQL
// ════════════════════════════════════════

async function alterarSenha(e) {

  if (
    e &&
    typeof e.preventDefault === 'function'
  ) {

    e.preventDefault();
  }


  if (!usuarioAtual) {

    alert(
      'Você precisa estar logado.'
    );

    abrirModalAuth();

    return;
  }


  const campoAtual =
    document.getElementById(
      'senhaAtual'
    );

  const campoNova =
    document.getElementById(
      'senhaNova'
    );

  const campoConf =
    document.getElementById(
      'senhaConf'
    );

  const erro =
    document.getElementById(
      'erroSenha'
    );

  const ok =
    document.getElementById(
      'okSenha'
    );


  if (
    !campoAtual ||
    !campoNova ||
    !campoConf
  ) {

    console.error(
      'Campos de alteração de senha não encontrados.'
    );

    return;
  }


  const atual =
    campoAtual.value;


  const nova =
    campoNova.value;


  const conf =
    campoConf.value;


  if (erro) {

    erro.classList.add(
      'hidden'
    );

    erro.textContent =
      '';
  }


  if (ok) {

    ok.classList.add(
      'hidden'
    );

    ok.textContent =
      '';
  }


  // CAMPOS OBRIGATÓRIOS
  if (
    !atual ||
    !nova ||
    !conf
  ) {

    if (erro) {

      erro.textContent =
        'Preencha todos os campos.';

      erro.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Preencha todos os campos.'
      );
    }

    return;
  }


  // VALIDAR NOVA SENHA
  const errosSenha =
    validarSenhaForte(
      nova
    );


  if (
    errosSenha.length > 0
  ) {

    if (erro) {

      erro.textContent =
        'Senha fraca. Adicione: ' +
        errosSenha.join(', ') +
        '.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  // A NOVA SENHA NÃO DEVE SER IGUAL À ATUAL
  if (
    atual === nova
  ) {

    if (erro) {

      erro.textContent =
        'A nova senha deve ser diferente da senha atual.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  // CONFIRMAÇÃO
  if (
    nova !== conf
  ) {

    if (erro) {

      erro.textContent =
        'As senhas não coincidem.';

      erro.classList.remove(
        'hidden'
      );
    }

    return;
  }


  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'alterar_senha'
    );


    dados.append(
      'senha_atual',
      atual
    );


    dados.append(
      'senha_nova',
      nova
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    // SESSÃO EXPIRADA
    if (
      resultado.login === false
    ) {

      usuarioAtual =
        null;


      atualizarUI();


      alert(
        'Sua sessão expirou. Faça login novamente.'
      );


      fecharPerfil();

      abrirModalAuth();

      return;
    }


    // ERRO RETORNADO PELO PHP
    if (
      !resultado.sucesso
    ) {

      if (erro) {

        erro.textContent =
          resultado.mensagem ||
          'Não foi possível alterar a senha.';


        erro.classList.remove(
          'hidden'
        );

      } else {

        alert(
          resultado.mensagem ||
          'Não foi possível alterar a senha.'
        );
      }

      return;
    }


    // SUCESSO
    if (ok) {

      ok.textContent =
        'Senha alterada com sucesso!';


      ok.classList.remove(
        'hidden'
      );
    }


    campoAtual.value =
      '';


    campoNova.value =
      '';


    campoConf.value =
      '';


    if (ok) {

      setTimeout(
        () => {

          ok.classList.add(
            'hidden'
          );

        },
        3000
      );
    }


  } catch (erroFetch) {

    console.error(
      'Erro ao alterar senha:',
      erroFetch
    );


    if (erro) {

      erro.textContent =
        'Não foi possível alterar a senha. Tente novamente.';


      erro.classList.remove(
        'hidden'
      );

    } else {

      alert(
        'Não foi possível alterar a senha.'
      );
    }
  }
}

// ════════════════════════════════════════
// PEDIDOS DO MYSQL
// ════════════════════════════════════════

let pedidosBanco = [];

async function carregarPedidosBanco() {

  try {

    const resposta = await fetch(
      '/styleshop/src/php/pedido.php?acao=listar'
    );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' + resposta.status
      );

    }


    const resultado =
      await resposta.json();


    // SESSÃO EXPIRADA
    if (resultado.login === false) {

      pedidosBanco = [];

      usuarioAtual = null;

      atualizarUI();

      abrirModalAuth();

      return false;

    }


    // ERRO DO PHP
    if (!resultado.sucesso) {

      console.error(
        'Erro ao carregar pedidos:',
        resultado.mensagem
      );

      pedidosBanco = [];

      return false;

    }


    // PEDIDOS VINDOS DO MYSQL
    pedidosBanco =
      Array.isArray(resultado.pedidos)
        ? resultado.pedidos
        : [];


    return true;


  } catch (erro) {

    console.error(
      'Erro ao carregar pedidos do MySQL:',
      erro
    );

    pedidosBanco = [];

    return false;

  }

}

// ════════════════════════════════════════
// CANCELAR PEDIDO — MYSQL
// ════════════════════════════════════════

async function cancelarPedido(
  pedidoId
) {

  if (!usuarioAtual) {

    alert(
      'Você precisa estar logado.'
    );

    abrirModalAuth();

    return;
  }


  if (
    typeof pedidosBanco ===
    'undefined'
  ) {

    console.error(
      'pedidosBanco não foi definido.'
    );

    return;
  }


  const pedido =
    pedidosBanco.find(
      p =>
        String(p.id) ===
        String(pedidoId)
    );


  if (!pedido) {

    alert(
      'Pedido não encontrado.'
    );

    return;
  }


  // SÓ PODE CANCELAR ENQUANTO
  // ESTIVER AGUARDANDO PAGAMENTO
  if (
    pedido.status !==
      'aguardando' &&
    pedido.status !==
      'aguardando_pagamento'
  ) {

    alert(
      'Este pedido não pode mais ser cancelado.'
    );

    return;
  }


  const confirmar =
    confirm(
      `Tem certeza que deseja cancelar o pedido #${pedido.id}?`
    );


  if (!confirmar) {

    return;
  }


  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'cancelar'
    );


    dados.append(
      'id_pedido',
      pedido.id
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/pedido.php',
        {
          method: 'POST',
          body: dados
        }
      );


    if (!resposta.ok) {

      throw new Error(
        'Erro HTTP: ' +
        resposta.status
      );
    }


    const resultado =
      await resposta.json();


    if (
      resultado.login === false
    ) {

      usuarioAtual =
        null;


      atualizarUI();


      alert(
        'Sua sessão expirou. Faça login novamente.'
      );


      abrirModalAuth();

      return;
    }


    if (
      !resultado.sucesso
    ) {

      alert(
        resultado.mensagem ||
        'Não foi possível cancelar o pedido.'
      );

      return;
    }


    // BUSCAR NOVAMENTE DO MYSQL
    if (
      typeof carregarPedidosBanco ===
      'function'
    ) {

      await carregarPedidosBanco();
    }


    renderizarPedidos();


    alert(
      'Pedido cancelado com sucesso!'
    );


  } catch (erro) {

    console.error(
      'Erro ao cancelar pedido:',
      erro
    );


    alert(
      'Não foi possível cancelar o pedido. Tente novamente.'
    );
  }
}


// ════════════════════════════════════════
// RENDERIZAR PEDIDOS
// ════════════════════════════════════════

function renderizarPedidos() {

  if (!usuarioAtual) {

    return;
  }


  if (
    typeof pedidosBanco ===
    'undefined'
  ) {

    console.error(
      'pedidosBanco não foi definido.'
    );

    return;
  }


  const pedidos =
    pedidosBanco;


  const el =
    document.getElementById(
      'listaPedidos'
    );


  if (!el) {

    return;
  }


  if (!pedidos.length) {

    el.innerHTML =
      '<p class="text-gray-400 text-sm">Você ainda não fez nenhum pedido.</p>';

    return;
  }


  // ════════════════════════════════════════
  // NOMES DOS STATUS
  // ════════════════════════════════════════

  const labels = {

    aguardando:
      'Aguardando pagamento',

    aguardando_pagamento:
      'Aguardando pagamento',

    enviado:
      'Enviado',

    entregue:
      'Entregue',

    cancelado:
      'Cancelado'

  };


  // ════════════════════════════════════════
  // CLASSES DOS STATUS
  // ════════════════════════════════════════

  const classes = {

    aguardando:
      'status-aguardando',

    aguardando_pagamento:
      'status-aguardando',

    enviado:
      'status-enviado',

    entregue:
      'status-entregue',

    cancelado:
      'status-cancelado'

  };


  // ════════════════════════════════════════
  // ETAPAS DO PEDIDO
  // ════════════════════════════════════════

  const passos = {

    aguardando: [

      {
        cor: '#f59e0b',
        label:
          'Pedido realizado'
      },

      {
        cor: '#d1d5db',
        label:
          'Pagamento confirmado'
      },

      {
        cor: '#d1d5db',
        label:
          'Em separação'
      },

      {
        cor: '#d1d5db',
        label:
          'Enviado'
      },

      {
        cor: '#d1d5db',
        label:
          'Entregue'
      }

    ],


    aguardando_pagamento: [

      {
        cor: '#f59e0b',
        label:
          'Pedido realizado'
      },

      {
        cor: '#d1d5db',
        label:
          'Pagamento confirmado'
      },

      {
        cor: '#d1d5db',
        label:
          'Em separação'
      },

      {
        cor: '#d1d5db',
        label:
          'Enviado'
      },

      {
        cor: '#d1d5db',
        label:
          'Entregue'
      }

    ],


    enviado: [

      {
        cor: '#10b981',
        label:
          'Pedido realizado'
      },

      {
        cor: '#10b981',
        label:
          'Pagamento confirmado'
      },

      {
        cor: '#10b981',
        label:
          'Em separação'
      },

      {
        cor: '#38bdf8',
        label:
          'Enviado'
      },

      {
        cor: '#d1d5db',
        label:
          'Entregue'
      }

    ],


    entregue: [

      {
        cor: '#10b981',
        label:
          'Pedido realizado'
      },

      {
        cor: '#10b981',
        label:
          'Pagamento confirmado'
      },

      {
        cor: '#10b981',
        label:
          'Em separação'
      },

      {
        cor: '#10b981',
        label:
          'Enviado'
      },

      {
        cor: '#10b981',
        label:
          'Entregue ✔'
      }

    ],


    cancelado: [

      {
        cor: '#ef4444',
        label:
          'Pedido cancelado'
      }

    ]

  };


  // ════════════════════════════════════════
  // MONTAR PEDIDOS
  // ════════════════════════════════════════

  el.innerHTML =

    pedidos
      .map(p => {

        const etapas =

          passos[p.status] ||

          passos.aguardando;


        // RASTREIO
        const rastreioHtml =

          p.rastreio

            ? `
              <p class="text-xs text-gray-500 mt-1">

                Rastreio:

                <strong>
                  ${p.rastreio}
                </strong>

              </p>
            `

            : '';


        // PREVISÃO
        const previsaoHtml =

          p.previsao

            ? `
              <p class="text-xs text-gray-500">

                Previsão:

                <strong>
                  ${p.previsao}
                </strong>

              </p>
            `

            : '';


        // ETAPAS
        const etapasHtml =

          etapas
            .map(

              (et, i) => `

                <div class="track-step">

                  <div>

                    <div
                      class="track-dot"
                      style="background:${et.cor}"
                    ></div>


                    ${
                      i <
                      etapas.length - 1

                        ? '<div class="track-line"></div>'

                        : ''
                    }

                  </div>


                  <p
                    class="text-sm"

                    style="
                      color:
                      ${
                        et.cor ===
                          '#d1d5db'

                          ? '#9ca3af'

                          : 'inherit'
                      }
                    "
                  >

                    ${et.label}

                  </p>

                </div>

              `

            )
            .join('');


        // ITENS
        const itensHtml =

          (p.itens || [])
            .map(

              it => `

                <span
                  class="text-xs bg-gray-100 px-2 py-1 rounded"
                >

                  ${it.nome}
                  x${it.qtd}

                </span>

              `

            )
            .join(' ');


        // BOTÃO CANCELAR
        const cancelarHtml =

          (
            p.status ===
              'aguardando' ||

            p.status ===
              'aguardando_pagamento'
          )

            ? `

              <button

                onclick="cancelarPedido('${p.id}')"

                class="mt-2 w-full border border-red-500 text-red-500 text-sm py-1 rounded-lg hover:bg-red-50 font-semibold"

              >

                ✕ Cancelar compra

              </button>

            `

            : '';


        // CARD DO PEDIDO
        return `

          <div
            class="border border-gray-200 rounded-xl p-4 mb-4"
          >


            <div
              class="flex justify-between items-start flex-wrap gap-2 mb-3"
            >


              <div>

                <p class="font-bold text-sm">
                  ${p.id}
                </p>


                <p class="text-xs text-gray-500">
                  ${p.data}
                </p>

              </div>


              <span
                class="status-pill ${
                  classes[p.status] ||
                  classes.aguardando
                }"
              >

                ${
                  labels[p.status] ||
                  labels.aguardando
                }

              </span>


            </div>


            <div
              class="flex flex-wrap gap-2 mb-3"
            >

              ${itensHtml}

            </div>


            <p
              class="text-sm font-bold text-pink-600 mb-3"
            >

              Total:

              R$
              ${Number(
                p.total || 0
              )
                .toFixed(2)
                .replace('.', ',')}

            </p>


            ${rastreioHtml}

            ${previsaoHtml}


            <details class="mt-3">

              <summary
                class="text-xs text-pink-600 cursor-pointer font-semibold"
              >

                Ver acompanhamento

              </summary>


              <div class="mt-3 pl-2">

                ${etapasHtml}

              </div>

            </details>


            <button

              onclick="abrirWhatsApp('${p.id}')"

              class="mt-3 w-full border border-green-500 text-green-600 text-sm py-1 rounded-lg hover:bg-green-50 font-semibold"

            >

              💬 Falar sobre este pedido

            </button>


            ${cancelarHtml}


          </div>

        `;

      })
      .join('');
}


// ════════════════════════════════════════
// WHATSAPP
// ════════════════════════════════════════

function abrirWhatsApp(
  pedidoId
) {

  const numero =
    '556196027179';


  const nome =

    usuarioAtual

      ? usuarioAtual.nome

      : '';


  const msg =

    pedidoId

      ? `Olá! Sou ${nome} e gostaria de informações sobre o pedido ${pedidoId}.`

      : 'Olá! Vim da loja StyleShop e gostaria de atendimento.';


  window.open(

    'https://wa.me/' +

      numero +

      '?text=' +

      encodeURIComponent(msg),

    '_blank'

  );
}

// ════════════════════════════════════════
// CONFIGURAR FORMULÁRIOS
// ════════════════════════════════════════

function configurarFormularios() {

  // ════════════════════════════════════════
  // FORMULÁRIO DE LOGIN
  // ════════════════════════════════════════

  const formLogin =
    document.getElementById(
      'formLogin'
    );


  if (formLogin) {

    formLogin.addEventListener(
      'submit',
      function (event) {

        event.preventDefault();

        fazerLogin();
      }
    );
  }


  // ════════════════════════════════════════
  // FORMULÁRIO DE CADASTRO
  // ════════════════════════════════════════

  const formCadastro =
    document.getElementById(
      'formCadastro'
    );


  if (formCadastro) {

    formCadastro.addEventListener(
      'submit',
      function (event) {

        event.preventDefault();

        fazerCadastro();
      }
    );
  }


  // ════════════════════════════════════════
  // FORMULÁRIO DE DADOS PESSOAIS
  // ════════════════════════════════════════

  const formDados =
    document.getElementById(
      'formDadosPessoais'
    );


  if (formDados) {

    formDados.addEventListener(
      'submit',
      salvarDadosPessoais
    );
  }


  // ════════════════════════════════════════
  // FORMULÁRIO DE ENDEREÇO
  // ════════════════════════════════════════

  const formEndereco =
    document.getElementById(
      'formEndereco'
    );


  if (formEndereco) {

    formEndereco.addEventListener(
      'submit',
      salvarEndereco
    );
  }


  // ════════════════════════════════════════
  // FORMULÁRIO DE SENHA
  // ════════════════════════════════════════

  const formSenha =
    document.getElementById(
      'formSenha'
    );


  if (formSenha) {

    formSenha.addEventListener(
      'submit',
      alterarSenha
    );
  }
}


// ════════════════════════════════════════
// CONFIGURAR CAMPO DE FOTO
// ════════════════════════════════════════

function configurarCampoFoto() {

  const inputFoto =
    document.getElementById(
      'inputFotoPerfil'
    );


  if (!inputFoto) {

    return;
  }


  inputFoto.addEventListener(
    'change',
    carregarFotoPerfil
  );
}

// ════════════════════════════════════════
// CARREGAR FOTO DE PERFIL
// ════════════════════════════════════════

function carregarFotoPerfil(event) {

  const input =
    event && event.target
      ? event.target
      : document.getElementById('inputFotoPerfil');


  if (
    !input ||
    !input.files ||
    !input.files[0]
  ) {
    return;
  }


  const arquivo =
    input.files[0];


  // TIPOS PERMITIDOS
  const tiposPermitidos = [
    'image/jpeg',
    'image/png',
    'image/webp'
  ];


  if (
    !tiposPermitidos.includes(
      arquivo.type
    )
  ) {

    alert(
      'Escolha uma imagem JPG, PNG ou WEBP.'
    );

    input.value = '';

    return;
  }


  // MÁXIMO 5 MB
  const tamanhoMaximo =
    5 * 1024 * 1024;


  if (
    arquivo.size >
    tamanhoMaximo
  ) {

    alert(
      'A imagem deve ter no máximo 5 MB.'
    );

    input.value = '';

    return;
  }


  const reader =
    new FileReader();


  reader.onload =
    function (e) {

      const img =
        new Image();


      img.onload =
        function () {

          const max =
            200;


          let largura =
            img.width;


          let altura =
            img.height;


          if (
            largura > altura
          ) {

            if (
              largura > max
            ) {

              altura =
                Math.round(
                  altura *
                  max /
                  largura
                );

              largura =
                max;
            }

          } else {

            if (
              altura > max
            ) {

              largura =
                Math.round(
                  largura *
                  max /
                  altura
                );

              altura =
                max;
            }
          }


          const canvas =
            document.createElement(
              'canvas'
            );


          canvas.width =
            largura;


          canvas.height =
            altura;


          const ctx =
            canvas.getContext(
              '2d'
            );


          if (!ctx) {

            alert(
              'Não foi possível processar a imagem.'
            );

            return;
          }


          ctx.drawImage(
            img,
            0,
            0,
            largura,
            altura
          );


          const fotoBase64 =
            canvas.toDataURL(
              'image/jpeg',
              0.85
            );


          atualizarPreviewFoto(
            fotoBase64
          );


          salvarFotoPerfil(
            fotoBase64
          );
        };


      img.onerror =
        function () {

          alert(
            'Não foi possível abrir essa imagem.'
          );
        };


      img.src =
        e.target.result;
    };


  reader.onerror =
    function () {

      alert(
        'Não foi possível ler o arquivo.'
      );
    };


  reader.readAsDataURL(
    arquivo
  );
}

// ════════════════════════════════════════
// ATUALIZAR PREVIEW DA FOTO
// ════════════════════════════════════════

function atualizarPreviewFoto(foto) {

  if (!foto) {
    return;
  }


  // FOTO DENTRO DO PERFIL
  const avatarCircle =
    document.getElementById(
      'avatarCircle'
    );


  if (avatarCircle) {

    avatarCircle.innerHTML = `
      <img
        src="${foto}"
        alt="Foto de perfil"
        style="
          width: 100%;
          height: 100%;
          object-fit: cover;
          border-radius: 50%;
        "
      >
    `;
  }


  // FOTO DA BARRA DE NAVEGAÇÃO
  const navFoto =
    document.getElementById(
      'navFoto'
    );


  if (navFoto) {

    navFoto.innerHTML = `
      <img
        src="${foto}"
        alt="Foto de perfil"
        style="
          width: 100%;
          height: 100%;
          object-fit: cover;
          border-radius: 50%;
        "
      >
    `;
  }
}


// ════════════════════════════════════════
// SALVAR FOTO NO MYSQL
// ════════════════════════════════════════

async function salvarFotoPerfil(foto) {

  if (!foto) {
    return;
  }


  try {

    const dados =
      new FormData();


    dados.append(
      'acao',
      'salvar_foto'
    );


    dados.append(
      'foto',
      foto
    );


    const resposta =
      await fetch(
        '/styleshop/src/php/perfil.php',
        {
          method: 'POST',
          body: dados
        }
      );


    const resultado =
      await resposta.json();


    if (!resultado.sucesso) {

      console.error(
        'Erro ao salvar foto:',
        resultado.mensagem
      );


      alert(
        resultado.mensagem ||
        'Não foi possível salvar a foto.'
      );

      return;
    }


    // ATUALIZA O USUÁRIO ATUAL
    if (usuarioAtual) {

      usuarioAtual.foto =
        foto;

      usuarioAtual.foto_perfil =
        foto;
    }


    // ATUALIZA A FOTO NA TELA
    atualizarPreviewFoto(
      foto
    );


    console.log(
      'Foto de perfil salva com sucesso.'
    );


  } catch (erro) {

    console.error(
      'Erro ao salvar foto de perfil:',
      erro
    );


    alert(
      'Não foi possível salvar a foto de perfil.'
    );
  }
}

// ════════════════════════════════════════
// CONFIGURAR FORÇA DA SENHA
// ════════════════════════════════════════

function configurarForcaSenha() {

  const senhaCadastro =
    document.getElementById(
      'cadSenha'
    );


  if (senhaCadastro) {

    senhaCadastro.addEventListener(
      'input',
      function () {

        avaliarForca(
          this.value
        );
      }
    );
  }


  const senhaNova =
    document.getElementById(
      'senhaNova'
    );


  if (senhaNova) {

    senhaNova.addEventListener(
      'input',
      function () {

        avaliarForca(
          this.value
        );
      }
    );
  }
}


// ════════════════════════════════════════
// CONFIGURAR BUSCA AUTOMÁTICA DE CEP
// ════════════════════════════════════════

function configurarBuscaCep() {

  const campoCepPerfil =
    document.getElementById(
      'eCep'
    );


  if (campoCepPerfil) {

    campoCepPerfil.addEventListener(
      'blur',
      function () {

        const cep =
          this.value.replace(
            /\D/g,
            ''
          );


        if (
          cep.length === 8
        ) {

          buscarCep(
            cep
          );
        }
      }
    );
  }
}


// ════════════════════════════════════════
// BOTÃO FINALIZAR COMPRA
// ════════════════════════════════════════

function configurarBotaoFinalizar() {

  const botao =
    document.getElementById(
      'btnFinalizarCompra'
    );


  if (!botao) {

    return;
  }


  botao.addEventListener(
    'click',
    function () {

      finalizarCompra();
    }
  );
}


// ════════════════════════════════════════
// BOTÃO LOGIN / CONTA
// ════════════════════════════════════════

function configurarBotaoAuth() {

  const botao =
    document.getElementById(
      'btnAuth'
    );


  if (!botao) {

    return;
  }


  botao.addEventListener(
    'click',
    function () {

      abrirModalAuth();
    }
  );
}


// ════════════════════════════════════════
// BOTÃO LOGOUT
// ════════════════════════════════════════

function configurarBotaoLogout() {

  const botao =
    document.getElementById(
      'btnSair'
    );


  if (!botao) {

    return;
  }


  botao.addEventListener(
    'click',
    async function () {

      await fazerLogout();
    }
  );
}


// ════════════════════════════════════════
// BOTÃO PERFIL
// ════════════════════════════════════════

function configurarBotaoPerfil() {

  const botao =
    document.getElementById(
      'btnPerfil'
    );


  if (!botao) {

    return;
  }


  botao.addEventListener(
    'click',
    function () {

      abrirPerfil();
    }
  );
}


// ════════════════════════════════════════
// CONFIGURAR BOTÕES DO CHECKOUT
// ════════════════════════════════════════

function configurarCheckout() {

  // CONTINUAR PARA PAGAMENTO
  const btnPagamento =
    document.getElementById(
      'btnIrPagamento'
    );


  if (btnPagamento) {

    btnPagamento.addEventListener(
      'click',
      function () {

        irParaPagamento();
      }
    );
  }


  // VOLTAR PARA ENDEREÇO
  const btnVoltarEndereco =
    document.getElementById(
      'btnVoltarEndereco'
    );


  if (btnVoltarEndereco) {

    btnVoltarEndereco.addEventListener(
      'click',
      function () {

        voltarParaEndereco();
      }
    );
  }


  // CONTINUAR PARA REVISÃO
  const btnRevisao =
    document.getElementById(
      'btnIrRevisao'
    );


  if (btnRevisao) {

    btnRevisao.addEventListener(
      'click',
      function () {

        irParaRevisao();
      }
    );
  }


  // VOLTAR PARA PAGAMENTO
  const btnVoltarPagamento =
    document.getElementById(
      'btnVoltarPagamento'
    );


  if (btnVoltarPagamento) {

    btnVoltarPagamento.addEventListener(
      'click',
      function () {

        voltarParaPagamento();
      }
    );
  }


  // CONFIRMAR PEDIDO
  const btnConfirmar =
    document.getElementById(
      'btnConfirmarPedido'
    );


  if (btnConfirmar) {

    btnConfirmar.addEventListener(
      'click',
      function () {

        confirmarPedido();
      }
    );
  }
}

function abrirAbaPerfil(aba) {

  const abas = [
  'dados',
  'endereco',
  'pedidos',
  'senha'
];

  abas.forEach(nome => {

    const conteudo =
      document.getElementById(
        'aba-' + nome
      );

    if (conteudo) {

      if (nome === aba) {
        conteudo.classList.remove('hidden');
      } else {
        conteudo.classList.add('hidden');
      }

    }

  });


  const botoes =
    document.querySelectorAll(
      '.perfil-tab'
    );

  botoes.forEach(botao => {
    botao.classList.remove('ativo');
  });


  const botaoAtivo =
    document.getElementById(
      'btn-aba-' + aba
    );

  if (botaoAtivo) {
    botaoAtivo.classList.add('ativo');
  }


  /// Atualiza os pedidos ao abrir essa aba
if (aba === 'pedidos') {

  if (
    typeof carregarPedidosBanco === 'function'
  ) {

    carregarPedidosBanco()
      .then(() => {

        if (
          typeof renderizarPedidos === 'function'
        ) {
          renderizarPedidos();
        }

      });

  }

}

}

// ════════════════════════════════════════
// CONFIGURAR BOTÕES DAS ABAS DO PERFIL
// ════════════════════════════════════════

function configurarAbasPerfil() {

  const configuracoes = [

    {
      id:
        'btn-aba-dados',

      aba:
        'dados'
    },

    {
      id:
        'btn-aba-endereco',

      aba:
        'endereco'
    },

    {
      id:
        'btn-aba-pedidos',

      aba:
        'pedidos'
    },

    {
  id:
    'btn-aba-senha',

  aba:
    'senha'
}
  ];


  configuracoes.forEach(
    item => {

      const botao =
        document.getElementById(
          item.id
        );


      if (!botao) {

        return;
      }


      botao.addEventListener(
        'click',
        function () {

          abrirAbaPerfil(
            item.aba
          );
        }
      );
    }
  );
}


// ════════════════════════════════════════
// LIMPAR ESTADO VISUAL DO CHECKOUT
// ════════════════════════════════════════

function limparCheckoutVisual() {

  const etapa1 =
    document.getElementById(
      'checkoutEtapa1'
    );

  const etapa2 =
    document.getElementById(
      'checkoutEtapa2'
    );

  const etapa3 =
    document.getElementById(
      'checkoutEtapa3'
    );

  const etapa4 =
    document.getElementById(
      'checkoutEtapa4'
    );


  if (etapa1) {

    etapa1.classList.remove(
      'hidden'
    );
  }


  if (etapa2) {

    etapa2.classList.add(
      'hidden'
    );
  }


  if (etapa3) {

    etapa3.classList.add(
      'hidden'
    );
  }


  if (etapa4) {

    etapa4.classList.add(
      'hidden'
    );
  }


  const pagamentos =
    document.querySelectorAll(
      'input[name="formaPagamento"]'
    );


  pagamentos.forEach(
    campo => {

      campo.checked =
        false;
    }
  );


  const erroEndereco =
    document.getElementById(
      'erroCheckoutEndereco'
    );


  if (erroEndereco) {

    erroEndereco.classList.add(
      'hidden'
    );
  }


  const erroPagamento =
    document.getElementById(
      'erroCheckoutPagamento'
    );


  if (erroPagamento) {

    erroPagamento.classList.add(
      'hidden'
    );
  }
}


// ════════════════════════════════════════
// FINALIZAR TELA DE PEDIDO CONFIRMADO
// ════════════════════════════════════════

function concluirCheckout() {

  fecharFinalizar();

  limparCheckoutVisual();


  // GARANTIR QUE O CARRINHO VISUAL
  // CONTINUE SINCRONIZADO COM O MYSQL
  carregarCarrinhoBanco()
    .then(
      () => {

        renderizarCarrinho();
      }
    );
}


// ════════════════════════════════════════
// GARANTIR QUE O CONTADOR DO CARRINHO
// SEMPRE REPRESENTE O MYSQL
// ════════════════════════════════════════

function atualizarContadorCarrinho() {

  const contador =
    document.getElementById(
      'contador'
    );


  if (!contador) {

    return;
  }


  const quantidade =
    carrinhoItens.reduce(
      (total, item) => {

        return (
          total +
          Number(
            item.qtd || 0
          )
        );

      },
      0
    );


  contador.textContent =
    String(
      quantidade
    );
}


// ════════════════════════════════════════
// ATUALIZAR CARRINHO COMPLETO
// ════════════════════════════════════════

async function atualizarCarrinhoCompleto() {

  await carregarCarrinhoBanco();

  renderizarCarrinho();

  atualizarContadorCarrinho();
}


// ════════════════════════════════════════
// VERIFICAR SE A PÁGINA POSSUI PRODUTOS
// ════════════════════════════════════════

function verificarProdutosDisponiveis() {

  if (
    typeof produtos ===
    'undefined'
  ) {

    return;
  }


  produtos.forEach(
    produto => {

      produto.id =
        Number(
          produto.id
        );


      produto.preco =
        Number(
          produto.preco || 0
        );


      produto.estoque =
        Number(
          produto.estoque || 0
        );


      produto.destaque =
        Number(
          produto.destaque || 0
        );
    }
  );
}


// ════════════════════════════════════════
// LIMPAR DADOS ANTIGOS DO LOCALSTORAGE
// ════════════════════════════════════════

function limparDadosAntigosLocalStorage() {

  // O carrinho agora fica somente no MySQL.
  localStorage.removeItem(
    'carrinho'
  );


  // O usuário agora é identificado
  // pela sessão PHP.
  localStorage.removeItem(
    'usuarioAtual'
  );


  // Estruturas antigas de usuários/pedidos
  // também não são mais utilizadas.
  localStorage.removeItem(
    'usuarios'
  );


  localStorage.removeItem(
    'pedidos'
  );
}

// ════════════════════════════════════════
// MODO ESCURO
// ════════════════════════════════════════

function alternarTema() {

  const html =
    document.documentElement;

  const escuroAtivo =
    html.classList.toggle('dark');


  localStorage.setItem(
    'tema',
    escuroAtivo
      ? 'escuro'
      : 'claro'
  );


  atualizarBotaoTema();
}


// ════════════════════════════════════════
// ATUALIZAR BOTÃO DO TEMA
// ════════════════════════════════════════

function atualizarBotaoTema() {

  const temaBtn =
    document.getElementById(
      'temaBtn'
    );


  if (!temaBtn) {

    return;
  }


  const escuroAtivo =
    document.documentElement
      .classList
      .contains('dark');


  temaBtn.textContent =
    escuroAtivo
      ? '☀️'
      : '🌙';


  temaBtn.setAttribute(
    'aria-label',
    escuroAtivo
      ? 'Ativar modo claro'
      : 'Ativar modo escuro'
  );
}


// ════════════════════════════════════════
// CARREGAR TEMA SALVO
// ════════════════════════════════════════

function carregarTemaSalvo() {

  const tema =
    localStorage.getItem(
      'tema'
    );


  if (
    tema === 'escuro'
  ) {

    document.documentElement
      .classList
      .add('dark');

  } else {

    document.documentElement
      .classList
      .remove('dark');
  }


  atualizarBotaoTema();
}


// ════════════════════════════════════════
// CONFIGURAR BOTÃO DO TEMA
// ════════════════════════════════════════

function configurarBotaoTema() {

  const temaBtn =
    document.getElementById(
      'temaBtn'
    );


  if (!temaBtn) {

    return;
  }


  temaBtn.addEventListener(
    'click',
    alternarTema
  );
}

// ════════════════════════════════════════
// INICIALIZAÇÃO DO STYLSHOP
// ════════════════════════════════════════

async function inicializarSite() {

  try {

    // ════════════════════════════════════════
    // 1. LIMPAR DADOS ANTIGOS
    // ════════════════════════════════════════

    limparDadosAntigosLocalStorage();


    // ════════════════════════════════════════
    // 2. NORMALIZAR PRODUTOS
    // ════════════════════════════════════════

    verificarProdutosDisponiveis();


    // ════════════════════════════════════════
    // 3. CONFIGURAR EVENTOS
    // ════════════════════════════════════════

    carregarTemaSalvo();

    configurarBotaoTema();

    configurarMascaras();

    configurarBuscaCep();

    configurarCampoFoto();

    configurarForcaSenha();

    configurarFormularios();

    configurarBotaoFinalizar();

    configurarBotaoAuth();

    configurarBotaoLogout();

    configurarBotaoPerfil();

    configurarCheckout();

    configurarAbasPerfil();


    // ════════════════════════════════════════
    // 4. VERIFICAR SESSÃO PHP
    // ════════════════════════════════════════

    const logado =
      await verificarSessaoUsuario();


    // ════════════════════════════════════════
    // 5. ATUALIZAR INTERFACE
    // ════════════════════════════════════════

    atualizarUI();


    // ════════════════════════════════════════
    // 6. CARRINHO
    // ════════════════════════════════════════

    if (logado) {

      await atualizarCarrinhoCompleto();

    } else {

      carrinhoItens =
        [];


      renderizarCarrinho();

      atualizarContadorCarrinho();
    }


    // ════════════════════════════════════════
    // 7. PRODUTOS
    // ════════════════════════════════════════

    if (
      typeof produtos !==
      'undefined'
    ) {

      renderizarProdutos(
        produtos
      );
    }


    console.log(
      'StyleShop iniciado com sucesso.'
    );


  } catch (erro) {

    console.error(
      'Erro durante a inicialização do StyleShop:',
      erro
    );
  }
}


// ════════════════════════════════════════
// INICIAR SOMENTE QUANDO O HTML ESTIVER PRONTO
// ════════════════════════════════════════

if (
  document.readyState ===
  'loading'
) {

  document.addEventListener(
    'DOMContentLoaded',
    inicializarSite
  );

} else {

  inicializarSite();
}

