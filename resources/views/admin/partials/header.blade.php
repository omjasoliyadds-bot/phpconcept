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
        <div class="me-3">
            <a href="#" class="position-relative text-decoration-none" id="notificationBell" data-bs-toggle="modal"
                data-bs-target="#notificationModal">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="fas fa-bell text-dark"></i>
                </div>
                <span id="notificationCount"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white"
                    style="font-size: 10px; display: none;">
                    0
                </span>
            </a>
        </div>

        <!-- User Profile -->
        <div class="dropdown profile-dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button"
                data-bs-toggle="dropdown">
                <div class="me-2 text-end d-none d-sm-block">
                    <p class="mb-0 fw-bold text-dark" style="font-size: 13px;">{{ auth()->user()->name }}</p>
                    <p class="mb-0 text-muted" style="font-size: 11px;">Administrator</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4361ee&color=fff"
                    alt="Profile" class="rounded-circle" style="width: 40px; height: 40px;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item py-2" href="{{ route('admin.profile') }}"><i
                            class="far fa-user me-2 text-muted"></i> My Profile</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item py-2 text-danger logout-btn" href="javascript:void(0)"
                        style="cursor: pointer;">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Notification Modal -->
<div class="modal fade right" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="notificationModalLabel">
                    <i class="fas fa-bell text-primary me-2"></i>
                    <span class="badge bg-warning ms-2 rounded-pill fs-6" id="allNotifications">Latest Notifications</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background-color: white;">
                <div id="notificationList" class="list-group list-group-flush">
                    <div class="p-5 text-center text-muted">
                        <i class="far fa-bell-slash fs-1 mb-3 text-light"></i>
                        <p class="mb-0 mb-3 fs-5">No notifications</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top d-flex justify-content-between px-3 py-2">

                <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary">
                    Mark all as read
                </button>

                <a href="{{ route('admin.notifications') }}" class="btn btn-sm btn-primary">
                    View All
                </a>

            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            loadLatestNotifications();

            // Refresh notifications when modal opens
            $('#notificationModal').on('shown.bs.modal', function () {
                loadLatestNotifications();
            });

            $('#markAllReadBtn').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.notifications.mark_read') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status) {
                            loadLatestNotifications();
                            Swal.fire({
                                icon: 'success',
                                title: 'All marked read',
                                timer: 1000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to mark notifications as read. Please try again.'
                        });
                    }
                });
            });

            $('#notificationList').on('click', '.notification-item', function (e) {
                e.preventDefault();
                const notificationId = $(this).data('id');

                $.ajax({
                    url: `{{ url('admin/notifications') }}/${notificationId}/mark-as-read`,
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status) {
                            loadLatestNotifications();
                        }
                    }
                });
            });

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
        function loadLatestNotifications() {
            $.ajax({
                url: "{{ route('admin.notifications.data') }}",
                method: 'GET',
                success: function (response) {
                    let html = '';
                    let notifications = response.notifications;

                    if (notifications.length > 0) {

                        notifications.forEach(notification => {
                            let data = notification.data;
                            let actionText = '';
                            let nameText = '';
                            let iconClass = 'fas fa-bell';
                            let performer = data.uploaded_by || data.performed_by || 'User';

                            if (notification.type.includes('document_upload')) {
                                actionText = 'uploaded file';
                                nameText = data.document_name;
                                iconClass = 'fas fa-file-upload text-success';
                            } else if (notification.type.includes('document_delete')) {
                                actionText = 'deleted file';
                                nameText = data.document_name;
                                iconClass = 'fas fa-trash-alt text-danger';
                            } else if (notification.type.includes('folder_create')) {
                                actionText = 'created folder';
                                nameText = data.folder_name;
                                iconClass = 'fas fa-folder-plus text-primary';
                            } else if (notification.type.includes('folder_delete')) {
                                actionText = 'deleted folder';
                                nameText = data.folder_name;
                                iconClass = 'fas fa-folder-minus text-danger';
                            } else {
                                actionText = 'performed action';
                                nameText = data.document_name || data.folder_name || '';
                            }

                            html += `
                                    <a href="#" class="list-group-item list-group-item-action border-0 border-bottom py-3 notification-item ${notification.read_at ? 'bg-white' : 'bg-primary bg-opacity-10'}" data-id="${notification.id}" style="transition: all 0.2s;">
                                        <div class="d-flex align-items-start">

                                            <div class="me-3 bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                <i class="${iconClass} fs-5"></i>
                                            </div>

                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-semibold text-dark text-truncate mb-1">
                                                    ${performer}
                                                </div>
                                                <div class="small text-muted mb-2 text-wrap">
                                                    ${actionText} <b class="text-dark">${nameText}</b>
                                                </div>
                                                <div class="text-secondary d-flex align-items-center" style="font-size: 0.75rem;">
                                                    <i class="far fa-clock me-1"></i> ${timeAgo(notification.created_at)}
                                                </div>
                                            </div>

                                            ${notification.read_at ? '' : '<div class="ms-2 mt-2"><span class="bg-primary rounded-circle d-block shadow-sm" style="width: 10px; height: 10px;"></span></div>'}

                                        </div>
                                    </a>
                                `;
                        });

                    } else {
                        html = `
                                <div class="p-5 text-center text-muted">
                                    <i class="far fa-bell-slash fs-1 mb-3 text-light"></i>
                                    <p class="mb-0 fs-5">No notifications</p>
                                </div>`;
                    }

                    $('#notificationList').html(html);
                    let unreadCount = response.unreadCount;

                    if (unreadCount > 0) {
                        $('#notificationCount').text(unreadCount).show();
                    } else {
                        $('#notificationCount').hide();
                    }
                }
            });
        }

        function timeAgo(datetime) {
            const now = new Date();
            const past = new Date(datetime);
            const diff = Math.floor((now - past) / 1000);

            if (diff < 60) return "Just now";
            if (diff < 3600) return Math.floor(diff / 60) + " min ago";
            if (diff < 86400) return Math.floor(diff / 3600) + " hr ago";
            return Math.floor(diff / 86400) + " days ago";
        }
    </script>
@endpush