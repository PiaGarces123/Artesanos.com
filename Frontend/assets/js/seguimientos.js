document.addEventListener("DOMContentLoaded", function() {

    // =========================================================================
    // 1. FUNCIONES AUXILIARES (Tus funciones existentes)
    // =========================================================================

    const mostrarError = (div, input, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
        if (input) input.classList.add("errorInput");
    };
 


    //Funcion para rechazar solicitud de seguimiento
    async function rejectFollowRequest(userId) {
        // Div de error dentro del modal de solicitudes
        const errorDiv = document.getElementById('errorRequestsModal'); 
        
        try {
            let formData = new FormData();
            formData.append('targetUserId', userId); // El ID del usuario a rechazar

            const response = await fetch('./BACKEND/FuncionesPHP/rechazarSeguimiento.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Error de red al rechazar la solicitud.');
            }

            const data = await response.json();

            if (data.status === 'success') {
                // -----------------------------------------------------------
                // 💡 ¡ÉXITO! Volvemos a llenar el modal.
                // -----------------------------------------------------------
                loadPendingRequests(); 
                
                // También actualizamos la insignia de la campana
                updateNotificationBadge(); 
                
            } else if (data.status === 'errorSession') {
                // Si la sesión expira, cerramos el modal y mostramos la notificación global
                const requestsModalEl = document.getElementById('requestsModal');
                const modalInstance = bootstrap.Modal.getInstance(requestsModalEl);
                if (modalInstance) modalInstance.hide();
                
                // Usamos la función global que ya tienes
                showStaticNotificationModal('error', data.message, () => {
                    window.location.reload();
                });
                
            } else {
                // Otro error (ej. "usuario no encontrado")
                if (typeof mostrarError === 'function') {
                    mostrarError(errorDiv, null, data.message);
                }
            }

        } catch (error) {
            console.error('Error en rejectFollowRequest:', error);
            if (typeof mostrarError === 'function') {
                mostrarError(errorDiv, null, error.message);
            }
        }
    }

    //Funcion para aceptar solicitud de seguimiento
    async function acceptFollowRequest(userId) {
        // Div de error dentro del modal de solicitudes
        const errorDiv = document.getElementById('errorRequestsModal'); 
        
        try {
            let formData = new FormData();
            formData.append('targetUserId', userId); // El ID del usuario a aceptar

            // 1. Llamar al nuevo endpoint
            const response = await fetch('./BACKEND/FuncionesPHP/aceptarSeguimiento.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Error de red al aceptar la solicitud.');
            }

            const data = await response.json();

            if (data.status === 'success') {
                // 2. ¡Éxito! Recargar la lista de solicitudes pendientes
                loadPendingRequests(); 
                
                // 3. Actualizar la insignia de la campana
                updateNotificationBadge(); 
                
                // 4. (Opcional) Mostrar una notificación de éxito
                // showStaticNotificationModal('success', data.message);

            } else if (data.status === 'errorSession') {
                // Manejo de sesión (igual que en reject)
                const requestsModalEl = document.getElementById('requestsModal');
                const modalInstance = bootstrap.Modal.getInstance(requestsModalEl);
                if (modalInstance) modalInstance.hide();
                
                showStaticNotificationModal('error', data.message, () => {
                    window.location.reload();
                });
                
            } else {
                // Otro error (ej. "fallo al crear álbum")
                if (typeof mostrarError === 'function') {
                    mostrarError(errorDiv, null, data.message);
                } else {
                    alert(data.message); // Fallback
                }
            }

        } catch (error) {
            console.error('Error en acceptFollowRequest:', error);
            if (typeof mostrarError === 'function') {
                mostrarError(errorDiv, null, error.message);
            }
        }
    }

    //para mostrar la cantidad de notificaciones pendientes
    async function updateNotificationBadge() {
        
        // Usamos la variable global 'logged_in_user_id' definida en modals.php
        if (!logged_in_user_id) {
            return; // No hacer nada si el usuario no ha iniciado sesión
        }

        // 1. Seleccionar la insignia
        const badge = document.getElementById('notificationBadge');
        if (!badge) {
            // Si no existe la insignia en la página, no continuar
            return;
        }

        // 2. Hacer fetch al backend para obtener el conteo
        try {
            const response = await fetch('./BACKEND/FuncionesPHP/obtenerConteoSeguidoresPendientes.php');
            
            if (!response.ok) {
                throw new Error('Error de red al obtener notificaciones.');
            }

            const data = await response.json();

            if (data.status === 'success') {
                const count = parseInt(data.count, 10);

                // 3. Actualizar el DOM
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('d-none'); // Mostrar la insignia
                } else {
                    badge.classList.add('d-none'); // Ocultar si el conteo es 0
                }
            } else {
                // Ocultar si hay un error de sesión o de lógica
                badge.classList.add('d-none');
            }

        } catch (error) {
            console.error('Error en updateNotificationBadge:', error);
            badge.classList.add('d-none'); // Ocultar si hay un error
        }
    }


    
    //Carga las solicitudes de seguimiento pendientes en el modal.
    async function loadPendingRequests() {
        const container = document.getElementById('requestsModalContainer');
        const errorDiv = document.getElementById('errorRequestsModal');
        
        // Limpiar errores previos y mostrar spinner (el spinner ya está en el HTML)
        if(errorDiv) errorDiv.textContent = ''; 

        try {
            // 1. Hacemos el fetch al nuevo endpoint de PHP
            // (Este script usará la SESIÓN para saber de quién son las solicitudes)
            const response = await fetch('./BACKEND/FuncionesPHP/getPendingFollows.php');

            if (!response.ok) {
                throw new Error('Error de red al cargar las solicitudes.');
            }
            
            const requests = await response.json();

            // 2. Comprobar si hay resultados
            if (requests.length === 0) {
                container.innerHTML = '<div class="alert alert-info text-center">No tienes solicitudes pendientes.</div>';
                return;
            }

            // 3. Construir el HTML de la lista
            let html = '<ul class="list-group list-group-flush">';
            
            requests.forEach(req => {
                // El PHP (que te doy abajo) ya nos dará la ruta correcta
                // o la imagen por defecto.
                const profilePic = req.U_profilePic; 
                const username = req.U_nameUser;
                const userId = req.U_id; 

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        
                        <div class="d-flex align-items-center">
                            <img src="${profilePic}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <strong class="d-block">${username}</strong>
                                <small class="text-muted">Quiere seguirte.</small>
                            </div>
                        </div>
                        
                        <div class="ms-2">
                            <button class="btn btn-success btn-sm" data-action="accept" data-user-id="${userId}">
                                <i class="uil uil-check"></i> Aceptar
                            </button>
                            <button class="btn btn-danger btn-sm ms-2" data-action="reject" data-user-id="${userId}">
                                <i class="uil uil-times"></i> Rechazar
                            </button>
                        </div>
                    </li>
                `;
            });
            html += '</ul>';

            // 4. Inyectar el HTML en el contenedor
            container.innerHTML = html;

            // 5. Llamar a la función que crearás luego
            attachFollowListeners();

        } catch (error) {
            console.error('Error en loadPendingRequests:', error);
            
            // (Asumiendo que tienes tu función 'mostrarError' disponible)
            if (typeof mostrarError === 'function') {
                mostrarError(errorDiv, null, error.message);
            }
            
            container.innerHTML = '<div class="alert alert-danger text-center">Error al cargar las solicitudes.</div>';
        }
    }


    //agregar listeners a los botones aceptar y rechazar
    function attachFollowListeners() {
        const container = document.getElementById('requestsModalContainer');
        
        container.querySelectorAll('button[data-action="accept"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.currentTarget.dataset.userId;
                // Deshabilitamos ambos botones para evitar clics duplicados
                e.currentTarget.closest('.list-group-item').querySelectorAll('button').forEach(b => b.disabled = true);
                e.currentTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                // Llamada a la función de aceptar
                acceptFollowRequest(userId);
            });
        });

        container.querySelectorAll('button[data-action="reject"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.currentTarget.dataset.userId;
                // Deshabilitamos ambos botones
                e.currentTarget.closest('.list-group-item').querySelectorAll('button').forEach(b => b.disabled = true);
                e.currentTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                // Llamada a la función de rechazar
                rejectFollowRequest(userId);
            });
        });
    }

    updateNotificationBadge();

    const requestsModalEl = document.getElementById('requestsModal');
    
    if (requestsModalEl) {
        
        // 1. Escuchar el evento 'shown.bs.modal' (cuando el modal se termina de abrir)
        requestsModalEl.addEventListener('shown.bs.modal', () => {
            // 2. Llamar a tu función para cargar los datos
            loadPendingRequests();
        });

        // 3. (Opcional) Limpiar el modal cuando se cierra
        requestsModalEl.addEventListener('hidden.bs.modal', () => {
            const container = document.getElementById('requestsModalContainer');
            // Devolvemos el spinner para la próxima vez que se abra
            container.innerHTML = `<p class="text-center mt-3 text-secondary">
                                      <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                      Cargando solicitudes...
                                   </p>`;
        });
    }
});