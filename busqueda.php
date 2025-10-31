<?php
require_once "./BACKEND/Clases/Image.php";
require_once "./BACKEND/Clases/User.php"; 
require_once "./BACKEND/conexion.php"; 

$conn = conexion();
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$isLoggedIn = true;

// Obtener parámetros de búsqueda de la URL
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchType = isset($_GET['type']) ? $_GET['type'] : 'perfil';

// Validar tipo de búsqueda
if (!in_array($searchType, ['perfil', 'imagen'])) {
    $searchType = 'perfil';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda - Artesanos</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="./Frontend/assets/css/styles.css">
</head>
<body>
    
    <?php include("./Frontend/includes/header.php"); ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content-offset">
        <div class="container-fluid">
            
            <!-- ============================================ -->
            <!-- ENCABEZADO DE LA PÁGINA DE BÚSQUEDA -->
            <!-- ============================================ -->
            <div class="search-page-header mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <a href="./index.php" class="btn volver-btn">
                        <i class="uil uil-arrow-left me-1"></i> Volver al Feed
                    </a>
                    <h5 class="mb-0">
                        <i class="uil uil-search me-2"></i>
                        <?php if ($searchQuery): ?>
                            Resultados para "<?= htmlspecialchars($searchQuery) ?>"
                        <?php else: ?>
                            Búsqueda
                        <?php endif; ?>
                    </h5.buscarPor-btn>
                </div>                
            </div>

            <!-- ============================================ -->
            <!-- CONTENEDOR DE RESULTADOS -->
            <!-- ============================================ -->
            <div id="searchResultsContainer" class="search-results-area">
                <?php if (!$searchQuery): ?>
                    <!-- Mensaje inicial si no hay búsqueda -->
                    <div class="text-center mt-5 text-secondary">
                        <i class="uil uil-search-alt fs-1"></i>
                        <p class="mt-3 fs-5">Escribe algo para comenzar a buscar</p>
                        <p class="text-muted">Encuentra perfiles de artesanos o busca imágenes por título</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- MODALES -->
    <?php require_once("./Frontend/includes/modals.php"); ?>


    <!-- ============================================ -->
    <!-- SCRIPTS -->
    <!-- ============================================ -->

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Masonry (para grid de imágenes) -->
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

    <!-- Variables globales -->
    <script>
        window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        window.initialSearchQuery = <?= json_encode($searchQuery) ?>;
        window.initialSearchType = <?= json_encode($searchType) ?>;
    </script>

    <!-- Sistema de búsqueda -->
    <script src="./Frontend/assets/js/busquedaPage.js"></script>
    
    <!-- Sistema de búsqueda desde header (redirecciona a busqueda.php) -->
    <script src="./Frontend/assets/js/headerSearch.js"></script>

    <!-- Orquestador general (logout) -->
    <script src="./Frontend/assets/js/actionNormal.js"></script>


</body>
</html>