<?php
session_start();
require 'db_connection.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // Consulta para obtener los datos del usuario
    $query = "SELECT nbUsuario, apellidoUsuario, numTelefonoU, email FROM usuario WHERE idUsuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Devuelve los datos del usuario en formato JSON
        echo json_encode(['success' => true, 'usuario' => $usuario]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>