<?php
require 'db_connection.php';

// Obtener todos los horarios
$stmt = $pdo->query("SELECT * FROM horarios");
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // Agregar un nuevo horario
        $tipo_comida = $_POST['tipo_comida'];
        $horaInicial = $_POST['horaInicial'];
        $horaFinal = $_POST['horaFinal'];

        if (!empty($tipo_comida) && !empty($horaInicial) && !empty($horaFinal)) {
            $stmt = $pdo->prepare("INSERT INTO horarios (tipo_comida, horaInicial, horaFinal) VALUES (:tipo_comida, :horaInicial, :horaFinal)");
            $stmt->execute([
                'tipo_comida' => $tipo_comida,
                'horaInicial' => $horaInicial,
                'horaFinal' => $horaFinal
            ]);
            header('Location: horarios.php');
            exit;
        } else {
            echo "<script>alert('Todos los campos son obligatorios.');</script>";
        }
    }

    if (isset($_POST['edit'])) {
        // Editar un horario existente
        $idHorario = $_POST['idHorario'];
        $tipo_comida = $_POST['tipo_comida'];
        $horaInicial = $_POST['horaInicial'];
        $horaFinal = $_POST['horaFinal'];

        if (!empty($tipo_comida) && !empty($horaInicial) && !empty($horaFinal)) {
            $stmt = $pdo->prepare("UPDATE horarios SET tipo_comida = :tipo_comida, horaInicial = :horaInicial, horaFinal = :horaFinal WHERE idHorario = :idHorario");
            $stmt->execute([
                'tipo_comida' => $tipo_comida,
                'horaInicial' => $horaInicial,
                'horaFinal' => $horaFinal,
                'idHorario' => $idHorario
            ]);
            header('Location: horarios.php');
            exit;
        } else {
            echo "<script>alert('Todos los campos son obligatorios.');</script>";
        }
    }

    if (isset($_POST['delete'])) {
        // Eliminar un horario
        $idHorario = $_POST['idHorario'];
        $stmt = $pdo->prepare("DELETE FROM horarios WHERE idHorario = :idHorario");
        $stmt->execute(['idHorario' => $idHorario]);
        header('Location: horarios.php');
        exit;
    }
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Horarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Administrador de Horarios</h5>
        </div>
        <div class="card-body">
            <!-- Tabla de horarios -->
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Comida</th>
                        <th>Hora Inicial</th>
                        <th>Hora Final</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $horario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($horario['idHorario']); ?></td>
                            <td><?php echo htmlspecialchars($horario['tipo_comida']); ?></td>
                            <td><?php echo htmlspecialchars($horario['horaInicial']); ?></td>
                            <td><?php echo htmlspecialchars($horario['horaFinal']); ?></td>
                            <td>
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $horario['idHorario']; ?>">
                                    <i class="bx bx-edit-alt"></i> Editar
                                </button>

                                <!-- Formulario para eliminar con estilo similar -->
                                <!-- <form method="POST" style="display: inline;">
                                    <input type="hidden" name="idHorario" value="<?php echo htmlspecialchars($horario['idHorario']); ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-trash"></i> Eliminar
                                    </button>
                                </form> -->
                            </td>
                        </tr>

                        <!-- Modal de edición -->
                        <div class="modal fade" id="editModal<?php echo $horario['idHorario']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Horario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="idHorario" value="<?php echo htmlspecialchars($horario['idHorario']); ?>">
                                            <div class="mb-3">
                                                <label for="tipo_comida" class="form-label">Tipo de Comida</label>
                                                <input type="text" class="form-control" name="tipo_comida" value="<?php echo htmlspecialchars($horario['tipo_comida']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="horaInicial" class="form-label">Hora Inicial</label>
                                                <input type="time" class="form-control" name="horaInicial" value="<?php echo htmlspecialchars($horario['horaInicial']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="horaFinal" class="form-label">Hora Final</label>
                                                <input type="time" class="form-control" name="horaFinal" value="<?php echo htmlspecialchars($horario['horaFinal']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="edit" class="btn btn-primary">Guardar Cambios</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Formulario para agregar nuevo horario -->
            <!-- <h5 class="mt-4">Agregar Nuevo Horario</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label for="tipo_comida" class="form-label">Tipo de Comida</label>
                    <input type="text" class="form-control" name="tipo_comida" required>
                </div>
                <div class="col-md-4">
                    <label for="horaInicial" class="form-label">Hora Inicial</label>
                    <input type="time" class="form-control" name="horaInicial" required>
                </div>
                <div class="col-md-4">
                    <label for="horaFinal" class="form-label">Hora Final</label>
                    <input type="time" class="form-control" name="horaFinal" required>
                </div>
                <div class="col-md-12">
                    <button type="submit" name="add" class="btn btn-success">Agregar Horario</button>
                </div>
            </form> -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
