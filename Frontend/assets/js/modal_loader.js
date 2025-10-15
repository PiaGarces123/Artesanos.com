document.addEventListener("DOMContentLoaded", () => {
    const loginBtn = document.getElementById("loginBtn");
    const feedImgs = document.querySelectorAll(".feed-img");

    const openLoginModal = async () => {
        const container = document.getElementById("modalContainer");

        // Si no está cargado el HTML del modal, cargarlo
        if (!container.innerHTML.trim()) {
            const res = await fetch("/ProgIII/Artesanos.com/Frontend/pages/login.html");
            container.innerHTML = await res.text();

            // Cargar flatpickr
            if (!document.querySelector('script[src="https://cdn.jsdelivr.net/npm/flatpickr"]')) {
                await new Promise((resolve, reject) => {
                    const s = document.createElement("script");
                    s.src = "https://cdn.jsdelivr.net/npm/flatpickr";
                    s.onload = resolve;
                    s.onerror = reject;
                    document.body.appendChild(s);
                });
            }

            const fNac = document.querySelector("#fNac");
            if (fNac && window.flatpickr) {
                flatpickr(fNac, { dateFormat: "d-m-Y", altInput: true, altFormat: "d-m-Y", allowInput: true });
            }

            // Cargar modal_login.js solo una vez
            if (!document.querySelector('script[src="/ProgIII/Artesanos.com/Frontend/assets/js/modal_login.js"]')) {
                const s = document.createElement("script");
                s.src = "/ProgIII/Artesanos.com/Frontend/assets/js/modal_login.js";
                document.body.appendChild(s);
            }
        }

        // Mostrar modal
        const loginModal = document.getElementById("loginModal");
        if (loginModal) {
            loginModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
    };

    // Activadores
    loginBtn?.addEventListener("click", openLoginModal);
    feedImgs.forEach(img => img.addEventListener("click", openLoginModal));
});
