// busqueda.js - Sistema de búsqueda de perfiles e imágenes
// Gestiona la búsqueda y renderizado de resultados

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================================
    // 1. ELEMENTOS DEL DOM
    // =========================================================================
    
    const resultsContainer = document.getElementById('searchResultsContainer');
    const buscarBtnsDesktop = document.querySelectorAll("#searchAndFiltersDesktop .buscarPor-btn");
    const buscarBtnsMobile = document.querySelectorAll("#searchAndFiltersMobile .buscarPor-btn");
    const searchInputDesktop = document.getElementById("searchInput");
    const searchInputMobile = document.getElementById("searchInputMobile");
    const searchButtonDesktop = document.getElementById("searchButtonDesktop");
    const searchButtonMobile = document.getElementById("searchButtonMobile");

    
    // =========================================================================
    // 2. FUNCIONES AUXILIARES (UTILIDADES)
    // =========================================================================
    
    /**
     * Obtiene el tipo de búsqueda activo (perfil, imagen)
     * @param {NodeList} buttons - Lista de botones de filtro
     * @returns {string} - Tipo de búsqueda activo
     */
    const getActiveSearchType = (buttons) => {
        const activeBtn = Array.from(buttons).find(b => b.classList.contains('active'));
        return activeBtn ? activeBtn.dataset.buscarPor : 'perfil';
    };

    /**
     * Muestra un mensaje de error en el contenedor
     * @param {HTMLElement} container - Contenedor donde mostrar el error
     * @param {string} message - Mensaje de error
     */
    const mostrarError = (container, message) => {
        container.innerHTML = `
            <div class="alert alert-danger text-center mt-3">
                <i class="uil uil-exclamation-triangle fs-3"></i>
                <p class="mb-0 mt-2">${message}</p>
            </div>
        `;
    };

    /**
     * Muestra un spinner de carga
     * @param {HTMLElement} container - Contenedor donde mostrar el spinner
     * @param {string} message - Mensaje de carga
     */
    const mostrarSpinner = (container, message = 'Buscando...') => {
        container.innerHTML = `
            <div class="text-center mt-5 mb-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-secondary">${message}</p>
            </div>
        `;
    };


    // =========================================================================
    // 3. FUNCIONES DE RENDERIZADO
    // =========================================================================
    
    /**
     * Muestra resultados de perfiles en formato de tarjetas
     * @param {Array} users - Array de usuarios encontrados
     * @param {HTMLElement} container - Contenedor donde renderizar
     */
    const mostrarPerfiles = (users, container) => {
        // Destruir Masonry si existe (viene del módulo feed)
        if (window.feedModule && window.feedModule.destroyMasonry) {
            window.feedModule.destroyMasonry();
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
    };

    /**
     * Muestra mensaje cuando no hay resultados de búsqueda
     * @param {HTMLElement} container - Contenedor donde mostrar
     * @param {string} query - Término buscado
     */
    const mostrarSinResultados = (container, query) => {
        container.innerHTML = `
            <div class="custom-message-card text-center mt-5">
                <i class="uil uil-frown fs-1"></i>
                <p class="mt-3 fs-5">No se encontraron resultados para "<strong>${query}</strong>"</p>
                <button class="btn custom-btn-secondary" onclick="location.reload()">
                    <i class="uil uil-redo me-1"></i> Volver al feed
                </button>
            </div>
        `;
    };


    // =========================================================================
    // 4. FUNCIONES DE BÚSQUEDA (FETCH)
    // =========================================================================
    
    /**
     * Realiza una búsqueda según el término y tipo
     * @param {string} query - Término de búsqueda
     * @param {string} type - Tipo de búsqueda ('perfil' o 'imagen')
     */
    const realizarBusqueda = async (query, type) => {
        if (!resultsContainer) return;
        
        // Si no hay texto, vuelve al feed inicial
        if (!query.trim()) {
            if (window.feedModule && window.feedModule.cargarFeedInicial) {
                window.feedModule.cargarFeedInicial();
            }
            return;
        }
        
        mostrarSpinner(resultsContainer, 'Buscando...');
        
        const formData = new FormData();
        formData.append('query', query.trim());
        formData.append('searchType', type);
        
        try {
            const res = await fetch("./BACKEND/FuncionesPHP/buscar.php", { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                if (data.results.length === 0) {
                    mostrarSinResultados(resultsContainer, data.query);
                } else {
                    if (type === 'perfil') {
                        mostrarPerfiles(data.results, resultsContainer);
                    } else {
                        // Usar la función del módulo feed para mostrar imágenes con Masonry
                        if (window.feedModule && window.feedModule.mostrarImagenesFeed) {
                            window.feedModule.mostrarImagenesFeed(data.results, resultsContainer);
                        }
                    }
                }
            } else {
                mostrarError(resultsContainer, data.message || 'Error en la búsqueda.');
            }
        } catch (err) {
            console.error(err);
            mostrarError(resultsContainer, 'Error de conexión.');
        }
    };


    // =========================================================================
    // 5. EVENT LISTENERS
    // =========================================================================
    
    // Marcar botón activo (tipo de búsqueda)
    [...buscarBtnsDesktop, ...buscarBtnsMobile].forEach(btn => {
        btn.addEventListener('click', () => {
            const parent = btn.closest('#searchAndFiltersDesktop, #searchAndFiltersMobile');
            parent.querySelectorAll(".buscarPor-btn").forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Eventos de búsqueda en Desktop
    if (searchInputDesktop && searchButtonDesktop) {
        searchButtonDesktop.addEventListener('click', () => 
            realizarBusqueda(searchInputDesktop.value, getActiveSearchType(buscarBtnsDesktop))
        );
        searchInputDesktop.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchButtonDesktop.click();
            }
        });
    }

    // Eventos de búsqueda en Móvil
    if (searchInputMobile && searchButtonMobile) {
        searchButtonMobile.addEventListener('click', () => {
            realizarBusqueda(searchInputMobile.value, getActiveSearchType(buscarBtnsMobile));
            // Cerrar offcanvas móvil después de buscar
            const offcanvasEl = document.getElementById('sidebarOffcanvas');
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvasInstance) offcanvasInstance.hide();
        });
        searchInputMobile.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchButtonMobile.click();
            }
        });
    }
});