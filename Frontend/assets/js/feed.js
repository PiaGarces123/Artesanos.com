// feed.js - Gestión del feed inicial y sistema Masonry
// Controla la carga de imágenes del feed y su disposición visual

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================================
    // 1. VARIABLES Y ELEMENTOS DEL DOM
    // =========================================================================
    
    const resultsContainer = document.getElementById('searchResultsContainer');
    let msnry = null; // Instancia de Masonry

    
    // =========================================================================
    // 2. FUNCIONES AUXILIARES (UTILIDADES)
    // =========================================================================
    
    /**
     * Muestra un mensaje de error en el contenedor
     * @param {HTMLElement} container - Contenedor donde mostrar el error
     * @param {string} message - Mensaje de error
     * @param {string} icon - Clase del icono (opcional)
     */
    const mostrarError = (container, message, icon = 'uil-exclamation-triangle') => {
        container.innerHTML = `
            <div class="alert alert-danger text-center mt-3">
                <i class="uil ${icon} fs-3"></i>
                <p class="mb-0 mt-2">${message}</p>
            </div>
        `;
    };

    /**
     * Muestra un spinner de carga
     * @param {HTMLElement} container - Contenedor donde mostrar el spinner
     * @param {string} message - Mensaje de carga
     */
    const mostrarSpinner = (container, message = 'Cargando...') => {
        container.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">${message}</p>
            </div>
        `;
    };

    /**
     * Muestra un mensaje cuando no hay resultados
     * @param {HTMLElement} container - Contenedor donde mostrar el mensaje
     * @param {string} message - Mensaje a mostrar
     */
    const mostrarMensajeVacio = (container, message) => {
        container.innerHTML = `
            <div class="text-center mt-5 text-secondary">
                <i class="uil uil-image-slash fs-1"></i>
                <p class="mt-3">${message}</p>
            </div>
        `;
    };


    // =========================================================================
    // 3. FUNCIONES DE MASONRY
    // =========================================================================
    
    /**
     * Destruye la instancia actual de Masonry
     */
    const destroyMasonry = () => {
        if (msnry) {
            msnry.destroy();
            msnry = null;
        }
    };

    /**
     * Configura e inicializa Masonry en el contenedor
     * @param {HTMLElement} container - Contenedor del grid
     */
    const initMasonry = (container) => {
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
            
            // Layout manual inicial
            setTimeout(() => {
                if (msnry) msnry.layout();
            }, 10);

            // Relayout cuando las imágenes carguen
            const images = container.querySelectorAll('img');
            let loadedCount = 0;
            const totalImages = images.length;

            images.forEach(img => {
                if (img.complete) {
                    loadedCount++;
                    checkAllLoaded();
                } else {
                    img.addEventListener('load', () => {
                        loadedCount++;
                        checkAllLoaded();
                    });
                    img.addEventListener('error', () => {
                        loadedCount++;
                        checkAllLoaded();
                    });
                }
            });

            function checkAllLoaded() {
                if (loadedCount === totalImages && msnry) {
                    setTimeout(() => {
                        msnry.layout();
                    }, 50);
                }
            }

            // Relayout al redimensionar ventana
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if (msnry) {
                        msnry.layout();
                    }
                }, 250);
            });

            // Layout inicial adicional
            setTimeout(() => {
                if (msnry) msnry.layout();
            }, 200);

        } catch (error) {
            console.error('Error al inicializar Masonry:', error);
        }
    };


    // =========================================================================
    // 4. FUNCIONES DE RENDERIZADO
    // =========================================================================
    
    /**
     * Muestra imágenes del feed con layout Masonry
     * @param {Array} images - Array de imágenes del feed
     * @param {HTMLElement} container - Contenedor donde renderizar
     */
    const mostrarImagenesFeed = (images, container) => {
        console.log('📸 Total de imágenes recibidas:', images.length);
        console.log('🖼️ Datos de imágenes:', images);
        
        destroyMasonry();
        container.innerHTML = '';
        
        images.forEach((img, index) => {
            const cardWrapper = document.createElement('div');
            cardWrapper.classList.add('feed-grid-item');

            const badge = img.visibility === 1 
                ? `<div class="feed-img-privacy"><i class="uil uil-lock"></i></div>` 
                : '';

            let randHeight;
            if (window.innerWidth <= 767) {
                randHeight = Math.floor(Math.random() * (260 - 200 + 1)) + 200;
            } else if (window.innerWidth <= 1200) {
                randHeight = Math.floor(Math.random() * (340 - 240 + 1)) + 240;
            } else {
                randHeight = Math.floor(Math.random() * (400 - 250 + 1)) + 250;
            }

            cardWrapper.innerHTML = `
                <div class="feed-img-card" style="height:${randHeight}px;">
                    <a href="./image.php?id=${img.id}" class="text-decoration-none d-block position-relative h-100">
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
                            <div class="feed-img-actions">
                                <button class="btn btn-light btn-sm" 
                                        onclick="event.preventDefault(); toggleLike(${img.id}, this);">
                                    <i class="uil uil-heart"></i>
                                </button>
                                <button class="btn btn-light btn-sm" 
                                        onclick="event.preventDefault(); window.location.href='./image.php?id=${img.id}#comments';">
                                    <i class="uil uil-comment-dots"></i>
                                </button>
                            </div>
                        </div>
                        <div class="feed-img-title">${img.title || 'Sin título'}</div>
                    </a>
                </div>
            `;

            container.appendChild(cardWrapper);
            console.log(`✅ Imagen ${index + 1}/${images.length} agregada al DOM`);
        });

        console.log('📦 Items en container:', container.querySelectorAll('.feed-grid-item').length);
        console.log('📐 Ancho del contenedor:', container.offsetWidth + 'px');
        console.log('📐 Alto del contenedor:', container.offsetHeight + 'px');
        
        const firstItem = container.querySelector('.feed-grid-item');
        if (firstItem) {
            console.log('📏 Ancho del primer item:', firstItem.offsetWidth + 'px');
        }

        setTimeout(() => {
            initMasonry(container);
        }, 100);
    };


    // =========================================================================
    // 5. FUNCIONES DE CARGA DE DATOS (FETCH)
    // =========================================================================
    
    /**
     * Carga el feed inicial de imágenes desde el backend
     */
    const cargarFeedInicial = async () => {
        if (!resultsContainer) return;

        mostrarSpinner(resultsContainer, 'Cargando feed...');
        
        try {
            const res = await fetch("./BACKEND/FuncionesPHP/obtenerFeed.php", { method: 'POST' });
            const data = await res.json();
            
            if (data.status === 'success') {
                if (data.results.length === 0) {
                    mostrarMensajeVacio(resultsContainer, 'No hay imágenes disponibles.');
                } else {
                    mostrarImagenesFeed(data.results, resultsContainer);
                }
            } else {
                mostrarError(resultsContainer, 'Error al cargar el feed.');
            }
        } catch (err) {
            console.error(err);
            mostrarError(resultsContainer, 'Error de conexión.');
        }
    };


    // =========================================================================
    // 6. INICIALIZACIÓN
    // =========================================================================
    
    cargarFeedInicial();


    // =========================================================================
    // 7. EXPONER FUNCIONES GLOBALES (para usar desde busqueda.js)
    // =========================================================================
    
    window.feedModule = {
        cargarFeedInicial: cargarFeedInicial,
        mostrarImagenesFeed: mostrarImagenesFeed,
        destroyMasonry: destroyMasonry,
        initMasonry: initMasonry
    };
});


// =========================================================================
// 8. FUNCIONES GLOBALES
// =========================================================================

/**
 * Toggle Like - Marca/desmarca "like" en una imagen
 * @param {number} imageId - ID de la imagen
 * @param {HTMLElement} button - Botón de like
 */
window.toggleLike = function (imageId, button) {
    if (!window.isLoggedIn) {
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        return;
    }
    button.classList.toggle('liked');
    console.log('Toggle like en imagen:', imageId);
};