// feedLoader.js - Carga el feed principal en index.php usando Masonry

document.addEventListener("DOMContentLoaded", () => {
    // Referencia al contenedor principal del feed
    const resultsContainer = document.getElementById('searchResultsContainer');

    // Variable global local para guardar la instancia activa de Masonry
    let msnry = null; 

    // =====================================================
    // CARGA DEL FEED INICIAL
    // =====================================================
    cargarFeedInicial(); 
    
    // =====================================================
    // FUNCIONES PRINCIPALES
    // =====================================================
    
    // -----------------------------------------------------
    //--> CARGA DEL FEED INICIAL (Petición AJAX al backend)
    async function cargarFeedInicial(){
        if(!resultsContainer) return;

        resultsContainer.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">Cargando feed...</p>
            </div>
        `;
        
        try{
            const res = await fetch("./BACKEND/FuncionesPHP/obtenerFeed.php", {method: 'POST'});
            const data = await res.json();
            
            if(data.status === 'success'){
                if(data.results.length === 0){
                    resultsContainer.innerHTML = `
                        <div class="text-center mt-5 text-secondary">
                            <i class="uil uil-image-slash fs-1"></i>
                            <p class="mt-3">No hay imágenes disponibles.</p>
                        </div>
                    `;
                } else {
                    mostrarImagenes(data.results, resultsContainer);
                }
            } else {
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger text-center mt-3">
                        <i class="uil uil-exclamation-triangle fs-3"></i>
                        <p class="mb-0 mt-2">Error al cargar el feed.</p>
                    </div>
                `;
            }
        } catch(err){
            console.error(err);
            resultsContainer.innerHTML = `
                <div class="alert alert-danger text-center mt-3">
                    <i class="uil uil-exclamation-triangle fs-3"></i>
                    <p class="mb-0 mt-2">Error de conexión.</p>
                </div>
            `;
        }
    }

    // -----------------------------------------------------
    //--> MUESTRA RESULTADOS DE IMÁGENES (MODIFICADO PARA MODAL)

    function mostrarImagenes(images, container) {
        console.log('📸 Total de imágenes recibidas:', images.length);
        
        if(msnry){
            msnry.destroy();
            msnry = null;
        }
        
        container.innerHTML = '';
        
        images.forEach((img, index) => {
            const cardWrapper = document.createElement('div');
            cardWrapper.classList.add('feed-grid-item');

            const badge = img.visibility === 1 
                ? `<div class="feed-img-privacy"><i class="uil uil-lock"></i></div>` 
                : '';

            // --- (Lógica de altura aleatoria sin cambios) ---
            let randHeight;
            if(window.innerWidth <= 767) {
                randHeight = Math.floor(Math.random() * (260 - 200 + 1)) + 200;
            } else if(window.innerWidth <= 1200) {
                randHeight = Math.floor(Math.random() * (340 - 240 + 1)) + 240;
            } else {
                randHeight = Math.floor(Math.random() * (400 - 250 + 1)) + 250;
            }

            // --- ¡CAMBIO AQUÍ! ---
            // 1. Eliminamos la etiqueta <a>
            // 2. Añadimos 'data-action', 'data-image-id' y 'cursor: pointer' al div
            cardWrapper.innerHTML = `
                <div class="feed-img-card" 
                     style="height:${randHeight}px; cursor: pointer;"
                     data-action="view-single-image"
                     data-image-id="${img.id}">
                    
                    ${badge}
                    <img src="${img.imageUrl}" 
                         alt="${img.title || 'Imagen'}" 
                         loading="lazy"
                         style="height:100%; width:100%; object-fit:cover;">
                    <div class="feed-img-overlay">
                        <div class="feed-img-header">
                            <img src="${img.profileImage || './Frontend/assets/images/appImages/default.jpg'}" 
                                 alt="${img.username}">
                            <p>@${img.username}</p>
                        </div>
                    </div>
                    <div class="feed-img-title">${img.title || ''}</div>
                </div>
            `;

            container.appendChild(cardWrapper);
        });

        // --- ¡CAMBIO AÑADIDO! ---
        // Adjuntamos los listeners a las tarjetas que acabamos de crear
        container.querySelectorAll('div[data-action="view-single-image"]').forEach(card => {
            card.addEventListener('click', (e) => {
                // Evitamos que el clic se dispare si se hace clic en un botón dentro de la card
                if (e.target.closest('button')) return; 
                
                const imgId = e.currentTarget.dataset.imageId;
                
                if (typeof openImageModal === 'function') {
                    openImageModal(imgId);
                } else {
                    console.error('La función openImageModal no está definida. ¿Cargaste imageModal.js?');
                }
            });
        });
        // --- FIN DEL CAMBIO ---

        // PASO 4: Inicializar Masonry
        setTimeout(() => {
            initMasonry(container);
        }, 100);
    }

    // -----------------------------------------------------
    //--> CONFIGURA E INICIALIZA MASONRY
    function initMasonry(container) {
        try {
            const gutterSize = window.innerWidth <= 767 ? 8 : 10;
            
            msnry = new Masonry(container, {
                itemSelector: '.feed-grid-item',
                columnWidth: '.feed-grid-item',
                percentPosition: true,
                gutter: gutterSize,
                horizontalOrder: false,
                transitionDuration: '0.3s',
                initLayout: false 
            });
            
            setTimeout(() => {
                if(msnry) msnry.layout();
            }, 10);

            // Relayout cuando las imágenes carguen
            const images = container.querySelectorAll('img');
            let loadedCount = 0;
            const totalImages = images.length;

            const checkAllLoaded = () => {
                if(loadedCount === totalImages && msnry) {
                    setTimeout(() => {
                        msnry.layout();
                    }, 50);
                }
            };
            
            images.forEach(img => {
                if(img.complete) {
                    loadedCount++;
                    checkAllLoaded();
                } else {
                    img.addEventListener('load', () => { loadedCount++; checkAllLoaded(); });
                    img.addEventListener('error', () => { loadedCount++; checkAllLoaded(); });
                }
            });

            // Relayout al redimensionar ventana
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if(msnry) {
                        msnry.layout();
                    }
                }, 250);
            });

            setTimeout(() => {
                if(msnry) msnry.layout();
            }, 200);

        } catch(error) {
            console.error('Error al inicializar Masonry:', error);
        }
    }
});