<div class="header d-flex justify-content-between align-items-center">

    <!-- Mobile Sidebar Button -->
    <button class="btn btn-light d-lg-none sidebar-toggle-btn header-icon-btn" onclick="toggleSidebar()">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Search Section -->
    <div class="position-relative d-none d-md-block">
        <i class="fa fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input type="text" class="search-input" placeholder="Search documents, folders...">
    </div>

    <!-- Right Section -->
    <div class="d-flex align-items-center gap-3">
        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-none d-lg-block text-end me-3">
                    <div class="fw-bold small lh-1">{{ auth()->user()->name }}</div>
                    <div class="text-muted extra-small" style="font-size: 0.7rem;">Verified User</div>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=80" class="user-avatar" alt="User Avatar">
            </a>

            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 p-2" style="border-radius: 1rem; min-width: 200px;">
                <li class="px-3 py-2 border-bottom mb-2">
                    <span class="fw-bold d-block">{{ auth()->user()->name }}</span>
                    <span class="text-muted small">{{ auth()->user()->email }}</span>
                </li>
                <li>
                    <a class="dropdown-item py-2 px-3 rounded-2" href="{{ route('user.profile') }}">
                        <i class="fa-regular fa-user me-2"></i> My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 px-3 rounded-2" href="{{ route('user.profile') }}">
                        <i class="fa-regular fa-hard-drive me-2"></i> Storage Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider mx-2"></li>
                <li>
                    <a class="dropdown-item py-2 px-3 rounded-2 text-danger logout-btn" href="javascript:void(0)">
                        <i class="fa fa-arrow-right-from-bracket me-2"></i> Sign Out
                    </a>
                </li>
            </ul>
        </div>

    </div>

</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.logout-btn').on('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will be logged out!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('api.logout.user') }}",
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
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush