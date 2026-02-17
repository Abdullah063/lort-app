@extends('layouts.auth')
@section('content')
<main class="auth-cover-wrapper">
    <div class="auth-cover-content-inner">
        <div class="auth-cover-content-wrapper">
            <div class="auth-img">
                <img src="{{ asset('assets/images/auth/auth-cover-login-bg.svg') }}" alt="" class="img-fluid">
            </div>
        </div>
    </div>
    <div class="auth-cover-sidebar-inner">
        <div class="auth-cover-card-wrapper">
            <div class="auth-cover-card p-sm-5">
                <div class="wd-50 mb-5">
                    <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="img-fluid">
                </div>
                <h2 class="fs-20 fw-bolder mb-4">Giriş Yap</h2>
                <h4 class="fs-13 fw-bold mb-2">WhatsApp API Panel</h4>
                <p class="fs-12 fw-medium text-muted">WhatsApp Business API yönetim paneline hoş geldiniz.</p>
                <div id="loginError" class="alert alert-danger d-none mb-3"></div>
                <form id="loginForm" class="w-100 mt-4 pt-2">
                    <div class="mb-4">
                        <input type="email" id="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" id="password" class="form-control" placeholder="Şifre" required>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe">
                                <label class="custom-control-label c-pointer" for="rememberMe">Beni Hatırla</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" id="loginBtn" class="btn btn-lg btn-primary w-100">Giriş Yap</button>
                    </div>
                </form>
                <div class="w-100 mt-4 text-center">
                    <small class="text-muted">İletişim: abdullahaltun016@gmail.com</small>
                </div>
                <div class="mt-5 text-muted">
                    <span>Hesabınız yok mu?</span>
                    <a href="/admin/register" class="fw-bold">Hesap Oluştur</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const loginBtn = document.getElementById('loginBtn');
        const errorDiv = document.getElementById('loginError');

        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Giriş yapılıyor...';
        errorDiv.classList.add('d-none');

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    email,
                    password
                })
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

            if (response.ok && data.success) {
                window.location.href = '/';
            } else if (data.inactive) {
                errorDiv.className = 'alert alert-warning mb-3';
                errorDiv.innerHTML = `
                    <i class="feather-alert-circle me-1"></i>
                    <strong>Hesabınız henüz aktif değil!</strong><br>
                    <small>Hesabınız admin onayı bekliyor. Onay için iletişime geçin:</small><br>
                    <small><a href="mailto:abdullahaltun016@gmail.com"><i class="feather-mail me-1"></i>abdullahaltun016@gmail.com</a></small><br>
                    <small><a href="https://www.instagram.com/_abdullah_altun/" target="_blank"><i class="feather-instagram me-1"></i>@_abdullah_altun</a></small>
                `;
                errorDiv.classList.remove('d-none');
            } else {
                errorDiv.className = 'alert alert-danger mb-3';
                errorDiv.textContent = data.detail || data.message || 'Giriş başarısız';
                errorDiv.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Login error:', error);
            errorDiv.textContent = 'Bağlantı hatası: ' + error.message;
            errorDiv.classList.remove('d-none');
        } finally {
            loginBtn.disabled = false;
            loginBtn.innerHTML = 'Giriş Yap';
        }
    });
</script>
@endsection