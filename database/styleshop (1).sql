-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Tempo de geração: 30/09/2026 às 16:41
-- Versão do servidor: 8.0.44
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `styleshop`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `id` int NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `cargos`
--

INSERT INTO `cargos` (`id`, `nome`) VALUES
(1, 'admin'),
(8, 'customer'),
(2, 'developer'),
(6, 'financial'),
(7, 'logistics'),
(5, 'manager'),
(4, 'moderator'),
(3, 'support');

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinhos`
--

CREATE TABLE `carrinhos` (
  `id_carrinho` int NOT NULL,
  `id_usuario` int NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `carrinhos`
--

INSERT INTO `carrinhos` (`id_carrinho`, `id_usuario`, `criado_em`, `atualizado_em`) VALUES
(1, 1, '2026-09-16 11:48:05', '2026-09-16 11:48:05'),
(2, 2, '2026-09-30 12:08:39', '2026-09-30 12:08:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id_endereco` int NOT NULL,
  `id_usuario` int NOT NULL,
  `nome_endereco` varchar(100) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `rua` varchar(150) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id_endereco`, `id_usuario`, `nome_endereco`, `cep`, `estado`, `cidade`, `bairro`, `rua`, `numero`, `complemento`, `referencia`, `principal`, `data_cadastro`) VALUES
(1, 1, 'Larissa Peixoto', '72726109', 'DF', 'brazlandia', 'setor veredas', 'quadra 5 conjunto i casa 7', '7', 'na frente da escola', '', 1, '2026-09-21 12:11:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_carrinho`
--

CREATE TABLE `itens_carrinho` (
  `id_item_carrinho` int NOT NULL,
  `id_carrinho` int NOT NULL,
  `id_produto` int NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id_item_pedido` int NOT NULL,
  `id_pedido` int NOT NULL,
  `id_produto` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `itens_pedido`
--

INSERT INTO `itens_pedido` (`id_item_pedido`, `id_pedido`, `id_produto`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 2, 1, 200.00, 200.00),
(2, 2, 2, 1, 200.00, 200.00),
(3, 3, 2, 1, 200.00, 200.00),
(4, 4, 3, 5, 250.00, 1250.00),
(5, 5, 3, 1, 250.00, 250.00),
(6, 6, 3, 5, 250.00, 1250.00),
(7, 7, 2, 8, 200.00, 1600.00),
(8, 8, 1, 1, 139.00, 139.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `id_item` int NOT NULL,
  `id_venda` int NOT NULL,
  `id_produto` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id_pagamento` int NOT NULL,
  `id_pedido` int NOT NULL,
  `metodo_pagamento` enum('pix','cartao_credito','cartao_debito','boleto') NOT NULL,
  `status_pagamento` enum('pendente','aprovado','recusado','cancelado','estornado') NOT NULL DEFAULT 'pendente',
  `valor` decimal(10,2) NOT NULL,
  `codigo_transacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_endereco` int NOT NULL,
  `status_pedido` enum('aguardando_pagamento','pago','processando','enviado','entregue','cancelado') NOT NULL DEFAULT 'aguardando_pagamento',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `frete` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `id_endereco`, `status_pedido`, `subtotal`, `frete`, `desconto`, `total`, `criado_em`, `atualizado_em`) VALUES
