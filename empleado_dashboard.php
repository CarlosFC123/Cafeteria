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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        
        /* .modal {
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
        } */

        .modal-content {
            background-color: #fff; /* Fondo blanco */
            margin: 5% auto; /* Centra el modal vertical y horizontalmente */
            padding: 25px;
            border: none;
            width: 90%;
            max-width: 800px; /* Ancho máximo del modal */
            border-radius: 15px; /* Bordes redondeados */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); /* Sombra */
            transform: translateY(0); /* Posición inicial */
            animation: modalFadeIn 0.4s; /* Animación de aparición */
            position: relative; /* Asegura que el contenido no se mueva */
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px); /* Comienza 50px más arriba */
            }
            to {
                opacity: 1;
                transform: translateY(0); /* Termina en su posición original */
            }
        }

        #productModal.modal,
        #desayunoModal.modal,
        #almuerzoModal.modal {
            display: none; /* Oculta el modal por defecto */
            position: fixed; /* Fija el modal en la pantalla */
            z-index: 1000; /* Asegura que esté por encima de otros elementos */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Permite el desplazamiento si el contenido es muy largo */
            background-color: rgba(0, 0, 0, 0.7); /* Fondo oscuro semitransparente */
            backdrop-filter: blur(5px); /* Efecto de desenfoque */
            transition: all 0.3s ease; /* Transición suave */
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
        
        .content-card-product h3 {
            
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 500;
        }
        .price-container {
    display: flex;
    justify-content: space-between; /* Separa el precio y el botón */
    align-items: center; /* Centra verticalmente */
    width: 100%; /* Ocupa todo el ancho disponible */
    margin-top: 10px; /* Espaciado superior */
}

.price {
    font-size: 16px; /* Tamaño del texto del precio */
    font-weight: bold; /* Texto en negrita */
    color: #333; /* Color del texto */
}
        .price-list {
            flex: 1;
            text-align: left;
        }
        
        .add-cart {
            color: #C7A17A; /* Color del ícono */
            background-color: transparent; /* Fondo transparente */
            border: 2px solid #C7A17A; /* Borde del mismo color que el ícono */
            border-radius: 50%; /* Borde redondeado */
            padding: 10px; /* Espaciado interno */
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px; /* Ancho del botón */
            height: 40px; /* Altura del botón */
            cursor: pointer; /* Cambia el cursor al pasar sobre el botón */
            transition: all 0.3s ease; /* Transición suave para efectos */
        }

        .add-cart:hover {
            background-color: #C7A17A; /* Cambia el fondo al pasar el mouse */
            color: white; /* Cambia el color del ícono al pasar el mouse */
        }

        .add-cart i {
            font-size: 18px; /* Tamaño del ícono */
        }


        /* Estilo normal para pantallas grandes */
        .cart-button {
        display: flex;
        align-items: center;
        background-color:rgb(255, 255, 255);
        color: black;
        border: none;
        border-radius: 30px;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-left: auto;
        }

        .cart-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .cart-icon {
        font-size: 1.5rem;
        margin-right: 10px;
        position: relative;
        }

        .cart-text {
        font-weight: 600;
        font-size: 1rem;
        }

        @media screen and (max-width: 768px) {
            .cart-button {
                background-color: transparent; 
                box-shadow: none; 
                padding: 0; 
            }
            
            .cart-text {
                display: none; 
            } 
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
        .close-modalUser:focus {
            color: #000;
            text-decoration: none;
        }
        .cart-panel {
            position: fixed;
            top: 0;
            right: -800px; 
            width: 800px;
            height: 100%;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            padding: 20px;
            overflow-y: auto;
        }
        .cart-panel.open {
            right: 0;
        }
        .close-cart-panel {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        .cart-tabs {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            flex-wrap: wrap; /* Permite que las pestañas se ajusten en pantallas pequeñas */
        }
        .tab-button {
            background: none;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease-in-out;
        }
        .tab-button.active {
            border-bottom: 2px solid black;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        @media (max-width: 1024px) {
            .cart-panel {
                width: 60%; 
                right: -60%; 
            }
            
            .cart-panel.open {
                right: 0;
            }

            .tab-button {
                font-size: 14px;
                padding: 8px;
            }
        }
        @media (max-width: 768px) {
            .cart-panel {
                width: 100%; /* Ocupa todo el ancho en móviles */
                right: -100%; /* Ocultar inicialmente */
            }

            .cart-panel.open {
                right: 0;
            }

            .cart-tabs {
                flex-direction: column; /* Las pestañas se apilan en móviles */
                align-items: center;
            }

            .tab-button {
                width: 100%; /* Botones ocupan todo el ancho */
                padding: 12px;
                font-size: 18px;
            }
        }
        
      /* Estilos para la sección de productos y buscador */
        .container.top-products {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        padding: 15px 0;
        }

        .heading-1 {
        width: 100%;
        margin-bottom: 15px;
        font-size: 24px;
        color: #333;
        }

        .container-options {
        width: 70%;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
        }

        .filter-option {
        padding: 8px 16px;
        background-color: #f5f5f5;
        border-radius: 20px;
        font-size: 14px;
        color: #555;
        cursor: pointer;
        transition: all 0.3s ease;
        }

        .filter-option:hover {
        background-color: #e0e0e0;
        color: #333;
        }

        .filter-option.active {
        background-color: #d2a679;
        color: white;
        }

/* Estilos del buscador */
        .search-form {
        position: absolute;
        right: 0;
        top: 50px;
        width: 280px;
        padding: 0;
        transition: width 0.3s ease;
        }

        #searchInput {
        width: 100%;
        padding: 10px 40px 10px 15px;
        
        border-radius: 25px;
        font-size: 14px;
        color: #555;
        background-color: #fff;
        transition: all 0.3s ease;
        }

        #searchInput:focus {
        box-shadow: 0 0 8px rgba(210, 166, 121, 0.4);
        outline: none;
        width: 100%;
        }

        #btn-search {
        margin-left: -70px;
        border: none;
        background-color: transparent;
        color: #d2a679;
        }

        

        /* Estilo específico para el icono de búsqueda */
        #btn-search i {
        font-size: 20px;
        transition: transform 0.3s ease;
        }

        #btn-search:hover i {
        transform: scale(1.2);
        }

        .btn-clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background-color: transparent;
        color: #999;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        }

        .btn-clear:hover {
        color: #555;
        }

        /* Media queries para responsive */
        @media (max-width: 768px) {
        .container-options {
            width: 100%;
        }
        
        .search-form {
            position: relative;
            width: 100%;
            right: auto;
            top: auto;
            margin-top: 15px;
        }
        }
        /* Estilos para el ícono del menú en móviles */
