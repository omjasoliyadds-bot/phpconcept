@extends('user.layouts.user')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 d-flex align-items-center">
                <i class="fa fa-folder-open text-warning me-2"></i>
                <span id="currentFolderName">{{ $folder->name }}</span>
                <small class="text-muted ms-3 fs-6" id="currentFolderSize">(Calculating...)</small>
                <button class="btn btn-sm btn-link text-info ms-2" id="renameCurrentFolderBtn" data-id="{{ $folder->id }}"
                    data-name="{{ $folder->name }}">
                    <i class="fa fa-edit"></i>
                </button>
            </h4>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('explorer.index') }}">Explorer</a></li>
                    <li class="breadcrumb-item active">{{ $folder->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#folderModal">
                <i class="fa fa-folder-plus me-2"></i> New Subfolder
            </button>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fa fa-upload me-2"></i> Upload File
            </button>
            <a href="{{ route('explorer.index') }}" class="btn btn-light shadow-sm">
                <i class="fa fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <div class="profile-card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 30%">Name</th>
                        <th>Type</th>
                        <th>Subfolders</th>
                        <th>Total Size</th>
                        <th>Created At</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>

                <tbody id="filesList">
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="spinner-border text-primary opacity-50" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection

<!-- Create Folder Modal -->
<div class="modal fade" id="folderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fa fa-folder-plus text-primary me-2"></i> Create New Subfolder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFolderForm" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $folder->id }}">
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Folder Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa fa-folder text-muted"></i>
                            </span>
                            <input type="text" name="name" class="form-control border-start-0"
                                placeholder="Enter folder name" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Upload Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="document" class="form-control" required>
                    </div>
                    <p class="text-muted small">File will be uploaded to: <strong>{{ $folder->name }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Folder Modal -->
<div class="modal fade" id="renameFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fa fa-edit text-primary me-2"></i>
                    Rename Folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renameFolderForm">
                @csrf
                <div class="modal-body pt-2">
                    <input type="hidden" name="id" id="folder_id" value="{{ $folder->id }}">
                    <div class="mb-3">
                        <label class="form-label fw-medium">New Folder Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa fa-folder text-muted"></i>
                            </span>
                            <input type="text" name="name" class="form-control border-start-0"
                                value="{{ $folder->name }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Document Modal -->
<div class="modal fade" id="renameDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fa fa-edit text-info me-2"></i>
                    Rename Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renameDocForm">
                @csrf
                <div class="modal-body pt-2">
                    <input type="hidden" name="id" id="doc_id">
                    <div class="mb-3">
                        <label class="form-label fw-medium">New Document Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa fa-file text-muted"></i>
                            </span>
                            <input type="text" name="name" class="form-control border-start-0"
                                placeholder="Enter new name" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')

    <script>

        $(document).ready(function () {

            loadFolderContents();
            function loadFolderContents() {
                let folderId = "{{ $folder->id }}";
                $.ajax({
                    url: "{{ route('api.folders.files', $folder->id) }}",
                    type: "GET",
                    success: function (response) {
                        let html = '';

                        if (response.status) {
                            // Render Subfolders
                            if (response.subfolders && response.subfolders.length > 0) {
                                response.subfolders.forEach(sub => {
                                    let date = new Date(sub.created_at).toLocaleDateString();
                                    let totalSize = sub.total_size ? (sub.total_size / (1024 * 1024)).toFixed(2) + ' MB' : '0.00 MB';
                                    html += `
                                                <tr>
                                                    <td class="ps-4" onclick="window.location.href='/folders/${sub.id}/files'" style="cursor:pointer;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3" style="width:35px;height:35px;background:#fff8e1;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                                                  <i class="fa fa-folder text-warning"></i>
                                                            </div>
                                                            <span class="fw-medium">${sub.name}</span>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-light text-dark">Folder</span></td>
                                                    <td><span class="badge bg-light text-dark">${sub.subfolder_count} Subfolders</span></td>
                                                    <td>${totalSize}</td>
                                                    <td>${date}</td>
                                                    <td class="text-end pe-4">
                                                        <button class="btn btn-sm btn-outline-danger deleteFolderBtn" data-id="${sub.id}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `;
                                });
                            }

                            // Render Files
                            if (response.files.length > 0) {
                                response.files.forEach(file => {
                                    let date = new Date(file.created_at).toLocaleDateString();
                                    let size = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                    let downloadUrl = "{{ route('documents.download', ':id', false) }}".replace(':id', file.id);
                                    html += `
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-3" style="width:35px;height:35px;background:#f3f4f6;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                                                    <i class="fa ${file.icon}"></i>
                                                                </div>
                                                                <span class="fw-medium">${file.name}</span>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-light text-dark text-uppercase">${file.extension}</span></td>
                                                        <td>-</td>
                                                        <td>${size}</td>
                                                        <td>${date}</td>
                                                        <td class="text-end pe-4">
                                                            <button class="btn btn-sm btn-outline-info renameDocBtn" 
                                                                    data-id="${file.id}" 
                                                                    data-name="${file.name.split('.').slice(0, -1).join('.')}">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <a href="${downloadUrl}" class="btn btn-sm btn-outline-success">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                            <a href="/documents/${file.id}/manage-access" class="btn btn-sm btn-outline-primary">
                                                                <i class="fa fa-share-alt"></i>
                                                            </a>

                                                            <button class="btn btn-sm btn-outline-danger deleteDocBtn" data-id="${file.id}">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                    </td>
                                                </tr>
                                            `;
                                });
                            }

                            if ((!response.subfolders || response.subfolders.length === 0) && response.files.length === 0) {
                                html = '<tr><td colspan="6" class="text-center py-5 text-muted">This folder is empty</td></tr>';
                            }

                            if (response.totalSize !== undefined) {
                                let size = (response.totalSize / 1024 / 1024).toFixed(2) + ' MB';
                                $('#currentFolderSize').text(`(${size})`);
                            }
                            $('#filesList').html(html);
                        }
                    }
                });
            }

            // Subfolder Creation
            $('#createFolderForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('api.folders.store') }}",
                    method: "POST",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status) {
                            $('#folderModal').modal('hide');
                            $('#createFolderForm')[0].reset();
                            window.showSuccess(response.message);
                            loadFolderContents();
                        } else {
                            window.showErrors(response);
                        }
                    }
                });
            });

            // File Upload
            $('#uploadForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('api.documents.upload') }}",
                    method: "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            $('#uploadModal').modal('hide');
                            $('#uploadForm')[0].reset();
                            window.showSuccess(response.message);
                            loadFolderContents();
                        } else {
                            window.showErrors(response);
                        }
                    }
                });
            });

            // Delete Folder
            $(document).on('click', '.deleteFolderBtn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Subfolder?',
                    text: "All contents will be affected!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('api.folders.remove', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                if (response.status) {
                                    window.showSuccess(response.message);
                                    loadFolderContents();
                                }
                            }
                        });
                    }
                });
            });

            // Delete Document
            $(document).on('click', '.deleteDocBtn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Document?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('api.documents.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                if (response.status) {
                                    window.showSuccess(response.message);
                                    loadFolderContents();
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#renameCurrentFolderBtn', function () {
                $('#renameFolderModal').modal('show');
            });

            $('#renameFolderForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#folder_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('api.folders.update', ':id') }}".replace(':id', id),
                    method: "PUT",
                    data: formData,
                    success: function (response) {
                             if (response.status) {
                                 $('#renameFolderModal').modal('hide');
                                 $('#currentFolderName').text(response.folder.name);
                                 $('.breadcrumb-item.active').text(response.folder.name);
                                 $('#renameCurrentFolderBtn').data('name', response.folder.name);
                                 window.showSuccess(response.message);
                             } else {
                                 window.showErrors(response);
                             }
                    }
                });
            });

            $(document).on('click', '.renameDocBtn', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#doc_id').val(id);
                $('#renameDocForm input[name="name"]').val(name);
                $('#renameDocModal').modal('show');
            });

            $('#renameDocForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#doc_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('api.documents.update', ':id') }}".replace(':id', id),
                    method: "PUT",
                    data: formData,
                    success: function (response) {
                             if (response.status) {
                                 $('#renameDocModal').modal('hide');
                                 loadFolderContents();
                                 window.showSuccess(response.message);
                             } else {
                                 window.showErrors(response);
                             }
                    }
                });
            });

        });
    </script>
@endpush