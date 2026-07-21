<script>
    (function () {
        try {
            if (window.self !== window.top || window.sessionStorage.getItem('adasiSplashShown') === '1') {
                document.documentElement.classList.add('adasi-splash-skip');
            }
        } catch (error) {
            // Keep the splash visible when session storage is unavailable.
        }
    })();
</script>

<div id="adasi-splash" role="img" aria-label="ADASI">
    <div class="adasi-splash-text">
        <span style="animation-delay: 0.0s" aria-hidden="true">A</span>
        <span style="animation-delay: 0.1s" aria-hidden="true">D</span>
        <span style="animation-delay: 0.2s" aria-hidden="true">A</span>
        <span style="animation-delay: 0.3s" aria-hidden="true">S</span>
        <span style="animation-delay: 0.4s" aria-hidden="true">I</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var splashScreen = document.getElementById('adasi-splash');

        if (!splashScreen) {
            return;
        }

        if (window.self !== window.top) {
            splashScreen.style.display = 'none';
            return;
        }

        try {
            if (window.sessionStorage.getItem('adasiSplashShown') === '1') {
                splashScreen.style.display = 'none';
                return;
            }

            window.sessionStorage.setItem('adasiSplashShown', '1');
        } catch (error) {
            // The splash should still run and dismiss if storage is blocked.
        }

        window.setTimeout(function () {
            splashScreen.classList.add('adasi-splash-hidden');

            window.setTimeout(function () {
                splashScreen.style.display = 'none';
            }, 500);
        }, 1800);
    });
</script>
