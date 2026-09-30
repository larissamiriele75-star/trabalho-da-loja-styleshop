-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Tempo de geração: 26/08/2026 às 16:21
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
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id_produto` int NOT NULL,
  `img_produto` varchar(255) NOT NULL,
  `colecao_produto` varchar(100) NOT NULL,
  `avaliacao_produto` decimal(3,1) NOT NULL,
  `nome_produto` varchar(100) NOT NULL,
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

INSERT INTO `produtos` (`id_produto`, `img_produto`, `colecao_produto`, `avaliacao_produto`, `nome_produto`, `tamanho_produto`, `preco_produto`, `estoque_produto`, `cor_produto`, `destaque_produto`, `marca_produto`) VALUES
(1, 'moletons/moletom1.jpg', 'moletom', 4.8, 'Moletom Clássico', 'M', 139.00, 10, 'Preto', 1, 'StyleShop'),
(2, 'moletons/moletom2.jpg', 'moletom', 4.9, 'Moletom Grafite', 'M', 200.00, 8, 'Grafite', 0, 'StyleShop'),
(3, 'calcas/calca1.jpg', 'calças', 4.7, 'Calça Cargo', 'M', 250.00, 5, 'Preto', 0, 'StyleShop'),
(4, 'blusas/blusa1.jpg', 'blusas', 4.8, 'Blusa Básica', 'M', 100.00, 12, 'Preto', 0, 'StyleShop'),
(5, 'tenis/tenis1.jpg', 'tênis', 4.9, 'Tênis Air Jordan', '40', 350.00, 0, 'Preto/Vermelho', 0, 'StyleShop'),
(6, 'moletons/moletom3.jpg', 'moletom', 4.8, 'Moletom Blessed', 'M', 220.00, 10, 'Preto', 0, 'StyleShop'),
(7, 'moletons/moletom4.jpg', 'moletom', 4.7, 'Moletom Tie-Dye', 'M', 149.00, 7, 'Verde', 0, 'StyleShop'),
(8, 'moletons/moletom5.jpg', 'moletom', 4.9, 'Moletom Canguru', 'M', 260.00, 6, 'Bege', 1, 'StyleShop'),
(9, 'moletons/moletom6.jpg', 'moletom', 4.6, 'Moletom Cropped', 'M', 129.00, 9, 'Bege', 0, 'StyleShop'),
(10, 'moletons/moletom7.jpg', 'moletom', 4.8, 'Moletom Básico Preto', 'M', 134.00, 11, 'Preto', 0, 'StyleShop'),
(11, 'moletons/moletom8.jpg', 'moletom', 4.9, 'Moletom College', 'M', 159.00, 5, 'Branco/Verde', 0, 'StyleShop'),
(12, 'moletons/moletom9.jpg', 'moletom', 4.8, 'Moletom Streetwear', 'M', 200.00, 8, 'Vinho', 0, 'StyleShop'),
(13, 'calcas/calca2.jpg', 'calças', 4.7, 'Calça Jeans', 'M', 120.00, 10, 'Azul', 0, 'StyleShop'),
(14, 'calcas/calca3.jpg', 'calças', 4.6, 'Calça Slim', 'M', 80.00, 8, 'Preto', 0, 'StyleShop'),
(15, 'calcas/calca4.jpg', 'calças', 4.8, 'Calça Wide Leg', 'M', 139.00, 7, 'Azul', 1, 'StyleShop'),
(16, 'calcas/calca5.jpg', 'calças', 4.7, 'Calça Street', 'M', 200.00, 6, 'Preto', 0, 'StyleShop'),
(17, 'calcas/calca6.jpg', 'calças', 4.8, 'Calça Jogger', 'M', 129.00, 9, 'Cinza', 0, 'StyleShop'),
(18, 'calcas/calca7.jpg', 'calças', 4.7, 'Calça Flare', 'M', 149.00, 5, 'Preto', 0, 'StyleShop'),
(19, 'calcas/calca8.jpg', 'calças', 4.9, 'Calça Alfaiataria', 'M', 169.00, 8, 'Azul', 0, 'StyleShop'),
(20, 'calcas/calca9.jpg', 'calças', 4.6, 'Calça Moletom', 'M', 119.00, 10, 'Preto', 0, 'StyleShop'),
(21, 'calcas/calca10.jpg', 'calças', 4.8, 'Calça Sarja', 'M', 144.00, 7, 'Preto', 0, 'StyleShop'),
(22, 'blusas/blusa2.jpg', 'blusas', 4.7, 'Blusa Cristã Personalizada', 'M', 200.00, 8, 'Marrom', 0, 'StyleShop'),
(23, 'blusas/blusa3.jpg', 'blusas', 4.8, 'Blusa Listrada', 'M', 120.00, 10, 'Verde/Branco', 0, 'StyleShop'),
(24, 'blusas/blusa4.jpg', 'blusas', 4.7, 'Blusa Oversized', 'M', 139.00, 7, 'Bege', 0, 'StyleShop'),
(25, 'blusas/blusa5.jpg', 'blusas', 4.9, 'Blusa Estampada', 'M', 210.00, 5, 'Branco', 1, 'StyleShop'),
(26, 'blusas/blusa6.jpg', 'blusas', 4.8, 'Blusa Manga Longa', 'M', 129.00, 9, 'Vermelho', 0, 'StyleShop'),
(27, 'blusas/blusa7.jpg', 'blusas', 4.7, 'Blusa Tie-Dye', 'M', 134.00, 6, 'Azul/Preto', 0, 'StyleShop'),
(28, 'blusas/blusa8.jpg', 'blusas', 4.8, 'Blusa Polo', 'M', 149.00, 8, 'Vinho/Branco', 0, 'StyleShop'),
(29, 'blusas/blusa9.jpg', 'blusas', 4.6, 'Blusa Regata', 'M', 89.00, 12, 'Bege', 0, 'StyleShop'),
(30, 'blusas/blusa10.jpg', 'blusas', 4.9, 'Blusa Cropped Tricot', 'M', 159.00, 7, 'Branco', 1, 'StyleShop'),
(31, 'blusas/blusa11.jpg', 'blusas', 4.8, 'Blusa de Casal Personalizada', 'M', 200.00, 5, 'Azul', 0, 'StyleShop'),
(32, 'tenis/tenis2.jpg', 'tênis', 4.8, 'Tênis Street Star', '40', 249.00, 10, 'Preto/Branco', 0, 'StyleShop'),
(33, 'tenis/tenis3.jpg', 'tênis', 4.8, 'Tênis Retro Run', '40', 370.00, 8, 'Branco/Cinza', 0, 'StyleShop'),
(34, 'tenis/tenis4.jpg', 'tênis', 4.9, 'Tênis Chunky Platform', '40', 320.00, 7, 'Bege', 1, 'StyleShop'),
(35, 'tenis/tenis5.jpg', 'tênis', 4.7, 'Tênis Casual Slip-On', '40', 230.00, 9, 'Marrom', 0, 'StyleShop'),
(36, 'tenis/tenis6.jpg', 'tênis', 4.8, 'Tênis White Classic', '40', 299.00, 11, 'Branco', 0, 'StyleShop'),
(37, 'tenis/tenis7.jpg', 'tênis', 4.9, 'Tênis Skate Pro High', '40', 349.00, 6, 'Preto/Branco', 1, 'StyleShop'),
(38, 'tenis/tenis8.jpg', 'tênis', 4.7, 'Tênis Urban Neon', '40', 420.00, 5, 'Azul/Branco', 0, 'StyleShop'),
(39, 'tenis/tenis9.jpg', 'tênis', 4.9, 'Tênis Running Max', '40', 459.00, 8, 'Vermelho/Preto', 0, 'StyleShop'),
(40, 'tenis/tenis10.jpg', 'tênis', 4.8, 'Tênis Suede Vintage', '40', 238.00, 10, 'Marrom', 0, 'StyleShop');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produto`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
