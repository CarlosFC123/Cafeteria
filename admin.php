<?php

require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nbUsuario'];
    $apellido = $_POST['apellidoUsuario'];
    $telefono = $_POST['numTelefonoU'];
    $contrasena = $_POST['pwdContraseña'];
    $idRol = $_POST['idRol'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("INSERT INTO usuario (nbUsuario, apellidoUsuario, numTelefonoU, pwdContraseña, idRol, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $apellido, $telefono, $contrasena, $idRol, $email]);

    header("Location: admin_dashboard.php");
    
    exit;
}
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Agregar Usuario</h4>
        <div class="card p-4">
            <form method="POST" action="admin.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nbUsuario" class="form-control" placeholder="Ingrese el nombre" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellidoUsuario" class="form-control" placeholder="Ingrese el apellido" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="numTelefonoU" class="form-control" placeholder="Ingrese el teléfono" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Ingrese el email" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contraseña</label>
                        <input type="text" name="pwdContraseña" class="form-control" placeholder="Ingrese la contraseña" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select name="idRol" class="form-select" required>
                            <option value="" selected>-- Seleccione un rol --</option>
                            <option value="1">Cliente</option>
                            <option value="2">Administrador</option>
                            <option value="3">Encargado de pedidos</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="admin_dashboard.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
