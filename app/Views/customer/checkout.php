<?= $this->include('customer/partials/header') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .checkout-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        max-width: 760px;
        margin: 0 auto;
    }

    .checkout-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .checkout-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #333333;
        margin: 0 0 .35rem;
    }

    .checkout-subtitle {
        color: #666666;
        margin: 0;
        line-height: 1.55;
        font-size: .95rem;
    }

    .checkout-back {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        text-decoration: none;
        color: #1d9f57;
        font-weight: 700;
        font-size: .88rem;
        border: 1px solid #86efac;
        background: #ecfdf3;
        padding: .5rem .85rem;
        border-radius: 999px;
    }

    .items-table {
        border: 1px solid #e8ecef;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .items-head, .items-row {
        display: grid;
        grid-template-columns: 1.4fr .55fr .75fr .8fr;
        gap: .5rem;
        padding: .75rem 1rem;
        align-items: center;
    }

    .items-head {
        background: #f8f9fa;
        font-weight: 800;
        font-size: .8rem;
        color: #555;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .items-row {
        border-top: 1px solid #e8ecef;
        font-size: .92rem;
    }

    .items-row strong {
        font-weight: 700;
        color: #333;
    }

    .total-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #ecfdf3, #f8fffb);
        border: 1px solid #bbf7d0;
        margin-bottom: 1.25rem;
    }

    .total-banner span:first-child {
        font-weight: 700;
        color: #333;
    }

    .total-banner span:last-child {
        font-size: 1.35rem;
        font-weight: 900;
        color: #166534;
    }

    .checkout-card {
        border: 1px solid #e8ecef;
        border-radius: 14px;
        padding: 1.15rem;
        margin-bottom: 1rem;
        background: #fafbfc;
    }

    .checkout-card h3 {
        margin: 0 0 .85rem;
        font-size: 1rem;
        font-weight: 800;
        color: #333;
    }

    .field-label {
        display: block;
        font-weight: 700;
        margin-bottom: .4rem;
        color: #333333;
        font-size: .88rem;
    }

    .input {
        width: 100%;
        padding: .75rem .9rem;
        border-radius: 10px;
        border: 1px solid #d7dce1;
        background: #ffffff;
        font-size: 0.92rem;
        color: #333333;
        box-sizing: border-box;
    }

    .input:focus {
        outline: none;
        border-color: #27c56f;
        box-shadow: 0 0 0 3px rgba(39, 197, 111, .12);
    }

    .address-mode {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: .75rem;
        font-size: .9rem;
    }

    .note-box {
        margin-top: .75rem;
        padding: .7rem .85rem;
        border-radius: 10px;
        font-size: .86rem;
        line-height: 1.5;
    }

    .note-box--muted {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #4b5563;
    }

    .note-box--success {
        background: #ecfdf3;
        border: 1px solid #86efac;
        color: #166534;
    }

    #checkout_map {
        height: 280px;
        border-radius: 12px;
        border: 1px solid #d7dce1;
        margin-top: .65rem;
        background: #e8ecef;
    }

    .btn-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin-top: .25rem;
        padding: .9rem 1rem;
        border-radius: 12px;
        border: none;
        background: #27c56f;
        color: #fff;
        font-weight: 800;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        cursor: pointer;
    }

    .btn-submit:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .btn-locate {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .5rem;
        padding: .5rem .8rem;
        border-radius: 8px;
        border: 1px solid #27c56f;
        background: #fff;
        color: #1d9f57;
        font-weight: 700;
        font-size: .8rem;
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .items-head, .items-row {
            grid-template-columns: 1fr;
            gap: .2rem;
        }

        .items-head {
            display: none;
        }

        .items-row {
            padding: .85rem;
        }
    }
</style>

