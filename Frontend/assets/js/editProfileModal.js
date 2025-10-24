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
    const validarFecha = (input, errorDiv) => {
        let isValid = true;
        input.classList.remove("errorInput");

        if (!input?.value.trim()) { 
            mostrarError(errorDiv, input, "Campo obligatorio."); 
            isValid = false; 
        } else {
            // --- 1. PARSEO MANUAL (Para formato DD-MM-YYYY) ---
            // El valor de input.value estará en formato DD-MM-YYYY (ej. "31-12-2000")
            const parts = input.value.split('-'); 
            
            // Verifica el formato básico de 3 partes
            if (parts.length !== 3) {
                mostrarError(errorDiv, input, "Formato de fecha inválido.");
                isValid = false;
                // Si el formato es incorrecto, no tiene sentido continuar
                if (!isValid) input.classList.add("errorInput");
                return isValid; 
            }

            // Crear la fecha en formato MM/DD/YYYY para que new Date la interprete correctamente
            // parts[0] = DD, parts[1] = MM, parts[2] = YYYY
            const dateString = `${parts[1]}/${parts[0]}/${parts[2]}`; 
            const fecha = new Date(dateString);
            
            // Verifica si la fecha resultante es un objeto de Fecha válido
            if (isNaN(fecha.getTime())) {
                mostrarError(errorDiv, input, "Fecha inválida. Revisa el día/mes.");
                isValid = false;
            } 
            
            // Si la fecha es válida, procede con la validación de edad
            else {
                // --- 2. VALIDACIÓN DE EDAD (Mantenemos tu lógica) ---
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const mesHoy = hoy.getMonth();
                const mesFecha = fecha.getMonth();
                const diaHoy = hoy.getDate();
                const diaFecha = fecha.getDate();
                
                // Ajuste de edad si aún no ha cumplido años este mes
                if (mesHoy < mesFecha || (mesHoy === mesFecha && diaHoy < diaFecha)) {
                    edad--;
                }

                if (edad < 18) { 
                    mostrarError(errorDiv, input, "Debes tener al menos 18 años."); 
                    isValid = false; 
                }
            }
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


    function initModalEditProfileLogic() {

        // LOGICA PARA QUE FUNICONE EL INPUT FILE
        const changePicButton = document.getElementById('changeProfilePic');
        const profilePicInput = document.getElementById('profilePicInput');

        if (changePicButton && profilePicInput) {
            
            // 💡 1. Lógica para abrir el selector de archivo
            changePicButton.addEventListener('click', (e) => {
                e.preventDefault();
                profilePicInput.click();
            });

            // 💡 2. Lógica para previsualizar inmediatamente después de seleccionar la foto
            profilePicInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // Actualizar el src de la imagen visible en el modal
                        const avatarImage = document.querySelector('.profile-avatar-edit img');
                        if (avatarImage) {
                            avatarImage.src = event.target.result;
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
        //-------------------------------------------------
        // Inicializar flatpickr
        const fNac = document.querySelector("#editDateBirth");
        if (fNac && typeof flatpickr === 'function') {
            flatpickr(fNac, { dateFormat: "d-m-Y", altInput: true, altFormat: "d-m-Y", allowInput: true });
        }
        //-------------------------------------------------
        // inputs y botones
        const editDateBirth = document.getElementById("editDateBirth");
        const today = new Date().toISOString().split("T")[0];
        editDateBirth?.setAttribute("min", "1925-01-01");
        editDateBirth?.setAttribute("max", today);

        const editName = document.getElementById("editName");
        const editLastName = document.getElementById("editLastName");
        const editUsername = document.getElementById("editUsername");
        const editBiography = document.getElementById("editBiography");
        const saveChangeBtn = document.getElementById("saveChangeEditProfileButton");

        const errorEditUsername = document.getElementById("errorEditUsername");
        const errorEditName = document.getElementById("errorEditName");
        const errorEditLastName = document.getElementById("errorEditLastName");
        const errorEditDateBirth = document.getElementById("errorEditDateBirth");
        

        // Regex
        const userRegex = /^[a-zA-Z0-9._%+-]{4,20}$/;
        const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/;

        // ====================================================================
        // EVENT LISTEN DE BOTON DE GUARDAR CAMBIOS
        // ====================================================================

        // Login submit
        if (saveChangeBtn) {
            saveChangeBtn.addEventListener("click", async e => {
                e.preventDefault(); 
                limpiarErrores();

                let valido = validarFecha(editDateBirth, errorEditDateBirth);
                valido &= validarCampo(editName, nameRegex, errorEditName, "Solo letras (2-30)");
                valido &= validarCampo(editLastName, nameRegex, errorEditLastName, "Solo letras (2-30)");
                valido &= validarCampo(editUsername, userRegex, errorEditUsername, "Entre 4 y 20 caracteres");
                if (!valido) return;

                const formData = new FormData();
                formData.append("profilePic", profilePicInput.files || "");
                formData.append("dateBirth", editDateBirth.value.trim());
                formData.append("name", editName.value.trim());
                formData.append("lastName", editLastName.value.trim());
                formData.append("username", editUsername.value.trim());
                formData.append("biography", editBiography.value.trim());

                //PARTE DE CODIGO PARA Guardar Cambios
                try {
                    const res = await fetch("./BACKEND/FuncionesPHP/editProfile.php", { method: "POST", body: formData });
                    
                    // Obtenemos la respuesta como texto y la parseamos.
                    let data = await res.json();

                    let callback = null;
                    let message = data.message || "Operación completada.";
                    let type = data.status || 'error';

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
            });
        }
    }

    initModalEditProfileLogic();

    // const editProfileModalEl = document.getElementById('editProfileModal');
    // if (editProfileModalEl) {
    //     // Evento para cuando el modal se abre
    //     editProfileModalEl.addEventListener('shown.bs.modal', (e) => {
    //         //injectFormEditProfile();
    //         initModalEditProfileLogic();
    //     });

    //     // Evento para cuando el modal se cierra
    //     editProfileModalEl.addEventListener('hidden.bs.modal', (e) => {
    //         const container = document.getElementById("editProfileForm");
    //         container.innerHTML = "";
    //     });
    // }

});