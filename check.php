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
    $contribuicaoJogador = $contribuicao / $totalPresent;

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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist de Jogadores</title>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

	/* Cabeçalho */
	header {
	    background-color: #ff6600; /* Cor laranja */
	    color: white;
	    text-align: center;
	    padding: 20px 0;
	    position: relative;
	}

	/* Responsividade da logo */
	header img {
	    width: 100px; /* Mantém o tamanho padrão da logo */
	    height: 100px; /* Garante que a moldura seja circular */
	    border-radius: 50%; /* Moldura arredondada */
	    position: absolute;
	    top: 50%; /* Centraliza verticalmente */
	    left: 15%; /* Posiciona a logo mais à esquerda em telas maiores */
	    transform: translate(-50%, -50%);
	}

	/* Estilo do título */
	header h1 {
	    font-size: 2.5em;
	    margin: 0;
	    padding-left: 140px; /* Espaço para a logo */
	    display: inline-block;
	}

	/* Estilo para o link no cabeçalho */
	.header-link {
	    position: absolute;
	    top: 20px;
	    right: 30px;
	    font-size: 1.1em;
	    color: white;
	    text-decoration: none;
	    background-color: #333;
	    padding: 10px 15px;
	    border-radius: 5px;
	    transition: background-color 0.3s ease;
	}

	.header-link:hover {
	    background-color: #555;
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

        .player-form, .result {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .player-form input[type="text"], .player-form input[type="submit"] {
            padding: 10px;
            margin: 5px 0;
            width: 29%;
            position: left;
            box-sizing: border-box;
        }

        .player-list {
            list-style: none;
            padding: 0;
            columns: 6; /* Dividir em colunas */
        }

        .player-list li {
	    font-size: 0.7em; /* Reduz o tamanho da fonte em 30% */
            padding: 5px;
            background-color: #e9e9e9;
            margin: 5px 0;
            border-radius: 5px;
        }

        .player-list li input {
            margin-right: 10px;
        }

	/* Estilo do botão */
	button[type="submit"],
	input[type="submit"] {
	    background-color: #333;
            color: white;
            border: none;
	    padding: 10px 20px;
	    border-radius: 5px;
	    font-size: 1em;
	    cursor: pointer;
	    transition: background-color 0.3s ease;
	}

	button[type="submit"]:hover,
	input[type="submit"]:hover {
            background-color: #ff6600;
	}

        .result {
            font-weight: bold;
        }

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
        
            .player-list {
                columns: 2;
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
	    
	    .player-list {
                columns: 2;
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
    
        .player-list {
                columns: 1;
        }
    }
    </style>
</head>
<body>
    <header>
       <img src="futibas.jpg" alt="Logo do Futibas"> 
       <h1>Futibas de Segunda</h1>
       
       <a href="index.php" class="header-link">Home</a>

    </header>
    
    <div class="form-section">
    	<h2>Adicionar Novo Jogador</h2>
    	<form method="post" action="">
        	<label for="nome">Nome:</label>
        	<input type="text" id="nome" name="nome" required>
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

        	<button type="submit">Enviar</button>
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
        
    <?php   
    // Fecha a conexão
    $conn->close();
    ?>

</body>
</html>

