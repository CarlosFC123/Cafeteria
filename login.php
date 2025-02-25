<?php 
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Consulta ajustada para verificar el email en la base de datos
        $stmt = $pdo->prepare("SELECT idUsuario, nbUsuario, apellidoUsuario, pwdContraseña, idRol FROM usuario WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificación de la contraseña (sin cifrado)
        if ($user && $password === $user['pwdContraseña']) {
            $_SESSION['usuario_id'] = $user['idUsuario'];
            $_SESSION['usuario_nombre'] = $user['nbUsuario'];
            $_SESSION['usuario_apellido'] = $user['apellidoUsuario'];
            $_SESSION['usuario_rol'] = $user['idRol'];

            // Redirigir según el rol del usuario
            if ($user['idRol'] == 2) {
                header("Location: menu.php");
            } else {
                header("Location: empleado_dashboard.php");
            }
            exit;
        } else {
            $error = "Correo electrónico o contraseña incorrectos.";
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
    <title>Iniciar Sesión</title>
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
        /* body {
            font-family: Arial, sans-serif;
            background: url('./img/cafeteriafondo.jpeg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        } */

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 380px;
            padding: 40px;
            text-align: center;
        }
        .login-container h2 {
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
        .toggle-password {
            left: 245px;
            margin-left: 245px;
            cursor: pointer;
            font-size: 18px;
        }
        .login-container input {
            width: 100%;
            padding: 15px 15px 15px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-container button {
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
        .login-container button:hover {
            background: linear-gradient(to right, #764ba2, #667eea);
        }
        .error-message {
            color: #ff4961;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .login-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
            border: 3px solid rgb(255, 255, 255);
        }
        .registroU{
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Cafetería UTR</h1>
        <img src="./img/logocafe.jpeg" alt="Usuario" class="login-image">
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Contraseña" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            <button type="submit">Iniciar Sesión</button>
            <p class="registroU">¿No tienes cuenta? <a href="registroUsuario.php" style="color: red; text-decoration: none; ">Regístrate aquí</a></p>
        </form>
    </div>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            if (passwordField) {
                var passwordFieldType = passwordField.getAttribute("type");
                passwordField.setAttribute("type", passwordFieldType === "password" ? "text" : "password");
            }
        }
    </script>
</body>
</html>
