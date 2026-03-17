@extends('user.layouts.user')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">File Explorer</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#folderModal">
                <i class="fa fa-folder-plus me-2"></i> New Folder
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fa fa-upload me-2"></i> Upload File
            </button>
        </div>
    </div>

    <div class="profile-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 30%">Name</th>
                        <th>Type</th>
                        <th>Subfolders</th>
                        <th>Total Size</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="explorerTableBody">
                    <!-- Data will be loaded via AJAX -->
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div class="modal fade" id="folderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-sm border-0 rounded-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fa fa-folder-plus text-primary me-2"></i> Create New Folder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createFolderForm" method="POST">
                    @csrf
                    <div class="modal-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Folder Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa fa-folder text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0" placeholder="Enter folder name" required>
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

    <!-- Rename Folder Modal -->
    <div class="modal fade" id="renameFolderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-sm border-0 rounded-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fa fa-edit text-primary me-2"></i> Rename Folder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="renameFolderForm">
                    @csrf
                    <div class="modal-body pt-2">
                        <input type="hidden" name="id" id="folder_id">
                        <div class="mb-3">
                            <label class="form-label fw-medium">New Folder Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa fa-folder text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0" placeholder="Enter new folder name" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename Document Modal -->
    <div class="modal fade" id="renameDocModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rename Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="renameDocForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="doc_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select File</label>
                            <input type="file" name="document" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Folder (Optional)</label>
                            <select name="folder_id" id="uploadFolderSelect" class="form-select">
                                <option value="">No Folder (Root)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            loadExplorer();

            // Load Explorer Data
            function loadExplorer() {
                $.ajax({
                    url: "{{ route('api.folders.explorer') }}",
                    method: "GET",
                    success: function (response) {
                        if (response.status) {
                            renderTable(response.folders, response.files);
                        }
                    }
                });

                // Fetch all folders for dropdown
                $.ajax({
                    url: "{{ route('api.folders.all') }}",
                    method: "GET",
                    success: function (response) {
                        if (response.status) {
                            updateFolderDropdown(response.folders);
                        }
                    }
                });
            }

            function renderTable(folders, files) {
                let html = '';
                
                // Folders
                if (folders.length > 0) {
                    // html += `<tr><td colspan="5" class="bg-light fw-bold py-2"><i class="fa fa-folder text-warning me-2"></i> Folders</td></tr>`;
                    folders.forEach(folder => {
                        let date = new Date(folder.created_at).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit', year: 'numeric'
                        });
                        let totalSize = folder.total_size ? (folder.total_size / (1024 * 1024)).toFixed(2) + ' MB' : '0.00 MB';
                        html += `
                            <tr>
                                <td onclick="window.location.href='/folders/${folder.id}/files'" style="cursor:pointer;">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3" style="width:35px;height:35px;background:#fff8e1;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                            <i class="fa fa-folder text-warning"></i>
                                        </div>
                                        <span class="fw-medium">${folder.name}</span>
                                    </div>
                                </td>
                                <td> <span class="badge bg-light text-dark">Folder</span></td>
                                <td><span class="badge bg-light text-dark">${folder.subfolder_count} Subfolders</span></td>
                                <td>${totalSize}</td>
                                <td>${date}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info renameFolderBtn" data-id="${folder.id}" data-name="${folder.name}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger deleteFolderBtn" data-id="${folder.id}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                // Files
                if (files.length > 0) {
                    // html += `<tr><td colspan="5" class="bg-light fw-bold py-2"><i class="fa fa-file text-primary me-2"></i> Files</td></tr>`;
                    files.forEach(file => {
                        let date = new Date(file.created_at).toLocaleDateString('en-US', {
                            month: 'short', day: '2-digit', year: 'numeric'
                        });
                        let size = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        html += `
                            <tr>
                                <td>
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
                                <td class="text-end">
                                    <a href="/api/documents/${file.id}/download" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-info renameDocBtn" data-id="${file.id}" data-name="${file.name.split('.').slice(0, -1).join('.')}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger deleteDocBtn" data-id="${file.id}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                if (folders.length === 0 && files.length === 0) {
                    html = `<tr><td colspan="6" class="text-center py-5 text-muted">No folders or files found.</td></tr>`;
                }

                $('#explorerTableBody').html(html);
            }

            function updateFolderDropdown(folders) {
                let options = '<option value="">No Folder (Root)</option>';
                folders.forEach(folder => {
                    options += `<option value="${folder.id}">${folder.name}</option>`;
                });
                $('#uploadFolderSelect').html(options);
            }

            // Folder Creation
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
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1500, showConfirmButton: false });
                            loadExplorer();
                        }
                    }
                });
            });

            // Folder Rename
            $(document).on('click', '.renameFolderBtn', function () {
                $('#folder_id').val($(this).data('id'));
                $('#renameFolderForm input[name="name"]').val($(this).data('name'));
                $('#renameFolderModal').modal('show');
            });

            $('#renameFolderForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#folder_id').val();
                $.ajax({
                    url: "{{ route('api.folders.update', ':id') }}".replace(':id', id),
                    method: "PUT",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status) {
                            $('#renameFolderModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Renamed', text: response.message, timer: 1500, showConfirmButton: false });
                            loadExplorer();
                        }
                    }
                });
            });

            // Folder Delete
            $(document).on('click', '.deleteFolderBtn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Folder?',
                    text: "All files inside will also be affected if any.",
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
                                    Swal.fire('Deleted!', response.message, 'success');
                                    loadExplorer();
                                }
                            }
                        });
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
                            Swal.fire({ icon: 'success', title: 'Uploaded', text: response.message, timer: 1500, showConfirmButton: false });
                            loadExplorer();
                        }
                    }
                });
            });

            // Document Rename
            $(document).on('click', '.renameDocBtn', function () {
                $('#doc_id').val($(this).data('id'));
                $('#renameDocForm input[name="name"]').val($(this).data('name'));
                $('#renameDocModal').modal('show');
            });

            $('#renameDocForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#doc_id').val();
                $.ajax({
                    url: "{{ route('api.documents.update', ':id') }}".replace(':id', id),
                    method: "PUT",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status) {
                            $('#renameDocModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Renamed', text: response.message, timer: 1500, showConfirmButton: false });
                            loadExplorer();
                        }
                    }
                });
            });

            // Document Delete
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
                                    Swal.fire('Deleted!', response.message, 'success');
                                    loadExplorer();
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
