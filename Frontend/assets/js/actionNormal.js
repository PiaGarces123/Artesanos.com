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
});