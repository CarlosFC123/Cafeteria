<?php
header("Content-Type: application/json; charset=UTF-8");

// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para obtener los productos
$sql = "
    SELECT 
        p.idProducto,
        c.nbCategoria,
        pr.nbProveedor,
        p.nbProducto,
        p.desProducto,
        p.precioProducto,
        p.imgProducto,
        t.nbTipo,
        p.destacado,
        i.canActual
    FROM productos p
    INNER JOIN categorias c ON p.idCategoria = c.idCategoria
    INNER JOIN proveedores pr ON p.idProveedor = pr.idProveedor
    INNER JOIN tipos_categorias t ON p.idTipo = t.idTipo
    LEFT JOIN inventario i ON p.idProducto = i.idProducto
    ORDER BY p.idProducto ASC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Convertir los resultados a un array asociativo
    $productos = array();
    while($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    // Devolver los productos en formato JSON
    echo json_encode($productos);
} else {
    echo json_encode([]);
}

$conn->close();
?>