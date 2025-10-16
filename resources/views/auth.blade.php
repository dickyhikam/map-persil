<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $nama_menu }}</title>

    <!-- Bootstrap 5 + Icons (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="{{ asset('style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('buttons.css') }}" rel="stylesheet" type="text/css">
    <style>
        #copyEmailIcon,
        #copyPasswordIcon {
            cursor: pointer;
            transition: color 0.3s;
        }

        #copyEmailIcon:hover,
        #copyPasswordIcon:hover {
            color: #007bff;
            /* Ganti warna saat hover */
        }
    </style>
</head>

<body>
    <main class="container py-5">
        <div class="login-wrapper bg-white p-4 p-md-5">
            <!-- Compact header -->
            <div class="d-flex align-items-center mb-4">
                <div class="rounded-circle brand-gradient d-inline-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                    <i class="bi bi-globe-asia-australia text-white fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-0">Sign in to your account</h4>
                    <div class="small-muted">Please enter your credentials</div>
                </div>
            </div>

            <div id="alert" class="alert alert-danger d-none py-2">Invalid email or password.</div>

            {{-- Login form --}}
            <form id="loginForm" class="needs-validation" novalidate>
                <div class="form-floating mb-3 position-relative">
                    <input type="email" class="form-control" id="email" placeholder="name@domain.com" required>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Email address</label>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" placeholder="Password" required minlength="6">
                    <label for="password"><i class="bi bi-key me-2"></i>Password</label>
                    <button type="button" id="togglePassword" class="btn btn-link input-icon text-decoration-none" tabindex="-1" aria-label="Show / hide password">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                </div>
                <button class="btn btn-gradient w-100 py-2 text-white" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
            <hr class="my-4">
            <p class="small text-muted mb-0">Demo credentials:</p>
            <div class="d-flex">
                <div class="d-flex align-items-center">
                    <code id="demo-email">admin@example.com</code>
                    <i id="copyEmailIcon" class="bi bi-clipboard text-primary" style="cursor: pointer; margin-left: 10px;" title="Copy Email"></i>
                </div>
                <div class="d-flex align-items-center ms-4">
                    <code id="demo-password">password123</code>
                    <i id="copyPasswordIcon" class="bi bi-clipboard text-primary" style="cursor: pointer; margin-left: 10px;" title="Copy Password"></i>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Jika sudah login, langsung lempar ke dashboard
        if (sessionStorage.getItem('isAuth') === 'true') {
            window.location.href = "{{ route('pageMaps') }}";
        }

        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        toggleBtn.addEventListener('click', () => {
            const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
            pwd.setAttribute('type', type);
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });

        // Simple validator helper
        function validate(form) {
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return false;
            }
            return true;
        }

        // Demo auth check (hardcoded)
        const VALID_EMAIL = 'admin@example.com';
        const VALID_PASSWORD = 'password123';

        // Handle login form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Jangan submit form secara default
            const form = e.currentTarget;
            if (!validate(form)) return;

            const email = document.getElementById('email').value.trim();
            const pass = document.getElementById('password').value;

            // Validasi email dan password
            const alertBox = document.getElementById('alert');
            const isValid = (email.toLowerCase() === VALID_EMAIL && pass === VALID_PASSWORD);

            if (!isValid) {
                alertBox.classList.remove('d-none');
                return;
            }

            // Simpan ke sessionStorage dan redirect
            sessionStorage.setItem('isAuth', 'true');
            sessionStorage.setItem('userEmail', email);
            sessionStorage.setItem('loginAt', String(Date.now()));

            // Redirect ke dashboard
            window.location.href = "{{ route('pageMaps') }}";
        });

        // Salin Email
        document.getElementById('copyEmailIcon').addEventListener('click', function() {
            const emailText = document.getElementById('demo-email').innerText;

            // Buat elemen input untuk menyalin teks
            const tempInput = document.createElement('input');
            tempInput.value = emailText;
            document.body.appendChild(tempInput);

            // Pilih dan salin teks dari input
            tempInput.select();
            document.execCommand('copy');

            // Hapus elemen input sementara
            document.body.removeChild(tempInput);

            // Ubah ikon menjadi "check-circle" setelah disalin
            const copyEmailIcon = document.getElementById('copyEmailIcon');
            copyEmailIcon.classList.remove('bi-clipboard');
            copyEmailIcon.classList.add('bi-check-circle');
            copyEmailIcon.setAttribute('title', 'Copied');

            // Kembalikan ikon setelah beberapa detik
            setTimeout(() => {
                copyEmailIcon.classList.remove('bi-check-circle');
                copyEmailIcon.classList.add('bi-clipboard');
                copyEmailIcon.setAttribute('title', 'Copy Email');
            }, 2000);
        });

        // Salin Password
        document.getElementById('copyPasswordIcon').addEventListener('click', function() {
            const passwordText = document.getElementById('demo-password').innerText;

            // Buat elemen input untuk menyalin teks
            const tempInput = document.createElement('input');
            tempInput.value = passwordText;
            document.body.appendChild(tempInput);

            // Pilih dan salin teks dari input
            tempInput.select();
            document.execCommand('copy');

            // Hapus elemen input sementara
            document.body.removeChild(tempInput);

            // Ubah ikon menjadi "check-circle" setelah disalin
            const copyPasswordIcon = document.getElementById('copyPasswordIcon');
            copyPasswordIcon.classList.remove('bi-clipboard');
            copyPasswordIcon.classList.add('bi-check-circle');
            copyPasswordIcon.setAttribute('title', 'Copied');

            // Kembalikan ikon setelah beberapa detik
            setTimeout(() => {
                copyPasswordIcon.classList.remove('bi-check-circle');
                copyPasswordIcon.classList.add('bi-clipboard');
                copyPasswordIcon.setAttribute('title', 'Copy Password');
            }, 2000);
        });
    </script>
</body>

</html>