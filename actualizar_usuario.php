<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$nuevaContrasena = $_POST['nuevaContrasena'];

try {
    $query = "UPDATE usuario SET nbUsuario = ?, apellidoUsuario = ?, numTelefonoU = ?, email = ?";
    $params = [$nombre, $apellido, $telefono, $correo];

    if (!empty($nuevaContrasena)) {
        
        $query .= ", pwdContraseña = ?";
        $params[] = $nuevaContrasena;
    }

    $query .= " WHERE idUsuario = ?";
    $params[] = $usuario_id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>