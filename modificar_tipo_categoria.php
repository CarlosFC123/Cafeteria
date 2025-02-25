<?php
session_start();
require 'db_connection.php';

// Obtener el ID del tipo desde la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID de tipo no proporcionado.";
    exit;
}

$idTipo = intval($_GET['id']);

// Obtener los datos del tipo de categoría
$stmt = $pdo->prepare("SELECT idTipo, nbTipo FROM tipos_categorias WHERE idTipo = :idTipo");
$stmt->bindParam(':idTipo', $idTipo, PDO::PARAM_INT);
$stmt->execute();
$tipo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tipo) {
    echo "El tipo de categoría no existe.";
    exit;
}

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoNombre = trim($_POST['nbTipo']);

    if (empty($nuevoNombre)) {
        $error = "El nombre del tipo no puede estar vacío.";
    } else {
        // Actualizar el tipo de categoría en la base de datos
        $updateStmt = $pdo->prepare("UPDATE tipos_categorias SET nbTipo = :nbTipo WHERE idTipo = :idTipo");
        $updateStmt->bindParam(':nbTipo', $nuevoNombre, PDO::PARAM_STR);
        $updateStmt->bindParam(':idTipo', $idTipo, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            $_SESSION['success_message'] = "Tipo de categoría actualizado con éxito.";
            header("Location: tipo_categorias.php");
            exit;
        } else {
            $error = "Ocurrió un error al actualizar el tipo.";
        }
    }
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Tipo de Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container py-4">
    <div class="card">
        <div class="card-header">
            <h5>Modificar Tipo de Categoría</h5>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="nbTipo" class="form-label">Nombre del Tipo</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="nbTipo" 
                        name="nbTipo" 
                        value="<?php echo htmlspecialchars($tipo['nbTipo']); ?>" 
                        required
                    >
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="administrar_tipos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
