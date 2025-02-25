<?php

require 'db_connection.php';

$roles = [
    1 => "Cliente",
    2 => "Administrador",
    3 => "Encargado de pedidos"
];

// Obtener el usuario seleccionado desde la URL
$usuarioSeleccionado = null;
if (isset($_GET['id'])) {
    $idUsuario = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE idUsuario = ?");
    $stmt->execute([$idUsuario]);
    $usuarioSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioSeleccionado) {
        echo "<script>alert('Usuario no encontrado'); window.location.href='admin_dashboard.php';</script>";
        exit;
    }
}

// Procesar actualización de datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $idUsuario = $_POST['idUsuario'];
    $nombre = $_POST['nbUsuario'];
    $apellido = $_POST['apellidoUsuario'];
    $telefono = $_POST['numTelefonoU'];
    $email = $_POST['email'];
    $idRol = $_POST['idRol'];

    $stmt = $pdo->prepare("UPDATE usuario SET nbUsuario=?, apellidoUsuario=?, numTelefonoU=?, email=?, idRol=? WHERE idUsuario=?");
    $stmt->execute([$nombre, $apellido, $telefono, $email, $idRol, $idUsuario]);

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
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Editar Usuario</h4>
        <div class="card p-4">
            <form method="POST">
                <input type="hidden" name="idUsuario" value="<?php echo $usuarioSeleccionado['idUsuario']; ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nbUsuario" class="form-control" placeholder="Nuevo Nombre" value="<?php echo htmlspecialchars($usuarioSeleccionado['nbUsuario']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellidoUsuario" class="form-control" placeholder="Nuevo Apellido" value="<?php echo htmlspecialchars($usuarioSeleccionado['apellidoUsuario']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="numTelefonoU" class="form-control" placeholder="Nuevo Teléfono" value="<?php echo htmlspecialchars($usuarioSeleccionado['numTelefonoU']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nuevo Email" value="<?php echo htmlspecialchars($usuarioSeleccionado['email']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select name="idRol" class="form-select" required>
                            <option value="">-- Seleccionar Rol --</option>
                            <?php foreach ($roles as $key => $rol): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($usuarioSeleccionado['idRol'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $rol; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
                        <a href="admin_dashboard.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

