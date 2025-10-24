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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Frontend/assets/css/styles.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    
    
</head>
<body>
    
    <?php 
        include("./Frontend/includes/header.php"); 
        include("./Frontend/includes/modals.php");
    ?>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content-offset">
        <div class="container-fluid profile-container">
            
            <!-- CABECERA DEL PERFIL -->
            <div class="profile-header-section">
                <div class="row align-items-center">
                    
                    <!-- Avatar y nombre -->
                    <div class="col-lg-3 text-center mb-4 mb-lg-0">
                        <div class="profile-avatar-large mx-auto">
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

    <!-- MODAL EDITAR PERFIL -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-4" id="editProfileModalLabel">Editar Perfil</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm">
                        <div class="mb-3">
                            <label for="editBiography" class="form-label">Biografía:</label>
                            <textarea class="form-control" id="editBiography" rows="4" 
                                      placeholder="Escribe algo sobre ti..."><?= htmlspecialchars($user->biography ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL HISTORIAL DE FOTOS -->
    <div class="modal fade" id="profileHistoryModal" tabindex="-1" aria-labelledby="profileHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content p-4 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-4" id="profileHistoryModalLabel">Historial de Fotos de Perfil</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="profileHistoryGrid" class="row row-cols-2 row-cols-md-3 g-3">
                        <!-- Se cargará dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <script>
        // Abrir álbum
        function openAlbum(albumId) {
            window.location.href = `album.php?id=${albumId}`;
        }

        // Crear primer álbum
        document.getElementById('createFirstAlbum')?.addEventListener('click', function() {
            const modalElement = document.getElementById('createAlbumModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });

        // Editar perfil
        document.getElementById('editProfileForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const biography = document.getElementById('editBiography').value;
            
            try {
                const response = await fetch('../../BACKEND/updateProfile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ biography })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error al actualizar perfil: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar perfil');
            }
        });

        // Cargar historial de fotos de perfil
        document.getElementById('profileHistoryModal')?.addEventListener('shown.bs.modal', async function() {
            try {
                const response = await fetch('../../BACKEND/getProfileHistory.php');
                const result = await response.json();
                
                const grid = document.getElementById('profileHistoryGrid');
                
                if (result.success && result.images.length > 0) {
                    grid.innerHTML = result.images.map(img => `
                        <div class="col">
                            <div class="position-relative">
                                <img src="../../${img.I_ruta}" class="img-fluid rounded" alt="Foto de perfil">
                                <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2 small">
                                    ${new Date(img.I_publicationDate).toLocaleDateString()}
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<div class="col-12 text-center text-muted">No hay historial de fotos</div>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script> -->
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Para Fecha de Nacimiento -->
<!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
<!-- EL flactpickr lo ocmento porque me esta mostrando algo en la pantalla (REVISAR MOTIVO) -->

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

<!-- Para trabajar los albumes en la pagina profile.php -->
<script src="./Frontend/assets/js/myAlbumsProfile.js"></script>
</html>