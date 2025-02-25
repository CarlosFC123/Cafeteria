<?php
session_start();
require 'db_connection.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 1) {
    header("Location: login.php");
    exit;
}

$usuario_nombre = "Usuario"; 
$usuario_apellido = ""; 
if (isset($_SESSION['usuario_nombre']) && isset($_SESSION['usuario_apellido']) ) {
    $usuario_nombre = $_SESSION['usuario_nombre'];
    $usuario_apellido = $_SESSION['usuario_apellido'];
} else {
    $usuario_id = $_SESSION['usuario_id'];
    $usuario = obtenerUsuario($usuario_id); 
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_apellido'] = $usuario['apellido'];
    
    $usuario_nombre = $usuario['nombre'];
    $usuario_apellido = $usuario['apellido'];
}

function obtenerUsuario($usuario_id) {
    $conexion = new mysqli("localhost", "usuario", "contraseña", "basedatos");
    $query = "SELECT nombre, apellido, email FROM usuarios WHERE id = $usuario_id"; // Agrega el campo 'email'
    $resultado = $conexion->query($query);
    if ($resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    } else {
        return ["nombre" => "Usuario", "apellido" => "", ]; // Agrega un valor por defecto para 'email'
    }
}
$productos = $pdo->query(" 
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
")->fetchAll(PDO::FETCH_ASSOC);

$desayunos = $pdo->query(" 
    SELECT 
        d.idDesayuno,
        d.imgDesayuno,
        d.nombreProducto,
        d.precioDesayuno,
        d.cantidadDesayuno,
        d.precioTotalDesayuno,
        d.descripcionDesayuno,
        d.fecha
    FROM desayuno d
    ORDER BY d.idDesayuno ASC
")->fetchAll(PDO::FETCH_ASSOC);

$almuerzos = $pdo->query(" 
    SELECT 
        a.idAlmuerzo,
        a.imgAlmuerzo,
        a.nombreProducto,
        a.descripcionAlmuerzo,
        a.precioPorcion,
        a.precioMedia,
        a.precioOrden,
        a.cantidadPorcion,
        a.cantidadMedia,
        a.cantidadOrden,
        a.precioTotalAlmuerzo,
        a.fecha
    FROM almuerzo a
    ORDER BY a.idAlmuerzo ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cafetería UTR</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px;
            border: none;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            animation: modalFadeIn 0.4s;
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .close-modal {
            color: #777;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close-modal:hover,
        .close-modal:focus {
            color: #000;
            text-decoration: none;
        }
        #modalProductImage {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        #modalProductImage:hover {
            transform: scale(1.03);
        }
        .product-info {
            padding: 10px 0;
        }
        .info-label {
            font-weight: 600;
            margin-right: 10px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        #modalProductPrice {
            font-size: 1.8rem;
            margin-bottom: 0;
            color: #28a745; 
        }
        #modalProductName {
            color: #333;
            font-size: 1.8rem;
            margin-top: 10px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .badge {
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 20px;
        }
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 15px;
            }
            
            #modalProductImage {
                margin-bottom: 20px;
            }
        }
        .filter-option {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-todo-desayuno,
        .btn-todo,
        .btn-todo-almuerzo, 
        .btn-todo-producto { 
            display: none;
        }
        .modalUser {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        .modal-contentUser {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .close-modalUser {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-modalUser:hover,
        .close-modalUser:focus {
            color: #000;
            text-decoration: none;
        }
        .modal-dialog {
            width: 80%;
            max-width: 1000px;
            margin: 30px auto;
        }
        .modal-content2 {
            max-height: none;
            overflow: visible;
        }
        .menu-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 18%;
            text-align: center;
            margin: 10px;
            display: inline-block;
            vertical-align: top;
        }
        .menu-card h3 {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .menu-card p {
            text-align: center;
            margin: 0;
            padding: 5px;
        }
        .menu-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        #productModal.modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        #productModal .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px;
            border: none;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            animation: modalFadeIn 0.4s;
        }
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-product {
            width: 100%;
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .container-img {
            width: 100%;
            height: 180px;
            overflow: hidden;
            border-radius: 8px;
        }
        .container-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .button-group {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        .content-card-product {
            width: 100%;
            padding: 10px;
        }
        
        h3 {
            font-size: 16px;
            margin: 10px 0;
        }
        .price-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .price-list {
            flex: 1;
            text-align: left;
        }
        .price {
            font-size: 14px;
            margin: 2px 0;
        }
        .add-cart {
            color: white;
            padding: 10px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            cursor: pointer;
        }
        .add-cart i {
            font-size: 18px;
        }
        .modalUser {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

.modal-contentUser {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close-modalUser {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modalUser:hover,
.close-modalUser:focus {
    color: #000;
    text-decoration: none;
}
    </style>
</head>
<body>
    <header>
        <div class="container-hero">
            <div class="container hero">
                <div class="customer-support">
                    <i class="fa-solid fa-headset"></i>
                    <div class="content-customer-support">
                        <span class="text">Soporte al cliente</span>
                        <span class="number">123-456-7890</span>
                    </div>
                </div>

                <div class="container-logo">
                    <i class="fa-solid fa-mug-hot"></i>
                    <h1 class="logo"><a href="#">Cafetería UTR</a></h1>
                </div>
                <div class="container-user">
                    <div class="user-dropdown">
                        <span class="user-name">
                            <?php echo $usuario_nombre . ' ' . $usuario_apellido; ?>
                        </span>
                        <i class="fa-solid fa-caret-down"></i>
                        <div class="dropdown-content">
                            <a href="#">Mi perfil</a>
                            <a href="logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                    <i class="fa-solid fa-basket-shopping"></i>
                    <div class="content-shopping-cart">
                        <span class="text">Carrito</span>
                    </div>
                </div>
            </div>
        </div>
        <div style="background-color:rgb(100, 100, 100);">
            <nav class="navbar container">
                <i class="fa-solid fa-bars"></i>
                <ul class="menu">
                    <li><a href="#">Inicio</a></li>
                    <li><a id="menu-semanal-btn">Menú semanal</a></li>
                </ul>
            </nav>
        </div>
        <div id="menu-semanal-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Menú Semanal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="menu-semanal-cards" class="d-flex flex-row justify-content-between">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="banner">
        <div class="content-banner">
            <p>Utr</p>
            <h2>100%<br />Cardenales</h2>
            <!-- <a href="#" style="color: black; font-weight: bold;">Crear Orden</a> -->
        </div>
    </section>
    <main class="main-content">
        <section class="container top-categories">
            <br>
            <h1 class="heading-2">Categorías</h1>
            <div class="container-categories">
                <div class="card-category category-moca">
                    <p>Desayunos</p>
                    <span id="show-desayunos">Ver más</span>
                </div>
                <div class="card-category category-expreso">
                    <p>Almuerzos</p>
                    <span id="show-almuerzos">Ver más</span>
                </div>
                <div class="card-category category-capuchino">
                    <p>Productos</p>
                    <span id="show-products">Ver más</span>
                </div>
                <div class="card-category category-todo">
                    <p>Todo</p>
                    <span id="show-todo">Ver más</span>
                </div>
            </div>
        </section>
        <section class="container top-products">
            <h1 class="heading-1">-</h1>
            <div class="container-options">
                <span class="filter-option btn-todo">Todos</span>
                <span class="filter-option btn-todo-desayuno" data-filter="todo-desayuno" id="show-all-breakfasts">Todos los Desayunos</span> 
                <span class="filter-option btn-todo-almuerzo" data-filter="todo-almuerzo" id="show-all-almuerzos">Todos los Almuerzos</span> 
                <span class="filter-option btn-todo-producto" data-filter="todo" id="show-all-products">Todos los Productos</span> 
            </div>
        </section>
        <section class="container products-section" id="products-section">
            <div class="container-products">
                <?php foreach ($productos as $producto): ?>
                <div class="card-product" 
                    data-description="<?php echo htmlspecialchars($producto['desProducto']); ?>"
                    data-category="<?php echo htmlspecialchars($producto['nbCategoria']); ?>"
                    data-type="<?php echo htmlspecialchars($producto['nbTipo']); ?>"
                    data-quantity="<?php echo htmlspecialchars($producto['canActual']); ?>">
                    <div class="container-img">
                        <img src="uploads/<?php echo htmlspecialchars($producto['imgProducto']); ?>" alt="<?php echo htmlspecialchars($producto['nbProducto']); ?>" />
                    </div>
                    <div class="content-card-product">
                        <h3><?php echo htmlspecialchars($producto['nbProducto']); ?></h3>
                        <span class="add-cart">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </span>
                        <p class="price">$<?php echo number_format($producto['precioProducto'], 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container breakfast-section" id="breakfast-section" style="display: none;">
            <div class="container-products" id="container-breakfasts">
                <?php foreach ($desayunos as $desayuno): ?>
                    <div class="card-product"
                        data-description="<?php echo htmlspecialchars($desayuno['descripcionDesayuno']); ?>"
                        data-category="Desayuno"
                        data-cantidad="<?php echo htmlspecialchars($desayuno['cantidadDesayuno']); ?>">
                        <div class="container-img">
                            <img src="uploads/<?php echo htmlspecialchars($desayuno['imgDesayuno']); ?>" alt="<?php echo htmlspecialchars($desayuno['nombreProducto']); ?>" />
                        </div>
                        <div class="content-card-product">
                            <h3><?php echo htmlspecialchars($desayuno['nombreProducto']); ?></h3>
                            <div class="price-container">
                                <div class="price-list">
                                    <p class="price">$<?php echo number_format($desayuno['precioDesayuno'], 2); ?></p>
                                </div>
                                <div class="add-cart">
                                    <i class="fa-solid fa-basket-shopping"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container almuerzo-section" id="almuerzo-section" style="display: none;">
            <div class="container-products" id="container-almuerzos">
            <?php foreach ($almuerzo as $alm): ?>
                <div class="card-product"
                    data-description="<?php echo htmlspecialchars($alm['descripcionAlmuerzo']); ?>"
                    data-category="Almuerzo"
                    data-cantidad-porcion="<?php echo htmlspecialchars($alm['cantidadPorcion']); ?>"
                    data-cantidad-media="<?php echo htmlspecialchars($alm['cantidadMedia']); ?>"
                    data-cantidad-orden="<?php echo htmlspecialchars($alm['cantidadOrden']); ?>">
                    <div class="container-img">
                        <img src="uploads/<?php echo htmlspecialchars($alm['imgAlmuerzo']); ?>" alt="<?php echo htmlspecialchars($alm['nombreProducto']); ?>" />
                        
                    </div>
                    <div class="content-card-product">
                        <h3><?php echo htmlspecialchars($alm['nombreProducto']); ?></h3>
                        <div class="price-container">
                            <div class="price-list">
                                <p class="price">Porción: $<?php echo number_format($alm['precioPorcion'], 2); ?></p>
                                <p class="price">Media: $<?php echo number_format($alm['precioMedia'], 2); ?></p>
                                <p class="price">Orden: $<?php echo number_format($alm['precioOrden'], 2); ?></p>
                            </div>
                            <div class="add-cart">
                                <i class="fa-solid fa-basket-shopping"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </section>
        <section class="gallery">
            <img src="img/2.jpeg" alt="Gallery Img1" class="gallery-img-1" />
            <img src="img/3.jpeg" alt="Gallery Img2" class="gallery-img-2" />
            <img src="img/1.jpeg" alt="Gallery Img3" class="gallery-img-3" />
            <img src="img/4.jpeg" alt="Gallery Img4" class="gallery-img-4" />
            <img src="img/5.jpeg" alt="Gallery Img5" class="gallery-img-5" />
        </section>
    </main>
    <!-- <section class="container specials">
            <h1 class="heading-2">Especiales</h1>
            <div class="container-products">
                
            <div>
    </section>                                -->
    <!--Modal de Producto CODIGO CORRECTOO-->                           
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="row">
                <div class="col-md-5">
                    <img id="modalProductImage" src="" alt="Product Image" class="img-fluid rounded shadow">
                </div>
                <div class="col-md-7">
                    <h2 id="modalProductName" class="mb-3 fw-bold"></h2>
                    <div class="product-info">
                        <div class="info-item">
                            <span class="info-label">Categoría:</span>
                            <span id="modalProductCategory" class="badge bg-primary"></span>
                        </div>
                        <div class="info-item mt-2">
                            <span class="info-label">Tipo:</span>
                            <span id="modalProductType" class="badge bg-secondary"></span>
                        </div>
                        <div class="info-item mt-3">
                            <h4 id="modalProductPrice" class="text-success fw-bold"></h4>
                        </div>
                        <div class="info-item mt-3">
                            <h5>Descripción:</h5>
                            <p id="modalProductDescription" class="text-muted"></p>
                        </div>
                        <div class="info-item mt-3">
                            <h5>Cantidad en inventario:</h5>
                            <p id="modalProductQuantity" class="text-muted"></p>
                        </div>
                        <div class="info-item mt-3">
                            <h5>Estado del inventario:</h5>
                            <p id="modalProductStatus" class="text-muted"></p>
                        </div>
                        <!-- Formulario de cantidad y forma de pago -->
                        <form id="formProducto" class="mt-3">
                            <div class="form-group">
                            <h5 style="color: #2a9d8f; font-size: 1.1rem;">Cantidad:</h5>
                            <input type="number" id="cantidadProducto" name="cantidadProducto" class="form-control" min="1" required>
                            </div>
                            <div class="form-group mt-3">
                            <h5 style="color: #2a9d8f; font-size: 1.1rem;">Método de Pago:</h5>
                            <div>
                                <label>
                                <input type="radio" id="formaPagoEfectivoProducto" name="formaPagoProducto" value="efectivo" required>
                                Efectivo
                                </label>
                            </div>
                            <div>
                                <label>
                                <input type="radio" id="formaPagoTransferenciaProducto" name="formaPagoProducto" value="transferencia" required>
                                Transferencia
                                </label>
                            </div>
                            </div>
                            <!-- Total a pagar antes del botón -->
                            <div id="totalCalculadoProducto" class="mt-3" style="font-size: 1.2rem; color: #264653; font-weight: bold;"></div>

                            <button type="submit" id="btnAccionProducto" class="btn btn-primary w-100" style="background-color: #2a9d8f; border: none; border-radius: 10px; padding: 1rem;">
                            Solicitar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Modal de Transferencia para Productos -->
<div id="simulacionProductoModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <h2>Simulación de Transferencia (Productos)</h2>
    <form id="formSimulacionProducto">
      <div class="form-group">
        <label for="nombreBancoProducto">Nombre del Banco</label>
        <select id="nombreBancoProducto" name="nombreBancoProducto" class="form-control" required>
          <option value="BBVA">Banco BBVA</option>
          <option value="BANORTE">Banco BANORTE</option>
          <option value="HSBC">Banco HSBC</option>
        </select>
      </div>
      <div class="form-group">
        <label for="numeroCuentaProducto">Número de Cuenta</label>
        <input type="text" id="numeroCuentaProducto" name="numeroCuentaProducto" class="form-control" placeholder="1234-5678-9012-3456" required>
      </div>
      <div class="form-group">
        <label for="montoProducto">Monto</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="number" id="montoProducto" name="montoProducto" class="form-control" required>
        </div>
      </div>
      <button type="submit" id="btnAceptarTransferenciaProducto" class="btn btn-primary w-100" style="background-color: #264653; border: none; border-radius: 10px; padding: 1rem;">
        Aceptar Transferencia
      </button>
    </form>
    <div id="loadingProducto" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su transferencia...</p>
    </div>
    <div id="mensajeExitoProducto" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Transferencia exitosa!
    </div>
  </div>
</div>
<!-- Modal de Efectivo para Productos -->
<div id="efectivoProductoModal" class="modal">
  <div class="modal-content" style="width: 300px; margin: auto;">
    <span class="close-modal">&times;</span>
    <div id="loadingEfectivoProducto" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su solicitud...</p>
    </div>
    <div id="mensajeExitoEfectivoProducto" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Solicitud procesada con éxito!
    </div>
  </div>
</div>

    <!-- Modal para Almuerzos -->
<div id="almuerzoModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="row">
            <div class="col-md-5">
                <img id="modalAlmuerzoImage" src="" alt="Almuerzo Image" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-7">
                <h2 id="modalAlmuerzoName" class="mb-3 fw-bold" style="color: #264653; font-size: 2rem; border-bottom: 2px solid #2a9d8f; padding-bottom: 0.5rem;"></h2>
                <div class="product-info">
                    <!-- Mantener el contenido de información original -->
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Precios:</h5>
                        <p id="modalAlmuerzoPricePorcion" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoPriceMedia" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoPriceOrden" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                    </div>
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Descripción:</h5>
                        <p id="modalAlmuerzoDescription" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                    </div>
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Cantidades Disponibles:</h5>
                        <p id="modalAlmuerzoCantidadPorcion" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoCantidadMedia" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoCantidadOrden" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                    </div>
                    
                </div>
                <!-- Formulario de cantidad y forma de pago -->
                <form id="formAlmuerzo" class="mt-3">
                    <div class="form-group">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Tipo de Comida:</h5>
                        <select id="tipoComida" name="tipoComida" class="form-control" required>
                            <option value="porcion">Porción</option>
                            <option value="media">Media</option>
                            <option value="orden">Orden</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Cantidad:</h5>
                        <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Tortillas Extras:</h5>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="tortillasExtras" name="tortillasExtras" class="form-control" min="0" value="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Método de Pago:</h5>
                        <div>
                            <label for="formaPagoEfectivo">
                                <input type="radio" id="formaPagoEfectivo" name="formaPago" value="efectivo" required>
                                Efectivo
                            </label>
                        </div>
                        <div>
                            <label for="formaPagoTransferencia">
                                <input type="radio" id="formaPagoTransferencia" name="formaPago" value="transferencia" required>
                                Transferencia
                            </label>
                        </div>
                    </div>
                    <!-- Mostrar el total calculado debajo del formulario -->
                    <div id="totalCalculado" class="mt-3" style="font-size: 1.2rem; color: #264653; font-weight: bold;"></div>
                    <button type="submit" id="btnAccion" class="btn btn-primary w-100" style="background-color: #2a9d8f; border: none; border-radius: 10px; padding: 1rem; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s ease;">Solicitar</button>
                </form>
            </div>
        </div>
    </div>


<!-- Modal para Simulación de Transferencia -->
<div id="simulacionModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Simulación de Transferencia</h2>
        <form id="formSimulacion">
            <div class="form-group">
                <label for="nombreBanco">Nombre del Banco</label>
                <select id="nombreBanco" name="nombreBanco" class="form-control" required>
                    <option value="Banco 1">Banco BBVA</option>
                    <option value="Banco 2">Banco BANORTE</option>
                    <option value="Banco 3">Banco HSBC</option>
                </select>
            </div>
            <div class="form-group">
                <label for="numeroCuenta">Número de Cuenta</label>
                <input type="text" id="numeroCuenta" name="numeroCuenta" class="form-control" placeholder="1234-5678-9012-3456" required>
            </div>
            <div class="form-group">
                <label for="monto">Monto</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="monto" name="monto" class="form-control" required>
                </div>
            </div>
            <button type="submit" id="btnAceptarTransferencia" class="btn btn-primary w-100" style="background-color: #264653; border: none; border-radius: 10px; padding: 1rem; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s ease;">Aceptar Transferencia</button>
        </form>
        <div id="loading" class="loading" style="display: none; text-align: center; margin-top: 20px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <p>Procesando su transferencia...</p>
        </div>
        <div id="mensajeExito" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
            Transferencia exitosa!!
        </div>
    </div>
</div>
<!-- Nuevo Modal pequeño para Efectivo -->
<div id="efectivoModal" class="modal">
    <div class="modal-content" style="width: 300px; margin: auto;">
        <span class="close-modal">&times;</span>
        <div id="loadingEfectivo" class="loading" style="display: none; text-align: center; margin-top: 20px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <p>Procesando su solicitud...</p>
        </div>
        <div id="mensajeExitoEfectivo" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
            Solicitud procesada con éxito!!
        </div>
    </div>
</div>
<!-- Modal para Desayunos -->
<div id="desayunoModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <div class="row">
      <div class="col-md-5">
        <img id="modalDesayunoImage" src="" alt="Desayuno Image" class="img-fluid rounded shadow">
      </div>
      <div class="col-md-7">
        <h2 id="modalDesayunoName" class="mb-3 fw-bold" style="color: #264653; font-size: 2rem;"></h2>
        <div class="product-info">
          <!-- Precio -->
          <div class="info-item mt-3">
            <h5 style="color: #2a9d8f; font-size: 1.1rem;">Precio:</h5>
            <p id="modalDesayunoPrice" class="text-muted" style="font-size: 1rem;"></p>
          </div>
          <!-- Descripción -->
          <div class="info-item mt-3">
            <h5 style="color: #2a9d8f; font-size: 1.1rem;">Descripción:</h5>
            <p id="modalDesayunoDescription" class="text-muted" style="font-size: 1rem;"></p>
          </div>
          <!-- Cantidad Disponible -->
          <div class="info-item mt-3">
            <h5 style="color: #2a9d8f; font-size: 1.1rem;">Cantidad Disponible:</h5>
            <p id="modalDesayunoQuantity" class="text-muted" style="font-size: 1rem;"></p>
          </div>

          <!-- Formulario -->
          <form id="formDesayuno" class="mt-3">
            <!-- Cantidad a comprar -->
            <div class="form-group">
              <h5 style="color: #2a9d8f; font-size: 1.1rem;">Cantidad:</h5>
              <input type="number" id="cantidadDesayuno" name="cantidadDesayuno" class="form-control" min="1" required>
            </div>

            <!-- Método de pago -->
            <div class="form-group mt-3">
              <h5 style="color: #2a9d8f; font-size: 1.1rem;">Método de Pago:</h5>
              <div>
                <label>
                  <input type="radio" id="formaPagoEfectivoDesayuno" name="formaPagoDesayuno" value="efectivo" required>
                  Efectivo
                </label>
              </div>
              <div>
                <label>
                  <input type="radio" id="formaPagoTransferenciaDesayuno" name="formaPagoDesayuno" value="transferencia" required>
                  Transferencia
                </label>
              </div>
            </div>

            <!-- Aquí aparece el total ANTES del botón -->
            <div id="totalCalculadoDesayuno" class="mt-3" style="font-size: 1.2rem; color: #264653; font-weight: bold;"></div>

            <!-- Botón -->
            <button type="submit" id="btnAccionDesayuno" class="btn btn-primary w-100" 
                    style="background-color: #2a9d8f; border: none; border-radius: 10px; padding: 1rem;">
              Solicitar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Modal para Simulación de Transferencia -->
<div id="simulacionModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Simulación de Transferencia</h2>
        <form id="formSimulacion">
            <div class="form-group">
                <label for="nombreBanco">Nombre del Banco</label>
                <select id="nombreBanco" name="nombreBanco" class="form-control" required>
                    <option value="BBVA">Banco BBVA</option>
                    <option value="BANORTE">Banco BANORTE</option>
                    <option value="HSBC">Banco HSBC</option>
                </select>
            </div>
            <div class="form-group">
                <label for="numeroCuenta">Número de Cuenta</label>
                <input type="text" id="numeroCuenta" name="numeroCuenta" class="form-control" placeholder="1234-5678-9012-3456" required>
            </div>
            <div class="form-group">
                <label for="monto">Monto</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="monto" name="monto" class="form-control" required>
                </div>
            </div>
            <button type="submit" id="btnAceptarTransferencia" class="btn btn-primary w-100" style="background-color: #264653; border: none; border-radius: 10px; padding: 1rem; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s ease;">Aceptar Transferencia</button>
        </form>
        <div id="loading" class="loading" style="display: none; text-align: center; margin-top: 20px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <p>Procesando su transferencia...</p>
        </div>
        <div id="mensajeExito" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
            Transferencia exitosa!!
        </div>
    </div>
</div>

<!-- Modal para Efectivo -->
<div id="efectivoModal" class="modal">
    <div class="modal-content" style="width: 300px; margin: auto;">
        <span class="close-modal">&times;</span>
        <div id="loadingEfectivo" class="loading" style="display: none; text-align: center; margin-top: 20px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <p>Procesando su solicitud...</p>
        </div>
        <div id="mensajeExitoEfectivo" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
            Solicitud procesada con éxito!!
        </div>
    </div>
</div>


    <footer style="background-color:rgb(100, 100, 100);">
        <div class="container container-footer">
            <div class="menu-footer">
                <div class="contact-info">
                    <p class="title-footer">Información de Contacto</p>
                    <ul>
                        <li>Teléfono: 997-151-22-11</li>
                        <li>EmaiL: cafeteria@support.com</li>
                    </ul>
                    <div class="social-icons">
                        <a href="https://chat.whatsapp.com/CODIGO_UNICO_DEL_GRUPO" target="_blank">
                            <span class="whatsapp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="information">
                    <p class="title-footer">Información</p>
                    <ul>
                        <li><a href="#">Acerca de Nosotros</a></li>
                        <li><a href="#">Politicas de Privacidad</a></li>
                        <li><a href="#">Términos y condiciones</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>Universidad Tecnológica Regional del Sur &copy; 2025</p>
            </div>
        </div>
    </footer>
<script>
src="https://kit.fontawesome.com/81581fb069.js"
    crossorigin="anonymous"
</script>
<script>

$(document).ready(function() {
    // Mostrar secciones iniciales
    $('#products-section, #breakfast-section, #almuerzo-section').show();

    // Cargar datos iniciales
    loadBreakfasts();
    loadAlmuerzos();

    // Establecer título y filtros iniciales
    changeTitle('Todos');
    activateFilter('todo');
    filterProducts('todo', true);

    // Funciones auxiliares
    function changeTitle(newTitle) {
        $('.heading-1').text(newTitle);
    }

    function activateFilter(selectedFilter) {
        $('.filter-option').removeClass('active');
        $(`.filter-option[data-filter="${selectedFilter}"]`).addClass('active');
    }

    function filterProducts(filter, showAll = false) {
        const products = $('.card-product');
        
        if (showAll) {
            products.show();
            loadBreakfasts();
            loadAlmuerzos();
            $('#products-section, #breakfast-section, #almuerzo-section').show();
            return;
        }
        
        if (filter === 'destacados') {
            products.each(function() {
                $(this).toggle($(this).data('destacado') === 'true');
            });
        } else if (filter === 'todo') {
            products.show();
        } else if (filter === 'todo-desayuno') {
            $('#breakfast-section').show();
            $('#almuerzo-section').hide();
            $('#products-section').hide();
        } else if (filter === 'todo-almuerzo') {
            $('#breakfast-section').hide();
            $('#almuerzo-section').show();
            $('#products-section').hide();
        }
    }

    // Eventos de botones
    $('.btn-todo').on('click', function() {
        changeTitle('Todos');
        filterProducts('todo', true);
        $('.filter-option').removeClass('active');
        $(this).addClass('active');
        
        // Ocultar botones específicos
        $('.btn-todo-producto, .btn-todo-desayuno, .btn-todo-almuerzo').hide();
    });

    $('.btn-todo-producto').on('click', function() {
        $('#breakfast-section, #almuerzo-section').hide();
        $('#products-section').show();
        $('.btn-todo').removeClass('active');
        $(this).addClass('active');
        filterProducts('todo');
    });

    $('.btn-todo-desayuno').on('click', function() {
        $('#products-section, #almuerzo-section').hide();
        $('#breakfast-section').show();
        $('.btn-todo').removeClass('active');
        $(this).addClass('active');
        loadBreakfasts();
    });

    $('.btn-todo-almuerzo').on('click', function() {
        $('#products-section, #breakfast-section').hide();
        $('#almuerzo-section').show();
        $('.btn-todo').removeClass('active');
        $(this).addClass('active');
        loadAlmuerzos();
    });

    // Eventos de enlaces de navegación
    $('#show-products').on('click', function(e) {
        e.preventDefault();
        changeTitle('Productos');
        
        // Mostrar botón de productos y ocultar otros
        $('.btn-todo-producto').show().addClass('active');
        $('.btn-todo, .btn-todo-desayuno, .btn-todo-almuerzo').hide();
        
        // Mostrar sección de productos y ocultar otras
        $('#products-section').show();
        $('#breakfast-section, #almuerzo-section').hide();
        
        // Desplazarse a la sección
        $('html, body').animate({
            scrollTop: $('#products-section').offset().top - 100
        }, 500);
    });

    $('#show-todo').on('click', function(e) {
        e.preventDefault();
        changeTitle('Todos');
        
        // Mostrar botón de "Todos" y ocultar botones específicos
        $('.btn-todo').show().addClass('active');
        $('.btn-todo-producto, .btn-todo-desayuno, .btn-todo-almuerzo').hide();
        
        // Mostrar todas las secciones
        filterProducts('todo', true);
        
        // Desplazarse a la sección
        $('html, body').animate({
            scrollTop: $('#products-section').offset().top - 100
        }, 500);
    });

    $('#show-desayunos').on('click', function(e) {
        e.preventDefault();
        changeTitle('Desayunos');
        
        // Mostrar botón de desayunos y ocultar otros
        $('.btn-todo-desayuno').show().addClass('active');
        $('.btn-todo, .btn-todo-producto, .btn-todo-almuerzo').hide();
        
        // Mostrar sección de desayunos y ocultar otras
        $('#breakfast-section').show();
        $('#products-section, #almuerzo-section').hide();
        
        loadBreakfasts();
        
        // Desplazarse a la sección
        $('html, body').animate({
            scrollTop: $('#breakfast-section').offset().top - 100
        }, 500);
    });

    $('#show-almuerzos').on('click', function(e) {
        e.preventDefault();
        changeTitle('Almuerzos');
        
        // Mostrar botón de almuerzos y ocultar otros
        $('.btn-todo-almuerzo').show().addClass('active');
        $('.btn-todo, .btn-todo-producto, .btn-todo-desayuno').hide();
        
        // Mostrar sección de almuerzos y ocultar otras
        $('#almuerzo-section').show();
        $('#products-section, #breakfast-section').hide();
        
        loadAlmuerzos();
        
        // Desplazarse a la sección
        $('html, body').animate({
            scrollTop: $('#almuerzo-section').offset().top - 100
        }, 500);
    });

    // Eventos de opciones de filtro
    $('.filter-option').on('click', function() {
        const filter = $(this).data('filter');
        activateFilter(filter);
        filterProducts(filter);
    });

    // Eventos de modales
    $(document).on('click', '.fa-eye, .fa-basket-shopping', function() {
        const productCard = $(this).closest('.card-product');
        const productName = productCard.find('h3').text();
        const productImage = productCard.find('img').attr('src');
        const productDescription = productCard.data('description');

        if ($(this).closest('.card-product').parent().attr('id') === 'container-almuerzos') {
            // Lógica para almuerzos
            const productQuantityPorcion = productCard.data('cantidad-porcion');
            const productQuantityMedia = productCard.data('cantidad-media');
            const productQuantityOrden = productCard.data('cantidad-orden');

            $('#modalAlmuerzoName').text(productName);
            $('#modalAlmuerzoImage').attr('src', productImage);
            $('#modalAlmuerzoPricePorcion').text(`Porción: ${productCard.find('.price-list .price:eq(0)').text()}`);
            $('#modalAlmuerzoPriceMedia').text(`Media: ${productCard.find('.price-list .price:eq(1)').text()}`);
            $('#modalAlmuerzoPriceOrden').text(`Orden: ${productCard.find('.price-list .price:eq(2)').text()}`);
            $('#modalAlmuerzoDescription').text(productDescription);
            $('#modalAlmuerzoCantidadPorcion').text(`${productQuantityPorcion} disponibles`);
            $('#modalAlmuerzoCantidadMedia').text(`${productQuantityMedia} disponibles`);
            $('#modalAlmuerzoCantidadOrden').text(`${productQuantityOrden} disponibles`);
            $('#modalAlmuerzoEstadoInventario').text((productQuantityPorcion > 0 || productQuantityMedia > 0 || productQuantityOrden > 0) ? 'Disponible' : 'Agotado');
            $('#almuerzoModal').fadeIn(300);
            
            // Inicializar formulario de almuerzos
            inicializarFormularioAlmuerzo();
        }  else {
            // Lógica para productos
            const productQuantity = productCard.data('quantity');
            const productPrice = productCard.find('.price').text().replace('$', ''); // Obtener el precio del producto

            $('#modalProductName').text(productName);
            $('#modalProductImage').attr('src', productImage);
            $('#modalProductDescription').text(productDescription);
            $('#modalProductQuantity').text(`Cantidad en inventario: ${productQuantity}`);
            $('#modalProductPrice').text(`$${productPrice}`); // Mostrar el precio en el modal

            // Calcular el total en tiempo real
            $('#cantidadProducto').on('input', function() {
                const cantidad = parseFloat($(this).val());
                const precio = parseFloat(productPrice);
                const total = cantidad * precio;
                $('#totalCalculadoProducto').text(`Total a pagar: $${total.toFixed(2)}`);
            });

            // Mostrar el modal de productos
            $('#productModal').fadeIn(300);
        }
    });

    if ($(this).closest('.card-product').parent().attr('id') === 'container-breakfasts') {
            // Lógica para desayunos
            const productQuantity = productCard.data('quantity');
            const productPrice = productCard.find('.price').text().replace('$', ''); // Obtener el precio del desayuno

            $('#modalDesayunoName').text(productName);
            $('#modalDesayunoImage').attr('src', productImage);
            $('#modalDesayunoDescription').text(productDescription);
            $('#modalDesayunoQuantity').text(`Cantidad: ${productQuantity}`);
            $('#modalDesayunoPrice').text(`$${productPrice}`); // Mostrar el precio en el modal

            // Calcular el total en tiempo real
            $('#cantidadDesayuno').on('input', function() {
                const cantidad = parseFloat($(this).val());
                const precio = parseFloat(productPrice);
                const total = cantidad * precio;
                $('#totalCalculadoDesayuno').text(`Total: $${total.toFixed(2)}`);
            });

            // Mostrar el modal de desayunos
            $('#desayunoModal').fadeIn(300);
        }

    // Cerrar modales
    $('.close-modal, .modal').on('click', function(event) {
        if ($(event.target).hasClass('modal') || $(event.target).hasClass('close-modal')) {
            $('.modal').fadeOut(300);
        }
    });

    // Cambiar el texto del botón según el método de pago (desayunos)
    $('#formaPagoEfectivoDesayuno').on('change', function() {
        $('#btnAccionDesayuno').text('Solicitar');
    });

    $('#formaPagoTransferenciaDesayuno').on('change', function() {
        $('#btnAccionDesayuno').text('Comprar');
    });

    // Manejar el envío del formulario de desayuno
    $('#formDesayuno').on('submit', function(event) {
        event.preventDefault();

        const nombreDesayuno = $('#modalDesayunoName').text();
        const cantidad = $('#cantidadDesayuno').val();
        const precio = parseFloat($('#modalDesayunoPrice').text().replace('$', ''));
        const total = cantidad * precio;

        if ($('#formaPagoEfectivoDesayuno').is(':checked')) {
            // Lógica para pago en efectivo
            $('#efectivoModal').fadeIn(300);
            $('#loadingEfectivo').show();

            setTimeout(function() {
                $('#loadingEfectivo').hide();
                $('#mensajeExitoEfectivo').show();
                setTimeout(function() {
                    $('#efectivoModal').fadeOut(300);
                    $('#desayunoModal').fadeOut(300);
                }, 2000);
            }, 3000);
        } else if ($('#formaPagoTransferenciaDesayuno').is(':checked')) {
            // Mostrar el modal de transferencia
            $('#simulacionModal').fadeIn(300);
            $('#monto').val(total.toFixed(2));
        }
    });

    // Manejar el envío del formulario de transferencia
    $('#formSimulacion').on('submit', function(event) {
        event.preventDefault();
        $('#loading').show();
        $('#btnAceptarTransferencia').prop('disabled', true);

        setTimeout(function() {
            $('#loading').hide();
            $('#mensajeExito').show();
            setTimeout(function() {
                $('#simulacionModal').fadeOut(300);
                $('#desayunoModal').fadeOut(300);
                $('#btnAceptarTransferencia').prop('disabled', false);
            }, 2000);
        }, 3000);
    });

    // Cargar datos de desayunos
    function loadBreakfasts() {
        $.ajax({
            url: 'get_breakfasts.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const container = $('#container-breakfasts');
                container.empty();
                if (data.length > 0) {
                    data.forEach(desayuno => {
                        container.append(`
                            <div class="card-product" data-description="${desayuno.descripcionDesayuno}" data-quantity="${desayuno.cantidadDesayuno}">
                                <div class="container-img">
                                    <img src="uploads/${desayuno.imgDesayuno}" alt="${desayuno.nombreProducto}" />
                                </div>
                                <div class="content-card-product">
                                    <h3>${desayuno.nombreProducto}</h3>
                                    <span class="add-cart">
                                        <i class="fa-solid fa-basket-shopping"></i>
                                    </span>
                                    <p class="price">$${desayuno.precioDesayuno}</p>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    container.append('<p>No hay desayunos disponibles para hoy.</p>');
                }
                $('#breakfast-section').show();
            },
            error: function() {
                alert('Error al cargar los desayunos.');
            }
        });
    }

    // Cargar datos de almuerzos
    function loadAlmuerzos() {
        $.ajax({
            url: 'get_almuerzos.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const container = $('#container-almuerzos');
                container.empty();
                if (data.length > 0) {
                    data.forEach(almuerzo => {
                        container.append(`
                            <div class="card-product" data-description="${almuerzo.descripcionAlmuerzo}" data-cantidad-porcion="${almuerzo.cantidadPorcion}" data-cantidad-media="${almuerzo.cantidadMedia}" data-cantidad-orden="${almuerzo.cantidadOrden}">
                                <div class="container-img">
                                    <img src="uploads/${almuerzo.imgAlmuerzo}" alt="${almuerzo.nombreProducto}" />
                                </div>
                                <div class="content-card-product">
                                    <h3>${almuerzo.nombreProducto}</h3>
                                    <div class="price-container">
                                        <div class="price-list">
                                            <p class="price">Porción: $${almuerzo.precioPorcion}</p>
                                            <p class="price">Media: $${almuerzo.precioMedia}</p>
                                            <p class="price">Orden: $${almuerzo.precioOrden}</p>
                                        </div>
                                        <div class="add-cart">
                                            <i class="fa-solid fa-basket-shopping"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    container.append('<p>No hay almuerzos disponibles para hoy.</p>');
                }
                $('#almuerzo-section').show();
            },
            error: function() {
                alert('Error al cargar los almuerzos.');
            }
        });
    }

    // Mostrar todos los desayunos o almuerzos
    $('#show-all-breakfasts, #show-all-almuerzos').on('click', function(e) {
        e.preventDefault();
        if ($(this).attr('id') === 'show-all-breakfasts') {
            loadBreakfasts();
        } else {
            loadAlmuerzos();
        }
    });

    // ====== FUNCIONALIDAD PARA LA COMPRA DE ALMUERZOS ======
    
    // Inicialización del formulario de almuerzos
    $('#almuerzoModal').on('shown', function() {
        inicializarFormularioAlmuerzo();
    });

    // Función para inicializar el formulario de almuerzos
    function inicializarFormularioAlmuerzo() {
        const tipoComida = $('#tipoComida');
        const cantidad = $('#cantidad');
        const tortillasExtras = $('#tortillasExtras');
        const totalCalculado = $('#totalCalculado');
        const btnAccion = $('#btnAccion');
        
        // Resetear valores del formulario
        tipoComida.val('porcion');
        cantidad.val(1);
        tortillasExtras.val(0);
        $('#formaPagoEfectivo').prop('checked', true);
        btnAccion.text('Solicitar');
        
        // Calcular total inicial
        calcularTotalAlmuerzo();
    }
    
    // Función para calcular el total de almuerzos
    function calcularTotalAlmuerzo() {
        const precios = {
            porcion: 40,
            media: 55,
            orden: 100,
            tortillas: 1
        };
        
        const tipo = $('#tipoComida').val();
        const cantidadValue = parseInt($('#cantidad').val()) || 0;
        const tortillasValue = parseInt($('#tortillasExtras').val()) || 0;
        const precioComida = precios[tipo] || 0;
        const precioTortillas = precios.tortillas * tortillasValue;
        const total = (cantidadValue * precioComida) + precioTortillas;
        
        $('#totalCalculado').text(`Total: $${total.toFixed(2)}`);
        return total;
    }
    
    // Eventos para calcular total al cambiar selección
    $('#tipoComida, #cantidad, #tortillasExtras').on('change input', function() {
        calcularTotalAlmuerzo();
    });
    
    // Cambiar texto del botón según método de pago
    $('#formaPagoTransferencia').on('change', function() {
        $('#btnAccion').text('Comprar');
    });
    
    $('#formaPagoEfectivo').on('change', function() {
        $('#btnAccion').text('Solicitar');
    });
    
    // Manejar el envío del formulario de almuerzo
    $('#formAlmuerzo').on('submit', function(event) {
        event.preventDefault();
        
        const nombreAlmuerzo = $('#modalAlmuerzoName').text();
        const tipoComida = $('#tipoComida').val();
        const cantidadValue = parseInt($('#cantidad').val()) || 0;
        const tortillasValue = parseInt($('#tortillasExtras').val()) || 0;
        const total = calcularTotalAlmuerzo();
        
        if ($('#formaPagoEfectivo').is(':checked')) {
            // Lógica para pago en efectivo (preventa)
            const datos = {
                nombrePreventa: nombreAlmuerzo,
                cantidad_orden: cantidadValue,
                precioUnitarioPreventa: (tipoComida === 'porcion') ? 40 : 
                                        (tipoComida === 'media') ? 55 : 100,
                precioTotalPreventa: total,
                metodoPago: 'efectivo',
                tipoComida: tipoComida,
                tortillasExtras: tortillasValue
            };
            
            // Mostrar indicador de carga
            $('#efectivoModal').fadeIn(300);
            $('#loadingEfectivo').show();
            
            // Enviar datos al servidor
            $.ajax({
                url: 'guardar_preventa.php',
                method: 'POST',
                data: datos,
                success: function(response) {
                    $('#loadingEfectivo').hide();
                    $('#mensajeExitoEfectivo').show();
                    
                    setTimeout(function() {
                        $('#efectivoModal').fadeOut(300);
                        $('#almuerzoModal').fadeOut(300);
                        location.reload();
                    }, 2000);
                },
                error: function() {
                    $('#loadingEfectivo').hide();
                    alert('Error al guardar la solicitud de almuerzo');
                    $('#efectivoModal').fadeOut(300);
                }
            });
            
        } else if ($('#formaPagoTransferencia').is(':checked')) {
            // Mostrar modal de transferencia
            $('#simulacionModal').fadeIn(300);
            $('#monto').val(total.toFixed(2));
            
            // Preparar el procesamiento de la transferencia
            $('#formSimulacion').off('submit').on('submit', function(e) {
                e.preventDefault();
                
                $('#loading').show();
                $('#btnAceptarTransferencia').prop('disabled', true);
                
                const fechaActual = new Date().toISOString().slice(0, 19).replace('T', ' ');
                
                // Datos para la tabla pagos
                const datosPago = {
                    nombrePagos: nombreAlmuerzo,
                    canTotalP: total,
                    fePago: fechaActual,
                    metodoPago: 'transferencia',
                    cantidad_orden: cantidadValue,
                    tipoComida: tipoComida,
                    tortillasExtras: tortillasValue
                };
                
                // Simular procesamiento y enviar datos
                setTimeout(function() {
                    $.ajax({
                        url: 'guardar_pago.php',
                        method: 'POST',
                        data: datosPago,
                        success: function(response) {
                            $('#loading').hide();
                            $('#mensajeExito').show();
                            
                            setTimeout(function() {
                                $('#mensajeExito').hide();
                                $('#simulacionModal').fadeOut(300);
                                $('#almuerzoModal').fadeOut(300);
                                $('#btnAceptarTransferencia').prop('disabled', false);
                                location.reload();
                            }, 2000);
                        },
                        error: function() {
                            $('#loading').hide();
                            alert('Error al procesar el pago');
                            $('#btnAceptarTransferencia').prop('disabled', false);
                        }
                    });
                }, 3000);
            });
        }
    });
    
    // Control de inventario al realizar compra
    function actualizarInventario(tipoComida, cantidad) {
        // Esta función se podría implementar si necesitas actualizar el inventario
        // inmediatamente después de una compra sin recargar la página
        const productCard = $(`#container-almuerzos .card-product:contains('${$('#modalAlmuerzoName').text()}')`);
        
        if (productCard.length > 0) {
            const cantidadActual = parseInt(productCard.data(`cantidad-${tipoComida}`)) || 0;
            const nuevaCantidad = Math.max(0, cantidadActual - cantidad);
            
            productCard.data(`cantidad-${tipoComida}`, nuevaCantidad);
            
            // Actualizar texto en modal si está abierto
            $(`#modalAlmuerzoCantidad${tipoComida.charAt(0).toUpperCase() + tipoComida.slice(1)}`).text(
                `${tipoComida.charAt(0).toUpperCase() + tipoComida.slice(1)}: ${nuevaCantidad} disponibles`
            );
            
            // Actualizar estado de disponibilidad
            actualizarEstadoInventario();
        }
    }
    
    function actualizarEstadoInventario() {
        const porcion = parseInt($('#modalAlmuerzoCantidadPorcion').text().replace(/\D/g, '')) || 0;
        const media = parseInt($('#modalAlmuerzoCantidadMedia').text().replace(/\D/g, '')) || 0;
        const orden = parseInt($('#modalAlmuerzoCantidadOrden').text().replace(/\D/g, '')) || 0;
        
        $('#modalAlmuerzoEstadoInventario').text(
            (porcion > 0 || media > 0 || orden > 0) ? 'Disponible' : 'Agotado'
        );
    }
}); 
</script>
</body>
</html>