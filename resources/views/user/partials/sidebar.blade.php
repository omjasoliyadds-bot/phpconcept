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
            <li><a href="{{ route('user.share-with-me') }}"><i class="fa fa-share-nodes me-2"></i> Shared With Me</a></li>
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

    <!-- Storage Monitoring Widget -->
    <div class="sidebar-storage">
        <div class="storage-title">Storage Usage</div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 45%;" aria-valuenow="45" aria-valuemin="0"
                aria-valuemax="100"></div>
        </div>
        <div class="storage-text">
            <span>4.5 GB used</span>
            <span>10 GB total</span>
        </div>
    </div>

</div>