// =========================================================================
// 1. CONFIGURACIÓN INICIAL Y ELEMENTOS DEL MODAL
// =========================================================================

let imageModalInstance = null;
let currentModalData = { // Almacena los datos de la imagen abierta
    imageId: null,
    ownerId: null,
    followStatus: null
};

// --- Elementos del DOM (Solo el modal principal) ---
const imageModalEl = document.getElementById('imageModal');

// Asegurarnos de crear la instancia del modal una sola vez
if (imageModalEl) {
    imageModalInstance = new bootstrap.Modal(imageModalEl);
}

// =========================================================================
// 2. LÓGICA DE APERTURA Y CARGA DE DATOS
// =========================================================================

/**
 * Función principal para abrir el modal y cargar los datos
 */
async function openImageModal(imageId) {
    if (!imageModalInstance) return;

    imageModalInstance.show();
    setModalToLoading(); // Poner spinners/texto de carga

    try {
        let formData = new FormData();
        formData.append('imageId', imageId);

        const response = await fetch('./BACKEND/FuncionesPHP/getModalImageData.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Error de red al obtener datos de la imagen.');
        const data = await response.json();
        if (data.status !== 'success') throw new Error(data.message);

        // Guardamos los datos globalmente para que los listeners puedan usarlos
        currentModalData = {
            imageId: imageId,
            ownerId: data.ownerId,
            followStatus: data.followStatus
        };
        
        // Rellenamos el modal con los datos
        populateImageModal(data);
        
        // Adjuntamos los listeners para "Like" y "Comentar"
        attachImageModalListeners();

    } catch (error) {
        console.error('Error en openImageModal:', error);
        setModalToError(error.message); // Mostrar error en el modal
    }
}

/**
 * Rellena el modal con los datos del JSON
 */
function populateImageModal(data) {
    // Re-buscamos los elementos del DOM cada vez
    document.getElementById('imagePublic').src = data.imageRuta;
    document.getElementById('avatarUser').src = data.ownerAvatar;
    document.getElementById('nameUser').textContent = data.ownerName;
    document.getElementById('TitleImage').textContent = data.imageTitle;

    // --- Lógica de Likes ---
    const likeBtn = document.getElementById('likeButton');
    document.getElementById('likeCount').textContent = data.likeCount;
    if (data.hasLiked) {
        likeBtn.classList.add('active'); 
    } else {
        likeBtn.classList.remove('active');
    }

    // --- Lógica de Comentarios (Usa la función de abajo) ---
    populateCommentList(data.comments, data.isMyImage);
}

/**
 * Rellena la lista de comentarios
 */
function populateCommentList(comments, isMyImage) {
    let commentsHTML = '';
    const commentListContainer = document.getElementById('commentListContainer');
    
    if (!comments || comments.length === 0) {
        commentsHTML = '<p class="text-center text-secondary small">No hay comentarios aún. ¡Sé el primero!</p>';
    } else {
        comments.forEach(comment => {
            const isMyComment = (logged_in_user_id == comment.C_idUser);
            const canDelete = (isMyImage || isMyComment);

            let deleteButtonHTML = '';
            if (canDelete) {
                deleteButtonHTML = `
                    <button class="btn btn-sm btn-outline-danger p-0 comment-delete-btn" 
                            data-action="delete-comment" 
                            data-comment-id="${comment.C_id}">
                        <i class="uil uil-trash-alt p-1"></i>
                    </button>
                `;
            }

            commentsHTML += `
                <div class="comment">
                    <div class="comment-user d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center"> 
                            <div class="comment-avatar">
                                <img src="${comment.U_profilePic}" alt="Avatar de ${comment.U_nameUser}">
                            </div>
                            <div class="comment-username">${comment.U_nameUser}</div>
                        </div>
                        ${deleteButtonHTML}
                    </div>
                    <div class="comment-text">${comment.C_content}</div>
                </div>
            `;
        });
    }
    commentListContainer.innerHTML = commentsHTML;
    
    // Adjuntamos listeners a los botones de borrado que acabamos de crear
    attachDeleteCommentListeners();
}

