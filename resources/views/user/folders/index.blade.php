@extends('layouts.user.user')

@section('content')
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#folderModal">
        <i class="fa fa-folder-plus me-2"></i> New Folder
    </button>

    <div class="modal fade" id="folderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-sm border-0 rounded-3">

                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fa fa-folder-plus text-primary me-2"></i>
                        Create New Folder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Form -->
                <form id="createFolderForm" method="POST">
                    @csrf

                    <div class="modal-body pt-2">
                        <div class="text-center mb-3">
                            <div
                                style="width:60px;height:60px;background:#f1f5f9;border-radius:12px;                                                                                  display:flex;align-items:center;justify-content:center;margin:auto;">
                                <i class="fa fa-folder text-warning fs-3"></i>
                            </div>
                        </div>

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

                    <!-- Footer -->
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-check me-1"></i>
                            Create
                        </button>
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
                        <input type="hidden" name="id" id="folder_id">
                        <div class="mb-3">
                            <label class="form-label fw-medium">New Folder Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa fa-folder text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0"
                                    placeholder="Enter new folder name" required>
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
    <div class="container my-5">
        <div class="table-responsive mt-4">
            <table class="table align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Owner</th>
                        <th>Date Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="allFolders">
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
                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('folders.store') }}",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status) {
                            $('#createFolderForm')[0].reset();
                            $('#folderModal').modal('hide');
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Folder created successfully',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            allFolderLoad();
                        } else {
                            // Show validation errors if any
                            alert(response.message || 'Error creating folder');
                        }
                    },
                    error: function (xhr) {
                        alert('Something went wrong. Please try again.');
                    }
                })
            })

            $(document).on('click', '.renameBtn', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#folder_id').val(id);
                $('#renameFolderForm input[name="name"]').val(name);
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
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            allFolderLoad();
                        } else {
                            alert(response.message || 'Error renaming folder');
                        }
                    },
                    error: function (xhr) {
                        alert('Something went wrong. Please try again.');
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function (e) {
                e.preventDefault();
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
                            url: "{{ route('folders.remove', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        allFolderLoad();
                                    });
                                }
                            }
                        });
                    }
                });
            })
        })

        function allFolderLoad() {

            $.ajax({
                url: "{{ route('folders.all') }}",
                method: "GET",
                success: function (response) {
                    let html = '';
                    if (response.status) {
                        response.folders.forEach(folder => {
                            let date = new Date(folder.created_at).toLocaleString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            let totalSize = folder.total_size ? (folder.total_size / (1024 * 1024)).toFixed(2) + ' MB' : '0.00 MB';
                            html += `
                                <tr>
                                        <td onclick="window.location.href='/folders/${folder.id}/files'" style="cursor:pointer;">
                                        <i class="fa fa-folder text-warning me-2"></i>
                                            ${folder.name}
                                        </td>
                                        <td>${totalSize}</td>
                                        <td>me</td>
                                            <td>${date}</td>
                                            <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-info renameBtn" data-id="${folder.id}" data-name="${folder.name}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${folder.id}"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                `;
                        });
                        $('#allFolders').html(html);
                    }
                }
            });
        }

        function openFolder(id) {
            console.log("Opening folder: " + id);
        }
    </script>
@endpush