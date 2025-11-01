document.addEventListener("DOMContentLoaded", () => {

     // Función para limpiar todos los errores visuales
    const limpiarErrores = () => {
        document.querySelectorAll(".error").forEach(div => {
            div.textContent = "";
            div.classList.remove("visible-error");
        });
        document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
    };
    // Mostrar errores
    const mostrarError = (div, input, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
        if (input) input.classList.add("errorInput");
    };

    //Muestra el modal de notificación fijo con el contenido dinámico y adjunta una acción de callback.
 


    // Objeto Modal
    const confirmSetProfileImageModalEl = document.getElementById('confirmSetProfileImageModal');
    const confirmSetProfileImageModal = confirmSetProfileImageModalEl ? (bootstrap.Modal.getInstance(confirmSetProfileImageModalEl) || new bootstrap.Modal(confirmSetProfileImageModalEl)) : null;
    
    const setProfilePicture = async (imageId) => {
        
        
        // 💡 Endpoint de la API
        let updateProfileImageEndpoint = './BACKEND/FuncionesPHP/updateProfileImage.php';
        
        let formData = new FormData();
        formData.append('imageId', imageId);

        try {
            let res = await fetch(updateProfileImageEndpoint, { method: "POST", body: formData });
            let responseText = await res.text();
            let data = JSON.parse(responseText);

            // Ocultar el modal de confirmación antes de mostrar la notificación
            if (confirmSetProfileImageModal) confirmSetProfileImageModal.hide();
            
            let callback = null;
            
            if (data.status === 'success') {
                // OTRA OPCION: 🚀 ACCIÓN REQUERIDA: Invocar injectProfileImagesList() para recargar la lista
                //callback = () => injectProfileImagesList();
                callback = () => window.location.href = './profile.php'; //Recargar pagina profile
            } else if (data.status === 'errorSession') {
                // 🚀 ACCIÓN REQUERIDA: Redireccionar al index (tras logout)
                callback = () => window.location.href = './index.php'; // Ajusta la ruta si es necesario
            } 
            // Si data.status es 'error', callback se mantiene como null (no hace nada)
            
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            if (confirmSetProfileImageModal) confirmSetProfileImageModal.hide();
            console.error('Error en la actualización:', error);
            // Error de red: No hay callback (no hace nada)
            showStaticNotificationModal('error', 'Fallo de red o error al comunicarse con el servidor.', null);
        }
    };


    /**
     * Inyecta la lista de imágenes de perfil de un usuario en un contenedor.
     * Asume que el backend filtra las imágenes que son de perfil (ej: I_isProfile = 1).
     */
    function injectProfileImagesList() {
        
        // 1. Define tus contenedores (asegúrate de que existan en tu modal/HTML)
        const container = document.getElementById("profileHistoryContainer");
        const errorDiv = document.getElementById("errorProfileHistory"); 

        if (!container || !errorDiv) {
            console.error("Faltan los contenedores 'profileHistoryContainer' o 'errorProfileHistory' en el DOM.");
            return;
        }

        // 2. Mostramos un spinner de carga
        container.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando imágenes...</p>`;
        
        // 3. Limpiamos errores previos (asumiendo que tienes esta función)
        // if (typeof limpiarErrores === 'function') limpiarErrores();

        async function fetchImages() {
            try {
                // 4. Hacemos el fetch al endpoint
                const response = await fetch(`./BACKEND/FuncionesPHP/getProfileImages.php`); 

                if (!response.ok) {
                    throw new Error('Fallo al obtener las imágenes de perfil.');
                }

                const images = await response.json();
                
                let imagesHTML = '';
                
                if (images.length === 0) {
                    imagesHTML = `<div class="alert alert-info text-center mt-3">No tienes imágenes de perfil subidas.</div>`;
                } else {
                    
                    // 5. Contenedor con scroll y grid
                    imagesHTML = `
                        <div class="profile-images-scroll-container"> 
                            
                            <div class="row row-cols-2 row-cols-md-4 g-3">
                    `;
                    
                    images.forEach(image => {
                        const dropdownId = `profileMenu-${image.I_id}`;
                        const isCurrentProfile = image.I_currentProfile == 1;

                        imagesHTML += `
                            <div class="col">
                                <div class="profile-image-card position-relative ${isCurrentProfile ? 'is-current' : ''}">
                                    
                                    <div class="dropdown position-absolute top-0 end-0 m-1" style="z-index: 10;">
                                        <button class="btn btn-sm btn-light py-0 px-1 rounded-circle" type="button" 
                                                id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="uil uil-ellipsis-v fs-6"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="${dropdownId}">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center ${isCurrentProfile ? 'disabled' : ''}" 
                                                href="#" 
                                                data-image-id="${image.I_id}" 
                                                data-action="set-profile">
                                                    
                                                    ${isCurrentProfile 
                                                        ? '<i class="uil uil-check me-2"></i> Actual' 
                                                        : '<i class="uil uil-user-check me-2"></i> Definir como perfil'
                                                    }
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <img src="${image.I_ruta}" alt="Imagen de perfil" 
                                        class="img-fluid rounded" 
                                        style="aspect-ratio: 1 / 1; object-fit: cover; cursor: pointer;"
                                        data-action="view-image">
                                    
                                    ${isCurrentProfile ? '<div class="current-profile-indicator"><i class="uil uil-check"></i></div>' : ''}
                                </div>
                            </div>
                        `;
                    });
                    
                    imagesHTML += `</div></div>`; // Cierre del row y del scroll-container
                }
                
                    // 8. Renderizamos el HTML
                    container.innerHTML = imagesHTML;

                    // 9. Adjuntamos los listeners para la acción "Definir como perfil"
                    document.querySelectorAll('a[data-action="set-profile"]').forEach(link => {
                        if (link.classList.contains('disabled')) return; // No adjuntar listener si está deshabilitado

                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation(); // Evita que se cierre el dropdown si acaso
                            
                            let idToSet = e.currentTarget.dataset.imageId;
                            let confirmBtn = document.getElementById('confirmSetProfileImageButton');

                            if (confirmSetProfileImageModal && confirmBtn) {

                                // 1. Limpiamos el listener anterior y adjuntamos el nuevo ID
                                let newConfirmBtn = confirmBtn.cloneNode(true);
                                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

                                let finalConfirmBtn = document.getElementById('confirmSetProfileImageButton');
                                
                                // 2. Adjuntar el listener de confirmación al nuevo botón
                                finalConfirmBtn.addEventListener('click', () => {
                                    setProfilePicture(idToSet); // Llama a la función que hace el fetch para actualizar la DB
                                }, { once: true }); // Usamos { once: true } para que el listener se elimine solo
                                
                                // 3. Mostrar el modal
                                confirmSetProfileImageModal.show();
                            }
                        });
                    });

                    // 10. Adjuntar listeners para VER la imagen en el modal
                    const modalViewImage = document.getElementById('imgProfilePicHistory');
                    const modalViewElement = document.getElementById('viewProfilePicHistoryModal');

                    if (modalViewImage && modalViewElement) {
                        // Creamos la instancia del modal de Bootstrap
                        const viewModal = new bootstrap.Modal(modalViewElement);

                        document.querySelectorAll('img[data-action="view-image"]').forEach(img => {
                            img.addEventListener('click', (e) => {
                                const imageUrl = e.currentTarget.src;
                                
                                // 1. Asignamos la ruta de la imagen al <img> del modal
                                modalViewImage.src = imageUrl;
                                
                                // 2. Mostramos el modal
                                viewModal.show();
                            });
                        });
                    } else {
                        console.warn("No se encontraron los elementos del modal #viewProfilePicHistoryModal o #imgProfilePicHistory.");
                    }
                    
            } catch (error) {
                // 10. Manejo de errores
                if (typeof mostrarError === 'function') mostrarError(errorDiv, null, "Error al cargar imágenes: " + error.message);
                console.error("Error al cargar imágenes: ", error);
                container.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar las imágenes de perfil.</p>`;
            }
        }
        
        fetchImages();
    }

    const profileHistoryModalEl = document.getElementById('profileHistoryModal');
    if (profileHistoryModalEl) {
        // Evento para cuando el modal se abre
        profileHistoryModalEl.addEventListener('shown.bs.modal', (e) => {
            injectProfileImagesList();
        });

        // Evento para cuando el modal se cierra
        profileHistoryModalEl.addEventListener('hidden.bs.modal', (e) => {
            const container = document.getElementById("profileHistoryContainer");
            container.innerHTML = "";
        });
    }

    


});