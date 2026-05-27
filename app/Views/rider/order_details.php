<?= $this->include('rider/partials/header') ?>
<?php helper('return_refund'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
$isReturnFlow = ! empty($order) && function_exists('is_return_refund_status') && is_return_refund_status((string) ($order['delivery_status'] ?? ''));
$backUrl = ! empty($is_return_pickup) ? site_url('rider/returns') : site_url('rider/deliveries');
$backLabel = ! empty($is_return_pickup) ? 'Return Pickups' : 'Deliveries';
?>

<div class="order-details-shell">
    <?= view('partials/order_details_styles') ?>

    <div class="orders-header">
        <a href="<?= $backUrl ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to <?= esc($backLabel) ?>
        </a>
        <h1><?= $isReturnFlow ? 'Return / Refund Details' : 'Order Details' ?></h1>
        <p>Review order information and delivery progress.</p>
    </div>

    <?php if (! empty($order)): ?>
        <?= view('partials/order_details_card', [
            'audience' => 'rider',
            'order' => $order,
            'items' => $items ?? [],
            'return_meta' => $return_meta ?? [],
            'is_return_pickup' => $is_return_pickup ?? false,
            'map_element_id' => 'rider_delivery_map',
        ]) ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Order Not Found</h3>
            <p>This order is not available.</p>
            <a href="<?= $backUrl ?>" class="btn-checkout btn-action">Back to <?= esc($backLabel) ?></a>
        </div>
    <?php endif; ?>
</div>

<?php if (! empty($order)): ?>
<script>
function customerCancelledAtDelivery(orderId) {
    const notes = prompt(
        'The customer cancelled or refused the order in person at delivery.\n\nAdd notes (optional):',
        'Customer cancelled at delivery location.'
    );
    if (notes === null) return;
    if (!confirm('Mark this order as CANCELLED because the customer refused it face-to-face? Stock will be restored.')) return;

    const params = new URLSearchParams();
    params.set('order_id', String(orderId));
    params.set('status', 'customer_cancelled_at_delivery');
    const trimmed = (notes || '').trim();
    if (trimmed !== '') params.set('cancel_reason', trimmed);

    fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Order cancelled.');
            window.location.href = '<?= site_url('rider/deliveries') ?>';
        } else {
            alert(data.message || 'Failed to cancel order');
        }
    })
    .catch(() => alert('An error occurred while cancelling the order'));
}