// =========================================================================
// 3. LÓGICA DE LISTENERS (LIKE, COMENTAR, BORRAR)
// =========================================================================

/**
 * Adjunta los listeners (Like, Publicar y Perfil) usando el patrón cloneNode.
 */
function attachImageModalListeners() {
    
    // --- 1. LÓGICA DE LIKE ---
    const likeBtn = document.getElementById('likeButton');
    if (likeBtn) {
        let newLikeBtn = likeBtn.cloneNode(true);
        likeBtn.parentNode.replaceChild(newLikeBtn, likeBtn);
        newLikeBtn.addEventListener('click', handleLikeClick);
    }

    // --- 2. LÓGICA DE PUBLICAR COMENTARIO (CON TU VALIDACIÓN) ---
    const postBtn = document.getElementById('postCommentButton');
    const input = document.getElementById('newCommentInput');
    
    if (postBtn && input) {
        let newPostBtn = postBtn.cloneNode(true);
        let newInput = input.cloneNode(true);
        
        postBtn.parentNode.replaceChild(newPostBtn, postBtn);
        input.parentNode.replaceChild(newInput, input);
        
        newPostBtn.disabled = true;
        
        newInput.addEventListener('input', () => {
            const commentText = newInput.value.trim();
            if (commentText.length === 0 || commentText.length > 255) {
                newPostBtn.disabled = true;
            } else {
                newPostBtn.disabled = false;
            }
        });
        
        newPostBtn.addEventListener('click', handlePostComment);
    }
    
    // --- 3. ¡NUEVA LÓGICA AÑADIDA! (CLIC EN PERFIL) ---
    const avatarImg = document.getElementById('avatarUser');
    const nameEl = document.getElementById('nameUser');
    
    if (avatarImg && nameEl) {
        // Preparamos la URL (usando la variable global 'currentModalData')
        const profileUrl = `./profile.php?user_id=${currentModalData.ownerId}`;

        // Clonamos
        let newAvatarImg = avatarImg.cloneNode(true);
        let newNameEl = nameEl.cloneNode(true);
        avatarImg.parentNode.replaceChild(newAvatarImg, avatarImg);
        nameEl.parentNode.replaceChild(newNameEl, nameEl);

        // Añadimos estilo para que parezca un enlace
        newAvatarImg.style.cursor = 'pointer';
        newNameEl.style.cursor = 'pointer';

        // Función de redirección
        const redirectToProfile = () => {
            // No redirigir si el usuario está seleccionando texto
            if (window.getSelection().toString() === '') {
                window.location.href = profileUrl;
            }
        };

        // Adjuntamos listeners
        newAvatarImg.addEventListener('click', redirectToProfile);
        newNameEl.addEventListener('click', redirectToProfile);
    }
}

/**
 * Maneja el clic en el botón "Me Gusta"
 */
async function handleLikeClick() {
    const likeBtn = document.getElementById('likeButton'); 
    likeBtn.disabled = true;

    try {
        let formData = new FormData();
        formData.append('imageId', currentModalData.imageId);
        
        const response = await fetch('./BACKEND/FuncionesPHP/toggleLike.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            document.getElementById('likeCount').textContent = data.newLikeCount;
            if (data.newLikeStatus) {
                likeBtn.classList.add('active');
            } else {
                likeBtn.classList.remove('active');
            }
        }
    } catch (error) {
        console.error("Error en handleLikeClick:", error);
    } finally {
        likeBtn.disabled = false;
    }
}

/**
 * Maneja el clic en "Publicar" comentario
 */
