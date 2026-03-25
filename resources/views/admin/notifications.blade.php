@extends('admin.layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Notifications</h4>
            <p class="text-muted mb-0">All notification entries with read/unread states.</p>
        </div>
        <button id="markAllReadPageBtn" class="btn btn-sm btn-primary">Mark All as Read</button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush" id="allNotificationList">
                <div class="p-5 text-center text-muted" id="noNotifications">
                    <i class="far fa-bell-slash fs-1 mb-3 text-light"></i>
                    <p class="mb-0 fs-5">No notifications</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            loadAllNotifications();

            $('#markAllReadPageBtn').on('click', function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('admin.notifications.mark_read') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status) {
                            loadAllNotifications();
                            Swal.fire({
                                icon: 'success',
                                title: 'All notifications marked as read.',
                                timer: 1200,
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

            $('#allNotificationList').on('click', '.notification-item', function (e) {
                e.preventDefault();
                let notificationId = $(this).data('id');

                $.ajax({
                    url: `{{ url('admin/notifications') }}/${notificationId}/mark-as-read`,
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        loadAllNotifications();
                    }
                });
            });
        });

        function loadAllNotifications() {
            $.ajax({
                url: "{{ route('admin.notifications.data') }}?all=1",
                method: 'GET',
                success: function (response) {
                    let html = '';
                    let notifications = response.notifications;

                    if (notifications.length > 0) {
                        $('#noNotifications').hide();

                        notifications.forEach(function (notification) {
                            let data = notification.data || {};
                            let statusClass = notification.read_at ? 'bg-white' : 'bg-primary bg-opacity-10';
                            let indicator = notification.read_at ? '' : '<div class="ms-2 mt-2"><span class="bg-primary rounded-circle d-block shadow-sm" style="width: 10px; height: 10px;"></span></div>';

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
                            <a href="#" class="list-group-item list-group-item-action border-0 border-bottom py-3 notification-item ${statusClass}" data-id="${notification.id}">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="${iconClass} fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-dark text-truncate mb-1">${performer}</div>
                                        <div class="small text-muted mb-2 text-wrap">${actionText} <b class="text-dark">${nameText}</b></div>
                                        <div class="text-secondary d-flex align-items-center" style="font-size: 0.75rem;"><i class="far fa-clock me-1"></i> ${timeAgo(notification.created_at)}</div>
                                    </div>
                                    ${indicator}
                                </div>
                            </a>\n`;
                        });

                        $('#allNotificationList').html(html);
                    } else {
                        $('#allNotificationList').html('');
                        $('#noNotifications').show();
                    }

                    if (response.unreadCount > 0) {
                        $('#notificationCount').text(response.unreadCount).show();
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