<?php if (! empty($order)): ?>
const status = <?= json_encode((string) ($order['delivery_status'] ?? '')) ?>;
let customerLat = <?= json_encode(!empty($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null) ?>;
let customerLng = <?= json_encode(!empty($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null) ?>;
let storeLat = <?= json_encode(!empty($order['store_latitude']) ? (float) $order['store_latitude'] : null) ?>;
let storeLng = <?= json_encode(!empty($order['store_longitude']) ? (float) $order['store_longitude'] : null) ?>;
const storeAddress = <?= json_encode((string) ($order['store_address'] ?? 'Store')) ?>;
const customerAddress = <?= json_encode((string) ($order['delivery_address'] ?? $order['shipping_address'] ?? 'Customer')) ?>;
const toStore = ['ready_for_pickup', 'accepted_by_rider'].includes(status);

const mapEl = document.getElementById('rider_delivery_map');

function isValidCoord(v) {
    return typeof v === 'number' && Number.isFinite(v);
}

async function geocodeAddress(addr) {
    const q = (addr || '').trim();
    if (q.length < 4) return null;

    // Nominatim (OpenStreetMap) geocoding. Only used when DB coordinates are missing.
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`;
    const res = await fetch(url).catch(() => null);
    if (!res) return null;

    const json = await res.json().catch(() => null);
    if (!Array.isArray(json) || json.length === 0) return null;

    const lat = parseFloat(json[0]?.lat);
    const lng = parseFloat(json[0]?.lon);
    return isValidCoord(lat) && isValidCoord(lng) ? { lat, lng } : null;
}

if (mapEl) {
    (async function initRiderDeliveryMap() {
        let destLat = toStore ? storeLat : customerLat;
        let destLng = toStore ? storeLng : customerLng;

        // If the "toStore/toCustomer" destination coords are missing, try to geocode the address.
        if (!isValidCoord(destLat) || !isValidCoord(destLng)) {
            const addressToUse = toStore ? storeAddress : customerAddress;
            const geo = await geocodeAddress(addressToUse);
            if (geo) {
                destLat = geo.lat;
                destLng = geo.lng;
            }
        }

        // Final fallback: if customer coords are missing but store coords exist (or vice versa), show what we can.
        if (!isValidCoord(destLat) || !isValidCoord(destLng)) {
            destLat = isValidCoord(storeLat) ? storeLat : customerLat;
            destLng = isValidCoord(storeLng) ? storeLng : customerLng;
        }

        if (!isValidCoord(destLat) || !isValidCoord(destLng)) return;

        const map = L.map('rider_delivery_map').setView([destLat, destLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([destLat, destLng]).addTo(map)
            .bindPopup(toStore ? `Pickup: ${storeAddress}` : `Delivery: ${customerAddress}`);

        let routeLine = null;

    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
        return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function formatManeuver(step) {
        const m = step?.maneuver || {};
        const type = String(m.type || '').replaceAll('_', ' ').trim();
        const mod = String(m.modifier || '').replaceAll('_', ' ').trim();
        const name = String(step?.name || '').trim();
        const base = type ? type.charAt(0).toUpperCase() + type.slice(1) : 'Continue';
        if (mod && name) return `${base} ${mod} to ${name}`;
        if (name) return `${base} to ${name}`;
        if (mod) return `${base} ${mod}`;
        return base;
    }

    async function drawRouteGuide(fromLat, fromLng, toLat, toLng) {
        const stepsEl = document.getElementById('route_steps');
        const metaEl = document.getElementById('route_meta');
        if (stepsEl) stepsEl.textContent = 'Loading road guide...';
        try {
            const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&steps=true`;
            const res = await fetch(url);
            const data = await res.json();
            if (!data?.routes?.length) throw new Error('No route');
            const route = data.routes[0];
            const coords = (route.geometry?.coordinates || []).map(c => [c[1], c[0]]);
            if (routeLine) map.removeLayer(routeLine);
            routeLine = L.polyline(coords, { color: '#1976d2', weight: 5, opacity: 0.9 }).addTo(map);
            const steps = (route.legs?.[0]?.steps || []).slice(0, 12);
            if (stepsEl) {
                stepsEl.innerHTML = steps.length
                    ? steps.map((s, i) => `${i + 1}. ${formatManeuver(s)} (${Math.max(1, Math.round(s.distance || 0))}m)`).join('<br>')
                    : 'Road guide unavailable.';
            }
            if (metaEl) {
                const km = haversineKm(fromLat, fromLng, toLat, toLng);
                metaEl.textContent = `Estimated distance: ${km.toFixed(2)} km | ETA: ~${Math.max(3, Math.round((km / 25) * 60))} min`;
            }
        } catch (e) {
            if (routeLine) map.removeLayer(routeLine);
            routeLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], { color: '#27c56f', dashArray: '8 8', weight: 4 }).addTo(map);
            if (stepsEl) stepsEl.textContent = 'Road routing unavailable. Showing direct line.';
        }
    }

    function pushRiderLocation(lat, lng) {
        const body = `order_id=<?= (int) ($order['id'] ?? 0) ?>&rider_latitude=${encodeURIComponent(lat)}&rider_longitude=${encodeURIComponent(lng)}`;
        fetch('<?= site_url('dashboard/updateRiderLocation') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        }).catch(() => {});
    }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const rLat = pos.coords.latitude;
                const rLng = pos.coords.longitude;
                L.marker([rLat, rLng]).addTo(map).bindPopup('Your location');
                drawRouteGuide(rLat, rLng, destLat, destLng);
                pushRiderLocation(rLat, rLng);
            });

            if (status === 'to_receive') {
                navigator.geolocation.watchPosition((pos) => {
                    pushRiderLocation(pos.coords.latitude, pos.coords.longitude);
                    drawRouteGuide(pos.coords.latitude, pos.coords.longitude, destLat, destLng);
                }, () => {}, { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 });
            }
        }
    })();
}
<?php endif; ?>
</script>
<?php endif; ?>

<script>
(function () {
    function copyText(text) {
        const value = (text || '').trim();
        if (!value) {
            alert('No contact number to copy.');
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value).then(function () {
                alert('Contact number copied.');
            }).catch(function () {
                fallbackCopy(value);
            });
            return;
        }
        fallbackCopy(value);
    }

    function fallbackCopy(value) {
        const area = document.createElement('textarea');
        area.value = value;
        document.body.appendChild(area);
        area.select();
        try {
            document.execCommand('copy');
            alert('Contact number copied.');
        } catch (e) {
            alert('Unable to copy. Select the number manually.');
        }
        document.body.removeChild(area);
    }

    document.querySelectorAll('.rider-copy-contact-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            copyText(btn.getAttribute('data-copy-text') || '');
        });
    });

    const contactEl = document.getElementById('rider_contact_number');
    if (contactEl) {
        contactEl.addEventListener('dblclick', function () {
            copyText(contactEl.textContent || '');
        });
        contactEl.title = 'Double-click to copy';
    }
})();
</script>

<?= $this->include('rider/partials/footer') ?>
