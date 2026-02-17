<!-- Header -->
<header class="nxl-header">
    <div class="header-wrapper">
        <!--! [Start] Header Left !-->
        <div class="header-left d-flex align-items-center gap-4">
            <!--! [Start] nxl-head-mobile-toggler !-->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <!--! [End] nxl-head-mobile-toggler !-->
            <!--! [Start] nxl-navigation-toggle !-->
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
            <!--! [End] nxl-navigation-toggle !-->
            <!--! [Start] nxl-lavel-mega-menu-toggle !-->
            <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                    <i class="feather-align-left"></i>
                </a>
            </div>
            <!--! [End] nxl-lavel-mega-menu-toggle !-->
            <!--! [Start] Breadcrumb !-->
            <div class="d-none d-lg-flex align-items-center gap-2">
                <span class="text-muted fs-13">
                    <i class="feather-home fs-12"></i>
                </span>
                <span class="text-muted fs-13">/</span>
                <span class="fs-13 fw-semibold text-dark" id="headerPageTitle">WhatsApp API</span>
            </div>
            <!--! [End] Breadcrumb !-->
        </div>
        <!--! [End] Header Left !-->
        <!--! [Start] Header Right !-->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                <!--! [Start] Header Search !-->
                <div class="dropdown nxl-h-item nxl-header-search">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-search"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-search-dropdown">
                        <div class="input-group search-form">
                            <span class="input-group-text">
                                <i class="feather-search fs-6 text-muted"></i>
                            </span>
                            <input type="text" class="form-control search-input-field" placeholder="Sayfa ara..." id="headerSearchInput">
                            <span class="input-group-text">
                                <button type="button" class="btn-close"></button>
                            </span>
                        </div>
                        <div class="dropdown-divider mt-0"></div>
                        <div class="search-items-wrapper">
                            <div class="searching-for px-4 py-2">
                                <p class="fs-11 fw-medium text-muted">Hızlı Erişim</p>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="/" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Dashboard</a>
                                    <a href="/chat" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Mesajlar</a>
                                    <a href="/bulk-send" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Toplu Mesaj</a>
                                    <a href="/history" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Geçmiş</a>
                                    <a href="/groups" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Gruplar</a>
                                    <a href="/auto-replies" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Oto. Cevap</a>
                                    <a href="/blacklist" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Kara Liste</a>
                                    <a href="/settings-api" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">Ayarlar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--! [End] Header Search !-->
                <!--! [Start] Full Screen !-->
                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                            <i class="feather-maximize maximize"></i>
                            <i class="feather-minimize minimize"></i>
                        </a>
                    </div>
                </div>
                <!--! [End] Full Screen !-->
                <!--! [Start] Dark/Light Theme !-->
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>
                <!--! [End] Dark/Light Theme !-->
                <!--! [Start] Notifications !-->
                <div class="dropdown nxl-h-item">
                    <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        <span class="badge bg-danger nxl-h-badge" id="headerNotifBadge" style="display:none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                        <div class="d-flex justify-content-between align-items-center notifications-head">
                            <h6 class="fw-bold text-dark mb-0">Bildirimler</h6>
                            <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" onclick="markAllNotificationsRead()">
                                <i class="feather-check"></i>
                                <span>Tümünü Okundu İşaretle</span>
                            </a>
                        </div>
                        <div id="headerNotifList" class="px-3 py-2">
                            <div class="text-center text-muted py-3">
                                <i class="feather-bell-off fs-3 d-block mb-2"></i>
                                <span class="fs-12">Bildirim yok</span>
                            </div>
                        </div>
                        <div class="text-center notifications-footer">
                            <a href="/chat" class="fs-13 fw-semibold text-dark">Tüm Mesajları Gör</a>
                        </div>
                    </div>
                </div>
                <!--! [End] Notifications !-->
                <!--! [Start] User Dropdown !-->
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <div class="d-flex align-items-center">
                            <div class="avatar-text avatar-md bg-primary text-white rounded-circle me-0" id="headerUserAvatar" style="width:36px;height:36px;font-size:14px;display:flex;align-items:center;justify-content:center;">
                                U
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="avatar-text avatar-md bg-primary text-white rounded-circle" style="width:40px;height:40px;font-size:16px;display:flex;align-items:center;justify-content:center;min-width:40px;" id="headerUserAvatarLg">
                                    U
                                </div>
                                <div class="ms-3">
                                    <h6 class="text-dark mb-0" id="headerUserName">
                                        Kullanıcı
                                    </h6>
                                    <span class="fs-12 fw-medium text-muted" id="headerUserEmail"></span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/" class="dropdown-item">
                            <i class="feather-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/profile" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profil</span>
                        </a>
                        <a href="/settings-api" class="dropdown-item">
                            <i class="feather-settings"></i>
                            <span>API Ayarları</span>
                        </a>
                        <a href="/chat" class="dropdown-item">
                            <i class="feather-message-circle"></i>
                            <span>Mesajlar</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" onclick="logout()" class="dropdown-item">
                            <i class="feather-log-out"></i>
                            <span>Çıkış Yap</span>
                        </a>
                    </div>
                </div>
                <!--! [End] User Dropdown !-->
            </div>
        </div>
        <!--! [End] Header Right !-->
    </div>
