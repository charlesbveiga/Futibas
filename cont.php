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

// Verificar se há uma requisição AJAX para atualizar um campo específico
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['campo']) && isset($_POST['valor'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $campo = $conn->real_escape_string($_POST['campo']);
    $valor = $conn->real_escape_string($_POST['valor']);

    // Atualizar o campo editado no banco de dados
    $sql = "UPDATE colaboracao_caixa SET $campo='$valor' WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        // Recalcular o total para a linha atual
        $sql = "SELECT * FROM colaboracao_caixa WHERE id='$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        $totalPorLinha = 0;
        foreach (['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'] as $mes) {
            $totalPorLinha += (float)$row[$mes];
        }

        // Atualizar o total para a linha atual
        $sqlUpdateTotal = "UPDATE colaboracao_caixa SET total='$totalPorLinha' WHERE id='$id'";
        $conn->query($sqlUpdateTotal);

        // Recalcular os totais mensais e o total arrecadado
        $totaisMensais = array_fill_keys(['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'], 0);
        $totalArrecadado = 0;

        $sqlAllRows = "SELECT * FROM colaboracao_caixa";
        $resultAllRows = $conn->query($sqlAllRows);
        while ($rowAll = $resultAllRows->fetch_assoc()) {
            foreach (['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'] as $mes) {
                $totaisMensais[$mes] += (float)$rowAll[$mes];
            }
            $totalArrecadado += (float)$rowAll['total'];
        }

        // Atualizar os totais mensais e o total arrecadado
        $sqlUpdateTotals = "UPDATE colaboracao_caixa SET total_jan='{$totaisMensais['jan']}', total_fev='{$totaisMensais['fev']}', total_mar='{$totaisMensais['mar']}', total_abr='{$totaisMensais['abr']}', total_mai='{$totaisMensais['mai']}', total_jun='{$totaisMensais['jun']}', total_jul='{$totaisMensais['jul']}', total_ago='{$totaisMensais['ago']}', total_sete='{$totaisMensais['set']}', total_outu='{$totaisMensais['out']}', total_nov='{$totaisMensais['nov']}', total_dez='{$totaisMensais['dez']}', total_arrecadado='$totalArrecadado'";
        $conn->query($sqlUpdateTotals);

        echo "Sucesso";
    } else {
        echo "Erro ao atualizar registro: " . $conn->error;
    }
    exit();
}

// Recuperar dados da tabela
$sql = "SELECT * FROM colaboracao_caixa";
$result = $conn->query($sql);

// Inicializar os totais mensais
$totaisMensais = array_fill_keys(['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'], 0);
$totalArrecadado = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboração para o Caixa 2024</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="./src/styles.css">
   
    <script>
        $(document).ready(function () {
    // Função para tornar a célula editável
    $('.editable').click(function() {
        var originalContent = $(this).text();
        var id = $(this).data('id');
        var campo = $(this).data('campo');
        
        $(this).addClass('cellEditing');
        $(this).html('<input type="text" value="' + originalContent + '" />');
        $(this).children().first().focus();

        // Salva a alteração ao sair do campo de edição
        $(this).children().first().keypress(function(e) {
            if (e.which == 13) { // 13 é o código da tecla Enter
                var newContent = $(this).val();
                $(this).parent().text(newContent);
                $(this).parent().removeClass('cellEditing');

                // Enviar a atualização para o servidor via AJAX
                $.ajax({
                    url: 'cont.php',
                    type: 'POST',
                    data: {
                        id: id,
                        campo: campo,
                        valor: newContent
                    },
                    success: function(response) {
                        console.log(response);
                        alert("Alteração salva com sucesso!");
                    }
                });
            }
        });

        // Cancela a edição ao perder o foco
        $(this).children().first().blur(function() {
            $(this).parent().text(originalContent);
            $(this).parent().removeClass('cellEditing');
        });
    });
});
    </script>
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
       
    <h1 class="title-centralizado">Colaboração para o Caixa 2024</h1>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Presença</th>
                <th>Jan</th>
                <th>Fev</th>
                <th>Mar</th>
                <th>Abr</th>
                <th>Mai</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Ago</th>
                <th>Set</th>
                <th>Out</th>
                <th>Nov</th>
                <th>Dez</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td class='editable' data-id='" . $row['id'] . "' data-campo='nome'>" . htmlspecialchars($row['nome']) . "</td>";
                    echo "<td class='editable' data-id='" . $row['id'] . "' data-campo='nome'>" . htmlspecialchars($row['presenca']) . "</td>";
                    
                    // Calcular o total de cada linha e atualizar os totais mensais
                    $totalPorLinha = 0;
                    foreach (['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'] as $mes) {
                        $valorMes = htmlspecialchars($row[$mes]);
                        echo "<td class='editable' data-id='" . $row['id'] . "' data-campo='$mes'>$valorMes</td>";
                        $totaisMensais[$mes] += (float)$row[$mes];
                        $totalPorLinha += (float)$row[$mes];
                    }
                    $totalArrecadado += $totalPorLinha;
                    echo "<td class='resultado'>" . $totalPorLinha . "</td>";
                    echo "<td class='resultado'><a href='delete.php?id=" . $row['id'] . "' onclick=\"return confirm('Tem certeza que deseja excluir este registro?');\">Excluir</a></td>";
                    echo "</tr>";
                }

                // Exibir totais mensais
                echo "<tr><td colspan='2'><strong style='color: #333;'>Total Mensal</strong></td>";
                foreach (['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'] as $mes) {
                    echo "<td><strong style='color: #333;'>" . $totaisMensais[$mes] . "</strong></td>";
                }
                echo "<td><strong style='color: #333;'>" . $totalArrecadado . "</strong></td><td></td></tr>";
            } else {
                echo "<tr><td colspan='17'>Nenhum dado encontrado.</td></tr>";
            }
            ?> 
        </tbody>
    </table>
        </header>
    <script src="js/toogle.js"></script>


    <?php
    // Fecha a conexão
    $conn->close();
    ?>
</body>
</html>
