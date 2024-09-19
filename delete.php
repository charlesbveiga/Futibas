<?php
// delete.php

include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM colaboracao_caixa WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: cont.php');
        exit();
    } else {
        echo "Erro ao excluir registro: " . $conn->error;
    }
}
?>

