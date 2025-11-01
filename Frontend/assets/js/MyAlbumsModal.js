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
 
    const showStaticNotificationModal = (type, message, acceptCallback = null) => {
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

    const confirmDeleteModalEl = document.getElementById('confirmDeleteModal');
    const confirmDeleteModal = confirmDeleteModalEl ? (bootstrap.Modal.getInstance(confirmDeleteModalEl) || new bootstrap.Modal(confirmDeleteModalEl)) : null;

    const myAlbumsModalEl = document.getElementById('myAlbumsModal');

    const imagesModalEl = document.getElementById('imagesAlbumModal');
    const imagesModalInstance = imagesModalEl ? (bootstrap.Modal.getInstance(imagesModalEl) || new bootstrap.Modal(imagesModalEl)) : null;
    const imagesModalTitleEl = document.getElementById('imagesAlbumLabel');
    const imagesModalContainer = document.getElementById('imagesAlbumContainer');
    const imagesModalErrorDiv = document.getElementById('errorImagesAlbum');


    // =========================================================================
    // 3. FUNCIONES DE ACCIÓN (FETCH A LA BD)
    // =========================================================================

    const deleteAlbum = async (albumId) => {
        let deleteEndpoint = './BACKEND/FuncionesPHP/eliminarAlbum.php'; 
        let formData = new FormData();
        formData.append('albumId', albumId);
        
        try {
            let res = await fetch(deleteEndpoint, { method: "POST", body: formData });
            let data = await res.json(); 

            if (confirmDeleteModal) confirmDeleteModal.hide();
            
            let callback = null;
            if (data.status === 'success') {
                callback = () => injectSelectAlbumList(); 
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
                callback = () => injectAlbumImages(albumIdToRefresh, imagesModalInstance); 
            } else if (data.status === 'errorSession') {
                callback = () => window.location.href = './index.php';
            } 
            
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            if (confirmDeleteModal) confirmDeleteModal.hide();
            console.error('Error en la eliminación de imagen:', error);
            showStaticNotificationModal('error', 'Fallo de red al borrar imagen.', null);
        }
    };

    const setCoverImage = async (imageId, albumId) => {
        let formData = new FormData();
        formData.append('imageId', imageId);
        formData.append('albumId', albumId);

        try {
            const res = await fetch('./BACKEND/FuncionesPHP/establecerPortadaAlbum.php', { method: 'POST', body: formData });
            const data = await res.json();

            let callback = null;
            if (data.status === 'success') {
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

    const setProfileImage = async (imageId) => {
        let formData = new FormData();
        formData.append('imageId', imageId); 
        
        try {
            const res = await fetch('./BACKEND/FuncionesPHP/actualizarFotoPerfil.php', { method: 'POST', body: formData });
            const data = await res.json();

            let callback = null;
            if (data.status === 'success') {
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
    // 4. FUNCIÓN PARA INYECTAR IMÁGENES
    // =========================================================================
    
    const injectAlbumImages = (albumId, modalInstance) => {
        if (!modalInstance || !imagesModalContainer || !imagesModalErrorDiv) {
            console.error("Faltan elementos del modal de imágenes.");
            return;
        }

        imagesModalContainer.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando imágenes...</p>`;
        limpiarErrores(); 

        async function fetchAlbumImages() {
            try {
                let formData = new FormData();
                formData.append('albumId', albumId);
                
                // Usamos la variable global de modals.php
                formData.append('isMyProfile', true); 
                formData.append('profileUserId', logged_in_user_id);
                
                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerImagenesAlbum.php`, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) throw new Error('Fallo al obtener las imágenes del álbum.');
                
                const images = await response.json(); 
                
                let imagesHTML = '';
                if (images.length === 0) {
                    imagesHTML = `<div class="alert alert-info text-center mt-3">Este álbum no tiene imágenes.</div>`;
                } else {
                    imagesHTML = `<div class="row row-cols-2 row-cols-md-3 g-3">`; 

                    images.forEach(image => {
                        const dropdownId = `imageMenu-${image.I_id}`;
                        let menuHTML = '';
                        // Como este modal siempre es "mío", mostramos el menú siempre a no ser que sea de sistema
                        if (image.isSystemAlbum != 1) {
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
                        // Tarjeta de imagen
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

                // --- C. Listener para Borrar Imagen (Usa tu lógica de modal) ---
                imagesModalContainer.querySelectorAll('a[data-action="delete-image"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault(); e.stopPropagation();
                        const idToDelete = e.currentTarget.dataset.imageId;
                        let confirmBtn = document.getElementById('confirmDeleteButton'); 

                        let confirmDeleteModalMessage = document.getElementById('deleteMessage');
                        if (confirmDeleteModalMessage) {
                            confirmDeleteModalMessage.innerHTML = "¿Estás seguro de que deseas eliminar esta IMAGEN?";
                        }

                        if (confirmDeleteModal && confirmBtn) {
                            let newConfirmBtn = confirmBtn.cloneNode(true);
                            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                            let finalConfirmBtn = document.getElementById('confirmDeleteButton');
                            
                            finalConfirmBtn.addEventListener('click', () => {
                                deleteImage(idToDelete, albumId); 
                            }, { once: true });
                            
                            confirmDeleteModal.show();
                        }
                    });
                });

                // --- D. Listener para ver imagen (con fix de stack) ---
                imagesModalContainer.querySelectorAll('img[data-action="view-single-image"]').forEach(img => {
                    img.addEventListener('click', (e) => {
                        const imgId = e.currentTarget.dataset.imageId;
                        const currentModalEl = document.getElementById('imagesAlbumModal');
                        if (!currentModalEl) return;
                        
                        const currentModalInstance = bootstrap.Modal.getInstance(currentModalEl);
                        if (!currentModalInstance) return;

                        currentModalEl.addEventListener('hidden.bs.modal', () => {
                            if (typeof openImageModal === 'function') {
                                openImageModal(imgId);
                            } else {
                                console.error('La función openImageModal no está definida.');
                            }
                        }, { once: true }); 
                        currentModalInstance.hide();
                    });
                });

            } catch (error) {
                mostrarError(imagesModalErrorDiv, null, "Error al cargar imágenes: " + error.message);
                imagesModalContainer.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar las imágenes.</p>`;
            }
        }
        
        fetchAlbumImages();
        modalInstance.show(); 
    };


    // =========================================================================
    // 5. FUNCIÓN INICIAL (MODIFICADA CON FILTROS)
    // =========================================================================
    
    function injectSelectAlbumList() {
        
        const container = document.getElementById("myAlbumsContainer");
        const errorDiv = document.getElementById('errorMyAlbums'); 
        
        container.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando álbumes...</p>`;
        limpiarErrores();

        async function fetchAlbums() {
            
            // --- Leer el filtro seleccionado del modal ---
            let filterValue = 'all'; 
            const checkedFilter = document.querySelector('#modalAlbumFilters input[name="albumFilterModal"]:checked');
            if (checkedFilter) {
                filterValue = checkedFilter.value;
            }
            
            let formData = new FormData();
            formData.append('filterType', filterValue);
            // (No enviamos user_id, el backend usará la sesión)

            try {
                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerAlbums.php`, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) throw new Error('Fallo al obtener los álbumes.');

                const albums = await response.json();
                
                let albumsHTML = '';
                
                if (albums.length === 0) {
                    albumsHTML = `<div class="alert alert-info text-center mt-3">No tienes álbumes existentes.</div>`;
                } else {
                    albumsHTML = `
                        <div class="albums-scroll-container"> 
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-4">
                    `;
                    
                    albums.forEach(album => {
                        const dropdownId = `albumMenu-${album.A_id}`; 
                        const showDeleteOption = album.A_isSystemAlbum != 1;
                        const imageHtml = album.A_cover 
                            ? `<img src="${album.A_cover}" alt="${album.A_title}" class="album-card-image">`
                            : `<div class="d-flex align-items-center justify-content-center h-100">
                                <i class="uil uil-image-slash" style="font-size: 4rem; color: var(--secondary-text);"></i>
                            </div>`;

                        albumsHTML += `
                            <div class="col">
                                <input type="radio" class="btn-check album-radio" 
                                    name="myAlbumsId" 
                                    id="myAlbums-${album.A_id}" 
                                    value="${album.A_id}"  
                                    autocomplete="off">
                                
                                <label class="btn btn-outline-secondary p-0 w-100 h-100 album-card-select position-relative" 
                                       for="myAlbums-${album.A_id}" 
                                       data-album-id="${album.A_id}"
                                       data-album-title="${album.A_title}"
                                       style="border: none; cursor: pointer;">
                                    
                                    <div class="dropdown position-absolute top-0 end-0 m-1" style='z-index:100;'>
                                        <button class="btn btn-sm p-0 border-0" type="button" 
                                                id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="uil uil-ellipsis-h fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="${dropdownId}">
                                            
                                            ${showDeleteOption ? `
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center text-danger" href="#" 
                                                    data-album-id="${album.A_id}" data-action="delete-album">
                                                    <i class="uil uil-trash-alt me-2"></i> Eliminar Álbum
                                                </a>
                                            </li>
                                            ` : ''}
                                        </ul>
                                    </div>

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


                // --- Listener de borrado de ÁLBUM (con mensaje) ---
                container.querySelectorAll('a[data-action="delete-album"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation(); 
                        
                        let idToDelete = e.currentTarget.dataset.albumId;
                        let confirmBtn = document.getElementById('confirmDeleteButton');
                        
                        let confirmDeleteModalMessage = document.getElementById('deleteMessage');
                        if (confirmDeleteModalMessage) {
                            confirmDeleteModalMessage.innerHTML = "¿Estás seguro de que deseas eliminar este ÁLBUM y TODAS SUS IMÁGENES?";
                        }
                        
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
                
                // --- Listener para ABRIR modal de imágenes ---
                container.querySelectorAll('label.album-card-select').forEach(label => {
                    label.addEventListener('click', (e) => {
                        
                        if (e.target.closest('.dropdown')) {
                            return;
                        }
                        
                        if (imagesModalInstance) {
                            const albumId = label.dataset.albumId;
                            const albumTitle = label.dataset.albumTitle;
                            
                            if(imagesModalTitleEl) imagesModalTitleEl.textContent = albumTitle;
                            injectAlbumImages(albumId, imagesModalInstance);

                            const currentModalInstance = bootstrap.Modal.getInstance(myAlbumsModalEl);
                            if (currentModalInstance) {
                                currentModalInstance.hide();
                            }
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
    // 6. LLAMADA INICIAL (Se dispara al abrir el modal)
    // =========================================================================

    if (myAlbumsModalEl) {
        myAlbumsModalEl.addEventListener('shown.bs.modal', (e) => {
            injectSelectAlbumList();
        });

        myAlbumsModalEl.addEventListener('hidden.bs.modal', (e) => {
            const container = document.getElementById("myAlbumsContainer");
            if (container) {
                container.innerHTML = "";
            }
        });
    }

    const modalFilterContainer = document.getElementById('modalAlbumFilters');
    if (modalFilterContainer) {
        const filterRadios = modalFilterContainer.querySelectorAll('.btn-check');
        
        filterRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                injectSelectAlbumList(); 
            });
        });
    }
});