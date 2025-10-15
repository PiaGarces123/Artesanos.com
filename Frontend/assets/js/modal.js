document.addEventListener("DOMContentLoaded", () => {
    const loginBtn = document.getElementById("loginBtn");
    const feedImgs = document.querySelectorAll(".feed-img");
    const container = document.getElementById("modalContainer");

    const loadModalHTML = async () => {
        if (!container.innerHTML.trim()) {
            const res = await fetch("/ProgIII/Artesanos.com/Frontend/pages/login.html");
            container.innerHTML = await res.text();

            // Inicializar flatpickr si existe
            const fNac = document.querySelector("#fNac");
            if (fNac && !window.flatpickr) {
                const s = document.createElement("script");
                s.src = "https://cdn.jsdelivr.net/npm/flatpickr";
                s.onload = () => {
                    flatpickr(fNac, { dateFormat: "d-m-Y", altInput: true, altFormat: "d-m-Y", allowInput: true });
                };
                document.body.appendChild(s);
            } else if (fNac && window.flatpickr) {
                flatpickr(fNac, { dateFormat: "d-m-Y", altInput: true, altFormat: "d-m-Y", allowInput: true });
            }
        }
    };

    const openModal = async () => {
        await loadModalHTML();
        const loginModal = document.getElementById("loginModal");
        if (!loginModal) return;

        loginModal.style.display = "flex";
        document.body.style.overflow = "hidden";

        // ----- CIERRE -----
        const closeBtn = document.getElementById("closeLoginModal");
        closeBtn?.addEventListener("click", () => {
            loginModal.style.display = "none";
            document.body.style.overflow = "auto";
        });

        loginModal.addEventListener("click", function(e) {
            if (e.target === loginModal) {
                loginModal.style.display = "none";
                document.body.style.overflow = "auto";
            }
        });

        // ----- LOGIN/SIGNUP -----
        initModalLogic();
    };

    // Activadores
    loginBtn?.addEventListener("click", openModal);
    feedImgs.forEach(img => img.addEventListener("click", openModal));

    // ----- LÓGICA INTERNA DEL MODAL -----
    function initModalLogic() {
        //Ojo de la contraseña
        const input = eye.closest('.form-groupLogin').querySelector('input.form-style');

        const loginModal = document.getElementById("loginModal");
        const checkbox = document.getElementById("reg-log");

        // Login
        const loginEmail = document.querySelector(".card-front input[name='mail']");
        const loginPass = document.querySelector(".card-front input[name='pass']");
        const loginBtnSubmit = document.querySelector(".card-front .btnLogin");
        const errorEmailLogin = document.getElementById("errorEmailLogin");
        const errorPassLogin = document.getElementById("errorPassLogin");

        // Signup
        const signupFnac = document.querySelector(".card-back input[name='fNac']");
        const today = new Date().toISOString().split("T")[0];
        signupFnac?.setAttribute("min", "1925-01-01");
        signupFnac?.setAttribute("max", today);

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

        // Regex
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/;
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/;

        // Mostrar y limpiar errores
        const mostrarError = (div, input, msg) => {
            if (!div) return;
            div.textContent = msg;
            div.classList.add("visible-error");
            if (input) input.classList.add("errorInput");
        };
        const limpiarErrores = () => {
            document.querySelectorAll(".error").forEach(div => div.textContent = "");
            document.querySelectorAll(".errorInput").forEach(inp => inp.classList.remove("errorInput"));
        };

        const validarCampo = (input, regex, errorDiv, msg) => {
            if (!input?.value.trim()) { mostrarError(errorDiv, input, "Campo obligatorio."); return false; }
            if (regex && !regex.test(input.value)) { mostrarError(errorDiv, input, msg); return false; }
            return true;
        };
        const validarFecha = (input, errorDiv) => {
            if (!input?.value) { mostrarError(errorDiv, input, "Campo obligatorio."); return false; }
            const fecha = new Date(input.value);
            const min = new Date(input.min); const max = new Date(input.max);
            if (fecha < min || fecha > max) { mostrarError(errorDiv, input, `Fecha entre ${input.min} y ${input.max}`); return false; }
            let edad = new Date().getFullYear() - fecha.getFullYear();
            const mes = new Date().getMonth() - fecha.getMonth();
            if (mes < 0 || (mes === 0 && new Date().getDate() < fecha.getDate())) edad--;
            if (edad < 18) { mostrarError(errorDiv, input, "Debes tener al menos 18 años."); return false; }
            return true;
        };

        // Login submit
        loginBtnSubmit?.addEventListener("click", async e => {
            e.preventDefault(); limpiarErrores();
            let valido = validarCampo(loginEmail, emailRegex, errorEmailLogin, "Email inválido");
            valido &= validarCampo(loginPass, passRegex, errorPassLogin, "Debe tener mayúscula, minúscula, número y símbolo");
            if (!valido) return;

            const formData = new FormData();
            formData.append("mail", loginEmail.value.trim());
            formData.append("pass", loginPass.value.trim());

            try {
                const res = await fetch("../BACKEND/Validation/login.php", { method: "POST", body: formData });
                const data = await res.json();
                if (data.status === "success") { alert(`✅ Bienvenid@ ${data.user.username}`); loginModal.style.display="none"; document.body.style.overflow="auto"; }
                else mostrarError(errorPassLogin, null, data.message);
            } catch { mostrarError(errorPassLogin, null, "Error del servidor"); }
        });

        // Signup submit
        signupBtnSubmit?.addEventListener("click", async e => {
            e.preventDefault(); limpiarErrores();
            let valido = validarFecha(signupFnac, errorFnac);
            valido &= validarCampo(signupName, nameRegex, errorNbre, "Solo letras (2-30)");
            valido &= validarCampo(signupLast, nameRegex, errorApe, "Solo letras (2-30)");
            valido &= validarCampo(signupUser, null, errorUser, "Usuario obligatorio");
            if (signupUser?.value.length<4 || signupUser?.value.length>20){ mostrarError(errorUser, signupUser, "Entre 4 y 20 caracteres"); valido=false; }
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
                const res = await fetch("../../../BACKEND/Validation/signup.php", { method: "POST", body: formData });
                const data = await res.json();
                if (data.status==="success"){ alert("✅ Registro exitoso"); checkbox.checked=false; }
                else if (data.errores){
                    if(data.errores.fNac) mostrarError(errorFnac, signupFnac, data.errores.fNac);
                    if(data.errores.nbre) mostrarError(errorNbre, signupName, data.errores.nbre);
                    if(data.errores.ape) mostrarError(errorApe, signupLast, data.errores.ape);
                    if(data.errores.userName) mostrarError(errorUser, signupUser, data.errores.userName);
                    if(data.errores.mail) mostrarError(errorEmailSignUp, signupEmail, data.errores.mail);
                    if(data.errores.pass) mostrarError(errorPassSignUp, signupPass, data.errores.pass);
                } else alert(data.message);
            } catch { alert("⚠️ Error en el servidor."); }
        });

        // Toggle password
        document.querySelectorAll(".toggle-pass").forEach(eye => {
            eye.addEventListener("click", () => {
                if (!input) return;
                if (input.type==="password"){ input.type="text"; eye.classList.replace("uil-eye","uil-eye-slash"); }
                else{ input.type="password"; eye.classList.replace("uil-eye-slash","uil-eye"); }
            });
        });
    }
});
