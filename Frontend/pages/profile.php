<?php
require_once "../../BACKEND/Clases/Image.php"; 
require_once "../../BACKEND/conexion.php"; 
$conn = conexion();
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 123;
}

$userId = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Usuario');


// Obtener datos del usuario desde la BD
$sql = "SELECT U_nameUser, U_name, U_lastName, U_biography, U_dateBirth, U_registrationDate 
        FROM users WHERE U_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// Obtener imagen de perfil
$profileImagePath = Imagen::getProfileImagePath($conn, $userId);

// Obtener estadísticas
// Followers (personas que me siguen)
$sqlFollowers = "SELECT COUNT(*) as total FROM follow WHERE F_idFollowed = ? AND F_status = 1";
$stmtFollowers = mysqli_prepare($conn, $sqlFollowers);
mysqli_stmt_bind_param($stmtFollowers, "i", $userId);
mysqli_stmt_execute($stmtFollowers);
$resultFollowers = mysqli_stmt_get_result($stmtFollowers);
$followers = mysqli_fetch_assoc($resultFollowers)['total'];

// Following (personas que sigo)
$sqlFollowing = "SELECT COUNT(*) as total FROM follow WHERE F_idFollower = ? AND F_status = 1";
$stmtFollowing = mysqli_prepare($conn, $sqlFollowing);
mysqli_stmt_bind_param($stmtFollowing, "i", $userId);
mysqli_stmt_execute($stmtFollowing);
$resultFollowing = mysqli_stmt_get_result($stmtFollowing);
$following = mysqli_fetch_assoc($resultFollowing)['total'];

// Obtener álbumes del usuario (excluyendo álbumes del sistema)
$sqlAlbums = "SELECT a.A_id, a.A_title, a.A_creationDate,
              (SELECT i.I_ruta FROM images i 
               WHERE i.I_idAlbum = a.A_id AND i.I_isCover = 1 
               LIMIT 1) as cover_image,
              (SELECT COUNT(*) FROM images i WHERE i.I_idAlbum = a.A_id) as image_count
              FROM albums a 
              WHERE a.A_idUser = ? AND a.A_isSystemAlbum = 0
              ORDER BY a.A_creationDate DESC";
$stmtAlbums = mysqli_prepare($conn, $sqlAlbums);
mysqli_stmt_bind_param($stmtAlbums, "i", $userId);
mysqli_stmt_execute($stmtAlbums);
$resultAlbums = mysqli_stmt_get_result($stmtAlbums);
$albums = [];
while ($row = mysqli_fetch_assoc($resultAlbums)) {
    $albums[] = $row;
}

$isLoggedIn = true;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?= $username ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    
    
</head>
<body>
    
    <?php include("../includes/header.php"); ?>


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
                        <h1 class="profile-username"><?= $username ?></h1>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($userData['U_name'] . ' ' . $userData['U_lastName']) ?>
                        </p>
                    </div>

                    <!-- Información y estadísticas -->
                    <div class="col-lg-9">
                        
                        <!-- Estadísticas -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-4">
                                <div class="stats-card">
                                    <div class="stat-value"><?= count($albums) ?></div>
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
                                <?= !empty($userData['U_biography']) 
                                    ? nl2br(htmlspecialchars($userData['U_biography'])) 
                                    : 'Sin biografía aún. ¡Añade una descripción sobre ti!' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE ÁLBUMES -->
        <section class="albums-section">
            <h4 class="mb-4">
                <i class="uil uil-folder-open me-2"></i>Mis Álbumes
            </h4>

            <?php if (empty($albums)): ?>
                <div class="empty-albums">
                    <i class="uil uil-folder-slash"></i>
                    <h5>No tienes álbumes todavía</h5>
                    <p class="mb-3">¡Crea tu primer álbum y comparte tus artesanías!</p>
                    <button class="btn btn-secondary" id="createFirstAlbum">
                        <i class="uil uil-plus-circle me-1"></i>
                        <br> Crear Álbum
                    </button>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($albums as $album): ?>
                        <div class="col">
                            <div class="album-card-profile" onclick="openAlbum(<?= $album['A_id'] ?>)">
                                <?php if (!empty($album['cover_image'])): ?>
                                    <img src="../../<?= htmlspecialchars($album['cover_image']) ?>" 
                                            alt="<?= htmlspecialchars($album['A_title']) ?>" 
                                            class="album-card-image">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <i class="uil uil-image-slash" style="font-size: 4rem; color: white;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="album-card-overlay">
                                    <h3 class="album-card-title"><?= htmlspecialchars($album['A_title']) ?></h3>
                                    <div class="album-card-info">
                                        <?= $album['image_count'] ?> imagen<?= $album['image_count'] != 1 ? 'es' : '' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

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
                                      placeholder="Escribe algo sobre ti..."><?= htmlspecialchars($userData['U_biography'] ?? '') ?></textarea>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>