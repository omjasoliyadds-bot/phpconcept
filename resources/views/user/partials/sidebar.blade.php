<div class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <i class="fas fa-cloud-bolt me-2"></i> CloudDocs
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="{{ route('user.dashboard') }}" class="{{ Route::is('user.dashboard') ? 'active' : '' }}"><i
                        class="fa fa-home me-2"></i> Dashboard</a></li>
            <li><a href="{{ route('explorer.index') }}" class="{{ Route::is('explorer.index') ? 'active' : '' }}"><i
                        class="fa fa-compass me-2"></i> File Explorer</a></li>
            <li><a href="{{ route('folders.index') }}" class="{{ Route::is('folders.index') ? 'active' : '' }}"><i
                        class="fa fa-folder-open me-2"></i> Folders</a></li>
            <li><a href="{{ route('user.share-with-me') }}" class="{{ Route::is('user.share-with-me') ? 'active' : '' }}"><i class="fa fa-share-nodes me-2"></i> Shared With Me</a></li>
            <li><a href="{{ route('user.profile') }}" class="{{ Route::is('user.profile') ? 'active' : '' }}"><i
                        class="fa fa-user-gear me-2"></i> Profile Settings</a></li>
            <li>
                <form id="headerLogoutForm" action="{{ route('api.logout.user') }}" method="POST"
                    style="display: none;">
                    @csrf
                </form>

                <a class="text-danger logout-btn" href="javascript:void(0)">
                    <i class="fa fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

</div>