<div class="checkout-panel">
    <div class="checkout-top">
        <div>
            <div class="checkout-title">Checkout</div>
            <p class="checkout-subtitle">Review your order, choose payment, and confirm delivery details.</p>
        </div>
        <a href="<?= site_url('customer/cart') ?>" class="checkout-back">← Back to Cart</a>
    </div>

    <div class="items-table">
        <div class="items-head">
            <div>Item</div>
            <div>Qty</div>
            <div>Unit</div>
            <div>Total</div>
        </div>
        <?php foreach ($cart_items as $item): ?>
            <div class="items-row">
                <div><strong><?= esc($item['display_name'] ?? $item['name']) ?></strong></div>
                <div><?= (int) ($item['quantity'] ?? 0) ?></div>
                <div>₱<?= number_format((float) ($item['price'] ?? 0), 2) ?></div>
                <div>₱<?= number_format((float) ($item['amount'] ?? 0), 2) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="total-banner">
        <span>Amount due</span>
        <span>₱<?= number_format((float) $estimated_total, 2) ?></span>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="note-box" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;margin-bottom:1rem;">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('customer/checkout') ?>" onsubmit="return validatePayment();">
        <?= csrf_field() ?>

        <div class="checkout-card">
            <h3>Payment Method</h3>
            <label class="field-label" for="payment_method">Choose payment</label>
            <select class="input" id="payment_method" name="payment_method" required>
                <option value="">Select payment method</option>
                <option value="cash_on_delivery">Cash on Delivery (COD)</option>
                <option value="gcash">GCash</option>
            </select>

            <div id="cash_fields" style="display:none;">
                <div class="note-box note-box--muted">You will pay in cash when the rider delivers your order.</div>
            </div>

            <div id="gcash_fields" style="display:none;">
                <label class="field-label" for="gcash_reference" style="margin-top:.75rem;">GCash reference number</label>
                <input class="input" type="text" id="gcash_reference" name="gcash_reference" maxlength="50" placeholder="Enter transaction reference">
                <div class="note-box note-box--success">
                    Send <strong>₱<?= number_format((float) $estimated_total, 2) ?></strong> via GCash, then enter your reference above.
                </div>
            </div>
        </div>

        <div class="checkout-card">
            <h3>Delivery Address</h3>
            <div class="address-mode">
                <label><input type="radio" name="delivery_address_mode" value="manual" checked onchange="toggleAddressMode()"> Enter address</label>
                <label><input type="radio" name="delivery_address_mode" value="saved_address" onchange="toggleAddressMode()"> Use saved address</label>
            </div>

            <div id="manual_fields">
                <input class="input" type="text" id="delivery_address_line" name="delivery_address_line" placeholder="Street / House No." style="margin-bottom:.5rem;">
                <input class="input" type="text" id="delivery_barangay" name="delivery_barangay" placeholder="Barangay" style="margin-bottom:.5rem;">
                <input class="input" type="text" id="delivery_city" name="delivery_city" placeholder="City / Municipality" style="margin-bottom:.5rem;">
                <input class="input" type="text" id="delivery_province" name="delivery_province" placeholder="Province" style="margin-bottom:.5rem;">
                <input class="input" type="text" id="delivery_postal_code" name="delivery_postal_code" placeholder="Postal code" style="margin-bottom:.5rem;">
                <input class="input" type="text" id="delivery_country" name="delivery_country" value="Philippines" placeholder="Country">
            </div>

            <div id="saved_fields" style="display:none;" class="note-box note-box--muted">
                <?= ! empty($customer_delivery_address)
                    ? 'Saved address: ' . esc($customer_delivery_address)
                    : 'No saved address found. Please enter your address manually.' ?>
                <?php if (! empty($customer_delivery_latitude) && ! empty($customer_delivery_longitude)): ?>
                    <div style="margin-top:.4rem;">Your registered map location will be sent to the rider automatically.</div>
                <?php endif; ?>
            </div>

            <input type="hidden" name="delivery_latitude" id="delivery_latitude">
            <input type="hidden" name="delivery_longitude" id="delivery_longitude">

            <div id="checkout_pin_section">
                <button type="button" class="btn-locate" onclick="useCurrentLocation()">Use current location</button>
                <span id="geo_status" style="display:block;margin-top:.35rem;font-size:.82rem;color:#666;"></span>
                <div id="checkout_map"></div>
            </div>
        </div>

        <button class="btn-submit" type="submit" id="submit_btn" disabled>Select payment method</button>
    </form>
</div>

