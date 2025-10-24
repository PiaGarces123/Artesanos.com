// restrictedActions.js

document.addEventListener("DOMContentLoaded", () => {
    const userLoggedIn = window.isLoggedIn || false;

    // ----------------- INICIALIZACIÓN DE MODALES DE BOOTSTRAP -----------------
    const createAlbumModalEl = document.getElementById("createAlbumModal");
    const myAlbumsModalEl = document.getElementById("myAlbumsModal");
    const favoritesModalEl = document.getElementById("favoritesModal");
    const loginModalEl = document.getElementById("loginModal"); 

    const createAlbumModal = createAlbumModalEl ? new bootstrap.Modal(createAlbumModalEl) : null;
    const myAlbumsModal = myAlbumsModalEl ? new bootstrap.Modal(myAlbumsModalEl) : null;
    const favoritesModal = favoritesModalEl ? new bootstrap.Modal(favoritesModalEl) : null;
    const loginModal = loginModalEl ? new bootstrap.Modal(loginModalEl) : null;


    // Botones que requieren verificación de sesión (EXCLUIMOS navHome)
    const restrictedButtons = [
        // Botones de acción (Publicar/Álbumes) - Desktop (IDs originales)
        { id: "createAlbumBtn", action: () => openCreateAlbumModal() },
        { id: "myAlbumsBtn", action: () => openMyAlbumsModal() },
        // Botones de acción (Publicar/Álbumes) - Mobile (IDs Mobile)
        { id: "createAlbumBtnMobile", action: () => openCreateAlbumModal() },
        { id: "myAlbumsBtnMobile", action: () => openMyAlbumsModal() },
        
        // Botones de navegación - Desktop
        { id: "navFavoritesDesktop", action: () => openFavoritesModal() },
        { id: "navProfileDesktop", action: () => {window.location.href = './profile.php'}  },
        
        // Botones de navegación - Mobile
        { id: "navFavoritesMobile", action: () => openFavoritesModal() },
        { id: "navProfileMobile", action: () => {window.location.href = './profile.php'}  },
        
        // Botones de busqueda
        { id: "searchButtonMobile", action: () => {} /* realizar busqueda */},
        { id: "searchButtonDesktop", action: () => {} /* realizar busqueda */}
    ];

    restrictedButtons.forEach(btnData => {
        const btn = document.getElementById(btnData.id);
        if (!btn) return;

        btn.addEventListener("click", (e) => {
            // Prevenimos la acción predeterminada para TODOS los botones en esta lista
            e.preventDefault();
            
            if (userLoggedIn) {
                // 1. Ejecutar acción normal
                btnData.action(); 
                
                // 2. Si la acción viene de un botón móvil, cerramos el Offcanvas
                const offcanvasEl = document.getElementById('sidebarOffcanvas');
                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvasInstance) {
                    offcanvasInstance.hide();
                }
            } else {
                // Si no está logueado, forzamos la apertura del modal de Login/Signup
                if (loginModal) {
                    loginModal.show();
                    
                    // Cerramos el Offcanvas antes de mostrar el Login
                    const offcanvasEl = document.getElementById('sidebarOffcanvas');
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvasInstance) {
                        offcanvasInstance.hide();
                    }
                }
            }
        });
    });

    // ----------------- FUNCIONES DE LOS MODALES DE CONTENIDO -----------------

    function openCreateAlbumModal() {
        if (createAlbumModal) createAlbumModal.show();
    }

    function openMyAlbumsModal() {
        if (myAlbumsModal) myAlbumsModal.show();
    }

    function openFavoritesModal() {
        if (favoritesModal) favoritesModal.show();
    }
    
    // ====================================================================
    // LÓGICA DE DRAG AND DROP (Subida de Archivos)
    // ====================================================================
    const fileUploadArea = document.getElementById("fileUpload");
    const imageInput = document.getElementById("imageInput");

    if (fileUploadArea && imageInput) {
        // 1. Enlazar el click del área visual al input de archivo oculto
        fileUploadArea.addEventListener("click", () => {
            imageInput.click();
        });

        // 2. Manejar Drag and Drop: Añadir clase 'dragover' al arrastrar
        fileUploadArea.addEventListener("dragover", (e) => {
            e.preventDefault(); // Necesario para permitir el drop
            e.stopPropagation();
            fileUploadArea.classList.add("dragover");
        });

        fileUploadArea.addEventListener("dragleave", (e) => {
            e.stopPropagation();
            fileUploadArea.classList.remove("dragover");
        });
        
        // 3. Manejar la selección o el drop de archivos
        const handleFiles = (files) => {
            // Lógica para previsualización o validación de archivos aquí
            fileUploadArea.classList.remove("dragover");
        };

        fileUploadArea.addEventListener("drop", (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (e.dataTransfer.files.length > 0) {
                // Asigna los archivos arrastrados al input
                imageInput.files = e.dataTransfer.files; 
                handleFiles(e.dataTransfer.files);
            }
        });

        // 4. Limpiar la clase visual cuando se selecciona un archivo (usando el click)
        imageInput.addEventListener("change", () => {
            handleFiles(imageInput.files);
        });
    }
});