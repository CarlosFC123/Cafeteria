<?php
session_start();
require 'db_connection.php';

// Obtener opciones de desayuno y almuerzo desde la base de datos
$desayunos = $pdo->query("SELECT idDesayuno, nombreProducto, fecha FROM desayuno")->fetchAll(PDO::FETCH_ASSOC);
$almuerzos = $pdo->query("SELECT idAlmuerzo, nombreProducto, fecha FROM almuerzo")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['feInicioM'] ?? '';
    $dia_semana = $_POST['dia_semana'] ?? '';
    $opciones_desayuno = $_POST['desayuno'] ?? [];
    $opciones_almuerzo = $_POST['almuerzo'] ?? [];

    if (!empty($fecha) && !empty($dia_semana)) {
        // Convertir opciones seleccionadas a JSON para guardarlas en una sola columna
        $nombreDesayuno = json_encode(array_map(function($id) use ($desayunos) {
            foreach ($desayunos as $desayuno) {
                if ($desayuno['idDesayuno'] == $id) {
                    return $desayuno['nombreProducto'];
                }
            }
            return null;
        }, $opciones_desayuno));

        $nombreAlmuerzo = json_encode(array_map(function($id) use ($almuerzos) {
            foreach ($almuerzos as $almuerzo) {
                if ($almuerzo['idAlmuerzo'] == $id) {
                    return $almuerzo['nombreProducto'];
                }
            }
            return null;
        }, $opciones_almuerzo));

        // Inserción en menu_semanal (incluyendo el día de la semana)
        $stmt = $pdo->prepare("INSERT INTO menu_semanal (feInicioM, dia_semana, nombreDesayuno, nombreAlmuerzo) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$fecha, $dia_semana, $nombreDesayuno, $nombreAlmuerzo])) {
            // Obtener el ID del menú semanal recién insertado
            $idMenuSemanal = $pdo->lastInsertId();

            // Actualizar la fecha en los desayunos seleccionados
            if (!empty($opciones_desayuno)) {
                foreach ($opciones_desayuno as $idDesayuno) {
                    // Obtener las fechas actuales del desayuno
                    $stmt = $pdo->prepare("SELECT fecha FROM desayuno WHERE idDesayuno = ?");
                    $stmt->execute([$idDesayuno]);
                    $desayuno = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Si la fecha es '0000-00-00' o está vacía, tratarla como NULL
                    $fechas = ($desayuno['fecha'] === '0000-00-00' || empty($desayuno['fecha'])) ? [] : explode(',', $desayuno['fecha']);

                    // Agregar la nueva fecha a la lista de fechas
                    if (!in_array($fecha, $fechas)) {
                        $fechas[] = $fecha;
                        $nuevasFechas = implode(',', $fechas);

                        // Actualizar las fechas en la base de datos
                        $stmt = $pdo->prepare("UPDATE desayuno SET fecha = ? WHERE idDesayuno = ?");
                        $stmt->execute([$nuevasFechas, $idDesayuno]);
                    }
                }
            }

            // Actualizar la fecha en los almuerzos seleccionados
            if (!empty($opciones_almuerzo)) {
                foreach ($opciones_almuerzo as $idAlmuerzo) {
                    // Obtener las fechas actuales del almuerzo
                    $stmt = $pdo->prepare("SELECT fecha FROM almuerzo WHERE idAlmuerzo = ?");
                    $stmt->execute([$idAlmuerzo]);
                    $almuerzo = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Si la fecha es '0000-00-00' o está vacía, tratarla como NULL
                    $fechas = ($almuerzo['fecha'] === '0000-00-00' || empty($almuerzo['fecha'])) ? [] : explode(',', $almuerzo['fecha']);

                    // Agregar la nueva fecha a la lista de fechas
                    if (!in_array($fecha, $fechas)) {
                        $fechas[] = $fecha;
                        $nuevasFechas = implode(',', $fechas);

                        // Actualizar las fechas en la base de datos
                        $stmt = $pdo->prepare("UPDATE almuerzo SET fecha = ? WHERE idAlmuerzo = ?");
                        $stmt->execute([$nuevasFechas, $idAlmuerzo]);
                    }
                }
            }

            header("Location: menu_semanal.php?success=1");
            exit;
        } else {
            $error = "Hubo un error al guardar el menú. Inténtalo nuevamente.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Menú Semanal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 650px;
            margin: 60px auto;
            border: 1px solid #dee2e6;
        }
        h1 {
            color: #343a40;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group label {
            margin-bottom: 5px;
            color: #495057;
        }
        .checkbox-container {
            display: flex;
            justify-content: space-between;
        }
        .checkbox-section {
            flex: 1;
            margin-right: 10px;
        }
        .checkbox-container div {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .checkbox-container input {
            margin-right: 5px;
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Agregar Menú Semanal</h1>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="feInicioM" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="feInicioM" name="feInicioM" required>
                </div>

                <div class="form-group mb-3">
                    <label for="dia_semana" class="form-label">Día de la Semana</label>
                    <select class="form-control" id="dia_semana" name="dia_semana" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <div class="checkbox-container">
                        <div class="checkbox-section">
                            <label for="desayuno" class="form-label">Desayunos</label>
                            <?php foreach ($desayunos as $desayuno): ?>
                                <div>
                                    <input type="checkbox" id="desayuno_<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>" name="desayuno[]" value="<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>">
                                    <label for="desayuno_<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>"><?php echo htmlspecialchars($desayuno['nombreProducto']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="checkbox-section">
                            <label for="almuerzo" class="form-label">Almuerzos</label>
                            <?php foreach ($almuerzos as $almuerzo): ?>
                                <div>
                                    <input type="checkbox" id="almuerzo_<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>" name="almuerzo[]" value="<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>">
                                    <label for="almuerzo_<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>"><?php echo htmlspecialchars($almuerzo['nombreProducto']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Guardar Menú</button>
                    <a href="menu_semanal.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>