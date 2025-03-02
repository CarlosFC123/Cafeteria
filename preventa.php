<?php
include 'sidebar.php';
require 'db_connection.php';

$preventas = $pdo->query("
    SELECT 
        idPreventa, 
        nombrePreventa, 
        cantidad_orden,       
        precioUnitarioPreventa, 
        (cantidad_orden * precioUnitarioPreventa) AS precioTotalpreventa,
        metodoPago,
        turnoVentaP,
        estado_pv
    FROM preventa
    ORDER BY idPreventa ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Manejar eliminación de preventas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPreventa'])) {
    $idPreventa = $_POST['idPreventa'];

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Obtener los datos de la preventa antes de eliminarla
        $stmt = $pdo->prepare("SELECT nombrePreventa, cantidad_orden, tipoComida, estado_pv FROM preventa WHERE idPreventa = ?");
        $stmt->execute([$idPreventa]);
        $preventa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($preventa) {
            $nombrePreventa = $preventa['nombrePreventa'];
            $cantidad_orden = $preventa['cantidad_orden'];
            $tipoComida = $preventa['tipoComida'];
            $estado_pv = $preventa['estado_pv'];

            // Solo sumar la cantidad al stock si el estado no es "Completado"
            if ($estado_pv !== 'Completado') {
                // Determinar el tipo de preventa y actualizar la cantidad correspondiente
                if ($tipoComida === 'desayuno') {
                    // Es un desayuno: sumar cantidad_orden a cantidadDesayuno en la tabla desayuno
                    $stmt = $pdo->prepare("UPDATE desayuno SET cantidadDesayuno = cantidadDesayuno + ? WHERE nombreProducto = ?");
                    $stmt->execute([$cantidad_orden, $nombrePreventa]);
                } elseif (strpos($tipoComida, 'almuerzo') !== false) {
                    // Es un almuerzo: determinar si es porción, media u orden
                    $columnaActualizar = '';
                    if ($tipoComida === 'almuerzo-porcion') {
                        $columnaActualizar = 'cantidadPorcion';
                    } elseif ($tipoComida === 'almuerzo-media') {
                        $columnaActualizar = 'cantidadMedia';
                    } elseif ($tipoComida === 'almuerzo-orden') {
                        $columnaActualizar = 'cantidadOrden';
                    }

                    if ($columnaActualizar) {
                        // Sumar cantidad_orden a la columna correspondiente en la tabla almuerzo
                        $stmt = $pdo->prepare("UPDATE almuerzo SET $columnaActualizar = $columnaActualizar + ? WHERE nombreProducto = ?");
                        $stmt->execute([$cantidad_orden, $nombrePreventa]);
                    }
                } elseif ($tipoComida === 'producto') {
                    // Es un producto: sumar cantidad_orden a canActual en la tabla inventario
                    // Primero, obtener el idProducto basado en el nombre del producto
                    $stmt = $pdo->prepare("SELECT idProducto FROM productos WHERE nbProducto = ?");
                    $stmt->execute([$nombrePreventa]);
                    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($producto) {
                        $idProducto = $producto['idProducto'];

                        // Sumar cantidad_orden a canActual en la tabla inventario
                        $stmt = $pdo->prepare("UPDATE inventario SET canActual = canActual + ? WHERE idProducto = ?");
                        $stmt->execute([$cantidad_orden, $idProducto]);
                    } else {
                        throw new Exception("Producto no encontrado en la tabla productos.");
                    }
                }
            }

            // Eliminar la preventa
            $stmt = $pdo->prepare("DELETE FROM preventa WHERE idPreventa = ?");
            $stmt->execute([$idPreventa]);

            // Confirmar la transacción
            $pdo->commit();
            echo "<script>window.location.href='preventa.php';</script>";
            exit;
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Preventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
            cursor: pointer;
        }
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
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Administración de Preventas</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Preventa</th>
                            <th>Nombre Preventa</th>
                            <th>Cantidad Orden</th>
                            <th>Precio Unitario</th>
                            <th>Precio Total</th>
                            <th>Metodo de pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preventas as $preventa): ?>
                        <tr id="preventa-<?php echo $preventa['idPreventa']; ?>">
                            <td><?php echo htmlspecialchars($preventa['idPreventa']); ?></td>
                            <td><?php echo htmlspecialchars($preventa['nombrePreventa']); ?></td>                            
                            <td><?php echo htmlspecialchars($preventa['cantidad_orden']); ?></td>
                            <td>$<?php echo number_format($preventa['precioUnitarioPreventa'], 2); ?></td>
                            <td>$<?php echo number_format($preventa['precioTotalpreventa'], 2); ?></td>
                            <td><?php echo htmlspecialchars($preventa['metodoPago']); ?></td>
                            <td>
                                <form method="POST" action="actualizar_estado.php" style="display:inline;">
                                    <input type="hidden" name="idPreventa" value="<?php echo $preventa['idPreventa']; ?>">
                                    <select name="estado_pv" onchange="this.form.submit()" class="form-select form-select-sm">
                                        <option value="Pendiente" <?php echo ($preventa['estado_pv'] === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="Completado" <?php echo ($preventa['estado_pv'] === 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                        <option value="Cancelado" <?php echo ($preventa['estado_pv'] === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $preventa['idPreventa']; ?>)">
                                        <i class='bx bx-trash'></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(idPreventa) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar formulario para eliminar la preventa
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idPreventa';
                input.value = idPreventa;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>