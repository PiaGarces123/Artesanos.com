<?php
function conexion() {
    $servidor = "localhost";
    $usuario = "root";
    $password = "1234";
    $bd = "artesanos_db";

    // Conexión en estilo procedural
    $conn = mysqli_connect($servidor, $usuario, $password, $bd);

    // Verificación de la conexión
    if (!$conn) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    return $conn;
}

function desconexion($conexion){
    if ($conexion) {
        mysqli_close($conexion);
    }
}
?>

