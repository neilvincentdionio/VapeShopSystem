<?php
$checkoutTotalValue = (float) ($cart_total ?? $estimated_total ?? 0);
?>
<script>
if (!window.__checkout_modal_bootstrapped__) {
window.__checkout_modal_bootstrapped__ = true;

if (!window.__leaflet_loaded__) {
    const lCss = document.createElement('link');
    lCss.rel = 'stylesheet';
    lCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(lCss);
    const lJs = document.createElement('script');
    lJs.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    document.head.appendChild(lJs);
    window.__leaflet_loaded__ = true;
}
const gcashMerchantNumber = '+639850640073';
const gcashMerchantName = 'QuickPuff VapeShop';
const checkoutTotal = <?= json_encode($checkoutTotalValue) ?>;
const gcashQrImageUrl = <?= json_encode(base_url('public/assets/img/gcash_real_qr.png?v=20260528b')) ?>;
const savedDeliveryAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
const savedDeliveryLatitude = <?= json_encode(isset($customer_delivery_latitude) ? (float) $customer_delivery_latitude : null) ?>;
const savedDeliveryLongitude = <?= json_encode(isset($customer_delivery_longitude) ? (float) $customer_delivery_longitude : null) ?>;
let checkoutMapLocked = false;
let checkoutMap = null;
let checkoutMarker = null;
let checkoutGeocodeDebounce = null;
let checkoutGeocodeLockUntil = 0;

const deliveryAddressData = {
    'South Cotabato': {
        'General Santos City': [],
        'Koronadal City': [],
        'Banga': [],
        'Lake Sebu': [],
        'Norala': [],
        'Polomolok': [],
        'Santo Nino': [],
        'Surallah': [],
        "T'boli": [],
        'Tampakan': [],
        'Tantangan': [],
        'Tupi': []
    },
    'Sarangani': {
        'Alabel': [],
        'Glan': [],
        'Kiamba': [],
        'Maasim': [],
        'Maitum': [],
        'Malapatan': [],
        'Malungon': []
    }
};

const defaultBarangayList = [];

const deliveryBarangayOverrides = {
    'General Santos City': [
        'Apopong', 'Baluan', 'Bawing', 'Buayan', 'Bula', 'Calumpang', 'City Heights',
        'Conel', 'Dadiangas East', 'Dadiangas North', 'Dadiangas South', 'Dadiangas West',
        'Fatima', 'Katangawan', 'Labangal', 'Lagao', 'Ligaya', 'Mabuhay', 'Olympog',
        'San Isidro', 'San Jose', 'Siguel', 'Sinawal', 'Tambler', 'Tinagacan', 'Upper Labay'
    ],
    'Koronadal City': [
        'Avancena', 'Cacub', 'Caloocan', 'Carpenter Hill', 'Concepcion',
        'General Paulino Santos', 'Mabini', 'Magsaysay', 'Morales', 'San Isidro',
        'Santa Cruz', 'Zone I', 'Zone II', 'Zone III', 'Zone IV'
    ],
    'Polomolok': [
        'Cannery Site', 'Glamang', 'Kinilis', 'Koronadal Proper', 'Landan',
        'Lapu', 'Lumakil', 'Magsaysay', 'Maligo', 'Pagalungan',
        'Palkan', 'Poblacion', 'Rubber', 'Silway 7', 'Silway 8', 'Sumbakil'
    ],
    'Alabel': [
        'Alegria', 'Bagacay', 'Baluntay', 'Domolok', 'Kawas', 'Maribulan',
        'Pag-asa', 'Paraiso', 'Poblacion', 'Spring', 'Tokawal'
    ],
    'Glan': [
        'Baliton', 'Batulaki', 'Big Margus', 'Burias', 'Calabanit', 'Cross',
        'Datal Bukay', 'E. Alegado', 'Gumasa', 'Kapatan', 'Lago', 'Poblacion',
        'Rio del Pilar', 'San Jose', 'Taluya', 'Tangisan', 'Upper Klinan'
    ],
    'Malungon': [
        'Alpabel', 'Banate', 'Datal Batong', 'Datal Bila', 'Datal Tampal',
        'Kawayan', 'Lower Mainit', 'Malungon Gamay', 'Poblacion', 'San Juan',
        'Tamban', 'Upper Mainit'
    ]
};

const deliveryPostalCodes = {
    'General Santos City': '9500',
    'Koronadal City': '9506',
    'Banga': '9501',
    'Lake Sebu': '9514',
    'Norala': '9508',
    'Polomolok': '9504',
    'Santo Nino': '9511',
    'Surallah': '9512',
    "T'boli": '9513',
    'Tampakan': '9507',
    'Tantangan': '9510',
    'Tupi': '9505',
    'Alabel': '9501',
    'Glan': '9517',
    'Kiamba': '9514',
    'Maasim': '9502',
    'Maitum': '9515',
    'Malapatan': '9516',
    'Malungon': '9503'
};

const PSGC_API_BASE = 'https://psgc.cloud/api/v1';
const localityCodeByName = {
    // South Cotabato
    'Banga': { code: '126302000', type: 'municipality' },
    'General Santos City': { code: '126303000', type: 'city' },
    'Koronadal City': { code: '126306000', type: 'city' },
    'Norala': { code: '126311000', type: 'municipality' },
    'Polomolok': { code: '126312000', type: 'municipality' },
    'Surallah': { code: '126313000', type: 'municipality' },
    'Tampakan': { code: '126314000', type: 'municipality' },
    'Tantangan': { code: '126315000', type: 'municipality' },
    "T'boli": { code: '126316000', type: 'municipality' },
    'Tupi': { code: '126317000', type: 'municipality' },
    'Santo Nino': { code: '126318000', type: 'municipality' },
    'Lake Sebu': { code: '126319000', type: 'municipality' },
    // Sarangani
    'Alabel': { code: '128001000', type: 'municipality' },
    'Glan': { code: '128002000', type: 'municipality' },
    'Kiamba': { code: '128003000', type: 'municipality' },
    'Maasim': { code: '128004000', type: 'municipality' },
    'Maitum': { code: '128005000', type: 'municipality' },
    'Malapatan': { code: '128006000', type: 'municipality' },
    'Malungon': { code: '128007000', type: 'municipality' }
};
const remoteBarangayCache = {};

function showToast(message, type = 'processing', showSpinner = false) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let content = '';
    if (showSpinner) {
        content += '<div class="toast-spinner"></div>';
    }
    content += `<span>${message}</span>`;
    
    toast.innerHTML = content;
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto hide after 3 seconds for success/error, 5 seconds for processing
    const hideTime = type === 'processing' ? 5000 : 3000;
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, hideTime);
}

function openCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;
    initDeliveryAddressFields();
    const hasSavedCoords = savedDeliveryLatitude !== null && savedDeliveryLongitude !== null;
    if (savedDeliveryAddress.trim() && hasSavedCoords) {
        const savedRadio = document.querySelector('input[name="delivery_address_mode"][value="saved_address"]');
        const manualRadio = document.querySelector('input[name="delivery_address_mode"][value="manual"]');
        if (savedRadio && !savedRadio.disabled) {
            savedRadio.checked = true;
            if (manualRadio) {
                manualRadio.checked = false;
            }
        }
    }
    modal.classList.add('show');
    setTimeout(() => {
        toggleDeliveryAddressMode();
        const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (mode === 'manual' || savedDeliveryLatitude === null || savedDeliveryLongitude === null) {
            initCheckoutMap();
        }
    }, 250);
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    if (!modal) return;
    modal.classList.remove('show');
}

function toggleCheckoutModalFields() {
    const method = document.getElementById('popup_payment_method').value;
    const gcashWrap = document.getElementById('popup_gcash_wrap');
    if (!gcashWrap) return;
    gcashWrap.style.display = method === 'gcash' ? 'block' : 'none';
    if (method === 'gcash') {
        const refInput = document.getElementById('popup_gcash_reference');
        const qrImage = document.getElementById('popup_gcash_qr');

        if (qrImage) {
            qrImage.src = gcashQrImageUrl;
        }
    }
}