(1, 1, 1, 'cancelado', 200.00, 0.00, 0.00, 200.00, '2026-09-21 12:11:53', '2026-09-21 14:17:20'),
(2, 1, 1, 'cancelado', 200.00, 0.00, 0.00, 200.00, '2026-09-21 13:20:05', '2026-09-21 14:13:21'),
(3, 1, 1, 'cancelado', 200.00, 0.00, 0.00, 200.00, '2026-09-21 14:01:14', '2026-09-21 14:13:13'),
(4, 1, 1, 'cancelado', 1250.00, 0.00, 0.00, 1250.00, '2026-09-21 14:19:31', '2026-09-21 14:19:47'),
(5, 1, 1, 'cancelado', 250.00, 0.00, 0.00, 250.00, '2026-09-21 14:23:20', '2026-09-21 14:23:28'),
(6, 1, 1, 'cancelado', 1250.00, 0.00, 0.00, 1250.00, '2026-09-21 14:24:50', '2026-09-21 14:25:54'),
(7, 1, 1, 'cancelado', 1600.00, 0.00, 0.00, 1600.00, '2026-09-21 14:25:26', '2026-09-21 14:25:51'),
(8, 1, 1, 'aguardando_pagamento', 139.00, 0.00, 0.00, 139.00, '2026-09-30 12:19:30', '2026-09-30 12:19:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id_produto` int NOT NULL,
  `img_produto` varchar(255) NOT NULL,
  `colecao_produto` varchar(100) NOT NULL,
  `avaliacao_produto` decimal(3,1) NOT NULL,
  `nome_produto` varchar(100) NOT NULL,
  `descricao_produto` text,
  `tamanho_produto` varchar(10) NOT NULL,
  `preco_produto` decimal(10,2) NOT NULL,
  `estoque_produto` int NOT NULL DEFAULT '0',
  `cor_produto` varchar(30) NOT NULL,
  `destaque_produto` tinyint(1) NOT NULL DEFAULT '0',
  `marca_produto` varchar(100) NOT NULL DEFAULT 'StyleShop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id_produto`, `img_produto`, `colecao_produto`, `avaliacao_produto`, `nome_produto`, `descricao_produto`, `tamanho_produto`, `preco_produto`, `estoque_produto`, `cor_produto`, `destaque_produto`, `marca_produto`) VALUES
(1, 'moletons/moletom1.jpg', 'moletom', 4.8, 'Moletom Clássico', 'Um clássico para os dias mais frios. Este moletom cinza aposta em um design limpo, sem excesso de detalhes, perfeito para quem prefere peças discretas e fáceis de combinar.', 'M', 139.00, 9, 'Preto', 1, 'StyleShop'),
(2, 'moletons/moletom2.jpg', 'moletom', 4.9, 'Moletom Grafite', 'Para quem gosta de estampas que chamam atenção, o Moletom Grafite traz uma arte colorida de inspiração urbana nas costas. Uma peça cheia de personalidade para destacar o look.', 'M', 200.00, 8, 'Grafite', 0, 'StyleShop'),
(3, 'calcas/calca1.jpg', 'calças', 4.7, 'Calça Cargo', 'A Calça Cargo preta traz bolsos utilitários e uma proposta inspirada no streetwear. O acabamento escuro deixa a peça ainda mais marcante e combina muito bem com camisetas, moletons e tênis.', 'M', 250.00, 5, 'Preto', 0, 'StyleShop'),
(4, 'blusas/blusa1.jpg', 'blusas', 4.8, 'Blusa Básica', 'Básica só no nome. A camiseta preta ganha destaque com uma pequena estampa colorida centralizada no peito, criando um contraste divertido sem deixar a peça exagerada.', 'M', 100.00, 12, 'Preto', 0, 'StyleShop'),
(5, 'tenis/tenis1.jpg', 'tênis', 4.9, 'Tênis Air Jordan', 'Inspirado no universo do basquete, o Air Jordan combina preto, vermelho e branco em um tênis de presença forte. Um modelo feito para ser o ponto de destaque da produção.', '40', 350.00, 0, 'Preto/Vermelho', 0, 'StyleShop'),
(6, 'moletons/moletom3.jpg', 'moletom', 4.8, 'Moletom Blessed', 'Arte, atitude e referências clássicas se encontram neste moletom preto. A estampa Renaissance ocupa grande parte da peça e transforma um moletom escuro em uma composição muito mais expressiva.', 'M', 220.00, 10, 'Preto', 0, 'StyleShop'),
(7, 'moletons/moletom4.jpg', 'moletom', 4.7, 'Moletom Tie-Dye', 'O efeito tie-dye em tons de verde cria um desenho único e cheio de movimento. Uma escolha para fugir das cores básicas e acrescentar um toque artístico ao guarda-roupa.', 'M', 149.00, 7, 'Verde', 0, 'StyleShop'),
(8, 'moletons/moletom5.jpg', 'moletom', 4.9, 'Moletom Canguru', 'Com tonalidade bege clara e bolso canguru frontal, este moletom aposta em uma estética minimalista. Ótimo para combinações em tons neutros e produções mais suaves.', 'M', 260.00, 6, 'Bege', 1, 'StyleShop'),
(9, 'moletons/moletom6.jpg', 'moletom', 4.6, 'Moletom Cropped', 'Com estampa NEW YORK em destaque, este cropped mistura referências universitárias e streetwear. O comprimento reduzido dá à peça uma proposta jovem e descontraída.', 'M', 129.00, 9, 'Bege', 0, 'StyleShop'),
(10, 'moletons/moletom7.jpg', 'moletom', 4.8, 'Moletom Básico Preto', 'Preto, simples e sem complicação. Este moletom básico funciona como uma peça coringa para montar desde produções totalmente escuras até combinações com acessórios e tênis coloridos.', 'M', 134.00, 11, 'Preto', 0, 'StyleShop'),
(11, 'moletons/moletom8.jpg', 'moletom', 4.9, 'Moletom College', 'O estilo college aparece com força neste moletom branco e verde. As letras grandes na parte frontal lembram os tradicionais uniformes universitários americanos.', 'M', 159.00, 5, 'Branco/Verde', 0, 'StyleShop'),
(12, 'moletons/moletom9.jpg', 'moletom', 4.8, 'Moletom Streetwear', 'O tom vinho já chama atenção, mas a grande estampa gráfica é o elemento principal deste moletom. Uma peça com forte influência streetwear para quem prefere roupas com presença.', 'M', 200.00, 8, 'Vinho', 0, 'StyleShop'),
(13, 'calcas/calca2.jpg', 'calças', 4.7, 'Calça Jeans', 'Jeans azul de lavagem clássica para acompanhar diferentes momentos do dia. O acabamento tradicional faz desta calça uma opção simples para combinar com praticamente qualquer parte de cima.', 'M', 120.00, 10, 'Azul', 0, 'StyleShop'),
(14, 'calcas/calca3.jpg', 'calças', 4.6, 'Calça Slim', 'Com corte mais ajustado e tonalidade escura, a Calça Slim cria uma silhueta mais definida. Combina especialmente bem com camisetas, camisas e tênis de perfil baixo.', 'M', 80.00, 8, 'Preto', 0, 'StyleShop'),
(15, 'calcas/calca4.jpg', 'calças', 4.8, 'Calça Wide Leg', 'A modelagem ampla é a protagonista desta Wide Leg jeans. As pernas largas dão movimento à peça e criam uma estética atual inspirada nas tendências dos anos 90 e 2000.', 'M', 139.00, 7, 'Azul', 1, 'StyleShop'),
(16, 'calcas/calca5.jpg', 'calças', 4.7, 'Calça Street', 'Uma calça preta com influência direta da moda urbana. Os detalhes utilitários e o caimento descontraído fazem dela uma ótima parceira para produções streetwear.', 'M', 200.00, 6, 'Preto', 0, 'StyleShop'),
(17, 'calcas/calca6.jpg', 'calças', 4.8, 'Calça Jogger', 'A Jogger cinza combina cintura ajustada, caimento descontraído e uma proposta esportiva. Pensada para quem gosta de roupas práticas sem deixar o estilo de lado.', 'M', 129.00, 9, 'Cinza', 0, 'StyleShop'),
(18, 'calcas/calca7.jpg', 'calças', 4.7, 'Calça Flare', 'A modelagem flare começa mais ajustada e ganha amplitude na parte inferior das pernas. Em preto, o formato fica ainda mais destacado e traz uma referência retrô ao visual.', 'M', 149.00, 5, 'Preto', 0, 'StyleShop'),
(19, 'calcas/calca8.jpg', 'calças', 4.9, 'Calça Alfaiataria', 'A alfaiataria ganha uma interpretação mais descontraída nesta calça azul. O corte estruturado ajuda a criar uma produção mais elegante sem exigir um look totalmente formal.', 'M', 169.00, 8, 'Azul', 0, 'StyleShop'),
(20, 'calcas/calca9.jpg', 'calças', 4.6, 'Calça Moletom', 'Feita para aqueles dias em que conforto vem primeiro. A Calça Moletom preta apresenta aparência macia e descontraída, ideal para combinar com tênis e peças oversized.', 'M', 119.00, 10, 'Preto', 0, 'StyleShop'),
(21, 'calcas/calca10.jpg', 'calças', 4.8, 'Calça Sarja', 'A sarja preta oferece uma alternativa ao jeans tradicional. Seu acabamento uniforme e discreto permite criar desde combinações simples até produções um pouco mais arrumadas.', 'M', 144.00, 7, 'Preto', 0, 'StyleShop'),
(22, 'blusas/blusa2.jpg', 'blusas', 4.7, 'Blusa Cristã Personalizada', 'Uma camiseta que transforma mensagem em parte do visual. A estampa cristã ocupa posição de destaque sobre o fundo marrom, criando uma peça de identidade forte e inspiração religiosa.', 'M', 200.00, 8, 'Marrom', 0, 'StyleShop'),
(23, 'blusas/blusa3.jpg', 'blusas', 4.8, 'Blusa Listrada', 'Listras verdes e brancas dão a esta blusa uma estética retrô e descontraída. A combinação de cores cria um resultado marcante sem depender de grandes estampas.', 'M', 120.00, 10, 'Verde/Branco', 0, 'StyleShop'),
(24, 'blusas/blusa4.jpg', 'blusas', 4.7, 'Blusa Oversized', 'A modelagem oversized proporciona um caimento propositalmente amplo. Em bege, a peça ganha uma aparência clean que funciona muito bem com calças largas, jeans ou cargo.', 'M', 139.00, 7, 'Bege', 0, 'StyleShop'),
(25, 'blusas/blusa5.jpg', 'blusas', 4.9, 'Blusa Estampada', 'Uma explosão de ilustrações transforma esta camiseta branca em uma peça de destaque. As estampas espalhadas criam um visual criativo para quem gosta de roupas mais expressivas.', 'M', 210.00, 5, 'Branco', 1, 'StyleShop'),
(26, 'blusas/blusa6.jpg', 'blusas', 4.8, 'Blusa Manga Longa', 'Com mangas longas e blocos em vinho, preto e branco, esta blusa traz uma combinação inspirada no estilo esportivo retrô. Uma escolha interessante para dias de temperatura mais amena.', 'M', 129.00, 9, 'Vermelho', 0, 'StyleShop'),
(27, 'blusas/blusa7.jpg', 'blusas', 4.7, 'Blusa Tie-Dye', 'O azul intenso e o efeito tie-dye fazem cada região da peça parecer diferente. A mistura de tons cria profundidade e dá à blusa uma aparência artística e descontraída.', 'M', 134.00, 6, 'Azul/Preto', 0, 'StyleShop'),
(28, 'blusas/blusa8.jpg', 'blusas', 4.8, 'Blusa Polo', 'Delicada e cheia de movimento, a Blusa Polo combina gola clássica com uma modelagem curta e detalhe fluido na barra. O contraste entre vinho e branco reforça seu charme.', 'M', 149.00, 8, 'Vinho/Branco', 0, 'StyleShop'),
(29, 'blusas/blusa9.jpg', 'blusas', 4.6, 'Blusa Regata', 'A textura é o grande destaque desta blusa bege. O acabamento trabalhado cria uma aparência aconchegante e acrescenta informação ao look mesmo sem estampas ou cores fortes.', 'M', 89.00, 12, 'Bege', 0, 'StyleShop'),
(30, 'blusas/blusa10.jpg', 'blusas', 4.9, 'Blusa Cropped Tricot', 'Este cropped de tricot branco aposta na simplicidade e na textura do tecido para se destacar. O resultado é uma peça leve visualmente, delicada e fácil de combinar.', 'M', 159.00, 7, 'Branco', 1, 'StyleShop'),
(31, 'blusas/blusa11.jpg', 'blusas', 4.8, 'Blusa de Casal Personalizada', 'Uma camiseta azul-clara personalizada com estampa pequena e discreta. A proposta combina simplicidade com um detalhe exclusivo, deixando a peça com uma identidade própria.', 'M', 200.00, 5, 'Azul', 0, 'StyleShop'),
(32, 'tenis/tenis2.jpg', 'tênis', 4.8, 'Tênis Street Star', 'O Street Star traz a clássica combinação preto e branco em um tênis de cano alto inspirado na cultura skate. O contraste forte combina especialmente bem com jeans e peças streetwear.', '40', 249.00, 10, 'Preto/Branco', 0, 'StyleShop'),
(33, 'tenis/tenis3.jpg', 'tênis', 4.8, 'Tênis Retro Run', 'Linhas retrô e combinação de branco com cinza dão personalidade ao Retro Run. O desenho remete aos tênis esportivos clássicos e funciona muito bem em produções inspiradas nos anos 90.', '40', 370.00, 8, 'Branco/Cinza', 0, 'StyleShop'),
(34, 'tenis/tenis4.jpg', 'tênis', 4.9, 'Tênis Chunky Platform', 'O solado elevado e a construção robusta fazem do Chunky Platform um tênis impossível de passar despercebido. O bege suaviza o volume e facilita a combinação com tons neutros.', '40', 320.00, 7, 'Bege', 1, 'StyleShop'),
(35, 'tenis/tenis5.jpg', 'tênis', 4.7, 'Tênis Casual Slip-On', 'Sem cadarços e com formato simples, o Casual Slip-On aposta na praticidade. A tonalidade marrom reforça sua aparência clássica e combina bem com roupas em cores terrosas.', '40', 230.00, 9, 'Marrom', 0, 'StyleShop'),
(36, 'tenis/tenis6.jpg', 'tênis', 4.8, 'Tênis White Classic', 'Branco do cabedal ao solado, o White Classic aposta em uma estética limpa e minimalista. Uma ótima escolha quando o objetivo é deixar as outras peças da produção assumirem o destaque.', '40', 299.00, 11, 'Branco', 0, 'StyleShop'),
(37, 'tenis/tenis7.jpg', 'tênis', 4.9, 'Tênis Skate Pro High', 'O cano alto, o xadrez preto e branco e a inspiração no universo do skate definem o Skate Pro High. Um tênis com identidade forte e uma pegada alternativa.', '40', 349.00, 6, 'Preto/Branco', 1, 'StyleShop'),
(38, 'tenis/tenis8.jpg', 'tênis', 4.7, 'Tênis Urban Neon', 'Azul, branco e detalhes contrastantes criam o visual esportivo do Urban Neon. As diferentes camadas do cabedal deixam o modelo mais dinâmico e chamativo.', '40', 420.00, 5, 'Azul/Branco', 0, 'StyleShop'),
(39, 'tenis/tenis9.jpg', 'tênis', 4.9, 'Tênis Running Max', 'Vermelho intenso, detalhes pretos e construção esportiva fazem o Running Max se destacar imediatamente. Um tênis para quem não quer que os pés passem despercebidos.', '40', 459.00, 8, 'Vermelho/Preto', 0, 'StyleShop'),
(40, 'tenis/tenis10.jpg', 'tênis', 4.8, 'Tênis Suede Vintage', 'O acabamento marrom com aparência de camurça dá ao Suede Vintage uma personalidade retrô. O formato baixo e a inspiração clássica completam a proposta nostálgica do modelo.', '40', 238.00, 10, 'Marrom', 0, 'StyleShop');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nome_social` varchar(100) DEFAULT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `foto_perfil` longtext,
  `nascimento` date NOT NULL,
  `pronomes` enum('ele/dele','ela/dela','elu/delu') DEFAULT NULL,
  `senha_segura` varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','developer','support','moderator','manager','financial','logistics','customer') NOT NULL DEFAULT 'customer',
  `acessibilidade_alto_contraste` tinyint(1) NOT NULL DEFAULT '0',
  `cargo_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `nome_social`, `cpf`, `email`, `telefone`, `foto_perfil`, `nascimento`, `pronomes`, `senha_segura`, `tipo_usuario`, `acessibilidade_alto_contraste`, `cargo_id`) VALUES
