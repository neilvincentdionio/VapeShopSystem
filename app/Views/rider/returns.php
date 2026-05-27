<?= $this->include('rider/partials/header') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<?php helper('return_refund'); ?>

<style>
    .page-header,
    .returns-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .page-header { padding: 2rem; margin-bottom: 2rem; }
    .page-header h1 { font-size: 1.8rem; color: #333; margin-bottom: .6rem; }
    .page-header p { color: #666; line-height: 1.6; }
    .returns-panel { overflow-x: auto; }

    .returns-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .returns-table th,
    .returns-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: top;
    }

    .returns-table th {
        background: #f8f9fa;
        color: #666;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .muted { color: #666; font-size: .85rem; margin-top: .25rem; }
    .payout-chip {
        display: inline-block;
        margin-top: .35rem;
        padding: .25rem .55rem;
        border-radius: 8px;
        background: #eef2ff;
        color: #3730a3;
        font-size: .78rem;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 600;
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }

    .status-badge.is-done {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #4caf50;
    }

    .status-badge.is-complete {
        background: #e3f2fd;
        color: #1565c0;
        border-color: #42a5f5;
    }

    tr.is-complete-row { opacity: .92; }

    .action-stack { display: flex; flex-wrap: wrap; gap: .5rem; }
    .action-btn {
        border: 0;
        border-radius: 999px;
        padding: .5rem .85rem;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #27c56f;
        color: #fff;
    }

    .action-btn.btn-outline {
        background: #fff;
        color: #333;
        border: 1px solid #d7dce1;
    }

    .action-btn.btn-scan { background: #1976d2; }
    .action-btn.btn-accept { background: #f59e0b; color: #fff; }
    .empty-state { padding: 2.5rem 1rem; text-align: center; color: #666; }
    tr.is-highlighted { background: rgba(39, 197, 111, 0.08); }

    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background: #fff;
        margin: 4% auto;
        border-radius: 12px;
        width: 92%;
        max-width: 860px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        overflow: hidden;
    }

    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body { padding: 1.25rem; }
    .close { font-size: 1.6rem; cursor: pointer; color: #888; }
    .form-group { margin-bottom: .85rem; }
    .form-group label { display: block; font-weight: 600; margin-bottom: .35rem; font-size: .88rem; }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: .55rem .65rem;
        border: 1px solid #d7dce1;
        border-radius: 8px;
        font: inherit;
    }

    #returnMapCanvas { height: 380px; border: 1px solid #e0e0e0; border-radius: 8px; }
    #returnQrReader { width: 100%; max-width: 420px; margin: 0 auto; }
    .scan-manual { margin-top: .75rem; display: flex; gap: .5rem; }
    .scan-manual input { flex: 1; }
</style>

<?php $highlightOrderId = (int) (service('request')->getGet('order_id') ?? 0); ?>

<section class="page-header">
    <h1>Return Pickups</h1>
    <p>Accept the pickup first, then view the map and scan the customer return QR code. Completed refunds stay here until you clear them from Profile.</p>
</section>

<section class="returns-panel">
    <?php if (empty($returns)): ?>
        <div class="empty-state">No return pickups assigned to you right now.</div>
    <?php else: ?>
        <table class="returns-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Pickup Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($returns as $item): ?>
                    <?php
                        $orderId = (int) ($item['id'] ?? 0);
                        $status = (string) ($item['delivery_status'] ?? '');
                        $address = trim((string) ($item['shipping_address'] ?? ''));
                        if ($address === '') {
                            $address = trim((string) (($item['customer']['address'] ?? '') ?: 'No pickup address provided'));
                        }
                        $returnMeta = parse_return_meta(
                            (string) ($item['shipment_notes'] ?? ''),
                            (string) ($item['delivery_notes'] ?? '')
                        ) ?? [];
                        $riderAccepted = rider_accepted_return_pickup($returnMeta);
                        $isComplete = $status === 'return_refund';
                    ?>
                    <tr id="return-<?= $orderId ?>" class="<?= $highlightOrderId === $orderId ? 'is-highlighted' : '' ?><?= $isComplete ? ' is-complete-row' : '' ?>">
                        <td>
                            <strong><?= esc($item['reference_number'] ?? ('Order #' . $orderId)) ?></strong>
                            <div class="muted"><?= esc(date('M d, Y', strtotime((string) ($item['created_at'] ?? 'now')))) ?></div>
                        </td>
                        <td>
                            <?= esc($item['customer']['name'] ?? 'Customer') ?>
                            <div class="muted"><?= esc($item['contact_number'] ?? ($item['customer']['phone'] ?? '')) ?></div>
                        </td>
                        <td class="muted"><?= esc($address) ?></td>
                        <td>
                            <span class="status-badge <?= $isComplete ? 'is-complete' : ($status === 'return_picked_up' ? 'is-done' : '') ?>">
                                <?php if ($isComplete): ?>
                                    Complete
                                <?php elseif ($status === 'return_picked_up'): ?>
                                    Picked Up
                                <?php elseif ($riderAccepted): ?>
                                    Ready to Scan QR
                                <?php else: ?>
                                    Awaiting Your Approval
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-stack">
                                <?php if (! $isComplete): ?>
                                    <button type="button" class="action-btn btn-outline" onclick="openReturnMap(<?= $orderId ?>)">
                                        <i class="fas fa-map"></i> View Map
                                    </button>
                                <?php endif; ?>
                                <a class="action-btn btn-outline" href="<?= site_url('rider/order-details/' . $orderId) ?>">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                                <?php if ($status === 'return_approved' && ! $riderAccepted): ?>
                                    <button type="button" class="action-btn btn-accept" onclick="acceptReturnPickup(<?= $orderId ?>)">
                                        <i class="fas fa-check"></i> Accept Pickup
                                    </button>
                                <?php elseif ($status === 'return_approved' && $riderAccepted): ?>
                                    <button type="button" class="action-btn btn-scan" onclick="openReturnScanModal(<?= $orderId ?>)">
                                        <i class="fas fa-qrcode"></i> Scan QR &amp; Pick Up
                                    </button>
                                <?php elseif ($status === 'return_picked_up'): ?>
                                    <span class="muted"><i class="fas fa-hourglass-half"></i> Waiting for admin refund</span>
                                <?php elseif ($isComplete): ?>
                                    <span class="muted"><i class="fas fa-check-circle"></i> Refund completed</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<div id="returnMapModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Return Pickup Map</h3>
            <span class="close" onclick="closeReturnMap()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="returnMapLabel" style="font-weight:700;margin-bottom:.5rem;"></div>
            <div id="returnMapCanvas"></div>
            <div id="returnMapMeta" class="muted" style="margin-top:.6rem;"></div>
            <div id="returnMapDirections" style="margin-top:.6rem; max-height:180px; overflow:auto; border:1px solid #e0e0e0; border-radius:8px; padding:.55rem .65rem; font-size:.9rem; color:#333;"></div>
        </div>
    </div>
</div>

<div id="returnScanModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3>Scan Return QR</h3>
            <span class="close" onclick="closeReturnScanModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p class="muted" style="margin-bottom:.75rem;">Ask the customer to show the return QR from their order. You can also paste the code manually.</p>
            <div id="returnQrReader"></div>
            <div class="scan-manual">
                <input type="text" id="returnQrManual" placeholder="Paste return QR text or token">
                <button type="button" class="action-btn" onclick="submitReturnPickupManual()">Use Code</button>
            </div>
            <div style="margin-top:1rem;text-align:right;">
                <button type="button" class="action-btn btn-outline" onclick="closeReturnScanModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let returnMap = null;
let returnMapLayers = [];
let returnScanOrderId = 0;
let returnQrScanner = null;
let returnRiderMarker = null;
let returnRiderWatchId = null;
let returnRouteLine = null;
let returnRouteTarget = null;

function acceptReturnPickup(orderId) {
    if (!confirm('Accept this return pickup assignment? You can scan the customer QR after accepting.')) {
        return;
    }

    const params = new URLSearchParams();
    params.set('order_id', String(orderId));
    params.set('status', 'accept_return_pickup');

    fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Return pickup accepted.');
            window.location.reload();
        } else {
            alert(data.message || 'Unable to accept return pickup');
        }
    })
    .catch(() => alert('An error occurred while accepting the pickup'));
}

function openReturnMap(orderId) {
    document.getElementById('returnMapModal').style.display = 'block';
    document.getElementById('returnMapLabel').textContent = 'Loading map...';
    document.getElementById('returnMapMeta').textContent = '';
    document.getElementById('returnMapDirections').textContent = 'Loading route directions...';

    fetch(`<?= site_url('dashboard/order-details-json') ?>/${orderId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.order) {
            document.getElementById('returnMapMeta').textContent = 'Unable to load map data.';
            return;
        }

        const o = data.order;
        const storeLat = o.store_latitude;
        const storeLng = o.store_longitude;
        const custLat = o.delivery_latitude;
        const custLng = o.delivery_longitude;
        const riderLat = o.rider_latitude;
        const riderLng = o.rider_longitude;
        const deliveryStatus = String(o.delivery_status || '').toLowerCase();
        const isPickedUp = deliveryStatus === 'return_picked_up';
        const hasCustomerPoint = !!(custLat && custLng);
        const hasStorePoint = !!(storeLat && storeLng);
        const routeToCustomerFirst = !isPickedUp && hasCustomerPoint;
        returnRouteTarget = routeToCustomerFirst
            ? { lat: Number(custLat), lng: Number(custLng), label: 'Customer pickup location' }
            : (hasStorePoint ? { lat: Number(storeLat), lng: Number(storeLng), label: 'Store return location' } : null);

        document.getElementById('returnMapLabel').textContent = 'Store (return to) & Customer pickup location';

        if (!returnMap) {
            returnMap = L.map('returnMapCanvas').setView([6.12, 125.17], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(returnMap);
        } else {
            returnMap.invalidateSize();
        }

        returnMapLayers.forEach(l => returnMap.removeLayer(l));
        returnMapLayers = [];

        const bounds = [];
        if (storeLat && storeLng) {
            returnMapLayers.push(L.marker([storeLat, storeLng]).addTo(returnMap).bindPopup('Store: ' + (o.store_address || 'Pickup point')));
            bounds.push([storeLat, storeLng]);
        }
        if (!isPickedUp && custLat && custLng) {
            returnMapLayers.push(L.marker([custLat, custLng]).addTo(returnMap).bindPopup('Customer return pickup: ' + (o.shipping_address || 'Customer')));
            bounds.push([custLat, custLng]);
        }

        if (bounds.length >= 2) {
            returnMap.fitBounds(L.latLngBounds(bounds).pad(0.2));
            document.getElementById('returnMapMeta').innerHTML =
                '<strong>Store:</strong> ' + (o.store_address || 'Not set') +
                '<br><strong>Customer:</strong> ' + (o.shipping_address || 'Not set');
        } else if (bounds.length === 1) {
            returnMap.setView(bounds[0], 14);
            document.getElementById('returnMapMeta').textContent = isPickedUp
                ? ('Store destination: ' + (o.store_address || 'Not set'))
                : (hasStorePoint ? 'Only store coordinates are available.' : 'Only customer coordinates are available.');
        } else {
            document.getElementById('returnMapMeta').textContent = 'No map coordinates saved. Use the address text above.';
        }

        if (returnRouteTarget) {
            document.getElementById('returnMapLabel').textContent =
                'Rider route to ' + returnRouteTarget.label;
        }

        if (riderLat && riderLng && returnRouteTarget) {
            if (!returnRiderMarker) {
                returnRiderMarker = L.marker([Number(riderLat), Number(riderLng)], {
                    icon: L.divIcon({
                        className: 'rider-motor-icon',
                        html: '<div style="font-size:20px;line-height:20px;">🏍️</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10],
                    })
                }).addTo(returnMap).bindPopup('Your current location');
            } else {
                returnRiderMarker.setLatLng([Number(riderLat), Number(riderLng)]);
            }
            drawReturnRoadGuide(Number(riderLat), Number(riderLng), returnRouteTarget.lat, returnRouteTarget.lng);
            const km = haversineKm(Number(riderLat), Number(riderLng), returnRouteTarget.lat, returnRouteTarget.lng);
            const eta = Math.max(2, Math.round((km / 25) * 60));
            document.getElementById('returnMapMeta').textContent =
                `Your distance to destination: ${km.toFixed(2)} km | ETA: ~${eta} min`;
            returnMap.fitBounds(L.latLngBounds([[Number(riderLat), Number(riderLng)], [returnRouteTarget.lat, returnRouteTarget.lng]]).pad(0.18));
        } else if (returnRouteTarget) {
            document.getElementById('returnMapMeta').textContent = 'Getting your current rider location...';
        }

        // Show current rider location and live route line on the return pickup map.
        startReturnRiderLocationTracking({
            riderToLat: returnRouteTarget ? returnRouteTarget.lat : null,
            riderToLng: returnRouteTarget ? returnRouteTarget.lng : null,
        });
    })
    .catch(() => {
        document.getElementById('returnMapMeta').textContent = 'Failed to load map.';
    });
}

function closeReturnMap() {
    document.getElementById('returnMapModal').style.display = 'none';
    stopReturnRiderLocationTracking();
    returnRouteTarget = null;
}

function startReturnRiderLocationTracking(routeTarget = { riderToLat: null, riderToLng: null }) {
    if (!navigator.geolocation || !returnMap) {
        return;
    }

    if (returnRiderWatchId !== null) {
        return;
    }

    const onLocationUpdate = (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const riderLatLng = [lat, lng];

        if (!returnRiderMarker) {
            returnRiderMarker = L.marker(riderLatLng, {
                icon: L.divIcon({
                    className: 'rider-motor-icon',
                    html: '<div style="font-size:20px;line-height:20px;">🏍️</div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                })
            }).addTo(returnMap).bindPopup('Your current location');
        } else {
            returnRiderMarker.setLatLng(riderLatLng);
        }

        if (routeTarget.riderToLat && routeTarget.riderToLng) {
            drawReturnRoadGuide(lat, lng, routeTarget.riderToLat, routeTarget.riderToLng);
            const km = haversineKm(lat, lng, routeTarget.riderToLat, routeTarget.riderToLng);
            const eta = Math.max(2, Math.round((km / 25) * 60));
            document.getElementById('returnMapMeta').textContent =
                `Your distance to destination: ${km.toFixed(2)} km | ETA: ~${eta} min`;
            returnMap.fitBounds(L.latLngBounds([[lat, lng], [routeTarget.riderToLat, routeTarget.riderToLng]]).pad(0.18));
        }
    };

    const onLocationError = () => {
        // Keep map usable even when location permission is denied.
    };

    returnRiderWatchId = navigator.geolocation.watchPosition(
        onLocationUpdate,
        onLocationError,
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 5000,
        }
    );
}

function stopReturnRiderLocationTracking() {
    if (returnRiderWatchId !== null && navigator.geolocation) {
        navigator.geolocation.clearWatch(returnRiderWatchId);
        returnRiderWatchId = null;
    }

    if (returnMap && returnRiderMarker) {
        returnMap.removeLayer(returnRiderMarker);
        returnRiderMarker = null;
    }

    if (returnMap && returnRouteLine) {
        returnMap.removeLayer(returnRouteLine);
        returnRouteLine = null;
    }
    const panel = document.getElementById('returnMapDirections');
    if (panel) {
        panel.textContent = '';
    }
}

async function drawReturnRoadGuide(fromLat, fromLng, toLat, toLng) {
    if (!returnMap) {
        return;
    }
    const panel = document.getElementById('returnMapDirections');
    const setPanel = (html) => {
        if (panel) {
            panel.innerHTML = html;
        }
    };

    try {
        const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&steps=true`;
        const res = await fetch(url);
        const data = await res.json();

        if (!data || data.code !== 'Ok' || !Array.isArray(data.routes) || data.routes.length === 0) {
            throw new Error('No route');
        }

        const route = data.routes[0];
        const coords = (route.geometry?.coordinates || []).map((c) => [c[1], c[0]]);
        if (returnRouteLine) {
            returnMap.removeLayer(returnRouteLine);
        }
        returnRouteLine = L.polyline(coords, { color: '#1976d2', weight: 5, opacity: 0.9 }).addTo(returnMap);

        const steps = (route.legs?.[0]?.steps || []).slice(0, 12);
        if (steps.length === 0) {
            setPanel('No step-by-step directions available.');
        } else {
            setPanel(steps.map((s, i) => `${i + 1}. ${formatReturnManeuver(s)} (${Math.max(1, Math.round((s.distance || 0)))}m)`).join('<br>'));
        }
    } catch (error) {
        if (returnRouteLine) {
            returnMap.removeLayer(returnRouteLine);
        }
        returnRouteLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
            color: '#27c56f',
            dashArray: '8 8',
            weight: 4,
            opacity: 0.9,
        }).addTo(returnMap);
        setPanel('Road route unavailable. Showing direct line to destination.');
    }
}

