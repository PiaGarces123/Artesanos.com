// followListModals.js
// Gestiona los modales de Seguidores y Siguiendo

document.addEventListener("DOMContentLoaded", function() {

    const mostrarError = (div, msg) => {
        if (!div) return;
        div.textContent = msg;
        div.classList.add("visible-error");
    };

    window.updateFollowCounters = async (user_id) => {
        try {
            let formData = new FormData();
            formData.append('user_id', user_id);

            const followersRes = await fetch('./BACKEND/FuncionesPHP/getFollowers.php', {
                method: 'POST',
                body: formData
            });
            const followersData = await followersRes.json();

            const followingRes = await fetch('./BACKEND/FuncionesPHP/getFollowing.php', {
                method: 'POST',
                body: formData
            });
            const followingData = await followingRes.json();

            if (followersData.status === 'success') {
                const followersCountEl = document.getElementById('followersCount');
                if (followersCountEl) followersCountEl.textContent = followersData.seguidores.length;
            }

            if (followingData.status === 'success') {
                const followingCountEl = document.getElementById('followingCount');
                if (followingCountEl) followingCountEl.textContent = followingData.siguiendo.length;
            }

        } catch (error) {
            console.error('Error al actualizar contadores:', error);
        }
    };

    // ==============================
    // CARGAR SEGUIDORES
    // ==============================
    async function loadFollowers(user_id) {
        const container = document.getElementById('followersModalContainer');
        const errorDiv = document.getElementById('errorFollowersModal');

        if(errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove("visible-error");
        }

        container.innerHTML = `
            <div class="text-center mt-3">
                <div class="spinner-border text-primary"></div>
                <p class="text-secondary mt-2">Cargando seguidores...</p>
            </div>
        `;

        try {
            let formData = new FormData();
            formData.append('user_id', user_id);

            const res = await fetch('./BACKEND/FuncionesPHP/getFollowers.php', {
                method: 'POST',
                body: formData
            });

            if (!res.ok) throw new Error('Error de red al cargar seguidores');
            const data = await res.json();

            if (data.status === 'success') {
                const seguidores = data.seguidores;
                if (seguidores.length === 0) {
                    container.innerHTML = '<div class="alert alert-info text-center">No hay seguidores aún.</div>';
                    return;
                }

                let html = '<ul class="list-group list-group-flush">';
                seguidores.forEach(u => {
                    html += `
                        <li class="list-group-item d-flex align-items-center">
                            <a href="profile.php?user_id=${u.U_id}" class="d-flex align-items-center text-decoration-none flex-grow-1">
                                <img src="${u.U_profilePic}" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <strong class="text-dark">${u.U_nameUser}</strong>
                                    <small class="text-muted">${u.U_name} ${u.U_lastName}</small>
                                </div>
                            </a>
                        </li>
                    `;
                });
                html += '</ul>';
                container.innerHTML = html;

            } else {
                mostrarError(errorDiv, data.message || 'Error al cargar seguidores.');
                container.innerHTML = '<div class="alert alert-danger text-center">Error al cargar seguidores.</div>';
            }

        } catch (err) {
            console.error(err);
            mostrarError(errorDiv, err.message);
            container.innerHTML = `<div class="alert alert-danger text-center">${err.message}</div>`;
        }
    }

    // ==============================
    // CARGAR SIGUIENDO
    // ==============================
    async function loadFollowing(user_id) {
        const container = document.getElementById('followingModalContainer');
        const errorDiv = document.getElementById('errorFollowingModal');

        if(errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove("visible-error");
        }

        container.innerHTML = `
            <div class="text-center mt-3">
                <div class="spinner-border text-primary"></div>
                <p class="text-secondary mt-2">Cargando siguiendo...</p>
            </div>
        `;

        try {
            let formData = new FormData();
            formData.append('user_id', user_id);

            const res = await fetch('./BACKEND/FuncionesPHP/getFollowing.php', {
                method: 'POST',
                body: formData
            });

            if (!res.ok) throw new Error('Error de red al cargar siguiendo');
            const data = await res.json();

            if (data.status === 'success') {
                const siguiendo = data.siguiendo;
                if (siguiendo.length === 0) {
                    container.innerHTML = '<div class="alert alert-info text-center">No sigue a nadie aún.</div>';
                    return;
                }

                let html = '<ul class="list-group list-group-flush">';
                siguiendo.forEach(u => {
                    html += `
                        <li class="list-group-item d-flex align-items-center">
                            <a href="profile.php?user_id=${u.U_id}" class="d-flex align-items-center text-decoration-none flex-grow-1">
                                <img src="${u.U_profilePic}" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <strong class="text-dark">${u.U_nameUser}</strong>
                                    <small class="text-muted">${u.U_name} ${u.U_lastName}</small>
                                </div>
                            </a>
                        </li>
                    `;
                });
                html += '</ul>';
                container.innerHTML = html;

            } else {
                mostrarError(errorDiv, data.message || 'Error al cargar siguiendo.');
                container.innerHTML = '<div class="alert alert-danger text-center">Error al cargar siguiendo.</div>';
            }

        } catch (err) {
            console.error(err);
            mostrarError(errorDiv, err.message);
            container.innerHTML = `<div class="alert alert-danger text-center">${err.message}</div>`;
        }
    }

    // ==============================
    // EVENT LISTENERS PARA LOS MODALES
    // ==============================
    const followersModalEl = document.getElementById('followersModal');
    if (followersModalEl) {
        followersModalEl.addEventListener('shown.bs.modal', () => loadFollowers(user_id));
        followersModalEl.addEventListener('hidden.bs.modal', () => {
            const container = document.getElementById('followersModalContainer');
            container.innerHTML = `<p class="text-center mt-3 text-secondary">
                                      <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                      Cargando seguidores...
                                   </p>`;
        });
    }

    const followingModalEl = document.getElementById('followingModal');
    if (followingModalEl) {
        followingModalEl.addEventListener('shown.bs.modal', () => loadFollowing(user_id));
        followingModalEl.addEventListener('hidden.bs.modal', () => {
            const container = document.getElementById('followingModalContainer');
            container.innerHTML = `<p class="text-center mt-3 text-secondary">
                                      <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                      Cargando siguiendo...
                                   </p>`;
        });
    }

});
