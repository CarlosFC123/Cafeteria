<?php
// Definir el mensaje de bienvenida
$mensaje_bienvenida = "Bienvenido Administrador"; // Este mensaje puede ser dinámico
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #3b82f6 0%, #60a5fa 100%);
            padding: 20px;
            height: 100vh;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar h4 {
            color: #ffffff;
            font-size: 1.5rem;
            padding: 15px 10px;
            border-bottom: 2px solid #bfdbfe;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar a:not(.submenu-toggle) {
            display: flex;
            align-items: center;
        }

        .sidebar a:not(.submenu-toggle)::before {
            content: '\f105';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 10px;
            font-size: 14px;
        }

        .sidebar a:hover {
            background-color: #93c5fd;
            transform: translateX(5px);
        }

        .submenu-toggle {
            position: relative;
        }

        .submenu-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            transition: transform 0.3s ease;
        }

        .submenu-toggle.active::after {
            transform: rotate(180deg);
        }

        .submenu {
            display: none;
            padding-left: 15px;
            margin-top: 5px;
            border-left: 2px solid #bfdbfe;
        }

        .submenu a {
            font-size: 0.95em;
            padding: 10px 15px;
            color: #f0f9ff;
        }

        .submenu a:hover {
            color: #ffffff;
            background-color: #93c5fd;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .text-danger {
            color: #000000 !important; /* Cambiado a negro */
        }

        .text-danger:hover {
            background-color: #ef4444 !important;
            color: white !important;
        }

        /* Animación para el submenu */
        .submenu {
            transition: all 0.3s ease-in-out;
            opacity: 0;
            transform: translateY(-10px);
        }

        .submenu.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scrollbar personalizado */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #60a5fa;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #bfdbfe;
            border-radius: 3px;
        }

        /* Estilo para el mensaje de bienvenida en la barra lateral */
        .welcome-message {
            font-size: 0.85rem; /* Tamaño de letra más pequeño */
            color: #f0f9ff; /* Color de texto claro */
            margin-top: 5px; /* Espacio superior */
            margin-bottom: 10px; /* Espacio inferior */
            padding-bottom: 5px; /* Espacio adicional abajo */
            border-bottom: 1px solid #bfdbfe; /* Línea decorativa */
            text-align: center; /* Centrado */
        }

        .hamburger-menu {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }

            .sidebar a {
                font-size: 14px;
                padding: 10px 12px;
            }

            .sidebar a:not(.submenu-toggle)::before {
                content: '\f105';  /* Icono de FontAwesome para el menú */
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                margin-right: 8px;
                font-size: 16px;
            }

            .submenu {
                display: none;
            }

            .submenu.show {
                display: block;
            }

            .sidebar {
                position: absolute;
                z-index: 1000;
                top: 0;
                left: -250px;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .hamburger-menu {
                display: block;
                font-size: 24px;
                cursor: pointer;
                position: absolute;
                top: 20px;
                left: 20px;
                z-index: 1001;
            }
        }
    </style>
</head>
<body>
<div class="hamburger-menu" onclick="toggleSidebar()">☰</div>
    <div class="sidebar">
        <h4>Menú</h4>

        <!-- Mostrar el mensaje de bienvenida solo si la variable PHP está definida -->
        <?php if (isset($mensaje_bienvenida)) { ?>
            <div class="welcome-message">
                <p><?php echo $mensaje_bienvenida; ?></p>
            </div>
        <?php } ?>

        <a href="menu.php">Inicio</a>
        <a href="admin_dashboard.php">Usuarios</a>
        <a href="ordenes.php">Ordenes</a>
        <a href="#" class="submenu-toggle" onclick="toggleSubmenu(event)">Productos</a>
        <div class="submenu">
            <a href="productos.php">Productos</a>
            <a href="proveedores.php">Proveedores</a>
            <a href="productos_proveedores.php">Productos_Proveedores</a>
            <a href="productos_baja.php">Productos_Baja</a>
        </div>
        <a href="#" class="submenu-toggle" onclick="toggleSubmenu(event)">Categorías</a>
        <div class="submenu">
            <a href="categorias.php">Categorías</a>
            <a href="tipo_categorias.php">Tipo Categorías</a>
        </div>

        <a href="menu_semanal.php">Menu Semanal</a>
        <a href="#" class="submenu-toggle" onclick="toggleSubmenu(event)">Comida</a>
        <div class="submenu">
            <a href="desayuno.php">Desayuno</a>
            <a href="almuerzo.php">Almuerzo</a>
        </div>
        <a href="inventario.php">Inventario</a>
        <a href="#" class="submenu-toggle" onclick="toggleSubmenu(event)">Ganancias</a>
        <div class="submenu">
            <a href="pagos.php">Pagos-ventas</a>
            <a href="preventa.php">Preventa</a>
            <a href="ganancias.php">Ganancias</a>
            <a href="corte_caja.php">Corte de Caja</a>
        </div>
        <a href="logout.php" style="color: #000000;">Cerrar Sesión</a>
    </div>

    <script>
        function toggleSubmenu(event) {
            event.preventDefault();
            const submenuToggle = event.target;
            submenuToggle.classList.toggle('active');
            const submenu = submenuToggle.nextElementSibling;
            
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                submenu.classList.remove('show');
            } else {
                submenu.style.display = 'block';
                setTimeout(() => {
                    submenu.classList.add('show');
                }, 10);
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html>
