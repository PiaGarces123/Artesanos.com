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


    <!----------------- MODAL PARA PUBLICAR CONTENIDO ----------------->
    <div class="modal fade" id="createAlbumModal" tabindex="-1" aria-labelledby="createAlbumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <!-- Titulo -->
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="createAlbumModalLabel">Seleccionar Imágenes</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeCreateModal"></button>
                </div>
                
                <!-- Formulario -->
                <form id="createAlbumForm" enctype="multipart/form-data" method="post" class="mt-3"> 
                    
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-semibold text-secondary mb-3">Arrastrar y Soltar Fotos</label>
                        
                        <div class="file-upload border border-2 border-dashed p-5 text-center bg-white rounded-3 cursor-pointer" id="fileUpload">
                            <i class="upload-icon uil uil-image-upload fs-1 text-secondary"></i>
                            <div class="upload-text text-dark fw-medium">Arrastra imágenes aquí o haz clic para seleccionar</div>
                            <div class="upload-hint small text-muted mt-1">PNG, JPG hasta 5MB cada una</div>
                        </div>
                        
                        <input type="file" name="imageInput[]" id="imageInput" multiple accept="image/*" class="d-none">
                        
                        <div class="image-preview row g-3 mt-3" id="imagePreview">
                        </div>
                    </div>

                    <!-- Si hubo un error -->
                    <div class="error" id="errorCreateAlbum"></div>
                    
                    <!-- Botones -->
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="cancelCreate" data-bs-dismiss="modal">Cancelar</button>
                        <button  type="button" class="btn btn-primary" id="continueCreate">Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!----------------- Modal siguiente (Imagenes seleccionadas) ----------------->
    <div class="modal fade" id="selectImages" tabindex="-1" aria-labelledby="selectImagesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="selectImagesLabel">Imagenes Seleccionadas:</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeSelectImages"></button>
                </div>
                
                <form id="selectImagesForm" class="mt-3"> 
                    
                    
                </form>
            </div>
        </div>
    </div>
    <!-- Modal para seleccionar opcion -->
    <div class="modal fade" id="optionAlbumModal" tabindex="-1" aria-labelledby="optionAlbumLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> 
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="optionAlbumLabel">Selecciona Dónde Publicar:</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeOptionAlbum"></button>
                </div>
                
                <div class="row g-3 justify-content-center">
                    
                    <div class="col-12 col-md-5">
                        <input type="radio" class="btn-check" name="albumOption" id="createAlbumRadio" value="create" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary p-4 w-100 h-100 custom-option-card" for="createAlbumRadio">
                            <i class="uil uil-folder-plus fs-1 mb-2"></i>
                            <div class="fw-bold">Crear Nuevo Álbum</div>
                            <div class="small text-muted mt-1">Se te pedirá un título para el álbum.</div>
                        </label>
                    </div>

                    <div class="col-12 col-md-5">
                        <input type="radio" class="btn-check" name="albumOption" id="selectAlbumRadio" value="select" autocomplete="off">
                        <label class="btn btn-outline-secondary p-4 w-100 h-100 custom-option-card" for="selectAlbumRadio">
                            <i class="uil uil-folder-open fs-1 mb-2"></i>
                            <div class="fw-bold">Publicar en Álbum Existente</div>
                            <div class="small text-muted mt-1">Selecciona un álbum de tu lista.</div>
                        </label>
                    </div>
                    
                </div>
                
                <div id="optionAlbumContainer" class="mt-4">
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" id="backToCarousel">Volver</button>
                    <button type="button" class="btn btn-primary" id="postOptionAlbum">PUBLICAR</button>
                </div>
                <div class="error" id="errorPostAlbum"></div>
            </div>
        </div>
    </div>



<!-----------------  MODAL DE MIS ALBUMES ----------------->
    <div class="modal fade" id="myAlbumsModal" tabindex="-1" aria-labelledby="myAlbumsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="myAlbumsModalLabel">Mis Álbumes:</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closemyAlbums"></button>
                </div>
                
                <div id="myAlbumsContainer" class="mt-4">
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" id="closeMyAlbums" data-bs-dismiss="modal">Cerrar</button>
                </div>
                <div class="error" id="errorMyAlbums"></div>
            </div>
        </div>
    </div>
    <!-- Modal para preguntar si desea o no eleminar un album -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content p-3 rounded-4 shadow-lg">
                    
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger fw-bold" id="confirmDeleteLabel">⚠️ Confirmar Eliminación</h5>
                    </div>
                    
                    <div class="modal-body text-center pt-2 pb-3">
                        <p id="deleteMessage">¿Estás seguro de que deseas eliminar este álbum y **todas sus imágenes**?</p>
                        <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
                    </div>
                    
                    <div class="modal-footer d-flex justify-content-center border-0 pt-0 gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- ---------------------- FAVORITOS ------------------------ -->
    <div class="modal fade" id="favoritesModal" tabindex="-1" aria-labelledby="favoritesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-4" id="favoritesModalLabel">Mis Favoritos</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Contenido de la sección de favoritos...</p>
                </div>
            </div>
        </div>
    </div>

