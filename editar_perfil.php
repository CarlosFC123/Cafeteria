<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST['idUsuario'];
    $nombre = $_POST['nbUsuario'];
    $apellido = $_POST['apellidoUsuario'];
    $email = $_POST['email'];
    $password = $_POST['pwdContraseña'];

    // Actualizar los datos del usuario
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, pwdContraseña=? WHERE id=?");
        $stmt->execute([$nombre, $apellido, $email, $hashedPassword, $idUsuario]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=? WHERE id=?");
        $stmt->execute([$nombre, $apellido, $email, $idUsuario]);
    }

    // Actualizar la sesión con los nuevos datos
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_apellido'] = $apellido;
    $_SESSION['usuario_email'] = $email; // Actualiza el correo en la sesión

    echo json_encode(['status' => 'success']);
}
?>