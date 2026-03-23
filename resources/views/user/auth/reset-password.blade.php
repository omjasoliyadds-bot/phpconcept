<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Private-Docs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .auth-card {
            max-width: 400px;
            width: 100%;
            margin-top: 100px;
        }
    </style>

</head>

<body>

    <div class="container d-flex justify-content-center">
        <div class="card auth-card shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 text-center">
                <h4 class="fw-bold">Reset Password</h4>
                <p class="text-muted small">Update your account password</p>
            </div>

            <div class="card-body p-4">
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" class="form-control bg-light" name="email"
                            value="{{ $email ?? old('email') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" required placeholder="••••••••">
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white border-top-0 pb-4 text-center">
                <a href="{{ route('login') }}" class="small text-decoration-none fw-bold">Back to Login</a>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        $(document).ready(function () {

            $('#resetPasswordForm').validate({

                rules: {
                    password: {
                        required: true,
                        minlength: 8
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    }
                },

                messages: {
                    password: {
                        required: "Please enter a password",
                        minlength: "Password must be at least 8 characters"
                    },
                    password_confirmation: {
                        required: "Please confirm your password",
                        equalTo: "Passwords do not match"
                    }
                },

                submitHandler: function (form) {

                    let formData = new FormData(form);
                    let btn = $(form).find('button[type="submit"]');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Updating...');
                    $.ajax({
                        url: "{{ route('api.password.reset') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href = "{{ route('login') }}";
                                });
                            } else {
                                let errorMsg = '';
                                if (response.errors) {
                                    $.each(response.errors, function (key, value) {
                                        errorMsg += value[0] + '<br>';
                                    });
                                } else if (response.message) {
                                    errorMsg = response.message;
                                }
                                Swal.fire('Error', errorMsg, 'error');
                                btn.prop('disabled', false).html('<i class="fa fa-lock me-2"></i> Reset Password');
                            }
                        },

                        error: function () {
                            Swal.fire('Error', 'Something went wrong', 'error');
                            btn.prop('disabled', false)
                                .html('<i class="fa fa-lock me-2"></i> Reset Password');
                        }
                    });
                }
            });
        });

    </script>

</body>

</html>