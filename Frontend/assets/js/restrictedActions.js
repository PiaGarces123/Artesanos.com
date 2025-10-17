document.addEventListener("DOMContentLoaded", () => {
    // Variable que indica si hay sesión activa (desde tu script)
    const userLoggedIn = window.isLoggedIn || false;

    // Botones que requieren sesión
    const restrictedButtons = [
        { id: "createAlbumBtn", action: () => openCreateAlbumModal() },
        { id: "myAlbumsBtn", action: () => openMyAlbumsModal() },
        { id: "navFavorites", action: () => openFavoritesModal() },
        { id: "navProfile", action: () => {} /*window.location.href = "/profile"*/ }
        // Agregar aquí todos los botones que requieren sesión
    ];

    // Función para asignar el evento correcto según sesión
    restrictedButtons.forEach(btnData => {
        const btn = document.getElementById(btnData.id);
        if (!btn) return;

        btn.addEventListener("click", (e) => {
            e.preventDefault();
            if (userLoggedIn) {
                btnData.action(); // Ejecuta la función normal
            } else {
                window.openModal(); // Abre modal de login
            }
        });
    });

    // ----------------- FUNCIONES DE LOS MODALES -----------------

    function openCreateAlbumModal() {
        const modal = document.getElementById("createAlbumModal");
        if (!modal) return;

        modal.classList.add("active");
        document.body.style.overflow = "hidden";

        // Cerrar modal
        const closeBtn = document.getElementById("closeCreateModal");
        closeBtn?.addEventListener("click", () => closeModal(modal));
        modal.addEventListener("click", e => { if (e.target === modal) closeModal(modal); });
    }

    function openMyAlbumsModal() {
        const modal = document.getElementById("myAlbumsModal");
        if (!modal) return;

        modal.classList.add("active");
        document.body.style.overflow = "hidden";

        // Cerrar modal
        const closeBtn = document.getElementById("closeAlbumsModal");
        closeBtn?.addEventListener("click", () => closeModal(modal));
        modal.addEventListener("click", e => { if (e.target === modal) closeModal(modal); });
    }

    function openFavoritesModal() {
        const favoritesModal = document.getElementById("favoritesModal");
        if (!favoritesModal) return;

        favoritesModal.classList.add("active");
        document.body.style.overflow = "hidden";

        const closeBtn = favoritesModal.querySelector(".close-modal");
        closeBtn?.addEventListener("click", () => closeModal(favoritesModal));

        favoritesModal.addEventListener("click", (e) => {
            if (e.target === favoritesModal) closeModal(favoritesModal);
        });
    }



    function closeModal(modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "auto";
    }
});
