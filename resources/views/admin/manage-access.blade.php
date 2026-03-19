@extends('admin.layouts.admin')

@section('title', 'Manage Access')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 5px;
        }

        .card-sharing {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Admin Access Control Override</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.documents.view') }}">Documents</a></li>
                        <li class="breadcrumb-item active">Manage Access: {{ $document->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.documents.view') }}" class="btn btn-light shadow-sm">
                <i class="fa fa-arrow-left me-2"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card card-sharing border-0 p-4 mb-4">
                    <h5 class="fw-bold mb-4 text-primary">
                        <i class="fa fa-user-shield me-2"></i> Grant Manual Access
                    </h5>
                    <form id="shareForm">
                        @csrf
                        <input type="hidden" id="share_document_id" value="{{ $document->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Users</label>
                            <select id="share_users" class="form-select select2" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Set Permissions</label>
                            <select id="permission" class="form-select select2" multiple>
                                <option value="view" selected>View</option>
                                <option value="edit">Edit</option>
                                <option value="download">Download</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="shareBtn">
                            Update Permissions
                        </button>
                    </form>
                </div>

                <div class="card card-sharing border-0 p-4">
                    <h5 class="fw-bold mb-3">File Information</h5>
                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                        <div class="me-3 fs-3 text-info">
                            <i class="fa {{ $document->icon }}"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-truncate" style="max-width: 250px;">{{ $document->name }}</div>
                            <div class="small text-muted">Owned by: <strong>{{ $document->user->name }}</strong></div>
                            <div class="small text-muted">{{ number_format($document->size / 1024 / 1024, 2) }} MB • Created
                                {{ $document->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-sharing border-0 p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="fa fa-users text-info me-2"></i> Current Access List
                    </h5>
                    <div id="sharedUsersList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary opacity-50" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                width: '100%',
                placeholder: '-- Select --'
            });

            const docId = "{{ $document->id }}";
            let currentSharedUsers = [];

            loadSharedUsers(docId);

            // Auto-select permissions when user is selected
            $('#share_users').on('change', function () {
                let selectedUsers = $(this).val();
                if (selectedUsers && selectedUsers.length === 1) {
                    let userId = selectedUsers[0];
                    let existing = currentSharedUsers.find(item => item.user.id == userId);
                    if (existing) {
                        $('#permission').val(existing.permissions).trigger('change');
                    } else {
                        $('#permission').val(['view']).trigger('change');
                    }
                }
            });

            // Handle Share Form
            $('#shareForm').on('submit', function (e) {
                e.preventDefault();
                let userIds = $('#share_users').val();
                let permission = $('#permission').val();

                if (!userIds || userIds.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select at least one user.'
                    });
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.documents.share', ':id') }}".replace(':id', docId),
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        user_ids: userIds,
                        permission: permission
                    },
                    success: function (response) {
                         if (response.status) {
                             Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                             loadSharedUsers(docId);
                             $('#share_users').val(null).trigger('change');
                         } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update permissions'
                            });
                         }
                    }
                });
            });

            function loadSharedUsers(docId) {
                $.ajax({
                    url: "{{ route('admin.documents.permissions', ':id') }}".replace(':id', docId),
                    method: "GET",
                    success: function (response) {
                        let html = '';
                        if (response.status && response.data.length > 0) {
                            currentSharedUsers = response.data;
                            response.data.forEach(function (item) {
                                html += `
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-3 border shadow-sm transition-hover">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-3">
                                                    ${item.user.name.charAt(0).toUpperCase()}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">${item.user.name}</div>
                                                    <div class="small text-muted">${item.user.email}</div>
                                                    <div class="mt-2">
                                                        ${item.permissions.map(p => {
                                                            let badgeClass = p === 'edit' ? 'bg-warning' : (p === 'download' ? 'bg-success' : 'bg-info');
                                                            return `<span class="badge ${badgeClass} text-white me-1 text-capitalize">${p}</span>`;
                                                        }).join('')}
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-outline-danger btn-sm px-3 rounded-pill revokeBtn" data-user-id="${item.user.id}">
                                                <i class="fa fa-user-minus me-1"></i> Revoke
                                            </button>
                                        </div>
                                    `;
                            });
                        } else {
                            currentSharedUsers = [];
                            html = `
                                    <div class="text-center py-5">
                                        <i class="fa fa-user-shield text-muted fs-1 mb-3 opacity-25"></i>
                                        <p class="text-muted">No specific users have access to this file yet.</p>
                                    </div>
                                `;
                        }
                        $('#sharedUsersList').html(html);
                    }
                });
            }

            $(document).on('click', '.revokeBtn', function () {
                let userId = $(this).data('user-id');

                Swal.fire({
                    title: 'Revoke Access?',
                    text: "User will no longer be able to access this file.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, revoke it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.documents.revoke_permission', ':id') }}".replace(':id', docId),
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                user_id: userId
                            },
                            success: function (response) {
                                 if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                     loadSharedUsers(docId);
                                 }
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .avatar-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .transition-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s ease;
        }
    </style>
@endsection
