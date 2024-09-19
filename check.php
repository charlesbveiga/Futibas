<?php
// index.php

include 'db_connect.php';

// Verificar se há uma requisição POST para adicionar novo nome
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome'])) {
    $nome = $conn->real_escape_string($_POST['nome']);
    $sql = "INSERT INTO colaboracao_caixa (nome) VALUES ('$nome')";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erro ao adicionar registro: " . $conn->error;
    }
}

// Consulta para obter todos os jogadores
$players = [];
$sql = "SELECT id, nome, presenca, jan FROM colaboracao_caixa";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
}

// Inicializa variáveis
$valorQuadra = 100.00;
$valorJogador = 0.00;
$contribuicao = 0.00;
$contribuicaoPartida = 0.00;
$totalPresent = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['players'])) {
    // Calcula o total de jogadores presentes
    $totalPresent = count($_POST['players']);
    
    // Calcula o valor total arrecadado pelos jogadores presentes
    $valorJogador = $totalPresent * 10.00;

    // Calcula a diferença entre o valor da quadra e o valor arrecadado
    if ($valorJogador >= $valorQuadra) {
        $contribuicaoAdicional = 0;
    } else {
        $contribuicaoAdicional = $valorQuadra - $valorJogador;
    }

    // Calcula a contribuição adicional por jogador se houver déficit
    if ($totalPresent > 0) {
        $contribuicaoPartida = $contribuicaoAdicional / $totalPresent;
    }
    
    // Calcula a contribuição com o caixa
    $contribuicao = $valorJogador - $valorQuadra;
    
    // Calcula a contribuição com o caixa por jogador
    if ($totalPresent <= 10) {
        $contribuicaoJogador = 0;
    } else {
        $contribuicaoJogador = $contribuicao / $totalPresent;
    }

    // Atualiza o banco de dados para cada jogador presente
    foreach ($_POST['players'] as $playerId) {
        // Atualiza o campo 'presenca' somando +1
        $sqlUpdatePresenca = "UPDATE colaboracao_caixa SET presenca = presenca + 1 WHERE id = $playerId";
        $conn->query($sqlUpdatePresenca);

        // Atualiza o campo 'jan' somando o valor da contribuição por jogador
        $sqlUpdateJan = "UPDATE colaboracao_caixa SET sete = sete + $contribuicaoJogador WHERE id = $playerId";
        $conn->query($sqlUpdateJan);
    }
    
        // Redireciona para evitar resubmissão do formulário ao recarregar a página
    	header('Location: ' . $_SERVER['PHP_SELF']);
   	exit();
    
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist de Jogadores</title>
    <link rel="icon" href=".src/futibas.png" type="image/x-icon">    <!-- Favicon -->
    <link rel="stylesheet" href="./src/styles.css">
</head>
<body>
<header>
    <nav class="nav-bar">
        <div class="logo">
            <img src="./src/futibas.png" alt="Logo Futibas">       
        </div>

        <div class="nav-list">
                <ul>
                    <li class="nav-item"><a href="https://cbvportfolio.com.br/futibas/" class="nav-link">Inicio</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">Partidas</a></li>
                    <li class="nav-item"><a href="https://cbvportfolio.com.br/futibas/atletas.html" class="nav-link">Atletas</a></li>
                    <li class="nav-item"><a href="https://cbvportfolio.com.br/futibas/cont.php" class="nav-link">Contribuição</a></li>
                    <li class="nav-item"><a href="https://cbvportfolio.com.br/futibas/check.php" class="nav-link">Check</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">Loja</a></li>
                </ul>
            </div>

            <div class="login-button">
                <a href="#">Entrar</a>
                <button><a href="#">REGISTRE-SE</a></button>
            </div>

            <div class="mobile-menu-icon">
                <button onclick="menuShow()"><img class="icon" src="./src/img/menu_white_36dp.svg" alt="Ícone menu toogle"></button>
            </div>
        </nav>
        <div class="mobile-menu">
            <ul>
                <li>
                    <a href="https://cbvportfolio.com.br/futibas/">Inicio</a>
                </li>
                <li>
                    <a href="#">Partidas</a>
                </li>
                <li>
                    <a href="https://cbvportfolio.com.br/futibas/atletas.html">Atletas</a>
                </li>
                <li>
                    <a href="https://cbvportfolio.com.br/futibas/cont.php">Contribuição</a>
                </li>
                <li>
                    <a href="https://cbvportfolio.com.br/futibas/check.php">Check</a>
                </li>
                <li>
                    <a href="#">Loja</a>
                </li>
            </ul>
            <div class="login-button2">
                <a href="#">Entrar</a>
                <button><a href="#">REGISTRE-SE</a></button>
            </div>
        </div>
    </nav>
    <!-- Formulário de Adição de Jogador-->
    <div class="form-section">
        <h2>Adicionar Novo Jogador</h2>
        <form action="cont.php" method="post">
            <label for="nome"><strong>Nome:</strong></label>
            <input class="adicionar" type="text" id="nome" name="nome" required>
            <input type="submit" value="Adicionar">
        </form>
    </div>

    <div class="player-form">    
        <h1>Checklist de Jogadores</h1>

        <form method="post" action="">
            <ul class="player-list">
            <?php foreach ($players as $player): ?>
                <li>
                    <input type="checkbox" name="players[]" value="<?php echo $player['id']; ?>"> 
                    <?php echo htmlspecialchars($player['nome']); ?>
                </li>
            <?php endforeach; ?>
            </ul>
            <div class="enter-button">
                <button type="submit">Enviar</button>
            </div>
        </form>

    <div class="result">
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <p>Total de jogadores presentes: <?php echo $totalPresent; ?></p>
            <p>Valor da quadra: R$ <?php echo number_format($valorQuadra, 2, ',', '.'); ?></p>
            <p>Valor arrecadado pelos jogadores presentes: R$ <?php echo number_format($valorJogador, 2, ',', '.'); ?></p>
            <p>Diferença a ser coberta (Contribuição): R$ <?php echo number_format($contribuicaoAdicional, 2, ',', '.'); ?></p>
            <p>Contribuição adicional por jogador: R$ <?php echo number_format($contribuicaoPartida, 2, ',', '.'); ?></p>
            <p>Contribuição com o caixa: R$ <?php echo number_format($contribuicao, 2, ',', '.'); ?></p>
            <p>Contribuição Por Jogador: R$ <?php echo number_format($contribuicaoJogador, 9, ',', '.'); ?></p>
        <?php endif; ?>
    </div>
</header>
    <script src="js/toogle.js"></script>
</div>    
<?php   
// Fecha a conexão
$conn->close();
?>

</body>
</html>