async function handlePostComment() {
    const postBtn = document.getElementById('postCommentButton');
    const input = document.getElementById('newCommentInput');
    const errorDiv = document.getElementById('errorPostComment');
    
    const commentText = input.value.trim();
    if (commentText === '') return; 
    
    postBtn.disabled = true;
    input.disabled = true;
    if(errorDiv) errorDiv.style.display = 'none';

    try {
        let formData = new FormData();
        formData.append('imageId', currentModalData.imageId);
        formData.append('commentText', commentText);

        const response = await fetch('./BACKEND/FuncionesPHP/postComment.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            input.value = '';
            populateCommentList(data.comments, currentModalData.ownerId == logged_in_user_id);
            postBtn.disabled = true; // Deshabilitar de nuevo tras publicar
        } else {
            if(errorDiv) {
                errorDiv.textContent = data.message;
                errorDiv.style.display = 'block';
            }
        }
    } catch (error) {
        console.error("Error en handlePostComment:", error);
    } finally {
        postBtn.disabled = false;
        input.disabled = false;
    }
}

/**
 * Adjunta listeners a los botones de BORRAR comentario
 */
function attachDeleteCommentListeners() {
    const confirmModalEl = document.getElementById('confirmDeleteModal');
    const confirmBtn = document.getElementById('confirmDeleteButton');
    const confirmMessage = document.getElementById('deleteMessage');
    
    if (!confirmModalEl || !confirmBtn || !confirmMessage) {
        console.error("No se encuentra el modal de confirmación de borrado.");
        return;
    }
    
    const confirmModal = new bootstrap.Modal(confirmModalEl);

    document.querySelectorAll('button[data-action="delete-comment"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const commentId = e.currentTarget.dataset.commentId;
            
            confirmMessage.innerHTML = "¿Estás seguro de que deseas eliminar este comentario?";
            
            let newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', () => {
                executeDeleteComment(commentId);
                confirmModal.hide();
            }, { once: true });
            
            confirmModal.show();
        });
    });
}

/**
 * Ejecuta el fetch para borrar el comentario
 */
async function executeDeleteComment(commentId) {
    try {
        let formData = new FormData();
        formData.append('commentId', commentId);
        formData.append('imageId', currentModalData.imageId); // Necesario para recargar

        const response = await fetch('./BACKEND/FuncionesPHP/deleteComment.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            populateCommentList(data.comments, currentModalData.ownerId == logged_in_user_id);
        } else {
            alert(data.message); 
        }
    } catch (error) {
        console.error("Error en executeDeleteComment:", error);
    }
}

// =========================================================================
// 4. LÓGICA DE ESTADO (CARGA Y LIMPIEZA)
// =========================================================================

/**
 * Pone el modal en estado de "carga"
 */
function setModalToLoading() {
    const img = document.getElementById('imagePublic');
    const avatar = document.getElementById('avatarUser');
    const name = document.getElementById('nameUser');
    const title = document.getElementById('TitleImage');
    const count = document.getElementById('likeCount');
    const likeBtn = document.getElementById('likeButton');
    const comments = document.getElementById('commentListContainer');
    const error = document.getElementById('errorPostComment');

    if (img) img.src = '';
    if (avatar) avatar.src = '';
    if (name) name.textContent = 'Cargando...';
    if (title) title.textContent = '...';
    if (count) count.textContent = '-';
    if (likeBtn) likeBtn.classList.remove('active'); 
    if (comments) comments.innerHTML = '<p class="text-center text-secondary small">Cargando comentarios...</p>';
    if (error) error.style.display = 'none';
}

/**
 * Muestra un error dentro del modal
 */
function setModalToError(message) {
    // Re-buscamos elementos para asegurarnos
    const name = document.getElementById('nameUser');
    const title = document.getElementById('TitleImage');
    const comments = document.getElementById('commentListContainer');

    if (name) name.textContent = 'Error';
    if (title) title.textContent = message;
    if (comments) comments.innerHTML = `<div class="alert alert-danger">${message}</div>`;
}

// Limpiar el modal al cerrar para que no muestre datos viejos
if (imageModalEl) {
    imageModalEl.addEventListener('hidden.bs.modal', () => {
        setModalToLoading(); // Resetea el modal a su estado de "carga"
        
        currentModalData = {
            imageId: null,
            ownerId: null,
            followStatus: null
        };
    });
}