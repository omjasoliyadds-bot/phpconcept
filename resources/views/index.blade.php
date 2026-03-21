<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Inter", sans-serif;
            color: #f8fafc;
        }

        .card {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
        }

        .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: transparent !important;
            padding: 2rem 1.5rem 1rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            height: 48px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 12px;
            padding: 0 1rem;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            color: #f8fafc;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .btn-primary {
            height: 48px;
            font-weight: 600;
            background: #6366f1;
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .card-footer {
            background: transparent;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            color: #94a3b8;
        }

        .card-footer a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }

        .card-footer a:hover {
            color: #818cf8;
        }

        @media(max-width:576px) {

            .card {
                margin: 10px;
            }

        }
    </style>

</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-sm-10 col-12">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h5 class="mb-0">Create Account</h5>
                    </div>

                    <div class="card-body">
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

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Enter password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Confirm password">
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary">Register</button>
                            </div>
                        </form>

                    </div>

                    <div class="card-footer text-center">
                        Already have an account?
                        <a href="{{ route('login') }}">Login</a>
                    </div>

                </div>

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
            $('#userRegister').validate({
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                        email: true
                    },
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
                        }
                    });
                }
            })
        })
    </script>
</body>

</html>