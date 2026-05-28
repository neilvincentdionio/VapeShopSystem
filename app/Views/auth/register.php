<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Vape Shop System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/background.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
            background: #ffffff;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
        }

        .register-shell {
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
        }

        .form-panel {
            width: 100%;
            background: #f9fbfa;
            padding: 2rem;
            border-radius: 24px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.12);
        }

        .form-header h2 {
            font-size: 1.9rem;
            color: #1a2a24;
            margin-bottom: 0.45rem;
        }

        .form-header p {
            color: #61726b;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .alert,
        .validation-errors {
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #dff5e6;
            color: #175e32;
            border: 1px solid #bfe3ca;
        }

        .alert-error,
        .validation-errors {
            background: #fbe5e8;
            color: #8a2435;
            border: 1px solid #f4c3cc;
        }

        .validation-errors ul {
            margin: 0.55rem 0 0 1.2rem;
        }

        .form-section {
            margin-bottom: 1.25rem;
            padding: 1.2rem;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #e3ebe6;
        }

        .section-tag {
            display: inline-block;
            margin-bottom: 0.5rem;
            color: #27c56f;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .form-section h3 {
            font-size: 1.15rem;
            color: #1a2a24;
            margin-bottom: 0.25rem;
        }

        .section-copy {
            color: #677973;
            font-size: 0.92rem;
            line-height: 1.55;
            margin-bottom: 1rem;
        }

        .input-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .input-grid.single {
            grid-template-columns: minmax(0, 1fr);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .form-group label {
            color: #23352e;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.82rem 0.9rem;
            border-radius: 12px;
            border: 1px solid #d5dfd9;
            background: #fdfefe;
            color: #1f2e28;
            font-size: 0.98rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #27c56f;
            box-shadow: 0 0 0 4px rgba(39, 197, 111, 0.12);
            transform: translateY(-1px);
        }

        .form-group select {
            width: 100%;
            padding: 0.82rem 0.9rem;
            border-radius: 12px;
            border: 1px solid #d5dfd9;
            background: #fdfefe;
            color: #1f2e28;
            font-size: 0.98rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231f2e28' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.9rem center;
            background-size: 1.2rem;
            padding-right: 2.8rem;
        }

        .form-group select::-ms-expand {
            display: none;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap input {
            padding-right: 2.8rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #666666;
            font-size: 1rem;
            line-height: 1;
        }

        .form-group small {
            color: #6c8077;
            line-height: 1.45;
            font-size: 0.84rem;
        }

        .form-help {
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #f0f7f3;
            border: 1px solid #d4e8dd;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #2e5d46;
            line-height: 1.4;
        }

        .map-panel {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e3ebe6;
        }

        .map-panel h4 {
            margin: 0 0 .35rem;
            color: #1a2a24;
            font-size: 1rem;
        }

        .map-panel p {
            margin: 0 0 .75rem;
            color: #677973;
            font-size: .88rem;
            line-height: 1.45;
        }

        .map-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            margin-bottom: .65rem;
        }

        .map-btn {
            border: 1px solid #27c56f;
            background: #fff;
            color: #1d9f57;
            border-radius: 10px;
            padding: .55rem .85rem;
            font-weight: 700;
            cursor: pointer;
        }

        .map-btn:hover {
            background: #f0faf4;
        }

        #register_map {
            height: 240px;
            width: 100%;
            border: 1px solid #d5dfd9;
            border-radius: 12px;
            overflow: hidden;
        }

        #register_map_status {
            margin-top: .55rem;
            font-size: .84rem;
            color: #6c8077;
        }

        .submit-btn {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.95rem 1rem;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #27c56f, #1d9f57);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .submit-btn:hover {
            filter: brightness(1.02);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(29, 159, 87, 0.28);
        }

        .signin-link {
            margin-top: 1rem;
            text-align: center;
        }

        .signin-link a {
            color: #1d9f57;
            text-decoration: none;
            font-weight: 600;
        }

        .signin-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .form-panel {
                width: 100%;
            }

            .form-panel {
                padding: 1.35rem;
            }

            .input-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="register-shell">
        <main class="form-panel">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Set up a customer account for the vape shop storefront. Complete all sections below, then submit your registration.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="validation-errors">
                    <strong>Please fix the following before creating your account:</strong>
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/register') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <section class="form-section">
                    <span class="section-tag">Section 1</span>
                    <h3>Account Info</h3>
                    <p class="section-copy">Use the details you want tied to your VapeShop customer login.</p>

                    <div class="input-grid">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                data-safe-input="person_name"
                                value="<?= esc(old('name')) ?>"
                                autocomplete="name"
                                required
                                placeholder="Enter your full name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= esc(old('email')) ?>"
                                autocomplete="email"
                                required
                                placeholder="Enter your email address"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                    placeholder="Minimum 8 characters"
                                >
                                <button type="button" class="password-toggle" data-target="password" aria-label="Show password">&#128065;</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-input-wrap">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    autocomplete="new-password"
                                    required
                                    placeholder="Re-enter your password"
                                >
                                <button type="button" class="password-toggle" data-target="confirm_password" aria-label="Show password">&#128065;</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 2</span>
                    <h3>ID Verification</h3>
                    <p class="section-copy">Upload a verification ID for age verification to confirm that the customer is of legal age to purchase vape products.</p>

                    <div class="input-grid single">
                        <div class="form-group">
                            <label for="verification_id_image">Verification ID</label>
                            <input
                                type="file"
                                id="verification_id_image"
                                name="verification_id_image"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >
                            <small>Upload a clear photo of a valid government-issued ID. This will be used for age verification only. Accepted formats: JPG, JPEG, PNG, or WEBP. Maximum file size: 3 MB.</small>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 3</span>
                    <h3>Contact</h3>
                    <p class="section-copy">Provide a phone number the shop can use for delivery or order-related updates.</p>

                    <div class="input-grid single">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input
                                type="tel"
                                id="phone_number"
                                name="phone_number"
                                value="<?= esc(old('phone_number')) ?>"
                                autocomplete="tel"
                                required
                                placeholder="e.g. +63 912 345 6789"
                            >
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 4</span>
                    <h3>Address Information</h3>
                    <p class="section-copy">Add the customer delivery address for shipping, fulfillment, and order coordination.</p>

                    <div class="input-grid">
                        <div class="form-group">
                            <label for="address_line">Street Address</label>
                            <input
                                type="text"
                                id="address_line"
                                name="address_line"
                                data-safe-input="address"
                                value="<?= esc(old('address_line')) ?>"
                                autocomplete="street-address"
                                required
                                placeholder="Street / House No."
                            >
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country" required>
                                <option value="Philippines" selected>Philippines</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-grid">
                        <div class="form-group">
                            <label for="province">Province</label>
                            <select id="province" name="province" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="city">City / Municipality</label>
                            <select id="city" name="city" required>
                                <option value="">Select City / Municipality</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-grid">
                        <div class="form-group">
                            <label for="barangay">Barangay</label>
                            <select id="barangay" name="barangay" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                value="<?= esc(old('postal_code')) ?>"
                                placeholder="Postal code"
                                autocomplete="postal-code"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-help">
                        Delivery areas near General Santos City only (South Cotabato, Sarangani, Sultan Kudarat). Select Province, then City, then Barangay.
                    </div>

                    <div class="map-panel">
                        <h4>Pin Delivery Location</h4>
                        <p>This location will be used automatically when you choose <strong>Use My Address</strong> during checkout.</p>
                        <div class="map-toolbar">
                            <button type="button" class="map-btn" id="register_use_location_btn">Use Current Location</button>
                            <span id="register_map_status">Tap the map or use your current location to set your delivery pin.</span>
                        </div>
                        <div id="register_map"></div>
                        <input type="hidden" name="delivery_latitude" id="delivery_latitude" value="<?= esc(old('delivery_latitude')) ?>">
                        <input type="hidden" name="delivery_longitude" id="delivery_longitude" value="<?= esc(old('delivery_longitude')) ?>">
                    </div>
                </section>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="signin-link">
                <a href="<?= site_url('login') ?>">Back to Login</a>
            </div>
        </main>
    </div>

    <script src="<?= base_url('public/assets/js/safe-input.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/gensan-region-address-data.js') ?>"></script>
    <script>
        setTimeout(function () {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);

        document.querySelectorAll('.password-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }

                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });

        // Address System Functionality
        (function() {
            const addressData = GensanRegionAddress.getAddressData();
            const cityPostalCodes = GensanRegionAddress.cityPostalCodes;

            function renderOptions(select, values, placeholder, selectedValue) {
                if (!select) {
                    return;
                }
                select.innerHTML = '';
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = placeholder;
                select.appendChild(placeholderOption);

                values.forEach(function(value) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    if (selectedValue && selectedValue === value) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
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

            function normalizeProvinceName(raw) {
                const norm = normalizeLocationText(raw);
                if (norm.includes('sarangani') && !norm.includes('south cotabato')) {
                    return 'Sarangani';
                }
                if (norm.includes('sultan kudarat')) {
                    return 'Sultan Kudarat';
                }
                if (norm.includes('soccsksargen') || norm.includes('south cotabato')) {
                    return 'South Cotabato';
                }
                return String(raw || '').trim();
            }

            function normalizeCityName(raw) {
                let trimmed = String(raw || '').trim().replace(/^(municipality|city)\s+of\s+/i, '');
                const norm = normalizeLocationText(trimmed);
                if (norm === 'gensan' || norm.includes('general santos')) {
                    return 'General Santos City';
                }
                if (norm === 'alabel' || norm.includes('alabel')) {
                    return 'Alabel';
                }
                if (norm.includes('koronadal')) {
                    return 'Koronadal City';
                }
                if (norm.includes('polomolok')) {
                    return 'Polomolok';
                }
                if (norm.includes('tacurong')) {
                    return 'Tacurong City';
                }
                if (norm.includes('tupi')) {
                    return 'Tupi';
                }
                if (norm.includes('malungon')) {
                    return 'Malungon';
                }
                return trimmed;
            }

            function setSelectValueWithFallback(selectEl, targetValue) {
                if (!selectEl || !targetValue) {
                    return false;
                }
                const targetNorm = normalizeLocationText(targetValue);
                let bestValue = '';

                for (const opt of Array.from(selectEl.options)) {
                    if (!opt.value) {
                        continue;
                    }
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
                return true;
            }

            function setBarangayValueFromCandidates(selectEl, candidates) {
                if (!selectEl) {
                    return false;
                }
                const values = Array.from(selectEl.options)
                    .map((opt) => opt.value)
                    .filter(Boolean);
                if (!values.length) {
                    return false;
                }

                const normalizedValues = values.map((name) => ({
                    original: name,
                    norm: normalizeLocationText(name)
                }));

                const list = Array.isArray(candidates) ? candidates : [];
                for (const candidateRaw of list) {
                    const candidate = String(candidateRaw || '').trim();
                    if (!candidate) {
                        continue;
                    }
                    const targetNorm = normalizeLocationText(candidate);
                    if (!targetNorm) {
                        continue;
                    }

                    const exact = normalizedValues.find((v) => v.norm === targetNorm);
                    if (exact) {
                        selectEl.value = exact.original;
                        return true;
                    }

                    const partial = normalizedValues.find((v) => v.norm.includes(targetNorm) || targetNorm.includes(v.norm));
                    if (partial) {
                        selectEl.value = partial.original;
                        return true;
                    }
                }

                return false;
            }

            async function reverseGeocodeRegisterAddress(lat, lng) {
                const statusEl = document.getElementById('register_map_status');
                if (statusEl) {
                    statusEl.textContent = 'Looking up address for your location...';
                }

                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`,
                        { headers: { 'Accept': 'application/json' } }
                    );
                    if (!response.ok) {
                        throw new Error('reverse-geocode-failed');
                    }

                    const data = await response.json();
                    const addr = data.address || {};
                    const street = [addr.house_number, addr.road].filter(Boolean).join(' ').trim()
                        || addr.pedestrian
                        || addr.residential
                        || '';
                    const city = normalizeCityName(addr.city || addr.town || addr.municipality || addr.county || '');
                    const province = normalizeProvinceName(addr.state || addr.region || '');
                    const barangayCandidates = [
                        addr.suburb,
                        addr.neighbourhood,
                        addr.village,
                        addr.hamlet,
                        addr.quarter,
                        addr.city_district
                    ].filter(Boolean);
                    let postal = addr.postcode || '';

                    const streetInput = document.getElementById('address_line');
                    if (street && streetInput) {
                        streetInput.value = street;
                    }

                    const provinceSelect = document.getElementById('province');
                    const citySelect = document.getElementById('city');
                    const barangaySelect = document.getElementById('barangay');

                    if (province && provinceSelect) {
                        setSelectValueWithFallback(provinceSelect, province);
                        provinceSelect.dispatchEvent(new Event('change'));
                    }
                    if (city && citySelect) {
                        setSelectValueWithFallback(citySelect, city);
                        citySelect.dispatchEvent(new Event('change'));
                    }
                    loadBarangays('');
                    setBarangayValueFromCandidates(barangaySelect, barangayCandidates);
                    updatePostalCodeByCity();

                    const postalInput = document.getElementById('postal_code');
                    if (postal && postalInput) {
                        postalInput.value = postal;
                    } else if (postalInput && !postalInput.value && city === 'General Santos City') {
                        postalInput.value = '9500';
                    }

                    if (statusEl) {
                        statusEl.textContent = 'Location captured and address autofilled.';
                    }
                } catch (error) {
                    if (statusEl) {
                        statusEl.textContent = 'Location captured. Adjust address fields if needed.';
                    }
                }
            }

            window.reverseGeocodeRegisterAddress = reverseGeocodeRegisterAddress;

            function loadProvinces() {
                const provinceSelect = document.getElementById('province');
                if (provinceSelect) {
                    renderOptions(
                        provinceSelect,
                        GensanRegionAddress.getProvinceNames(),
                        'Select Province',
                        GensanRegionAddress.defaultProvince
                    );
                }
            }

            function loadCities(selected) {
                const provinceSelect = document.getElementById('province');
                const citySelect = document.getElementById('city');
                const province = provinceSelect ? provinceSelect.value : '';
                const cities = province && addressData[province] ? Object.keys(addressData[province]) : [];
                renderOptions(citySelect, cities, 'Select City', selected || '<?= esc(old('city')) ?>');
            }

            function loadBarangays(selected) {
                const provinceSelect = document.getElementById('province');
                const citySelect = document.getElementById('city');
                const barangaySelect = document.getElementById('barangay');
                const province = provinceSelect ? provinceSelect.value : '';
                const city = citySelect ? citySelect.value : '';
                let barangays = [];
                if (province && city && addressData[province] && addressData[province][city]) {
                    barangays = addressData[province][city];
                }
                renderOptions(barangaySelect, barangays, 'Select Barangay', selected || '<?= esc(old('barangay')) ?>');
            }

            function updatePostalCodeByCity() {
                const citySelect = document.getElementById('city');
                const postalCodeInput = document.getElementById('postal_code');
                if (!postalCodeInput || !citySelect) {
                    return;
                }
                const city = citySelect.value || '';
                const postal = cityPostalCodes[city] || '';
                if (postal !== '') {
                    postalCodeInput.value = postal;
                }
            }

            // Initialize the address system
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');

            if (provinceSelect && citySelect && barangaySelect) {
                loadProvinces();
                loadCities('<?= esc(old('city')) ?>');
                loadBarangays('<?= esc(old('barangay')) ?>');
                updatePostalCodeByCity();

                provinceSelect.addEventListener('change', function() {
                    loadCities('');
                    loadBarangays('');
                    if (citySelect) {
                        citySelect.focus();
                    }
                });

                citySelect.addEventListener('change', function() {
                    loadBarangays('');
                    updatePostalCodeByCity();
                    if (barangaySelect) {
                        barangaySelect.focus();
                    }
                });
            }
        })();

        (function initRegistrationMap() {
            const defaultLat = 6.1164;
            const defaultLng = 125.1716;
            const latInput = document.getElementById('delivery_latitude');
            const lngInput = document.getElementById('delivery_longitude');
            const statusEl = document.getElementById('register_map_status');
            const form = document.querySelector('form[action*="auth/register"]');
            let registerMap = null;
            let registerMarker = null;

            function setRegisterPin(lat, lng, message) {
                if (!latInput || !lngInput) {
                    return;
                }
                latInput.value = String(lat);
                lngInput.value = String(lng);
                if (!registerMap || typeof L === 'undefined') {
                    return;
                }
                if (!registerMarker) {
                    registerMarker = L.marker([lat, lng]).addTo(registerMap);
                } else {
                    registerMarker.setLatLng([lat, lng]);
                }
                registerMap.setView([lat, lng], Math.max(registerMap.getZoom(), 15));
                if (message && statusEl) {
                    statusEl.textContent = message;
                }
            }

            function initMap() {
                if (typeof L === 'undefined' || registerMap) {
                    return;
                }
                const initialLat = parseFloat(latInput?.value || '') || defaultLat;
                const initialLng = parseFloat(lngInput?.value || '') || defaultLng;
                registerMap = L.map('register_map').setView([initialLat, initialLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(registerMap);
                setRegisterPin(initialLat, initialLng);
                registerMap.on('click', (event) => {
                    setRegisterPin(event.latlng.lat, event.latlng.lng, 'Delivery pin updated.');
                });
                setTimeout(() => registerMap.invalidateSize(), 250);
            }

            document.getElementById('register_use_location_btn')?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    if (statusEl) {
                        statusEl.textContent = 'Geolocation is not supported on this device.';
                    }
                    return;
                }
                if (statusEl) {
                    statusEl.textContent = 'Getting your current location...';
                }
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    setRegisterPin(lat, lng, 'Getting address details...');
                    if (typeof window.reverseGeocodeRegisterAddress === 'function') {
                        await window.reverseGeocodeRegisterAddress(lat, lng);
                    } else if (statusEl) {
                        statusEl.textContent = 'Current location saved as your delivery pin.';
                    }
                }, () => {
                    if (statusEl) {
                        statusEl.textContent = 'Unable to get location. Please tap the map to set your pin.';
                    }
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            form?.addEventListener('submit', (event) => {
                if (!latInput?.value || !lngInput?.value) {
                    event.preventDefault();
                    alert('Please pin your delivery location on the map before creating your account.');
                    if (statusEl) {
                        statusEl.textContent = 'Delivery location is required.';
                    }
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMap);
            } else {
                initMap();
            }
        })();
    </script>
</body>
</html>
