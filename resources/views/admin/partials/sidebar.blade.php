<aside id="sidebar">
    <div class="sidebar-header">
        <h4>SECURE<span>DOCS</span></h4>
    </div>

    <div class="sidebar-menu">
        <p class="menu-label">Main</p>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.users.view') }}" class="nav-link {{ request()->routeIs('admin.users.view') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>
        </ul>

        <p class="menu-label">Files & Storage</p>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.documents.view') }}" class="nav-link">
                    <i class="fas fa-folder"></i>
                    <span>All Documents</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Recent Documents</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Storage Stats</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
