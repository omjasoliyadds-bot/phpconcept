@extends('user.layouts.user')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Folders</h4>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#folderModal">
            <i class="fa fa-folder-plus me-2"></i> New Folder
        </button>
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
                                <span class="input-group-text border-end-0 text-muted">
                                    <i class="fa fa-folder text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0"
                                    placeholder="Enter folder name" maxlength="255" required>
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
                                <span class="input-group-text bg-dark border-end-0 text-muted">
                                    <i class="fa fa-folder text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0"
                                    placeholder="Enter new folder name" maxlength="255" required>
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

    <div class="profile-card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark py-3">
            <h5 class="mb-0 fw-bold text-white"><i class="fa fa-folder-tree me-2"></i> Folder Structure</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="text-white">
                    <tr>
                        <th class="ps-4">Folder Name</th>
                        <th>Type</th>
                        <th>Subfolders</th>
                        <th>Total Size</th>
                        <th>Created At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="allFolders">
                    <!-- Loaded via AJAX -->
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Loading folders...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            allFolderLoad();

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
                            $('#createFolderForm')[0].reset();
                            $('#folderModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Folder created', timer: 1500, showConfirmButton: false });
                            allFolderLoad();
                        } else {
                            window.showErrors(response);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON) {
                            window.showErrors(xhr.responseJSON);
                        } else {
                            window.showErrors({ message: 'Internal server error' });
                        }
                    }
                });
            });

            $(document).on('click', '.renameBtn', function () {
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
                            allFolderLoad();
                        } else {
                            window.showErrors(response);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON) {
                            window.showErrors(xhr.responseJSON);
                        } else {
                            window.showErrors({ message: 'Internal server error' });
                        }
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Folder?',
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
                                    Swal.fire('Deleted!', response.message, 'success');
                                    allFolderLoad();
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.deleteDocBtn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Document?',
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
                                    allFolderLoad();
                                }
                            }
                        });
                    }
                });
            });

            function allFolderLoad() {
                $.ajax({
                    url: "{{ route('api.folders.all') }}",
                    method: "GET",
                    success: function (response) {
                        if (response.status) {
                            renderFolders(response.folders);
                        }
                    }
                });
            }

            function renderFolders(folders) {
                let html = '';
                let rootFolders = folders.filter(f => !f.parent_id);

                if (rootFolders.length === 0) {
                    html = '<tr><td colspan="6" class="text-center py-4 text-muted">No folders found</td></tr>';
                } else {
                    rootFolders.forEach(folder => {
                        html += renderFolderRow(folder, folders, 0);
                    });
                }
                $('#allFolders').html(html);
            }

            function renderFolderRow(folder, allFolders, depth) {
                let date = new Date(folder.created_at).toLocaleDateString();
                let size = window.formatBytes(folder.total_size || 0);
                let subCount = folder.subfolder_count || 0;
                let padding = depth * 25;

                let row = `
                                <tr>
                                    <td class="ps-4" style="padding-left: ${padding + 24}px !important;" onclick="window.location.href='/folders/${folder.id}/files'" style="cursor:pointer;">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-folder text-warning me-2"></i>
                                            <span class="fw-medium" style="cursor:pointer;">${folder.name}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark">Folder</span></td>
                                    <td><span class="badge bg-light text-dark">${subCount} Subfolders</span></td>
                                    <td>${size}</td>
                                    <td>${date}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-info renameBtn" data-id="${folder.id}" data-name="${folder.name}"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger deleteBtn" data-id="${folder.id}"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            `;

                return row;
            }


        });
    </script>
@endpush