<?php
// edit.php

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    // Processar edição do registro
    $id = $_POST['edit_id'];
    $nome = $conn->real_escape_string($_POST['nome']);
    $jan = $_POST['jan'];
    $fev = $_POST['fev'];
    // Continue com os outros campos...

    $sql = "UPDATE colaboracao_caixa SET nome='$nome', jan='$jan', fev='$fev' WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: index.php');
        exit();
    } else {
        echo "Erro ao editar registro: " . $conn->error;
    }
} elseif (isset($_GET['id'])) {
    // Recuperar dados para exibir no formulário de edição
    $id = $_GET['id'];
    $sql = "SELECT * FROM colaboracao_caixa WHERE id='$id'";
    $edit_result = $conn->query($sql);
    $edit_data = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro</title>
</head>
<body>
    <h2>Editar Nome</h2>
    <form action="edit.php" method="post">
        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($edit_data['nome']); ?>" required>
        <label for="jan">Jan:</label>
        <input type="text" id="jan" name="jan" value="<?php echo htmlspecialchars($edit_data['jan']); ?>" required>
        <!-- Continue com os campos para cada mês -->
        <input type="submit" value="Salvar">
    </form>
</body>
</html>

