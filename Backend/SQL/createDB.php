<?php
    //CONECTARSE AL SERVIDOR
    $servername = "localhost";
    $username = "root"; 
    $password = "";     

    // Crear conexión
    $conn = new mysqli($servername, $username, $password);

    // Verificar conexión
    if ($conn->connect_error) { //Si hubo un error
        die("Error de conexión: " . $conn->connect_error);
    }
   
    //Si se conectó exitosamente
    //CREAR LA BASE DE DATOS
    $sql = "CREATE DATABASE IF NOT EXISTS artesanos_db";
    if ($conn->query($sql) === TRUE) {
        echo "Base de datos creada correctamente.<br>";
        $conn->select_db("artesanos_db"); // <-- Empezar a trabajar en la bd
    } else {
        die("Error al crear la base de datos: " . $conn->error);
    }
    

    //Verificar si se creó correctamente

    //Si salió bien
    if($conn->query($sql) === TRUE){

        //--------------------------CREAR LA TABLA USUARIO--------------------------
        /* PARA ACTUALIZAR EL ESTADO: 'UPDATE usuarios SET estado = 1 WHERE id_usuario = 5;' */
        //status      0=activo, 1=bloqueado
        //fecha de registro es automática      
        $sqlTable= "CREATE TABLE users(
            U_id INT AUTO_INCREMENT PRIMARY KEY,
            U_nameUser VARCHAR(20) UNIQUE,
            U_name VARCHAR(100), 
            U_lastName VARCHAR(100),
            U_email VARCHAR(100),
            U_pass VARCHAR(100),
            U_dateBirth DATE,
            U_registrationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
            U_status TINYINT(1) DEFAULT 0 ,
            U_biography VARCHAR(100)
        )";
        
        //Si salió bien
        if($conn->query($sqlTable) === TRUE){

            //--------------------------CREAR LA TABLA ALBUM--------------------------
            $sqlTable= "CREATE TABLE albums(
                A_id INT AUTO_INCREMENT PRIMARY KEY,
                A_title VARCHAR(30),
                A_creationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                A_idUser INT,
                FOREIGN KEY (A_idUser) REFERENCES users(U_id)
            )";
            //Si salió bien
            if($conn->query($sqlTable) === TRUE){

                //--------------------------CREAR LA TABLA IMAGEN--------------------------
                //visibility       0=publica, 1=privada 
                //I_isProfile       0=no, 1=si 
                //I_currentProfile       0=no, 1=si 
                //I_revisionStatus       0=no, 1=si 
                //fecha de registro es automática  
                // El UNIQUE es para que no tenga más de una imagen de perfil activa
                $sqlTable= "CREATE TABLE images(
                    I_id INT AUTO_INCREMENT PRIMARY KEY,
                    I_title VARCHAR(30),
                    I_visibility TINYINT(1) DEFAULT 0, 
                    I_isProfile TINYINT(1) DEFAULT 0, 
                    I_currentProfile TINYINT(1) DEFAULT 0, 
                    I_revisionStatus TINYINT(1) DEFAULT 0, 
                    I_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                    I_idAlbum INT,
                    I_idUser INT,
                    FOREIGN KEY (I_idUser) REFERENCES users(U_id),
                    FOREIGN KEY (I_idAlbum) REFERENCES albums(A_id)
                )";

                //Si salió bien
                if($conn->query($sqlTable) === TRUE){

                    //--------------------------CREAR LA TABLA COMENTARIO--------------------------
                    $sqlTable= "CREATE TABLE comments(
                        C_id INT AUTO_INCREMENT PRIMARY KEY,
                        C_content VARCHAR(100),
                        C_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                        C_idImage INT,
                        C_idUser INT,
                        FOREIGN KEY (C_idUser) REFERENCES users(U_id),
                        FOREIGN KEY (C_idImage) REFERENCES images(I_id)
                    )";

                    //Si salió bien
                    if($conn->query($sqlTable) === TRUE){

                        //--------------------------CREAR LA TABLA LIKE--------------------------
                        $sqlTable= "CREATE TABLE likes(
                            L_id INT AUTO_INCREMENT PRIMARY KEY,
                            L_publicationDate DATETIME DEFAULT CURRENT_TIMESTAMP, 
                            L_idImage INT,
                            L_idUser INT,
                            FOREIGN KEY (L_idUser) REFERENCES users(U_id),
                            FOREIGN KEY (L_idImage) REFERENCES images(I_id),
                            UNIQUE (L_idUser, L_idImage)
                        )";

                        //Si salió bien
                        if($conn->query($sqlTable) === TRUE){

                            //--------------------------CREAR LA TABLA DENUNCIA--------------------------
                            //I_currentProfile       0=pendiente, 1=resuelta, -1=rechazada
                            $sqlTable= "CREATE TABLE complaints(
                                D_id INT AUTO_INCREMENT PRIMARY KEY,
                                D_complaintDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                                D_reason VARCHAR(100), 
                                D_status TINYINT DEFAULT 0,  
                                D_idImage INT,
                                D_idUser INT,
                                FOREIGN KEY (D_idUser) REFERENCES users(U_id),
                                FOREIGN KEY (D_idImage) REFERENCES images(I_id)
                            )";

                            //Si salió bien
                            if($conn->query($sqlTable) === TRUE){

                                //--------------------------CREAR LA TABLA SEGUIMIENTO--------------------------
                                //I_currentProfile       0=pendiente, 1=aceptado, -1=rechazado
                                $sqlTable= "CREATE TABLE follow(
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

                                //Si salió bien
                                if($conn->query($sqlTable) === TRUE){

                                    echo "✅ Todas las tablas fueron creadas correctamente.";

                                }//Si hubo un error al crear la tabla Follows
                                else{
                                    echo "Hubo un error al crear la Tabla Follows :( <br>";
                                    echo "Error: " . $conn->error;
                                }

                            }//Si hubo un error al crear la tabla Denuncias
                            else{
                                echo "Hubo un error al crear la Tabla Denuncias :( <br>";
                                echo "Error: " . $conn->error;
                            }

                        }//Si hubo un error al crear la tabla Likes
                        else{
                            echo "Hubo un error al crear la Tabla Likes :( <br>";
                            echo "Error: " . $conn->error;
                        }

                    }//Si hubo un error al crear la tabla Comentarios
                    else{
                        echo "Hubo un error al crear la Tabla Comentarios :( <br>";
                        echo "Error: " . $conn->error;
                    }

                }//Si hubo un error al crear la tabla Imagenes
                else{
                    echo "Hubo un error al crear la Tabla Imagenes :( <br>";
                    echo "Error: " . $conn->error;
                }

            }//Si hubo un error al crear la tabla Albumes
            else{
                echo "Hubo un error al crear la Tabla Albumes :( <br>";
                echo "Error: " . $conn->error;
            }

        }//Si hubo un error al crear la tabla Usuarios
        else{
            echo "Hubo un error al crear la Tabla Usuarios :( <br>";
            echo "Error: " . $conn->error;
        }


    }
    //Si hubo un error al crear la base de datos
    else{
        echo "Hubo un error al crear la Base de Datos :( <br>";
        echo "Error: " . $conn->error;
    }



    //Cerrar Conexión
    $conn->close();




?>