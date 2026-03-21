<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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

        .mb-3.text-center a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .mb-3.text-center a:hover {
            color: #6366f1;
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
                margin: 15px;
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
                        <h5 class="mb-0">Login Page</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" id="userLogin">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Enter password">
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>

                    <div class="mb-3 text-center">
                        <a href="{{ route('auth.reset-password') }}">Forgot Password?</a>
                    </div>

                    <div class="card-footer text-center">
                        Create an account?
                        <a href="/">Register Here</a>
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
                                    }else {
                                        window.location.href = "{{ route('user.dashboard') }}";
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    })
                }
            });
        })
    </script>
</body>

</html>