<?= $this->include('customer/partials/header') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
$isReturnFlow = ! empty($order) && function_exists('is_return_refund_status') && is_return_refund_status((string) ($order['delivery_status'] ?? ''));
?>

<div class="order-details-shell">
    <?= view('partials/order_details_styles') ?>

    <div class="orders-header">
        <a href="<?= site_url('customer/orders') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <h1><?= $isReturnFlow ? 'Return / Refund Details' : 'Order Details' ?></h1>
        <p><?= $isReturnFlow ? 'Review return/refund information and status updates.' : 'Review order information and delivery progress.' ?></p>
    </div>

    <?php if (! empty($order)): ?>
        <?= view('partials/order_details_card', [
            'audience' => 'customer',
            'order' => $order,
            'items' => $items ?? [],
            'return_meta' => $return_meta ?? [],
            'tracking_info' => $tracking_info ?? [],
            'can_cancel' => $can_cancel ?? false,
            'can_request_return' => $can_request_return ?? false,
            'return_request_message' => $return_request_message ?? '',
        ]) ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Order Not Found</h3>
            <p>The order you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="<?= site_url('customer/orders') ?>" class="btn-checkout btn-action">Back to Orders</a>
        </div>
    <?php endif; ?>
</div>

<?php if (! empty($order) && ! $isReturnFlow): ?>
<script>
let trackingMap;
let riderMarker;
let destinationMarker;
let storeMarker;
let riderMotorIcon;
let riderAnimationFrame = null;

function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function initTrackingMap() {
    const el = document.getElementById('customer_tracking_map');
    if (!el) return;
    trackingMap = L.map('customer_tracking_map').setView([6.1164, 125.1716], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(trackingMap);
    riderMotorIcon = L.divIcon({
        className: 'rider-motor-icon',
        html: '<div style="font-size:22px;line-height:22px;">🏍️</div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });
}

function toggleTrackingMapFullscreen() {
    const wrap = document.getElementById('tracking_map_wrap');
    if (!wrap) return;
    if (!document.fullscreenElement) {
        wrap.requestFullscreen?.().then(() => setTimeout(() => trackingMap?.invalidateSize(), 150));
        return;
    }
    document.exitFullscreen?.().then(() => setTimeout(() => trackingMap?.invalidateSize(), 150));
}

function animateRiderMarker(toLat, toLng, durationMs = 1200) {
    if (!riderMarker) {
        riderMarker = L.marker([toLat, toLng], { icon: riderMotorIcon }).addTo(trackingMap).bindPopup('Rider location');
        return;
    }
    const from = riderMarker.getLatLng();
    const startLat = from.lat;
    const startLng = from.lng;
    const startTs = performance.now();
    if (riderAnimationFrame) cancelAnimationFrame(riderAnimationFrame);
    const step = (now) => {
        const t = Math.min(1, (now - startTs) / durationMs);
        riderMarker.setLatLng([startLat + (toLat - startLat) * t, startLng + (toLng - startLng) * t]);
        if (t < 1) riderAnimationFrame = requestAnimationFrame(step);
    };
    riderAnimationFrame = requestAnimationFrame(step);
}

function refreshTracking() {
    fetch(`<?= site_url('dashboard/orderTracking/' . (int) ($order['id'] ?? 0)) ?>?t=${Date.now()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.tracking || !trackingMap) return;
        const t = data.tracking;
        const statusEl = document.getElementById('tracking_status_text');
        const metaEl = document.getElementById('tracking_meta');
        if (!statusEl || !metaEl) return;

        if (t.phase === 'pickup') {
            statusEl.textContent = 'Rider on the way to store for pickup';
        } else if (t.delivery_address) {
            statusEl.textContent = `Delivery Address: ${t.delivery_address}`;
        }

        if (t.store_latitude && t.store_longitude) {
            const s = [t.store_latitude, t.store_longitude];
            if (!storeMarker) storeMarker = L.marker(s).addTo(trackingMap).bindPopup(`Pickup: ${t.store_address || 'Store'}`);
            else storeMarker.setLatLng(s);
        }

        if (t.delivery_latitude && t.delivery_longitude) {
            const d = [t.delivery_latitude, t.delivery_longitude];
            if (!destinationMarker) destinationMarker = L.marker(d).addTo(trackingMap).bindPopup(t.delivery_address || 'Destination');
            else destinationMarker.setLatLng(d);
            trackingMap.setView(d, 14);
        }

        if (t.status === 'to_receive' && t.rider_latitude && t.rider_longitude && t.delivery_latitude && t.delivery_longitude) {
            animateRiderMarker(t.rider_latitude, t.rider_longitude);
            const km = haversineKm(t.rider_latitude, t.rider_longitude, t.delivery_latitude, t.delivery_longitude);
            const eta = Math.max(2, Math.round((km / 25) * 60));
            statusEl.textContent = `Out for Delivery | Rider: ${t.rider?.name || 'Assigned rider'}`;
            metaEl.textContent = `Distance: ${km.toFixed(2)} km | ETA: ~${eta} min`;
            trackingMap.fitBounds(L.latLngBounds([[t.rider_latitude, t.rider_longitude], [t.delivery_latitude, t.delivery_longitude]]).pad(0.25));
        } else if (t.status === 'to_receive') {
            metaEl.textContent = 'Waiting for live rider GPS location...';
        } else {
            metaEl.textContent = `Status: ${String(t.status || '').replaceAll('_', ' ')}`;
        }
    }).catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    const liveMap = document.getElementById('customer_tracking_map');
    if (liveMap) {
        initTrackingMap();
        refreshTracking();
        setInterval(refreshTracking, 3000);
        document.getElementById('tracking_map_fullscreen_btn')?.addEventListener('click', toggleTrackingMapFullscreen);
        return;
    }

    const staticMapEl = document.getElementById('order_details_map');
    <?php if (! empty($order['delivery_latitude']) && ! empty($order['delivery_longitude'])): ?>
    if (staticMapEl) {
        const lat = <?= (float) $order['delivery_latitude'] ?>;
        const lng = <?= (float) $order['delivery_longitude'] ?>;
        const map = L.map('order_details_map').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup('Delivery location');
        <?php if (! empty($order['store_latitude']) && ! empty($order['store_longitude'])): ?>
        L.marker([<?= (float) $order['store_latitude'] ?>, <?= (float) $order['store_longitude'] ?>]).addTo(map).bindPopup('Store');
        <?php endif; ?>
    }
    <?php endif; ?>
});
</script>
<?php endif; ?>

<?= $this->include('customer/partials/footer') ?>
