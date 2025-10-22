document.addEventListener("DOMContentLoaded", () => {

    // --------------------------------------------------------------------------------------
    // FUNCIONES DE UTILIDAD (Deben ser accesibles si no están en otro JS)
    // --------------------------------------------------------------------------------------

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

    const validarCampo = (input, regex, errorDiv, msg) => {
        let isValid = true;
        input.classList.remove("errorInput");

        if (!input?.value.trim()) { 
            mostrarError(errorDiv, input, "Campo obligatorio."); 
            isValid = false; 
        } else if (regex && !regex.test(input.value)) { 
            mostrarError(errorDiv, input, msg); 
            isValid = false; 
        }

        if (!isValid) {
            input.classList.add("errorInput");
        }
        return isValid;
    };



    function returnBasicFormData(){ //Retorna un formData con los datos basicmos de las imagenes
        const fileInputOriginal = document.getElementById('imageInput');

        if (!fileInputOriginal || fileInputOriginal.files.length === 0) {
            // Devuelve un FormData vacío si no hay archivos
            return new FormData(); 
        }
        
        // 1. Obtener los NodeLists de los elementos por su atributo name
        // (Esto solo funciona porque los inputs del carrusel tienen names como "titleImage[0]", etc.)
        const titleInputs = document.getElementsByName('titleImage[]');
        const visibilitySelects = document.getElementsByName('visibilityImage[]');

        let formData = new FormData();

        for (const file of fileInputOriginal.files) {
            formData.append('imageInput[]', file); 
        }

        Array.from(titleInputs).forEach(title => {
            formData.append('titleImage[]', title.value.trim() || ''); 
        });

        Array.from(visibilitySelects).forEach(visibility => {
            formData.append('visibilityImage[]', visibility.value); 
        });

        return formData;
    }

    // Función para mostrar el modal de notificación fijo
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



    // Función que necesita saber el índice activo del carrusel para la portada
    function setupCoverSelection() {
            const isCoverCheckbox = document.getElementById('isCoverCheckbox');
            const coverImageIndexInput = document.getElementById('coverImageIndex');
            
            // El carrusel está en el modal anterior, pero necesitamos saber qué slide está activa.
            // Usaremos el modal de carrusel (que ahora está oculto) para obtener la referencia.
            const selectImagesModalEl = document.getElementById('selectImages');

            if (isCoverCheckbox && coverImageIndexInput && selectImagesModalEl) {
                
                // 1. Obtener el índice de la slide visible actualmente en el carrusel
                // (Asumimos que el carrusel se ha detenido en la última slide activa cuando se ocultó)
                const getActiveCarouselIndex = () => {
                    const carouselContainer = selectImagesModalEl.querySelector('#carouselSelectImages');
                    if (!carouselContainer) return 0; // Si el carrusel no existe, por defecto es 0

                    const items = carouselContainer.querySelectorAll('.carousel-item');
                    let activeIndex = 0;
                    
                    items.forEach((item, index) => {
                        // Buscamos la clase 'active' para saber qué slide era la última que se vio.
                        if (item.classList.contains('active')) {
                            activeIndex = index;
                        }
                    });
                    return activeIndex;
                };

                // 2. Establecer el valor inicial del campo oculto al índice 0 (por defecto, la primera imagen)
                coverImageIndexInput.value = '0';

                // 3. Listener para el checkbox
                isCoverCheckbox.addEventListener('change', () => {
                    if (isCoverCheckbox.checked) {
                        // Si marca: La portada es la imagen que el usuario estaba viendo.
                        const activeIndex = getActiveCarouselIndex();
                        coverImageIndexInput.value = activeIndex.toString();
                    } else {
                        // Si desmarca: La portada vuelve a ser la primera imagen (índice 0).
                        coverImageIndexInput.value = '0';
                    }
                });
            }
            
            // Opcional: Para el botón 'Volver' del formulario de Título, puedes asegurar que el estado
            // del checkbox de portada se conserve si el usuario vuelve al carrusel.
        }
    
    // --------------------------------------------------------------------------------------
    // 💡 FUNCIÓN CLAVE: Inyecta el FORMULARIO DE TÍTULO (Opción "Crear")
    // --------------------------------------------------------------------------------------
    function injectCreateAlbumForm() {
        limpiarErrores();
        const createAlbumTitleHTML = 
        `<form id="createAlbumTitleForm" class="mt-3">    
            <div class="mb-4">
                <div class="form-groupLogin mb-3 position-relative">
                    <label for="albumTitleInput" class="form-label visually-hidden">Título del Álbum</label>
                    <input type="text" class="form-style form-control" 
                        placeholder="Título del Álbum (Máx 30 caracteres)" 
                        name="albumTitle" 
                        id="albumTitleInput" 
                        maxlength="30"
                        required>
                    <i class="input-icon uil uil-tag-alt"></i>
                </div>
                <div class="error" id="errorAlbumTitle"></div>
                
                <div class="form-check form-switch mt-3 mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" value="1" id="isCoverCheckbox" name="isCover">
                    <label class="form-check-label fw-semibold" for="isCoverCheckbox">
                        Usar la imagen actual como portada del nuevo álbum.
                    </label>
                    <p class="small text-muted mt-1">NOTA: Al desmarcar la opcion se utilizara la primer imagen como portada</p>
                </div>
                <input type="hidden" id="coverImageIndex" name="coverImageIndex" value="0">
            </div>
        </form>`;
        
        document.getElementById("optionAlbumContainer").innerHTML = createAlbumTitleHTML;
        setupCoverSelection(); 
    }
    
    // --------------------------------------------------------------------------------------
    // 💡 FUNCIÓN CLAVE: Inyecta la LISTA DE ÁLBUMES (Opción "Seleccionar Existente")
    // --------------------------------------------------------------------------------------
    // Asegúrate de que mostrarError esté definido en el scope global o accesible
// function mostrarError(div, input, msg) { ... } 
// const errorPostAlbum = document.getElementById('errorPostAlbum'); // debe ser accesible


    function injectSelectAlbumList() {
        
        const container = document.getElementById("optionAlbumContainer");
        const errorDiv = document.getElementById('errorPostAlbum'); 
        
        // Mostramos un spinner de carga mientras se obtienen los datos
        container.innerHTML = `<p class="text-center mt-3 text-secondary"><div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div> Cargando álbumes...</p>`;
        
        // Limpiamos errores previos
        limpiarErrores();

        // Convertimos a async/await para manejar el flujo de forma más limpia
        async function fetchAlbums() {
            try {
                const response = await fetch(`./BACKEND/FuncionesPHP/obtenerAlbums.php`);

                if (!response.ok) {
                    throw new Error('Fallo al obtener los álbumes.');
                }

                const albums = await response.json();
                
                // Filtramos solo los álbumes que NO son del sistema (A_isSystemAlbum = 0)
                const filteredAlbums = albums.filter(album => album.A_isSystemAlbum == 0);

                let albumsHTML = '';
                
                if (filteredAlbums.length === 0) {
                    albumsHTML = `<div class="alert alert-info text-center mt-3">No tienes álbumes existentes donde puedas publicar.</div>`;
                } else {
                    albumsHTML = `
                        <h5 class="text-secondary fw-bold mt-3 mb-3">Selecciona un álbum de destino:</h5>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    `;
                    
                    filteredAlbums.forEach(album => {
                        // Usamos un radio button oculto + label con estilo de tarjeta
                        albumsHTML += `
                            <div class="col">
                                <input type="radio" class="btn-check album-radio" 
                                    name="existingAlbumId" 
                                    id="album-${album.A_id}" 
                                    value="${album.A_id}" 
                                    autocomplete="off">
                                
                                <label class="btn btn-outline-secondary p-2 w-100 h-100 album-card-select" for="album-${album.A_id}">
                                    
                                    <img src="${album.A_cover}" alt="Portada de ${album.A_title}" 
                                        class="img-fluid rounded mb-2" style="height: 80px; object-fit: cover;">
                                        
                                    <p class="mb-0 fw-semibold text-truncate small">${album.A_title}</p>
                                    <p class="text-muted small mb-0">${album.A_count} imágenes</p>
                                </label>
                            </div>
                        `;
                    });
                    albumsHTML += `</div>`;
                }
                
                container.innerHTML = albumsHTML; // Inyectar el contenido de los álbumes
                
            } catch (error) {
                mostrarError(errorDiv, null, "Error al cargar álbumes: " + error.message);
                container.innerHTML = `<p class="text-danger text-center mt-3">No se pudieron cargar los álbumes.</p>`;
            }
        }
        
        // Ejecutar la función de fetch
        fetchAlbums();
    }


    function initModalOptionAlbum(){
        const titleAlbumRegex = /^[a-zA-Z0-9._+-ÁÉÍÓÚáéíóúÑñ\s]{1,30}$/;

        const optionAlbumModalEl = document.getElementById('optionAlbumModal');
        const btnPostOptionALbum = document.getElementById('postOptionAlbum');
        
        // Elementos Radio Button (Tarjetas)
        const createRadio = document.getElementById('createAlbumRadio');
        const selectRadio = document.getElementById('selectAlbumRadio');
        

        // 1. Lógica de Sincronización (Inyectar contenido al cambiar de radio)
        if (createRadio && selectRadio) {
            
            // Inicializar al cargar el modal (por defecto: Crear Álbum)
            injectCreateAlbumForm();

            // Listeners para las tarjetas de opción (Radio Buttons)
            createRadio.addEventListener('change', () => {
                if (createRadio.checked) injectCreateAlbumForm();
            });

            selectRadio.addEventListener('change', () => {
                if (selectRadio.checked) injectSelectAlbumList();
            });
        }
        
        
        
        // 2. Lógica de Volver (Botón fijo)
        document.getElementById('backToCarousel')?.addEventListener('click', () => {
            const optionAlbumModal = bootstrap.Modal.getInstance(optionAlbumModalEl);
            const selectImagesModal = new bootstrap.Modal(document.getElementById('selectImages'));
            
            if (optionAlbumModal) optionAlbumModal.hide();
            selectImagesModal.show();
        });

        // 3. Lógica de Transición (Botón 'PUBLICAR') 
        if(btnPostOptionALbum){
            btnPostOptionALbum.addEventListener('click', async e => {
                e.preventDefault();

                let formData = returnBasicFormData();
                
                const selectedOption = document.querySelector('input[name="albumOption"]:checked').value;
                //const optionAlbumModal = bootstrap.Modal.getInstance(optionAlbumModalEl);
                const errorPostAlbum = document.getElementById('errorPostAlbum');

                if(selectedOption=='create'){
                    const albumTitleInput = document.getElementById('albumTitleInput');
                    const coverImageIndex = document.getElementById('coverImageIndex');
                    const errorDiv = document.getElementById('errorAlbumTitle');

                    let valido = validarCampo(albumTitleInput, titleAlbumRegex, errorDiv, "Título inválido (máx 30 caracteres).");
                    if (!valido) return;

                    formData.append('actionPost', selectedOption);
                    formData.append('titleAlbum', albumTitleInput.value.trim());
                    formData.append('coverImageIndex', coverImageIndex.value);

                    
                }else{
                    if(selectedOption=='select'){
                        // 💡 1. Obtener el radio button marcado dentro del grupo 'existingAlbumId'
                        const selectedRadio = document.querySelector('input[name="existingAlbumId"]:checked');
                        
                        if (!selectedRadio) {
                            // 2. Mostrar error si no hay selección
                            mostrarError(errorPostAlbum, null, "Debes seleccionar un álbum de destino.");
                            return;
                        }

                        // 3. Obtener el valor (que es el A_id)
                        const selectedAlbumId = selectedRadio.value;

                        formData.append('actionPost', selectedOption);
                        formData.append('albumSelected',selectedAlbumId);
                    }
                }

                //PARTE DE CODIGO PARA FINALIZAR LA PUBLICACION
                try {
                    const res = await fetch("./BACKEND/FuncionesPHP/publicarContenido.php", { method: "POST", body: formData });
                    
                    // Obtenemos la respuesta como texto y la parseamos.
                    let data = await res.json();

                    let callback = null;
                    let message = data.message || "Operación completada.";
                    let type = data.status || 'error';

                    // 1. Manejo de Éxito
                    if (type === 'success') {
                        // Cierra el modal de opciones ANTES de mostrar la notificación
                        const optionAlbumModal = bootstrap.Modal.getInstance(document.getElementById('optionAlbumModal'));
                        if (optionAlbumModal) optionAlbumModal.hide();
                        
                        // Recarga la página al aceptar
                        callback = () => window.location.reload(); 
                    }
                    // 2. Manejo de Error de Sesión
                    else if (type === 'errorSession') {
                        // Redirige al logout.php al aceptar
                        message = "Sesión expirada. Por favor, vuelve a iniciar sesión.";
                        callback = () => window.location.href = './BACKEND/Validation/logout.php';
                    }
                    // 3. Manejo de Error de Validación o Interno (General)
                    else if (type === 'error') {
                        // No hay callback, solo muestra el mensaje de error
                        message = "Error: " + message;
                    } 
                    // 4. Manejo de Error de Lógica Final (el último 'else' de tu estructura)
                    else {
                        // Esto captura cualquier otro error del servidor que no clasificaste.
                        message = "Error inesperado: " + message;
                    }
                    
                    // Muestra el modal de notificación fijo con el mensaje y el callback
                    showStaticNotificationModal(type, message, callback);
                    
                } catch (error) { 
                    // Fallo de red o respuesta no válida (e.g., error 500)
                    // Asumo que tienes una referencia a errorPostAlbum en tu scope local para mostrar el mensaje
                    mostrarError(errorPostAlbum, null, "Error crítico de conexión o respuesta no válida."); 
                    console.error("Fallo en el fetch:", error.message);
                }

            
            });
        }
    }

    
    
    initModalOptionAlbum();
});