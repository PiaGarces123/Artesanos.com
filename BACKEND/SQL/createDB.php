<?php
    // Datos de conexión
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password);

    // Verificar conexión
    if ($conn->connect_error) {
        die("❌ Error de conexión: " . $conn->connect_error);
    }

    // Crear la base de datos
    $sql = "CREATE DATABASE IF NOT EXISTS artesanos_db";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Base de datos creada o verificada correctamente.<br>";
        $conn->select_db("artesanos_db");
    } else {
        die("❌ Error al crear la base de datos: " . $conn->error);
    }

    //-------------------------- CREAR TABLA USERS --------------------------
    $sqlTable = "CREATE TABLE users(
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
    if ($conn->query($sqlTable) === TRUE) {

        //-------------------------- CREAR TABLA ALBUMS (CON LÓGICA DE SISTEMA) --------------------------
        $sqlTable = "CREATE TABLE albums(
            A_id INT AUTO_INCREMENT PRIMARY KEY,
            A_title VARCHAR(30),
            A_creationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
            A_idUser INT NOT NULL,

            -- 1. Bandera para identificar álbumes del sistema (Likes, etc.)
            A_isSystemAlbum TINYINT(1) DEFAULT 0, 

            -- 2. FK Opcional para el Usuario Seguido (Solo se usa si A_isSystemAlbum = 1)
            A_idFollowedUser INT DEFAULT NULL, 

            -- Restricciones de Clave Foránea
            FOREIGN KEY (A_idUser) REFERENCES users(U_id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            
            FOREIGN KEY (A_idFollowedUser) REFERENCES users(U_id)
                ON DELETE CASCADE ON UPDATE CASCADE,

            -- Restricción para asegurar que solo haya UN álbum de sistema por par (A, B)
            UNIQUE (A_idUser, A_idFollowedUser) 
        )";
        if ($conn->query($sqlTable) === TRUE) {

            //-------------------------- CREAR TABLA IMAGES (AÑADIDO I_isCover, I_ruta NO ES UNIQUE) --------------------------
            $sqlTable = "CREATE TABLE images(
                I_id INT AUTO_INCREMENT PRIMARY KEY,
                I_title VARCHAR(30),
                I_visibility TINYINT(1) DEFAULT 0, 
                I_isProfile TINYINT(1) DEFAULT 0, 
                I_currentProfile TINYINT(1) DEFAULT 0, 
                I_revisionStatus TINYINT(1) DEFAULT 0, 
                I_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                I_ruta VARCHAR(100) UNIQUE,
                I_idAlbum INT DEFAULT NULL,
                I_idUser INT NOT NULL,
                I_isCover TINYINT(1) DEFAULT 0,
                FOREIGN KEY (I_idUser) REFERENCES users(U_id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (I_idAlbum) REFERENCES albums(A_id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            )";
            if ($conn->query($sqlTable) === TRUE) {

                echo "✔️ Tabla IMAGES creada con I_isCover.<br>";
                
                // -------------------------- TABLA PIVOTE album_images_link (NUEVA) --------------------------
                $sqlTable = "CREATE TABLE album_images_link (
                    L_id INT AUTO_INCREMENT PRIMARY KEY,
                    L_idAlbum INT NOT NULL,
                    L_idImage INT NOT NULL,

                    UNIQUE (L_idAlbum, L_idImage),
                    
                    FOREIGN KEY (L_idAlbum) REFERENCES albums(A_id)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                        
                    FOREIGN KEY (L_idImage) REFERENCES images(I_id)
                        ON DELETE CASCADE ON UPDATE CASCADE
                )";
                if ($conn->query($sqlTable) === TRUE) {
                    echo "✔️ Tabla album_images_link (Colección de Álbumes) creada correctamente.<br>";

                    //-------------------------- TABLA COMMENTS --------------------------
                    $sqlTable = "CREATE TABLE comments(
                        C_id INT AUTO_INCREMENT PRIMARY KEY,
                        C_content VARCHAR(255),
                        C_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                        C_idImage INT NOT NULL,
                        C_idUser INT NOT NULL,
                        FOREIGN KEY (C_idUser) REFERENCES users(U_id)
                            ON DELETE CASCADE ON UPDATE CASCADE,
                        FOREIGN KEY (C_idImage) REFERENCES images(I_id)
                            ON DELETE CASCADE ON UPDATE CASCADE
                    )";
                    if ($conn->query($sqlTable) === TRUE) {

                        //-------------------------- TABLA LIKES --------------------------
                        $sqlTable = "CREATE TABLE likes(
                            L_id INT AUTO_INCREMENT PRIMARY KEY,
                            L_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                            L_idImage INT NOT NULL,
                            L_idUser INT NOT NULL,
                            FOREIGN KEY (L_idUser) REFERENCES users(U_id)
                                ON DELETE CASCADE ON UPDATE CASCADE,
                            FOREIGN KEY (L_idImage) REFERENCES images(I_id)
                                ON DELETE CASCADE ON UPDATE CASCADE,
                            UNIQUE (L_idUser, L_idImage)
                        )";
                        if ($conn->query($sqlTable) === TRUE) {

                            //-------------------------- TABLA COMPLAINTS (QUEJAS) --------------------------
                            $sqlTable = "CREATE TABLE complaints(
                                D_id INT AUTO_INCREMENT PRIMARY KEY,
                                D_complaintDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                                D_reason VARCHAR(255), 
                                D_status TINYINT DEFAULT 0,
                                D_idImage INT NOT NULL,
                                D_idUser INT NOT NULL,
                                FOREIGN KEY (D_idUser) REFERENCES users(U_id)
                                    ON DELETE CASCADE ON UPDATE CASCADE,
                                FOREIGN KEY (D_idImage) REFERENCES images(I_id)
                                    ON DELETE CASCADE ON UPDATE CASCADE
                            )";
                            if ($conn->query($sqlTable) === TRUE) {

                                //-------------------------- TABLA FOLLOW --------------------------
                                $sqlTable = "CREATE TABLE follow(
                                    F_id INT AUTO_INCREMENT PRIMARY KEY,
                                    F_status TINYINT DEFAULT 0,
                                    F_followDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    F_resolutionDate DATETIME,
                                    F_idFollower INT NOT NULL,
                                    F_idFollowed INT NOT NULL,
                                    FOREIGN KEY (F_idFollower) REFERENCES users(U_id)
                                        ON DELETE CASCADE ON UPDATE CASCADE,
                                    FOREIGN KEY (F_idFollowed) REFERENCES users(U_id)
                                        ON DELETE CASCADE ON UPDATE CASCADE,
                                    UNIQUE (F_idFollower, F_idFollowed)
                                )";
                                if ($conn->query($sqlTable) === TRUE) {
                                    echo "🎉 Todas las tablas fueron creadas correctamente con CASCADE.<br>";
                                } else {
                                    echo "❌ Error al crear tabla Follow: " . $conn->error . "<br>";
                                }

                            } else {
                                echo "❌ Error al crear tabla Complaints: " . $conn->error . "<br>";
                            }

                        } else {
                            echo "❌ Error al crear tabla Likes: " . $conn->error . "<br>";
                        }

                    } else {
                        echo "❌ Error al crear tabla Comments: " . $conn->error . "<br>";
                    }
                    
                } else {
                    echo "❌ Error al crear tabla album_images_link: " . $conn->error . "<br>";
                }

            } else {
                echo "❌ Error al crear tabla Images: " . $conn->error . "<br>";
            }

        } else {
            echo "❌ Error al crear tabla Albums: " . $conn->error . "<br>";
        }

    } else {
        echo "❌ Error al crear tabla Users: " . $conn->error . "<br>";
    }

    // Cerrar conexión
    $conn->close();
?>