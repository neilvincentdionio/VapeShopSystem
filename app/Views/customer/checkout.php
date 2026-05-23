<?= $this->include('customer/partials/header') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .checkout-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        max-width: 920px;
        margin: 0 auto;
    }

    .checkout-title {
        font-size: 1.35rem;
        font-weight: 900;
        color: #333333;
        margin-bottom: .35rem;
    }

    .checkout-subtitle {
        color: #666666;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }

    .items {
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .items-head, .items-row {
        display: grid;
        grid-template-columns: 1.5fr .8fr .8fr .9fr;
        gap: 0;
        align-items: center;
        padding: .9rem 1rem;
    }

    .items-head {
        background: #f8f9fa;
        font-weight: 900;
        color: #333333;
        font-size: .85rem;
    }

    .items-row {
        border-top: 1px solid #e0e0e0;
        color: #333333;
    }

    .checkout-summary {
        margin-top: 1.5rem;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
        align-items: start;
    }

    .form-card {
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        padding: 1.25rem;
        background: #ffffff;
    }

    .form-card h3 {
        margin: 0 0 .75rem 0;
        font-size: 1rem;
        font-weight: 900;
        color: #333333;
    }

    .field-label {
        display: block;
        font-weight: 700;
        margin-bottom: .45rem;
        color: #333333;
    }

    .input {
        width: 100%;
        padding: .85rem 1rem;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        background: #ffffff;
        font-size: 0.95rem;
        color: #333333;
    }

    .change-box {
        margin-top: .75rem;
        padding: .85rem 1rem;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
        color: #333333;
        font-weight: 900;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 10px;
        padding: .85rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: .74rem;
        font-weight: 900;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        width: 100%;
        margin-top: 1rem;
        background: #27c56f;
        border-color: #27c56f;
        color: #ffffff;
    }

    .btn:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    @media (max-width: 860px) {
        .checkout-summary {
            grid-template-columns: 1fr;
        }
        .items-head, .items-row {
            grid-template-columns: 1.5fr .8fr .8fr;
        }
        .items-head .col-hide, .items-row .col-hide { display: none; }
    }
</style>

<div class="checkout-panel">
    <div class="checkout-title">Checkout</div>
    <div class="checkout-subtitle">Choose your payment method to place your order (COD or GCash).</div>

    <div class="items">
        <div class="items-head">
            <div>Item</div>
            <div>Qty</div>
            <div>Unit</div>
            <div class="col-hide">Total</div>
        </div>

        <?php foreach ($cart_items as $item): ?>
            <div class="items-row">
                <div style="font-weight:800;"><?= esc($item['display_name'] ?? $item['name']) ?></div>
                <div><?= (int) ($item['quantity'] ?? 0) ?></div>
                <div>₱<?= number_format((float) ($item['price'] ?? 0), 2) ?></div>
                <div class="col-hide">₱<?= number_format((float) ($item['amount'] ?? 0), 2) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="checkout-summary">
        <div class="form-card">
            <h3>Amount Due</h3>
            <div style="font-size:1.35rem; font-weight:1000; color:#333333; margin-bottom:.25rem;">
                ₱<?= number_format((float) $estimated_total, 2) ?>
            </div>
            <div style="color:#666666; line-height:1.6; font-weight:600; font-size:.95rem;">
                Select your preferred payment method
            </div>
        </div>

        <div class="form-card">
            <h3>Payment Method</h3>
            <form method="post" action="<?= site_url('customer/checkout') ?>" onsubmit="return validatePayment();">
                <label class="field-label" for="payment_method">Choose Payment Method</label>
                <select class="input" id="payment_method" name="payment_method" required onchange="togglePaymentFields()">
                    <option value="">Select Payment Method</option>
                    <option value="cash_on_delivery">Cash on Delivery (COD)</option>
                    <option value="gcash">GCash</option>
                </select>

                <!-- Cash on Delivery Fields -->
                <div id="cash_fields" style="display: none; margin-top: 1rem;">
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
                        <strong>Note:</strong> You will pay in cash upon delivery.
                    </div>
                </div>

                <!-- GCash Fields -->
                <div id="gcash_fields" style="display: none; margin-top: 1rem;">
                    <label class="field-label" for="gcash_reference">GCash Reference Number</label>
                    <input
                        class="input"
                        type="text"
                        id="gcash_reference"
                        name="gcash_reference"
                        placeholder="Enter your GCash transaction reference number"
                        maxlength="50"
                    >
                    
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #e8f5e8; border-radius: 8px; font-size: 0.9rem; color: #2d5a2d;">
                        <strong>GCash Instructions:</strong><br>
                        1. Send payment to: 09XX-XXX-XXXX<br>
                        2. Amount: ₱<?= number_format((float) $estimated_total, 2) ?><br>
                        3. Enter your reference number above
                    </div>
                </div>

                <div style="margin-top:1rem; border-top:1px solid #e0e0e0; padding-top:1rem;">
                    <label class="field-label">Delivery Address</label>
                    <div style="margin-bottom:.6rem;">
                        <label><input type="radio" name="delivery_address_mode" value="manual" checked onchange="toggleAddressMode()"> Enter address</label>
                        <label style="margin-left:1rem;"><input type="radio" name="delivery_address_mode" value="saved_address" onchange="toggleAddressMode()"> Use saved</label>
                    </div>
                    <div id="manual_fields">
                        <input class="input" type="text" id="delivery_address_line" name="delivery_address_line" placeholder="Street / House No." style="margin-bottom:.5rem;">
                        <input class="input" type="text" id="delivery_barangay" name="delivery_barangay" placeholder="Barangay" style="margin-bottom:.5rem;">
                        <input class="input" type="text" id="delivery_city" name="delivery_city" placeholder="City / Municipality" style="margin-bottom:.5rem;">
                        <input class="input" type="text" id="delivery_province" name="delivery_province" placeholder="Province" style="margin-bottom:.5rem;">
                        <input class="input" type="text" id="delivery_postal_code" name="delivery_postal_code" placeholder="Postal code" style="margin-bottom:.5rem;">
                        <input class="input" type="text" id="delivery_country" name="delivery_country" value="Philippines" placeholder="Country">
                    </div>
                    <div id="saved_fields" style="display:none; color:#666; font-size:.9rem;">
                        <?= !empty($customer_delivery_address) ? 'Saved address: ' . esc($customer_delivery_address) : 'No saved address found.' ?>
                        <?php if (! empty($customer_delivery_latitude) && ! empty($customer_delivery_longitude)): ?>
                            <div style="margin-top:.45rem;">Your registered delivery location will be sent to the rider and admin automatically.</div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                    <input type="hidden" name="delivery_longitude" id="delivery_longitude">
                    <div id="checkout_pin_section">
                        <div style="display:flex; gap:.5rem; margin:.7rem 0;">
                            <button type="button" class="btn" style="margin:0; width:auto; padding:.55rem .8rem;" onclick="useCurrentLocation()">Use Current Location</button>
                            <span id="geo_status" style="font-size:.85rem; color:#666;"></span>
                        </div>
                        <div id="checkout_map" style="height:260px; border-radius:12px; border:1px solid #e0e0e0;"></div>
                    </div>
                </div>

                <button class="btn" type="submit" id="submit_btn" disabled>
                    Select Payment Method
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const paymentMethodSelect = document.getElementById('payment_method');
    const cashFields = document.getElementById('cash_fields');
    const gcashFields = document.getElementById('gcash_fields');
    const submitBtn = document.getElementById('submit_btn');
    const savedDeliveryAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
    const savedDeliveryLatitude = <?= json_encode(isset($customer_delivery_latitude) ? (float) $customer_delivery_latitude : null) ?>;
    const savedDeliveryLongitude = <?= json_encode(isset($customer_delivery_longitude) ? (float) $customer_delivery_longitude : null) ?>;
    let map;
    let marker;
    let geocodeDebounceTimer = null;
    let mapLocked = false;

    function togglePaymentFields() {
        const selectedMethod = paymentMethodSelect.value;
        
        // Hide all payment fields
        cashFields.style.display = 'none';
        gcashFields.style.display = 'none';
        
        // Show relevant fields based on selection
        if (selectedMethod === 'cash_on_delivery') {
            cashFields.style.display = 'block';
            submitBtn.textContent = 'Place Order (COD)';
        } else if (selectedMethod === 'gcash') {
            gcashFields.style.display = 'block';
            submitBtn.textContent = 'Place Order (GCash)';
        } else {
            submitBtn.textContent = 'Select Payment Method';
        }
        
        // Enable/disable submit button
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
            if (!gcashReference) {
                alert('Please enter your GCash reference number.');
                return false;
            }
            if (gcashReference.length < 6) {
                alert('GCash reference number appears to be invalid. Please check and try again.');
                return false;
            }
        }
        const lat = document.getElementById('delivery_latitude').value;
        const lng = document.getElementById('delivery_longitude').value;
        const addressMode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (addressMode === 'saved_address') {
            applySavedDeliveryLocation();
        }
        if (!lat || !lng) {
            alert(addressMode === 'saved_address'
                ? 'Your saved delivery location is missing. Please enter your address manually and pin the map.'
                : 'Please pin your delivery location on the map.');
            return false;
        }
        const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (mode === 'saved_address' && !<?= json_encode(!empty($customer_delivery_address)) ?>) {
            alert('No saved address found. Please enter your address manually.');
            return false;
        }
        if (mode === 'saved_address' && (savedDeliveryLatitude === null || savedDeliveryLongitude === null)) {
            alert('Your account has no saved map location yet. Please enter your address manually and pin the map.');
            return false;
        }
        return true;
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

    function setDeliveryCoordinates(lat, lng) {
        document.getElementById('delivery_latitude').value = String(lat);
        document.getElementById('delivery_longitude').value = String(lng);
    }

    function applySavedDeliveryLocation() {
        if (savedDeliveryLatitude === null || savedDeliveryLongitude === null) {
            return false;
        }
        setDeliveryCoordinates(savedDeliveryLatitude, savedDeliveryLongitude);
        return true;
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
        } else if (mode === 'saved_address') {
            mapLocked = false;
            if (savedDeliveryAddress.trim()) {
                if (!map) {
                    map = L.map('checkout_map').setView([6.1164, 125.1716], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                    bindMapClick();
                }
                geocodeAddressAndPin(savedDeliveryAddress);
            }
        } else {
            mapLocked = false;
            document.getElementById('geo_status').textContent = '';
            if (!map) {
                map = L.map('checkout_map').setView([6.1164, 125.1716], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                bindMapClick();
            } else {
                map.invalidateSize();
            }
            geocodeManualAddressDebounced();
            bindMapClick();
        }
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
            status.textContent = 'Geolocation not supported.';
            return;
        }
        status.textContent = 'Getting location...';
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 16);
            setPinnedLocation(lat, lng);
            const manualMode = document.querySelector('input[name="delivery_address_mode"][value="manual"]');
            if (manualMode) {
                manualMode.checked = true;
                toggleAddressMode();
            }
            reverseGeocodeAndFillManual(lat, lng)
                .then(() => {
                    status.textContent = 'Location captured and address autofilled.';
                })
                .catch(() => {
                    status.textContent = 'Location captured. Address autofill unavailable.';
                });
        }, () => {
            status.textContent = 'Permission denied or unavailable. Pin manually.';
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    function getManualAddressString() {
        const parts = [
            document.getElementById('delivery_address_line')?.value || '',
            document.getElementById('delivery_barangay')?.value || '',
            document.getElementById('delivery_city')?.value || '',
            document.getElementById('delivery_province')?.value || '',
            document.getElementById('delivery_postal_code')?.value || '',
            document.getElementById('delivery_country')?.value || 'Philippines'
        ]
        .map((v) => v.trim())
        .filter(Boolean);
        return parts.join(', ');
    }

    function geocodeManualAddressDebounced() {
        clearTimeout(geocodeDebounceTimer);
        geocodeDebounceTimer = setTimeout(() => {
            const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
            if (mode !== 'manual') return;
            const fullAddress = getManualAddressString();
            if (fullAddress.length < 8) return;
            geocodeAddressAndPin(fullAddress);
        }, 500);
    }

    async function geocodeAddressAndPin(addressText) {
        if (!addressText || addressText.trim().length < 5) return;
        const query = encodeURIComponent(addressText);
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=${query}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        if (!Array.isArray(data) || !data.length) return;
        const lat = Number(data[0].lat);
        const lng = Number(data[0].lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        map.setView([lat, lng], 16);
        setPinnedLocation(lat, lng);
    }

    async function reverseGeocodeAndFillManual(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('reverse-geocode-failed');
        const data = await response.json();
        const addr = data.address || {};

        const street = [addr.house_number, addr.road].filter(Boolean).join(' ').trim();
        const barangay = addr.suburb || addr.neighbourhood || addr.village || addr.hamlet || '';
        const city = addr.city || addr.town || addr.municipality || addr.county || '';
        const province = addr.state || '';
        const postal = addr.postcode || '';
        const country = addr.country || 'Philippines';

        if (street) document.getElementById('delivery_address_line').value = street;
        if (barangay) document.getElementById('delivery_barangay').value = barangay;
        if (city) document.getElementById('delivery_city').value = city;
        if (province) document.getElementById('delivery_province').value = province;
        if (postal) document.getElementById('delivery_postal_code').value = postal;
        if (country) document.getElementById('delivery_country').value = country;
    }

    // Event listeners
    paymentMethodSelect.addEventListener('change', togglePaymentFields);
    if (savedDeliveryLatitude !== null && savedDeliveryLongitude !== null) {
        const savedRadio = document.querySelector('input[name="delivery_address_mode"][value="saved_address"]');
        if (savedRadio) {
            savedRadio.checked = true;
        }
    }
    toggleAddressMode();
    const initialMode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
    if (initialMode === 'manual' || savedDeliveryLatitude === null || savedDeliveryLongitude === null) {
        if (!map) {
            map = L.map('checkout_map').setView([6.1164, 125.1716], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            bindMapClick();
            setPinnedLocation(6.1164, 125.1716);
        }
    }
    ['delivery_address_line', 'delivery_barangay', 'delivery_city', 'delivery_province', 'delivery_postal_code', 'delivery_country']
        .forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('change', geocodeManualAddressDebounced);
            el.addEventListener('input', geocodeManualAddressDebounced);
        });
    // Initialize
    togglePaymentFields();
</script>

<?= $this->include('customer/partials/footer') ?>


