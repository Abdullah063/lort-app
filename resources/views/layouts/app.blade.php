<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp API Panel</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/favicon.ico')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/css/vendors.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/css/daterangepicker.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/theme.min.css')}}" />
    <style>
        /* Modal blur/overlay fix - tema CSS override */
        body.modal-open .nxl-container,
        body.modal-open .nxl-header,
        body.modal-open .nxl-navigation,
        body.modal-open .nxl-content,
        body.modal-open main,
        .nxl-container,
        .nxl-header,
        .nxl-navigation {
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        body {
            filter: none !important;
            -webkit-filter: none !important;
        }
        .modal-backdrop {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        .modal {
            filter: none !important;
            -webkit-filter: none !important;
        }
        /* Tema overlay'larını gizle */
        .nxl-backdrop {
            display: none !important;
        }
        /* page-header: header-wrapper'ın hemen altında sabit dursun */
        .nxl-content .page-header {
            position: sticky;
            top: 80px;
            z-index: 1020;
            background: #f5f6fa;
            margin-top: 0 !important;
            padding-top: 15px !important;
            padding-bottom: 10px !important;
            margin-bottom: 10px !important;
        }
    </style>
</head>

<body>

    @include('includes.navbar')
    @include('includes.header')
    <main class="nxl-container">
        @yield('content')
    </main>

    @include('includes.customizer')

    <script src="{{asset('assets/vendors/js/vendors.min.js')}}"></script>
    <script src="{{asset('assets/vendors/js/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/vendors/js/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/vendors/js/circle-progress.min.js')}}"></script>
    <script src="{{asset('assets/js/common-init.min.js')}}"></script>
    <script src="{{asset('assets/js/theme-customizer-init.min.js')}}"></script>
    <script>
    // Blur/filter temizleyici - tüm elementlerden filter kaldır
    (function() {
        function removeAllFilters() {
            var selectors = [
                'body', '.nxl-container', '.nxl-header', '.nxl-navigation',
                '.nxl-content', 'main', '.page-wrapper', '.main-content'
            ];
            selectors.forEach(function(sel) {
                document.querySelectorAll(sel).forEach(function(el) {
                    if (el.style.filter) el.style.filter = '';
                    if (el.style.webkitFilter) el.style.webkitFilter = '';
                    if (el.style.backdropFilter) el.style.backdropFilter = '';
                });
            });
            // Tema overlay elementlerini kaldır
            document.querySelectorAll('.nxl-backdrop, .pcoded-overlay, .menu-overlay').forEach(function(el) {
                el.style.display = 'none';
            });
        }

        // Modal açılınca ve kapanınca temizle
        document.addEventListener('shown.bs.modal', function() {
            removeAllFilters();
            setTimeout(removeAllFilters, 50);
            setTimeout(removeAllFilters, 200);
        });
        document.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            removeAllFilters();
        });

        // Sayfa yüklendiğinde ve periyodik olarak
        document.addEventListener('DOMContentLoaded', function() {
            removeAllFilters();
            setInterval(removeAllFilters, 1000);
        });

        // MutationObserver - style değişikliklerini yakala
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.type === 'attributes' && m.attributeName === 'style') {
                    var el = m.target;
                    if (el.style.filter && el.style.filter !== 'none') {
                        el.style.filter = 'none';
                    }
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            ['body', '.nxl-container', 'main'].forEach(function(sel) {
                var el = sel === 'body' ? document.body : document.querySelector(sel);
                if (el) observer.observe(el, { attributes: true, attributeFilter: ['style'] });
            });
        });
    })();
    </script>
    @yield('scripts')

</body>

</html>