function openInGcashApp() {
    const refInput = document.getElementById('popup_gcash_reference');

    // Attempt app deep-link first.
    const deepLink = 'gcash://';
    const fallbackWeb = 'https://www.gcash.com/';

    const start = Date.now();
    window.location.href = deepLink;

    setTimeout(() => {
        // If app did not open, fallback to GCash website.
        if (Date.now() - start < 1800) {
            window.open(fallbackWeb, '_blank');
        }
    }, 1200);
}

function validateCheckoutModal() {
    const method = document.getElementById('popup_payment_method').value;
    if (!method) {
        alert('Please select a payment method.');
        return false;
    }

    if (method === 'gcash') {
        const gcashRef = (document.getElementById('popup_gcash_reference').value || '').trim();
        const isSampleRef = gcashRef.toUpperCase() === 'QWERTY';
        if (!isSampleRef && !/^\d{10,13}$/.test(gcashRef)) {
            alert('Please enter a valid GCash reference number.');
            return false;
        }
    }

    const addressMode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
    if (addressMode === 'manual') {
        const requiredFields = [
            ['delivery_address_line', 'Please enter your street address.'],
            ['delivery_city', 'Please select your city or municipality.'],
            ['delivery_barangay', 'Please select your barangay.'],
            ['delivery_postal_code', 'Please enter your postal code.']
        ];

        for (const [fieldId, message] of requiredFields) {
            const field = document.getElementById(fieldId);
            if (!field || !(field.value || '').trim()) {
                alert(message);
                if (field) field.focus();
                return false;
            }
        }
    } else {
        const savedAddress = <?= json_encode((string) ($customer_delivery_address ?? '')) ?>;
        if (!savedAddress.trim()) {
            alert('No saved address found. Please enter your delivery address manually.');
            return false;
        }
    }

    if (addressMode === 'saved_address') {
        // Use saved account coordinates when available; otherwise keep the pin set on the map below.
        if (savedDeliveryLatitude !== null && savedDeliveryLongitude !== null) {
            applySavedDeliveryLocation();
        }
    }

    const latValue = (document.getElementById('delivery_latitude')?.value || '').trim();
    const lngValue = (document.getElementById('delivery_longitude')?.value || '').trim();
    if (!latValue || !lngValue) {
        alert(addressMode === 'saved_address'
            ? 'Please pin your delivery location on the map below your saved address.'
            : 'Please pin your exact delivery location on the map.');
        return false;
    }

    return true;
}

function bindCheckoutMapClick() {
    if (!checkoutMap) {
        return;
    }
    checkoutMap.off('click');
    if (!checkoutMapLocked) {
        checkoutMap.on('click', (e) => {
            setCheckoutPin(e.latlng.lat, e.latlng.lng);
        });
    }
}

function setDeliveryCoordinates(lat, lng) {
    const latInput = document.getElementById('delivery_latitude');
    const lngInput = document.getElementById('delivery_longitude');
    if (latInput) {
        latInput.value = String(lat);
    }
    if (lngInput) {
        lngInput.value = String(lng);
    }
}

function applySavedDeliveryLocation() {
    if (savedDeliveryLatitude === null || savedDeliveryLongitude === null) {
        return false;
    }
    setDeliveryCoordinates(savedDeliveryLatitude, savedDeliveryLongitude);
    return true;
}

