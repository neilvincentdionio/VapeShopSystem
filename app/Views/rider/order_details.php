<?= $this->include('rider/partials/header') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.order-details-wrap{max-width:980px;margin:0 auto;display:grid;gap:1rem}
.details-card{background:#fff;border:1px solid #e0e0e0;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.06);padding:1.1rem}
.details-card h2{margin:0 0 .4rem;color:#223}
.meta{color:#666;font-size:.92rem}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.info-line{margin:.35rem 0}
.label{font-weight:700;color:#333}
.item-row{display:flex;justify-content:space-between;gap:1rem;border:1px solid #eef1f4;border-radius:10px;padding:.7rem;margin:.55rem 0}
.back-link{display:inline-flex;align-items:center;gap:.45rem;color:#2a7a4b;text-decoration:none;font-weight:600}
@media (max-width:780px){.grid{grid-template-columns:1fr}}
</style>

<section class="order-details-wrap">
    <a class="back-link" href="<?= site_url('rider/deliveries') ?>"><i class="fas fa-arrow-left"></i> Back to Deliveries</a>

    <article class="details-card">
        <h2><?= esc($order['reference_number'] ?? ('Order #' . ($order['id'] ?? ''))) ?></h2>
        <div class="meta">Status: <?= esc(ucwords(str_replace('_', ' ', (string) ($order['delivery_status'] ?? 'to_pay')))) ?></div>
    </article>

    <article class="details-card grid">
        <div>
            <div class="info-line"><span class="label">Customer:</span> <?= esc($order['customer']['name'] ?? 'Customer') ?></div>
            <div class="info-line"><span class="label">Email:</span> <?= esc($order['customer']['email'] ?? 'N/A') ?></div>
            <div class="info-line"><span class="label">Contact:</span> <?= esc($order['contact_number'] ?? ($order['customer']['phone'] ?? 'Not provided')) ?></div>
        </div>
        <div>
            <div class="info-line"><span class="label">Shipping Address:</span></div>
            <div class="meta"><?= esc($order['shipping_address'] ?? 'No address provided') ?></div>
            <?php if (!empty($order['shipment_notes'])): ?>
                <div class="info-line"><span class="label">Delivery Notes:</span> <?= esc($order['shipment_notes']) ?></div>
            <?php endif; ?>
        </div>
    </article>

    <article class="details-card">
        <h2>Order Items</h2>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <div>
                        <div><?= esc($item['name'] ?? 'Product') ?></div>
                        <div class="meta">Qty: <?= (int) ($item['qty'] ?? 0) ?></div>
                    </div>
                    <div><strong>&#8369;<?= number_format(((float) ($item['unit_price'] ?? 0)) * ((int) ($item['qty'] ?? 0)), 2) ?></strong></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="meta">No items found.</div>
        <?php endif; ?>
    </article>
    <?php if ((!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])) || (!empty($order['store_latitude']) && !empty($order['store_longitude']))): ?>
    <article class="details-card">
        <h2>Delivery Map</h2>
        <div class="meta" id="destination_label" style="margin-bottom:.5rem;"></div>
        <div id="rider_delivery_map" style="height:320px;border:1px solid #e0e0e0;border-radius:10px;"></div>
        <div class="meta" id="route_meta" style="margin-top:.6rem;"></div>
        <div id="route_steps" class="meta" style="margin-top:.6rem; border:1px solid #e0e0e0; border-radius:8px; padding:.55rem .65rem; max-height:180px; overflow:auto;"></div>
    </article>
    <?php endif; ?>
</section>

<?php if ((!empty($order['delivery_latitude']) && !empty($order['delivery_longitude'])) || (!empty($order['store_latitude']) && !empty($order['store_longitude']))): ?>
<script>
const status = <?= json_encode((string) ($order['delivery_status'] ?? '')) ?>;
const customerLat = <?= json_encode(!empty($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null) ?>;
const customerLng = <?= json_encode(!empty($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null) ?>;
const storeLat = <?= json_encode(!empty($order['store_latitude']) ? (float) $order['store_latitude'] : null) ?>;
const storeLng = <?= json_encode(!empty($order['store_longitude']) ? (float) $order['store_longitude'] : null) ?>;
const storeAddress = <?= json_encode((string) ($order['store_address'] ?? 'Store')) ?>;
const customerAddress = <?= json_encode((string) ($order['delivery_address'] ?? $order['shipping_address'] ?? 'Customer')) ?>;
const toStore = ['ready_for_pickup', 'accepted_by_rider'].includes(status);
const destLat = toStore ? storeLat : customerLat;
const destLng = toStore ? storeLng : customerLng;
const destinationLabel = toStore ? `Pickup Location: ${storeAddress}` : `Delivery Location: ${customerAddress}`;
document.getElementById('destination_label').textContent = destinationLabel;
const map = L.map('rider_delivery_map').setView([destLat, destLng], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
L.marker([destLat, destLng]).addTo(map).bindPopup(destinationLabel);
let routeLine = null;
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
        const rLat = pos.coords.latitude;
        const rLng = pos.coords.longitude;
        L.marker([rLat, rLng]).addTo(map).bindPopup('Your location');
        drawRouteGuide(rLat, rLng, destLat, destLng);
        const km = haversineKm(rLat, rLng, destLat, destLng);
        const eta = Math.max(3, Math.round((km / 25) * 60));
        document.getElementById('route_meta').textContent = `Estimated distance: ${km.toFixed(2)} km | ETA: ~${eta} min`;
    });
}

function pushRiderLocation(lat, lng) {
    const orderId = <?= (int) ($order['id'] ?? 0) ?>;
    const body = `order_id=${orderId}&rider_latitude=${encodeURIComponent(lat)}&rider_longitude=${encodeURIComponent(lng)}`;
    fetch('<?= site_url('dashboard/updateRiderLocation') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
    }).catch(() => {});
}

if (status === 'to_receive' && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
        pushRiderLocation(pos.coords.latitude, pos.coords.longitude);
    }, () => {}, { enableHighAccuracy: true, timeout: 10000 });

    navigator.geolocation.watchPosition((pos) => {
        pushRiderLocation(pos.coords.latitude, pos.coords.longitude);
        drawRouteGuide(pos.coords.latitude, pos.coords.longitude, destLat, destLng);
    }, () => {}, { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 });
}
function haversineKm(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2-lat1) * Math.PI / 180;
  const dLon = (lon2-lon1) * Math.PI / 180;
  const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
  return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
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
  if (stepsEl) stepsEl.textContent = 'Loading road guide...';
  try {
    const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&steps=true`;
    const res = await fetch(url);
    const data = await res.json();
    if (!data || data.code !== 'Ok' || !Array.isArray(data.routes) || data.routes.length === 0) throw new Error('No route');
    const route = data.routes[0];
    const coords = (route.geometry?.coordinates || []).map((c) => [c[1], c[0]]);
    if (routeLine) map.removeLayer(routeLine);
    routeLine = L.polyline(coords, { color: '#1976d2', weight: 5, opacity: 0.9 }).addTo(map);
    const steps = (route.legs?.[0]?.steps || []).slice(0, 12);
    if (stepsEl) {
      stepsEl.innerHTML = steps.length
        ? steps.map((s, i) => `${i + 1}. ${formatManeuver(s)} (${Math.max(1, Math.round((s.distance || 0)))}m)`).join('<br>')
        : 'Road guide unavailable.';
    }
  } catch (e) {
    if (routeLine) map.removeLayer(routeLine);
    routeLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], { color: '#27c56f', dashArray: '8 8', weight: 4 }).addTo(map);
    if (stepsEl) stepsEl.textContent = 'Road routing service unavailable. Showing direct guide line.';
  }
}
</script>
<?php endif; ?>

<?= $this->include('rider/partials/footer') ?>

