<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 70px;
            --primary-color: #4361ee;
            --secondary-color: #805dca;
            --bg-color: #f8f9fa;
            --sidebar-bg: #ffffff;
            --sidebar-color: #3b3f5c;
            --sidebar-active-bg: rgba(67, 97, 238, 0.08);
            --sidebar-active-color: #4361ee;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            height: var(--header-height);
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f1f2f3;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 1px;
        }

        .sidebar-menu {
            padding: 20px 0;
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }

        .menu-label {
            padding: 10px 25px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #afb2bb;
            letter-spacing: 1px;
        }

        .nav-link {
            padding: 12px 25px;
            color: var(--sidebar-color);
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link i {
            width: 20px;
            margin-right: 15px;
            font-size: 18px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-color);
        }

        .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-color);
            border-left-color: var(--primary-color);
        }

        /* Main Content Styling */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        header {
            height: var(--header-height);
            background: #fff;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .admin-content {
            padding: 30px;
            flex: 1;
        }

        footer {
            padding: 20px 30px;
            background: #fff;
            border-top: 1px solid #f1f2f3;
            text-align: center;
            color: #888ea8;
            font-size: 14px;
        }

        /* Profile Dropdown */
        .profile-dropdown img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        @media (max-width: 992px) {
            #sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                left: 0;
            }
            #main-content {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    <!-- Main Content -->
    <div id="main-content">
        <!-- Header -->
        @include('admin.partials.header')

        <!-- Page Content -->
        <main class="admin-content">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('admin.partials.footer')
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Toggle Sidebar for Mobile
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
