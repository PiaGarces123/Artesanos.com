// favoritesModal.js - Maneja la visualización de imágenes favoritas

document.addEventListener("DOMContentLoaded", () => {

    // =========================================================================
    // 1. FUNCIONES AUXILIARES
    // =========================================================================

    const limpiarErrores = () => {
        document.querySelectorAll(".error").forEach(div => {
            div.textContent = "";
            div.classList.remove("visible-error");
        });
        document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
    };

    const mostrarError = (div, input, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
        if (input) input.classList.add("errorInput");
    };

    // =========================================================================
    // 2. ELEMENTOS DEL DOM
    // =========================================================================

    const favoritesModalEl = document.getElementById('favoritesModal');
    const favoritesModalInstance = favoritesModalEl ? (bootstrap.Modal.getInstance(favoritesModalEl) || new bootstrap.Modal(favoritesModalEl)) : null;
    const favoritesContainer = document.getElementById('favoritesContainer');
    const favoritesErrorDiv = document.getElementById('errorFavorites');

    // =========================================================================
    // 3. FUNCIÓN PRINCIPAL: CARGAR FAVORITOS
    // =========================================================================

    /**
     * Carga y muestra las imágenes favoritas del usuario
     */
    const loadFavoriteImages = async () => {
        if (!favoritesContainer || !favoritesErrorDiv) {
            console.error("Faltan elementos del modal de favoritos.");
            return;
        }

        // Mostrar spinner de carga
        favoritesContainer.innerHTML = `
            <p class="text-center mt-3 text-secondary">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> 
                Cargando tus favoritos...
            </p>
        `;
        
        limpiarErrores();

        try {
            const response = await fetch('./BACKEND/FuncionesPHP/getFavoriteImages.php');
            
            if (!response.ok) {
                throw new Error('Error al obtener favoritos');
            }

            const images = await response.json();
            
            // Verificar si el array está vacío
            if (images.length === 0) {
                favoritesContainer.innerHTML = `
                    <div class="alert alert-info text-center mt-3">
                        <i class="uil uil-heart-break fs-1 mb-2 d-block"></i>
                        <p class="mb-0">Aún no tienes imágenes favoritas.</p>
                        <p class="small text-muted mt-2">Da "me gusta" a las imágenes que te gusten para verlas aquí.</p>
                    </div>
                `;
                return;
            }

            // Renderizar las imágenes
            renderFavoriteImages(images);

        } catch (error) {
            console.error('Error al cargar favoritos:', error);
            mostrarError(favoritesErrorDiv, null, "Error de conexión al cargar favoritos.");
            favoritesContainer.innerHTML = `
                <p class="text-danger text-center mt-3">No se pudieron cargar tus favoritos.</p>
            `;
        }
    };

    // =========================================================================
    // 4. FUNCIÓN DE RENDERIZADO
    // =========================================================================

    /**
     * Renderiza las imágenes favoritas en el modal
     */
    const renderFavoriteImages = (images) => {
        let imagesHTML = `
            <div class="favorites-scroll-container"> 
                <div class="row row-cols-2 row-cols-md-3 g-3">
        `;
        
        images.forEach(image => {
            imagesHTML += `
                <div class="col">
                    <div class="profile-image-card position-relative">
                        <img src="${image.I_ruta}" 
                             alt="${image.I_title || 'Imagen favorita'}" 
                             class="img-fluid rounded" 
                             style="aspect-ratio: 1 / 1; object-fit: cover; cursor: pointer;"
                             data-action="view-favorite-image"
                             data-image-id="${image.I_id}">
                        
                        <!-- Badge con el usuario -->
                        <div class="favorite-owner-badge">
                            <img src="${image.ownerProfileImage}" 
                                 alt="${image.ownerUsername}"
                                 class="rounded-circle"
                                 style="width: 24px; height: 24px; object-fit: cover; margin-right: 4px;">
                            <span class="small">@${image.ownerUsername}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        imagesHTML += `</div></div>`;
        favoritesContainer.innerHTML = imagesHTML;

        // Adjuntar listeners para abrir el modal de imagen
        attachViewImageListeners();
    };

    // =========================================================================
    // 5. EVENT LISTENERS
    // =========================================================================

    /**
     * Adjunta listeners para ver cada imagen en el modal principal
     */
    const attachViewImageListeners = () => {
        favoritesContainer.querySelectorAll('img[data-action="view-favorite-image"]').forEach(img => {
            img.addEventListener('click', (e) => {
                const imgId = e.currentTarget.dataset.imageId;
                
                // Cerrar el modal de favoritos antes de abrir el modal de imagen
                if (favoritesModalInstance) {
                    favoritesModalEl.addEventListener('hidden.bs.modal', () => {
                        if (typeof openImageModal === 'function') {
                            openImageModal(imgId);
                        } else {
                            console.error('La función openImageModal no está definida.');
                        }
                    }, { once: true });
                    
                    favoritesModalInstance.hide();
                } else {
                    // Fallback: abrir directamente
                    if (typeof openImageModal === 'function') {
                        openImageModal(imgId);
                    }
                }
            });
        });
    };

    // =========================================================================
    // 6. INICIALIZACIÓN DEL MODAL
    // =========================================================================

    if (favoritesModalEl) {
        // Cargar favoritos cuando se abre el modal
        favoritesModalEl.addEventListener('shown.bs.modal', () => {
            loadFavoriteImages();
        });

        // Limpiar contenedor cuando se cierra el modal
        favoritesModalEl.addEventListener('hidden.bs.modal', () => {
            if (favoritesContainer) {
                favoritesContainer.innerHTML = "";
            }
            limpiarErrores();
        });
    }

    console.log('✅ favoritesModal.js inicializado correctamente');
});