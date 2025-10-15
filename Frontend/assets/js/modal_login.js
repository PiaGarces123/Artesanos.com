document.addEventListener("DOMContentLoaded", () => {

    // ----- MODAL -----
    const logo = document.getElementById("logo");
    const loginModal = document.getElementById("loginModal");
    const closeModalBtn = document.getElementById("closeCreateModal");
    const checkbox = document.getElementById("reg-log");
    const btnLogin = document.getElementById("btn-login");
    const btnSignup = document.getElementById("btn-signup");

    //Abrir Modal
    function abrirModal() {
        loginModal.style.display = "flex";
        document.body.style.overflow = "hidden"; // evitar scroll del fondo
    }

    //Cerrar Modal
    function cerrarModal() {
        loginModal.style.display = "none";
        document.body.style.overflow = "auto";
    }

    logo.addEventListener("click", abrirModal);
    closeModalBtn.addEventListener("click", cerrarModal);

    // --- CERRAR MODAL HACIENDO CLICK FUERA DEL CONTENIDO ---
    window.addEventListener("click", e => { if(e.target === loginModal) cerrarModal(); });

    btnLogin.addEventListener("click", () => checkbox.checked = false);
    btnSignup.addEventListener("click", () => checkbox.checked = true);

    // ----- SELECTORES -----
    // Login
    const loginEmail = document.querySelector(".card-front input[name='mail']");
    const loginPass = document.querySelector(".card-front input[name='pass']");
    const loginBtnSubmit = document.querySelector(".card-front .btnLogin");
    const errorEmailLogin = document.getElementById("errorEmailLogin");
    const errorPassLogin = document.getElementById("errorPassLogin");

    // Signup
    const signupFnac = document.querySelector(".card-back input[name='fNac']");
        const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
        signupFnac.setAttribute("min", "1925-01-01");
        signupFnac.setAttribute("max", today);
    const signupName = document.querySelector(".card-back input[name='nbre']");
    const signupLast = document.querySelector(".card-back input[name='ape']");
    const signupUser = document.querySelector(".card-back input[name='userName']");
    const signupEmail = document.querySelector(".card-back input[name='mail']");
    const signupPass = document.querySelector(".card-back input[name='pass']");
    const signupBtnSubmit = document.querySelector(".card-back .btnLogin");
    const errorFnac = document.getElementById("errorFnac");
    const errorNbre = document.getElementById("errorNbre");
    const errorApe = document.getElementById("errorApe");
    const errorUser = document.getElementById("errorUser");
    const errorEmailSignUp = document.getElementById("errorEmailSignUp");
    const errorPassSignUp = document.getElementById("errorPassSignUp");

    // ----- REGEX -----
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/;
    const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/;

    // ----- FUNCIONES GENERALES -----

    //Mostrar Error
    function mostrarError(div, input, mensaje) {
        div.textContent = mensaje;
        div.classList.add("visible-error");
        if(input) input.classList.add("errorInput");
    }

    //Limpiar Errores para que no se vean
    function limpiarErrores() {
        document.querySelectorAll(".error").forEach(div => {
            div.textContent = "";
            div.classList.remove("visible-error");
        });
        document.querySelectorAll(".errorInput").forEach(input => input.classList.remove("errorInput"));
    }

    // Para cambiar entre Log In y Sign Up 
        btnLogin.addEventListener(
            "click", () => { checkbox.checked = false; 
            // muestra el login 
        });

        btnSignup.addEventListener(
            "click", () => { checkbox.checked = true;
            // muestra el signup 
        });

    //Para ver contraseña
    document.querySelectorAll(".toggle-pass").forEach(eye => {
        eye.addEventListener("click", () => {
            const input = eye.previousElementSibling; 
            if (input.type === "password") {
                input.type = "text";
                eye.classList.replace("uil-eye", "uil-eye-slash");
            } else {
                input.type = "password";
                eye.classList.replace("uil-eye-slash", "uil-eye");
            }
        });
    });

    //Validar Campo
    function validarCampo(input, regex, errorDiv, mensaje) {
        if(!input.value.trim()) {
            mostrarError(errorDiv, input, "Campo obligatorio.");
            return false;
        } else if(regex && !regex.test(input.value)) {
            mostrarError(errorDiv, input, mensaje);
            return false;
        }
        return true;
    }

    //Validar Fecha de Nacimiento
    function validarFecha(input, errorDiv) {
        if (!input.value) {
            mostrarError(errorDiv, input, "Campo obligatorio.");
            return false;
        }

        const fechaNac = new Date(input.value);
        const minFecha = new Date(input.min);
        const maxFecha = new Date(input.max);

        // Verifica que esté dentro del rango permitido
        if (fechaNac < minFecha || fechaNac > maxFecha) {
            mostrarError(errorDiv, input, `Fecha debe estar entre ${input.min} y ${input.max}`);
            return false;
        }

        // Calcular edad
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();

        // Ajustar si todavía no cumplió años este año
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }

        // Validar que tenga al menos 18 años
        if (edad < 18) {
            mostrarError(errorDiv, input, "Debes tener al menos 18 años para registrarte.");
            return false;
        }

        return true;
    }


    // ----- LOGIN -----
    loginBtnSubmit.addEventListener("click", async (e) => {
        e.preventDefault();
        limpiarErrores();

        let valido = true;
        valido &= validarCampo(loginEmail, emailRegex, errorEmailLogin, "Email inválido. Ej: usuario@dominio.com");
        valido &= validarCampo(loginPass, passRegex, errorPassLogin, "Debe tener mayúscula, minúscula, número y símbolo (mín.6).");

        if(!valido) return;

        // Crear FormData manualmente (no hay <form> real)
        const formData = new FormData();
        formData.append("mail", loginEmail.value.trim());
        formData.append("pass", loginPass.value.trim());

        try {
            const res = await fetch("../BACKEND/Validation/login.php", {
                method: "POST",
                body: formData
            });
            const data = await res.json();

            if(data.status === "success") {
                alert(`✅ Bienvenid@ ${data.user.username}`);
                cerrarModal();
                // Podés redirigir: window.location.href = "dashboard.php";
            } else {
                // Mostrar error general
                mostrarError(errorPassLogin, null, data.message);
            }
        } catch(err) {
            console.error(err);
            mostrarError(errorPassLogin, null, "Error del servidor. Intenta más tarde.");
        }
    });

    // ----- SIGNUP -----
    signupBtnSubmit.addEventListener("click", (e) => {
        e.preventDefault();
        limpiarErrores();

        let valido = true;
        valido &= validarFecha(signupFnac, errorFnac);
        valido &= validarCampo(signupName, nameRegex, errorNbre, "Solo letras (2-30 caracteres)");
        valido &= validarCampo(signupLast, nameRegex, errorApe, "Solo letras (2-30 caracteres)");
        valido &= validarCampo(signupUser, null, errorUser, "Usuario obligatorio");
        if(signupUser.value.length < 4 || signupUser.value.length > 20){
            mostrarError(errorUser, signupUser, "Debe tener entre 4 y 20 caracteres");
            valido = false;
        }
        valido &= validarCampo(signupEmail, emailRegex, errorEmailSignUp, "Email inválido. Ej: usuario@dominio.com");
        valido &= validarCampo(signupPass, passRegex, errorPassSignUp, "Debe tener mayúscula, minúscula, número y símbolo (mín.6)");

        if(!valido) return;

        alert("✅ Registro válido - ACÁ SE HACE LA CONSULTA AL PHP.");
        // Acá harías el fetch al PHP para registrar
    });

});
