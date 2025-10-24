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
 
    const showStaticNotificationModal = (type, message, acceptCallback = null) => {
        let modalEl = document.getElementById('staticNotificationModal');
        let modalIcon = document.getElementById('notificationIconStatic');
        let modalMessage = document.getElementById('notificationMessageStatic');
        let acceptBtn = document.getElementById('notificationAcceptButton');
        
        if (!modalEl || !modalIcon || !modalMessage || !acceptBtn) return;

        // Configurar estilos y contenido
        let modalContent = modalEl.querySelector('.modal-content');
        
        // Limpiamos clases de estado
        modalContent.classList.remove('alert-success', 'alert-danger');
        
        if (type === 'success') {
            modalIcon.innerHTML = '🎉';
            modalContent.classList.add('alert-success');
        } else {
            modalIcon.innerHTML = '⚠️';
            modalContent.classList.add('alert-danger');
        }
        
        modalMessage.textContent = message;
        
        // 💡 1. Limpiamos y recreamos el listener del botón Aceptar
        // Clonar para eliminar listeners antiguos
        let newAcceptBtn = acceptBtn.cloneNode(true);
        acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);
        
        let finalAcceptBtn = document.getElementById('notificationAcceptButton');
        let staticModalInstance = new bootstrap.Modal(modalEl); // Creamos la instancia para mostrar

        finalAcceptBtn.addEventListener('click', () => {
            // 2. Ejecutar la acción de callback (redirección/recarga)
            if (acceptCallback) {
                acceptCallback();
            }
            // 3. Cerrar el modal (si la acción no fue una redirección que ya lo cerraría)
            staticModalInstance.hide();
        });

        // Mostrar el modal
        staticModalInstance.show();
    };


    // Objeto Modal
    const confirmDeleteModalEl = document.getElementById('confirmDeleteModal');
    const confirmDeleteModal = confirmDeleteModalEl ? (bootstrap.Modal.getInstance(confirmDeleteModalEl) || new bootstrap.Modal(confirmDeleteModalEl)) : null;

    

    const deleteAlbum = async (albumId) => {
        
        
        // 💡 Endpoint de la API
        let deleteEndpoint = './BACKEND/FuncionesPHP/eliminarAlbum.php'; 
        
        let formData = new FormData();
        formData.append('albumId', albumId);
        
        try {
            let res = await fetch(deleteEndpoint, { method: "POST", body: formData });
            let responseText = await res.text();
            let data = JSON.parse(responseText);

            // Ocultar el modal de confirmación antes de mostrar la notificación
            if (confirmDeleteModal) confirmDeleteModal.hide();
            
            let callback = null;
            
            if (data.status === 'success') {
                // 🚀 ACCIÓN REQUERIDA: Invocar injectSelectAlbumList() para recargar la lista
                callback = () => injectSelectAlbumList(); 
                
            } else if (data.status === 'errorSession') {
                // 🚀 ACCIÓN REQUERIDA: Redireccionar al index (tras logout)
                callback = () => window.location.href = '../../index.php'; // Ajusta la ruta si es necesario
            } 
            // Si data.status es 'error', callback se mantiene como null (no hace nada al aceptar)
            
            showStaticNotificationModal(data.status, data.message, callback);

        } catch (error) {
            if (confirmDeleteModal) confirmDeleteModal.hide();
            console.error('Error en la eliminación:', error);
            // Error de red: No hay callback (no hace nada)
            showStaticNotificationModal('error', 'Fallo de red o error al comunicarse con el servidor.', null);
        }
    };


    function injectSelectAlbumList() {
        
        const container = document.getElementById("myAlbumsProfileSection");
        const errorDiv = document.getElementById('errorMyAlbumsProfile'); 
        
        // Mostramos un spinner de carga mientras se obtienen los datos
        container.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando álbumes...</p>`;
        
        // Limpiamos errores previos (Asumo que limpiarErrores() está accesible)
        limpiarErrores();

        async function fetchAlbums() {
            try {
                // Asumo que el fetch ahora incluye el idUser si es necesario:
                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerAlbums.php`); 

                if (!response.ok) {
                    throw new Error('Fallo al obtener los álbumes.');
                }

                const albums = await response.json();
                
                // Filtramos solo los álbumes que NO son del sistema (A_isSystemAlbum = 0)
                // const filteredAlbums = albums.filter(album => album.A_isSystemAlbum == 0);

                let albumsHTML = '';
                
                if (albums.length === 0) {
                    albumsHTML =    `<div class="empty-albums">
                                        <i class="uil uil-folder-slash"></i>
                                        <h5>No tienes álbumes todavía</h5>
                                        <p class="mb-3">¡Crea tu primer álbum y comparte tus artesanías!</p>
                                        <button class="btn btn-secondary" id="createFirstAlbum">
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
                        // Usamos un radio button oculto + label con estilo de tarjeta
                        // ID único para el botón del dropdown de cada álbum
                        const dropdownId = `albumProfileMenu-${album.A_id}`; 
                        
                        // Lógica para determinar si mostrar la opción de borrar
                        // (Asumimos que solo puedes borrar si NO es un álbum del sistema)
                        const showDeleteOption = album.A_isSystemAlbum != 1;

                        // Prepara el HTML de la imagen/placeholder de tu compañera
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
                                
                                <label class="btn btn-outline-secondary p-0 w-100 h-100 album-card-select position-relative" for="myAlbumsProfile-${album.A_id}" style="border: none">
                                    
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
                                                    data-album-id="${album.A_id}" data-action="deleteAlbumProfile">
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
                    albumsHTML += `</div></div>`; // Cierre del row y del albums-scroll-container
                }
                
                container.innerHTML = albumsHTML;


                // ----------------------------------------------------------------------------------
                // 💡 CRÍTICO: Adjuntar listener para el borrado (se hace al final del renderizado)
                // ----------------------------------------------------------------------------------
                
               
                document.querySelectorAll('a[data-action="deleteAlbumProfile"]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        
                        let idToDelete = e.currentTarget.dataset.albumId;
                        let confirmBtn = document.getElementById('confirmDeleteButton');
                        
                        if (confirmDeleteModal && confirmBtn) {
                            
                            // 1. Limpiamos el listener anterior y adjuntamos el nuevo ID
                            let newConfirmBtn = confirmBtn.cloneNode(true);
                            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                            
                            let finalConfirmBtn = document.getElementById('confirmDeleteButton');
                            
                            // 2. Adjuntar el listener de confirmación al nuevo botón
                            finalConfirmBtn.addEventListener('click', () => {
                                deleteAlbum(idToDelete); // Llama a la función de fetch para borrar
                            }, { once: true }); // Usamos { once: true } para que el listener se elimine solo
                            
                            // 3. Mostrar el modal
                            confirmDeleteModal.show();
                        }
                    });
                });
                
            } catch (error) {
                // Asumo que mostrarError está accesible
                mostrarError(errorDiv, null, "Error al cargar álbumes: " + error.message);
                container.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar los álbumes.</p>`;
            }
        }
        
        fetchAlbums();
    }
    
    injectSelectAlbumList();

    


});