<!-----------------  MODAL DE LOGIN ----------------->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4 shadow-lg custom-login-card"> 
                
                <button type="button" class="btn-close position-absolute top-0 end-0 mt-3 me-3" data-bs-dismiss="modal" aria-label="Close" id="closeLoginModal"></button>

                <!-- Opciones LogIn o SignUp -->
                <ul class="nav nav-pills nav-justified mb-4 custom-login-tabs" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab" aria-controls="pills-login" aria-selected="true">Log In</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-signup-tab" data-bs-toggle="pill" data-bs-target="#pills-signup" type="button" role="tab" aria-controls="pills-signup" aria-selected="false">Sign Up</button>
                    </li>
                </ul>

                <!-- Logo -->
                <div class="text-center logo mb-3">
                    <img src="./Frontend/assets/images/appImages/logo.png" alt="logo" class="rounded-circle" style="width: 80px; height: 80px;">
                </div>

                <div class="tab-content" id="pills-tabContent">
                    <!-- -------------------------- LOGIN -------------------------- -->
                    <div class="tab-pane fade show active" id="pills-login" role="tabpanel" aria-labelledby="pills-login-tab">
                        <h4 class="text-center text-uppercase mb-4">Log In</h4>

                        <!-- Formulario -->
                        <form id="loginForm">
                            
                            <div class="form-groupLogin mb-2 position-relative">
                                <input type="email" class="form-style form-control" placeholder="Email" name="mail" id="mailLogin" required>
                                <i class="input-icon uil uil-at"></i>
                            </div>
                            <div class="error" id="errorEmailLogin"></div>

                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="password" class="form-style form-control" placeholder="Contraseña" name="pass" id="passLogin" required>
                                <i class="toggle-pass uil uil-eye"></i>
                                <i class="input-icon uil uil-lock-alt"></i>
                            </div>
                            <div class="error" id="errorPassLogin"></div>

                            <button type="button" class="btnLogin btn btn-primary w-100 mt-4" id="loginBtnSubmit">Login</button>
                            <p class="mb-0 mt-3 text-center small"><a href="#" class="linkLogin">¿Olvidaste tu contraseña?</a></p>
                        </form>
                    </div>

                    <!-- -------------------------- SIGNUP -------------------------- -->
                    <div class="tab-pane fade" id="pills-signup" role="tabpanel" aria-labelledby="pills-signup-tab">
                        <h4 class="text-center text-uppercase mb-4">Sign Up</h4>

                        <!-- Formulario -->
                        <form id="signupForm">
                            
                            <div class="form-groupLogin mb-2 position-relative">
                                <input type="date" class="form-style form-control" placeholder="Fecha de Nacimiento" name="fNac" id="fNac" required>
                                <i class="input-icon uil uil-calendar-alt"></i>
                            </div>
                            <div class="error" id="errorFnac"></div>

                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="text" class="form-style form-control" placeholder="Nombre" name="nbre" id="nbre" required>
                                <i class="input-icon uil uil-user"></i>
                            </div>
                            <div class="error" id="errorNbre"></div>
                            
                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="text" class="form-style form-control" placeholder="Apellido" name="ape" id="ape" required>
                                <i class="input-icon uil uil-user"></i>
                            </div>
                            <div class="error" id="errorApe"></div>

                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="text" class="form-style form-control" placeholder="Nombre de Usuario" name="userName" id="userName" required >
                                <i class="input-icon uil uil-user-circle"></i>
                            </div>
                            <div class="error" id="errorUser"></div>

                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="email" class="form-style form-control" placeholder="Email" name="mail" id="mailSignUp" required>
                                <i class="input-icon uil uil-at"></i>
                            </div>
                            <div class="error" id="errorEmailSignUp"></div>

                            <div class="form-groupLogin mt-3 mb-2 position-relative">
                                <input type="password" class="form-style form-control" placeholder="Contraseña" name="pass" id="passSignUp" required>
                                <i class="toggle-pass uil uil-eye"></i>
                                <i class="input-icon uil uil-lock-alt"></i>
                            </div>
                            <div class="error" id="errorPassSignUp"></div>

                            <button type="button" class="btnLogin btn btn-primary w-100 mt-4" id="signupBtnSubmit">Registrarse</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!----------------------------Modal de notifcacion Estatica (Reutilizada por otros modales)--------------------->
    <div class="modal fade" id="staticNotificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3 rounded-4 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="staticNotificationModalLabel">Notificación</h5>
                </div>
                <div class="modal-body pt-2 pb-3">
                    <div id="notificationIconStatic" class="mb-3 fs-2"></div>
                    <p id="notificationMessageStatic" class="fw-semibold"></p>
                </div>
                <div class="modal-footer d-flex justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100" id="notificationAcceptButton">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    
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

