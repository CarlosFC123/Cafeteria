<?php 
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $nmTelefono = trim($_POST['numTelefonoU']);
    $password = trim($_POST['password']);

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
            $error = "Este correo electrónico ya está registrado.";
        } elseif ($userPhone) {
            $error = "Este número de teléfono ya está registrado.";
        } else {
            // Insertar el nuevo usuario en la base de datos
            $stmt = $pdo->prepare("INSERT INTO usuario (nbUsuario, apellidoUsuario, email, numTelefonoU, pwdContraseña, idRol) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([$nombre, $apellido, $email, $nmTelefono, $password, 1]); 

            // Redirigir a la página de inicio de sesión
            header("Location: login.php");
            exit;
        }
    } else {
        $error = "Por favor, llena todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 380px;
            padding: 40px;
            text-align: center;
        }
        .register-container h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
        .register-container input {
            width: 100%;
            padding: 15px 15px 15px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .register-container button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .register-container button:hover {
            background: linear-gradient(to right, #764ba2, #667eea);
        }
        .error-message {
            color: #ff4961;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .iniciarsesion{
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Registro</h1>
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="nombre" placeholder="Nombre" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="apellido" placeholder="Apellido" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="numTelefonoU" placeholder="Número de Telefono" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Registrar</button>
        </form>

        <p class="iniciarsesion">¿Ya tienes cuenta? <a href="login.php" style="color: red; text-decoration: none; ">Inicia sesión aquí</a></p>
    </div>
</body>
</html>