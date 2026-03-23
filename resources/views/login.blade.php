<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Private-Docs</title>

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
                <h4 class="fw-bold">Login</h4>
                <p class="text-muted small">Sign in to your account</p>
            </div>

            <div class="card-body p-4">
                <form method="POST" id="userLogin">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="{{ route('auth.reset-password') }}" class="small text-decoration-none">Forgot?</a>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="Enter password">
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">Login</button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white border-top-0 pb-4 text-center">
                <span class="text-muted small">Don't have an account?</span>
                <a href="/" class="small text-decoration-none fw-bold">Register</a>
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
            $('#userLogin').validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                    }
                },
                messages: {
                    email: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter your password",
                    }
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('text-danger small');
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    let formData = new FormData(form);
                    $.ajax({
                        url: "{{ route('api.login.user') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
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
                                    $('#userLogin')[0].reset();
                                    if (response.role == 'admin') {
                                        window.location.href = "{{ route('admin.dashboard') }}";
                                    } else {
                                        window.location.href = "{{ route('user.dashboard') }}";
                                    }
                                });
                            } else {
                                // Specific handling for unverified email
                                let icon = 'error';
                                if (response.message.toLowerCase().includes('verify')) {
                                    icon = 'warning';
                                }

                                Swal.fire({
                                    icon: icon,
                                    title: icon === 'warning' ? 'Verification Required' : 'Error',
                                    text: response.message,
                                });
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = xhr.responseJSON?.message || 'Something went wrong';
                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Login Failed',
                                text: errorMsg,
                            });
                        }
                    })
                }
            });
        })
    </script>
</body>

</html>