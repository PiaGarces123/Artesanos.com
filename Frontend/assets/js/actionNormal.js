// actionNormal.js - Orquestador general (manejo de Logout y funciones globales)


//Esta funcion se define para mostrar un modal de notificación estática y es una funcion global
    const showStaticNotificationModal = (type, message, acceptCallback = null) => {
        let modalEl = document.getElementById('staticNotificationModal');
        let modalIcon = document.getElementById('notificationIconStatic');
        let modalMessage = document.getElementById('notificationMessageStatic');
        let acceptBtn = document.getElementById('notificationAcceptButton');
        let staticNotificationModalLabel = document.getElementById('staticNotificationModalLabel');

        if (!modalEl || !modalIcon || !modalMessage || !acceptBtn || !staticNotificationModalLabel) return;

        let modalContent = modalEl.querySelector('.modal-content');
        modalContent.classList.remove('alert-success', 'alert-danger');
        staticNotificationModalLabel.classList.remove('text-primary');
        
        if (type === 'success') {
            modalIcon.innerHTML = '✅';
            modalContent.classList.add('alert-success');
            staticNotificationModalLabel.classList.add('text-primary');
        } else {
            modalIcon.innerHTML = '⚠️';
            modalContent.classList.add('alert-danger');
            staticNotificationModalLabel.classList.remove('text-primary');
        }
        
        modalMessage.textContent = message;
        
        let newAcceptBtn = acceptBtn.cloneNode(true);
        acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);
        
        let finalAcceptBtn = document.getElementById('notificationAcceptButton');
        let staticModalInstance = new bootstrap.Modal(modalEl);

        finalAcceptBtn.addEventListener('click', () => {
            if (acceptCallback) {
                acceptCallback();
            }
            staticModalInstance.hide();
        });
        staticModalInstance.show();
    };

document.addEventListener("DOMContentLoaded", () => {
    
    
    // =====================================================
    // LÓGICA DE CERRAR SESIÓN (¡ACTUALIZADA!)
    // =====================================================
    const logoutLink = document.getElementById("logoutLink");
    const logoutModalEl = document.getElementById('confirmLogoutModal');

    if (logoutLink && logoutModalEl) {
        
        // 1. Crear la instancia del nuevo modal
        const logoutModal = new bootstrap.Modal(logoutModalEl);
        
        // 2. El botón del header ahora ABRE EL MODAL
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            
            // 3. Buscar el botón de confirmación DENTRO del modal
            let confirmBtn = document.getElementById('confirmLogoutButton');
            if (!confirmBtn) return;
            
            // 4. Usar tu patrón cloneNode para limpiar listeners
            let newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // 5. Adjuntar el listener de "Sí, cerrar sesión"
            newConfirmBtn.addEventListener('click', () => {
                // Aquí va la acción real de cerrar sesión
                window.location.href = './BACKEND/Validation/logout.php'; 
            }, { once: true });
            
            // 6. Mostrar el modal
            logoutModal.show();
        });
    }

});