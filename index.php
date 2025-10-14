<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artesanos</title>
    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="./Frontend/assets/css/styles.css" />
</head>
<body>
    
    <?php

    //Incluir Header
    include("./Frontend/includes/header.html");

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

    <!-- Link al archivo JS -->
    <script src="./Frontend/assets/js/index.js"></script>
</body>
</html>