(1, 'larissa miriele peixoto casemiro', NULL, '07527455144', 'larissamiriele75@gmail.com', NULL, 'data:image/jpeg;base64,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', '2008-07-03', NULL, '$2y$10$zoFYsuOombSlyoFE2y3HYO9YDSdzZfbTErtAVctz7KqeDm16tK892', 'admin', 0, NULL),
(2, 'Miriele Peixoto', NULL, '07527455144', 'mirielepeixoto02@gmail.com', NULL, NULL, '2008-07-03', NULL, '$2y$10$wsacTpsvz3s/Mfpw2l3S2eQsnqVSSlFGXFpZu0DVqLE3wNbc0myza', 'customer', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id_venda` int NOT NULL,
  `id_usuario` int NOT NULL,
  `data_venda` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `carrinhos`
--
ALTER TABLE `carrinhos`
  ADD PRIMARY KEY (`id_carrinho`),
  ADD UNIQUE KEY `uk_carrinho_usuario` (`id_usuario`);

--
-- Índices de tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id_endereco`),
  ADD KEY `fk_endereco_usuario` (`id_usuario`);

--
-- Índices de tabela `itens_carrinho`
--
ALTER TABLE `itens_carrinho`
  ADD PRIMARY KEY (`id_item_carrinho`),
  ADD UNIQUE KEY `uk_carrinho_produto` (`id_carrinho`,`id_produto`),
  ADD KEY `idx_item_carrinho` (`id_carrinho`),
  ADD KEY `idx_item_produto` (`id_produto`);

