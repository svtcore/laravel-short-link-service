<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @yield('styles')
</head>

<body>
    <nav class="sidebar">
        <div class="logo">Shortener Admin</div>

        <a href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="{{ route('admin.domains.index') }}"
            class="nav-item {{ request()->routeIs('admin.domains.*') ? 'active' : '' }}">
            <i class="fas fa-globe"></i> Domains
        </a>
        <a href="{{ route('admin.links.index') }}" class="nav-item nav-item {{ request()->routeIs('admin.links.*') ? 'active' : '' }}">
            <i class="fas fa-link"></i> Links
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-cog"></i> Settings
        </a>
    </nav>


    <div class="top-navbar">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
        </div>
        <div class="nav-icons">
            <i class="fas fa-bell"></i>
            <i class="fas fa-envelope"></i>
            <div class="user-profile">
                <span>{{ Auth::user()->name }}</span>
            </div>
        </div>
    </div>

    <main class="main-content">
        @yield('content')
    </main>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    @yield('scripts')
</body>

</html>