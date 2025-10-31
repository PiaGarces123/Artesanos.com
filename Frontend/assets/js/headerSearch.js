// headerSearch.js - Manejo de búsqueda desde el header
// Redirige a busqueda.php cuando se hace una búsqueda

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================================
    // 1. ELEMENTOS DEL DOM
    // =========================================================================
    
    const searchInputDesktop = document.getElementById("searchInput");
    const searchInputMobile = document.getElementById("searchInputMobile");
    const searchButtonDesktop = document.getElementById("searchButtonDesktop");
    const searchButtonMobile = document.getElementById("searchButtonMobile");
    const buscarBtnsDesktop = document.querySelectorAll("#searchAndFiltersDesktop .buscarPor-btn");
    const buscarBtnsMobile = document.querySelectorAll("#searchAndFiltersMobile .buscarPor-btn");

    
    // =========================================================================
    // 2. FUNCIONES AUXILIARES
    // =========================================================================
    
    /**
     * Obtiene el tipo de búsqueda activo
     */
    const getActiveSearchType = (buttons) => {
        const activeBtn = Array.from(buttons).find(b => b.classList.contains('active'));
        return activeBtn ? activeBtn.dataset.buscarPor : 'perfil';
    };

    /**
     * Redirige a la página de búsqueda con parámetros
     */
    const redirigirABusqueda = (query, type) => {
        if (!query.trim()) {
            // Si no hay texto, no hacer nada o mostrar alerta
            return;
        }
        
        const url = `./busqueda.php?q=${encodeURIComponent(query.trim())}&type=${type}`;
        window.location.href = url;
    };

    // Marcar botón activo (tipo de búsqueda)
    [...buscarBtnsDesktop, ...buscarBtnsMobile].forEach(btn => {
        btn.addEventListener('click', () => {
            const parent = btn.closest('#searchAndFiltersDesktop, #searchAndFiltersMobile');
            parent.querySelectorAll(".buscarPor-btn").forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
    
    // =========================================================================
    // 3. EVENT LISTENERS
    // =========================================================================

    // Búsqueda desde Desktop
    if (searchInputDesktop && searchButtonDesktop) {
        searchButtonDesktop.addEventListener('click', () => {
            const query = searchInputDesktop.value;
            const type = getActiveSearchType(buscarBtnsDesktop);
            redirigirABusqueda(query, type);
        });

        searchInputDesktop.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchButtonDesktop.click();
            }
        });
    }

    // Búsqueda desde Móvil
    if (searchInputMobile && searchButtonMobile) {
        searchButtonMobile.addEventListener('click', () => {
            const query = searchInputMobile.value;
            const type = getActiveSearchType(buscarBtnsMobile);
            
            // Cerrar offcanvas antes de redirigir
            const offcanvasEl = document.getElementById('sidebarOffcanvas');
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvasInstance) offcanvasInstance.hide();
            
            // Pequeño delay para que se cierre el offcanvas
            setTimeout(() => {
                redirigirABusqueda(query, type);
            }, 300);
        });

        searchInputMobile.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchButtonMobile.click();
            }
        });
    }

    console.log('✅ Sistema de redirección a búsqueda inicializado');
});