--
-- Índices de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id_item_pedido`),
  ADD KEY `idx_itens_pedido` (`id_pedido`),
  ADD KEY `idx_itens_produto` (`id_produto`);

--
-- Índices de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_venda` (`id_venda`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id_pagamento`),
  ADD KEY `idx_pagamento_pedido` (`id_pedido`),
  ADD KEY `idx_pagamento_status` (`status_pagamento`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedido_endereco` (`id_endereco`),
  ADD KEY `idx_pedidos_usuario` (`id_usuario`),
  ADD KEY `idx_pedidos_status` (`status_pedido`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produto`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuario_cargo` (`cargo_id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id_venda`),
  ADD KEY `fk_vendas_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `carrinhos`
--
ALTER TABLE `carrinhos`
  MODIFY `id_carrinho` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id_endereco` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `itens_carrinho`
--
ALTER TABLE `itens_carrinho`
  MODIFY `id_item_carrinho` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id_item_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `id_item` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id_pagamento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id_venda` int NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `carrinhos`
--
ALTER TABLE `carrinhos`
  ADD CONSTRAINT `fk_carrinho_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `fk_endereco_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `itens_carrinho`
--
ALTER TABLE `itens_carrinho`
  ADD CONSTRAINT `fk_item_carrinho` FOREIGN KEY (`id_carrinho`) REFERENCES `carrinhos` (`id_carrinho`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Restrições para tabelas `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `fk_item_pedido_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_pedido_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Restrições para tabelas `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`id_venda`) REFERENCES `vendas` (`id_venda`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`);

--
-- Restrições para tabelas `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `fk_pagamento_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_endereco` FOREIGN KEY (`id_endereco`) REFERENCES `enderecos` (`id_endereco`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `fk_vendas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
