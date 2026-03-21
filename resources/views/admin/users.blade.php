@extends('admin.layouts.admin')

@section('title', 'Users')
@section('styles')
    <style>
        .card {
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .table thead th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa fa-users"></i> All Users</h5>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="allUsers">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Sharing</th>
                                <th>Storage Usage</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let table = $('#allUsers').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.users.data') }}",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'can_share', name: 'can_share', orderable: false, searchable: false },
                    { data: 'storage', name: 'storage', orderable: false, searchable: false },
                ]
            });

            $(document).on('change', '.toggle-status', function () {
                let id = $(this).data('id');
                let status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: "{{ route('admin.users.toggle') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
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
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong!'
                        });
                    }
                });
            });

            $(document).on('change', '.toggle-sharing', function () {
                let id = $(this).data('id');
                
                $.ajax({
                    url: "{{ route('admin.users.toggle_sharing') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
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
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong!'
                        });
                    }
                });
            });

            $(document).on('click', '.edit-storage', function () {
                let id = $(this).data('id');
                let currentLimit = $(this).data('limit');
                let currentLimitGB = (currentLimit / (1024 * 1024 * 1024)).toFixed(2);

                Swal.fire({
                    title: 'Update Storage Limit',
                    text: 'Enter the new storage limit in GB:',
                    input: 'number',
                    inputValue: currentLimitGB,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    confirmButtonColor: '#0d6efd',
                    inputValidator: (value) => {
                        if (!value || value < 0) {
                            return 'Please enter a valid positive number';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let newLimitGB = result.value;
                        let newLimitBytes = Math.round(newLimitGB * 1024 * 1024 * 1024);

                        $.ajax({
                            url: "{{ route('admin.users.update_storage_limit') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                storage_limit: newLimitBytes
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
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Could not update storage limit!'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
