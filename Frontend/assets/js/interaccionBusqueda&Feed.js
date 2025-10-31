// Orquestador principal
// Coordina la inicialización de módulos de feed y búsqueda

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================================
    // 1. EVENT LISTENERS GENERALES
    // =========================================================================
    
    // Evento de logout
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Estás seguro de que quieres cerrar tu sesión?')) {
                window.location.href = './BACKEND/Validation/logout.php';
            }
        });
    }
    
    // Aquí pueden ir otros event listeners globales
    // que no pertenezcan específicamente a feed o búsqueda
});