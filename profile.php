<?php
require_once "./BACKEND/Clases/Image.php";
require_once "./BACKEND/Clases/User.php"; 
require_once "./BACKEND/Clases/Album.php";
require_once "./BACKEND/conexion.php"; 
$conn = conexion();
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$isLoggedIn = true;

$user = User::getById($conn, $_SESSION['user_id']);
if(!$user){
    header("Location: index.php");
    exit();
}

$dateObj = new DateTime($user->dateBirth);
$formattedDate = $dateObj->format('d-m-Y');

// Obtener imagen de perfil
$profileImagePath = Imagen::getProfileImagePath($conn,$user->id);

//Followers (personas que me siguen)
$followers = User::countFollowers($conn, $user->id);

// Following (personas que sigo)
$following = User::countFollowing($conn, $user->id);

$cantAlbums = count(Album::getByUser($conn, $user->id));


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?= $user->username ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <link rel="stylesheet" href="./Frontend/assets/css/styles.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    
</head>
<body>
    
    <?php 
        include("./Frontend/includes/header.php"); 
    ?>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content-offset">
        <div class="container-fluid profile-container">
            
            <!-- CABECERA DEL PERFIL -->
            <div class="profile-header-section">
                <div class="row align-items-center">
                    
                    <!-- Avatar y nombre -->
                    <div class="col-lg-3 text-center mb-4 mb-lg-0">
                        <div class="profile-avatar-large mx-auto" data-bs-toggle="modal" data-bs-target="#viewProfilePicModal">
                            <img src="<?= htmlspecialchars($profileImagePath) ?>" alt="Avatar">
                        </div>
                        <h1 class="profile-username"><?= $user->username ?></h1>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($user->name . ' ' . $user->lastName) ?>
                        </p>
                    </div>

                    <!-- Información y estadísticas -->
                    <div class="col-lg-9">
                        
                        <!-- Estadísticas -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-4">
                                <div class="stats-card">
                                    <div class="stat-value"><?= $cantAlbums; ?></div>
                                    <div class="stat-label">Álbumes</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="stats-card">
                                    <div class="stat-value"><?= $followers ?></div>
                                    <div class="stat-label">Followers</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="stats-card">
                                    <div class="stat-value"><?= $following ?></div>
                                    <div class="stat-label">Following</div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 mb-4 flex-wrap">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="uil uil-edit me-1"></i> Editar Perfil
                            </button>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#profileHistoryModal">
                                <i class="uil uil-history me-1"></i> Historial de Fotos
                            </button>
                        </div>

                        <!-- Descripción -->
                        <div class="description-card">
                            <h3 class="description-title">
                                <i class="uil uil-info-circle me-1"></i> Biografía:
                            </h3>
                            <p class="mb-0 text-secondary">
                                <?= !empty($user->biography) 
                                    ? nl2br(htmlspecialchars($user->biography)) 
                                    : 'Sin biografía aún. ¡Añade una descripción sobre ti!' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE ÁLBUMES -->
        <section class="albums-section" id="myAlbumsProfileSection">
            
        </section>
        <div class="error" id="errorMyAlbumsProfile"></div>

    </main>
    
    
    <!-- MODAL VER FOTO DE PERFIL -->
     
    <div class="modal fade" id="viewProfilePicModal" tabindex="-1" aria-labelledby="viewProfilePicModalLabel" aria-hidden="true">
        <!-- Usamos modal-sm para que sea pequeño, pero el contenido interno lo forzará a la imagen -->
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-0 rounded-4 border-0 shadow-lg"> 
                
                <!-- Contenedor del Cuerpo y Pie (Unificado) -->
                <div class="modal-body p-0 position-relative">
                    
                    <!-- 1. Imagen Principal (Ocupa el 100% del cuerpo del modal) -->
                    <div class="profile-pic-display-wrap">
                        <img src="<?= htmlspecialchars($profileImagePath ?? './Frontend/assets/images/appImages/default.jpg') ?>" 
                            alt="Tu Foto de Perfil" 
                            class="img-fluid w-100 rounded-4"
                            style="aspect-ratio: 1/1; object-fit: cover;"
                            id="modalCurrentProfilePic">
                    </div>
                    
                    <!-- 2. Botón de Cerrar (Invisible sobre la imagen) -->
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 z-20" 
                            data-bs-dismiss="modal" aria-label="Cerrar" style="filter: drop-shadow(0 0 2px black);">
                    </button>
                    
                    
                    <div class="error mt-2" id="errorRemoveProfilePic"></div>
                </div>
                
            </div>
        </div>
    </div>

