document.addEventListener("DOMContentLoaded", () => {
    // Función para limpiar todos los errores visuales
    const limpiarErrores = () => {
        document.querySelectorAll(".error").forEach(div => {
            div.textContent = "";
            div.classList.remove("visible-error");
        });
        document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
    };
    // Mostrar errores
    const mostrarError = (div, input, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
        if (input) input.classList.add("errorInput");
    };

    const validarCampo = (input, regex, errorDiv, msg) => {
        let isValid = true;
        input.classList.remove("errorInput");

        if (!input?.value.trim()) { 
            mostrarError(errorDiv, input, "Campo obligatorio."); 
            isValid = false; 
        } else if (regex && !regex.test(input.value)) { 
            mostrarError(errorDiv, input, msg); 
            isValid = false; 
        }

        if (!isValid) {
            input.classList.add("errorInput");
        }
        return isValid;
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


    function initModalChangePassword() {

        
        // inputs y botones
        
        const changePassCurrent = document.getElementById("changePassCurrent");
        const changePassNew = document.getElementById("changePassNew");
        const changePassNewConfirm = document.getElementById("changePassNewConfirm");
        const changePasswordBtn = document.getElementById("changePasswordBtn");

        const errorChangePassCurrent = document.getElementById("errorChangePassCurrent");
        const errorChangePassNew = document.getElementById("errorChangePassNew");
        const errorChangePassNewConfirm = document.getElementById("errorChangePassNewConfirm");
        

        // Regex
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/;

        // ====================================================================
        // EVENT LISTEN DE BOTON DE GUARDAR CAMBIOS
        // ====================================================================

        // Cambiar Contraseña submit
        if (changePasswordBtn) {
            changePasswordBtn.addEventListener("click", async e => {
                e.preventDefault(); 
                limpiarErrores();

                let valido = validarCampo(changePassCurrent, passRegex, errorChangePassCurrent, "Debe tener mayúscula, minúscula, número y símbolo");
                valido &= validarCampo(changePassNew, passRegex, errorChangePassNew, "Debe tener mayúscula, minúscula, número y símbolo");
                if(changePassNew.value === changePassNewConfirm.value){
                    valido &= true;
                }else{
                    valido &= false;
                    mostrarError(errorChangePassNewConfirm, changePassNewConfirm, "La contraseña nueva debe coincidir");
                }
                if (!valido) return;


                const formData = new FormData();
                formData.append("changePassCurrent", changePassCurrent.value.trim());
                formData.append("changePassNew", changePassNew.value.trim());

                //PARTE DE CODIGO PARA Guardar Cambios
                try {
                    const res = await fetch("./BACKEND/FuncionesPHP/changePassword.php", { method: "POST", body: formData });
                    
                    // Obtenemos la respuesta como texto y la parseamos.
                    let data = await res.json();

                    let callback = null;
                    let message = data.message || "Operación completada.";
                    let type = data.status || 'error';
                    // Cierra el modal de opciones ANTES de mostrar la notificación
                    const changePasswordModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                    if (changePasswordModal) changePasswordModal.hide();

                    // 1. Manejo de Éxito
                    if (type === 'success') {
                        

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
                    let errorChangePass = document.getElementById("errorChangePass");
                    mostrarError(errorChangePass, null, "Error crítico de conexión o respuesta no válida."); 
                    console.error("Fallo en el fetch:", error.message);
                }
            });
        }
    }

    initModalChangePassword();

    

});