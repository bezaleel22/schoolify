<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('website::layouts.partials.head')
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Admin specific styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @stack('admin-styles')
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Admin Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h4>Admin Panel</h4>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('admin.pages.index') }}"><i class="fas fa-file"></i> Pages</a></li>
                <li><a href="{{ route('admin.blog.index') }}"><i class="fas fa-blog"></i> Blog</a></li>
                <li><a href="{{ route('admin.events.index') }}"><i class="fas fa-calendar"></i> Events</a></li>
                <li><a href="{{ route('admin.gallery.index') }}"><i class="fas fa-images"></i> Gallery</a></li>
                <li><a href="{{ route('admin.staff.index') }}"><i class="fas fa-users"></i> Staff</a></li>
                <li><a href="{{ route('admin.contacts.index') }}"><i class="fas fa-envelope"></i> Contact Submissions</a></li>
                <li><a href="{{ route('admin.newsletter.index') }}"><i class="fas fa-mail-bulk"></i> Newsletter</a></li>
                <li><a href="{{ route('admin.analytics.index') }}"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            </ul>
        </nav>

        <!-- Admin Content -->
        <div class="admin-content">
            <!-- Admin Header -->
            <header class="admin-header">
                <div class="header-content">
                    <button class="sidebar-toggle"><i class="fas fa-bars"></i></button>
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <div class="header-actions">
                        <a href="{{ route('website.home') }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Admin Main Content -->
            <main class="admin-main">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Admin Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Admin sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
        });
    </script>
    
    @stack('admin-scripts')

    <style>
        .admin-body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #34495e;
            border-bottom: 1px solid #4a5f7a;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid #34495e;
            transition: background 0.3s;
        }
        
        .sidebar-menu li a:hover {
            background: #34495e;
        }
        
        .admin-content {
            flex: 1;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .admin-main {
            padding: 30px;
        }
        
        .sidebar-collapsed .admin-sidebar {
            width: 70px;
        }
        
        .sidebar-collapsed .sidebar-header h4,
        .sidebar-collapsed .sidebar-menu li a span {
            display: none;
        }
    </style>
</body>
</html>