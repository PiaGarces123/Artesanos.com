<header class="header navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        
        <button class="navbar-toggler p-0 border-0 d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <a class="navbar-brand me-3 d-flex d-lg-none align-items-center" href="#">
            <div class="app-logo me-2">
                <img src="./Frontend/assets/images/appImages/logo.png" alt="logo" class="rounded-circle">
            </div>
            <span class="d-sm-block">Artesanos</span>
        </a>

        <div id="searchAndFiltersDesktop" class="d-none d-lg-flex flex-grow-1 align-items-center gap-3">
            
            <div class="input-group flex-grow-1" style="max-width: 500px;">
                <input type="text" class="search-input form-control" placeholder="🔍Buscar artesanías, perfiles..." id="searchInput">
                <button class="btn btn-primary" type="button" id="searchButtonDesktop">
                    <i class="uil uil-search"></i>
                </button>
            </div>
            
            <div class="buscarPor d-flex gap-2 me-4">
                <button class="buscarPor-btn btn btn-sm active" data-buscar-por="perfil">🔍Perfil</button>
                <button class="buscarPor-btn btn btn-sm" data-buscar-por="imagen">🔍Imagen</button>
            </div>
        </div>

        <div class="navbar-right d-flex align-items-center">
            <div class="user-info d-flex align-items-center gap-2">
                 <span>
                    <?php 
                        if (isset($_SESSION['username'])){
                            echo "<img src='./Frontend/assets/images/userImages/default.png' alt='user' class='rounded-circle' style='width: 32px;'> ";
                            echo htmlspecialchars($_SESSION['username']);
                        }else{
                            echo"<button type='button' class='btn btn-outline-primary btn-login-header' data-bs-toggle='modal' data-bs-target='#loginModal'>Iniciar sesión</button>";
                        }
                    ?>
                </span>
            </div>
        </div>
    </div>
</header>


<aside class="sidebar-fixed d-none d-lg-block" id="sidebarDesktop">
    <div class="sidebar-header d-flex align-items-center justify-content-center p-3 mb-3 border-bottom">
        <div class="app-logo me-2">
            <img src="./Frontend/assets/images/appImages/logo.png" alt="logo" class="rounded-circle">
        </div>
        <span class="fs-5 fw-bold text-secondary">Artesanos</span>
    </div>
    
    <div class="sidebar-actions px-3 mb-3 border-bottom pb-3">
        <button class="btn btn-primary w-100 mb-2" id="createAlbumBtn">
            <i class="uil uil-plus-circle me-1"></i> Publicar
        </button>
        
        <button class="btn btn-secondary w-100" id="myAlbumsBtn">
            <i class="uil uil-folder-open me-1"></i> Mis Álbumes
        </button>
    </div>
    
    <nav class="list-group list-group-flush">
        <a href="./index.php" id="navHome" class="list-group-item list-group-item-action nav-item active" data-view="home">
            <span class="nav-icon me-2">🏠</span> Inicio
        </a>
        <a href="#" id="navFavoritesDesktop" class="list-group-item list-group-item-action nav-item" data-view="favorites">
            <span class="nav-icon me-2">❤️</span> Favoritos
        </a>
        <a href="#" id="navProfileDesktop" class="list-group-item list-group-item-action nav-item" data-view="profile">
            <span class="nav-icon me-2">👤</span> Mi perfil
        </a>
    </nav>
    
    <div class="albums-list px-3 mt-auto pt-3 border-top">
        <div class="albums-title d-flex justify-content-between mb-2 small text-uppercase fw-bold">
            Álbumes recientes
            <span class="text-secondary small" id="albumsCount">0</span>
        </div>
        <div id="sidebarAlbums">
            </div>
    </div>
</aside>


<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header bg-light border-bottom">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menú de Navegación</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        
        <div id="searchAndFiltersMobile" class="p-3 border-bottom">
            <h6 class="small text-uppercase fw-bold mb-2">Buscar</h6>
            
            <div class="input-group mb-3">
                <input type="text" class="search-input form-control" placeholder="🔍Buscar artesanías, perfiles..." id="searchInputMobile">
                <button class="btn btn-primary" type="button" id="searchButtonMobile">
                    <i class="uil uil-search"></i>
                </button>
            </div>
            
            <h6 class="small text-uppercase fw-bold mb-2">Filtrar por:</h6>
            <div class="buscarPor d-flex gap-2 justify-content-start">
                <button class="buscarPor-btn btn btn-sm active" data-buscar-por="perfil">🔍Perfil</button>
                <button class="buscarPor-btn btn btn-sm" data-buscar-por="imagen">🔍Imagen</button>
            </div>
        </div>
        
        <div class="sidebar-actions p-3 border-bottom">
            <button class="btn btn-primary w-100 my-1" id="createAlbumBtnMobile">
                <i class="uil uil-plus-circle me-1"></i> Publicar
            </button>
            
            <button class="btn btn-secondary w-100 my-1" id="myAlbumsBtnMobile">
                <i class="uil uil-folder-open me-1"></i> Mis Álbumes
            </button>
        </div>

        
        <nav class="list-group list-group-flush">
            <a href="./index.php" id="navHome" class="list-group-item list-group-item-action nav-item active" data-view="home">
                <span class="nav-icon me-2">🏠</span> Inicio
            </a>
            <a href="#" id="navFavoritesMobile" class="list-group-item list-group-item-action nav-item" data-view="favorites">
                <span class="nav-icon me-2">❤️</span> Favoritos
            </a>
            <a href="#" id="navProfileMobile" class="list-group-item list-group-item-action nav-item" data-view="profile">
                <span class="nav-icon me-2">👤</span> Mi perfil
            </a>
        </nav>
        
        <div class="albums-list p-3 mt-auto">
            <div class="albums-title d-flex justify-content-between mb-2 small text-uppercase fw-bold">
                Álbumes recientes
                <span class="text-secondary small" id="albumsCount">0</span>
            </div>
            <div id="sidebarAlbums">
                </div>
        </div>
    </div>
</div>