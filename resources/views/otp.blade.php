<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .otp-card {
            max-width: 400px;
            margin-top: 120px;
        }

        .otp-input {
            text-align: center;
            font-size: 20px;
            letter-spacing: 5px;
        }

        label.error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center">
        <div class="card otp-card shadow-sm w-100">
            <div class="card-body p-4 text-center">

                <h4 class="fw-bold">OTP Verification</h4>
                <p class="text-muted small">Enter the 6-digit OTP sent to your email</p>
                <form id="otpForm">
                    @csrf
                    <input type="hidden" name="otp_token" id="otp_token" value="{{ $otpToken }}">
                    <input type="text" name="otp" id="otp" maxlength="6" 
                        class="form-control otp-input mt-3" placeholder="Enter 6-digit OTP"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        autocomplete="one-time-code" inputmode="numeric">

                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        Verify OTP
                    </button>

                </form>
                <button class="btn btn-link mt-2" onclick="resendOtp()">Resend OTP</button>

            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- jQuery Validation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $("#otpForm").validate({
            rules: {
                otp: {
                    required: true,
                    digits: true,
                    minlength: 6,
                    maxlength: 6
                }
            },
            messages: {
                otp: {
                    required: "OTP is required",
                    digits: "Only numbers allowed",
                    minlength: "OTP must be 6 digits",
                    maxlength: "OTP must be 6 digits"
                }
            },
            submitHandler: function (form) {
                verifyOtp();
            }
        });

        function verifyOtp() {
            let otp = $('#otp').val();
            let otp_token = $('#otp_token').val();

            $.ajax({
                url: "{{ route('verify.otp') }}",
                type: "POST",
                data: {
                    otp: otp,
                    otp_token: otp_token,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            if (response.role == 'admin') {
                                window.location.href = "{{ route('admin.dashboard') }}";
                            } else {
                                window.location.href = "{{ route('user.dashboard') }}";
                            }
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        }

        function resendOtp() {
            let otp_token = $('#otp_token').val();
            $.ajax({
                url: "{{ route('resend.otp') }}",
                type: "POST",
                data: {
                    otp_token: otp_token,
                    _token: "{{ csrf_token() }}" 
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire('Success', 'OTP resent successfully', 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    </script>

</body>

</html>