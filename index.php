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
    include("./Frontend/includes/modals.php");
    ?>


   
    
</body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Para Fecha de Nacimiento -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Variable global JS que indica si el usuario inició sesión
    window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>

<!--Scripts Personalizados -->

<!--Scripts Básicos o normales(cerrar sesion, buscar) -->
<script src="./Frontend/assets/js/actionNormal.js"></script>

<!--Scripts Básicos de Modales -->
<script src="./Frontend/assets/js/modal.js"></script>

<!--Scripts para Restringir Acciones dependiendo de si Inició sesion o no -->
<script src="./Frontend/assets/js/restrictedActions.js"></script>


<!--Scripts Para publicar Contenido, muestra imagenes seleccionadas -->
<script src="./Frontend/assets/js/modalSelectImages.js"></script>

<!--Opcion de Crear o Seleccionar Album -->
<script src="./Frontend/assets/js/modalOptionAlbum.js"></script>

<!-- Para trabajar los albumes -->
<script src="./Frontend/assets/js/myAlbumsModal.js"></script>

