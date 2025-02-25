<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }

require 'db_connection.php';

// Obtener datos de la orden a editar
if (isset($_GET['id'])) {
    $idOrden = $_GET['id'];
    $orden = $pdo->prepare("SELECT * FROM ordenes WHERE idOrden = ?");
    $orden->execute([$idOrden]);
    $orden = $orden->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        echo "<script>alert('Orden no encontrada.'); window.location.href = 'ordenes.php';</script>";
        exit;
    }
} else {
    echo "<script>window.location.href = 'ordenes.php';</script>";
    exit;
}

// Actualizar los datos de la orden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $idHorario = $_POST['idHorario'];
    $feOrden = $_POST['feOrden'];
    $estado_orden = $_POST['estado_orden'];
    $precioTotalOrdenes = $_POST['precioTotalOrdenes'];
    $codigoOrden = $_POST['codigoOrden'];

    $stmt = $pdo->prepare("UPDATE ordenes SET idUsuario = ?, idHorario = ?, feOrden = ?, estado_orden = ?, precioTotalOrdenes = ?, codigoOrden = ? WHERE idOrden = ?");
    $stmt->execute([$idUsuario, $idHorario, $feOrden, $estado_orden, $precioTotalOrdenes, $codigoOrden, $idOrden]);

    header("Location: ordenes.php");
    exit;
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Orden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Editar Orden</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="idUsuario" class="form-label">ID Usuario</label>
                    <input type="number" class="form-control" id="idUsuario" name="idUsuario" value="<?php echo htmlspecialchars($orden['idUsuario']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="idHorario" class="form-label">ID Horario</label>
                    <input type="number" class="form-control" id="idHorario" name="idHorario" value="<?php echo htmlspecialchars($orden['idHorario']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="feOrden" class="form-label">Fecha Orden</label>
                    <input type="date" class="form-control" id="feOrden" name="feOrden" value="<?php echo htmlspecialchars($orden['feOrden']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="estado_orden" class="form-label">Estado Orden</label>
                    <select class="form-select" id="estado_orden" name="estado_orden" required>
                        <option value="Pendiente" <?php echo ($orden['estado_orden'] === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Completado" <?php echo ($orden['estado_orden'] === 'Completado') ? 'selected' : ''; ?>>Completado</option>
                        <option value="Cancelado" <?php echo ($orden['estado_orden'] === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="precioTotalOrdenes" class="form-label">Precio Total</label>
                    <input type="number" class="form-control" id="precioTotalOrdenes" name="precioTotalOrdenes" step="0.01" value="<?php echo htmlspecialchars($orden['precioTotalOrdenes']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="codigoOrden" class="form-label">Código Orden</label>
                    <input type="text" class="form-control" id="codigoOrden" name="codigoOrden" value="<?php echo htmlspecialchars($orden['codigoOrden']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="ordenes.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
