<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Respuesta JSON
    $response = [];

    if (!empty($email) && !empty($password)) {
        // Buscar el usuario en la base de datos
        $stmt = $pdo->prepare("SELECT idUsuario, nbUsuario, apellidoUsuario, pwdContraseña FROM usuario WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar la contraseña (si no está hasheada, compara directamente)
            if ($password === $user['pwdContraseña']) {
                $response['status'] = 'success';
                $response['message'] = 'Inicio de sesión exitoso.';
                $response['nbUsuario'] = $user['nbUsuario']; // Nombre del usuario
                $response['apellidoUsuario'] = $user['apellidoUsuario']; // Apellido del usuario
                $response['userId'] = $user['idUsuario']; // Opcional: devolver el ID del usuario
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Contraseña incorrecta.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'El correo electrónico no está registrado.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Por favor, completa todos los campos.';
    }

    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>