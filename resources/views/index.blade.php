<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Private-Docs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .auth-card {
            max-width: 500px;
            width: 100%;
            margin-top: 80px;
            margin-bottom: 50px;
        }
    </style>

</head>

<body>

    <div class="container d-flex justify-content-center">
        <div class="card auth-card shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 text-center">
                <h4 class="fw-bold">Create Account</h4>
                <p class="text-muted small">Join us today</p>
            </div>

            <div class="card-body p-4">
                <form id="userRegister">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Create password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="Confirm password">
                        </div>
                    </div>

                    <div class="d-grid mt-2">
                        <button class="btn btn-primary" type="submit" id="register">Register</button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white border-top-0 pb-4 text-center">
                <span class="text-muted small">Already have an account?</span>
                <a href="{{ route('login') }}" class="small text-decoration-none fw-bold">Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $.validator.addMethod("strongPassword", function (value, element) {
                return /^(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).+$/.test(value);
            }, "Password must contain at least one lowercase letter, one number, and one special character.");
            $('#userRegister').validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 255
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 8,
                        strongPassword: true
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    }
                },
                messages: {
                    name: {
                        required: "Please enter your name",
                    },
                    email: {
                        required: "Please enter your email",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter your password",
                        minlength: "Password must be at least 8 characters"
                    },
                    password_confirmation: {
                        required: "Please confirm your password",
                        equalTo: "Passwords do not match"
                    }
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('text-danger small');
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    let btn = $('#register');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Registering...');
                    let formData = new FormData(form);
                    $.ajax({
                        url: "{{ route('api.user.store') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            if (response.status) {
                                $('#userRegister')[0].reset();
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then((result) => {
                                    window.location.href = "{{ route('login') }}";
                                })
                            } else {
                                btn.prop('disabled', false).html('Register');
                                if (response.errors) {
                                    let errorMessages = '';
                                    Object.values(response.errors).forEach(err => {
                                        errorMessages += err[0] + '<br>';
                                    });
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Validation Error',
                                        html: errorMessages,
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Registration failed'
                                    });
                                }
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        },
                        complete: function () {
                            btn.prop('disabled', false).html('Register');
                        }
                    });
                }
            })
        })
    </script>
</body>

</html>