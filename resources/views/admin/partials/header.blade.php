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

        <!-- User Profile -->
        <div class="dropdown profile-dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button"
                data-bs-toggle="dropdown">
                <div class="me-2 text-end d-none d-sm-block">
                    <p class="mb-0 fw-bold text-dark" style="font-size: 13px;">{{ auth()->user()->name }}</p>
                    <p class="mb-0 text-muted" style="font-size: 11px;">Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4361ee&color=fff"
                    alt="Profile">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="far fa-user me-2"></i> My Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-danger logout-btn" href="javascript:void(0)" style="cursor: pointer;">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        $(document).ready(function () {
            console.log('Logout script loaded');
            $('.logout-btn').on('click', function (e) {
                console.log('Logout button clicked');
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will be logged out!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4361ee',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('api.admin.logout') }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Logged Out',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        window.location.href = "{{ route('login') }}";
                                    });
                                }
                            },
                            error: function (xhr) {
                                console.error('Logout failed');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong during logout. Please try again.',
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush