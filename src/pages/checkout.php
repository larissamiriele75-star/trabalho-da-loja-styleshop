<?php
// 1. Inicia a sessão para identificar o usuário logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONEXÃO COM O BANCO (Ajuste com as suas credenciais)
require_once "../php/conexao.php";


// 2. Proteção: Se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /styleshop/public/index.php");
exit();
    exit;
}

$id_usuario_logado = $_SESSION['id_usuario'];

// 3. Busca todos os endereços cadastrados deste usuário específico
$sql = "SELECT 
            id_endereco,
            nome_endereco,
            cep,
            rua,
            numero,
            bairro,
            cidade,
            estado,
            principal
        FROM enderecos
        WHERE id_usuario = ?";

$stmt = $conexao->prepare($sql);

$stmt->bind_param("i", $id_usuario_logado);

$stmt->execute();

$resultado = $stmt->get_result();

$enderecos = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!-- 4. INTERFACE HTML (Renderização no Carrinho / Checkout) -->
<div class="checkout-enderecos">
    <h3>Selecione o Endereço de Entrega</h3>

    <?php if (empty($enderecos)): ?>
        <!-- Validação exigida no escopo: Bloqueia a compra se não houver endereço -->
        <div class="alerta-erro">
            <p>⚠️ Você não possui nenhum endereço cadastrado.</p>
            <a href="perfil-enderecos.php" class="btn-cadastrar">Cadastrar Endereço Obrigatório</a>
        </div>
    <?php else: ?>
        
        <!-- Formulário que envia o endereço selecionado para o fechamento do pedido -->
        <form action="finalizar-pedido.php" method="POST">
            
            <div class="lista-enderecos">
                <?php foreach ($enderecos as $end): ?>
                    <label class="card-endereco">
                        <!-- O input radio garante que apenas um endereço seja escolhido por vez -->
                        <!-- Se o endereço for o 'principal', ele já vem marcado (checked) automaticamente -->
                        <input type="radio" name="id_endereco_selecionado" value="<?= $end['id_endereco'] ?>" <?= $end['principal'] == 1 ? 'checked' : '' ?> required>
                        
                        <div class="info-endereco">
                            <strong><?= htmlspecialchars($end['nome_endereco'] ?? 'Endereço') ?></strong>
                            <p><?= htmlspecialchars($end['rua']) ?>, Nº <?= htmlspecialchars($end['numero']) ?></p>
                            <p><?= htmlspecialchars($end['bairro']) ?> - <?= htmlspecialchars($end['cidade']) ?>/<?= htmlspecialchars($end['estado']) ?></p>
                            <p>CEP: <?= htmlspecialchars($end['cep']) ?></p>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Botão para avançar no checkout -->
            <button type="submit" class="btn-avancar">Confirmar Endereço e Pagar</button>
        </form>

    <?php endif; ?>
</div>
