// actionNormal.js - Sistema de búsqueda y feed estilo Pinterest con Masonry
// Controla la carga inicial, el buscador (desktop/móvil), y la
// disposición visual dinámica de imágenes.

document.addEventListener("DOMContentLoaded", () => {
    //Los eventos principales
    const resultsContainer = document.getElementById('searchResultsContainer');
    const buscarBtnsDesktop = document.querySelectorAll("#searchAndFiltersDesktop .buscarPor-btn");
    const buscarBtnsMobile = document.querySelectorAll("#searchAndFiltersMobile .buscarPor-btn");
    const searchInputDesktop = document.getElementById("searchInput");
    const searchInputMobile = document.getElementById("searchInputMobile");
    const searchButtonDesktop = document.getElementById("searchButtonDesktop");
    const searchButtonMobile = document.getElementById("searchButtonMobile");

    // Variable global para guardar la instancia activa de Masonry
    let msnry = null; 


    // =====================================================
    // CARGA DEL FEED INICIAL
    // =====================================================
    cargarFeedInicial(); // Al iniciar la página, se carga el feed principal


    // =====================================================
    // MARCAR BOTÓN ACTIVO (tipo de búsqueda, Usuario o Imagen)
    // =====================================================

    //Los tres puntos (...) son el operador spread (“expandir”).
    //Este operador sirve para combinar los dos conjuntos de botones en una sola lista.
    [...buscarBtnsDesktop, ...buscarBtnsMobile].forEach(btn => {
        // .forEach(btn => { ... })
        //Recorre cada botón de ese arreglo y ejecuta la función interna una vez por cada uno.

        //Agrega un evento de clic a cada botón, de modo que al hacer clic se marque como activo y se desmarquen los demás.
        btn.addEventListener('click', () => {

            // Busca el contenedor del grupo de botones (desktop o mobile)
            const parent = btn.closest('#searchAndFiltersDesktop, #searchAndFiltersMobile');
            // Quita la clase "active" de todos los botones
            parent.querySelectorAll(".buscarPor-btn").forEach(b => b.classList.remove('active'));
            // Marca el botón actual como activo
            btn.classList.add('active');
            
        });
    });


    // =====================================================
    // EVENTOS DE BÚSQUEDA EN DESKTOP
    // =====================================================
    if(searchInputDesktop && searchButtonDesktop){
        // Clic en el botón de buscar
        searchButtonDesktop.addEventListener('click', () => realizarBusqueda(searchInputDesktop.value, getActiveSearchType(buscarBtnsDesktop)));
        // Presionar Enter ejecuta la búsqueda
        searchInputDesktop.addEventListener('keypress', e => {
            if(e.key === 'Enter'){
                e.preventDefault();
                searchButtonDesktop.click();
            }
        });
    }

    
    // =====================================================
    // EVENTOS DE BÚSQUEDA EN MÓVIL
    // =====================================================
    if(searchInputMobile && searchButtonMobile){
        searchButtonMobile.addEventListener('click', () => {
            realizarBusqueda(searchInputMobile.value, getActiveSearchType(buscarBtnsMobile));
            // Oculta el menú lateral (offcanvas) después de buscar
            const offcanvasEl = document.getElementById('sidebarOffcanvas');
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if(offcanvasInstance) offcanvasInstance.hide();
        });
        // Presionar Enter ejecuta la búsqueda
        searchInputMobile.addEventListener('keypress', e => {
            if(e.key === 'Enter'){
                e.preventDefault();
                searchButtonMobile.click();
            }
        });
    }


    // =====================================================
    // FUNCIONES PRINCIPALES
    // =====================================================

    //--> Obtiene el tipo de búsqueda activo (perfil, imagen, álbum, etc.)
    function getActiveSearchType(buttons){
        const activeBtn = Array.from(buttons).find(b => b.classList.contains('active'));
        return activeBtn ? activeBtn.dataset.buscarPor : 'perfil';
    }

    // -----------------------------------------------------
    //--> CARGA DEL FEED INICIAL (cuando no hay búsqueda activa)
    async function cargarFeedInicial(){
        if(!resultsContainer) return;

        // Muestra un spinner(Cargando..) mientras carga el feed
        resultsContainer.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">Cargando feed...</p>
            </div>
        `;
        
        try{
            // Petición al backend que devuelve las imágenes del feed
            const res = await fetch("./BACKEND/FuncionesPHP/obtenerFeed.php", {method: 'POST'});
            const data = await res.json();
            
            if(data.status === 'success'){
                // Si no hay resultados, muestra mensaje
                if(data.results.length === 0){
                    resultsContainer.innerHTML = `
                        <div class="text-center mt-5 text-secondary">
                            <i class="uil uil-image-slash fs-1"></i>
                            <p class="mt-3">No hay imágenes disponibles.</p>
                        </div>
                    `;
                } else {
                    // Si hay imágenes, las muestra con Masonry
                    mostrarImagenes(data.results, resultsContainer);
                }
            } else {
                // Error del backend
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger text-center mt-3">
                        <i class="uil uil-exclamation-triangle fs-3"></i>
                        <p class="mb-0 mt-2">Error al cargar el feed.</p>
                    </div>
                `;
            }
        } catch(err){
            // Error de conexión o de red
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
    //--> REALIZA UNA BÚSQUEDA (por nombre, imagen, etc.)
    async function realizarBusqueda(query, type){

        //Si envió sin contenido, no hace nada
        if(!resultsContainer) return;
        
        // Si no hay texto, vuelve al feed inicial
        if(!query.trim()){
            cargarFeedInicial();
            return;
        }
        
        // Muestra un loader mientras busca
        resultsContainer.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">Buscando...</p>
            </div>
        `;
        
        // Envía los parámetros al backend
        const formData = new FormData();
        formData.append('query', query.trim());
        formData.append('searchType', type);
        
        try{
            // Hacer la consulta
            const res = await fetch("./BACKEND/FuncionesPHP/buscar.php", {method: 'POST', body: formData});
            const data = await res.json();
            
            if(data.status === 'success'){

                // Si no hay resultados
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
                    // Si hay resultados
                    if(type === 'perfil') {
                        //Muestra perfiles
                        mostrarPerfiles(data.results, resultsContainer);
                    } else {
                        //Muestra imágenes
                        mostrarImagenes(data.results, resultsContainer);
                    }
                }
            } else {
                // Error en el proceso de búsqueda
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

    // -----------------------------------------------------
    //--> MUESTRA RESULTADOS DE PERFILES
    function mostrarPerfiles(users, container){
        // Si había un Masonry activo (de imágenes), se destruye
        if(msnry){
            msnry.destroy();
            msnry = null;
        }
        
        // Estructura HTML para los resultados de usuario
        let html = `
            <div class="mb-3">
                <button class="btn custom-btn-secondary btn-sm" onclick="location.reload()">
                    <i class="uil uil-arrow-left me-1"></i> Volver al feed
                </button>
            </div>
            <h4 class="mb-4">Resultados de perfiles:</h4>
            <div class="row g-3">
        `;
        
        // Recorre y muestra cada perfil encontrado
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

    // -----------------------------------------------------
    //--> MUESTRA RESULTADOS DE IMÁGENES CON MASONRY

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
        // Genera una tarjeta visual por cada imagen
        images.forEach((img, index) => {
            const cardWrapper = document.createElement('div');
            cardWrapper.classList.add('feed-grid-item');

            // Muestra ícono si la imagen es privada
            const badge = img.visibility === 1 
                ? `<div class="feed-img-privacy"><i class="uil uil-lock"></i></div>` 
                : '';

            // Altura aleatoria según viewport, para efecto "Pinterest"
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

    // -----------------------------------------------------
    //--> CONFIGURA E INICIALIZA MASONRY
    function initMasonry(container) {
        try {
            
            // Configuración de gutter según viewport
            // Define el espacio entre columnas según dispositivo
            const gutterSize = window.innerWidth <= 767 ? 8 : 10;
            
            // Inicializa la cuadrícula Masonry
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
            // Vuelve a organizar al cargar imágenes
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

            // Recalcula cuando todas las imágenes estén listas
            function checkAllLoaded() {
                if(loadedCount === totalImages && msnry) {
                    setTimeout(() => {
                        msnry.layout();
                    }, 50);
                }
            }

            // Relayout al redimensionar ventana
            // Reorganiza al cambiar el tamaño de la ventana
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
//--> Toggle Like
// Marca/desmarca "like" en una imagen.
// Si el usuario no está logueado, abre el modal de login.
window.toggleLike = function(imageId, button){
    if(!window.isLoggedIn){
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        return;
    }
    button.classList.toggle('liked');
    console.log('Toggle like en imagen:', imageId);
};