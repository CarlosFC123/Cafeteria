<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $idMenuSemanal = $_GET['id'];

    try {
        // Obtener el menú que se va a eliminar
        $stmt = $pdo->prepare("SELECT feInicioM, dia_semana, nombreDesayuno, nombreAlmuerzo FROM menu_semanal WHERE idMenuSemanal = ?");
        $stmt->execute([$idMenuSemanal]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu) {
            $fecha = $menu['feInicioM'];
            $dia_semana = $menu['dia_semana'];
            $nombreDesayuno = json_decode($menu['nombreDesayuno'], true); // Convertir JSON a array
            $nombreAlmuerzo = json_decode($menu['nombreAlmuerzo'], true); // Convertir JSON a array

            // Eliminar la fecha específica de los desayunos asociados
            if (!empty($nombreDesayuno)) {
                foreach ($nombreDesayuno as $nombre) {
                    // Obtener las fechas actuales del desayuno
                    $stmt = $pdo->prepare("SELECT fecha FROM desayuno WHERE nombreProducto = ?");
                    $stmt->execute([$nombre]);
                    $desayuno = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($desayuno && !empty($desayuno['fecha']) && $desayuno['fecha'] !== '0000-00-00') {
                        // Eliminar la fecha específica de la lista de fechas
                        $fechas = explode(',', $desayuno['fecha']);
                        $fechas = array_diff($fechas, [$fecha]); // Eliminar la fecha específica
                        $nuevasFechas = !empty($fechas) ? implode(',', $fechas) : null; // Si no hay fechas, establecer como NULL

                        // Actualizar las fechas en la base de datos
                        $stmt = $pdo->prepare("UPDATE desayuno SET fecha = ? WHERE nombreProducto = ?");
                        $stmt->execute([$nuevasFechas, $nombre]);
                    }
                }
            }

            // Eliminar la fecha específica de los almuerzos asociados
            if (!empty($nombreAlmuerzo)) {
                foreach ($nombreAlmuerzo as $nombre) {
                    // Obtener las fechas actuales del almuerzo
                    $stmt = $pdo->prepare("SELECT fecha FROM almuerzo WHERE nombreProducto = ?");
                    $stmt->execute([$nombre]);
                    $almuerzo = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($almuerzo && !empty($almuerzo['fecha']) && $almuerzo['fecha'] !== '0000-00-00') {
                        // Eliminar la fecha específica de la lista de fechas
                        $fechas = explode(',', $almuerzo['fecha']);
                        $fechas = array_diff($fechas, [$fecha]); // Eliminar la fecha específica
                        $nuevasFechas = !empty($fechas) ? implode(',', $fechas) : null; // Si no hay fechas, establecer como NULL

                        // Actualizar las fechas en la base de datos
                        $stmt = $pdo->prepare("UPDATE almuerzo SET fecha = ? WHERE nombreProducto = ?");
                        $stmt->execute([$nuevasFechas, $nombre]);
                    }
                }
            }

            // Eliminar el menú semanal
            $pdo->prepare("DELETE FROM menu_semanal WHERE idMenuSemanal = ?")->execute([$idMenuSemanal]);

            header("Location: menu_semanal.php?success=1");
            exit;
        } else {
            $error = "No se encontró el menú especificado.";
        }
    } catch (Exception $e) {
        $error = "Ocurrió un error al intentar eliminar el menú: " . $e->getMessage();
    }
}

// Si hay un error, mostrar la página de error
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error al Eliminar Menú</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center text-danger">Error al Eliminar Menú</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="text-center">
            <a href="menu_semanal.php" class="btn btn-secondary">Regresar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>