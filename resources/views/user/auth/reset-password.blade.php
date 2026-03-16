<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background: #f4f6f9;
        }
    </style>

</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="card-title mb-0">Reset Password</h5>
                    </div>

                    <div class="card-body">

                        <form id="resetPasswordForm" method="POST">

                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" name="email"
                                    value="{{ $email ?? old('email') }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-lock me-2"></i> Reset Password
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
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