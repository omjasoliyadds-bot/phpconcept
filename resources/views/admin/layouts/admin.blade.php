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

    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* ================= SIDEBAR ================= */

        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            z-index: 1000;
            transition: all .3s ease;
        }

        .sidebar-header {
            height: 70px;
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .sidebar-header h4 {
            font-weight: 700;
            color: #4361ee;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .menu-label {
            padding: 10px 25px;
            font-size: 11px;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
        }

        .nav-link {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: #3b3f5c;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: .2s;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: rgba(67, 97, 238, .08);
            color: #4361ee;
        }

        .nav-link.active {
            background: rgba(67, 97, 238, .08);
            color: #4361ee;
            border-left-color: #4361ee;
        }

        /* ================= MAIN CONTENT ================= */

        #main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .3s ease;
        }

        header {
            height: 70px;
            background: #fff;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
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
            border-top: 1px solid #eee;
            text-align: center;
            color: #888ea8;
            font-size: 14px;
        }

        /* ================= PROFILE ================= */

        .profile-dropdown img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* ================= MOBILE ================= */

        @media(max-width:992px) {

            #sidebar {
                left: -260px;
            }

            #sidebar.active {
                left: 0;
            }

            #main-content {
                margin-left: 0;
            }

        }

        /* ================= OVERLAY ================= */

        #overlay {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .4);
            top: 0;
            left: 0;
            z-index: 999;
        }

        #overlay.active {
            display: block;
        }
    </style>

    @yield('styles')

</head>

<body>

    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    <!-- Overlay -->
    <div id="overlay"></div>

    <!-- Main Content -->
    <div id="main-content">

        @include('admin.partials.header')

        <main class="admin-content">
            @yield('content')
        </main>

        @include('admin.partials.footer')

    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>

        $(document).ready(function () {

            $('#sidebarCollapse').click(function () {
                $('#sidebar').toggleClass('active');
                $('#overlay').toggleClass('active');
            });

            $('#overlay').click(function () {
                $('#sidebar').removeClass('active');
                $('#overlay').removeClass('active');
            });

        });

        window.formatBytes = function(bytes, precision = 2) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const dm = precision < 0 ? 0 : precision;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        };
    </script>
    @stack('scripts')
    @yield('scripts')

</body>

</html>