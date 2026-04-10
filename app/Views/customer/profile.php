<?= $this->include('customer/partials/header') ?>

<style>
    .profile-card h1 {
        font-size: 1.35rem;
        margin-bottom: .3rem;
    }

    .profile-card p {
        color: #666666;
        margin-bottom: .9rem;
    }

    .profile-row {
        margin-top: .72rem;
        padding: .65rem .75rem;
        border-radius: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
    }

    .profile-label {
        display: block;
        color: #666666;
        font-size: .82rem;
        margin-bottom: .2rem;
    }

    .profile-row-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
    }

    .profile-value {
        font-size: .96rem;
        font-weight: 600;
        color: #333333;
        flex: 1;
    }

    .profile-value-input {
        display: none;
        flex: 1;
    }

    .profile-edit-btn {
        border: 1px solid #d0d3d8;
        background: #ffffff;
        color: #4d5561;
        border-radius: 8px;
        padding: .32rem .7rem;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
    }

    .profile-edit-btn:hover {
        background: #f3f4f6;
        border-color: #c5c9cf;
    }

    .profile-edit-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    .profile-editor {
        margin-top: .55rem;
        display: none;
    }

    .profile-row.is-editing .profile-editor {
        display: block;
    }

    .profile-row.is-editing .profile-value {
        display: none;
    }

    .profile-row.is-editing .profile-value-input {
        display: block;
    }

    .profile-row.is-editing .profile-row-main {
        justify-content: flex-end;
    }

    .profile-input {
        width: 100%;
        padding: .56rem .7rem;
        border-radius: 8px;
        border: 1px solid #d7d9dd;
        background: #ffffff;
        color: #333333;
        font-size: .95rem;
    }

    .profile-input:focus {
        outline: none;
        border-color: #27c56f;
        box-shadow: 0 0 0 3px rgba(39, 197, 111, 0.14);
    }

    .profile-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .55rem;
    }

    .profile-field-group {
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .profile-field-label {
        font-size: .78rem;
        color: #5e6773;
        font-weight: 600;
    }

    .profile-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: linear-gradient(45deg, transparent 50%, #4d5561 50%), linear-gradient(135deg, #4d5561 50%, transparent 50%);
        background-position: calc(100% - 18px) calc(50% + 1px), calc(100% - 13px) calc(50% + 1px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
        padding-right: 2rem;
    }

    .profile-help {
        margin-top: .35rem;
        color: #666666;
        font-size: .8rem;
    }

    .profile-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: flex-end;
    }

    .profile-save-btn {
        border: 1px solid #27c56f;
        background: #27c56f;
        color: #ffffff;
        border-radius: 8px;
        padding: .6rem .9rem;
        font-size: .92rem;
        font-weight: 600;
        cursor: pointer;
    }

    .profile-save-btn:hover {
        background: #22ac61;
        border-color: #22ac61;
    }

    .validation-errors {
        margin-bottom: .9rem;
        padding: .86rem 1rem;
        border-radius: 10px;
        border: 1px solid rgba(220, 53, 69, 0.3);
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
    }

    .validation-errors ul {
        margin: .4rem 0 0 1.1rem;
    }

    @media (max-width: 720px) {
        .profile-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="panel profile-card">
    <?php
    $customerAccount = $customer_account ?? [];
    $combinedAddress = implode(', ', array_filter([
        $customerAccount['address_line'] ?? '',
        $customerAccount['barangay'] ?? '',
        $customerAccount['city'] ?? '',
        $customerAccount['province'] ?? '',
        $customerAccount['postal_code'] ?? '',
        $customerAccount['country'] ?? '',
    ]));
    $hasErrors = !empty(session()->getFlashdata('errors'));
    ?>
    <h1>Profile</h1>
    <p>Manage your customer details and keep account information updated.</p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="validation-errors">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('dashboard/profile/update') ?>" method="post">
        <?= csrf_field() ?>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="name">
            <span class="profile-label">Name</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($user_name ?? '') ?></span>
                <input class="profile-input profile-value-input" type="text" name="name" value="<?= esc(old('name', $customerAccount['name'] ?? ($user_name ?? ''))) ?>" required>
                <button type="button" class="profile-edit-btn" data-edit-target="name">Edit</button>
            </div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="email">
            <span class="profile-label">Email</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($user_email ?? '') ?></span>
                <input class="profile-input profile-value-input" type="email" name="email" value="<?= esc(old('email', $customerAccount['email'] ?? ($user_email ?? ''))) ?>" required>
                <button type="button" class="profile-edit-btn" data-edit-target="email">Edit</button>
            </div>
        </div>

        <div class="profile-row" data-row="role">
            <span class="profile-label">Role</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc(ucfirst((string) ($user_role ?? 'customer'))) ?></span>
                <button type="button" class="profile-edit-btn" data-role-locked="1" title="Role is managed by admin.">Edit</button>
            </div>
            <div class="profile-help">Role is managed by admin.</div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="phone">
            <span class="profile-label">Phone Number</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($customerAccount['phone_number'] ?? 'Not set') ?></span>
                <input class="profile-input profile-value-input" type="text" name="phone_number" value="<?= esc(old('phone_number', $customerAccount['phone_number'] ?? '')) ?>" placeholder="+63 900 000 0000">
                <button type="button" class="profile-edit-btn" data-edit-target="phone">Edit</button>
            </div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="address" data-focus-selector="#address-province">
            <span class="profile-label">Address</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($combinedAddress !== '' ? $combinedAddress : 'Not set') ?></span>
                <button type="button" class="profile-edit-btn" data-edit-target="address">Edit</button>
            </div>
            <div class="profile-editor">
                <div class="profile-grid-2">
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="address-line-input">Street</label>
                        <input class="profile-input" type="text" id="address-line-input" name="address_line" value="<?= esc(old('address_line', $customerAccount['address_line'] ?? '')) ?>" placeholder="Street / House No.">
                    </div>
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="country-input">Country</label>
                        <select class="profile-input profile-select" name="country" id="country-input">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="address-province">Province</label>
                        <select class="profile-input profile-select" name="province" id="address-province">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="address-city">City / Municipality</label>
                        <select class="profile-input profile-select" name="city" id="address-city">
                            <option value="">Select City / Municipality</option>
                        </select>
                    </div>
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="address-barangay">Barangay</label>
                        <select class="profile-input profile-select" name="barangay" id="address-barangay">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="profile-field-group">
                        <label class="profile-field-label" for="postal-code-input">Postal Code</label>
                        <input class="profile-input" type="text" id="postal-code-input" name="postal_code" value="<?= esc(old('postal_code', $customerAccount['postal_code'] ?? '')) ?>" placeholder="Postal code">
                    </div>
                </div>
                <div class="profile-help">Select Province, then City, then Barangay. Country is set to Philippines.</div>
            </div>
        </div>

        <div class="profile-row">
            <span class="profile-label">ID Verification</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= !empty($customerAccount['verification_id_path']) ? 'ID uploaded' : 'No ID uploaded' ?></span>
            </div>
        </div>

        <?php if (!empty($user_shop_name)): ?>
            <div class="profile-row">
                <span class="profile-label">Shop Name</span>
                <div class="profile-row-main">
                    <span class="profile-value"><?= esc($user_shop_name) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <input type="hidden" name="new_password" value="">
        <input type="hidden" name="confirm_password" value="">

        <div class="profile-actions">
            <button type="submit" class="profile-save-btn">Save Profile</button>
        </div>
    </form>
</section>

<script>
    (function() {
        const addressData = {
            'Agusan del Norte': { 'Butuan City': [], 'Cabadbaran City': [] },
            'Agusan del Sur': { 'Bayugan City': [] },
            'Basilan': { 'Isabela City': [], 'Lamitan City': [] },
            'Bukidnon': { 'Malaybalay City': [], 'Valencia City': [] },
            'Camiguin': {
                'Catarman': [], 'Guinsiliban': [], 'Mahinog': [], 'Mambajao': [], 'Sagay': []
            },
            'Cotabato': { 'Kidapawan City': [] },
            'Davao de Oro': {
                'Compostela': [], 'Laak': [], 'Mabini': [], 'Maco': [], 'Maragusan': [], 'Mawab': [],
                'Monkayo': [], 'Montevista': [], 'Nabunturan': [], 'New Bataan': [], 'Pantukan': []
            },
            'Davao del Norte': { 'Panabo City': [], 'Samal City': [], 'Tagum City': [] },
            'Davao del Sur': { 'Davao City': [], 'Digos City': [] },
            'Davao Occidental': {
                'Don Marcelino': [], 'Jose Abad Santos': [], 'Malita': [], 'Santa Maria': [], 'Sarangani': []
            },
            'Davao Oriental': { 'Mati City': [] },
            'Dinagat Islands': {
                'Basilisa': [], 'Cagdianao': [], 'Dinagat': [], 'Libjo': [], 'Loreto': [],
                'San Jose': [], 'Tubajon': []
            },
            'Lanao del Norte': { 'Iligan City': [] },
            'Lanao del Sur': { 'Marawi City': [] },
            'Maguindanao del Norte': {
                'Barira': [], 'Buldon': [], 'Datu Blah Sinsuat': [], 'Datu Odin Sinsuat': [],
                'Kabuntalan': [], 'Matanog': [], 'Northern Kabuntalan': [], 'Parang': [], 'Sultan Kudarat': []
            },
            'Maguindanao del Sur': {
                'Ampatuan': [], 'Buluan': [], 'Datu Abdullah Sangki': [], 'Datu Anggal Midtimbang': [],
                'Datu Hoffer Ampatuan': [], 'Datu Paglas': [], 'Datu Piang': [], 'Datu Salibo': [],
                'Datu Saudi-Ampatuan': [], 'General S.K. Pendatun': [], 'Guindulungan': [], 'Mamasapano': [],
                'Mangudadatu': [], 'Pagalungan': [], 'Paglat': [], 'Pandag': [], 'Rajah Buayan': [],
                'Shariff Aguak': [], 'Shariff Saydona Mustapha': [], 'South Upi': [], 'Sultan sa Barongis': [],
                'Talayan': []
            },
            'Misamis Occidental': { 'Oroquieta City': [], 'Ozamiz City': [], 'Tangub City': [] },
            'Misamis Oriental': { 'Cagayan de Oro City': [], 'El Salvador City': [], 'Gingoog City': [] },
            'Sarangani': {
                'Alabel': [], 'Glan': [], 'Kiamba': [], 'Maasim': [], 'Maitum': [], 'Malapatan': [], 'Malungon': []
            },
            'South Cotabato': { 'General Santos City': [], 'Koronadal City': [] },
            'Sultan Kudarat': { 'Tacurong City': [] },
            'Sulu': {
                'Banguingui': [], 'Hadji Panglima Tahil': [], 'Indanan': [], 'Jolo': [], 'Kalingalan Caluang': [],
                'Lugus': [], 'Luuk': [], 'Maimbung': [], 'Old Panamao': [], 'Omar': [], 'Pandami': [],
                'Panglima Estino': [], 'Pangutaran': [], 'Parang': [], 'Pata': [], 'Patikul': [],
                'Siasi': [], 'Talipao': [], 'Tapul': []
            },
            'Surigao del Norte': { 'Surigao City': [] },
            'Surigao del Sur': { 'Bislig City': [], 'Tandag City': [] },
            'Tawi-Tawi': {
                'Bongao': [], 'Languyan': [], 'Mapun': [], 'Panglima Sugala': [], 'Sapa-Sapa': [],
                'Sibutu': [], 'Simunul': [], 'Sitangkai': [], 'South Ubian': [], 'Tandubas': [], 'Turtle Islands': []
            },
            'Zamboanga del Norte': { 'Dapitan City': [], 'Dipolog City': [] },
            'Zamboanga del Sur': { 'Pagadian City': [], 'Zamboanga City': [] },
            'Zamboanga Sibugay': {
                'Alicia': [], 'Buug': [], 'Diplahan': [], 'Imelda': [], 'Ipil': [], 'Kabasalan': [],
                'Mabuhay': [], 'Malangas': [], 'Naga': [], 'Olutanga': [], 'Payao': [], 'Roseller Lim': [],
                'Siay': [], 'Talusan': [], 'Titay': [], 'Tungawan': []
            }
        };

        const defaultBarangayList = [
            'Poblacion', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4',
            'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10'
        ];

        const cityBarangayOverrides = {
            'General Santos City': [
                'Apopong', 'Baluan', 'Bawing', 'Buayan', 'Bula', 'Calumpang', 'City Heights',
                'Conel', 'Dadiangas East', 'Dadiangas North', 'Dadiangas South', 'Dadiangas West',
                'Fatima', 'Katangawan', 'Labangal', 'Lagao', 'Ligaya', 'Mabuhay', 'Olympog',
                'San Isidro', 'San Jose', 'Siguel', 'Sinawal', 'Tambler', 'Tinagacan', 'Upper Labay'
            ],
            'Davao City': ['Buhangin', 'Calinan', 'Matina', 'Poblacion', 'Talomo', 'Tugbok'],
            'Cagayan de Oro City': ['Balulang', 'Bugo', 'Carmen', 'Gusa', 'Kauswagan', 'Lapasan', 'Macabalan', 'Nazareth'],
            'Zamboanga City': ['Ayala', 'Baliwasan', 'Canelar', 'Divisoria', 'Guiwan', 'Pasonanca', 'Putik', 'Tetuan'],
            'Butuan City': ['Agao', 'Ambago', 'Ampayon', 'Baan', 'Bancasi', 'Dumalagan', 'Lemon', 'Maon'],
            'Iligan City': ['Bagong Silang', 'Hinaplanon', 'Pala-o', 'Poblacion', 'San Miguel', 'Saray', 'Tambacan'],
            'Koronadal City': ['Avancena', 'Cacub', 'Caloocan', 'Carpenter Hill', 'Concepcion', 'General Paulino Santos', 'Mabini'],
            'Kidapawan City': ['Amas', 'Balabag', 'Binoligan', 'Lanao', 'Magsaysay', 'Nuangan', 'Perez'],
            'Marawi City': ['Banggolo', 'Basak Malutlut', 'Datu sa Dansalan', 'Lilod Madaya', 'Marinaut', 'Poblacion'],
            'Surigao City': ['Anomar', 'Canlanipa', 'Luna', 'Mabua', 'Nabago', 'Punta Bilar']
        };

        const provinceSelect = document.getElementById('address-province');
        const citySelect = document.getElementById('address-city');
        const barangaySelect = document.getElementById('address-barangay');
        const postalCodeInput = document.getElementById('postal-code-input');

        const selectedProvince = <?= json_encode((string) old('province', $customerAccount['province'] ?? '')) ?>;
        let selectedCity = <?= json_encode((string) old('city', $customerAccount['city'] ?? '')) ?>;
        if (selectedCity.toLowerCase() === 'gensan') {
            selectedCity = 'General Santos City';
        }
        const selectedBarangay = <?= json_encode((string) old('barangay', $customerAccount['barangay'] ?? '')) ?>;

        const cityPostalCodes = {
            'Butuan City': '8600',
            'Cabadbaran City': '8605',
            'Bayugan City': '8502',
            'Isabela City': '7300',
            'Lamitan City': '7302',
            'Malaybalay City': '8700',
            'Valencia City': '8709',
            'Kidapawan City': '9400',
            'Panabo City': '8105',
            'Samal City': '8119',
            'Tagum City': '8100',
            'Davao City': '8000',
            'Digos City': '8002',
            'Mati City': '8200',
            'Iligan City': '9200',
            'Marawi City': '9700',
            'Oroquieta City': '7207',
            'Ozamiz City': '7200',
            'Tangub City': '7214',
            'Cagayan de Oro City': '9000',
            'El Salvador City': '9017',
            'Gingoog City': '9014',
            'Alabel': '9501',
            'Glan': '9517',
            'Kiamba': '9514',
            'Maasim': '9502',
            'Maitum': '9515',
            'Malapatan': '9516',
            'Malungon': '9503',
            'General Santos City': '9500',
            'Koronadal City': '9506',
            'Tacurong City': '9800',
            'Surigao City': '8400',
            'Bislig City': '8311',
            'Tandag City': '8300',
            'Dapitan City': '7101',
            'Dipolog City': '7100',
            'Pagadian City': '7016',
            'Zamboanga City': '7000',
            'Ipil': '7001',
            'Jolo': '7400',
            'Bongao': '7500'
        };

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

        function loadProvinces() {
            renderOptions(provinceSelect, Object.keys(addressData), 'Select Province', selectedProvince);
        }

        function loadCities(selected) {
            const province = provinceSelect ? provinceSelect.value : '';
            const cities = province && addressData[province] ? Object.keys(addressData[province]) : [];
            renderOptions(citySelect, cities, 'Select City', selected || '');
        }

        function loadBarangays(selected) {
            const province = provinceSelect ? provinceSelect.value : '';
            const city = citySelect ? citySelect.value : '';
            let barangays = [];
            if (province && city && addressData[province] && Object.prototype.hasOwnProperty.call(addressData[province], city)) {
                barangays = cityBarangayOverrides[city] || addressData[province][city] || [];
                if (!Array.isArray(barangays) || barangays.length === 0) {
                    barangays = defaultBarangayList;
                }
            }
            renderOptions(barangaySelect, barangays, 'Select Barangay', selected || '');
        }

        function updatePostalCodeByCity() {
            if (!postalCodeInput || !citySelect) {
                return;
            }
            const city = citySelect.value || '';
            const postal = cityPostalCodes[city] || '';
            if (postal !== '') {
                postalCodeInput.value = postal;
            }
        }

        if (provinceSelect && citySelect && barangaySelect) {
            loadProvinces();
            loadCities(selectedCity);
            loadBarangays(selectedBarangay);
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

        document.querySelectorAll('[data-edit-target]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const target = btn.getAttribute('data-edit-target');
                const row = document.querySelector('[data-row="' + target + '"]');
                if (!row) {
                    return;
                }
                row.classList.toggle('is-editing');
                if (row.classList.contains('is-editing')) {
                    const customFocusSelector = row.getAttribute('data-focus-selector');
                    const input = customFocusSelector
                        ? row.querySelector(customFocusSelector)
                        : row.querySelector('.profile-value-input, .profile-editor input, .profile-editor select');
                    if (input) {
                        input.focus();
                        if (typeof input.select === 'function') {
                            input.select();
                        }
                    }
                }
            });
        });

        document.querySelectorAll('[data-role-locked]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                alert('Role can only be changed by admin.');
            });
        });
    })();
</script>

<?= $this->include('customer/partials/footer') ?>
