<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $nmTelefono = trim($_POST['numTelefonoU']);
    $password = trim($_POST['password']);

    // Respuesta JSON
    $response = [];

    if (!empty($nombre) && !empty($apellido) && !empty($email) && !empty($nmTelefono) && !empty($password)) {
        // Verificar si el correo electrónico ya está registrado
        $stmt = $pdo->prepare("SELECT idUsuario FROM usuario WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $userEmail = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el número de teléfono ya está registrado
        $stmt = $pdo->prepare("SELECT idUsuario FROM usuario WHERE numTelefonoU = :numTelefonoU");
        $stmt->bindParam(":numTelefonoU", $nmTelefono, PDO::PARAM_STR);
        $stmt->execute();
        $userPhone = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userEmail) {
            $response['status'] = 'error';
            $response['message'] = 'Este correo electrónico ya está registrado.';
        } elseif ($userPhone) {
            $response['status'] = 'error';
            $response['message'] = 'Este número de teléfono ya está registrado.';
        } else {
            // Insertar el nuevo usuario en la base de datos (sin encriptar la contraseña)
            $stmt = $pdo->prepare("INSERT INTO usuario (nbUsuario, apellidoUsuario, email, numTelefonoU, pwdContraseña, idRol) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$nombre, $apellido, $email, $nmTelefono, $password, 1])) {
                $response['status'] = 'success';
                $response['message'] = 'Usuario registrado correctamente.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error al registrar el usuario.';
            }
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Por favor, llena todos los campos.';
    }

    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>