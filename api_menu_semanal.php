<?php
header('Content-Type: application/json');
require 'db_connection.php';

$diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
$menus = [];

foreach ($diasSemana as $dia) {
    $stmt = $pdo->prepare("SELECT * FROM menu_semanal WHERE dia_semana = :dia");
    $stmt->execute(['dia' => $dia]);
    $menus[$dia] = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo json_encode($menus);
?>