function initCheckoutMap() {
    if (typeof L === 'undefined') return;
    if (!checkoutMap) {
        checkoutMap = L.map('checkout_map').setView([6.1164, 125.1716], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(checkoutMap);
        bindCheckoutMapClick();
        if (savedDeliveryLatitude !== null && savedDeliveryLongitude !== null) {
            setCheckoutPin(savedDeliveryLatitude, savedDeliveryLongitude);
        } else {
            setCheckoutPin(6.1164, 125.1716);
        }
    } else {
        checkoutMap.invalidateSize();
        bindCheckoutMapClick();
    }
}

function setCheckoutPin(lat, lng) {
    setDeliveryCoordinates(lat, lng);
    if (!checkoutMap) {
        return;
    }
    if (!checkoutMarker) {
        checkoutMarker = L.marker([lat, lng]).addTo(checkoutMap);
    } else {
        checkoutMarker.setLatLng([lat, lng]);
    }
}

function checkoutUseCurrentLocation() {
    const status = document.getElementById('checkout_geo_status');
    if (!navigator.geolocation) {
        status.textContent = 'Geolocation unavailable.';
        return;
    }
    status.textContent = 'Getting location...';
    // Keep exact GPS pin stable while autofilling address fields.
    checkoutGeocodeLockUntil = Date.now() + 10000;
    navigator.geolocation.getCurrentPosition((pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (checkoutMap) checkoutMap.setView([lat, lng], 16);
        setCheckoutPin(lat, lng);
        const manualMode = document.querySelector('input[name="delivery_address_mode"][value="manual"]');
        if (manualMode) {
            manualMode.checked = true;
            toggleDeliveryAddressMode();
        }
        reverseGeocodeForCheckout(lat, lng)
            .then(() => {
                status.textContent = 'Location captured and address autofilled.';
            })
            .catch(() => {
                status.textContent = 'Location captured. Address autofill unavailable.';
            })
            .finally(() => {
                setTimeout(() => {
                    checkoutGeocodeLockUntil = 0;
                }, 400);
            });
    }, () => {
        status.textContent = 'Permission denied. Pin location manually.';
        checkoutGeocodeLockUntil = 0;
    }, { enableHighAccuracy: true, timeout: 10000 });
}

function getManualCheckoutAddress() {
    const parts = [
        document.getElementById('delivery_address_line')?.value || '',
        document.getElementById('delivery_barangay')?.value || '',
        document.getElementById('delivery_city')?.value || '',
        document.getElementById('delivery_province')?.value || '',
        document.getElementById('delivery_postal_code')?.value || '',
        document.getElementById('delivery_country')?.value || 'Philippines'
    ].map((v) => v.trim()).filter(Boolean);
    return parts.join(', ');
}

function geocodeCheckoutAddressDebounced() {
    clearTimeout(checkoutGeocodeDebounce);
    checkoutGeocodeDebounce = setTimeout(() => {
        if (Date.now() < checkoutGeocodeLockUntil) return;
        const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
        if (mode !== 'manual') return;
        const address = getManualCheckoutAddress();
        if (address.length < 8) return;
        geocodeCheckoutAddressToMap(address);
    }, 500);
}

async function geocodeCheckoutAddressToMap(addressText) {
    if (Date.now() < checkoutGeocodeLockUntil) return;
    if (!addressText || addressText.trim().length < 5) return;
    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=${encodeURIComponent(addressText)}`, {
        headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) return;
    const data = await response.json();
    if (!Array.isArray(data) || !data.length) return;
    const lat = Number(data[0].lat);
    const lng = Number(data[0].lon);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    if (checkoutMap) checkoutMap.setView([lat, lng], 16);
    setCheckoutPin(lat, lng);
}

async function reverseGeocodeForCheckout(lat, lng) {
    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, {
        headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) throw new Error('reverse-geocode-failed');
    const data = await response.json();
    const addr = data.address || {};

    const street = [addr.house_number, addr.road].filter(Boolean).join(' ').trim();
    const city = addr.city || addr.town || addr.municipality || addr.county || '';
    const barangayCandidates = [
        addr.suburb,
        addr.neighbourhood,
        addr.village,
        addr.hamlet,
        addr.quarter,
        addr.city_district
    ].filter(Boolean);
    const province = addr.state || '';
    const postal = addr.postcode || '';

    if (street) document.getElementById('delivery_address_line').value = street;
    if (province) setSelectValueWithFallback(document.getElementById('delivery_province'), province);
    let loadedBarangays = [];
    if (city) {
        setSelectValueWithFallback(document.getElementById('delivery_city'), city);
        loadedBarangays = await loadDeliveryBarangays();
    }
    setBarangayValueFromCandidates(document.getElementById('delivery_barangay'), barangayCandidates, loadedBarangays);
    if (postal) document.getElementById('delivery_postal_code').value = postal;
}

function normalizeLocationText(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/\./g, '')
        .replace(/\bbrgy\b/g, '')
        .replace(/\bbarangay\b/g, '')
        .replace(/\bcity\b/g, '')
        .replace(/\bpoblacion\b/g, 'pob')
        .replace(/\s+/g, ' ')
        .trim();
}

function setBarangayValueFromCandidates(selectEl, candidates, availableBarangays = []) {
    if (!selectEl) return false;
    const values = Array.isArray(availableBarangays) && availableBarangays.length
        ? availableBarangays
        : Array.from(selectEl.options).map((opt) => opt.value).filter(Boolean);

    if (!values.length) return false;

    const normalizedValues = values.map((name) => ({
        original: name,
        norm: normalizeLocationText(name)
    }));

    const list = Array.isArray(candidates) ? candidates : [];
    for (const candidateRaw of list) {
        const candidate = String(candidateRaw || '').trim();
        if (!candidate) continue;
        const targetNorm = normalizeLocationText(candidate);
        if (!targetNorm) continue;

        const exact = normalizedValues.find((v) => v.norm === targetNorm);
        if (exact) {
            selectEl.value = exact.original;
            selectEl.dispatchEvent(new Event('change'));
            return true;
        }

        const partial = normalizedValues.find((v) => v.norm.includes(targetNorm) || targetNorm.includes(v.norm));
        if (partial) {
            selectEl.value = partial.original;
            selectEl.dispatchEvent(new Event('change'));
            return true;
        }
    }

    return false;
}

function setSelectValueWithFallback(selectEl, targetValue) {
    if (!selectEl || !targetValue) return false;
    const targetNorm = normalizeLocationText(targetValue);
    let bestValue = '';

    for (const opt of Array.from(selectEl.options)) {
        if (!opt.value) continue;
        const optNorm = normalizeLocationText(opt.value);
        if (optNorm === targetNorm) {
            bestValue = opt.value;
            break;
        }
        if (!bestValue && (optNorm.includes(targetNorm) || targetNorm.includes(optNorm))) {
            bestValue = opt.value;
        }
    }

    if (!bestValue) {
        const extra = document.createElement('option');
        extra.value = targetValue;
        extra.textContent = targetValue;
        selectEl.appendChild(extra);
        bestValue = targetValue;
    }

    selectEl.value = bestValue;
    selectEl.dispatchEvent(new Event('change'));
    return true;
}

function renderDeliveryOptions(select, values, placeholder, selectedValue = '') {
    if (!select) return;
    select.innerHTML = '';
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    select.appendChild(placeholderOption);

    values.forEach((value) => {
        if (!value) return;
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        if (selectedValue === value) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

function getApiItems(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && Array.isArray(payload.data)) {
        return payload.data;
    }
    if (payload && payload.items && Array.isArray(payload.items)) {
        return payload.items;
    }
    return [];
}

function initDeliveryAddressFields() {
    const provinceSelect = document.getElementById('delivery_province');
    const citySelect = document.getElementById('delivery_city');
    if (!provinceSelect || !citySelect || citySelect.dataset.initialized === '1') {
        return;
    }

    const provinces = Object.keys(deliveryAddressData);
    const currentProvince = provinceSelect.value || 'South Cotabato';
    renderDeliveryOptions(provinceSelect, provinces, 'Select Province', currentProvince);
    renderDeliveryOptions(citySelect, Object.keys(deliveryAddressData[currentProvince] || {}), 'Select City / Municipality');
    citySelect.dataset.initialized = '1';

    provinceSelect.addEventListener('change', function () {
        const selectedProvince = provinceSelect.value || 'South Cotabato';
        renderDeliveryOptions(citySelect, Object.keys(deliveryAddressData[selectedProvince] || {}), 'Select City / Municipality');
        renderDeliveryOptions(document.getElementById('delivery_barangay'), [], 'Select Barangay');
        updateDeliveryPostalCode();
        geocodeCheckoutAddressDebounced();
    });

    citySelect.addEventListener('change', function () {
        loadDeliveryBarangays();
        updateDeliveryPostalCode();
        geocodeCheckoutAddressDebounced();
    });

    ['delivery_address_line', 'delivery_barangay', 'delivery_postal_code', 'delivery_country', 'delivery_province']
        .forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('change', geocodeCheckoutAddressDebounced);
            el.addEventListener('input', geocodeCheckoutAddressDebounced);
        });
}

async function loadDeliveryBarangays() {
    const city = document.getElementById('delivery_city')?.value || '';
    const barangaySelect = document.getElementById('delivery_barangay');
    if (!barangaySelect) return;

    let barangays = deliveryBarangayOverrides[city] || [];
    const localityInfo = localityCodeByName[city] || null;

    if (city && localityInfo?.code) {
        if (!Array.isArray(remoteBarangayCache[city])) {
            try {
                const path = localityInfo.type === 'city' ? 'cities' : 'municipalities';
                const res = await fetch(`${PSGC_API_BASE}/${path}/${encodeURIComponent(localityInfo.code)}/barangays?per_page=500`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const payload = await res.json();
                    const apiItems = getApiItems(payload);
                    const apiBarangays = apiItems
                        .map((item) => String(item.name || '').trim())
                        .filter(Boolean);
                    if (apiBarangays.length) {
                        remoteBarangayCache[city] = Array.from(new Set(apiBarangays)).sort((a, b) => a.localeCompare(b));
                    }
                }
            } catch (e) {
                // Keep local overrides only if external lookup fails.
            }
        }

        if (Array.isArray(remoteBarangayCache[city]) && remoteBarangayCache[city].length) {
            barangays = remoteBarangayCache[city];
        }
    }

    if (!Array.isArray(barangays) || !barangays.length) {
        renderDeliveryOptions(barangaySelect, [], 'No barangays loaded');
        return [];
    }

    renderDeliveryOptions(barangaySelect, barangays, 'Select Barangay');
    return barangays;
}

function updateDeliveryPostalCode() {
    const city = document.getElementById('delivery_city')?.value || '';
    const postalInput = document.getElementById('delivery_postal_code');
    if (postalInput && deliveryPostalCodes[city]) {
        postalInput.value = deliveryPostalCodes[city];
    }
}

function toggleDeliveryAddressMode() {
    const mode = document.querySelector('input[name="delivery_address_mode"]:checked')?.value || 'manual';
    const manualFields = document.getElementById('manual_delivery_fields');
    const savedAddressFields = document.getElementById('saved_address_fields');
    const pinSection = document.getElementById('checkout_pin_section');
    const useSavedWithPin = mode === 'saved_address'
        && savedDeliveryLatitude !== null
        && savedDeliveryLongitude !== null;

    if (manualFields) {
        manualFields.style.display = mode === 'manual' ? 'grid' : 'none';
    }
    if (savedAddressFields) {
        savedAddressFields.style.display = mode === 'saved_address' ? 'block' : 'none';
    }
    if (pinSection) {
        pinSection.style.display = useSavedWithPin ? 'none' : 'block';
    }

    if (useSavedWithPin) {
        checkoutMapLocked = true;
        applySavedDeliveryLocation();
    } else if (mode === 'saved_address') {
        checkoutMapLocked = false;
        if (savedDeliveryAddress.trim()) {
            if (!checkoutMap) {
                initCheckoutMap();
            }
            geocodeCheckoutAddressToMap(savedDeliveryAddress);
        }
    } else {
        checkoutMapLocked = false;
        const status = document.getElementById('checkout_geo_status');
        if (status) {
            status.textContent = '';
        }
        if (!checkoutMap) {
            initCheckoutMap();
        } else {
            checkoutMap.invalidateSize();
        }
        geocodeCheckoutAddressDebounced();
        bindCheckoutMapClick();
    }
}

window.processDirectOrder = function processDirectOrder() {
    if (!checkoutTotal || checkoutTotal <= 0) {
        if (typeof showToast === 'function') {
            showToast('Add items to your cart first.', 'error');
        } else {
            alert('Add items to your cart first.');
        }
        return;
    }
    openCheckoutModal();
};

document.addEventListener('click', (event) => {
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal && event.target === checkoutModal) {
        closeCheckoutModal();
    }
});

}
</script>