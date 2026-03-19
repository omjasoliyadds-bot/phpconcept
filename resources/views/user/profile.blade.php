@extends('user.layouts.user')

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
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=100" class="rounded-circle mb-3 border border-4 border-light shadow-sm" alt="Avatar">
                    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted small">{{ auth()->user()->email }}</p>
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
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
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="••••••••">
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
@endsection

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
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
                        <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}">
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

@push('scripts')
    <script>
        $(document).ready(function () {
            // Update Profile Form Validation & Submission
            $('#updateProfileForm').validate({
                rules: {
                    name: { required: true },
                    email: { required: true, email: true },
                },
                 errorPlacement: function (error, element) {
                    error.addClass('text-danger small');
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    const btn = $(form).find('button[type="submit"]');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Updating...');
                    
                    $.ajax({
                        url: "{{ route('api.user.profile.update') }}",
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function (response) {
                            if (response.status) {  
                                 window.showSuccess(response.message);
                                 setTimeout(() => {
                                     location.reload();
                                 }, 1000);
                             } else {
                                 window.showErrors(response);
                                 btn.prop('disabled', false).html('Update');
                             }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', 'An error occurred. Please try again.', 'error');
                            btn.prop('disabled', false).html('Update');
                        }
                    });
                }
            });

            // Change Password Form Validation & Submission
            $('#changePasswordForm').validate({
                rules: {
                    current_password: { required: true },
                    new_password: { required: true, minlength: 8 },
                    new_password_confirmation: { required: true, equalTo: "#new_password" }
                },
                messages: {
                    new_password_confirmation: { equalTo: "Passwords do not match" }
                },
                 errorPlacement: function (error, element) {
                    error.addClass('text-danger small');
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    const btn = $(form).find('button[type="submit"]');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Updating...');

                    $.ajax({
                        url: "{{ route('api.user.change-password') }}",
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function (response) {
                            if (response.status) {
                                 window.showSuccess(response.message);
                                 form.reset();
                                 btn.prop('disabled', false).html('<i class="fa fa-key me-2"></i> Update Password');
                             } else {
                                 window.showErrors(response);
                                 btn.prop('disabled', false).html('<i class="fa fa-key me-2"></i> Update Password');
                             }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', 'An internal error occurred.', 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-key me-2"></i> Update Password');
                        }
                    });
                }
            });
        });
    </script>
@endpush