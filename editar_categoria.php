<?php
require 'db_connection.php';

// Obtener la categoría seleccionada desde la URL
$categoriaSeleccionada = null;
if (isset($_GET['id'])) {
    $idCategoria = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    $categoriaSeleccionada = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoriaSeleccionada) {
        echo "<script>alert('Categoría no encontrada'); window.location.href='categorias.php';</script>";
        exit;
    }
}

// Procesar actualización de datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $idCategoria = $_POST['idCategoria'];
    $nombre = $_POST['nbCategoria'];

    $stmt = $pdo->prepare("UPDATE categorias SET nbCategoria=? WHERE idCategoria=?");
    $stmt->execute([$nombre, $idCategoria]);

    header("Location: categorias.php");
    exit;
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Editar Categoría</h4>
        <div class="card p-4">
            <form method="POST">
                <input type="hidden" name="idCategoria" value="<?php echo $categoriaSeleccionada['idCategoria']; ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" name="nbCategoria" class="form-control" placeholder="Nuevo Nombre de Categoría" value="<?php echo htmlspecialchars($categoriaSeleccionada['nbCategoria']); ?>" required>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
                        <a href="categorias.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>