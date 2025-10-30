document.addEventListener("DOMContentLoaded", () => {
    
    const followButton = document.getElementById("followBtn");
    
    if (!followButton) {
        return; // No hay botón de seguir
    }

    const updateButtonState = (action, targetId) => {
        followButton.disabled = false;

        if (action === 'follow') {
            followButton.innerHTML = '<i class="uil uil-user-plus me-1"></i> Seguir';
            followButton.className = 'btn btn-primary';
            followButton.dataset.action = 'follow';
        } else if (action === 'pending') {
            followButton.innerHTML = '<i class="uil uil-clock-three me-1"></i> Pendiente';
            followButton.className = 'btn btn-outline-secondary';
            followButton.dataset.action = 'unfollow';
        } else if (action === 'following') {
            followButton.innerHTML = '<i class="uil uil-user-minus me-1"></i> Dejar de Seguir';
            followButton.className = 'btn btn-outline-danger';
            followButton.dataset.action = 'unfollow';
        }
    };

    // -------------------------------------------------------------
    // 1. FUNCIÓN "TRABAJADORA" (Contiene lógica de fetch)
    // -------------------------------------------------------------
    const executeFollowFetch = async (endpoint, targetUserId) => {
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
                // Actualizar el botón al nuevo estado
                updateButtonState(data.newState, targetUserId);
                // (Aquí también puedes actualizar el contador de seguidores)
                
            } else if (data.status === 'errorSession') {
                window.location.href = './index.php'; // Redirigir si la sesión expiró
            } else {
                alert(data.message);
                followButton.disabled = false; // Vuelve a habilitar si falló
            }

        } catch (error) {
            console.error('Error en executeFollowFetch:', error);
            alert('Error de red. Inténtalo de nuevo.');
            followButton.disabled = false; // Vuelve a habilitar si falló
        }
    };

    // -------------------------------------------------------------
    // 2. FUNCIÓN "GUARDIA" (Maneja el modal)
    // -------------------------------------------------------------
    const handleFollowAction = (action, targetUserId) => {
        let endpoint = '';
        
        if (action === 'follow') {
            // --- ACCIÓN DE SEGUIR ---
            endpoint = './BACKEND/FuncionesPHP/solicitarSeguimiento.php';
            // Ejecutar directamente, sin modal
            executeFollowFetch(endpoint, targetUserId);

        } else if (action === 'unfollow') {
            // --- ACCIÓN DE DEJAR DE SEGUIR ---
            endpoint = './BACKEND/FuncionesPHP/eliminarSeguimiento.php';
            
            // 1. Obtener elementos del modal (usando IDs de tu historial)
            const confirmModalEl = document.getElementById('confirmUnfollowModal');
            const confirmBtn = document.getElementById('confirmUnfollowButton');

            if (!confirmModalEl || !confirmBtn) {
                console.error("Modal de confirmación no encontrado. Ejecutando acción directamente.");
                executeFollowFetch(endpoint, targetUserId); // Fallback
                return;
            }
            
            // 2. Preparar el modal
            const confirmModal = bootstrap.Modal.getInstance(confirmModalEl) || new bootstrap.Modal(confirmModalEl);
            
            // Mensaje personalizado (importante por tu lógica de negocio)
            
            // 3. Clonar botón (tu patrón para limpiar listeners)
            let newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // 4. Adjuntar listener de "Sí, eliminar"
            newConfirmBtn.addEventListener('click', () => {
                // El usuario confirmó. Llamamos a la función "trabajadora"
                executeFollowFetch(endpoint, targetUserId);
                confirmModal.hide();
            }, { once: true });

            // 5. Mostrar el modal
            confirmModal.show();
        }
    };

    // -------------------------------------------------------------
    // 3. LISTENER DEL BOTÓN 
    // -------------------------------------------------------------
    followButton.addEventListener('click', (e) => {
        const action = e.currentTarget.dataset.action;
        const targetUserId = e.currentTarget.dataset.targetUserId;
        
        if (action && targetUserId) {
            handleFollowAction(action, targetUserId);
        }
    });
});