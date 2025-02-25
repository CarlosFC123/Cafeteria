<?php
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
require 'db_connection.php';

// Obtener opciones de desayuno y almuerzo desde la base de datos
$desayunos = $pdo->query("SELECT idDesayuno, nombreProducto, fecha FROM desayuno")->fetchAll(PDO::FETCH_ASSOC);
$almuerzos = $pdo->query("SELECT idAlmuerzo, nombreProducto, fecha FROM almuerzo")->fetchAll(PDO::FETCH_ASSOC);

// Obtener el menú semanal a editar
$menuId = $_GET['id'] ?? null;
if ($menuId) {
    $stmt = $pdo->prepare("SELECT feInicioM, nombreDesayuno, nombreAlmuerzo FROM menu_semanal WHERE idMenuSemanal = ?");
    $stmt->execute([$menuId]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['feInicioM'] ?? '';
    $opciones_desayuno = $_POST['desayuno'] ?? [];
    $opciones_almuerzo = $_POST['almuerzo'] ?? [];

    if (!empty($fecha) && $menuId) {
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

        // Obtener el menú actual para comparar cambios
        $stmt = $pdo->prepare("SELECT feInicioM, nombreDesayuno, nombreAlmuerzo FROM menu_semanal WHERE idMenuSemanal = ?");
        $stmt->execute([$menuId]);
        $menuActual = $stmt->fetch(PDO::FETCH_ASSOC);

        $desayunosActuales = json_decode($menuActual['nombreDesayuno'], true) ?? [];
        $almuerzosActuales = json_decode($menuActual['nombreAlmuerzo'], true) ?? [];

        // Obtener la fecha anterior del menú
        $fechaAnterior = $menuActual['feInicioM'];

        // Actualización en menu_semanal
        $stmt = $pdo->prepare("UPDATE menu_semanal SET feInicioM = ?, nombreDesayuno = ?, nombreAlmuerzo = ? WHERE idMenuSemanal = ?");
        if ($stmt->execute([$fecha, $nombreDesayuno, $nombreAlmuerzo, $menuId])) {
            // Procesar desayunos
            foreach ($desayunos as $desayuno) {
                $nombre = $desayuno['nombreProducto'];
                $stmt = $pdo->prepare("SELECT fecha FROM desayuno WHERE nombreProducto = ?");
                $stmt->execute([$nombre]);
                $desayunoData = $stmt->fetch(PDO::FETCH_ASSOC);

                $fechas = ($desayunoData['fecha'] === null || empty($desayunoData['fecha'])) ? [] : explode(',', $desayunoData['fecha']);

                // Si el desayuno estaba seleccionado anteriormente, eliminar la fecha anterior
                if (in_array($nombre, $desayunosActuales)) {
                    $fechas = array_diff($fechas, [$fechaAnterior]); // Eliminar solo la fecha anterior
                }

                // Si el desayuno está seleccionado ahora, agregar la nueva fecha
                if (in_array($nombre, json_decode($nombreDesayuno, true))) {
                    if (!in_array($fecha, $fechas)) {
                        $fechas[] = $fecha; // Agregar la nueva fecha
                    }
                }

                $nuevasFechas = !empty($fechas) ? implode(',', $fechas) : null;

                $stmt = $pdo->prepare("UPDATE desayuno SET fecha = ? WHERE nombreProducto = ?");
                $stmt->execute([$nuevasFechas, $nombre]);
            }

            // Procesar almuerzos
            foreach ($almuerzos as $almuerzo) {
                $nombre = $almuerzo['nombreProducto'];
                $stmt = $pdo->prepare("SELECT fecha FROM almuerzo WHERE nombreProducto = ?");
                $stmt->execute([$nombre]);
                $almuerzoData = $stmt->fetch(PDO::FETCH_ASSOC);

                $fechas = ($almuerzoData['fecha'] === null || empty($almuerzoData['fecha'])) ? [] : explode(',', $almuerzoData['fecha']);

                // Si el almuerzo estaba seleccionado anteriormente, eliminar la fecha anterior
                if (in_array($nombre, $almuerzosActuales)) {
                    $fechas = array_diff($fechas, [$fechaAnterior]); // Eliminar solo la fecha anterior
                }

                // Si el almuerzo está seleccionado ahora, agregar la nueva fecha
                if (in_array($nombre, json_decode($nombreAlmuerzo, true))) {
                    if (!in_array($fecha, $fechas)) {
                        $fechas[] = $fecha; // Agregar la nueva fecha
                    }
                }

                $nuevasFechas = !empty($fechas) ? implode(',', $fechas) : null;

                $stmt = $pdo->prepare("UPDATE almuerzo SET fecha = ? WHERE nombreProducto = ?");
                $stmt->execute([$nuevasFechas, $nombre]);
            }

            header("Location: menu_semanal.php?success=2");
            exit;
        } else {
            $error = "Hubo un error al actualizar el menú. Inténtalo nuevamente.";
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
    <title>Modificar Menú Semanal</title>
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
        <h1 class="text-center mb-4">Modificar Menú Semanal</h1>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="feInicioM" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="feInicioM" name="feInicioM" value="<?php echo htmlspecialchars($menu['feInicioM']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <div class="checkbox-container">
                        <div class="checkbox-section">
                            <label for="desayuno" class="form-label">Desayunos</label>
                            <?php 
                            $desayunosSeleccionados = json_decode($menu['nombreDesayuno']) ?? [];
                            foreach ($desayunos as $desayuno): 
                            ?>
                                <div>
                                    <input type="checkbox" id="desayuno_<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>" name="desayuno[]" value="<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>" <?php echo in_array($desayuno['nombreProducto'], $desayunosSeleccionados) ? 'checked' : ''; ?>>
                                    <label for="desayuno_<?php echo htmlspecialchars($desayuno['idDesayuno']); ?>"><?php echo htmlspecialchars($desayuno['nombreProducto']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="checkbox-section">
                            <label for="almuerzo" class="form-label">Almuerzos</label>
                            <?php 
                            $almuerzosSeleccionados = json_decode($menu['nombreAlmuerzo']) ?? [];
                            foreach ($almuerzos as $almuerzo): 
                            ?>
                                <div>
                                    <input type="checkbox" id="almuerzo_<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>" name="almuerzo[]" value="<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>" <?php echo in_array($almuerzo['nombreProducto'], $almuerzosSeleccionados) ? 'checked' : ''; ?>>
                                    <label for="almuerzo_<?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?>"><?php echo htmlspecialchars($almuerzo['nombreProducto']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Actualizar Menú</button>
                    <a href="menu_semanal.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>