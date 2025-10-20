document.addEventListener("DOMContentLoaded", () => {
    
    // Función para obtener la URL de un archivo local (necesaria para el carrusel)
    const getLocalImageUrl = (file) => {
        return new Promise(resolve => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.readAsDataURL(file);
        });
    };

    // Función principal para construir el carrusel (Asíncrona)
    const buildCarousel = async (files, targetForm) => {
        let carouselHTML = '';
        let carouselIndicators = '';
        const formContent = document.createElement('div');
        formContent.id = 'carouselSelectImages';
        formContent.className = 'carousel slide';

        const carouselInner = document.createElement('div');
        carouselInner.className = 'carousel-inner';

        // Recorremos los archivos
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const imageUrl = await getLocalImageUrl(file);
            const activeClass = i === 0 ? ' active' : '';

            // 1. Indicadores
            carouselIndicators += `<button type="button" data-bs-target="#carouselSelectImages" data-bs-slide-to="${i}" class="${activeClass}" aria-current="${i === 0}" aria-label="Slide ${i + 1}"></button>`;

            // 2. Item del Carrusel
            carouselHTML += `
                <div class="carousel-item${activeClass}">
                    <div class="d-flex flex-column align-items-center">
                        
                        <img src="${imageUrl}" class="d-block img-fluid rounded mb-4" alt="Imagen ${i + 1}" style="max-height: 350px; object-fit: contain;">

                        <div class="container-fluid" style="z-index: 10;">
                            <div class="row justify-content-center">
                                <div class="col-md-5">
                                    <div class="form-groupLogin mb-3 position-relative">
                                        <input type="text" class="form-style form-control" placeholder="Título para Imagen ${i + 1}" name="titleImage[${i}]" required>
                                        <i class="input-icon uil uil-tag-alt"></i>
                                    </div>
                                    <div class="error" id="errorTitleImage${i}"></div>
                                </div>
                                
                                <div class="col-md-5">
                                    <div class="form-groupLogin mb-3 position-relative">
                                        <select name="visibilityImage[${i}]" class="form-style form-control" required>
                                            <option value="0" selected>Pública</option>
                                            <option value="1">Privada</option>
                                        </select>
                                        <i class="input-icon uil uil-lock-alt"></i>
                                    </div>
                                    <div class="error" id="errorVisibilityImage${i}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        // 3. Ensamblar el Carrusel Completo
        formContent.innerHTML = `
            <div class="carousel-indicators">${carouselIndicators}</div>
            <div class="carousel-inner">${carouselHTML}</div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselSelectImages" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselSelectImages" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        `;

        // 4. Adjuntar al Formulario Destino
        targetForm.innerHTML = '';
        targetForm.appendChild(formContent);
        
        // Adjuntar los botones de acción al pie del modal
        const buttonsHTML = `
            <div class="d-flex justify-content-end gap-3 mt-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="cancelSelect">Cancelar</button>
                <button type="button" class="btn btn-primary" id="continueSelectImages">Continuar</button>
            </div>
        `;
        targetForm.insertAdjacentHTML('beforeend', buttonsHTML);

        // 5. Ocultar el primer modal y mostrar el segundo
        const createAlbumModal = bootstrap.Modal.getInstance(document.getElementById('createAlbumModal'));
        const selectImagesModal = new bootstrap.Modal(document.getElementById('selectImages'));
        
        if (createAlbumModal) createAlbumModal.hide();
        selectImagesModal.show();
    };


    function initModalSelectImages(){
        const inputFile = document.getElementById("imageInput");
        const errorInputFile = document.getElementById("errorCreateAlbum");
        const btnContinueCreate = document.getElementById('continueCreate');

        // Función para limpiar todos los errores visuales
        const limpiarErrores = () => {
            document.querySelectorAll(".error").forEach(div => {
                div.textContent = "";
                div.classList.remove("visible-error");
            });
            document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
        };

        // Función para mostrar errores
        const mostrarError = (div, input, msg) => {
            if (!div) return;
            div.textContent = msg;
            div.classList.add("visible-error");
            if (input) input.classList.add("errorInput");
        };
        
        // Validación específica del input file
        const validarCampoFile = (input, errorDiv, msg) => { 
            let isValid = true;
            input.classList.remove("errorInput");

            if(input.files.length === 0 || input.files.length > 20){
                mostrarError(errorDiv, input, msg); 
                isValid = false;
            }

            if (!isValid) {
                document.getElementById('fileUpload').classList.add("errorInput"); 
            } else {
                document.getElementById('fileUpload').classList.remove("errorInput");
            }
            return isValid;
        };


        if(btnContinueCreate){
            btnContinueCreate.addEventListener('click', async e => {
                e.preventDefault();
                limpiarErrores();
                
                // 1. Validar selección de archivos
                let valido = validarCampoFile(inputFile, errorInputFile, "Debe seleccionar al menos 1 Imagen y no más de 20 Imágenes");
                if (!valido) return;

                const selectImagesForm = document.getElementById("selectImagesForm");

                // 2. Construir el carrusel y cambiar de modal
                await buildCarousel(inputFile.files, selectImagesForm);
            });
        }

        // Esto es solamente para quitar el cartel de error caundo selecciona imagenes
        inputFile.addEventListener('change', (e) => {
            limpiarErrores();
        });
    }
    
    // Inicializar lógica
    initModalSelectImages();
});

