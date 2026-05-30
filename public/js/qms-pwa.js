/**
 * COMELEC QMS — app-mode gate (launcher / PWA standalone only).
 * Regular browser tabs are blocked. start_comelec_qms.bat opens with ?qms_app=1.
 */
(function () {
    var deferredInstall = null;

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.matchMedia('(display-mode: fullscreen)').matches
            || window.matchMedia('(display-mode: minimal-ui)').matches
            || window.navigator.standalone === true;
    }

    function isLauncherApp() {
        try {
            if (/[?&]qms_app=1(?:&|$)/.test(location.search)) {
                sessionStorage.setItem('qms_app', '1');
                return true;
            }
            return sessionStorage.getItem('qms_app') === '1';
        } catch (e) {
            return /[?&]qms_app=1(?:&|$)/.test(location.search);
        }
    }

    function isAllowedAppMode() {
        return isStandalone() || isLauncherApp();
    }

    function showGate() {
        var gate = document.getElementById('pwaGate');
        if (gate) gate.classList.remove('hidden');
        document.documentElement.classList.add('pwa-blocked');
    }

    function hideGate() {
        var gate = document.getElementById('pwaGate');
        if (gate) gate.classList.add('hidden');
        document.documentElement.classList.remove('pwa-blocked');
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredInstall = e;
        var btn = document.getElementById('pwaInstallBtn');
        if (btn) btn.classList.remove('hidden');
    });

    document.addEventListener('DOMContentLoaded', function () {
        registerServiceWorker();

        if (isAllowedAppMode()) {
            hideGate();
            return;
        }

        showGate();

        var installBtn = document.getElementById('pwaInstallBtn');
        if (installBtn) {
            installBtn.addEventListener('click', function () {
                alert('Please close this browser tab and run start_comelec_qms.bat to open QMS in app mode.');
            });
        }

        window.matchMedia('(display-mode: standalone)').addEventListener('change', function (e) {
            if (e.matches || isLauncherApp()) hideGate();
        });
    });

    if (!isAllowedAppMode()) {
        document.documentElement.classList.add('pwa-blocked');
    }
})();
