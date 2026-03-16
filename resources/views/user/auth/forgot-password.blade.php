<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
        }

        .card {
            border-radius: 12px;
            border: none;
        }

        .card-header {
            border-radius: 12px 12px 0 0;
        }

        .form-control {
            height: 45px;
        }

        .btn-primary {
            height: 45px;
            font-weight: 500;
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
                <div class="card shadow-lg">
                    <div class="card-header bg-warning text-white text-center fw-bold">
                        <h5 class="mb-0">Reset Link</h5>
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center">
                            <div class="icon-box">
                                <i class="fa fa-envelope"></i>
                            </div>

                            <h4 class="fw-bold">Forgot Password</h4>
                            <p class="text-muted small">
                                Enter your email and we will send you a reset link
                            </p>
                        </div>
                        <form method="POST" id="resetLinkForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter password">
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary">
                                <i class="fa fa-paper-plane mx-3"></i>Send Reset Link</button>
                            </div>
                        </form>
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
            $(document).on('submit','#resetLinkForm', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({ 
                    url: "{{ route('password.email') }}",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status) {
                            $('#resetLinkForm')[0].reset();
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1500, showConfirmButton: false });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire(
                            icon:'error', 
                            title: 'Error', 
                            text: xhr.responseJSON.message,
                            timer: 1500,
                            showConfirmButton: false
                        );
                    }
                });
            })
        })
    </script>
</body>

</html>