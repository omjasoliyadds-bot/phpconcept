@extends('admin.layouts.admin')

@section('title', 'Audit Logs')

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

        .badge-module {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 500;
        }

        .badge-action {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #212529;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa fa-history"></i> Audit Logs</h5>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="auditLogs">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Old Values</h6>
                            <pre id="oldValues" class="bg-light p-3 rounded"></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>New Values</h6>
                            <pre id="newValues" class="bg-light p-3 rounded"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let table = $('#auditLogs').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.audit-logs.data') }}",
                order: [[1, 'desc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'user_name', name: 'user.name' },
                    {
                        data: 'module',
                        name: 'module',
                        render: function (data) {
                            return data ? '<span class="badge badge-module">' + data + '</span>' : '-';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function (data) {
                            return '<span class="badge badge-action">' + data + '</span>';
                        }
                    },
                    { data: 'description', name: 'description' },
                    { data: 'ip_address', name: 'ip_address' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return '<button class="btn btn-sm btn-outline-primary view-details" data-old=\'' + JSON.stringify(data.old_values) + '\' data-new=\'' + JSON.stringify(data.new_values) + '\'><i class="fa fa-eye"></i></button>';
                        }
                    }
                ],
                language: {
                    emptyTable: "No audit logs found"
                }
            });

            $(document).on('click', '.view-details', function () {

                let oldHtml = '';
                let newHtml = '';

                let oldVal = $(this).attr('data-old');
                let newVal = $(this).attr('data-new');

                oldVal = oldVal ? JSON.parse(oldVal) : {};
                newVal = newVal ? JSON.parse(newVal) : {};

                Object.keys(oldVal).forEach(key => {
                    let oldValue = oldVal[key] ?? '-';
                    let newValue = newVal[key] ?? '-';

                    if (oldValue == newValue) return;
                    if (typeof oldValue === 'string' && oldValue.includes('T')) return;
                    // LEFT SIDE (OLD)
                    oldHtml += `<div class="text-danger mb-2">${oldValue}</div>`;

                    // RIGHT SIDE (NEW)
                    newHtml += `<div class="text-success mb-2">${newValue}</div>`;
                });

                $('#oldValues').html(oldHtml || 'No Changes');
                $('#newValues').html(newHtml || 'No Changes');
                $('#detailsModal').modal('show');
            });
        });
    </script>
@endpush