<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->

    <!-- MODAL EDITAR PERFIL -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
                
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="editProfileModalLabel">Editar Mi Perfil</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeEditProfile"></button>
                </div>
                
                <div class="modal-body">
                    <form id="editProfileForm">
                        
                        <div class="row g-3 mb-4 align-items-center">
                            
                            <div class="col-12 col-md-4 text-center">
                                <div class="profile-avatar-edit mx-auto mb-3 position-relative">
                                    <img src="<?= htmlspecialchars($profileImagePath ?? './Frontend/assets/images/appImages/default.jpg') ?>" 
                                        alt="Foto de Perfil" 
                                        class="rounded-circle border border-3 border-primary"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                    
                                    <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 me-2 mb-2" id="changeProfilePic">
                                        <i class="uil uil-camera" style="font-size: 1.2rem;"></i>
                                    </button>
                                    <input type="file" id="profilePicInput" name="profilePic" accept="image/*" class="d-none">
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-groupLogin mt-3 mb-2 position-relative">
                                    <label for="editUsername" class="form-label visually-hidden">Nombre de Usuario</label>
                                    <input type="text" class="form-style form-control" placeholder="Nombre de Usuario" name="editUsername" id="editUsername" 
                                        value="<?= htmlspecialchars($user->username ?? '') ?>" required>
                                    <i class="input-icon uil uil-user-circle"></i>
                                </div>
                                <div class="error" id="errorEditUsername"></div>
                                
                                <div class="form-groupLogin mt-3 mb-2 position-relative">
                                    <label for="editEmail" class="form-label visually-hidden">Email</label>
                                    <input type="email" class="form-style form-control text-muted" placeholder="Email" name="editEmail" id="editEmail" 
                                        value="<?= htmlspecialchars($user->email ?? '') ?>" readonly>
                                    <i class="input-icon uil uil-at"></i>
                                </div>
                            </div>
                        </div>
                        
                        <hr>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-groupLogin mt-3 mb-2 position-relative">
                                    <label for="editName" class="form-label visually-hidden">Nombre</label>
                                    <input type="text" class="form-style form-control" placeholder="Nombre" name="editName" id="editName" 
                                        value="<?= htmlspecialchars($user->name ?? '') ?>" required>
                                    <i class="input-icon uil uil-user"></i>
                                </div>
                                <div class="error" id="errorEditName"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-groupLogin mt-3 mb-2 position-relative">
                                    <label for="editLastName" class="form-label visually-hidden">Apellido</label>
                                    <input type="text" class="form-style form-control" placeholder="Apellido" name="editLastName" id="editLastName" 
                                        value="<?= htmlspecialchars($user->lastName ?? '') ?>" required>
                                    <i class="input-icon uil uil-user"></i>
                                </div>
                                <div class="error" id="errorEditLastName"></div>
                            </div>

                            <div class="col-12">
                                <div class="form-groupLogin position-relative mt-3 mb-2">
                                    <label for="editDateBirth" class="form-label visually-hidden">Fecha de Nacimiento</label>
                                    <input type="date" class="form-style form-control" placeholder="Fecha de Nacimiento" name="editDateBirth" id="editDateBirth" 
                                        value="<?= htmlspecialchars($formattedDate ?? '') ?>" required>
                                    <i class="input-icon uil uil-calendar-alt"></i>
                                </div>
                                <div class="error" id="errorEditDateBirth"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="editBiography" class="form-label fw-semibold text-secondary">Biografía:</label>
                            <textarea class="form-control" id="editBiography" name="editBiography" rows="4" 
                                    placeholder="Escribe algo sobre ti..."><?= htmlspecialchars($user->biography ?? '') ?></textarea>
                        </div>

                        <div class="text-start mb-4">
                            <button type="button" class="btn btn-sm btn-outline-danger mb-1" id="changePasswordButton" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="uil uil-key-skeleton-alt me-1"></i> Cambiar Contraseña
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-1" id="removeProfilePicButton">
                                <i class="uil uil-user-times me-1"></i> Remover Foto de Perfil
                            </button>
                        </div>
                        <div class="error" id="errorEditProfile"></div>
                    </form>
                </div>
                
                <div class="modal-footer d-flex justify-content-end border-0 pt-0 gap-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="editProfileForm" class="btn btn-primary" id="saveChangeEditProfileButton">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!----------------- MODAL PARA CAMBIAR CONTRASEÑA ----------------->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <!-- Titulo -->
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="changePasswordLabel">Cambiar Contraseña</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeChangePasswordModal"></button>
                </div>
                    
                    <!-- Formulario -->
                <form id="changePasswordForm">
                    
                    <!-- Actual -->
                    <div class="form-groupLogin mt-3 mb-2 position-relative">
                        <input type="password" class="form-style form-control" placeholder="Ingrese Contraseña Actual" name="pass" id="changePassCurrent" required>
                        <i class="toggle-pass uil uil-eye"></i>
                        <i class="input-icon uil uil-lock-alt"></i>
                    </div>
                    <div class="error" id="errorChangePassCurrent"></div>

                    <!-- Nueva -->
                    <div class="form-groupLogin mt-3 mb-2 position-relative">
                        <input type="password" class="form-style form-control" placeholder="Ingrese Nueva Contraseña" name="pass" id="changePassNew" required>
                        <i class="toggle-pass uil uil-eye"></i>
                        <i class="input-icon uil uil-lock-alt"></i>
                    </div>
                    <div class="error" id="errorChangePassNew"></div>

                    <!-- Confirmar Nueva -->
                    <div class="form-groupLogin mt-3 mb-2 position-relative">
                        <input type="password" class="form-style form-control" placeholder="Confirmar Contraseña" name="pass" id="changePassNewConfirm" required>
                        <i class="toggle-pass uil uil-eye"></i>
                        <i class="input-icon uil uil-lock-alt"></i>
                    </div>
                    <div class="error" id="errorChangePassNewConfirm"></div>

                </form>
                
                <!-- Botones -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="cancelChangePassword" data-bs-dismiss="modal">Cancelar</button>
                    <button  type="button" class="btn btn-primary" id="changePasswordBtn">Cambiar</button>
                </div>

                <div class="error" id="errorChangePass"></div>
                    
                
            </div>
        </div>
    </div>




    <!-- Modal para preguntar si desea o no quitar foto de perfil -->
    <div class="modal fade" id="confirmRemoveProfilePicModal" tabindex="-1" aria-labelledby="confirmRemoveProfilePicLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-3 rounded-4 shadow-lg">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-bold" id="confirmRemoveProfilePicLabel">Confirmar </h5>
                </div>
                
                <div class="modal-body text-center pt-2 pb-3">
                    <p id="deleteMessage">¿Estás Seguro de que Deseas Remover la Foto de Perfil Actual</p>
                </div>
                
                <div class="modal-footer d-flex justify-content-center border-0 pt-0 gap-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveProfilePicButton">Remover</button>
                </div>
            </div>
        </div>
    </div>


