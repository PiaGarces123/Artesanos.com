// busquedaPage.js - Lógica principal de búsqueda y resultados en busqueda.php

document.addEventListener("DOMContentLoaded", () => {
    
    // Elementos principales
    const resultsContainer = document.getElementById('searchResultsContainer');
    
    // Variable global para guardar la instancia activa de Masonry
    let msnry = null; 

    // =====================================================
    // INICIAR BÚSQUEDA AL CARGAR LA PÁGINA
    // =====================================================
    
    // Asume que PHP ha inyectado estas variables globales en busqueda.php
    const query = window.initialSearchQuery || '';
    const type = window.initialSearchType || 'perfil'; 

    if (query) {
        realizarBusqueda(query, type);
    } else {
        // Muestra un mensaje si no hay una consulta válida (ej: URL manipulada)
        if(resultsContainer) {
             resultsContainer.innerHTML = `
                <div class="text-center mt-5 text-secondary">
                    <p class="mt-3">Realiza una búsqueda desde la barra superior.</p>
                </div>
            `;
        }
    }


    // =====================================================
    // FUNCIONES PRINCIPALES
    // =====================================================
    
    // Función para corregir la ruta relativa a la ubicación de busquedaPage.php
    const corregirRutaImagen = (ruta) => {
        if (!ruta) return '';
        // Quitar './' inicial si existe
        let rutaLimpia = ruta.replace(/^\.\/+/, '');
        // Convertir en ruta absoluta desde la raíz del servidor
        return '/' + rutaLimpia; 
    };

    // -----------------------------------------------------
    //--> EJECUTA LA BÚSQUEDA AJAX
    async function realizarBusqueda(searchQuery, searchType){
        if(!resultsContainer) return;

        console.log(`🔎 Buscando: "${searchQuery}" por tipo: ${searchType}`);

        // Muestra un spinner mientras carga los resultados
        resultsContainer.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">Buscando resultados...</p>
            </div>
        `;
        
        try{
            const formData = new FormData();
            formData.append('query', searchQuery);
            formData.append('type', searchType); // 'perfil' o 'imagen'
            
            // Petición al backend que devuelve los resultados de la búsqueda
            // (Asegúrate de que este archivo PHP exista y maneje los parámetros)
            const res = await fetch("./BACKEND/FuncionesPHP/buscar.php", {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if(data.status === 'success'){
                if(data.results.length === 0){
                    resultsContainer.innerHTML = `
                        <div class="text-center mt-5 text-secondary">
                            <i class="uil uil-search-alt fs-1"></i>
                            <p class="mt-3">No se encontraron resultados para "${searchQuery}".</p>
                        </div>
                    `;
                } else {
                    // Decide cómo mostrar los resultados
                    if (searchType === 'perfil') {
                        mostrarPerfiles(data.results, resultsContainer);
                    } else if (searchType === 'imagen') {
                        mostrarImagenes(data.results, resultsContainer);
                    } else {
                        // Por defecto, muestra como imágenes si no es un tipo conocido
                        mostrarImagenes(data.results, resultsContainer);
                    }
                }
            } else {
                // Error del backend
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger text-center mt-3">
                        <i class="uil uil-exclamation-triangle fs-3"></i>
                        <p class="mb-0 mt-2">Error en la búsqueda.</p>
                    </div>
                `;
            }
        } catch(err){
            // Error de conexión o de red
            console.error('Error de red al buscar:', err);
            resultsContainer.innerHTML = `
                <div class="alert alert-danger text-center mt-3">
                    <p class="mb-0 mt-2">Error de conexión al servidor.</p>
                </div>
            `;
        }
    }
    
    // -----------------------------------------------------
    //--> MUESTRA RESULTADOS DE PERFILES (Necesita ser implementado)
    // NOTA: Debes crear la estructura HTML para mostrar perfiles
    function mostrarPerfiles(profiles, container) {
        // Destruir Masonry si estaba activo
        if(msnry){
            msnry.destroy();
            msnry = null;
        }
        
        container.innerHTML = '<h4>Resultados de Perfiles</h4><div class="row">';
        
        profiles.forEach(profile => {
             // Ejemplo de tarjeta de perfil
             container.innerHTML += `
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card p-3 text-center">
                        <img src="${profile.profileImage || './Frontend/assets/images/appImages/default.jpg'}" class="rounded-circle mx-auto" style="width: 80px; height: 80px;">
                        <h5 class="mt-2">@${profile.username}</h5>
                        <p>${profile.fullName || 'Sin nombre'}</p>
                        <a href="./profile.php?user=${profile.username}" class="btn btn-primary btn-sm">Ver Perfil</a>
                    </div>
                </div>
             `;
        });

        container.innerHTML += '</div>';
        console.log(`✅ Mostrando ${profiles.length} perfiles.`);
    }

    // -----------------------------------------------------
    //--> MUESTRA RESULTADOS DE IMÁGENES CON MASONRY (Adaptado del original)
    function mostrarImagenes(images, container) {
        console.log('📸 Total de imágenes recibidas:', images.length);
        
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

            const badge = img.visibility === 1 
                ? `<div class="feed-img-privacy"><i class="uil uil-lock"></i></div>` 
                : '';

            // Altura aleatoria para efecto "Pinterest"
            let randHeight;
            if(window.innerWidth <= 767) {
                randHeight = Math.floor(Math.random() * (260 - 200 + 1)) + 200;
            } else if(window.innerWidth <= 1200) {
                randHeight = Math.floor(Math.random() * (340 - 240 + 1)) + 240;
            } else {
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
        });

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
                initLayout: false // Control manual
            });
            
            // Layout manual inicial
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

            // Layout inicial adicional
            setTimeout(() => {
                if(msnry) msnry.layout();
            }, 200);

        } catch(error) {
            console.error('Error al inicializar Masonry:', error);
        }
    }
});