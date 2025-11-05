// =========================================================================
// 1. CONFIGURACIÓN INICIAL Y ELEMENTOS DEL MODAL
// =========================================================================

let imageModalInstance = null;
let currentModalData = { // Almacena los datos de la imagen abierta
    imageId: null,
    ownerId: null,
    followStatus: null,
    imageTitle: null,   
    imageVisibility: null
};

// --- Elementos del DOM (Solo el modal principal) ---
const imageModalEl = document.getElementById('imageModal');

// Asegurarnos de crear la instancia del modal una sola vez
if (imageModalEl) {
    imageModalInstance = new bootstrap.Modal(imageModalEl);
}

// (Nuevos Modales)
const editImageModalEl = document.getElementById('editImageModal');
const editImageModal = editImageModalEl ? new bootstrap.Modal(editImageModalEl) : null;
const reportImageModalEl = document.getElementById('reportImageModal');
const reportImageModal = reportImageModalEl ? new bootstrap.Modal(reportImageModalEl) : null;

/**
 * Formatea una fecha MySQL (YYYY-MM-DD HH:MM:SS) a un formato simple (DD/MM/YYYY).
 */
function formatSimpleDate(mysqlDate) {
    if (!mysqlDate) return '';
    try {
        const date = new Date(mysqlDate);
        // Opciones para un formato corto (ej: 31/10/2025)
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return date.toLocaleDateString('es-ES', options); // 'es-ES' nos da el formato DD/MM/YYYY
    } catch (e) {
        console.error('Error al formatear la fecha:', e);
        return ''; // Devuelve vacío si la fecha es inválida
    }
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
            followStatus: data.followStatus,
            imageTitle: data.imageTitle,             
            imageVisibility: data.imageVisibility
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
    
    // --- AÑADIMOS FECHA, TITULO ALBUM Y TITULO IMAGEN ---
    const titleEl = document.getElementById('TitleImage');
    let titleHTML = '';
    
    // 1. Añadimos la fecha de la imagen
    if (data.imagePublicationDate) {
        titleHTML += formatSimpleDate(data.imagePublicationDate);
    }
    
    // 2. Añadimos el título del álbum (si existe)
    if (data.albumTitle) {
        if (titleHTML !== '') {
            titleHTML += '<br>'; // Salto de línea si ya hay fecha
        }
        // Usamos text-uppercase de Bootstrap para que se vea "ALBUM:"
        titleHTML += `<span class="text-uppercase small fw-bold">Álbum:</span> ${data.albumTitle}`;
    }

    // 3. Añadimos el título de la imagen (si existe)
    if (data.imageTitle) {
        if (titleHTML !== '') {
            titleHTML += '<br>'; // Salto de línea
        }
        titleHTML += data.imageTitle;
    }
    
    titleEl.innerHTML = titleHTML;
    // --- ---

    // --- Lógica de Likes ---
    const likeBtn = document.getElementById('likeButton');
    document.getElementById('likeCount').textContent = data.likeCount;
    if (data.hasLiked) {
        likeBtn.classList.add('active'); 
    } else {
        likeBtn.classList.remove('active');
    }

    // --- Lógica de Botones Edit/Report ---
    const actionsContainer = document.getElementById('imageActionsContainer');
    let actionButtonHTML = '';
    if (data.isMyImage) {
        actionButtonHTML = `
            <button class="btn btn-outline-secondary btn-sm w-auto" id="editImageButton">
                <i class="uil uil-edit me-1"></i> Editar Imagen
            </button>
        `;
    } else {
        actionButtonHTML = `
            <button class="btn btn-outline-danger btn-sm w-auto" id="reportImageButton">
                <i class="uil uil-exclamation-triangle me-1"></i> Denunciar
            </button>
        `;
    }
    actionsContainer.innerHTML = actionButtonHTML;

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

            // --- Formateamos la fecha ---
            const formattedDate = formatSimpleDate(comment.C_publicationDate);

            commentsHTML += `
                <div class="comment">
                    <div class="comment-user d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center"> 
                            <div class="comment-avatar">
                                <img src="${comment.U_profilePic}" alt="Avatar de ${comment.U_nameUser}">
                            </div>
                            <div class="comment-username">${comment.U_nameUser}</div>
                            <div class="comment-date text-muted small ms-2">${formattedDate}</div>
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
    
    // --- 3. LÓGICA (CLIC EN PERFIL) ---
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

    // Listener para el botón "Editar Imagen"
    const editBtn = document.getElementById('editImageButton');
    if (editBtn) {
        let newEditBtn = editBtn.cloneNode(true);
        editBtn.parentNode.replaceChild(newEditBtn, editBtn);
        newEditBtn.addEventListener('click', handleEditImageClick);
    }

    // Listener para el botón "Denunciar Imagen"
    const reportBtn = document.getElementById('reportImageButton');
    if (reportBtn) {
        let newReportBtn = reportBtn.cloneNode(true);
        reportBtn.parentNode.replaceChild(newReportBtn, reportBtn);
        newReportBtn.addEventListener('click', handleReportImageClick);
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
    const confirmModalEl = document.getElementById('confirmDeleteCommentModal');
    const confirmBtn = document.getElementById('confirmDeleteCommentButton');
    const confirmMessage = document.getElementById('deleteCommentMessage');
    
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

/**
 * Maneja el clic en "Editar Imagen". Pre-popula y muestra el modal de edición.
 */
function handleEditImageClick() {
    if (!editImageModal) return;

    // 1. Poblar el modal de edición
    const form = document.getElementById('editImageForm');
    const titleInput = document.getElementById('editImageTitleInput');
    const idInput = document.getElementById('editImageIdInput');
    const visibilityInput = document.getElementById('editImageVisibilityInput');
    const saveBtn = document.getElementById('saveEditImageButton');

    titleInput.value = currentModalData.imageTitle;
    idInput.value = currentModalData.imageId;
    // (1 = Privado, 0 = Público). El checkbox es 'true' si es 1.
    visibilityInput.checked = (currentModalData.imageVisibility === 1); 
    
    // 2. Limpiar listener y adjuntar el nuevo
    let newSaveBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

    newSaveBtn.addEventListener('click', (e) => {
        e.preventDefault();
        executeEditImage(form);
    });

    // 3. Mostrar el modal de edición
    editImageModal.show();
}

/**
 * Ejecuta el fetch para guardar los cambios de la imagen
 */
async function executeEditImage(form) {
    const errorDiv = document.getElementById('errorEditImage');
    const saveBtn = document.getElementById('saveEditImageButton');
    errorDiv.style.display = 'none';
    saveBtn.disabled = true;

    try {
        const formData = new FormData(form);
        const res = await fetch('./BACKEND/FuncionesPHP/editarImagen.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            editImageModal.hide(); // Ocultar modal de edición

            // Actualizar los datos en el modal de imagen (sin recargar)
            document.getElementById('TitleImage').innerHTML = `${formatSimpleDate(new Date())}<br>${data.newTitle}`; // Asumimos fecha de hoy
            currentModalData.imageTitle = data.newTitle;
            currentModalData.imageVisibility = data.newVisibility;
            
            showStaticNotificationModal('success', data.message);

        } else {
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.textContent = 'Error de conexión.';
        errorDiv.style.display = 'block';
    } finally {
        saveBtn.disabled = false;
    }
}

/**
 * Maneja el clic en "Denunciar Imagen". Pre-popula y muestra el modal.
 */
function handleReportImageClick() {
    if (!reportImageModal) return;

    // 1. Poblar el modal de denuncia
    const form = document.getElementById('reportImageForm');
    const idInput = document.getElementById('reportImageIdInput');
    const reasonInput = document.getElementById('reportReasonInput');
    const saveBtn = document.getElementById('sendReportButton');
    
    idInput.value = currentModalData.imageId;
    reasonInput.value = ''; // Limpiar razón anterior

    // 2. Limpiar listener y adjuntar el nuevo
    let newSaveBtn = saveBtn.cloneNode(true);
    saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

    newSaveBtn.addEventListener('click', (e) => {
        e.preventDefault();
        executeReportImage(form);
    });
    
    reportImageModal.show();
}

/**
 * Ejecuta el fetch para enviar la denuncia
 */
async function executeReportImage(form) {
    const errorDiv = document.getElementById('errorReportImage');
    const saveBtn = document.getElementById('sendReportButton');
    errorDiv.style.display = 'none';
    saveBtn.disabled = true;

    try {
        const formData = new FormData(form);
        if (formData.get('reportReason').trim().length === 0) {
            errorDiv.textContent = 'Debes escribir un motivo.';
            errorDiv.style.display = 'block';
            saveBtn.disabled = false;
            return;
        }

        const res = await fetch('./BACKEND/FuncionesPHP/denunciarImagen.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            reportImageModal.hide();
            showStaticNotificationModal('success', data.message);
        } else {
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.textContent = 'Error de conexión.';
        errorDiv.style.display = 'block';
    } finally {
        saveBtn.disabled = false;
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
            followStatus: null,
            imageTitle: null,
            imageVisibility: null
        };
    });
}