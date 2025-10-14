<?php
// Conectarse al servidor
$servername = "localhost";
$username = "root";
$password = "";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS artesanos_db";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada correctamente.<br>";
    $conn->select_db("artesanos_db");
} else {
    die("Error al crear la base de datos: " . $conn->error);
}

// ---------------------- TABLA USERS ----------------------
$sql = "CREATE TABLE IF NOT EXISTS users(
    U_id INT AUTO_INCREMENT PRIMARY KEY,
    U_nameUser VARCHAR(20) UNIQUE,
    U_name VARCHAR(100),
    U_lastName VARCHAR(100),
    U_email VARCHAR(100) UNIQUE,
    U_pass VARCHAR(100),
    U_dateBirth DATE,
    U_registrationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    U_status TINYINT(1) DEFAULT 0,
    U_biography VARCHAR(100)
)";
if (!$conn->query($sql)) die("Error tabla USERS: " . $conn->error);

// ---------------------- TABLA IMAGES ----------------------
$sql = "CREATE TABLE IF NOT EXISTS images(
    I_id INT AUTO_INCREMENT PRIMARY KEY,
    I_title VARCHAR(30),
    I_visibility TINYINT(1) DEFAULT 0,
    I_isProfile TINYINT(1) DEFAULT 0,
    I_currentProfile TINYINT(1) DEFAULT 0,
    I_revisionStatus TINYINT(1) DEFAULT 0,
    I_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    I_idAlbum INT DEFAULT NULL,
    I_idUser INT,
    FOREIGN KEY (I_idUser) REFERENCES users(U_id)
)";
if (!$conn->query($sql)) die("Error tabla IMAGES: " . $conn->error);

// ---------------------- TABLA ALBUMS ----------------------
$sql = "CREATE TABLE IF NOT EXISTS albums(
    A_id INT AUTO_INCREMENT PRIMARY KEY,
    A_title VARCHAR(30),
    A_creationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    A_idUser INT,
    A_idPortada INT DEFAULT NULL,
    FOREIGN KEY (A_idPortada) REFERENCES images(I_id),
    FOREIGN KEY (A_idUser) REFERENCES users(U_id)
)";
if (!$conn->query($sql)) die("Error tabla ALBUMS: " . $conn->error);

// ---------------------- TABLA COMMENTS ----------------------
$sql = "CREATE TABLE IF NOT EXISTS comments(
    C_id INT AUTO_INCREMENT PRIMARY KEY,
    C_content VARCHAR(100),
    C_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    C_idImage INT,
    C_idUser INT,
    FOREIGN KEY (C_idUser) REFERENCES users(U_id),
    FOREIGN KEY (C_idImage) REFERENCES images(I_id)
)";
if (!$conn->query($sql)) die("Error tabla COMMENTS: " . $conn->error);

// ---------------------- TABLA LIKES ----------------------
$sql = "CREATE TABLE IF NOT EXISTS likes(
    L_id INT AUTO_INCREMENT PRIMARY KEY,
    L_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    L_idImage INT,
    L_idUser INT,
    FOREIGN KEY (L_idUser) REFERENCES users(U_id),
    FOREIGN KEY (L_idImage) REFERENCES images(I_id),
    UNIQUE (L_idUser, L_idImage)
)";
if (!$conn->query($sql)) die("Error tabla LIKES: " . $conn->error);

// ---------------------- TABLA COMPLAINTS ----------------------
$sql = "CREATE TABLE IF NOT EXISTS complaints(
    D_id INT AUTO_INCREMENT PRIMARY KEY,
    D_complaintDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    D_reason VARCHAR(100),
    D_status TINYINT DEFAULT 0,
    D_idImage INT,
    D_idUser INT,
    FOREIGN KEY (D_idUser) REFERENCES users(U_id),
    FOREIGN KEY (D_idImage) REFERENCES images(I_id)
)";
if (!$conn->query($sql)) die("Error tabla COMPLAINTS: " . $conn->error);

// ---------------------- TABLA FOLLOW ----------------------
$sql = "CREATE TABLE IF NOT EXISTS follow(
    F_id INT AUTO_INCREMENT PRIMARY KEY,
    F_status TINYINT DEFAULT 0,
    F_followDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    F_resolutionDate DATETIME,
    F_idFollower INT,
    F_idFollowed INT,
    FOREIGN KEY (F_idFollower) REFERENCES users(U_id),
    FOREIGN KEY (F_idFollowed) REFERENCES users(U_id),
    UNIQUE (F_idFollower, F_idFollowed)
)";
if (!$conn->query($sql)) die("Error tabla FOLLOW: " . $conn->error);

echo "✅ Todas las tablas fueron creadas correctamente.";

// Cerrar conexión
$conn->close();
?>
