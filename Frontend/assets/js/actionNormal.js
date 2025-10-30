// actionNormal.js - Sistema de búsqueda y feed estilo Pinterest con Masonry

document.addEventListener("DOMContentLoaded", () => {
    const resultsContainer = document.getElementById('searchResultsContainer');
    const buscarBtnsDesktop = document.querySelectorAll("#searchAndFiltersDesktop .buscarPor-btn");
    const buscarBtnsMobile = document.querySelectorAll("#searchAndFiltersMobile .buscarPor-btn");
    const searchInputDesktop = document.getElementById("searchInput");
    const searchInputMobile = document.getElementById("searchInputMobile");
    const searchButtonDesktop = document.getElementById("searchButtonDesktop");
    const searchButtonMobile = document.getElementById("searchButtonMobile");

    let msnry = null; // Variable global Masonry

    // Cargar feed inicial
    cargarFeedInicial();

    // Efecto de botón activo
    [...buscarBtnsDesktop, ...buscarBtnsMobile].forEach(btn => {
        btn.addEventListener('click', () => {
            const parent = btn.closest('#searchAndFiltersDesktop, #searchAndFiltersMobile');
            parent.querySelectorAll(".buscarPor-btn").forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Event listeners para búsqueda desktop
    if(searchInputDesktop && searchButtonDesktop){
        searchButtonDesktop.addEventListener('click', () => realizarBusqueda(searchInputDesktop.value, getActiveSearchType(buscarBtnsDesktop)));
        searchInputDesktop.addEventListener('keypress', e => {
            if(e.key === 'Enter'){
                e.preventDefault();
                searchButtonDesktop.click();
            }
        });
    }

    // Event listeners para búsqueda móvil
    if(searchInputMobile && searchButtonMobile){
        searchButtonMobile.addEventListener('click', () => {
            realizarBusqueda(searchInputMobile.value, getActiveSearchType(buscarBtnsMobile));
            const offcanvasEl = document.getElementById('sidebarOffcanvas');
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if(offcanvasInstance) offcanvasInstance.hide();
        });
        searchInputMobile.addEventListener('keypress', e => {
            if(e.key === 'Enter'){
                e.preventDefault();
                searchButtonMobile.click();
            }
        });
    }

    // ------------------------------
    const logoutLink = document.getElementById('logoutLink');

    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            // 1. Prevenir la acción por defecto para controlar la navegación nosotros.
            e.preventDefault();
            
            // 2. Mostrar el diálogo de confirmación.
            if (confirm('¿Estás seguro de que quieres cerrar tu sesión?')) {
                
                // 3. Si confirma, redirigir manualmente a la página de logout PHP.
                // Asegúrate que la ruta sea correcta:
                window.location.href = './BACKEND/Validation/logout.php'; 
            }
            // Si no confirma, no pasa nada y se queda en la página.
        });
    }

    // =====================================================
    // FUNCIONES PRINCIPALES
    // =====================================================

    function getActiveSearchType(buttons){
        const activeBtn = Array.from(buttons).find(b => b.classList.contains('active'));
        return activeBtn ? activeBtn.dataset.buscarPor : 'perfil';
    }

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

    async function realizarBusqueda(query, type){
        if(!resultsContainer) return;
        
        if(!query.trim()){
            cargarFeedInicial();
            return;
        }
        
        resultsContainer.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">Buscando...</p>
            </div>
        `;
        
        const formData = new FormData();
        formData.append('query', query.trim());
        formData.append('searchType', type);
        
        try{
            const res = await fetch("./BACKEND/FuncionesPHP/buscar.php", {method: 'POST', body: formData});
            const data = await res.json();
            
            if(data.status === 'success'){
                if(data.results.length === 0){
                    resultsContainer.innerHTML = `
                        <div class="custom-message-card text-center mt-5">
                            <i class="uil uil-frown fs-1"></i>
                            <p class="mt-3 fs-5">No se encontraron resultados para "<strong>${data.query}</strong>"</p>
                            <button class="btn custom-btn-secondary" onclick="location.reload()">
                                <i class="uil uil-redo me-1"></i> Volver al feed
                            </button>
                        </div>
                    `;
                } else {
                    if(type === 'perfil') {
                        mostrarPerfiles(data.results, resultsContainer);
                    } else {
                        mostrarImagenes(data.results, resultsContainer);
                    }
                }
            } else {
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger text-center mt-3">
                        <i class="uil uil-exclamation-triangle fs-3"></i>
                        <p class="mb-0 mt-2">${data.message || 'Error en la búsqueda.'}</p>
                    </div>
                `;
            }
        } catch(err){
            console.error(err);
        }
    }

    function mostrarPerfiles(users, container){
        // Destruir Masonry si existe
        if(msnry){
            msnry.destroy();
            msnry = null;
        }
        
        let html = `
            <div class="mb-3">
                <button class="btn custom-btn-secondary btn-sm" onclick="location.reload()">
                    <i class="uil uil-arrow-left me-1"></i> Volver al feed
                </button>
            </div>
            <h4 class="mb-4">Resultados de perfiles:</h4>
            <div class="row g-3">
        `;
        
        users.forEach(user => {
            html += `
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="./profile.php?id=${user.id}" class="text-decoration-none">
                        <div class="card shadow-sm h-100 hover-card d-flex flex-row align-items-center gap-3 p-3">
                            <img src="${user.profileImage}" alt="${user.username}" 
                                 class="rounded-circle border border-2 border-primary" 
                                 style="width:60px;height:60px;object-fit:cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold text-dark">@${user.username}</h6>
                                <p class="mb-0 small text-muted">${user.fullName}</p>
                                ${user.biography ? `<p class="mb-0 small text-secondary mt-1">${user.biography.substring(0, 50)}${user.biography.length > 50 ? '...' : ''}</p>` : ''}
                            </div>
                        </div>
                    </a>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    function mostrarImagenes(images, container) {
        // DEBUG: Verificar cuántas imágenes hay
        console.log('📸 Total de imágenes recibidas:', images.length);
        console.log('🖼️ Datos de imágenes:', images);
        
        // PASO 1: Destruir Masonry anterior si existe
        if(msnry){
            msnry.destroy();
            msnry = null;
        }
        
        // PASO 2: Limpiar contenedor
        container.innerHTML = '';
        
        // PASO 3: Crear elementos de imagen
        images.forEach((img, index) => {
            const cardWrapper = document.createElement('div');
            cardWrapper.classList.add('feed-grid-item');

            // Badge de visibilidad
            const badge = img.visibility === 1 
                ? `<div class="feed-img-privacy"><i class="uil uil-lock"></i></div>` 
                : '';

            // Altura aleatoria según viewport
            let randHeight;
            if(window.innerWidth <= 767) {
                // Móvil: 2 columnas
                randHeight = Math.floor(Math.random() * (260 - 200 + 1)) + 200;
            } else if(window.innerWidth <= 1200) {
                // Tablet: 3 columnas
                randHeight = Math.floor(Math.random() * (340 - 240 + 1)) + 240;
            } else {
                // Desktop: 4 columnas
                randHeight = Math.floor(Math.random() * (400 - 250 + 1)) + 250;
            }

            // Construir tarjeta
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
            
            // DEBUG: Confirmar que se agregó
            console.log(`✅ Imagen ${index + 1}/${images.length} agregada al DOM`);
        });

        // DEBUG: Verificar elementos en el DOM y dimensiones del contenedor
        console.log('📦 Items en container:', container.querySelectorAll('.feed-grid-item').length);
        console.log('📐 Ancho del contenedor:', container.offsetWidth + 'px');
        console.log('📐 Alto del contenedor:', container.offsetHeight + 'px');
        
        // DEBUG: Verificar ancho de cada item
        const firstItem = container.querySelector('.feed-grid-item');
        if(firstItem) {
            console.log('📏 Ancho del primer item:', firstItem.offsetWidth + 'px');
        }

        // PASO 4: Esperar a que el DOM esté listo e inicializar Masonry
        setTimeout(() => {
            initMasonry(container);
        }, 100);
    }

    function initMasonry(container) {
        try {
            // Configuración de gutter según viewport
            const gutterSize = window.innerWidth <= 767 ? 8 : 10;
            
            msnry = new Masonry(container, {
                itemSelector: '.feed-grid-item',
                columnWidth: '.feed-grid-item',
                percentPosition: true,
                gutter: gutterSize,
                horizontalOrder: false,
                transitionDuration: '0.3s',
                initLayout: false // Cambiar a false para controlar manualmente
            });
            
            // Layout manual inicial
            setTimeout(() => {
                if(msnry) msnry.layout();
            }, 10);

            // Relayout cuando las imágenes carguen
            const images = container.querySelectorAll('img');
            let loadedCount = 0;
            const totalImages = images.length;

            images.forEach(img => {
                if(img.complete) {
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
                if(loadedCount === totalImages && msnry) {
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
                    if(msnry) {
                        msnry.layout();
                    }
                }, 250);
            });

            // Layout inicial adicional
            setTimeout(() => {
                if(msnry) msnry.layout();
            }, 200);

        } catch(error) {
            console.error('Error al inicializar Masonry:', error);
        }
    }
});

// =====================================================
// Toggle Like
// =====================================================
window.toggleLike = function(imageId, button){
    if(!window.isLoggedIn){
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        return;
    }
    button.classList.toggle('liked');
    console.log('Toggle like en imagen:', imageId);
};