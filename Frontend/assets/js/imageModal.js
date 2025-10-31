// Variable global para la instancia del modal de imagen
let imageModalInstance = null;

// Elementos del Modal
const imageModalEl = document.getElementById('imageModal');
const modalImage = document.getElementById('imagePublic');
const modalAvatarUser = document.getElementById('avatarUser');
const modalNameUser = document.getElementById('nameUser');
const modalTitleImage = document.getElementById('TitleImage');
const modalLikeButton = document.getElementById('likeButton');
const modalLikeCount = document.getElementById('likeCount');
const modalCommentList = document.getElementById('commentListContainer');

// Asegurarnos de crear la instancia del modal una sola vez
if (imageModalEl) {
    imageModalInstance = new bootstrap.Modal(imageModalEl);
}

/**
 * Función principal para abrir el modal y cargar los datos
 * Esta función es llamada por los listeners en myAlbumsProfile.js y MyAlbumsModal.js
 */
async function openImageModal(imageId) {
    if (!imageModalInstance) return;

    // 1. Mostrar el modal
    imageModalInstance.show();
    
    // 2. Poner todo en estado de "carga"
    setModalToLoading();

    try {
        let formData = new FormData();
        formData.append('imageId', imageId);

        // 3. Llamar al backend
        const response = await fetch('./BACKEND/FuncionesPHP/getModalImageData.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Error de red al obtener datos de la imagen.');

        const data = await response.json();

        if (data.status !== 'success') throw new Error(data.message);

        // 4. Rellenar el modal con los datos
        populateImageModal(data);
        
        // 5. Adjuntar listeners para "Like" y "Comentar"
        attachImageModalListeners(imageId);

    } catch (error) {
        console.error('Error en openImageModal:', error);
        setModalToError(error.message);
    }
}

/**
 * Pone el modal en estado de "carga" (Spinners)
 */
function setModalToLoading() {
    // (Puedes añadir spinners si quieres, por ahora solo limpiamos)
    modalImage.src = '';
    modalAvatarUser.src = '';
    modalNameUser.textContent = 'Cargando...';
    modalTitleImage.textContent = '...';
    modalLikeCount.textContent = '-';
    modalLikeButton.classList.remove('active'); // 'active' será nuestra clase para "likeado"
    modalCommentList.innerHTML = '<p class="text-center text-secondary">Cargando comentarios...</p>';
}

/**
 * Muestra un error dentro del modal
 */
function setModalToError(message) {
    // (Limpiamos todo para que no se vea raro)
    modalImage.src = ''; 
    modalAvatarUser.src = '';
    modalNameUser.textContent = 'Error';
    modalTitleImage.textContent = message;
    modalCommentList.innerHTML = `<div class="alert alert-danger">${message}</div>`;
}

/**
 * Rellena el modal con los datos del JSON
 */
function populateImageModal(data) {
    modalImage.src = data.imageRuta;
    modalAvatarUser.src = data.ownerAvatar;
    modalNameUser.textContent = data.ownerName;
    modalTitleImage.textContent = data.imageTitle;

    // --- Lógica de Likes ---
    modalLikeCount.textContent = data.likeCount;
    if (data.hasLiked) {
        modalLikeButton.classList.add('active'); // (Necesitarás CSS para esto)
    } else {
        modalLikeButton.classList.remove('active');
    }

    // --- Lógica de Comentarios ---
    let commentsHTML = '';
    if (data.comments.length === 0) {
        commentsHTML = '<p class="text-center text-secondary small">No hay comentarios aún. ¡Sé el primero!</p>';
    } else {
        // Usamos el estilo que te gustó
        data.comments.forEach(comment => {
            commentsHTML += `
                <div class="comment">
                    <div class="comment-user">
                        <div class="comment-avatar">
                            <img src="${comment.U_profilePic}" alt="Avatar de ${comment.U_nameUser}">
                        </div>
                        <div class="comment-username">${comment.U_nameUser}</div>
                    </div>
                    <div class="comment-text">${comment.C_content}</div>
                </div>
            `;
        });
    }
    modalCommentList.innerHTML = commentsHTML;
}


/**
 * Adjunta los listeners para "Like" y "Publicar Comentario"
 * (Esta es la próxima función que haremos)
 */
function attachImageModalListeners(imageId) {
    // Limpiamos listeners viejos (muy importante)
    // (Usaremos tu patrón de cloneNode)

    // TODO: Lógica para el botón de Like
    
    // TODO: Lógica para el botón de Publicar Comentario
}

// (Opcional) Limpiar el modal al cerrar para que no muestre datos viejos
if (imageModalEl) {
    imageModalEl.addEventListener('hidden.bs.modal', () => {
        setModalToLoading(); 
        // Aquí también deberíamos "matar" los listeners
    });
}