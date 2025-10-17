<?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']); // TRUE si hay sesión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artesanos</title>
    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="./Frontend/assets/css/styles.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    
    <?php

    //Incluir Header
    include("./Frontend/includes/header.php");

    ?>


    <!-- Publicar Contenido Modal -->
    <div class="modal" id="createAlbumModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Publicar Contenido</h2>
                <button class="close-modal" id="closeCreateModal">&times;</button>
            </div>
            
            <form id="createAlbumForm">                
                <div class="form-group">
                    <label class="form-label">Subir imágenes</label>
                    <div class="file-upload" id="fileUpload">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Arrastra imágenes aquí o haz clic para seleccionar</div>
                        <div class="upload-hint">PNG, JPG hasta 5MB cada una</div>
                    </div>
                    <input type="file" id="imageInput" multiple accept="image/*">
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 32px;">
                    <button type="button" class="action-button btn-secondary" id="cancelCreate" style="flex: 1;">Cancelar</button>
                    <button type="submit" class="action-button btn-primary" style="flex: 1;">Continuar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- My Albums Modal -->
    <div class="modal" id="myAlbumsModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title">Mis álbumes</h2>
                <button class="close-modal" id="closeAlbumsModal">&times;</button>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 24px;" id="albumsGrid">
                <!-- Los álbumes se cargarán aquí -->
            </div>
        </div>
    </div>

    <!-- favorites modal -->
    <div class="modal" id="favoritesModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Mis Favoritos</h2>
                <button class="close-modal">&times;</button>
            </div>
            <!-- Contenido de favoritos aquí -->
        </div>
    </div>


    <!-- Contenedor vacío para cargar el modal -->
    <div id="modalContainer">
        <div class="modal" id="loginModal">
            <div class="modalLogin-content">

                <div class="sectionLogin">
                    <div class="containerLogin">
                        <div class="row full-height justify-content-center">
                            <div class="col-12 text-center align-self-center py-5">
                                <div class="section pb-5 pt-5 text-center">
                                    <h6 class="mb-0 pb-3">
                                        <span id="btn-login">Log In </span>
                                        <span id="btn-signup">Sign Up</span>
                                    </h6>

                                    <input class="checkbox" type="checkbox" id="reg-log" name="reg-log" title="Toggle between Log In and Sign Up"/>
                                    <label for="reg-log"></label>

                                    <div class="card-3d-wrap mx-auto">
                                        <div class="card-3d-wrapper">
                                            <button class="closeLoginModal" id="closeLoginModal">&times;</button>


                                            <!-- LOGIN -->
                                            <div class="card-front">
                                                <div class="center-wrap">                                                            
                                                    <div class="logo">
                                                        <img src="./Frontend/assets/images/appImages/logo.png" alt="logo">
                                                    </div>
                                                    <div class="section text-center">
                                                        <h4 class="mb-4 pb-3">LOG IN</h4>

                                                        <!-- Email -->
                                                        <div class="form-groupLogin">
                                                            <input type="email" class="form-style" placeholder="Email" name="mail"  pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" required>
                                                            <i class="input-icon uil uil-at"></i>
                                                        </div>                                                        
                                                        <div class="error" id="errorEmailLogin">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <!-- Contraseña -->
                                                        <div class="form-groupLogin mt-2">
                                                            <input type="password" class="form-style" placeholder="Contraseña" name="pass" minlength="6" required>
                                                            <i class="toggle-pass uil uil-eye"></i> <!-- ojo  -->
                                                            <i class="input-icon uil uil-lock-alt"></i>
                                                        </div>
                                                        <div class="error" id="errorPassLogin">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <a href="#" class="btnLogin mt-4" role="button" >Login</a>
                                                        <p class="mb-0 mt-4 text-center">
                                                            <a href="#" class="linkLogin">¿Olvidaste tu contraseña?</a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SIGNUP -->
                                            <div class="card-back">
                                                <div class="center-wrap">
                                                    <div class="section text-center">
                                                        <h4 class="mb-3 pb-3">SIGN UP</h4>

                                                        <!-- Fecha de Nacimiento -->
                                                        <div class="form-groupLogin fNacSignUp">
                                                            <input type="date" class="form-style flatpickr-input" placeholder="Fecha de Nacimiento" name="fNac" id="fNac" required>
                                                            <i class="input-icon uil uil-calendar-alt"></i>
                                                        </div>
                                                        <div class="error" id="errorFnac">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <!-- Nombre -->
                                                        <div class="form-groupLogin">
                                                            <input type="text" class="form-style" placeholder="Nombre" name="nbre" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}" required>
                                                            <i class="input-icon uil uil-user"></i>
                                                        </div>
                                                        <div class="error" id="errorNbre">
                                                            <!-- Se genera con js -->
                                                        </div>
                                                        
                                                        <!-- Apellido -->   
                                                        <div class="form-groupLogin">
                                                            <input type="text" class="form-style" placeholder="Apellido" name="ape"  pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}" required>
                                                            <i class="input-icon uil uil-user"></i>
                                                        </div>
                                                        <div class="error" id="errorApe">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <!-- Usuario -->
                                                        <div class="form-groupLogin mt-2">
                                                            <input type="text" class="form-style" placeholder="Nombre de Usuario" name="userName" minlength="4" maxlength="20" required >
                                                            <i class="input-icon uil uil-user-circle"></i>
                                                        </div>
                                                        <div class="error" id="errorUser">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <!-- Email -->
                                                        <div class="form-groupLogin mt-2">
                                                            <input type="email" class="form-style" placeholder="Email" name="mail"  pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" required>
                                                            <i class="input-icon uil uil-at"></i>
                                                        </div>
                                                        <div class="error" id="errorEmailSignUp">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <!-- Contraseña -->
                                                        <div class="form-groupLogin mt-2">
                                                            <input type="password" class="form-style" placeholder="Contraseña" name="pass" minlength="6" required>
                                                            <i class="toggle-pass uil uil-eye"></i> <!-- ojo  -->
                                                            <i class="input-icon uil uil-lock-alt"></i>
                                                        </div>
                                                        <div class="error" id="errorPassSignUp">
                                                            <!-- Se genera con js -->
                                                        </div>

                                                        <a href="#" class="btnLogin mt-4" role="button">Registrarse</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- card-3d-wrap -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</body>
<!-- Link al archivo JS -->
<script>
    // Variable global JS que indica si el usuario inició sesión
    window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>
<script src="./Frontend/assets/js/actionNormal.js"></script>
<script src="./Frontend/assets/js/modal.js"></script>
<script src="./Frontend/assets/js/restrictedActions.js"></script>
</html>