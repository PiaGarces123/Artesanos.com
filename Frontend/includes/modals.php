<?php
    // Obtenemos el ID del usuario logueado. Será 'null' si no hay sesión.
    $loggedInUserId_php = $_SESSION['user_id'] ?? null;
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
                
                <div class="album-filter-container mb-3" id="modalAlbumFilters">
                    <div class="btn-group w-100" role="group" aria-label="Filtro de álbumes">
                        
                        <input type="radio" class="btn-check" name="albumFilterModal" id="filterModalAll" value="all" checked>
                        <label class="btn btn-outline-secondary" for="filterModalAll">
                            <i class="uil uil-files-landscapes me-1"></i> Todos
                        </label>

                        <input type="radio" class="btn-check" name="albumFilterModal" id="filterModalOwn" value="own">
                        <label class="btn btn-outline-secondary" for="filterModalOwn">
                            <i class="uil uil-user-square me-1"></i> Propios
                        </label>

                        <input type="radio" class="btn-check" name="albumFilterModal" id="filterModalSystem" value="system">
                        <label class="btn btn-outline-secondary" for="filterModalSystem">
                            <i class="uil uil-heart-sign me-1"></i> Sistema (Likes)
                        </label>

                    </div>
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

    <!-- MODAL PARA EDITAR ALBUM (TITULO) -->
    <div class="modal fade" id="editAlbumModal" tabindex="-1" aria-labelledby="editAlbumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="editAlbumModalLabel">Editar Título del Álbum</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="editAlbumForm">
                    <div class="modal-body">
                        
                        <input type="hidden" id="editAlbumIdInput" name="albumId">

                        <div class="form-groupLogin mt-3 mb-2 position-relative">
                            <label for="editAlbumTitleInput" class="form-label visually-hidden">Título del Álbum</label>
                            <input type="text" class="form-style form-control" placeholder="Nuevo título del álbum" 
                                name="editAlbumTitle" id="editAlbumTitleInput" required>
                            <i class="input-icon uil uil-folder"></i>
                        </div>
                        
                        <div class="error" id="errorEditAlbum"></div>
                    </div>
                    
                    <div class="modal-footer d-flex justify-content-end border-0 pt-0 gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveEditAlbumButton">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER FOTOS DE UN ALBUM -->
    <div class="modal fade" id="imagesAlbumModal" tabindex="-1" aria-labelledby="imagesAlbumLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <!-- Titulo -->
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="imagesAlbumLabel">  </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeImagesAlbumModal"></button>
                </div>

                <!-- Formulario -->
                <div id="imagesAlbumContainer" class="modal-body images-album-body">
                    <!-- Aquí se inyectarán las imágenes -->
                </div>
                
                <!-- Botones -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="closeImagesAlbumModal" data-bs-dismiss="modal">Cerrar</button>
                </div>

                <div class="error" id="errorImagesAlbum"></div>


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

     <!-- Modal para visualización de imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                
                <div class="modal-body p-0">
                    <div class="row g-0">

                        <div class="col-lg-7 d-flex align-items-center justify-content-center bg-black" id="imageModalImageCol">
                            
                            <img src="" alt="Imagen" class="img-fluid" id="imagePublic">
                        
                        </div>

                        <div class="col-lg-5 d-flex flex-column" id="imageModalInfoCol">
                            
                            <div class="p-3 border-bottom">
                                
                                <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>

                                <div class="d-flex align-items-center mb-3">
                                    <img src="" alt="Avatar" class="rounded-circle me-2 avatar-img" id="avatarUser">
                                    <div>
                                        <h5 class="mb-0 fs-6 fw-bold" id="nameUser"></h5>
                                        <div class="text-muted small" id="TitleImage"></div>
                                    </div>
                                </div>
                                
                                <div class="like-section mt-3">
                                    <button class="btn btn-light border-0 btn-lg like-btn" id="likeButton">
                                        <i class="uil uil-heart"></i> 
                                        <span id="likeCount" class="ms-1">0</span>
                                    </button>
                                </div>

                                <div class="image-actions-container pt-2" id="imageActionsContainer">
                                </div>
                            </div>

                            

                            <div class="p-3 flex-grow-1 overflow-y-auto" id="commentListContainer">
                                <p class="text-center text-secondary small">Cargando comentarios...</p>
                            </div>

                            <div class="p-3 border-top bg-light">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Añade un comentario..." id="newCommentInput">
                                    <button class="btn btn-primary" type="button" id="postCommentButton">Publicar</button>
                                </div>
                                <div class="error" id="errorPostComment"></div>
                            </div>

                        </div> </div> </div> </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR IMAGEN -->
    <div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="editImageModalLabel">Editar Imagen</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="editImageForm">
                    <div class="modal-body">
                        
                        <input type="hidden" id="editImageIdInput" name="imageId">

                        <div class="form-groupLogin mt-3 mb-2 position-relative">
                            <label for="editImageTitleInput" class="form-label visually-hidden">Título</label>
                            <input type="text" class="form-style form-control" placeholder="Título de la imagen" 
                                name="editImageTitle" id="editImageTitleInput" required>
                            <i class="input-icon uil uil-comment-alt-edit"></i>
                        </div>
                        
                        <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="editImageVisibilityInput" name="editImageVisibility">
                        <label class="form-check-label" for="editImageVisibilityInput">
                            Privado (solo seguidores)
                        </label>
                        </div>
                        
                        <div class="error" id="errorEditImage"></div>
                    </div>
                    
                    <div class="modal-footer d-flex justify-content-end border-0 pt-0 gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveEditImageButton">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA DENUNCIAR -->
    <div class="modal fade" id="reportImageModal" tabindex="-1" aria-labelledby="reportImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-danger" id="reportImageModalLabel">Denunciar Imagen</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="reportImageForm">
                    <div class="modal-body">
                        <input type="hidden" id="reportImageIdInput" name="imageId">

                        <div class="mb-3">
                            <label for="reportReasonInput" class="form-label">Motivo de la denuncia:</label>
                            <textarea class="form-control" id="reportReasonInput" name="reportReason" rows="4" 
                                    placeholder="Describe por qué esta imagen infringe las normas..."></textarea>
                        </div>
                        
                        <div class="error" id="errorReportImage"></div>
                    </div>
                    
                    <div class="modal-footer d-flex justify-content-end border-0 pt-0 gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" id="sendReportButton">Enviar Denuncia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para preguntar si desea o no eliminar -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content p-3 rounded-4 shadow-lg">
                    
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger fw-bold" id="confirmDeleteLabel">⚠️ Confirmar Eliminación</h5>
                    </div>
                    
                    <div class="modal-body text-center pt-2 pb-3">
                        <p id="deleteMessage">¿Estás seguro de que deseas ELIMINAR?</p>
                        <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
                    </div>
                    
                    <div class="modal-footer d-flex justify-content-center border-0 pt-0 gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Eliminar</button>
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


    <!-- MODAL DE NOTIFICACIONES DE SEGUIMIENTO -->
    <div class="modal fade" id="requestsModal" tabindex="-1" aria-labelledby="requestsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="requestsModalLabel">
                        Solicitudes Pendientes
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="requestsModalContainer">
                    
                    <p class="text-center mt-3 text-secondary">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        Cargando solicitudes...
                    </p>

                </div>
                
                <div class="modal-footer d-flex justify-content-end gap-3 border-0 mt-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

                <div class="error" id="errorRequestsModal"></div>

            </div>
        </div>
    </div>

    <!-- 
    MODAL PARA CONFIRMAR CERRAR SESIÓN 
    (IDs únicos: confirmLogoutModal, confirmLogoutButton, etc.)
    -->
    <div class="modal fade" id="confirmLogoutModal" tabindex="-1" aria-labelledby="confirmLogoutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-3 rounded-4 shadow-lg">
                
                <div class="modal-header border-0 pb-0">
                    <!-- Título (usamos un ícono de "salir") -->
                    <h5 class="modal-title text-primary fw-bold" id="confirmLogoutLabel">
                        <i class="uil uil-sign-out-alt me-1"></i> Cerrar Sesión
                    </h5>
                </div>
                
                <div class="modal-body text-center pt-2 pb-3">
                    <!-- Mensaje (ID único) -->
                    <p id="logoutMessage">¿Estás seguro de que deseas cerrar sesión?</p>
                </div>
                
                <div class="modal-footer d-flex justify-content-center border-0 pt-0 gap-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- Botón de confirmación (ID único y clase 'btn-primary') -->
                    <button type="button" class="btn btn-primary" id="confirmLogoutButton">Cerrar Sesión</button>
                </div>
            </div>
        </div>
    </div>


    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!--Scripts Personalizados -->
<script>
    // 💡 Definimos una variable global *solo* para el ID del usuario logueado
    var logged_in_user_id = <?= json_encode($loggedInUserId_php) ?>;
</script>

<!-- JS para el Modal de Imagen -->
<script src="./Frontend/assets/js/imageModal.js"></script>

<!--Scripts de Acciones normales (cerrar sesion, buscar) -->
<script src="./Frontend/assets/js/actionNormal.js"></script>

<!--Scripts del Modal de Iniciar Session -->
<script src="./Frontend/assets/js/modal.js"></script>

<!--Scripts para Restringir Acciones dependiendo de si Inició sesion o no -->
<script src="./Frontend/assets/js/restrictedActions.js"></script>


<!--Scripts Para publicar Contenido, muestra imagenes seleccionadas -->
<script src="./Frontend/assets/js/modalSelectImages.js"></script>

<!--Opcion de Crear o Seleccionar Album -->
<script src="./Frontend/assets/js/modalOptionAlbum.js"></script>

<!-- Para trabajar los albumes -->
<script src="./Frontend/assets/js/myAlbumsModal.js"></script>

<!-- Para manejar los seguimientos -->
<script src="./Frontend/assets/js/seguimientos.js"></script>


    