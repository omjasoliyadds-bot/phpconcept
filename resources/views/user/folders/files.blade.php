@extends('layouts.user.user')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 d-flex align-items-center">
            <i class="fa fa-folder-open text-warning me-2"></i>
            <span id="currentFolderName">{{ $folder->name }}</span>
            <small class="text-muted ms-3 fs-6" id="currentFolderSize">(Calculating...)</small>
            <button class="btn btn-sm btn-link text-info ms-2" id="renameCurrentFolderBtn" 
                    data-id="{{ $folder->id }}" data-name="{{ $folder->name }}">
                <i class="fa fa-edit"></i>
            </button>
        </h4>
        <a href="{{ route('folders.index') }}" class="btn btn-light shadow-sm">
            <i class="fa fa-arrow-left me-2"></i> Back to Folders
        </a>
    </div>

    <div class="profile-card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">File Name</th>
                        <th>File Size</th>
                        <th>Created Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>

                <tbody id="filesList">
                    <tr>
                        <td colspan="3" class="text-center py-5">
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
            let folderId = "{{ $folder->id }}";
            $.ajax({
                url: "{{ route('folders.files', $folder->id) }}",
                type: "GET",
                success: function (response) {

                    let html = '';

                    if (response.status) {
                        if (response.files.length === 0) {
                            html = '<tr><td colspan="3" class="text-center text-muted">No files found in this folder</td></tr>';
                        } else {
                            response.files.forEach(file => {
                                let date = new Date(file.created_at).toLocaleDateString();
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
                                            <td>
                                                ${(file.size / 1024 / 1024).toFixed(2)} MB
                                            </td>
                                            <td>${date}</td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-info renameDocBtn" 
                                                        data-id="${file.id}" 
                                                        data-name="${file.name.split('.').slice(0, -1).join('.')}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a href="{{ route('documents.download', ':id') }}".replace(':id', file.id) class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download me-1"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        if (response.totalSize !== undefined) {
                            let size = (response.totalSize / 1024 / 1024).toFixed(2) + ' MB';
                            $('#currentFolderSize').text(`(${size})`);
                        }
                        $('#filesList').html(html);
                    }
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
                url: "{{ route('folders.update', ':id') }}".replace(':id', id),
                method: "PUT",
                data: formData,
                success: function (response) {
                    if (response.status) {
                        $('#renameFolderModal').modal('hide');
                        $('#currentFolderName').text(response.folder.name);
                        $('#renameCurrentFolderBtn').data('name', response.folder.name);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        alert(response.message || 'Error renaming folder');
                    }
                },
                error: function (xhr) {
                    alert('Something went wrong. Please try again.');
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
                        location.reload(); // Reload to reflect name change and update icons/data if needed
                    } else {
                        alert(response.message || 'Error renaming document');
                    }
                },
                error: function (xhr) {
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    </script>
@endpush