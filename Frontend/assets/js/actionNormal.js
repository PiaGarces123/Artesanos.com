// Efectos del buscador y los botones del header
document.addEventListener("DOMContentLoaded", () => {
    const buscarBtns = document.querySelectorAll(".buscarPor-btn");
    const searchInput = document.getElementById("searchInput");

    // Efecto: botón activo (Perfil / Imagen / Ambos)
    buscarBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            buscarBtns.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
        });
    });

    // Efecto visual al enfocar y desenfocar el input de búsqueda
    if (searchInput) {
        searchInput.addEventListener("focus", () => {
            searchInput.style.backgroundColor = "#fff";
            searchInput.style.boxShadow = "0 0 5px rgba(0,0,0,0.3)";
        });

        searchInput.addEventListener("blur", () => {
            searchInput.style.backgroundColor = "";
            searchInput.style.boxShadow = "";
        });
    }
});