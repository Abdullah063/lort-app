@extends('layouts.auth')
@section('content')
<main class="auth-cover-wrapper">
    <div class="auth-cover-content-inner">
        <div class="auth-cover-content-wrapper">
            <div class="auth-img">
                <img src="{{ asset('assets/images/auth/auth-cover-register-bg.svg') }}" alt="" class="img-fluid">
            </div>
        </div>
    </div>
    <div class="auth-cover-sidebar-inner">
        <div class="auth-cover-card-wrapper">
            <div class="auth-cover-card p-sm-5">
                <div class="wd-50 mb-5">
                    <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="img-fluid">
                </div>
                <h2 class="fs-20 fw-bolder mb-4">Kayıt Ol</h2>
                <h4 class="fs-13 fw-bold mb-2">WhatsApp API Yönetim Paneli</h4>
                <p class="fs-12 fw-medium text-muted">Hesabınızı oluşturun ve WhatsApp Business API'yi yönetmeye başlayın.</p>

                <!-- Alert -->
                <div id="alertBox" class="alert d-none mb-3" role="alert"></div>

                <form id="registerForm" class="w-100 mt-4 pt-2">
                    <div class="mb-4">
                        <input type="text" class="form-control" id="fullName" placeholder="Ad Soyad" required>
                    </div>
                    <div class="mb-4">
                        <input type="text" class="form-control" id="username" placeholder="Kullanıcı Adı" required>
                    </div>
                    <div class="mb-4">
                        <input type="email" class="form-control" id="email" placeholder="E-posta" required>
                    </div>
                    <div class="mb-4 generate-pass">
                        <div class="input-group field">
                            <input type="password" class="form-control password" id="password" placeholder="Şifre" required>
                            <div class="input-group-text border-start bg-gray-2 c-pointer show-pass" data-bs-toggle="tooltip" title="Şifreyi Göster/Gizle"><i></i></div>
                        </div>
                        <div class="progress-bar mt-2">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <input type="password" class="form-control" id="passwordConfirm" placeholder="Şifre Tekrar" required>
                    </div>
                    <div class="mt-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="termsCondition" required>
                            <label class="custom-control-label c-pointer text-muted" for="termsCondition" style="font-weight: 400 !important">Kullanım şartlarını kabul ediyorum</label>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" id="submitBtn" class="btn btn-lg btn-primary w-100">Hesap Oluştur</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    <small class="text-muted">Onay için iletişim: <a href="mailto:abdullahaltun016@gmail.com">abdullahaltun016@gmail.com</a></small>
                </div>
                <div class="mt-3 text-muted">
                    <span>Zaten hesabınız var mı?</span>
                    <a href="/admin/login" class="fw-bold">Giriş Yap</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const alertBox = document.getElementById('alertBox');
        const submitBtn = document.getElementById('submitBtn');

        const fullName = document.getElementById('fullName').value.trim();
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('passwordConfirm').value;

        // Validasyon
        if (!fullName || !username || !email || !password) {
            showAlert('Lütfen tüm alanları doldurun', 'danger');
            return;
        }

        if (password !== passwordConfirm) {
            showAlert('Şifreler eşleşmiyor', 'danger');
            return;
        }

        if (password.length < 6) {
            showAlert('Şifre en az 6 karakter olmalıdır', 'danger');
            return;
        }

        // Loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kayıt yapılıyor...';

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    username: username,
                    email: email,
                    password: password,
                    full_name: fullName
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (data.pending_approval) {
                    // Formu gizle, onay bekliyor mesajı göster
                    document.getElementById('registerForm').classList.add('d-none');
                    document.getElementById('alertBox').className = 'alert alert-warning';
                    document.getElementById('alertBox').innerHTML = `
                        <div class="text-center">
                            <i class="feather-clock d-block mb-3" style="font-size: 48px;"></i>
                            <h5 class="fw-bold mb-2">Kayıt Başarılı!</h5>
                            <p class="mb-2">Hesabınız oluşturuldu ancak <strong>admin onayı</strong> bekliyor.</p>
                            <p class="mb-3 text-muted">Hesabınız aktif edildiğinde giriş yapabileceksiniz. Onay için aşağıdaki kanallardan iletişime geçebilirsiniz:</p>
                            <div class="mb-3">
                                <a href="mailto:abdullahaltun016@gmail.com" class="d-block mb-2"><i class="feather-mail me-1"></i> abdullahaltun016@gmail.com</a>
                                <a href="https://www.instagram.com/_abdullah_altun/" target="_blank" class="d-block"><i class="feather-instagram me-1"></i> @_abdullah_altun</a>
                            </div>
                            <a href="/admin/login" class="btn btn-primary">Giriş Sayfasına Dön</a>
                        </div>
                    `;
                    document.getElementById('alertBox').classList.remove('d-none');
                } else {
                    showAlert('Kayıt başarılı! Yönlendiriliyorsunuz...', 'success');
                    setTimeout(() => { window.location.href = '/'; }, 1500);
                }
            } else {
                showAlert(data.detail || 'Kayıt başarısız', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Hesap Oluştur';
            }
        } catch (error) {
            showAlert('Bir hata oluştu: ' + error.message, 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Hesap Oluştur';
        }
    });

    function showAlert(message, type) {
        const alertBox = document.getElementById('alertBox');
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }
</script>

@endsection