function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatReturnManeuver(step) {
    const m = step?.maneuver || {};
    const type = String(m.type || '').replace(/_/g, ' ');
    const modifier = String(m.modifier || '');
    const name = String(step?.name || '');
    const typeText = type ? type.charAt(0).toUpperCase() + type.slice(1) : 'Continue';
    return `${typeText}${modifier ? ' ' + modifier : ''}${name ? ' to ' + name : ''}`;
}

function openReturnScanModal(orderId) {
    returnScanOrderId = orderId;
    document.getElementById('returnScanModal').style.display = 'block';
    document.getElementById('returnQrManual').value = '';

    if (returnQrScanner) {
        returnQrScanner.clear().catch(() => {});
        returnQrScanner = null;
    }

    returnQrScanner = new Html5Qrcode('returnQrReader');
    returnQrScanner.start(
        { facingMode: 'environment' },
        { fps: 8, qrbox: { width: 220, height: 220 } },
        (decoded) => submitReturnPickup(decoded),
        () => {}
    ).catch(() => {
        document.getElementById('returnQrReader').innerHTML =
            '<p class="muted">Camera unavailable. Paste the return QR text below.</p>';
    });
}

function closeReturnScanModal() {
    document.getElementById('returnScanModal').style.display = 'none';
    if (returnQrScanner) {
        returnQrScanner.stop().then(() => {
            returnQrScanner.clear();
            returnQrScanner = null;
        }).catch(() => { returnQrScanner = null; });
    }
}

function submitReturnPickupManual() {
    const code = document.getElementById('returnQrManual').value.trim();
    if (!code) {
        alert('Enter or paste the return QR code.');
        return;
    }
    submitReturnPickup(code);
}

function submitReturnPickup(scanText) {
    if (!returnScanOrderId) {
        return;
    }

    const params = new URLSearchParams();
    params.set('order_id', String(returnScanOrderId));
    params.set('status', 'return_picked_up');
    params.set('return_qr_scan', scanText);

    if (!confirm('Confirm return pickup for this order?')) {
        return;
    }

    closeReturnScanModal();

    fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Return pickup recorded.');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to record pickup');
        }
    })
    .catch(() => alert('An error occurred while recording pickup'));
}

<?php if ($highlightOrderId > 0): ?>
document.getElementById('return-<?= $highlightOrderId ?>')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
<?php endif; ?>
</script>

<?= $this->include('rider/partials/footer') ?>
