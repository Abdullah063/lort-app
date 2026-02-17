<!-- Mobile Menu Toggle Button -->
<div class="mobile-menu-toggle d-lg-none">
    <button class="btn btn-primary" onclick="toggleMobileMenu()" id="mobileMenuBtn">
        <i class="feather-menu"></i>
    </button>
</div>

<style>
.mobile-menu-toggle {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1050;
}
.mobile-menu-toggle .btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
@media (max-width: 991.98px) {
    .nxl-navigation {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 280px !important;
        height: 100vh !important;
        z-index: 1040 !important;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        background: #fff;
        overflow-y: auto;
    }
    .nxl-navigation.mobile-open {
        transform: translateX(0) !important;
    }
    .mobile-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1030;
        display: none;
    }
    .mobile-overlay.show {
        display: block;
    }
}
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

<!-- WhatsApp API Sidebar Navigation -->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="/" class="b-brand">
                <img src="{{ asset('assets/images/logo-full.svg') }}" alt="" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">

                @if(isset($authUser) && in_array($authUser->role, ['admin', 'superadmin']))
                {{-- ===== ADMIN / SUPERADMIN MENÜSÜ ===== --}}
                <li class="nxl-item nxl-caption">
                    <label>WhatsApp API</label>
                </li>
                <li class="nxl-item">
                    <a href="/" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Yönetim</label>
                </li>
                <li class="nxl-item">
                    <a href="/users" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Kullanıcı Yönetimi</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/quota-management" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">Kota Yönetimi</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/logs" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Giriş Logları</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Ayarlar</label>
                </li>
                <li class="nxl-item">
                    <a href="/settings-api" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">API Ayarları</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/profile" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Profil</span>
                    </a>
                </li>

                @else
                {{-- ===== NORMAL KULLANICI MENÜSÜ ===== --}}
                <li class="nxl-item nxl-caption">
                    <label>Ana Sayfa</label>
                </li>
                <li class="nxl-item">
                    <a href="/" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Mesajlaşma</label>
                </li>
                <li class="nxl-item">
                    <a href="/chat" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-message-circle"></i></span>
                        <span class="nxl-mtext">Mesajlar</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/bulk-send" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-send"></i></span>
                        <span class="nxl-mtext">Toplu Mesaj</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/history" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-clock"></i></span>
                        <span class="nxl-mtext">Mesaj Geçmişi</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Araçlar</label>
                </li>
                <li class="nxl-item">
                    <a href="/groups" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Numara Grupları</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/auto-replies" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-zap"></i></span>
                        <span class="nxl-mtext">Otomatik Cevaplar</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/blacklist" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-slash"></i></span>
                        <span class="nxl-mtext">Kara Liste</span>
                    </a>
                </li>

                <li class="nxl-item nxl-caption">
                    <label>Ayarlar</label>
                </li>
                <li class="nxl-item">
                    <a href="/settings-api" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">API Ayarları</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="/profile" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Profil</span>
                    </a>
                </li>
                @endif

                <!-- Çıkış -->
                <li class="nxl-item" style="margin-top:10px; border-top:1px solid #e5e7eb; padding-top:10px;">
                    <a href="javascript:void(0);" onclick="sidebarLogout()" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-log-out"></i></span>
                        <span class="nxl-mtext">Çıkış Yap</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    var nav = document.querySelector('.nxl-navigation');
    var overlay = document.getElementById('mobileOverlay');
    var btn = document.getElementById('mobileMenuBtn');
    if (!nav || !overlay || !btn) return;
    nav.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
    var icon = btn.querySelector('i');
    if (icon) icon.className = nav.classList.contains('mobile-open') ? 'feather-x' : 'feather-menu';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nxl-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                var nav = document.querySelector('.nxl-navigation');
                if (nav && nav.classList.contains('mobile-open')) toggleMobileMenu();
            }
        });
    });
});

async function sidebarLogout() {
    try {
        await fetch('/api/auth/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
    } catch(e) {}
    window.location.href = '/admin/login';
}
</script>