<script>
    const paymentMethodSelect = document.getElementById('payment_method');
    const cashFields = document.getElementById('cash_fields');
    const gcashFields = document.getElementById('gcash_fields');
    const submitBtn = document.getElementById('submit_btn');
    const savedDeliveryAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
    const savedDeliveryLatitude = <?= json_encode(isset($customer_delivery_latitude) ? (float) $customer_delivery_latitude : null) ?>;
    const savedDeliveryLongitude = <?= json_encode(isset($customer_delivery_longitude) ? (float) $customer_delivery_longitude : null) ?>;
    const defaultLat = 6.1164;
    const defaultLng = 125.1716;
    let map;
    let marker;
    let geocodeDebounceTimer = null;
    let mapLocked = false;
    let mapInitialized = false;

    function togglePaymentFields() {
        const selectedMethod = paymentMethodSelect.value;
        cashFields.style.display = selectedMethod === 'cash_on_delivery' ? 'block' : 'none';
        gcashFields.style.display = selectedMethod === 'gcash' ? 'block' : 'none';

        if (selectedMethod === 'cash_on_delivery') {
            submitBtn.textContent = 'Place Order (COD)';
        } else if (selectedMethod === 'gcash') {
            submitBtn.textContent = 'Place Order (GCash)';
        } else {
            submitBtn.textContent = 'Select payment method';
        }

        submitBtn.disabled = !selectedMethod;
    }

    function validatePayment() {
        const selectedMethod = paymentMethodSelect.value;
        if (!selectedMethod) {
            alert('Please select a payment method.');
            return false;
        }

        if (selectedMethod === 'gcash') {
            const gcashReference = document.getElementById('gcash_reference').value.trim();
            if (!gcashReference || gcashReference.length < 6) {
                alert('Please enter a valid GCash reference number.');
                return false;
            }
        }

        const addressMode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (addressMode === 'saved_address') {
            applySavedDeliveryLocation();
        }

        const lat = document.getElementById('delivery_latitude').value;
        const lng = document.getElementById('delivery_longitude').value;
        if (!lat || !lng) {
            alert(addressMode === 'saved_address'
                ? 'Your saved delivery location is missing. Please enter your address manually and pin the map.'
                : 'Please pin your delivery location on the map.');
            return false;
        }

        if (addressMode === 'saved_address' && !<?= json_encode(! empty($customer_delivery_address)) ?>) {
            alert('No saved address found. Please enter your address manually.');
            return false;
        }

        return true;
    }

    function setDeliveryCoordinates(lat, lng) {
        document.getElementById('delivery_latitude').value = String(lat);
        document.getElementById('delivery_longitude').value = String(lng);
    }

    function applySavedDeliveryLocation() {
        if (savedDeliveryLatitude === null || savedDeliveryLongitude === null) {
            return false;
        }
        setDeliveryCoordinates(savedDeliveryLatitude, savedDeliveryLongitude);
        if (map) {
            map.setView([savedDeliveryLatitude, savedDeliveryLongitude], 16);
            setPinnedLocation(savedDeliveryLatitude, savedDeliveryLongitude);
        }
        return true;
    }

    function ensureMap() {
        if (mapInitialized) {
            if (map) {
                setTimeout(() => map.invalidateSize(), 150);
            }
            return;
        }

        const mapEl = document.getElementById('checkout_map');
        if (!mapEl || mapEl.offsetParent === null) {
            return;
        }

        const startLat = savedDeliveryLatitude ?? defaultLat;
        const startLng = savedDeliveryLongitude ?? defaultLng;

        map = L.map('checkout_map').setView([startLat, startLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        setPinnedLocation(startLat, startLng);
        bindMapClick();
        mapInitialized = true;

        setTimeout(() => map.invalidateSize(), 200);
    }

    function bindMapClick() {
        if (!map) {
            return;
        }
        map.off('click');
        if (!mapLocked) {
            map.on('click', (e) => setPinnedLocation(e.latlng.lat, e.latlng.lng));
        }
    }

    function toggleAddressMode() {
        const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        const pinSection = document.getElementById('checkout_pin_section');
        const useSavedWithPin = mode === 'saved_address'
            && savedDeliveryLatitude !== null
            && savedDeliveryLongitude !== null;

        document.getElementById('manual_fields').style.display = mode === 'manual' ? 'block' : 'none';
        document.getElementById('saved_fields').style.display = mode === 'saved_address' ? 'block' : 'none';

        if (pinSection) {
            pinSection.style.display = useSavedWithPin ? 'none' : 'block';
        }

        if (useSavedWithPin) {
            mapLocked = true;
            applySavedDeliveryLocation();
            return;
        }

        mapLocked = false;
        ensureMap();

        if (mode === 'saved_address' && savedDeliveryAddress.trim()) {
            geocodeAddressAndPin(savedDeliveryAddress);
        } else if (mode === 'manual') {
            geocodeManualAddressDebounced();
        }

        bindMapClick();
    }

    function setPinnedLocation(lat, lng) {
        setDeliveryCoordinates(lat, lng);
        if (!map) {
            return;
        }
        if (!marker) {
            marker = L.marker([lat, lng]).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
        }
    }

    function useCurrentLocation() {
        const status = document.getElementById('geo_status');
        if (!navigator.geolocation) {
            status.textContent = 'Geolocation is not supported on this browser.';
            return;
        }

        status.textContent = 'Getting your location...';
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const manualMode = document.querySelector('input[name="delivery_address_mode"][value="manual"]');
            if (manualMode) {
                manualMode.checked = true;
            }
            toggleAddressMode();
            ensureMap();
            map.setView([lat, lng], 16);
            setPinnedLocation(lat, lng);
            reverseGeocodeAndFillManual(lat, lng)
                .then(() => { status.textContent = 'Location captured.'; })
                .catch(() => { status.textContent = 'Location captured. Fill address manually if needed.'; });
        }, () => {
            status.textContent = 'Unable to get location. Pin your address on the map.';
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    function getManualAddressString() {
        return [
            document.getElementById('delivery_address_line')?.value || '',
            document.getElementById('delivery_barangay')?.value || '',
            document.getElementById('delivery_city')?.value || '',
            document.getElementById('delivery_province')?.value || '',
            document.getElementById('delivery_postal_code')?.value || '',
            document.getElementById('delivery_country')?.value || 'Philippines'
        ].map((v) => v.trim()).filter(Boolean).join(', ');
    }

    function geocodeManualAddressDebounced() {
        clearTimeout(geocodeDebounceTimer);
        geocodeDebounceTimer = setTimeout(() => {
            const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
            if (mode !== 'manual') {
                return;
            }
            const fullAddress = getManualAddressString();
            if (fullAddress.length < 8) {
                return;
            }
            geocodeAddressAndPin(fullAddress);
        }, 500);
    }

    async function geocodeAddressAndPin(addressText) {
        if (!addressText || addressText.trim().length < 5) {
            return;
        }
        ensureMap();
        const query = encodeURIComponent(addressText);
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${query}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            return;
        }
        const data = await response.json();
        if (!Array.isArray(data) || !data.length) {
            return;
        }
        const lat = Number(data[0].lat);
        const lng = Number(data[0].lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }
        map.setView([lat, lng], 16);
        setPinnedLocation(lat, lng);
    }

    async function reverseGeocodeAndFillManual(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            throw new Error('reverse-geocode-failed');
        }
        const data = await response.json();
        const addr = data.address || {};
        const street = [addr.house_number, addr.road].filter(Boolean).join(' ').trim();
        if (street) document.getElementById('delivery_address_line').value = street;
        const barangay = addr.suburb || addr.neighbourhood || addr.village || addr.hamlet || '';
        if (barangay) document.getElementById('delivery_barangay').value = barangay;
        const city = addr.city || addr.town || addr.municipality || addr.county || '';
        if (city) document.getElementById('delivery_city').value = city;
        if (addr.state) document.getElementById('delivery_province').value = addr.state;
        if (addr.postcode) document.getElementById('delivery_postal_code').value = addr.postcode;
        if (addr.country) document.getElementById('delivery_country').value = addr.country;
    }

    paymentMethodSelect.addEventListener('change', togglePaymentFields);

    if (savedDeliveryLatitude !== null && savedDeliveryLongitude !== null) {
        const savedRadio = document.querySelector('input[name="delivery_address_mode"][value="saved_address"]');
        if (savedRadio) {
            savedRadio.checked = true;
        }
    }

    ['delivery_address_line', 'delivery_barangay', 'delivery_city', 'delivery_province', 'delivery_postal_code', 'delivery_country']
        .forEach((id) => {
            const el = document.getElementById(id);
            if (!el) {
                return;
            }
            el.addEventListener('input', geocodeManualAddressDebounced);
            el.addEventListener('change', geocodeManualAddressDebounced);
        });

    togglePaymentFields();
    toggleAddressMode();
    window.addEventListener('load', () => {
        setTimeout(() => ensureMap(), 250);
    });
</script>

<?= $this->include('customer/partials/footer') ?>
