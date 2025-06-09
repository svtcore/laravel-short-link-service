<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin panel for URL shortener service">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @vite(['resources/css/admin/burger.css'])
    @yield('styles')
</head>

<body>
    @if(app()->isDownForMaintenance())
        <div class="maintenance-banner fixed-bottom">
            <div class="container-fluid bg-danger text-white py-2">
                <div class="row align-items-center">
                    <div class="col text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>MAINTENANCE NOTICE:</strong> This website is currently undergoing maintenance. Access is
                        restricted to administrators only.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" aria-label="Main navigation">
        <div class="logo" role="heading" aria-level="1">Shortener Admin</div>

        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
            <i class="fas fa-chart-line" aria-hidden="true"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        <!-- Domains -->
        <a href="{{ route('admin.domains.index') }}"
            class="nav-item {{ request()->routeIs('admin.domains.*') ? 'active' : '' }}"
            aria-current="{{ request()->routeIs('admin.domains.*') ? 'page' : 'false' }}">
            <i class="fas fa-globe" aria-hidden="true"></i>
            <span class="nav-text">Domains</span>
        </a>

        <!-- Links -->
        <a href="{{ route('admin.links.index') }}"
            class="nav-item {{ request()->routeIs('admin.links.*') ? 'active' : '' }}"
            aria-current="{{ request()->routeIs('admin.links.*') ? 'page' : 'false' }}">
            <i class="fas fa-link" aria-hidden="true"></i>
            <span class="nav-text">Links</span>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}"
            class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}">
            <i class="fas fa-users" aria-hidden="true"></i>
            <span class="nav-text">Users</span>
        </a>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}"
            class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
            aria-current="{{ request()->routeIs('admin.settings.*') ? 'page' : 'false' }}">
            <i class="fas fa-cog" aria-hidden="true"></i>
            <span class="nav-text">Settings</span>
        </a>
    </nav>

    <div class="top-navbar">
        <div class="burger-menu" id="burgerMenu">
            <i class="fas fa-bars"></i>
        </div>
        <div class="search-bar" role="search">
            <label for="global-search" class="visually-hidden">Search</label>
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="text" id="global-search" placeholder="Search..." aria-controls="search-results"
                data-search-url="{{ route('admin.search.count') }}">
            <div id="search-results" class="search-results">
                <div class="search-category" data-search-type="links">
                    <a href="{{ route('admin.search.links') }}" class="category-link text-decoration-none"
                        data-route="links">
                        <div class="category-title">
                            <i class="fas fa-link"></i> Links <span class="badge results-count">0</span>
                        </div>
                    </a>
                    <div class="category-results"></div>
                </div>
                <div class="search-category" data-search-type="domains">
                    <a href="{{ route('admin.search.domains') }}" class="category-link text-decoration-none"
                        data-route="domains">
                        <div class="category-title">
                            <i class="fas fa-globe"></i> Domains <span class="badge results-count">0</span>
                        </div>
                    </a>
                    <div class="category-results"></div>
                </div>
                <div class="search-category" data-search-type="users">
                    <a href="{{ route('admin.search.users') }}" class="category-link text-decoration-none"
                        data-route="users">
                        <div class="category-title">
                            <i class="fas fa-user"></i> Users <span class="badge results-count">0</span>
                        </div>
                    </a>
                    <div class="category-results"></div>
                </div>
            </div>
        </div>
        <div class="nav-icons">
            <div class="user-profile">
                <a href="#" class="profile-link text-decoration-none">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ Auth::user()->name }}</span>
                </a>
            </div>
        </div>
    </div>

    <main class="main-content">
        @yield('content')
    </main>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    @vite(['resources/js/admin/search.js'])
    @yield('scripts')
</body>

</html>