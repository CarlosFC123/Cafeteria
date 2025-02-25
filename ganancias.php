<?php 
session_start(); 
include 'sidebar.php'; 
require 'db_connection.php'; 

// Establecer la zona horaria correcta
date_default_timezone_set('America/Merida');

// Obtener la fecha actual
$fechaActual = date('Y-m-d');

// Obtener los parámetros de filtro
$fechaInicioG = $_GET['fechaInicioG'] ?? null;
$fechaFinalG = $_GET['fechaFinalG'] ?? null;

// Construir la consulta con filtros de fechas y excluyendo "diario"
$query = "SELECT idGanancias, feInicioG, feFinalG, capital_inicial, ingresos, totalGanancias, tipoPeriodo FROM ganancias WHERE tipoPeriodo IN ('semanal', 'mensual', 'anual')";
$params = [];
if ($fechaInicioG && $fechaFinalG) {
    $query .= " AND feInicioG >= :fechaInicioG AND feFinalG <= :fechaFinalG";
    $params = ['fechaInicioG' => $fechaInicioG, 'fechaFinalG' => $fechaFinalG];
}
$query .= " ORDER BY feInicioG DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ganancias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lógica para obtener ingresos si ya existen datos para esas fechas
$ingresosTotales = 0;
$totalGanancias = 0;
$showIngreso = false;

if ($fechaInicioG && $fechaFinalG) {
    $checkGananciaQuery = "SELECT ingresos, totalGanancias FROM ganancias WHERE feInicioG = :fechaInicioG AND feFinalG = :fechaFinalG LIMIT 1";
    $checkGananciaStmt = $pdo->prepare($checkGananciaQuery);
    $checkGananciaStmt->execute(['fechaInicioG' => $fechaInicioG, 'fechaFinalG' => $fechaFinalG]);
    $existingGanancia = $checkGananciaStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingGanancia) {
        // Si ya existen datos con esas fechas, mostrar los ingresos y ganancias
        $ingresosTotales = $existingGanancia['ingresos'];
        $totalGanancias = $existingGanancia['totalGanancias'];
        $showIngreso = true;
    }
}

// Manejar la lógica para agregar nueva ganancia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $feInicioG = $_POST['feInicioG'];
    $feFinalG = $_POST['feFinalG'];
    $capitalInicial = $_POST['capital_inicial'];

    // Convertir las fechas a objetos DateTime para calcular la diferencia
    $fechaInicio = new DateTime($feInicioG);
    $fechaFinal = new DateTime($feFinalG);

    // Calcular la diferencia entre las fechas
    $diferencia = $fechaInicio->diff($fechaFinal);

    // Determinar el tipo de periodo (semanal, mensual o anual)
    if ($diferencia->days <= 7) {
        $tipoPeriodo = 'semanal';
    } elseif ($diferencia->days <= 30) {
        $tipoPeriodo = 'mensual';
    } else {
        $tipoPeriodo = 'anual';
    }

    // Calcular ingresos solo si la fecha actual está dentro del rango del periodo
    if ($fechaActual >= $feInicioG && $fechaActual <= $feFinalG) {
        $ingresosQuery = "SELECT SUM(totalVenta) AS ingresosTotales FROM ventas WHERE estadoVenta = 'Completada' AND turnoVenta IN ('Descanso 1', 'Descanso 2') AND fechaVenta BETWEEN :feInicioG AND :feFinalG";
        $ingresosStmt = $pdo->prepare($ingresosQuery);
        $ingresosStmt->execute(['feInicioG' => $feInicioG, 'feFinalG' => $feFinalG]);
        $ingresosResult = $ingresosStmt->fetch(PDO::FETCH_ASSOC);
        $ingresosTotales = $ingresosResult['ingresosTotales'] ?? 0;
    } else {
        $ingresosTotales = 0;
    }

    $totalGanancias = $ingresosTotales - $capitalInicial;

    // Insertar en la base de datos
    $nuevaGananciaQuery = "INSERT INTO ganancias (feInicioG, feFinalG, capital_inicial, ingresos, totalGanancias, tipoPeriodo) VALUES (:feInicioG, :feFinalG, :capitalInicial, :ingresos, :totalGanancias, :tipoPeriodo)";
    $nuevaGananciaStmt = $pdo->prepare($nuevaGananciaQuery);
    $nuevaGananciaStmt->execute([
        'feInicioG' => $feInicioG,
        'feFinalG' => $feFinalG,
        'capitalInicial' => $capitalInicial,
        'ingresos' => $ingresosTotales,
        'totalGanancias' => $totalGanancias,
        'tipoPeriodo' => $tipoPeriodo
    ]);

    header("Location: ganancias.php");
    exit;
}

// Manejar la lógica para eliminar ganancia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $idGanancias = $_POST['idGanancias'];
    $eliminarGananciaQuery = "DELETE FROM ganancias WHERE idGanancias = :idGanancias";
    $eliminarGananciaStmt = $pdo->prepare($eliminarGananciaQuery);
    $eliminarGananciaStmt->execute(['idGanancias' => $idGanancias]);
    header("Location: ganancias.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ganancias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Filtrar Ganancias</h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fechaInicioG" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicioG" name="fechaInicioG" value="<?php echo htmlspecialchars($fechaInicioG); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fechaFinalG" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fechaFinalG" name="fechaFinalG" value="<?php echo htmlspecialchars($fechaFinalG); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                <a href="ganancias.php" class="btn btn-secondary">Limpiar Filtros</a>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Historial de Ganancias</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarGanancia">Agregar Ganancia</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Capital Inicial</th>
                    <th>Ingresos</th>
                    <th>Total Ganancias</th>
                    <th>Tipo Periodo</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ganancias as $ganancia): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ganancia['feInicioG']); ?></td>
                        <td><?php echo htmlspecialchars($ganancia['feFinalG']); ?></td>
                        <td>$<?php echo number_format($ganancia['capital_inicial'], 2); ?></td>
                        <td>$<?php echo number_format($ganancia['ingresos'], 2); ?></td>
                        <td>$<?php echo number_format($ganancia['totalGanancias'], 2); ?></td>
                        <td><?php echo htmlspecialchars($ganancia['tipoPeriodo']); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="idGanancias" value="<?php echo $ganancia['idGanancias']; ?>">
                                <button type="submit" name="eliminar" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar Ganancia -->
<div class="modal fade" id="modalAgregarGanancia" tabindex="-1" aria-labelledby="modalAgregarGananciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarGananciaLabel">Agregar Nueva Ganancia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <label for="feInicioG" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="feInicioG" name="feInicioG" required>
                    <label for="feFinalG" class="form-label">Fecha Final</label>
                    <input type="date" class="form-control" id="feFinalG" name="feFinalG" required>
                    <label for="capital_inicial" class="form-label">Capital Inicial</label>
                    <input type="number" class="form-control" id="capital_inicial" name="capital_inicial" required>
                    <div class="modal-footer">
                        <button type="submit" name="agregar" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>