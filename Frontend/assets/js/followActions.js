document.addEventListener("DOMContentLoaded", () => {
    
    const followButton = document.getElementById("followBtn");
    
    if (!followButton) {
        // No hay botón de seguir en esta página (ej. es mi propio perfil)
        return;
    }

    /**
     * Actualiza la apariencia del botón
     */
    const updateButtonState = (action, targetId) => {
        followButton.disabled = false;

        if (action === 'follow') {
            // Estado: SEGUIR (El usuario no sigue al otro)
            followButton.innerHTML = '<i class="uil uil-user-plus me-1"></i> Seguir';
            followButton.className = 'btn btn-primary';
            followButton.dataset.action = 'follow';

        } else if (action === 'pending') {
            // Estado: PENDIENTE
            followButton.innerHTML = '<i class="uil uil-clock-three me-1"></i> Pendiente';
            followButton.className = 'btn btn-outline-secondary';
            followButton.dataset.action = 'unfollow'; // La acción para cancelar es "unfollow"

        } else if (action === 'following') {
            // Estado: SIGUIENDO
            followButton.innerHTML = '<i class="uil uil-user-minus me-1"></i> Dejar de Seguir';
            followButton.className = 'btn btn-outline-danger';
            followButton.dataset.action = 'unfollow';
        }
    };

    /**
     * Función principal que llama al backend
     */
    const handleFollowAction = async (action, targetUserId) => {
        let endpoint = '';
        
        // 1. Determinar a qué script de PHP llamar
        if (action === 'follow') {
            endpoint = './BACKEND/FuncionesPHP/solicitarSeguimiento.php';
        } else if (action === 'unfollow') {
            endpoint = './BACKEND/FuncionesPHP/eliminarSeguimiento.php';
        } else {
            return; // Acción desconocida
        }

        let formData = new FormData();
        formData.append('targetUserId', targetUserId);
        
        // Deshabilitar botón mientras se envía
        followButton.disabled = true;
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor.');
            }

            const data = await response.json();

            if (data.status === 'success') {
                // 2. Actualizar el botón al nuevo estado
                updateButtonState(data.newState, targetUserId);
                // (Opcional) Actualizar el contador de followers en la página
                // if (document.getElementById('followersCount')) { ... }
                
            } else if (data.status === 'errorSession') {
                window.location.href = './index.php'; // Redirigir si la sesión expiró
            } else {
                // Mostrar error (puedes usar tu modal showStaticNotificationModal si está disponible)
                alert(data.message);
                followButton.disabled = false;
            }

        } catch (error) {
            console.error('Error en handleFollowAction:', error);
            alert('Error de red. Inténtalo de nuevo.');
            followButton.disabled = false;
        }
    };

    // Adjuntar el listener al botón
    followButton.addEventListener('click', (e) => {
        const action = e.currentTarget.dataset.action;
        const targetUserId = e.currentTarget.dataset.targetUserId;
        
        if (action && targetUserId) {
            handleFollowAction(action, targetUserId);
        }
    });
});