@extends('admin.layouts.admin')
@section('content')
    <div class="container">
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">📄 All Documents</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="documentsTable">

                        <thead class="table-dark text-center">
                            <tr>
                                <th>#</th>
                                <th>File</th>
                                <th class="text-start">Name</th>
                                <th class="text-start">Shared With</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            loadDocuments();
            $(document).on('click', '.revoke-permissions', function () {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this action!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, revoke it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let id = $(this).data('id');
                        $.ajax({
                            url: "{{ route('admin.documents.revoke', ':id') }}".replace(':id', id),
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        position: 'top-end',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 1500,
                                        showCancelButton: false
                                    }).then(() => {
                                        loadDocuments();
                                    })
                                }
                            }
                        });
                    }
                })
            });

            $(document).on('click', '.delete-document', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this action!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.documents.forced.delete', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        position: 'top-end',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 1500,
                                        showCancelButton: false
                                    }).then(() => {
                                        loadDocuments();
                                    })
                                }
                            }
                        });
                    }
                })
            });

        });

        function loadDocuments() {
            if ($.fn.DataTable.isDataTable('#documentsTable')) {
                $('#documentsTable').DataTable().destroy();
            }
            $('#documentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.documents.data') }}",

                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },

                    {
                        data: 'icon',
                        name: 'icon',
                        render: function (data) {
                            return `<i class="fa ${data} fa-lg"></i>`;
                        },
                        orderable: false,
                        searchable: false
                    },

                    { data: 'name', name: 'name' },

                    {
                        data: 'permissions',
                        name: 'permissions',

                    }, {
                        data: 'action',
                        name: 'action',
                    }
                ]
            });
        }
    </script>
@endsection