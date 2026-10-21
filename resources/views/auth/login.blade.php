<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Login Sistem Manajemen LSP">
    <title>Login - Admin Panel</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        .login-card {
            border-radius: 1rem;
            overflow: hidden;
        }
        .login-illustration {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem;
            color: #ffffff;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container py-5">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-4 login-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row no-gutters">
                            <!-- Left Column: Branding / Info -->
                            <div class="col-lg-5 d-none d-lg-flex login-illustration text-center">
                                <div class="mb-4">
                                    <i class="fas fa-user-shield fa-5x text-white-50"></i>
                                </div>
                                <h3 class="font-weight-bold text-white mb-2">LSP Admin Panel</h3>
                                <p class="text-white-50 small mb-4">Sistem Terintegrasi Inventaris Produk & Penjualan (POS)</p>
                                <hr class="border-light w-75 my-3">
                                <div class="card bg-white text-dark shadow-sm w-100 text-left p-3 rounded">
                                    <div class="font-weight-bold small text-primary mb-1">
                                        <i class="fas fa-key mr-1"></i> Akun Login Default
                                    </div>
                                    <div class="small text-muted mb-1">
                                        <strong>Email:</strong> <code>admin@example.com</code>
                                    </div>
                                    <div class="small text-muted">
                                        <strong>Password:</strong> <code>password</code>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Login Form -->
                            <div class="col-lg-7">
                                <div class="p-4 p-md-5">
                                    <div class="text-center mb-4">
                                        <h1 class="h4 text-gray-900 font-weight-bold">Selamat Datang!</h1>
                                        <p class="text-muted small">Silakan masuk menggunakan akun terdaftar Anda</p>
                                    </div>

                                    @foreach (['success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'] as $type => $icon)
                                        @if (session($type))
                                            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show small" role="alert">
                                                <i class="fas fa-{{ $icon }} mr-1"></i> {{ session($type) }}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        @endif
                                    @endforeach

                                    <form class="user" method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-group">
                                            <label for="email" class="small text-gray-700 font-weight-bold">Alamat Email</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-gray-400"></i></span>
                                                </div>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    id="email"
                                                    name="email"
                                                    value="{{ old('email', 'admin@example.com') }}"
                                                    placeholder="Masukkan Alamat Email..."
                                                    required
                                                    autofocus>
                                                @error('email')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="password" class="small text-gray-700 font-weight-bold">Kata Sandi</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-gray-400"></i></span>
                                                </div>
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    id="password"
                                                    name="password"
                                                    value="password"
                                                    placeholder="Masukkan Kata Sandi..."
                                                    required>
                                                @error('password')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="remember" name="remember" value="1" checked>
                                                <label class="custom-control-label" for="remember">Ingat Saya</label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block shadow-sm font-weight-bold py-2">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk ke Sistem
                                        </button>
                                    </form>

                                    <hr>

                                    <!-- Quick Mobile Credentials Box -->
                                    <div class="d-block d-lg-none mt-3">
                                        <div class="alert alert-info py-2 px-3 small mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Akun Admin:</strong> <code>admin@example.com</code> | Pass: <code>password</code>
                                        </div>
                                    </div>

                                    <div class="text-center mt-3">
                                        <span class="small text-muted">&copy; {{ date('Y') }} LSP Web Development. Seluruh hak cipta dilindungi.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

</body>

</html>
