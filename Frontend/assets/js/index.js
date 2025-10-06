// Configuración Global
    const CONFIG = {
        API_BASE_URL: 'Backend/api/',  // URL base para las peticiones a la API
        IMAGES_PATH: 'Frontend/assets/images/usersImages/',  // Carpeta donde se guardan las imágenes de los usuarios
        CURRENT_USER_ID: 1 // En producción, esto vendría de la sesión
    };

    // Aplicación principal
    class ArtesanosApp {
        constructor() {
            // Variables de estado de la aplicación
            this.currentPage = 1;          // Página actual del feed (para paginación)
            this.loading = false;          // Bandera de carga (evita cargar más de una vez)
            this.hasMore = true;           // Indica si hay más datos que cargar
            this.currentAlbumId = null;    // ID del álbum seleccionado (si se filtra por álbum)
            this.currentSearch = '';       // Texto actual de búsqueda
            this.posts = [];               // Lista de publicaciones cargadas
            this.selectedImages = [];      // Imágenes seleccionadas al crear un álbum
            this.albums = [];              // Lista de álbumes del usuario
            
            // Iniciar la app
            this.init();
        }

        // Inicializa la aplicación cargando eventos y datos iniciales
        async init() {
            this.bindEvents();       // Asocia los eventos de la interfaz
            await this.loadUserInfo(); // Carga los datos del usuario (mock)
            await this.loadAlbums();   // Carga los álbumes del usuario
            await this.loadPosts();    // Carga el feed inicial de publicaciones
        }

        // ------------------ EVENTOS PRINCIPALES ------------------ 
        bindEvents() {
            // Búsqueda
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout); // Reinicia el temporizador
                // Espera 500ms antes de buscar (optimiza rendimiento)
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 500);
            });

            // Scroll infinito
            window.addEventListener('scroll', () => {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                    this.loadPosts();
                }
            });

            // Sidebar móvil : abre/cierra menú lateral
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('open'); // Alterna visibilidad
                });

                // Cierra si se hace clic fuera del sidebar
                document.addEventListener('click', (e) => {
                    if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                });
            }

            // Eventos de Modales
            this.bindModalEvents();
            
            // Navegación por el sidebar
            this.bindSidebarNavigation();
            
            //  Filtro de búsqueda por tipo (Perfil, Imagen, Ambos) 
            document.querySelectorAll('.buscarPor-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    // Quitar clase activa de todos
                    document.querySelectorAll('.buscarPor-btn').forEach(b => b.classList.remove('active'));
                    // Activar el botón seleccionado
                    btn.classList.add('active');
                    
                    // Obtiene el filtro actual
                    const tipoBusqueda = btn.dataset.buscarPor;
                    console.log("Filtro activo:", tipoBusqueda);
                    
                    // Aplica el filtro al feed
                    this.handleSearch(this.currentSearch, tipoBusqueda);
                });
            });
        }
        // ------------------------- Eventos de Modales ------------------------- 
        bindModalEvents() {
            // Modal publicar Contenido
            const createAlbumBtn = document.getElementById('createAlbumBtn');
            const createAlbumModal = document.getElementById('createAlbumModal');
            const closeCreateModal = document.getElementById('closeCreateModal');
            const cancelCreate = document.getElementById('cancelCreate');
            const createAlbumForm = document.getElementById('createAlbumForm');
            const fileUpload = document.getElementById('fileUpload');
            const imageInput = document.getElementById('imageInput');

            //Abre el modal
            createAlbumBtn.addEventListener('click', () => {
                createAlbumModal.classList.add('active');
            });

             // Cierra el modal desde botones
            [closeCreateModal, cancelCreate].forEach(btn => {
                btn.addEventListener('click', () => {
                    createAlbumModal.classList.remove('active');
                    this.resetCreateForm();
                });
            });
            // Cierra modal si se clickea fuera
            createAlbumModal.addEventListener('click', (e) => {
                if (e.target === createAlbumModal) {
                    createAlbumModal.classList.remove('active');
                    this.resetCreateForm();
                }
            });

            // --- Subida de archivos --- 
            fileUpload.addEventListener('click', () => imageInput.click());

            fileUpload.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUpload.classList.add('dragover');
            });

            fileUpload.addEventListener('dragleave', () => {
                fileUpload.classList.remove('dragover');
            });

            fileUpload.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUpload.classList.remove('dragover');
                this.handleFiles(e.dataTransfer.files);  // Maneja las imágenes arrastradas
            });

            imageInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files); // Maneja selección normal
            });


            // Envía el formulario de creación
            createAlbumForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createAlbum();
            });

            // Modal: mis álbumes
            const myAlbumsBtn = document.getElementById('myAlbumsBtn');
            const myAlbumsModal = document.getElementById('myAlbumsModal');
            const closeAlbumsModal = document.getElementById('closeAlbumsModal');

            // Abre y carga los álbumes
            myAlbumsBtn.addEventListener('click', async () => {
                myAlbumsModal.classList.add('active');
                await this.loadAlbums();
                this.renderAlbumsGrid();
            });

            // Cierra modal
            closeAlbumsModal.addEventListener('click', () => {
                myAlbumsModal.classList.remove('active');
            });

            myAlbumsModal.addEventListener('click', (e) => {
                if (e.target === myAlbumsModal) {
                    myAlbumsModal.classList.remove('active');
                }
            });
        }


        // ------------------------ NAVEGACIÓN DEL SIDEBAR ------------------------ 
        bindSidebarNavigation() {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Actualiza estado visual
                    document.querySelectorAll('.nav-item').forEach(nav => {
                        nav.classList.remove('active');
                    });                    
                    item.classList.add('active');
                    
                    // Cambia la vista
                    const view = item.dataset.view;
                    this.handleNavigation(view);
                });
            });
        }

        handleNavigation(view) {
            console.log('Navegando a:', view);
            // Controla el cambio de vista según el item
            switch(view) {
                case 'home':
                    this.currentAlbumId = null;
                    this.resetFeed();
                    this.loadPosts();
                    break;
                case 'favorites':
                    // Acá va la lógica para ver favoritos( los likes)
                    break;
                // ...otros casos
            }
        }


        // ---------------------- DATOS DEL USUARIO ---------------------- ACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
        async loadUserInfo() {
            // En producción, esto vendría del backend con la sesión
            // Por ahora usamos datos de ejemplo
            const userName = 'Juan Artesano';
            const userUsername = '@juan.artesano';
            const initials = userName.split(' ').map(n => n[0]).join('');
            
            document.getElementById('userName').textContent = userName;
            document.getElementById('userUsername').textContent = userUsername;
            document.getElementById('userInitials').textContent = initials;
        }

        // ---------------------- DATOS DEL ALBUM ---------------------- 
        async loadAlbums() {
            try {
                const response = await fetch(`${CONFIG.API_BASE_URL}albums.php?user_id=${CONFIG.CURRENT_USER_ID}`);
                const result = await response.json();
                
                if (result.success) {
                    this.albums = result.data;
                    this.renderSidebarAlbums(); // Muestra los primeros 3 en el sidebar
                    document.getElementById('albumsCount').textContent = this.albums.length;
                }
            } catch (error) {
                console.error('Error al cargar álbumes:', error);
            }
        }

        //  RENDERIZAR ÁLBUMES EN EL SIDEBAR
        renderSidebarAlbums() {
            const container = document.getElementById('sidebarAlbums');
            container.innerHTML = '';  // Limpia la lista antes de renderizar
            
            // Solo mostramos los primeros 3 álbumes
            this.albums.slice(0, 3).forEach(album => {
                const albumEl = document.createElement('div');
                albumEl.className = 'album-item';
                albumEl.dataset.albumId = album.id;
                
                //Continuación del script dentro del HTML
                albumEl.innerHTML = `
                    <div class="album-thumb">
                        ${album.cover_image ? `<img src="${album.cover_image}" alt="${album.title}">` : ''}
                    </div>
                    <div class="album-info">
                        <div class="album-name">${album.title}</div>
                        <div class="album-count">${album.image_count} piezas</div>
                    </div>
                `;
                
                albumEl.addEventListener('click', () => {
                    this.filterByAlbum(album.id);
                });
                
                container.appendChild(albumEl);
            });
        }

        // ---------------------- GRID DE ÁLBUMES (en el modal) ---------------------- 
        renderAlbumsGrid() {
            const container = document.getElementById('albumsGrid');
            container.innerHTML = ''; // Limpia el grid antes de agregar elementos
            
            // Si no hay álbumes, mostramos un mensaje
            if (this.albums.length === 0) {
                albumsGrid.innerHTML = '<p class="no-albums">No tienes álbumes todavía.</p>';
                return;
            }

            // Recorre y genera una "card" por álbum
            this.albums.forEach(album => {
                const albumCard = document.createElement('div');
                albumCard.className = 'album-card';
                
                const timeAgo = this.getTimeAgo(new Date(album.creation_date));
                
                albumCard.innerHTML = `
                    <div style="aspect-ratio: 4/3; background: linear-gradient(45deg, #e60023, #ff6b6b); border-radius: 8px; margin-bottom: 12px; position: relative; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; overflow: hidden;">
                        ${album.cover_image ? `<img src="${album.cover_image}" style="width: 100%; height: 100%; object-fit: cover;">` : '🎨'}
                        <div style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.5); border-radius: 12px; padding: 4px 8px; font-size: 12px;">${album.image_count}</div>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">${album.title}</h3>
                    <div style="font-size: 11px; color: #999;">${timeAgo}</div>
                `;
                
                // Al hacer clic, filtramos el feed por ese álbum
                albumCard.addEventListener('click', () => {
                    document.getElementById('myAlbumsModal').classList.remove('active');
                    this.filterByAlbum(album.id);
                });
                
                container.appendChild(albumCard);
            });
            
            // Botón para agregar nuevo álbum
            const addCard = document.createElement('div');
            addCard.className = 'album-card';
            addCard.style.border = '2px dashed #e9e9e9';
            addCard.style.display = 'flex';
            addCard.style.alignItems = 'center';
            addCard.style.justifyContent = 'center';
            addCard.style.color = '#999';
            addCard.style.minHeight = '200px';
            addCard.innerHTML = `
                <div style="text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 8px;">➕</div>
                    <div style="font-size: 14px; font-weight: 500;">Crear álbum</div>
                </div>
            `;
            addCard.addEventListener('click', () => {
                document.getElementById('myAlbumsModal').classList.remove('active');
                document.getElementById('createAlbumModal').classList.add('active');
            });
            container.appendChild(addCard);
        }

        getTimeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            const intervals = {
                año: 31536000,
                mes: 2592000,
                semana: 604800,
                día: 86400,
                hora: 3600,
                minuto: 60
            };
            
            for (let [name, value] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / value);
                if (interval >= 1) {
                    return `Creado hace ${interval} ${name}${interval > 1 ? (name === 'mes' ? 'es' : 's') : ''}`;
                }
            }
            return 'Creado hace un momento';
        }


        handleSearch(searchTerm) {
            this.currentSearch = searchTerm;
            this.currentFilterType = tipoBusqueda; // guardamos el tipo de búsqueda
            this.resetFeed();
            this.loadPosts();
        }

        filterByAlbum(albumId) {
            this.currentAlbumId = albumId;
            this.resetFeed();
            this.loadPosts();
        }

        resetFeed() {
            this.currentPage = 1;
            this.hasMore = true;
            this.posts = [];
            document.getElementById('postsGrid').innerHTML = '';
            document.getElementById('noResults').style.display = 'none';
            document.getElementById('error').style.display = 'none';
        }

        //  CARGAR POSTS (con paginación)
        async loadPosts() {
            // Evita múltiples cargas simultáneas
            if (this.loading || !this.hasMore) return;

            this.loading = true;
            this.showLoading(true);

            try {
                const url = this.buildApiUrl(); // Construimos la URL según filtros
                const response = await fetch(url);
                const result = await response.json();
                
                // Si la respuesta es correcta, procesamos los datos
                if (result.success) {
                    this.posts.push(...result.data);
                    this.renderPosts(result.data);
                    this.hasMore = result.has_more;
                    this.currentPage++;
                     // Si no hay más publicaciones
                    if (this.posts.length === 0) {
                        document.getElementById('noResults').style.display = 'block';
                    }
                } else {
                    this.showError();
                }
            } catch (error) {
                console.error('Error loading posts:', error);
                this.showError();
            }
            // Quitamos el loader
            this.loading = false;
            this.showLoading(false);
        }

        // ---------------- CONSTRUIR URL DE API CON FILTROS ---------------- 
        // Esta función arma dinámicamente la URL de la API (feed.php)
        // incluyendo parámetros de paginación y filtros activos (álbum, búsqueda y tipo)
        buildApiUrl() {
            // Inicializa los parámetros base: página actual y límite de resultados por página
            const params = new URLSearchParams({
                page: this.currentPage,
                limit: 20
            });

            // Si hay un álbum seleccionado, lo añade al filtro
            if (this.currentAlbumId) {
                params.append('album_id', this.currentAlbumId);
            }

            // Si hay un texto de búsqueda activo, lo añade al filtro
            if (this.currentSearch) {
                params.append('search', this.currentSearch);
            }

            // Si hay un filtro por tipo (ej: pintura, cerámica), lo añade al filtro
            if (this.currentFilterType) {
                params.append('tipo', this.currentFilterType);
            }

            // Retorna la URL final construida con todos los filtros activos
            return `${CONFIG.API_BASE_URL}feed.php?${params.toString()}`;
        }

        // ----------------------- RENDERIZAR POSTS (CARDS) ----------------------- 
        // Renderiza en pantalla la cuadrícula de publicaciones (cards)
        renderPosts(posts) {
            const grid = document.getElementById('postsGrid');
            
            // Itera sobre cada publicación recibida desde la API
            posts.forEach(post => {
                // Crea el contenedor de la card
                const postElement = this.createPostElement(post);
                // Lo agrega al contenedor de la cuadrícula
                grid.appendChild(postElement);
            });
        }

        // ----------------------- CREAR CARD INDIVIDUAL -----------------------
        // Genera la estructura HTML para una publicación individual (card)
        createPostElement(post) {
            const article = document.createElement('article');
            article.className = 'pin-card';
            article.dataset.imageId = post.id;
            
            // Define la ruta de la imagen (usa placeholder si no existe)
            const imagePath = post.image_url || `${CONFIG.IMAGES_PATH}${post.id}.jpg`;
            
            // Estructura HTML interna de la card
            article.innerHTML = `
                <div class="pin-overlay">
                    <button class="save-btn" data-image-id="${post.id}">Guardar</button>
                </div>
                <img src="${imagePath}" alt="${post.title}" class="pin-image" loading="lazy" onerror="this.src='https://via.placeholder.com/300x400?text=Sin+Imagen'">
                <div class="pin-info">
                    ${post.album.title ? `<span class="buscarPor-tag">${post.album.title}</span>` : ''}
                    <h3 class="pin-title">${post.title}</h3>
                    <div class="pin-meta">
                        <div class="artisan-info">
                            <div class="artisan-avatar">
                                <span style="color: white; font-size: 10px;">${this.getInitials(post.artisan.name)}</span>
                            </div>
                            <span class="artisan-name">${post.artisan.name}</span>
                        </div>
                        <div class="pin-stats">
                            <span class="like-btn" data-image-id="${post.id}" style="cursor: pointer;">❤️ ${post.likes_count}</span>
                            <span>💬 ${post.comments_count}</span>
                        </div>
                    </div>
                </div>
            `;

            // --- Asigna eventos interactivos a los botones ---

            // Botón "Guardar"
            const saveBtn = article.querySelector('.save-btn');
            saveBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Evita que se dispare el evento del artículo
                this.toggleLike(post.id, saveBtn); // Alterna estado de guardado
            });

            // Botón "Like"
            const likeBtn = article.querySelector('.like-btn');
            likeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleLike(post.id, likeBtn); // Alterna estado de "me gusta"
            });

            // Click general en la card, abre detalle
            article.addEventListener('click', () => {
                this.showPostDetail(post);
            });

            return article;
        }

        // ----------------------- OBTENER INICIALES -----------------------
        // Devuelve las iniciales (máx. 2 letras) del nombre del artesano
        getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        }

        // ----------------------- ALTERNAR LIKE / GUARDAR -----------------------
        // Envía solicitud al backend para alternar el "me gusta" o guardado de una imagen
        async toggleLike(imageId, button) {
            try {
                const response = await fetch(`${CONFIG.API_BASE_URL}toggle_like.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: CONFIG.CURRENT_USER_ID,
                        image_id: imageId
                    })
                });

                const result = await response.json();

                
                if (result.success) {
                    // Actualiza todos los elementos asociados a esta imagen
                    const likeElements = document.querySelectorAll(`[data-image-id="${imageId}"]`);
                    likeElements.forEach(el => {

                        // Actualiza contador 
                        if (el.classList.contains('like-btn')) {
                            const currentCount = parseInt(el.textContent.match(/\d+/)[0]);
                            const newCount = result.action === 'added' ? currentCount + 1 : currentCount - 1;
                            el.innerHTML = `❤️ ${newCount}`;

                        // Cambia texto del botón Guardar
                        } else if (el.classList.contains('save-btn')) {
                            el.textContent = result.action === 'added' ? '✓ Guardado' : 'Guardar';
                            setTimeout(() => {
                                if (result.action === 'added') el.textContent = 'Guardado';
                            }, 1500);
                        }
                    });
                }
            } catch (error) {
                console.error('Error al dar like:', error);
            }
        }

        // ----------------------- MOSTRAR DETALLE DE PUBLICACIÓN -----------------------
        // (Versión temporal con alert; luego reemplazar por modal)
        showPostDetail(post) {
            alert(`${post.title}\n\nArtesano: ${post.artisan.name}\nÁlbum: ${post.album.title}\nLikes: ${post.likes_count}\nComentarios: ${post.comments_count}\n\n[Aquí irá un modal con más detalles]`);
        }

        // ----------------------- MANEJO DE ARCHIVOS (IMÁGENES NUEVAS) -----------------------
        // Procesa las imágenes seleccionadas por el usuario
        handleFiles(files) {
            Array.from(files).forEach(file => {
                // Valida tipo y tamaño (máx. 5MB)
                if (file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        // Agrega imagen a la lista de seleccionadas
                        this.selectedImages.push({
                            file: file,
                            url: e.target.result,
                            name: file.name
                        });
                        // Renderiza vista previa
                        this.renderImagePreviews();
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert(`El archivo ${file.name} es demasiado grande o no es una imagen válida.`);
                }
            });
        }

        // ----------------------- RENDERIZAR VISTAS PREVIAS DE IMÁGENES -----------------------
        renderImagePreviews() {
            const container = document.getElementById('imagePreview');
            container.innerHTML = '';
            
            // Crea una miniatura por cada imagen seleccionada
            this.selectedImages.forEach((img, index) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${img.url}" class="preview-image" alt="${img.name}">
                    <button class="remove-image" data-index="${index}">&times;</button>
                `;
                
                // Botón para eliminar la imagen seleccionada
                previewItem.querySelector('.remove-image').addEventListener('click', () => {
                    this.removeImage(index);
                });
                
                container.appendChild(previewItem);
            });
        }

        // ----------------------- ELIMINAR IMAGEN DE LA LISTA -----------------------
        removeImage(index) {
            this.selectedImages.splice(index, 1);
            this.renderImagePreviews();
        }

        // ----------------------- CREAR NUEVO ÁLBUM -----------------------
        async createAlbum() {
            const title = document.getElementById('albumTitle').value.trim();
            
            // Validaciones básicas
            if (!title) {
                alert('Por favor ingresa un nombre para el álbum');
                return;
            }

            if (this.selectedImages.length === 0) {
                alert('Por favor selecciona al menos una imagen');
                return;
            }

            // Muestra estado "Cargando..."
            const loadingBtn = document.querySelector('#createAlbumForm button[type="submit"]');
            const originalText = loadingBtn.textContent;
            loadingBtn.textContent = 'Creando...';
            loadingBtn.disabled = true;

            try {
                // Datos del álbum a enviar
                const albumData = {
                    title: title,
                    user_id: CONFIG.CURRENT_USER_ID,
                    images: this.selectedImages.map(img => ({
                        title: img.name.split('.')[0] // título sin extensión
                    }))
                };

                // Solicitud para crear el álbum
                const response = await fetch(`${CONFIG.API_BASE_URL}create_album.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(albumData)
                });

                const result = await response.json();
                
                if (result.success) {
                    // Carga de imágenes asociadas al nuevo álbum
                    await this.uploadImages(result.album_id);
                    
                    alert('¡Álbum creado exitosamente!');
                    // Cierra modal, resetea formulario y recarga lista
                    document.getElementById('createAlbumModal').classList.remove('active');
                    this.resetCreateForm();
                    await this.loadAlbums();
                    this.filterByAlbum(result.album_id);
                } else {
                    alert('Error al crear el álbum: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear el álbum. Por favor intenta de nuevo.');
            } finally {
                // Restaura botón
                loadingBtn.textContent = originalText;
                loadingBtn.disabled = false;
            }
        }

        // ----------------------- SUBIR IMÁGENES AL SERVIDOR -----------------------
        async uploadImages(albumId) {
            const formData = new FormData();
            formData.append('album_id', albumId);
            formData.append('user_id', CONFIG.CURRENT_USER_ID);
            
            // Añade cada archivo al FormData
            this.selectedImages.forEach((img, index) => {
                formData.append(`images[${index}]`, img.file);
            });

            // Nota: Necesitarás crear un endpoint upload_images.php para manejar esto
            // const response = await fetch(`${CONFIG.API_BASE_URL}upload_images.php`, {
            //     method: 'POST',
            //     body: formData
            // });
            
            return new Promise(resolve => setTimeout(resolve, 1000));
        }

        // ----------------------- RESETEAR FORMULARIO DE CREACIÓN -----------------------
        resetCreateForm() {
            document.getElementById('createAlbumForm').reset();
            this.selectedImages = [];
            document.getElementById('imagePreview').innerHTML = '';
        }

        // ----------------------- MOSTRAR / OCULTAR LOADING -----------------------
        showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        // ----------------------- MOSTRAR ERROR GENERAL -----------------------
        showError() {
            document.getElementById('error').style.display = 'block';
        }
    }

    // ----------------------- INICIALIZAR LA APLICACIÓN -----------------------
    // Cuando el DOM esté listo, se crea una nueva instancia de la app principal
    document.addEventListener('DOMContentLoaded', () => {
        new ArtesanosApp();
    });