// modal.js

document.addEventListener("DOMContentLoaded", () => {
    
    // Función para limpiar todos los errores visuales
    const limpiarErrores = () => {
        document.querySelectorAll(".error").forEach(div => {
            div.textContent = "";
            div.classList.remove("visible-error");
        });
        document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
    };
    
    // ====================================================================
    // LÓGICA DE INICIALIZACIÓN DE COMPONENTES Y EVENT LISTENERS
    // ====================================================================
    function initModalLogic() {
        
        // Inicializar flatpickr
        const fNac = document.querySelector("#fNac");
        if (fNac && typeof flatpickr === 'function') {
            flatpickr(fNac, { dateFormat: "d-m-Y", altInput: true, altFormat: "d-m-Y", allowInput: true });
        }

        // Login
        const loginEmail = document.getElementById("mailLogin");
        const loginPass = document.getElementById("passLogin");
        const loginBtnSubmit = document.getElementById("loginBtnSubmit"); 
        const errorEmailLogin = document.getElementById("errorEmailLogin");
        const errorPassLogin = document.getElementById("errorPassLogin");

        // Signup
        const signupFnac = document.getElementById("fNac");
        const today = new Date().toISOString().split("T")[0];
        signupFnac?.setAttribute("min", "1925-01-01");
        signupFnac?.setAttribute("max", today);

        const signupName = document.getElementById("nbre");
        const signupLast = document.getElementById("ape");
        const signupUser = document.getElementById("userName");
        const signupEmail = document.getElementById("mailSignUp");
        const signupPass = document.getElementById("passSignUp");
        const signupBtnSubmit = document.getElementById("signupBtnSubmit"); 

        const errorFnac = document.getElementById("errorFnac");
        const errorNbre = document.getElementById("errorNbre");
        const errorApe = document.getElementById("errorApe");
        const errorUser = document.getElementById("errorUser");
        const errorEmailSignUp = document.getElementById("errorEmailSignUp");
        const errorPassSignUp = document.getElementById("errorPassSignUp");

        // Regex
        const userRegex = /^[a-zA-Z0-9._%+-]{4,20}$/;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/;
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/;

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

        // ====================================================================
        // EVENT LISTENERS DE BOTONES DE ENVÍO
        // ====================================================================

        // Login submit
        if (loginBtnSubmit) {
            loginBtnSubmit.addEventListener("click", async e => {
                e.preventDefault(); 
                limpiarErrores();
                
                let valido = validarCampo(loginEmail, emailRegex, errorEmailLogin, "Email inválido");
                valido &= validarCampo(loginPass, passRegex, errorPassLogin, "Debe tener mayúscula, minúscula, número y símbolo");
                if (!valido) return;

                const formData = new FormData();
                formData.append("mail", loginEmail.value.trim());
                formData.append("pass", loginPass.value.trim());

                try {
                    const res = await fetch("./BACKEND/Validation/login.php", { method: "POST", body: formData });
                    const data = await res.json();
                    
                    if (data.status == "success") { 
                        showStaticNotificationModal('success', `Bienvenid @${data.user.username}`, () => window.location.reload());                         
                    }
                    else { 
                        mostrarError(errorPassLogin, null, data.message);
                        loginPass.classList.add("errorInput"); 
                    }
                } catch { 
                    mostrarError(errorPassLogin, null, "Error del servidor"); 
                }
            });
        }

        // Signup submit
        if (signupBtnSubmit) {
            signupBtnSubmit.addEventListener("click", async e => {
                e.preventDefault(); 
                limpiarErrores();
                
                let valido = validarFecha(signupFnac, errorFnac);
                valido &= validarCampo(signupName, nameRegex, errorNbre, "Solo letras (2-30)");
                valido &= validarCampo(signupLast, nameRegex, errorApe, "Solo letras (2-30)");
                valido &= validarCampo(signupUser, userRegex, errorUser, "Entre 4 y 20 caracteres");
                valido &= validarCampo(signupEmail, emailRegex, errorEmailSignUp, "Email inválido");
                valido &= validarCampo(signupPass, passRegex, errorPassSignUp, "Debe tener mayúscula, minúscula, número y símbolo");
                if (!valido) return;

                const formData = new FormData();
                formData.append("fNac", signupFnac.value.trim());
                formData.append("nbre", signupName.value.trim());
                formData.append("ape", signupLast.value.trim());
                formData.append("userName", signupUser.value.trim());
                formData.append("mail", signupEmail.value.trim());
                formData.append("pass", signupPass.value.trim());

                try {
                    const res = await fetch("./BACKEND/Validation/signup.php", { method: "POST", body: formData });
                    const data = await res.json();
                    
                    if (data.status==="success"){ 
                        showStaticNotificationModal('success', `Registro exitoso`,null);
                        limpiarErrores();
                        
                        // Cambiar a la pestaña de Login de Bootstrap programáticamente
                        const loginTab = new bootstrap.Tab(document.getElementById('pills-login-tab'));
                        loginTab.show();
                    }
                    else if (data.errores){
                        // Manejar errores de validación de Backend y marcarlos en el frontend
                        if(data.errores.fNac) mostrarError(errorFnac, signupFnac, data.errores.fNac);
                        if(data.errores.nbre) mostrarError(errorNbre, signupName, data.errores.nbre);
                        if(data.errores.ape) mostrarError(errorApe, signupLast, data.errores.ape);
                        if(data.errores.userName) mostrarError(errorUser, signupUser, data.errores.userName);
                        if(data.errores.mail) mostrarError(errorEmailSignUp, signupEmail, data.errores.mail);
                        if(data.errores.pass) mostrarError(errorPassSignUp, signupPass, data.errores.pass);
                    } else alert(data.message);
                } catch { alert("⚠️ Error en el servidor."); }
            });
        }

        // Toggle password
        document.querySelectorAll(".toggle-pass").forEach(eye => {
            eye.addEventListener("click", () => {
                const input = eye.closest('.form-groupLogin').querySelector('.form-style');
                if (!input) return;
                if (input.type==="password"){ input.type="text"; eye.classList.replace("uil-eye","uil-eye-slash"); }
                else{ input.type="password"; eye.classList.replace("uil-eye-slash","uil-eye"); }
            });
        });
        
        // Limpiar errores al cambiar de pestaña
        const tabs = document.querySelectorAll('.custom-login-tabs button');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', limpiarErrores);
        });
    }
    
    // Ejecutamos la lógica de inicialización.
    initModalLogic();
});