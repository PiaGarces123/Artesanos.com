<!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="./Frontend/assets/images/appImages/logo.png" alt="logo">
            </div>
            
        </div>
        
        <div class="sidebar-actions">
            <button class="action-button btn-primary" id="createAlbumBtn">
                <img src="./Frontend/assets/images/appImages/publicar.png" alt="publicar">
            </button>
            
            <button class="action-button btn-secondary" id="myAlbumsBtn">
                <img src="./Frontend/assets/images/appImages/album.png" alt="album">
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a  id="navHome" class="nav-item active" data-view="home">
                <span class="nav-icon">🏠</span>
                Inicio
            </a>
            <a id="navFavorites" class="nav-item" data-view="favorites">
                <span class="nav-icon">❤️</span>
                Favoritos
            </a>
            <a id="navProfile" class="nav-item" data-view="profile">
                <span class="nav-icon">👤</span>
                Mi perfil
            </a>
        </nav>
        
        <div class="albums-list">
            <div class="albums-title">
                Álbumes recientes
                <span style="color: #999; font-size: 12px;" id="albumsCount">0</span>
            </div>
            <div id="sidebarAlbums">
                <!-- Los álbumes se cargarán aquí -->
            </div>
        </div>
    </aside>

    <!-- Header/NavBar -->
    <header class="header">
        <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="🔍Buscar..." id="searchInput">
        </div>
        
        <div class="buscarPor" id="buscarContainer">
            <button class="buscarPor-btn active" data-buscar-por="perfil">🔍Perfil</button>
            <button class="buscarPor-btn" data-buscar-por="imagen">🔍Imagen</button>
            <button class="buscarPor-btn" data-buscar-por="ambos">🔍Ambos</button>
        </div>

        <div class="navbar-right">
            <div class="user-info">
                 <span>
                    <?php 
                        if (isset($_SESSION['username'])){
                            echo "<img src='./Frontend/assets/images/userImages/default.png' alt='user'> ";
                            echo htmlspecialchars($_SESSION['username']);
                        }else{
                            echo"<button id='loginBtn' class='btn-login-header'>Iniciar sesión</button>";
                        }
                    ?>
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="posts-grid" id="postsGrid">
            <!-- Los posts se cargarán aquí dinámicamente -->
        </div>
        
        <div class="loading" id="loading">
            <div class="loading-spinner"></div>
            Cargando artesanías...
        </div>
        
        <div class="no-results" id="noResults" style="display: none;">
            <h3>No se encontraron resultados</h3>
            <p>Intenta con otros términos de búsqueda</p>
        </div>
        
        <div class="error" id="error" style="display: none;">
            Error al cargar las artesanías. Por favor, intenta de nuevo.
        </div>
    </main>