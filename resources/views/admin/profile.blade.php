@extends('admin.layouts.admin')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Profile Settings</h4>
    </div>

    <div class="row g-4">
        <!-- Account Info -->
        <div class="col-md-6">
            <div class="profile-card h-100">
                <div class="section-title">
                    <i class="fa fa-user-circle text-primary"></i> Account Information
                </div>
                <div class="mb-4 text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff&size=100"
                        class="rounded-circle mb-3 border border-4 border-light shadow-sm" alt="Avatar">
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small">{{ $user->email }}</p>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editAdminProfileModal"
                        data-id="{{ $user->id }}">
                        <i class="fa fa-edit me-2"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Security / Change Password -->
        <div class="col-md-6">
            <div class="profile-card h-100">

                <form id="changePasswordForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control"
                            placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-key me-2"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAdminProfileModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateProfileForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Update Profile AJAX
            $('#updateProfileForm').validate({
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: "{{ route('admin.profile.update', $user->id) }}",
                        method: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#editAdminProfileModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = '';
                            if (errors) {
                                $.each(errors, function(key, value) {
                                    errorMessages += value[0] + '<br>';
                                });
                            } else {
                                errorMessages = xhr.responseJSON.message || 'Something went wrong';
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessages,
                            });
                        }
                    });
                    return false;
                }
            });

            // Change Password AJAX
            $('#changePasswordForm').validate({
                rules: {
                    current_password: {
                        required: true
                    },
                    new_password: {
                        required: true,
                        minlength: 8
                    },
                    new_password_confirmation: {
                        required: true,
                        equalTo: "#new_password"
                    }
                },
                 errorPlacement: function (error, element) {
                    error.addClass('text-danger small');
                    error.insertAfter(element);
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: "{{ route('admin.password.update') }}",
                        method: "POST",
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.status === 'success') {
                                form.reset();
                                Swal.fire({
                                    icon: 'success',
                                    title: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = '';
                            if (errors) {
                                $.each(errors, function(key, value) {
                                    errorMessages += value[0] + '<br>';
                                });
                            } else {
                                errorMessages = xhr.responseJSON.message || 'Something went wrong';
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessages,
                            });
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endpush