<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Profile') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; --accent: #27c56f; }
        body { font-family: var(--main-font); background: #f5f7fa; min-height: 100vh; color: #333; }
        .container { max-width: 960px; margin: 0 auto; padding: 1.5rem 2rem 2.5rem; }
        .alert { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .92rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .validation-errors { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 10px; padding: .85rem 1rem; margin-bottom: 1rem; }
        .validation-errors ul { margin: .45rem 0 0 1.2rem; }
        .page-header {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 16px;
            padding: 1.35rem 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .page-header h1 { font-size: 1.55rem; font-weight: 800; margin-bottom: .3rem; }
        .page-header p { color: #666; font-size: .92rem; line-height: 1.5; }
        .profile-grid { display: grid; gap: 1.1rem; }
        .profile-card {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 16px;
            padding: 1.25rem 1.35rem; box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }
        .profile-card h2 {
            font-size: 1rem; font-weight: 800; margin-bottom: 1rem;
            display: flex; align-items: center; gap: .45rem; color: #1e293b;
        }
        .profile-card h2 i { color: var(--accent); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
        .form-grid .full { grid-column: 1 / -1; }
        .field { display: flex; flex-direction: column; gap: .35rem; }
        .field label { font-size: .82rem; font-weight: 700; color: #475569; }
        .field input, .field textarea {
            width: 100%; padding: .68rem .8rem; border: 1px solid #d7dce1;
            border-radius: 10px; font-size: .92rem; font-family: inherit;
        }
        .field input:focus, .field textarea:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(39, 197, 111, .14);
        }
        .field textarea { min-height: 88px; resize: vertical; }
        .field-hint { font-size: .78rem; color: #64748b; line-height: 1.4; }
        .map-toolbar { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; margin-bottom: .55rem; }
        .map-btn {
            border: 1px solid #27c56f; background: rgba(39, 197, 111, .1);
            color: #1d9f57; border-radius: 8px; padding: .5rem .75rem;
            font-weight: 700; font-size: .82rem; cursor: pointer;
        }
        .map-btn:hover { background: #27c56f; color: #fff; }
        #shopMap { height: 260px; border: 1px solid #e0e0e0; border-radius: 12px; }
        .coords-readout { margin-top: .45rem; font-size: .8rem; color: #64748b; }
        .form-actions {
            display: flex; justify-content: flex-end; gap: .65rem;
            margin-top: 1.1rem; padding-top: 1rem; border-top: 1px solid #eef2f7;
        }
        .btn-save {
            background: linear-gradient(135deg, #27c56f, #219653);
            color: #fff; border: none; border-radius: 10px;
            padding: .72rem 1.35rem; font-weight: 700; font-size: .92rem; cursor: pointer;
        }
        .btn-save:hover { filter: brightness(1.03); }
        .role-badge {
            display: inline-block; margin-top: .5rem;
            background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;
            border-radius: 999px; padding: .2rem .65rem; font-size: .78rem; font-weight: 700;
        }
        @media (max-width: 720px) {
            .container { padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <div class="page-header">
            <h1>Admin Profile</h1>
            <p>Update your account details and shop pickup location. Riders, customers, and order maps use this shop address automatically.</p>
            <span class="role-badge"><?= esc(ucfirst((string) ($user_role ?? 'admin'))) ?></span>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors) && is_array($errors)): ?>
            <div class="validation-errors">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc(is_array($error) ? implode(', ', $error) : $error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
            $admin = is_array($admin_account ?? null) ? $admin_account : [];
            $shop = is_array($shop_settings ?? null) ? $shop_settings : [];
            $shopLat = old('shop_latitude', $shop['shop_latitude'] ?? 6.1352000);
            $shopLng = old('shop_longitude', $shop['shop_longitude'] ?? 125.2179000);
        ?>

        <form method="post" action="<?= site_url('dashboard/profile/update') ?>" class="profile-grid" id="adminProfileForm">
            <?= csrf_field() ?>

            <div class="profile-card">
                <h2><i class="fas fa-user"></i> Account</h2>
                <div class="form-grid">
                    <div class="field">
                        <label for="admin_name">Full Name</label>
                        <input type="text" id="admin_name" name="name" required
                               value="<?= esc(old('name', $admin['name'] ?? $user_name ?? '')) ?>">
                    </div>
                    <div class="field">
                        <label for="admin_email">Email</label>
                        <input type="email" id="admin_email" name="email" required
                               value="<?= esc(old('email', $admin['email'] ?? $user_email ?? '')) ?>">
                    </div>
                    <div class="field">
                        <label for="admin_phone">Contact Number</label>
                        <input type="text" id="admin_phone" name="phone_number"
                               value="<?= esc(old('phone_number', $admin['phone_number'] ?? '')) ?>"
                               placeholder="+63 9XX XXX XXXX">
                    </div>
                    <div class="field">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" autocomplete="new-password"
                               placeholder="Leave blank to keep current">
                    </div>
                    <div class="field full">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <h2><i class="fas fa-store"></i> Shop Information</h2>
                <div class="form-grid">
                    <div class="field full">
                        <label for="shop_name">Shop Name</label>
                        <input type="text" id="shop_name" name="shop_name" required
                               value="<?= esc(old('shop_name', $shop['shop_name'] ?? $user_shop_name ?? '')) ?>">
                    </div>
                    <div class="field">
                        <label for="shop_phone">Shop Phone</label>
                        <input type="text" id="shop_phone" name="shop_phone"
                               value="<?= esc(old('shop_phone', $shop['shop_phone'] ?? '')) ?>"
                               placeholder="Displayed for riders/customers">
                    </div>
                    <div class="field full">
                        <label for="shop_address">Shop Address</label>
                        <textarea id="shop_address" name="shop_address" required
                                  placeholder="Street, barangay, city, province"><?= esc(old('shop_address', $shop['shop_address'] ?? '')) ?></textarea>
                        <span class="field-hint">Used on rider pickup maps, order details, and delivery routing.</span>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <h2><i class="fas fa-map-marker-alt"></i> Shop Location on Map</h2>
                <p class="field-hint" style="margin-bottom:.65rem;">Pin where riders pick up orders from your shop.</p>
                <div class="map-toolbar">
                    <button type="button" class="map-btn" onclick="shopMapUseCurrentLocation()">Use Current Location</button>
                    <span id="shopMapStatus" class="field-hint"></span>
                </div>
                <div id="shopMap"></div>
                <div class="coords-readout" id="shopCoordsReadout"></div>
                <input type="hidden" name="shop_latitude" id="shop_latitude" value="<?= esc((string) $shopLat) ?>">
                <input type="hidden" name="shop_longitude" id="shop_longitude" value="<?= esc((string) $shopLng) ?>">
            </div>

            <div class="profile-card">
                <div class="form-actions">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Profile &amp; Shop Location</button>
                </div>
            </div>
        </form>
    </div>

<script>
(function () {
    const defaultLat = <?= json_encode((float) $shopLat) ?>;
    const defaultLng = <?= json_encode((float) $shopLng) ?>;
    let shopMap = null;
    let shopMarker = null;

    function updateCoords(lat, lng) {
        document.getElementById('shop_latitude').value = String(lat);
        document.getElementById('shop_longitude').value = String(lng);
        const readout = document.getElementById('shopCoordsReadout');
        if (readout) {
            readout.textContent = 'Pinned: ' + Number(lat).toFixed(6) + ', ' + Number(lng).toFixed(6);
        }
    }

    function setShopPin(lat, lng) {
        if (!shopMap) return;
        if (!shopMarker) {
            shopMarker = L.marker([lat, lng], { draggable: true }).addTo(shopMap);
            shopMarker.on('dragend', () => {
                const pos = shopMarker.getLatLng();
                updateCoords(pos.lat, pos.lng);
            });
        } else {
            shopMarker.setLatLng([lat, lng]);
        }
        shopMap.setView([lat, lng], shopMap.getZoom() < 14 ? 15 : shopMap.getZoom());
        updateCoords(lat, lng);
    }

    function initShopMap() {
        const lat = parseFloat(document.getElementById('shop_latitude').value) || defaultLat;
        const lng = parseFloat(document.getElementById('shop_longitude').value) || defaultLng;
        shopMap = L.map('shopMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(shopMap);
        setShopPin(lat, lng);
        shopMap.on('click', (e) => setShopPin(e.latlng.lat, e.latlng.lng));
        setTimeout(() => shopMap.invalidateSize(), 200);
    }

    window.shopMapUseCurrentLocation = function () {
        const status = document.getElementById('shopMapStatus');
        if (!navigator.geolocation) {
            if (status) status.textContent = 'Geolocation is not supported on this device.';
            return;
        }
        if (status) status.textContent = 'Getting location...';
        navigator.geolocation.getCurrentPosition((position) => {
            setShopPin(position.coords.latitude, position.coords.longitude);
            if (status) status.textContent = 'Location updated from your device.';
        }, () => {
            if (status) status.textContent = 'Unable to get current location.';
        }, { enableHighAccuracy: true, timeout: 8000 });
    };

    document.getElementById('adminProfileForm').addEventListener('submit', function (e) {
        const lat = document.getElementById('shop_latitude').value;
        const lng = document.getElementById('shop_longitude').value;
        if (!lat || !lng) {
            e.preventDefault();
            alert('Please pin your shop location on the map.');
        }
    });

    document.addEventListener('DOMContentLoaded', initShopMap);
})();
</script>
</body>
</html>
