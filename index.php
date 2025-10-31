<?php
    //Incluir los archivos necesarios
    require_once "./BACKEND/Clases/Image.php"; 
    require_once "./BACKEND/conexion.php"; 

    //Comprobar sesion
    $conn = conexion();
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']); // TRUE si hay sesión
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Artesanos</title>
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

        <!-- Estilos Personalizados -->
        <link rel="stylesheet" href="./Frontend/assets/css/styles.css" />
    </head>
    <body>
        <!-- Incluir el Navbar y Sidebar -->
        <?php
            include("./Frontend/includes/header.php");
        ?>

        <!-- CONTENEDOR PRINCIPAL DE CONTENIDO -->
        <main class="main-content-offset">
            <div class="container-fluid">
                <!-- Contenedor de resultados con grid Masonry -->
                <div id="searchResultsContainer" class="feed-grid">
                    <!-- El feed se cargará automáticamente aquí vía JS -->
                </div>
            </div>
        </main>

        <script>
            // Variable global JS que indica si el usuario inició sesión
            window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        </script>
        
        <!-- MODALES -->
        <?php
            include("./Frontend/includes/modals.php");
        ?>



    <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        
        <!-- Masonry (para el feed de imágenes) -->
        <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

        

        <!-- Sistema de feed (carga imágenes con Masonry) -->
        <script src="./Frontend/assets/js/feedLoader.js"></script>

        <!-- Orquestador general (logout) -->
        <script src="./Frontend/assets/js/actionNormal.js"></script>

        
    </body>
</html>



