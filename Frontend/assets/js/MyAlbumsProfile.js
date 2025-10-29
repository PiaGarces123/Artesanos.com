document.addEventListener("DOMContentLoaded", () => {

    // =========================================================================
    // 1. FUNCIONES AUXILIARES (Tus funciones existentes)
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
 
    const showStaticNotificationModal = (type, message, acceptCallback = null) => {
        // ... (Tu código de showStaticNotificationModal sin cambios) ...
        let modalEl = document.getElementById('staticNotificationModal');
        let modalIcon = document.getElementById('notificationIconStatic');
        let modalMessage = document.getElementById('notificationMessageStatic');
        let acceptBtn = document.getElementById('notificationAcceptButton');
        
        if (!modalEl || !modalIcon || !modalMessage || !acceptBtn) return;

        let modalContent = modalEl.querySelector('.modal-content');
        modalContent.classList.remove('alert-success', 'alert-danger');
        
        if (type === 'success') {
            modalIcon.innerHTML = '🎉';
            modalContent.classList.add('alert-success');
        } else {
            modalIcon.innerHTML = '⚠️';
            modalContent.classList.add('alert-danger');
        }
        
        modalMessage.textContent = message;
        
        let newAcceptBtn = acceptBtn.cloneNode(true);
        acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);
        
        let finalAcceptBtn = document.getElementById('notificationAcceptButton');
        let staticModalInstance = new bootstrap.Modal(modalEl);

        finalAcceptBtn.addEventListener('click', () => {
            if (acceptCallback) {
                acceptCallback();
            }
            staticModalInstance.hide();
        });
        staticModalInstance.show();
    };

    // =========================================================================
    // 2. INSTANCIAS DE MODALES
    // =========================================================================

    // Modal de confirmación (Genérico, lo reusamos para Álbumes e Imágenes)
    const confirmDeleteModalEl = document.getElementById('confirmDeleteModal');
    const confirmDeleteModal = confirmDeleteModalEl ? (bootstrap.Modal.getInstance(confirmDeleteModalEl) || new bootstrap.Modal(confirmDeleteModalEl)) : null;

    // Modal para ver imágenes del álbum
    const imagesModalEl = document.getElementById('imagesAlbumModal');
    const imagesModalInstance = imagesModalEl ? (bootstrap.Modal.getInstance(imagesModalEl) || new bootstrap.Modal(imagesModalEl)) : null;
    const imagesModalTitleEl = document.getElementById('imagesAlbumLabel');
    const imagesModalContainer = document.getElementById('imagesAlbumContainer');
    const imagesModalErrorDiv = document.getElementById('errorImagesAlbum');


    // =========================================================================
    // 3. FUNCIONES DE ACCIÓN (FETCH A LA BD)
    // =========================================================================

    // --- (Función para borrar Álbum) ---
    const deleteAlbum = async (albumId) => {
        let deleteEndpoint = './BACKEND/FuncionesPHP/eliminarAlbum.php'; 
        let formData = new FormData();
        formData.append('albumId', albumId);
        
        try {
            let res = await fetch(deleteEndpoint, { method: "POST", body: formData });
            let data = await res.json(); // Asumimos JSON

            if (confirmDeleteModal) confirmDeleteModal.hide();
            
            let callback = null;
            if (data.status === 'success') {
                callback = () => injectSelectAlbumList(); // Recarga la lista de álbumes
            } else if (data.status === 'errorSession') {
                callback = () => window.location.href = './index.php';
            } 
            
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            if (confirmDeleteModal) confirmDeleteModal.hide();
            console.error('Error en la eliminación:', error);
            showStaticNotificationModal('error', 'Fallo de red o error al comunicarse con el servidor.', null);
        }
    };

    // --- Función para borrar Imagen  ---
    const deleteImage = async (imageId, albumIdToRefresh) => {
        let deleteEndpoint = './BACKEND/FuncionesPHP/eliminarImagen.php'; 
        let formData = new FormData();
        formData.append('imageId', imageId);
        
        try {
            let res = await fetch(deleteEndpoint, { method: "POST", body: formData });
            let data = await res.json(); 

            if (confirmDeleteModal) confirmDeleteModal.hide();
            
            let callback = null;
            if (data.status === 'success') {
                // Refrescar la lista de imágenes en el modal
                callback = () => injectAlbumImages(albumIdToRefresh, imagesModalInstance); 
            } else if (data.status === 'errorSession') {
                callback = () => window.location.href = './index.php';
            } 
            
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            if (confirmDeleteModal) confirmDeleteModal.hide();
            console.error('Error en la eliminación de imagen:', error);
            showStaticNotificationModal('error', 'Fallo de red o error al comunicarse con el servidor.', null);
        }
    };

    // --- Función para Establecer Portada de Álbum ---
    const setCoverImage = async (imageId, albumId) => {
        let formData = new FormData();
        formData.append('imageId', imageId);
        formData.append('albumId', albumId);

        try {
            const res = await fetch('./BACKEND/FuncionesPHP/establecerPortadaAlbum.php', { method: 'POST', body: formData });
            const data = await res.json();

            let callback = null;
            if (data.status === 'success') {
                // Refrescar la lista de ÁLBUMES (para que se vea la nueva portada)
                callback = () => injectSelectAlbumList();
            } else if (data.status === 'errorSession') {
                callback = () => window.location.href = './index.php';
            }
            
            if (imagesModalInstance) imagesModalInstance.hide();
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            console.error('Error al establecer portada:', error);
            showStaticNotificationModal('error', 'Fallo de red al establecer portada.', null);
        }
    };

    // --- Función para Usar como Foto de Perfil ---
    const setProfileImage = async (imageId) => {
        let formData = new FormData();
        formData.append('imageId', imageId); 
        
        try {
            const res = await fetch('./BACKEND/FuncionesPHP/updateProfileImage.php', { method: 'POST', body: formData });
            const data = await res.json();

            let callback = null;
            if (data.status === 'success') {
                // Esta acción requiere recargar la página para ver el cambio en todos lados
                callback = () => window.location.reload(); 
            } else if (data.status === 'errorSession') {
                callback = () => window.location.href = './index.php';
            }

            if (imagesModalInstance) imagesModalInstance.hide();
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            console.error('Error al usar como perfil:', error);
            showStaticNotificationModal('error', 'Fallo de red al usar como perfil.', null);
        }
    };

    // =========================================================================
    // 4.  FUNCIÓN PARA INYECTAR IMÁGENES EN EL MODAL
    // =========================================================================
    
    const injectAlbumImages = (albumId, modalInstance) => {
        if (!modalInstance || !imagesModalContainer || !imagesModalErrorDiv) {
            console.error("Faltan elementos del modal de imágenes.");
            return;
        }

        // 1. Mostrar Spinner
        imagesModalContainer.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando imágenes...</p>`;
        
        // 2. Limpiar errores (asumiendo que #errorImagesAlbum tiene clase .error)
        limpiarErrores(); 

        async function fetchAlbumImages() {
            try {
                let formData = new FormData();
                formData.append('albumId', albumId);
                // Pasamos las variables globales al backend para que filtre
                formData.append('isMyProfile', isMyProfile); // estas dos variables vienen de profile.php
                formData.append('profileUserId', user_id); // ID del perfil que se está viendo

                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerImagenesAlbum.php`, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) throw new Error('Fallo al obtener las imágenes del álbum.');
                
                // El PHP ya debe devolver las imágenes filtradas según la lógica
                const images = await response.json(); 
                
                let imagesHTML = '';
                if (images.length === 0) {
                    imagesHTML = `<div class="alert alert-info text-center mt-3">Este álbum no tiene imágenes.</div>`;
                } else {
                    imagesHTML = `<div class="row row-cols-2 row-cols-md-3 g-3">`; // Grid

                    images.forEach(image => {
                        const dropdownId = `imageMenu-${image.I_id}`;
                        let menuHTML = '';

                        // 3. Lógica de menú de 3 puntos (solo si es mi perfil)
                        if (isMyProfile) {
                            menuHTML = `
                                <div class="dropdown position-absolute top-0 end-0 m-1" style="z-index: 10;">
                                    <button class="btn btn-sm btn-light py-0 px-1 rounded-circle" type="button" 
                                            id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="uil uil-ellipsis-v fs-6"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="${dropdownId}">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="#" data-action="set-cover" data-image-id="${image.I_id}" data-album-id="${albumId}">
                                                <i class="uil uil-image-check me-2"></i> Definir como portada
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="#" data-action="set-profile" data-image-id="${image.I_id}">
                                                <i class="uil uil-user-check me-2"></i> Usar de Perfil
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-action="delete-image" data-image-id="${image.I_id}">
                                                <i class="uil uil-trash-alt me-2"></i> Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            `;
                        }

                        // 4. Tarjeta de imagen
                        imagesHTML += `
                            <div class="col">
                                <div class="profile-image-card position-relative"> 
                                    ${menuHTML}
                                    <img src="${image.I_ruta}" alt="${image.I_title || 'Imagen del álbum'}" 
                                         class="img-fluid rounded" 
                                         style="aspect-ratio: 1 / 1; object-fit: cover; cursor: pointer;"
                                         data-action="view-single-image"
                                         data-image-id="${image.I_id}">
                                </div>
                            </div>
                        `;
                    });
                    imagesHTML += `</div>`; // Cierre .row
                }

                // 5. Renderizar y adjuntar listeners
                imagesModalContainer.innerHTML = imagesHTML;

                // --- A. Listener para Establecer Portada ---
                imagesModalContainer.querySelectorAll('a[data-action="set-cover"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const imgId = e.currentTarget.dataset.imageId;
                        const albId = e.currentTarget.dataset.albumId;
                        setCoverImage(imgId, albId);
                    });
                });

                // --- B. Listener para Establecer Perfil ---
                imagesModalContainer.querySelectorAll('a[data-action="set-profile"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const imgId = e.currentTarget.dataset.imageId;
                        setProfileImage(imgId);
                    });
                });

                // --- C. Listener para Borrar Imagen (misma lógica de modal) ---
                imagesModalContainer.querySelectorAll('a[data-action="delete-image"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const idToDelete = e.currentTarget.dataset.imageId;
                        let confirmBtn = document.getElementById('confirmDeleteButton'); // Reusamos el botón

                        if (confirmDeleteModal && confirmBtn) {
                            let newConfirmBtn = confirmBtn.cloneNode(true);
                            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                            let finalConfirmBtn = document.getElementById('confirmDeleteButton');
                            
                            finalConfirmBtn.addEventListener('click', () => {
                                deleteImage(idToDelete, albumId); // Pasa el albumId para el refresh
                            }, { once: true });
                            
                            confirmDeleteModal.show();
                        }
                    });
                });

                // --- D. Listener para ver imagen (Futuro) ---
                imagesModalContainer.querySelectorAll('img[data-action="view-single-image"]').forEach(img => {
                    img.addEventListener('click', (e) => {
                        const imgId = e.currentTarget.dataset.imageId;
                        console.log(`TODO: Abrir modal de vista única para la imagen ${imgId}`);
                        // Aquí llamarías a: openSingleImageView(imgId);
                    });
                });

            } catch (error) {
                mostrarError(imagesModalErrorDiv, null, "Error al cargar imágenes: " + error.message);
                imagesModalContainer.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar las imágenes.</p>`;
            }
        }

        // 6. Ejecutar Fetch y Mostrar Modal
        fetchAlbumImages();
        modalInstance.show(); 
    };


    // =========================================================================
    // 5. FUNCIÓN INICIAL (MODIFICADA)
    // =========================================================================

    function injectSelectAlbumList() {
        
        const container = document.getElementById("myAlbumsProfileSection");
        const errorDiv = document.getElementById('errorMyAlbumsProfile'); 
        
        container.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando álbumes...</p>`;
        limpiarErrores();

        async function fetchAlbums() {
            let formData = new FormData();
            formData.append('user_id', user_id); // user_id global
            try {
                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerAlbums.php`, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) throw new Error('Fallo al obtener los álbumes.');

                let albums = await response.json(); // Declarado con let para reasignar
                
                if(!isMyProfile){
                    // Filtramos si NO es mi perfil
                    albums = albums.filter(album => album.A_isSystemAlbum == 0);
                }

                let albumsHTML = '';
                
                if (albums.length === 0) {
                    // (HTML de 'empty-albums'...)
                    albumsHTML =    `<div class="empty-albums">
                                        <i class="uil uil-folder-slash"></i>
                                        <h5>No tienes álbumes todavía</h5>
                                        <p class="mb-3">¡Crea tu primer álbum y comparte tus artesanías!</p>
                                        <button class="btn btn-secondary" id="createFirstAlbum" data-bs-toggle="modal" data-bs-target="#createAlbumModal">
                                            <i class="uil uil-plus-circle me-1"></i>
                                            <br> Crear Álbum
                                        </button>
                                    </div>`;
                } else {
                    albumsHTML = `
                        <div class="albums-scroll-container" style="max-height: 1000px;"> 
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-4">
                    `;
                    
                    albums.forEach(album => {
                        const dropdownId = `albumProfileMenu-${album.A_id}`; 
                        const showDeleteOption = album.A_isSystemAlbum != 1;
                        const imageHtml = album.A_cover 
                            ? `<img src="${album.A_cover}" alt="${album.A_title}" class="album-card-image">`
                            : `<div class="d-flex align-items-center justify-content-center h-100">
                                <i class="uil uil-image-slash" style="font-size: 4rem; color: var(--secondary-text);"></i>
                            </div>`;

                        albumsHTML += `
                            <div class="col">
                                <input type="radio" class="btn-check album-radio" 
                                    name="myAlbumsProfileId" 
                                    id="myAlbumsProfile-${album.A_id}" 
                                    value="${album.A_id}"  
                                    autocomplete="off">
                                
                                <label class="btn btn-outline-secondary p-0 w-100 h-100 album-card-select position-relative" 
                                       for="myAlbumsProfile-${album.A_id}" 
                                       data-album-id="${album.A_id}"
                                       data-album-title="${album.A_title}"
                                       style="border: none">`;
                                
                                if(isMyProfile) { 
                                    albumsHTML += `
                                    <div class="dropdown position-absolute top-0 end-0 m-1" style='z-index:100;'>
                                        <button class="btn btn-sm btn-light py-0 px-1 rounded-circle" type="button" 
                                                id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="uil uil-ellipsis-v fs-6"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="${dropdownId}">
                                            
                                            ${showDeleteOption ? `
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center text-danger" href="#" 
                                                    data-album-id="${album.A_id}" data-action="deleteAlbumProfile">
                                                    <i class="uil uil-trash-alt me-2"></i> Eliminar Álbum
                                                </a>
                                            </li>
                                            ` : ''}
                                        </ul>
                                    </div>
                                    `;
                                } 
                                
                                albumsHTML += `
                                    <div class="album-card position-relative overflow-hidden w-100 h-100">
                                        ${imageHtml}
                                        <div class="album-card-overlay">
                                            <h3 class="album-card-title">${album.A_title}</h3>
                                            <div class="album-card-info">
                                                ${album.A_count} imagen${album.A_count != 1 ? 'es' : ''}
                                            </div>
                                        </div>
                                        <div class="selection-indicator"></div>
                                    </div>
                                </label>
                            </div>
                        `;
                    });
                    albumsHTML += `</div></div>`;
                }
                
                container.innerHTML = albumsHTML;

                // --- Listener de borrado de ÁLBUM ---
                document.querySelectorAll('a[data-action="deleteAlbumProfile"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation(); // <-- AÑADIDO: Evita que el label reciba el clic
                        
                        let idToDelete = e.currentTarget.dataset.albumId;
                        let confirmBtn = document.getElementById('confirmDeleteButton');
                        
                        if (confirmDeleteModal && confirmBtn) {
                            let newConfirmBtn = confirmBtn.cloneNode(true);
                            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                            let finalConfirmBtn = document.getElementById('confirmDeleteButton');
                            
                            finalConfirmBtn.addEventListener('click', () => {
                                deleteAlbum(idToDelete);
                            }, { once: true });
                            
                            confirmDeleteModal.show();
                        }
                    });
                });
                
                // --- Listener para abrir modal de imágenes ---
                document.querySelectorAll('label.album-card-select').forEach(label => {
                    label.addEventListener('click', (e) => {
                        
                        // Si el clic fue en el dropdown (para borrar), no hacemos nada
                        if (e.target.closest('.dropdown')) {
                            return;
                        }
                        
                        if (imagesModalInstance) {
                            const albumId = label.dataset.albumId;
                            const albumTitle = label.dataset.albumTitle;
                            
                            // Poner el título en el modal
                            if(imagesModalTitleEl) imagesModalTitleEl.textContent = albumTitle;

                            // Cargar las imágenes
                            injectAlbumImages(albumId, imagesModalInstance);
                        }
                    });
                });
                
            } catch (error) {
                mostrarError(errorDiv, null, "Error al cargar álbumes: " + error.message);
                container.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar los álbumes.</p>`;
            }
        }
        
        fetchAlbums();
    }
    
    // =========================================================================
    // 6. LLAMADA INICIAL
    // =========================================================================
    injectSelectAlbumList();

});