<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->
    <!-- MODAL HISTORIAL DE FOTOS -->
    <div class="modal fade" id="profileHistoryModal" tabindex="-1" aria-labelledby="profileHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4 rounded-4 border shadow-lg" style="background-color: var(--background-color);">
            
                <!-- Titulo -->
                <div class="modal-header border-0 pb-0 mb-3">
                    <h2 class="modal-title fs-4 fw-bold text-primary" id="profileHistoryLabel">Historial de Fotos de Perfil</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeProfileHistoryModal"></button>
                </div>
                    
                    <!-- Formulario -->
                <div id="profileHistoryContainer" class="modal-body profile-history-body">
                    <!-- Aquí se inyectarán las imágenes de perfil mediante JavaScript -->
                </div>
                
                <!-- Botones -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="closeProfileHistoryModal" data-bs-dismiss="modal">Cerrar</button>
                </div>

                <div class="error" id="errorProfileHistory"></div>
                    
                
            </div>
        </div>
    </div>
    
    <!-- MODAL VER FOTO DE PERFIL EN EL HISTORIAL--> 
    <div class="modal fade" id="viewProfilePicHistoryModal" tabindex="-1" aria-labelledby="viewProfilePicHistoryLabel" aria-hidden="true">
        <!-- Usamos modal-sm para que sea pequeño, pero el contenido interno lo forzará a la imagen -->
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-0 rounded-4 border-0 shadow-lg"> 
                
                <!-- Contenedor del Cuerpo y Pie (Unificado) -->
                <div class="modal-body p-0 position-relative">
                    
                    <!-- 1. Imagen Principal (Ocupa el 100% del cuerpo del modal) -->
                    <div class="profile-pic-display-wrap">
                        <img src="" 
                            alt="Tu Foto de Perfil" 
                            class="img-fluid w-100 rounded-4"
                            style="aspect-ratio: 1/1; object-fit: cover;"
                            id="imgProfilePicHistory">
                    </div>
                    
                    <!-- 2. Botón de Cerrar (Invisible sobre la imagen) -->
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 z-20" 
                            data-bs-dismiss="modal" aria-label="Cerrar" style="filter: drop-shadow(0 0 2px black);">
                    </button>

                </div>
                
            </div>
        </div>
    </div>
    <!-- Modal para preguntar si desea o no Establecer la imagen de perfil -->
    <div class="modal fade" id="confirmSetProfileImageModal" tabindex="-1" aria-labelledby="confirmSetProfileImageLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-3 rounded-4 shadow-lg">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-primary fw-bold" id="confirmSetProfileImageLabel">Confirmar </h5>
                </div>
                
                <div class="modal-body text-center pt-2 pb-3">
                    <p id="deleteMessage">¿Estás Seguro de que Deseas Establecer esta Foto como Imagen de Perfil?</p>
                </div>
                
                <div class="modal-footer d-flex justify-content-center border-0 pt-0 gap-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmSetProfileImageButton">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->
<!-- -------------------------------------------------------------------------------------------------- -->
    <?php 
        require_once("./Frontend/includes/modals.php");
    ?>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Para Fecha de Nacimiento -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- EL flactpickr lo ocmento porque me esta mostrando algo en la pantalla (REVISAR MOTIVO) -->

<script>
    // Variable global JS que indica si el usuario inició sesión
    window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>

<!-- Para trabajar los albumes en la pagina profile.php -->
<script src="./Frontend/assets/js/myAlbumsProfile.js"></script>

<!-- Para el modal de editar perfil -->
<script src="./Frontend/assets/js/editProfileModal.js"></script>

<!-- Para remover imagen de perfil actual -->
<script src="./Frontend/assets/js/removeProfilePic.js"></script>

<!-- Para cambiar contraseña -->
<script src="./Frontend/assets/js/changePassword.js"></script>

<!-- Para manejar el historial de fotos de perfil -->
<script src="./Frontend/assets/js/profileHistory.js"></script>
</html>