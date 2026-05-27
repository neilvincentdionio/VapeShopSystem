    </div>
<script>
(() => {
    const path = window.location.pathname.toLowerCase();
    const shouldWatch = path.includes('/rider/dashboard')
        || path.includes('/rider/deliveries')
        || path.includes('/rider/returns');
    if (!shouldWatch) {
        return;
    }

    const endpoint = '<?= site_url('dashboard/live-update-token') ?>';
    let lastToken = null;
    let inFlight = false;
    let pendingReload = false;

    function isReloadSuspended() {
        if (typeof window.__suspendLiveReload === 'function') {
            try {
                return !!window.__suspendLiveReload();
            } catch (e) {
                console.debug('Suspend hook failed:', e);
            }
        }
        return false;
    }

    async function checkForUpdates() {
        if (document.hidden || inFlight) {
            return;
        }

        if (pendingReload && !isReloadSuspended()) {
            if (typeof window.__beforeLiveReload === 'function') {
                try {
                    window.__beforeLiveReload();
                } catch (e) {
                    console.debug('Before reload hook failed:', e);
                }
            }
            pendingReload = false;
            window.location.reload();
            return;
        }

        inFlight = true;
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (!data || !data.success || !data.token) {
                return;
            }

            if (lastToken === null) {
                lastToken = data.token;
                return;
            }

            if (data.token !== lastToken) {
                if (isReloadSuspended()) {
                    pendingReload = true;
                    lastToken = data.token;
                    return;
                }

                if (typeof window.__beforeLiveReload === 'function') {
                    try {
                        window.__beforeLiveReload();
                    } catch (e) {
                        console.debug('Before reload hook failed:', e);
                    }
                }
                pendingReload = false;
                window.location.reload();
            }
        } catch (error) {
            console.debug('Live update check failed:', error);
        } finally {
            inFlight = false;
        }
    }

    window.__triggerLiveReloadCheck = checkForUpdates;
    setTimeout(checkForUpdates, 2000);
    setInterval(checkForUpdates, 7000);
    window.addEventListener('focus', checkForUpdates);
})();
</script>
</body>
</html>
