<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Vape Shop System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/background.css') ?>">
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
                                <option value="South Cotabato" selected>South Cotabato</option>
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
                        Select Province, then City, then Barangay. Country is set to Philippines.
                    </div>
                </section>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="signin-link">
                <a href="<?= site_url('login') ?>">Back to Login</a>
            </div>
        </main>
    </div>

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
                const provinceSelect = document.getElementById('province');
                if (provinceSelect) {
                    renderOptions(provinceSelect, ['South Cotabato'], 'Select Province', 'South Cotabato');
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
                if (province && city && addressData[province] && Object.prototype.hasOwnProperty.call(addressData[province], city)) {
                    barangays = cityBarangayOverrides[city] || addressData[province][city] || [];
                    if (!Array.isArray(barangays) || barangays.length === 0) {
                        barangays = defaultBarangayList;
                    }
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
    </script>
</body>
</html>
