<?php

include 'sidebar.php';
require 'db_connection.php';

// Obtener menús semanales por día
$diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
$menus = [];
foreach ($diasSemana as $dia) {
    $menus[$dia] = $pdo->query("SELECT * FROM menu_semanal WHERE dia_semana = '$dia'")->fetch(PDO::FETCH_ASSOC);
}

// Obtener nombres de productos de desayuno y almuerzo
function obtenerNombres($jsonNombres) {
    $nombresArray = json_decode($jsonNombres);
    if (empty($nombresArray)) return [];
    return $nombresArray;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Menú Semanal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .menu-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .menu-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: calc(20% - 20px);
        }
        .menu-card h3 {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .menu-card p, .menu-card .btn-actions {
            text-align: center;
        }
        .btn-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .add-menu-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #007bff;
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .add-menu-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Administración de Menú Semanal</h1>

        <div class="menu-container">
            <?php foreach ($diasSemana as $dia): 
                $menu = $menus[$dia] ?? null;
            ?>
            <div class="menu-card">
                <h3><?php echo $dia; ?></h3>
                <?php if ($menu): ?>
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($menu['feInicioM']); ?></p>
                    <p><strong>Desayunos:</strong></p>
                    <ul>
                        <?php 
                        $desayunosSeleccionados = obtenerNombres($menu['nombreDesayuno']);
                        if ($desayunosSeleccionados) {
                            foreach ($desayunosSeleccionados as $nombreDesayuno): ?>
                                <li><?php echo htmlspecialchars($nombreDesayuno); ?></li>
                            <?php endforeach; 
                        } else { ?>
                            <li>No hay desayunos seleccionados</li>
                        <?php } ?>
                    </ul>
                    <p><strong>Almuerzos:</strong></p>
                    <ul>
                        <?php 
                        $almuerzosSeleccionados = obtenerNombres($menu['nombreAlmuerzo']);
                        if ($almuerzosSeleccionados) {
                            foreach ($almuerzosSeleccionados as $nombreAlmuerzo): ?>
                                <li><?php echo htmlspecialchars($nombreAlmuerzo); ?></li>
                            <?php endforeach; 
                        } else { ?>
                            <li>No hay almuerzos seleccionados</li>
                        <?php } ?>
                    </ul>
                    <div class="btn-actions">
                        <button class="btn btn-sm btn-primary" onclick="editarMenu(<?php echo $menu['idMenuSemanal']; ?>)">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminarMenu(<?php echo $menu['idMenuSemanal']; ?>)">Eliminar</button>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay menú asignado</p>
                    <a href="agregar_menu.php" class="btn btn-sm btn-success">Agregar</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="btn btn-primary btn-lg rounded-circle add-menu-btn" onclick="window.location.href='agregar_menu.php'">
        <i class='bx bx-plus'></i>
    </button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarMenu(id) {
            window.location.href = 'modificar_menu.php?id=' + id; // Concatenación tradicional
        }

        function confirmarEliminarMenu(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'eliminar_menu.php?id=' + id; // Concatenación tradicional
                }
            });
        }
    </script>
</body>
</html>