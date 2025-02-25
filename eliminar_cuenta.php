<?php 
// eliminar_cuenta.php
require 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['idUsuario'])) {
    $idUsuario = $data['idUsuario'];

    $stmt = $pdo->prepare("DELETE FROM usuario WHERE idUsuario = ?");
    if ($stmt->execute([$idUsuario])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}


?>