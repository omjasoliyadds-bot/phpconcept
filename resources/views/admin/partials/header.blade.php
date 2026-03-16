<header>
    <div class="d-flex align-items-center">
        <button type="button" id="sidebarCollapse" class="btn btn-light d-lg-none me-3">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-bar d-none d-md-block">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0" placeholder="Search...">
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center">
        <!-- Notifications -->
        <div class="dropdown me-3">
            <a class="nav-link dropdown-toggle text-muted" href="#" role="button" data-bs-toggle="dropdown">
                <i class="far fa-bell fs-5"></i>
                <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="margin-left: -5px; margin-top: -5px; padding: 3px 5px; font-size: 8px;">
                    3
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">New file uploaded</a></li>
                <li><a class="dropdown-item" href="#">System update complete</a></li>
                <li><a class="dropdown-item" href="#">User report ready</a></li>
            </ul>
        </div>

        <!-- User Profile -->
        <div class="dropdown profile-dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <div class="me-2 text-end d-none d-sm-block">
                    <p class="mb-0 fw-bold text-dark" style="font-size: 13px;">{{ auth()->user()->name }}</p>
                    <p class="mb-0 text-muted" style="font-size: 11px;">Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4361ee&color=fff" alt="Profile">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="far fa-user me-2"></i> My Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="logout-form" action="{{ route('api.logout.user') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
