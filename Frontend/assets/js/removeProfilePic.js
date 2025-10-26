document.addEventListener("DOMContentLoaded", () => {

    const mostrarError = (div, input, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
        if (input) input.classList.add("errorInput");
    };

    const showStaticNotificationModal = (type, message, acceptCallback = null) => {
        let modalEl = document.getElementById('staticNotificationModal');
        let modalIcon = document.getElementById('notificationIconStatic');
        let modalMessage = document.getElementById('notificationMessageStatic');
        let acceptBtn = document.getElementById('notificationAcceptButton');
        
        if (!modalEl || !modalIcon || !modalMessage || !acceptBtn) return;

        // Configurar estilos y contenido
        let modalContent = modalEl.querySelector('.modal-content');
        
        // Limpiamos clases de estado
        modalContent.classList.remove('alert-success', 'alert-danger');
        
        if (type === 'success') {
            modalIcon.innerHTML = '🎉';
            modalContent.classList.add('alert-success');
        } else {
            modalIcon.innerHTML = '⚠️';
            modalContent.classList.add('alert-danger');
        }
        
        modalMessage.textContent = message;
        
        // 💡 1. Limpiamos y recreamos el listener del botón Aceptar
        // Clonar para eliminar listeners antiguos
        let newAcceptBtn = acceptBtn.cloneNode(true);
        acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);
        
        let finalAcceptBtn = document.getElementById('notificationAcceptButton');
        let staticModalInstance = new bootstrap.Modal(modalEl); // Creamos la instancia para mostrar

        finalAcceptBtn.addEventListener('click', () => {
            // 2. Ejecutar la acción de callback (redirección/recarga)
            if (acceptCallback) {
                acceptCallback();
            }
            // 3. Cerrar el modal (si la acción no fue una redirección que ya lo cerraría)
            staticModalInstance.hide();
        });

        // Mostrar el modal
        staticModalInstance.show();
    };

    // Objeto Modal
    const confirmRemoveProfilePicModalEl = document.getElementById('confirmRemoveProfilePicModal');
    const confirmRemoveProfilePicModal = confirmRemoveProfilePicModalEl ? (bootstrap.Modal.getInstance(confirmRemoveProfilePicModalEl) || new bootstrap.Modal(confirmRemoveProfilePicModalEl)) : null;


    function initRemoveProfilePic() {

        const removeProfilePicBtn = document.getElementById("removeProfilePicButton");


        if (removeProfilePicBtn) {
            removeProfilePicBtn.addEventListener("click", () => {

                let confirmBtn = document.getElementById('confirmRemoveProfilePicButton');

                if (confirmRemoveProfilePicModal && confirmBtn) {
                    // 1. Limpiamos el listener anterior y adjuntamos el nuevo ID
                    let newConfirmBtn = confirmBtn.cloneNode(true);
                    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

                    let finalConfirmBtn = document.getElementById('confirmRemoveProfilePicButton');

                    // 2. Adjuntar el listener de confirmación al nuevo botón
                    finalConfirmBtn.addEventListener('click', async e => {
                        e.preventDefault();
                        try {
                            const res = await fetch("./BACKEND/FuncionesPHP/removeProfilePic.php");
                            
                            // Obtenemos la respuesta como texto y la parseamos.
                            let data = await res.json();

                            let callback = null;
                            let message = data.message || "Operación completada.";
                            let type = data.status || 'error';

                            // Cerramos el modal de confirmación
                            confirmRemoveProfilePicModal.hide();

                            // 1. Manejo de Éxito
                            if (type === 'success') {
                                // Cierra el modal de opciones ANTES de mostrar la notificación
                                const editProfileModal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
                                if (editProfileModal) editProfileModal.hide();

                                // Recarga la página al aceptar
                                callback = () => window.location.reload(); 
                            }
                            // 2. Manejo de Error de Sesión
                            else if (type === 'errorSession') {
                                // Redirige al logout.php al aceptar
                                message = "Sesión expirada. Por favor, vuelve a iniciar sesión.";
                                callback = () => window.location.href = './BACKEND/Validation/logout.php';
                            }
                            // 3. Manejo de Error de Validación o Interno (General)
                            else if (type === 'error') {
                                // No hay callback, solo muestra el mensaje de error
                                message = "Error: " + message;
                            } 
                            // 4. Manejo de Error de Lógica Final (el último 'else' de tu estructura)
                            else {
                                // Esto captura cualquier otro error del servidor que no clasificaste.
                                message = "Error inesperado: " + message;
                            }
                            
                            // Muestra el modal de notificación fijo con el mensaje y el callback
                            showStaticNotificationModal(type, message, callback);
                            
                        } catch (error) { 
                            let errorEditProfile = document.getElementById("errorEditProfile");
                            mostrarError(errorEditProfile, null, "Error crítico de conexión o respuesta no válida."); 
                            console.error("Fallo en el fetch:", error.message);
                        }
                    }, { once: true }); // Usamos { once: true } para que el listener se elimine solo
                    
                    // 3. Mostrar el modal
                    confirmRemoveProfilePicModal.show();
                }


            });
        }

    }

    initRemoveProfilePic();


});