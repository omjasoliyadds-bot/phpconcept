@extends('user.layouts.user')

@section('content')
    <div class="container">
        <div class="card border-0 p-4">
            <div class="row" id="allDocuments">

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            loadSharedDocuments();
        });

        function loadSharedDocuments() {
            $.ajax({
                url: "{{ route('user.get.share.documents') }}",
                method: "GET",
                dataType: "json",
                success: function (response) {
                    let html = '';
                    if (response.status && response.data.length > 0) {
                        response.data.forEach(function (doc) {
                            let permissions = doc.permissions?.map(p => p.permission) || [];
                            let permissionText = permissions.length ? permissions.join(', ') : 'N/A';
                            let downloadUrl = "{{ route('documents.download', ':id', false) }}".replace(':id', doc.id);
                            html += `
                                    <div class="col-12 mb-3">
                                        <div class="card shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fa ${doc.icon} me-2"></i>
                                                    <strong>${doc.name}</strong>
                                                </div>
                                                <p class="mb-1"><strong>Owner name:</strong> ${doc.user.name}</p>
                                                <p class="mb-1"><strong>Owner email:</strong> ${doc.user.email}</p>
                                                <p class="mb-1"><strong>Size:</strong> ${formatFileSize(doc.size)}</p>
                                                <p class="mb-1"><strong>Date:</strong> ${formatDate(doc.created_at)}</p>
                                                <div>
                                                ${permissions.includes('view') ? `<button class="btn btn-sm btn-primary">View</button>` : ''}
                                                ${permissions.includes('edit') ? `<button class="btn btn-sm btn-warning">Edit</button>` : ''}
                                                ${permissions.includes('download') ? `<a href="${downloadUrl}" class="btn btn-sm btn-success">Download</a>` : ''}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        });
                    } else {
                        html = `<div class='col-12'>
                                        <div class="card shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-2">
                                                    <i class="fa fa-folder-open text-muted" style="font-size: 3rem;"></i>
                                                </div>
                                                <h5 class="text-muted">No document shared yet</h5>
                                            </div>
                                        </div>
                                    </div>`;
                    }
                    $('#allDocuments').html(html);
                },
                error: function (xhr) {
                    $('#allDocuments').html(`<div class='col-12 text-center text-danger'>
                            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                            <h5>Error loading documents</h5>
                            <p>${xhr.responseJSON?.message || xhr.statusText}</p>
                        </div>`);
                }
            });
        }

        // helpers
        function formatFileSize(bytes) {
            // return (bytes < 1024) return bytes + ' B'; for kb
            // return (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB' for bytes;
            return (bytes / 1048576).toFixed(2) + ' MB';
        }

        function formatDate(date) {
            let d = new Date(date);
            return d.toLocaleString();
        }
    </script>
@endpush