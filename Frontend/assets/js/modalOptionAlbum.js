document.addEventListener("DOMContentLoaded", () => {

    // --------------------------------------------------------------------------------------
    // FUNCIONES DE UTILIDAD (Deben ser accesibles si no están en otro JS)
    // --------------------------------------------------------------------------------------

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
    function injectSelectAlbumList() {
        const selectAlbumHTML = `
            <div id="selectAlbumListContent">
                <p class="alert alert-info text-center mt-3">Aquí se cargaría la lista de tus álbumes existentes para seleccionar el destino.</p>
                <select id="existingAlbumSelect" class="form-select form-style">
                    <option value="">Selecciona un álbum...</option>
                    <option value="1">Vacaciones 2024</option>
                    <option value="2">Proyectos de Cerámica</option>
                </select>
                <div class="error" id="errorSelectAlbum"></div>
            </div>`;
            
        document.getElementById("optionAlbumContainer").innerHTML = selectAlbumHTML;
    }


    function initModalOptionAlbum(){
        
        const optionAlbumModalEl = document.getElementById('optionAlbumModal');
        const continueBtn = document.getElementById('postOptionSelection');
        
        // Elementos Radio Button (Tarjetas)
        const createRadio = document.getElementById('createAlbumRadio');
        const selectRadio = document.getElementById('selectAlbumRadio');
        
        // Contenedores
        const optionAlbumContainer = document.getElementById('optionAlbumContainer');


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
        
        // 2. Lógica de Transición (Botón 'Continuar') IMPLEMENTACION FUTURA,HACER VALIDACION
        if(continueBtn){
            continueBtn.addEventListener('click', async e => {
                e.preventDefault();
                
                const selectedOption = document.querySelector('input[name="albumOption"]:checked').value;
                const optionAlbumModal = bootstrap.Modal.getInstance(optionAlbumModalEl);
                
                // 💡 Implementación futura: Aquí se llamaría a la función final de subida.
                alert(`Opción seleccionada: ${selectedOption}. Se procede al submit/cierre.`); 
                
                // Simulación de éxito: Cierra el modal
                if (optionAlbumModal) optionAlbumModal.hide();
            });
        }
        
        // 3. Lógica de Volver (Botón fijo)
        document.getElementById('backToCarousel')?.addEventListener('click', () => {
            const optionAlbumModal = bootstrap.Modal.getInstance(optionAlbumModalEl);
            const selectImagesModal = new bootstrap.Modal(document.getElementById('selectImages'));
            
            if (optionAlbumModal) optionAlbumModal.hide();
            selectImagesModal.show();
        });
    }
    
    initModalOptionAlbum();
});