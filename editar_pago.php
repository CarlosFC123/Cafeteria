<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }


require 'db_connection.php';

// Verificar si se pasa un idPagos en la URL
if (isset($_GET['id'])) {
    $idPagos = $_GET['id'];

    // Obtener los detalles del pago a editar
    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE idPagos = ?");
    $stmt->execute([$idPagos]);
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra el pago
    if (!$pago) {
        echo "<script>alert('Pago no encontrado'); window.location.href='pagos.php';</script>";
        exit;
    }
}

// Manejo de formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPagos = $_POST['idPagos'];
    $fePago = $_POST['fePago'];
    $metodoPago = $_POST['metodoPago'];

    // Actualizar el pago en la base de datos
    $stmt = $pdo->prepare("UPDATE pagos SET fePago = ?, metodoPago = ? WHERE idPagos = ?");
    $stmt->execute([$fePago, $metodoPago, $idPagos]);

    // Redirigir después de guardar
    header('Location: pagos.php');
    exit;
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Editar Pago</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="idPagos" value="<?php echo htmlspecialchars($pago['idPagos']); ?>">

                <div class="mb-3">
                    <label for="idOrden" class="form-label">ID Orden</label>
                    <input type="text" class="form-control" id="idOrden" value="<?php echo htmlspecialchars($pago['idOrden']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="canTotalP" class="form-label">Cantidad Total</label>
                    <input type="text" class="form-control" id="canTotalP" value="<?php echo htmlspecialchars($pago['canTotalP']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="fePago" class="form-label">Fecha y Hora de Pago</label>
                    <input type="datetime-local" class="form-control" id="fePago" name="fePago" value="<?php echo date('Y-m-d\TH:i', strtotime($pago['fePago'])); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="metodoPago" class="form-label">Método de Pago</label>
                    <select class="form-select" id="metodoPago" name="metodoPago" required>
                        <option value="Efectivo" <?php echo $pago['metodoPago'] == 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="Transferencia" <?php echo $pago['metodoPago'] == 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="pagos.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
