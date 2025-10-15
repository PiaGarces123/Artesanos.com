// -------------------------- LOG IN --------------------------


// Esperar a que todo el contenido del DOM esté cargado
document.addEventListener("DOMContentLoaded", () => {

    // Referencias a los elementos clave
    const logo = document.getElementById("logo");
    const loginModal = document.getElementById("loginModal");
    const closeModalBtn = document.getElementById("closeCreateModal");

    // --- ABRIR MODAL ---
    logo.addEventListener("click", () => {
        // Mostrar el modal (por defecto puede estar oculto con display: none)
        loginModal.style.display = "flex"; 
        loginModal.classList.add("visible"); // opcional si usás clases CSS
        document.body.style.overflow = "hidden"; // evitar scroll del fondo
    });

    // --- CERRAR MODAL ---
    closeModalBtn.addEventListener("click", () => {
        cerrarModal();
    });

    // --- CERRAR MODAL HACIENDO CLICK FUERA DEL CONTENIDO ---
    window.addEventListener("click", (e) => {
        if (e.target === loginModal) {
            cerrarModal();
        }
    });

    // Función para cerrar el modal
    function cerrarModal() {
        loginModal.style.display = "none";
        loginModal.classList.remove("visible");
        document.body.style.overflow = "auto";
    }
});



// Para cambiar entre Log In y Sign Up 
    const checkbox = document.getElementById("reg-log");
    const btnLogin = document.getElementById("btn-login");
    const btnSignup = document.getElementById("btn-signup");

    btnLogin.addEventListener("click", () => {
        checkbox.checked = false; // muestra el login
    });

    btnSignup.addEventListener("click", () => {
        checkbox.checked = true; // muestra el signup
    });


//------------------------------INICIAR SESION------------------------------
/*
function openModal() {
    document.getElementById('modalOverlay').classList.add('active');
    document.getElementById('modalContainer').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.getElementById('modalContainer').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Cerrar modal con la tecla Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const albumBtn = document.getElementById("logo");
    albumBtn.addEventListener("click", openModal);
});*/