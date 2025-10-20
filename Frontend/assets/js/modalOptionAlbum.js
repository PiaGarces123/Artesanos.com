document.addEventListener("DOMContentLoaded", () => {

    function initModalOptionAlbum(){
        const btnCreateAlbum = document.getElementById("createAlbum");
        const btnSelectAlbum = document.getElementById("selectAlbum");
        const optionAlbumContainer = document.getElementById("optionAlbumContainer");

        // Lógica para sincronizar el checkbox de portada con el carrusel activo
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

        if(btnCreateAlbum){
            btnCreateAlbum.addEventListener("click", async e => {
                optionAlbumContainer.innerHTML = 
                `<form id="createAlbumTitleForm" class="mt-3">    
                    <div class="mb-4">
                        <div class="form-groupLogin mb-3 position-relative">
                            <label for="albumTitleInput" class="form-label visually-hidden">Título del Álbum</label>
                            <input type="text" class="form-style form-control" 
                                placeholder="Título del Álbum" 
                                name="albumTitle" 
                                id="albumTitleInput" 
                                maxlength="30"
                                required>
                            <i class="input-icon uil uil-tag-alt"></i>
                        </div>
                        <div class="error" id="errorAlbumTitle"></div>` +
                        // 💡 NUEVO: CHECKBOX PARA PORTADA
                        `<div class="form-check form-switch mt-3 mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" value="1" id="isCoverCheckbox" name="isCover">
                            <label class="form-check-label fw-semibold" for="isCoverCheckbox">
                                Usar la imagen actual como portada del nuevo álbum.
                            </label>
                        </div>` +
                        // 💡 CAMPO OCULTO para el índice de la imagen de portada
                        `<input type="hidden" id="coverImageIndex" name="coverImageIndex">
                    </div>
                    
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="backToOptions">Volver</button>
                        
                        <button type="submit" class="btn btn-primary" id="finalizeAlbumCreation">Publicar</button>
                    </div>
                </form>`;

                // Lógica para Volver al Option Album Modal
                document.getElementById('backToOptions')?.addEventListener('click', () => {
                    const selectAlbumModal = bootstrap.Modal.getInstance(document.getElementById('selectImages'));
                    const optionAlbumModal = bootstrap.Modal.getInstance(document.getElementById('optionAlbumModal'));
                    
                    if (optionAlbumModal) optionAlbumModal.hide();
                    selectAlbumModal.show();
                    optionAlbumContainer.innerHTML = "";
                });

                

                setupCoverSelection();
            });
        }

    }

    initModalOptionAlbum();


});