#mobile-menu-icon {
    display: none; /* Oculta el ícono por defecto */
    cursor: pointer;
    font-size: 24px;
    color: white;
}

/* Estilos para el menú en móviles */
#mobile-menu {
    display: flex; /* Muestra el menú en pantallas grandes */
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Media query para móviles */
@media (max-width: 768px) {
    #mobile-menu-icon {
        display: block; /* Muestra el ícono en móviles */
    }

    #mobile-menu {
        display: none; /* Oculta el menú en móviles por defecto */
        flex-direction: column;
        background-color: rgb(100, 100, 100);
        position: absolute;
        top: 50px; /* Ajusta según la altura de tu header */
        left: 0;
        width: 100%;
        padding: 10px;
    }

    #mobile-menu.active {
        display: flex; /* Muestra el menú cuando tiene la clase "active" */
    }
}

/* Navigation button styling improvements */
.menu li a {
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    transition: background-color 0.3s, color 0.3s;
    display: block;
    text-decoration: none;
}

.menu li a:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

/* Mobile-specific navigation styling */
@media (max-width: 768px) {
    .menu li {
        width: 100%;
        margin: 5px 0;
    }
    
    .menu li a {
        padding: 12px 15px;
        font-size: 16px;
        text-align: center;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .menu li a:hover, .menu li a:active {
        background-color: rgba(255, 255, 255, 0.3);
    }
}

@media (max-width: 768px) {
    #menu-semanal-modal .modal-dialog {
        width: 95%;
        max-width: none;
        margin: 10px auto;
    }
    
    #menu-semanal-modal .modal-content {
        padding: 10px;
    }
    
    #menu-semanal-modal .modal-header {
        padding: 10px 15px;
    }
    
    #menu-semanal-modal .modal-body {
        padding: 10px;
    }
    
    #menu-semanal-cards {
        flex-direction: column !important;
        align-items: center;
    }
    
    .menu-card {
        width: 90% !important;
        margin: 10px auto !important;
    }
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
                        <span class="user-name" id="user-name">
                            <?php echo $usuario_nombre . ' ' . $usuario_apellido; ?>
                        </span>
                        <i class="fa-solid fa-caret-down"></i>
                        <div class="dropdown-content">
                            <a href="#" id="miPerfil">Mi perfil</a>
                            <a href="logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                    <button class="cart-button">
                    <div class="cart-icon">
                        <span class="material-icons" style="font-size: 30px; color: #C7A17A;">shopping_cart</span>
                        
                    </div>
                    <span class="cart-text">Carrito</span>
                    </button>

                    <div id="cartPanel" class="cart-panel">
                        <span class="close-cart-panel">&times;</span>
                        <h2>Carrito</h2>
                        <div class="cart-tabs">
                            <button class="tab-button active" data-tab="compras">Compras</button>
                            <button class="tab-button" data-tab="solicitudes">Solicitudes</button>
                            <!-- <button class="tab-button" data-tab="ordenes">Órdenes</button> -->
                        </div>
                        <div class="cart-content">
                            <div id="compras" class="tab-content active">
                                <!-- Contenido de compras (pagos) -->
                                <div id="compras-list"></div>
                            </div>
                            <div id="solicitudes" class="tab-content">
                                <!-- Contenido de solicitudes (preventa) -->
                                <div id="solicitudes-list"></div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="background-color:rgb(100, 100, 100);">
            <nav class="navbar container">
                <i class="fa-solid fa-bars" id="mobile-menu-icon"></i>
                <ul class="menu" id="mobile-menu">
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
            <!-- <div id="botones-orden">
                <a href="#" id="crear-orden" style="color: black; font-weight: bold;">Crear Orden</a>
                <a href="#" id="guardar-orden" style="color: black; font-weight: bold; display: none;">Guardar Orden</a>
                <a href="#" id="cancelar-orden" style="color: black; font-weight: bold; display: none;">Cancelar</a>
            </div> -->
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
            <form class="search-form">
                <input type="search" id="searchInput" placeholder="Buscar..." oninput="filtrarProductos()" />
                <button type="button" id="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn-clear" id="btn-clear" onclick="clearSearch()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </form>
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
                        <div class="price-container">
                            <span class="add-cart" data-type="producto">
                                <i class="fas fa-basket-shopping"></i>
                            </span>
                            <p class="price">$<?php echo number_format($producto['precioProducto'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- modal perfil -->
        <div id="userProfileModal" class="modalUser">
            <div class="modal-contentUser">
                <span class="close-modalUser">&times;</span>
                <h2>Editar mi Perfil</h2>
                <div id="userProfileContent">
                    <form id="userProfileForm">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido:</label>
                            <input type="text" id="apellido" name="apellido" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" id="telefono" name="telefono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo electrónico:</label>
                            <input type="email" id="correo" name="correo" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="nuevaContrasena">Nueva contraseña:</label>
                            <input type="password" id="nuevaContrasena" name="nuevaContrasena" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="confirmarContrasena">Confirmar nueva contraseña:</label>
                            <input type="password" id="confirmarContrasena" name="confirmarContrasena" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>

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
                                <span class="add-cart" data-type="desayuno">
                                    <i class="fas fa-basket-shopping"></i>
                                </span>
                                <p class="price">$<?php echo number_format($desayuno['precioDesayuno'], 2); ?></p>
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
                            <span class="add-cart" data-type="almuerzo">
                                <i class="fa-solid fa-basket-shopping"></i>
                            </span>
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
                              
   <!-- Modal de Productos -->
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

<!-- Modal de Desayunos -->
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
                        <button type="submit" id="btnAccionDesayuno" class="btn btn-primary w-100" style="background-color: #2a9d8f; border: none; border-radius: 10px; padding: 1rem;">
                            Solicitar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Almuerzos -->
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
                    <!-- Precios -->
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Precios:</h5>
                        <p id="modalAlmuerzoPricePorcion" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoPriceMedia" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoPriceOrden" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                    </div>
                    <!-- Descripción -->
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Descripción:</h5>
                        <p id="modalAlmuerzoDescription" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                    </div>
                    <!-- Cantidades Disponibles -->
                    <div class="info-item mt-3">
                        <h5 style="color: #2a9d8f; font-size: 1.1rem; margin-bottom: 0.5rem;">Cantidades Disponibles:</h5>
                        <p id="modalAlmuerzoCantidadPorcion" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoCantidadMedia" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
                        <p id="modalAlmuerzoCantidadOrden" class="text-muted" style="font-size: 1rem; color: #6c757d; margin-bottom: 0;"></p>
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
                                <label>
                                    <input type="radio" id="formaPagoEfectivo" name="formaPago" value="efectivo" required>
                                    Efectivo
                                </label>
                            </div>
                            <div>
                                <label>
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

<!-- Modal de Transferencia para Desayunos -->
<div id="simulacionDesayunoModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <h2>Simulación de Transferencia (Desayunos)</h2>
    <form id="formSimulacionDesayuno">
      <div class="form-group">
        <label for="nombreBancoDesayuno">Nombre del Banco</label>
        <select id="nombreBancoDesayuno" name="nombreBancoDesayuno" class="form-control" required>
          <option value="BBVA">Banco BBVA</option>
          <option value="BANORTE">Banco BANORTE</option>
          <option value="HSBC">Banco HSBC</option>
        </select>
      </div>
      <div class="form-group">
        <label for="numeroCuentaDesayuno">Número de Cuenta</label>
        <input type="text" id="numeroCuentaDesayuno" name="numeroCuentaDesayuno" class="form-control" placeholder="1234-5678-9012-3456" required>
      </div>
      <div class="form-group">
        <label for="montoDesayuno">Monto</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="number" id="montoDesayuno" name="montoDesayuno" class="form-control" required>
        </div>
      </div>
      <button type="submit" id="btnAceptarTransferenciaDesayuno" class="btn btn-primary w-100" style="background-color: #264653; border: none; border-radius: 10px; padding: 1rem;">
        Aceptar Transferencia
      </button>
    </form>
    <div id="loadingDesayuno" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su transferencia...</p>
    </div>
    <div id="mensajeExitoDesayuno" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Transferencia exitosa!
    </div>
  </div>
</div>

<!-- Modal de Efectivo para Desayunos -->
<div id="efectivoDesayunoModal" class="modal">
  <div class="modal-content" style="width: 300px; margin: auto;">
    <span class="close-modal">&times;</span>
    <div id="loadingEfectivoDesayuno" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su solicitud...</p>
    </div>
    <div id="mensajeExitoEfectivoDesayuno" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Solicitud procesada con éxito!
    </div>
  </div>
</div>

<!-- Modal de Transferencia para Almuerzos -->
<div id="simulacionAlmuerzoModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <h2>Simulación de Transferencia (Almuerzos)</h2>
    <form id="formSimulacionAlmuerzo">
      <div class="form-group">
        <label for="nombreBancoAlmuerzo">Nombre del Banco</label>
        <select id="nombreBancoAlmuerzo" name="nombreBancoAlmuerzo" class="form-control" required>
          <option value="BBVA">Banco BBVA</option>
          <option value="BANORTE">Banco BANORTE</option>
          <option value="HSBC">Banco HSBC</option>
        </select>
      </div>
      <div class="form-group">
        <label for="numeroCuentaAlmuerzo">Número de Cuenta</label>
        <input type="text" id="numeroCuentaAlmuerzo" name="numeroCuentaAlmuerzo" class="form-control" placeholder="1234-5678-9012-3456" required>
      </div>
      <div class="form-group">
        <label for="montoAlmuerzo">Monto</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="number" id="montoAlmuerzo" name="montoAlmuerzo" class="form-control" required>
        </div>
      </div>
      <button type="submit" id="btnAceptarTransferenciaAlmuerzo" class="btn btn-primary w-100" style="background-color: #264653; border: none; border-radius: 10px; padding: 1rem;">
        Aceptar Transferencia
      </button>
    </form>
    <div id="loadingAlmuerzo" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su transferencia...</p>
    </div>
    <div id="mensajeExitoAlmuerzo" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Transferencia exitosa!
    </div>
  </div>
</div>

<!-- Modal de Efectivo para Almuerzos -->
<div id="efectivoAlmuerzoModal" class="modal">
  <div class="modal-content" style="width: 300px; margin: auto;">
    <span class="close-modal">&times;</span>
    <div id="loadingEfectivoAlmuerzo" class="loading" style="display: none; text-align: center; margin-top: 20px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
      </div>
      <p>Procesando su solicitud...</p>
    </div>
    <div id="mensajeExitoEfectivoAlmuerzo" class="alert alert-success" role="alert" style="display: none; text-align: center; margin-top: 20px;">
      ¡Solicitud procesada con éxito!
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
<!-- Incluir SweetAlert2 desde un CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    $(document).on('click', '.add-cart', function () {
        const productCard = $(this).closest('.card-product');
        const productType = $(this).data('type'); // Obtener el tipo de producto

        const productName = productCard.find('h3').text();
        const productImage = productCard.find('img').attr('src');
        const productDescription = productCard.data('description');

        if (productType === 'producto') {
            // Lógica para productos
            const productQuantity = productCard.data('quantity');
            const productPrice = productCard.find('.price').text().replace('$', '');
            const productCategory = productCard.data('category');
            const productTypeData = productCard.data('type');
            var productStatus = (productQuantity > 0) ? 'Disponible' : 'Agotado';

            $('#modalProductName').text(productName);
            $('#modalProductImage').attr('src', productImage);
            $('#modalProductDescription').text(productDescription);
            $('#modalProductQuantity').text(`${productQuantity}`);
            $('#modalProductPrice').text(`$${productPrice}`);
            $('#modalProductCategory').text(productCategory);
            $('#modalProductType').text(productTypeData);
            $('#modalProductStatus').text(productStatus);
            // Calcular el total en tiempo real
            $('#cantidadProducto').on('input', function () {
                const cantidad = parseFloat($(this).val());
                const precio = parseFloat(productPrice);
                const total = cantidad * precio;
                $('#totalCalculadoProducto').text(`Total a pagar: $${total.toFixed(2)}`);
            });

            // Mostrar el modal de productos
            $('#productModal').fadeIn(300);
        } else if (productType === 'desayuno') {
            // Lógica para desayunos
            const productQuantity = productCard.data('cantidad');
            const productPrice = productCard.find('.price').text().replace('$', '');

            $('#modalDesayunoName').text(productName);
            $('#modalDesayunoImage').attr('src', productImage);
            $('#modalDesayunoDescription').text(productDescription);
            $('#modalDesayunoQuantity').text(productQuantity);
            
            $('#modalDesayunoPrice').text(`$${productPrice}`);

            // Calcular el total en tiempo real
            $('#cantidadDesayuno').on('input', function () {
                const cantidad = parseFloat($(this).val());
                const precio = parseFloat(productPrice);
                const total = cantidad * precio;
                $('#totalCalculadoDesayuno').text(`Total: $${total.toFixed(2)}`);
            });

            // Mostrar el modal de desayunos
            $('#desayunoModal').fadeIn(300);
        } else if (productType === 'almuerzo') {
            // Lógica para almuerzos
            const productQuantityPorcion = productCard.data('cantidad-porcion');
            const productQuantityMedia = productCard.data('cantidad-media');
            const productQuantityOrden = productCard.data('cantidad-orden');

            $('#modalAlmuerzoName').text(productName);
            $('#modalAlmuerzoImage').attr('src', productImage);
            $('#modalAlmuerzoPricePorcion').text(`${productCard.find('.price-list .price:eq(0)').text()}`);
            $('#modalAlmuerzoPriceMedia').text(`${productCard.find('.price-list .price:eq(1)').text()}`);
            $('#modalAlmuerzoPriceOrden').text(`${productCard.find('.price-list .price:eq(2)').text()}`);
            $('#modalAlmuerzoDescription').text(productDescription);
            $('#modalAlmuerzoCantidadPorcion').text(`Porcion: ${productQuantityPorcion} disponibles`);
            $('#modalAlmuerzoCantidadMedia').text(`Media: ${productQuantityMedia} disponibles`);
            $('#modalAlmuerzoCantidadOrden').text(`Orden: ${productQuantityOrden} disponibles`);
            $('#modalAlmuerzoEstadoInventario').text((productQuantityPorcion > 0 || productQuantityMedia > 0 || productQuantityOrden > 0) ? 'Disponible' : 'Agotado');

            // Mostrar el modal de almuerzos
            $('#almuerzoModal').fadeIn(300);
        }
    });

    // Cerrar modales al hacer clic fuera o en el botón de cerrar
    $('.close-modal, .modal').on('click', function (event) {
        if ($(event.target).hasClass('modal') || $(event.target).hasClass('close-modal')) {
            $('.modal').fadeOut(300);
        }
    });
    $('.cart-button').on('click', function () {
        $('#cartPanel').toggleClass('open');
        cargarCompras(); // Cargar compras al abrir
    });

    $('.close-cart-panel').on('click', function () {
        $('#cartPanel').removeClass('open');
    });
    $('.tab-button').on('click', function () {
        const tab = $(this).data('tab');
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $(`#${tab}`).addClass('active');

        // Cargar datos según la pestaña seleccionada
        if (tab === 'compras') {
            cargarCompras();
        } else if (tab === 'solicitudes') {
            cargarSolicitudes();
        } else if (tab === 'ordenes') {
            cargarOrdenes();
        }
    });

    // Función para cargar compras (pagos)
    function cargarCompras() {
        $.ajax({
            url: 'obtener_compras.php',
            method: 'GET',
            success: function (data) {
                $('#compras-list').html(data);
            },
            error: function () {
                alert('Error al cargar las compras.');
            }
        });
    }

    // Función para cargar solicitudes (preventa)
    function cargarSolicitudes() {
        $.ajax({
            url: 'obtener_solicitudes.php',
            method: 'GET',
            success: function (data) {
                $('#solicitudes-list').html(data);
            },
            error: function () {
                alert('Error al cargar las solicitudes.');
            }
        });
    }

    // Función para cargar órdenes (ordenes)
    function cargarOrdenes() {
        $.ajax({
            url: 'obtener_ordenes.php',
            method: 'GET',
            success: function (data) {
                $('#ordenes-list').html(data);
            },
            error: function () {
                alert('Error al cargar las órdenes.');
            }
        });
    }

    // Cancelar una solicitud
    $(document).on('click', '.cancelar-solicitud', function () {
        const idPreventa = $(this).data('id');
        const fila = $(this).closest('tr'); // Obtener la fila que contiene el botón

        // Mostrar confirmación con SweetAlert2
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, volver',
            customClass: {
                confirmButton: 'btn btn-danger', // Clase personalizada para el botón de confirmación
                cancelButton: 'btn btn-secondary' // Clase personalizada para el botón de cancelación
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, hacer la solicitud AJAX
                $.ajax({
                    url: 'cancelar_solicitud.php',
                    method: 'POST',
                    data: { idPreventa: idPreventa },
                    dataType: 'json', // Asegurarse de que la respuesta sea interpretada como JSON
                    success: function (response) {
                        if (response && response.success) {
                            // Mostrar mensaje de éxito con SweetAlert2
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message || 'Solicitud cancelada correctamente.',
                                confirmButtonText: 'Aceptar',
                                customClass: {
                                    confirmButton: 'btn btn-success' // Clase personalizada para el botón
                                }
                            }).then(() => {
                                fila.remove(); // Eliminar la fila de la tabla
                            });
                        } else {
                            // Mostrar mensaje de error con SweetAlert2
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al cancelar la solicitud.',
                                confirmButtonText: 'Aceptar',
                                customClass: {
                                    confirmButton: 'btn btn-danger' // Clase personalizada para el botón
                                }
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        // Mostrar mensaje de error de AJAX con SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar la solicitud. Por favor, inténtalo de nuevo.',
                            confirmButtonText: 'Aceptar',
                            customClass: {
                                confirmButton: 'btn btn-danger' // Clase personalizada para el botón
                            }
                        });
                    }
                });
            }
        });
    });
    $(document).on('click', function (event) {
    // Si el clic no fue dentro del panel del carrito ni en el botón del carrito
    if (!$(event.target).closest('#cartPanel').length && !$(event.target).closest('.cart-button').length) {
        $('#cartPanel').removeClass('open'); // Cerrar el panel
    }
});
    $('#menu-semanal-btn').click(function(e) {
        e.preventDefault();
        $.get('menu_semanal.php', function(data) {
            var cards = $(data).find('.menu-card').clone();
            cards.each(function() {
                $(this).find('.btn-actions, a').remove();
                if ($(this).find('ul li').length === 0) {
                    $(this).find('p').remove();
                    $(this).append('<p>Menú del día aún no disponible</p>');
                }
            });
            $('#menu-semanal-cards').html(cards);
            $('#menu-semanal-modal').modal('show');
        }).fail(function() {
            alert('Error al cargar el menú semanal.');
        });
    });

    $('#menu-semanal-modal .close').click(function() {
        $('#menu-semanal-modal').modal('hide');
    });
    $('#formaPagoEfectivoProducto').on('change', function() {
        $('#btnAccionProducto').text('Solicitar');
    });

    $('#formaPagoTransferenciaProducto').on('change', function() {
        $('#btnAccionProducto').text('Comprar');
    });
    $('#formProducto').on('submit', function(event) {
        event.preventDefault();

        const nombreProducto = $('#modalProductName').text();
        const cantidad = $('#cantidadProducto').val();
        const precio = parseFloat($('#modalProductPrice').text().replace('$', ''));
        const total = cantidad * precio;

        if ($('#formaPagoEfectivoProducto').is(':checked')) {
            // Lógica para pago en efectivo
            const datos = {
                nombrePreventa: nombreProducto,
                cantidad_orden: cantidad,
                precioUnitarioPreventa: precio,
                precioTotalPreventa: total,
                metodoPago: 'efectivo'
            };

            // Mostrar indicador de carga
            $('#efectivoProductoModal').fadeIn(300);
            $('#loadingEfectivoProducto').show();
            $('#mensajeExitoEfectivoProducto').hide();

            // Simular un retraso de 3 segundos antes de enviar la solicitud
            setTimeout(function() {
                // Enviar datos al servidor
                $.ajax({
                    url: 'guardar_preventa_producto.php',
                    method: 'POST',
                    data: datos,
                    success: function(response) {
                        // Ocultar el indicador de carga
                        $('#loadingEfectivoProducto').hide();

                        // Mostrar el mensaje de éxito
                        $('#mensajeExitoEfectivoProducto').show();

                        // Ocultar el modal después de 2 segundos
                        setTimeout(function() {
                            $('#efectivoProductoModal').fadeOut(300);
                            $('#productModal').fadeOut(300);
                            location.reload();
                        }, 2000);
                    },
                    error: function() {
                        // Ocultar el indicador de carga en caso de error
                        $('#loadingEfectivoProducto').hide();
                        alert('Error al guardar la solicitud de producto');
                        $('#efectivoProductoModal').fadeOut(300);
                    }
                });
            }, 1000);
        } else if ($('#formaPagoTransferenciaProducto').is(':checked')) {
            // Lógica para transferencia
            $('#simulacionProductoModal').fadeIn(300);
            $('#montoProducto').val(total.toFixed(2));

            // Preparar el procesamiento de la transferencia
            $('#formSimulacionProducto').off('submit').on('submit', function(e) {
                e.preventDefault();

                $('#loadingProducto').show();
                $('#btnAceptarTransferenciaProducto').prop('disabled', true);

                const fechaActual = new Date().toISOString().slice(0, 19).replace('T', ' ');

                // Datos para la tabla pagos
                const datosPago = {
                    nombrePagos: nombreProducto,
                    canTotalP: total,
                    fePago: fechaActual,
                    metodoPago: 'transferencia',
                    cantidad_orden: cantidad
                };

                // Simular procesamiento y enviar datos
                setTimeout(function() {
                    $.ajax({
                        url: 'guardar_pago_producto.php',
                        method: 'POST',
                        data: datosPago,
                        success: function(response) {
                            $('#loadingProducto').hide();
                            $('#mensajeExitoProducto').show();

                            setTimeout(function() {
                                $('#mensajeExitoProducto').hide();
                                $('#simulacionProductoModal').fadeOut(300);
                                $('#productModal').fadeOut(300);
                                $('#btnAceptarTransferenciaProducto').prop('disabled', false);
                                location.reload();
                            }, 2000);
                        },
                        error: function() {
                            $('#loadingProducto').hide();
                            alert('Error al procesar el pago');
                            $('#btnAceptarTransferenciaProducto').prop('disabled', false);
                        }
                    });
                }, 2000);
            });
        }
    });


   

   
    // Cambiar texto del botón según método de pago (desayunos)
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
            const datos = {
                nombrePreventa: nombreDesayuno,
                cantidad_orden: cantidad,
                precioUnitarioPreventa: precio,
                precioTotalPreventa: total,
                metodoPago: 'efectivo'
            };

            // Mostrar indicador de carga
            $('#efectivoDesayunoModal').fadeIn(300);
            $('#loadingEfectivoDesayuno').show();
            $('#mensajeExitoEfectivoDesayuno').hide();

            // Simular un retraso de 3 segundos antes de enviar la solicitud
            setTimeout(function() {
                // Enviar datos al servidor
                
                $.ajax({
                    url: 'guardar_preventa_desayuno.php',
                    method: 'POST',
                    data: datos,
                    success: function(response) {
                        // Ocultar el indicador de carga
                        $('#loadingEfectivoDesayuno').hide();

                        // Mostrar el mensaje de éxito
                        $('#mensajeExitoEfectivoDesayuno').show();

                        // Ocultar el modal después de 2 segundos
                        setTimeout(function() {
                            $('#efectivoDesayunoModal').fadeOut(300);
                            $('#desayunoModal').fadeOut(300);
                            location.reload();
                        }, 2000);
                    },
                    error: function() {
                        // Ocultar el indicador de carga en caso de error
                        $('#loadingEfectivoDesayuno').hide();
                        alert('Error al guardar la solicitud de desayuno');
                        $('#efectivoDesayunoModal').fadeOut(300);
                    }
                });
            }, 2000);
        } else if ($('#formaPagoTransferenciaDesayuno').is(':checked')) {
            // Lógica para transferencia
            $('#simulacionDesayunoModal').fadeIn(300);
            $('#montoDesayuno').val(total.toFixed(2));

            // Preparar el procesamiento de la transferencia
            $('#formSimulacionDesayuno').off('submit').on('submit', function(e) {
                e.preventDefault();

                $('#loadingDesayuno').show();
                $('#btnAceptarTransferenciaDesayuno').prop('disabled', true);

                const fechaActual = new Date().toISOString().slice(0, 19).replace('T', ' ');

                // Datos para la tabla pagos
                const datosPago = {
                    nombrePagos: nombreDesayuno,
                    canTotalP: total,
                    fePago: fechaActual,
                    metodoPago: 'transferencia',
                    cantidad_orden: cantidad
                };

                // Simular procesamiento y enviar datos
                setTimeout(function() {
                    $.ajax({
                        url: 'guardar_pago_desayuno.php',
                        method: 'POST',
                        data: datosPago,
                        success: function(response) {
                            $('#loadingDesayuno').hide();
                            $('#mensajeExitoDesayuno').show();

                            setTimeout(function() {
                                $('#mensajeExitoDesayuno').hide();
                                $('#simulacionDesayunoModal').fadeOut(300);
                                $('#desayunoModal').fadeOut(300);
                                $('#btnAceptarTransferenciaDesayuno').prop('disabled', false);
                                location.reload();
                            }, 2000);
                        },
                        error: function() {
                            $('#loadingDesayuno').hide();
                            alert('Error al procesar el pago');
                            $('#btnAceptarTransferenciaDesayuno').prop('disabled', false);
                        }
                    });
                }, 3000);
            });
        }
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
                            <div class="card-product" data-description="${desayuno.descripcionDesayuno}" data-cantidad="${desayuno.cantidadDesayuno}">
                                <div class="container-img">
                                    <img src="uploads/${desayuno.imgDesayuno}" alt="${desayuno.nombreProducto}" />
                                </div>
                                <div class="content-card-product">
                                    <h3>${desayuno.nombreProducto}</h3>
                                    <span class="add-cart" data-type="desayuno">
                                        <i class="fa-solid fa-basket-shopping"></i>
                                    </span>
                                    <p class="price">$${desayuno.precioDesayuno}</p>
                                </div>
                            </div>
                        `);
                        console.log(desayuno.cantidadDesayuno);
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
                                        <span class="add-cart" data-type="almuerzo">
                                            <i class="fa-solid fa-basket-shopping"></i>
                                        </span>
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
    
    // Cambiar texto del botón según método de pago (almuerzos)
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
            $('#efectivoAlmuerzoModal').fadeIn(300);
            $('#loadingEfectivoAlmuerzo').show();
            $('#mensajeExitoEfectivoAlmuerzo').hide(); // Asegurarse de que el mensaje de éxito esté oculto

            // Simular un retraso de 3 segundos antes de enviar la solicitud
            setTimeout(function() {
                // Enviar datos al servidor
                $.ajax({
                    url: 'guardar_preventa.php',
                    method: 'POST',
                    data: datos,
                    success: function(response) {
                        // Ocultar el indicador de carga
                        $('#loadingEfectivoAlmuerzo').hide();

                        // Mostrar el mensaje de éxito
                        $('#mensajeExitoEfectivoAlmuerzo').show();

                        // Ocultar el modal después de 2 segundos
                        setTimeout(function() {
                            $('#efectivoAlmuerzoModal').fadeOut(300);
                            $('#almuerzoModal').fadeOut(300);
                            location.reload();
                        }, 2000);
                    },
                    error: function() {
                        // Ocultar el indicador de carga en caso de error
                        $('#loadingEfectivoAlmuerzo').hide();
                        alert('Error al guardar la solicitud de almuerzo');
                        $('#efectivoAlmuerzoModal').fadeOut(300);
                    }
                });
            }, 2000); // Simular un retraso de 3 segundos para el procesamiento
        } else if ($('#formaPagoTransferencia').is(':checked')) {
            // Lógica para transferencia (sin cambios)
            $('#simulacionAlmuerzoModal').fadeIn(300);
            $('#montoAlmuerzo').val(total.toFixed(2));

            // Preparar el procesamiento de la transferencia
            $('#formSimulacionAlmuerzo').off('submit').on('submit', function(e) {
                e.preventDefault();

                $('#loadingAlmuerzo').show();
                $('#btnAceptarTransferenciaAlmuerzo').prop('disabled', true);

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
                            $('#loadingAlmuerzo').hide();
                            $('#mensajeExitoAlmuerzo').show();

                            setTimeout(function() {
                                $('#mensajeExitoAlmuerzo').hide();
                                $('#simulacionAlmuerzoModal').fadeOut(300);
                                $('#almuerzoModal').fadeOut(300);
                                $('#btnAceptarTransferenciaAlmuerzo').prop('disabled', false);
                                location.reload();
                            }, 2000);
                        },
                        error: function() {
                            $('#loadingAlmuerzo').hide();
                            alert('Error al procesar el pago');
                            $('#btnAceptarTransferenciaAlmuerzo').prop('disabled', false);
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

    $(document).ready(function() {
    
        $('#miPerfil').on('click', function(e) {
            e.preventDefault();
            cargarDatosUsuario(); // Llama a obtener_usuario.php
            $('#userProfileModal').fadeIn(300);
        });

        // Cerrar el modal al hacer clic en la X
        $('.close-modalUser').on('click', function() {
            $('#userProfileModal').fadeOut(300);
        });

        // Cerrar el modal al hacer clic fuera de él
        $(window).on('click', function(event) {
            if (event.target === $('#userProfileModal')[0]) {
                $('#userProfileModal').fadeOut(300);
            }
        });

    
        function cargarDatosUsuario() {
            $.ajax({
                url: 'obtener_usuario.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const usuario = data.usuario;
                        $('#nombre').val(usuario.nbUsuario);
                        $('#apellido').val(usuario.apellidoUsuario);
                        $('#telefono').val(usuario.numTelefonoU);
                        $('#correo').val(usuario.email);
                    } else {
                        alert(data.message || 'Error al cargar los datos del usuario.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", error);
                    alert('Error al cargar los datos del usuario.');
                }
            });
        }

    
        $('#userProfileForm').on('submit', function(e) {
            e.preventDefault();

            const nuevaContrasena = $('#nuevaContrasena').val();
            const confirmarContrasena = $('#confirmarContrasena').val();

            if (nuevaContrasena !== confirmarContrasena) {
                alert('Las contraseñas no coinciden.');
                return;
            }

            const datos = {
                nombre: $('#nombre').val(),
                apellido: $('#apellido').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                nuevaContrasena: nuevaContrasena
            };

            console.log("Datos enviados:", datos); // Depuración

            $.ajax({
                url: 'actualizar_usuario.php',
                method: 'POST',
                data: datos,
                success: function(response) {
                    // Asegurarse de que la respuesta sea un objeto JSON
                    const jsonResponse = typeof response === "string" ? JSON.parse(response) : response;
                    
                    console.log("Respuesta del servidor:", jsonResponse); // Depuración
                    if (jsonResponse.success) {
                        console.log("Éxito en la actualización de datos.");
                        alert('Datos actualizados correctamente.');
                        $('#userProfileModal').fadeOut(300);

                        // Actualizar el nombre de usuario en la interfaz
                        $('#user-name').text(datos.nombre + ' ' + datos.apellido);
                    } else {
                        console.error("Error en la actualización de datos:", jsonResponse.message);
                        alert(jsonResponse.message || 'Error al actualizar los datos del usuario.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", error);
                    console.error("Detalles del error:", xhr.responseText);
                    alert('Error al actualizar los datos del usuario. Detalles: ' + xhr.responseText);
                }
            });
        });
    });    
});


// Codigo script para el buscador de productos
function filtrarProductos() {
        const input = document.getElementById('searchInput').value.toUpperCase();
        const productos = document.querySelectorAll('.card-product');
        const desayunos = document.querySelectorAll('#container-breakfasts .card-product');
        const almuerzos = document.querySelectorAll('#container-almuerzos .card-product');
        let resultadosEncontrados = false;

        // Filtrar productos
        productos.forEach(producto => {
            const nombre = producto.querySelector('h3').textContent.toUpperCase();
            if (nombre.startsWith(input)) {
                producto.style.display = '';
                resultadosEncontrados = true;
            } else {
                producto.style.display = 'none';
            }
        });

        // Filtrar desayunos
        desayunos.forEach(desayuno => {
            const nombre = desayuno.querySelector('h3').textContent.toUpperCase();
            if (nombre.startsWith(input)) {
                desayuno.style.display = '';
                resultadosEncontrados = true;
            } else {
                desayuno.style.display = 'none';
            }
        });

        // Filtrar almuerzos
        almuerzos.forEach(almuerzo => {
            const nombre = almuerzo.querySelector('h3').textContent.toUpperCase();
            if (nombre.startsWith(input)) {
                almuerzo.style.display = '';
                resultadosEncontrados = true;
            } else {
                almuerzo.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay resultados
        const mensajeNoEncontrado = document.getElementById('mensajeNoEncontrado');
        if (!resultadosEncontrados && input.length > 0) {
            if (!mensajeNoEncontrado) {
                const mensaje = document.createElement('p');
                mensaje.id = 'mensajeNoEncontrado';
                mensaje.textContent = 'Producto no encontrado';
                document.querySelector('.container-products').appendChild(mensaje);
            }
        } else {
            if (mensajeNoEncontrado) {
                mensajeNoEncontrado.remove();
            }
        }
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        filtrarProductos(); // Reaplicar el filtro para mostrar todos los productos
    }

    document.addEventListener('DOMContentLoaded', function () {
    // Selecciona el ícono del menú y el menú
    const mobileMenuIcon = document.getElementById('mobile-menu-icon');
    const mobileMenu = document.getElementById('mobile-menu');

    // Agrega un evento de clic al ícono
    mobileMenuIcon.addEventListener('click', function () {
        // Alterna la clase "active" en el menú
        mobileMenu.classList.toggle('active');
    });

    // Cierra el menú si se hace clic fuera de él
    document.addEventListener('click', function (event) {
        if (!mobileMenu.contains(event.target) && !mobileMenuIcon.contains(event.target)) {
            mobileMenu.classList.remove('active');
        }
    });
});
</script>
</body>
</html>