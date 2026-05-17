    </div>
<script>
(() => {
    const path = window.location.pathname.toLowerCase();
    const shouldWatch = path.includes('/customer/orders') || path.includes('/customer/order-details') || path.includes('/customer/receipt');
    if (!shouldWatch) {
        return;
    }

    const endpoint = '<?= site_url('dashboard/live-update-token') ?>';
    let lastToken = null;
    let inFlight = false;

    async function checkForUpdates() {
        if (document.hidden || inFlight) {
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
                window.location.reload();
            }
        } catch (error) {
            console.debug('Live update check failed:', error);
        } finally {
            inFlight = false;
        }
    }

    setTimeout(checkForUpdates, 2000);
    setInterval(checkForUpdates, 7000);
    window.addEventListener('focus', checkForUpdates);
})();
</script>
</body>
</html>
