// actionNormal.js - Orquestador general (manejo de Logout y funciones globales)

document.addEventListener("DOMContentLoaded", () => {
    
    
    // =====================================================
    // MANEJO DE CIERRE DE SESIÓN (LOGOUT)
    // =====================================================

    const logoutLink = document.getElementById('logoutLink');

    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            // Prevenir la acción por defecto
            e.preventDefault();
            
            // Mostrar diálogo de confirmación antes de redirigir
            if (confirm('¿Estás seguro de que quieres cerrar tu sesión?')) {
                // Redirigir al script de logout PHP
                window.location.href = './BACKEND/Validation/logout.php'; 
            }
        });

    }

});