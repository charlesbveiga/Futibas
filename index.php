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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboração para o Caixa 2024</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Estilo Geral */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }

	/* Cabeçalho */
	header {
	    background-color: #ff6600; /* Cor laranja */
	    color: white;
	    text-align: center;
	    padding: 20px 0;
	    position: relative;
	}


	header img {
	    width: 100px; /* Diminui um pouco o tamanho da logo para caber na moldura */
	    height: 100px; /* Garante que a moldura seja circular */
	    border-radius: 50%; /* Moldura arredondada */
	    position: absolute;
	    top: 70%; /* Centraliza verticalmente */
	    left: 20%; /* Posiciona a logo entre o 1/4 e o 2/4 da largura */
	    transform: translate(-50%, -50%); /* Centraliza a logo horizontalmente */
	}

	/* Estilo do título */
	header h1 {
	    font-size: 2.5em;
	    margin: 0;
	    display: inline-block; /* Mantém o título inline */

	}
	
	/* Estilo para o link no cabeçalho */
	.header-link {
	    position: absolute;
	    top: 20px;
	    right: 100px;
	    font-size: 1.1em;
	    color: white;
	    text-decoration: none;
	    background-color: #333;
	    padding: 10px 15px;
	    border-radius: 5px;
	    transition: background-color 0.3s ease;
	}

	.header-link:hover {
	    background-color: #555; /* Cor de fundo ao passar o mouse */
	}


        /* Estilo da Tabela */
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        td.editable {
            cursor: pointer;
            background-color: #f9f9f9;
        }

        tr:nth-child(even) {
            background-color: #e9e9e9; /* Cinza claro */
        }

        tr:hover {
            background-color: #ddd;
        }

        .cellEditing input {
            width: 100%;
            border: none;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-section {
            width: 90%;
            margin: 20px auto;
            text-align: center;
        }

        .form-section input[type="text"],
        .form-section input[type="submit"] {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-section input[type="submit"] {
            background-color: #333;
            color: white;
            cursor: pointer;
        }

        .form-section input[type="submit"]:hover {
            background-color: #ff6600;
        }

        /* Responsividade */
	@media (max-width: 800px) {
	    header img {
		width: 80px; /* Reduz o tamanho da logo */
		height: 80px;
		left: 10%; /* Ajusta a posição horizontal da logo */
	    }

	    header h1 {
		font-size: 2em; /* Reduz o tamanho do título */
		padding-left: 100px; /* Ajusta o espaço para a logo */
	    }

	    .header-link {
		right: 20px; /* Ajusta o posicionamento do link */
		font-size: 1em; /* Reduz o tamanho da fonte do link */
	    }
        
	    table {
                width: 100%;
            }
        }
        
        @media (max-width: 650px) {
	    header {
		position: relative;
		text-align: center; /* Centraliza o conteúdo */
	    }

	    header img {
		width: 70px; /* Reduz o tamanho da logo */
		height: 70px;
		margin: 10px auto; /* Centraliza a logo horizontalmente */
		display: block; /* Garante que a logo fique como um bloco abaixo do título */
		position: relative; /* Remove o posicionamento absoluto */
		margin-top: 10px; /* Espaçamento entre o título e a logo */
	    }

	    header h1 {
		font-size: 1.8em; /* Reduz o tamanho do título */
		margin: 0; /* Remove margens para garantir alinhamento */
		display: inline-block;
	    }

	    .header-link {
		position: absolute;
		top: 50%; /* Alinha o link verticalmente com o título */
		right: 10px;
		transform: translateY(-50%); /* Ajusta a posição do link para o centro vertical */
		font-size: 1em;
	    }

	    .form-section {
		margin-top: 20px; /* Garante que o formulário tenha um espaçamento abaixo da logo */
	    }
	    
	    table {
                width: 80%;
            }
	}


        @media (max-width: 480px) {
        header img {
		width: 60px; /* Reduz ainda mais o tamanho da logo em telas pequenas */
		height: 60px;
		margin: 0% 50% 0% 0%;                                                     
		transform: translateX(-50%);
    	}

    	header h1 {
       	 	font-size: 1.5em; /* Reduz o tamanho do título para telas pequenas */
        	padding-left: 0; /* Remove o espaçamento à esquerda para centralizar o título */
        	margin: 0% 0% 0% 0%;
    	}

    	.header-link {
        	top: 100px; /* Posiciona o link abaixo da logo e do título */
        	right: 10px;
        	ont-size: 0.9em; /* Ajusta o tamanho do texto do link */
    	}
    	
    	.form-section {
		margin-top: 20px; /* Garante que o formulário tenha um espaçamento abaixo da logo */
	}
    
	table {
                width: 60%;
        }
    }
    </style>
    
    <script>
        $(document).ready(function() {
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
                            url: 'index.php',
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

    <!-- Cabeçalho -->
    <header>
        <img src="futibas.jpg" alt="Logo Futibas"> 
        <h1>Futibas de Segunda</h1>
        
        <a href="check.php" class="header-link">Check List</a>

    </header>

    <!-- Formulário de Adição de Jogador-->
    <div class="form-section">
    	<h2>Adicionar Novo Jogador</h2>
    	<form action="index.php" method="post">
        	<label for="nome">Nome:</label>
        	<input type="text" id="nome" name="nome" required>
        	<input type="submit" value="Adicionar">
    	</form>
    </div>
    
    <h1 style="text-align: center;">Colaboração para o Caixa 2024</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
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
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
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
                    echo "<td>" . $totalPorLinha . "</td>";
                    echo "<td><a href='delete.php?id=" . $row['id'] . "' onclick=\"return confirm('Tem certeza que deseja excluir este registro?');\">Excluir</a></td>";
                    echo "</tr>";
                }

                // Exibir totais mensais
                echo "<tr><td colspan='2'><strong>Total Mensal</strong></td>";
                foreach (['','jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'sete', 'outu', 'nov', 'dez'] as $mes) {
                    echo "<td><strong>" . $totaisMensais[$mes] . "</strong></td>";
                }
                echo "<td><strong>" . $totalArrecadado . "</strong></td><td></td></tr>";
            } else {
                echo "<tr><td colspan='17'>Nenhum dado encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php
    // Fecha a conexão
    $conn->close();
    ?>
</body>
</html>