</header>

<script>
// Header - Kullanıcı bilgilerini yükle
async function loadHeaderUser() {
    try {
        const resp = await fetch('/api/auth/me', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return;
        const data = await resp.json();
        if (!data.success) return;
        const u = data.user;
        const initial = (u.full_name || u.username || 'U').charAt(0).toUpperCase();
        const avatarEl = document.getElementById('headerUserAvatar');
        const avatarLgEl = document.getElementById('headerUserAvatarLg');
        const nameEl = document.getElementById('headerUserName');
        const emailEl = document.getElementById('headerUserEmail');
        if (avatarEl) avatarEl.textContent = initial;
        if (avatarLgEl) avatarLgEl.textContent = initial;
        if (nameEl) {
            let badge = u.is_admin
                ? '<span class="badge bg-soft-danger text-danger ms-1">Admin</span>'
                : '<span class="badge bg-soft-info text-info ms-1">Kullanıcı</span>';
            nameEl.innerHTML = (u.full_name || u.username) + ' ' + badge;
        }
        if (emailEl) emailEl.textContent = u.email || '';
    } catch(e) {}
}

// Header Bildirimler - Okunmamış mesajları göster
let _headerNotifInterval = null;

async function loadHeaderNotifications() {
    try {
        const resp = await fetch('/api/user/conversations', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return;
        const data = await resp.json();
        if (!data.success) return;

        const unread = data.conversations.filter(c => c.unread_count > 0);
        const totalUnread = unread.reduce((sum, c) => sum + c.unread_count, 0);

        const badge = document.getElementById('headerNotifBadge');
        const list = document.getElementById('headerNotifList');
        if (!badge || !list) return;

        if (totalUnread > 0) {
            badge.textContent = totalUnread > 99 ? '99+' : totalUnread;
            badge.style.display = '';

            const maxShow = 5;
            const shown = unread.slice(0, maxShow);
            let html = '<div class="nxl-notifications-scroll" style="max-height:280px;overflow-y:auto;">';
            shown.forEach(c => {
                const phone = c.phone_number;
                const preview = getNotifPreview(c);
                const timeAgo = getTimeAgo(c.last_message_at);
                html += `
                    <a href="/chat?phone=${phone}" class="d-flex align-items-start py-2 px-1 border-bottom text-decoration-none header-notif-item" style="gap:10px;">
                        <div class="avatar-text bg-soft-success text-success rounded-circle" style="width:36px;height:36px;min-width:36px;font-size:14px;display:flex;align-items:center;justify-content:center;">
                            <i class="feather-message-circle" style="font-size:16px;"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-dark fs-13">${phone}</span>
                                <span class="fs-11 text-muted">${timeAgo}</span>
                            </div>
                            <p class="fs-12 text-muted mb-0" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${preview}</p>
                            <span class="badge bg-danger fs-10">${c.unread_count} yeni mesaj</span>
                        </div>
                    </a>`;
            });
            if (unread.length > maxShow) {
                html += `<div class="text-center py-2"><span class="fs-11 text-muted">+${unread.length - maxShow} konuşma daha</span></div>`;
            }
            html += '</div>';
            list.innerHTML = html;
        } else {
            badge.style.display = 'none';
            list.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="feather-bell-off fs-3 d-block mb-2"></i>
                    <span class="fs-12">Okunmamış mesaj yok</span>
                </div>`;
        }
    } catch (e) {}
}

function getNotifPreview(conv) {
    if (!conv.last_message_text && conv.last_message_type) {
        const typeMap = {image:'Resim', video:'Video', audio:'Ses', document:'Dosya', sticker:'Çıkartma'};
        return typeMap[conv.last_message_type] || conv.last_message_type;
    }
    return conv.last_message_text ? conv.last_message_text.substring(0, 50) : 'Yeni mesaj';
}

function getTimeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = (Date.now() - new Date(dateStr).getTime()) / 1000;
    if (diff < 60) return 'Az önce';
    if (diff < 3600) return Math.floor(diff / 60) + ' dk';
    if (diff < 86400) return Math.floor(diff / 3600) + ' sa';
    return Math.floor(diff / 86400) + ' gün';
}

async function markAllNotificationsRead() {
    try {
        const resp = await fetch('/api/user/conversations', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return;
        const data = await resp.json();
        if (!data.success) return;
        const unread = data.conversations.filter(c => c.unread_count > 0);
        for (const c of unread) {
            await fetch(`/api/user/messages/${c.phone_number}/mark-replied`, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        }
        loadHeaderNotifications();
    } catch (e) {}
}

async function logout() {
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

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
    loadHeaderUser();
    loadHeaderNotifications();
    _headerNotifInterval = setInterval(loadHeaderNotifications, 30000);
});
</script>