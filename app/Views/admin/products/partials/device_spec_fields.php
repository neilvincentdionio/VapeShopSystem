<?php
helper('product');

$deviceTypeOptions = product_device_type_options();
$chargingPortOptions = product_charging_port_options();
$selectedDeviceType = normalize_device_type(old('device_type', $product['device_type'] ?? ''));
$deviceFieldVisibility = $selectedDeviceType !== null
    ? device_type_field_visibility($selectedDeviceType)
    : ['battery_capacity' => false, 'wattage_range' => false, 'charging_port' => false, 'compatibility' => false];
?>
<div class="form-section device-spec-section" style="display: none;">
    <h4 class="section-title"><i class="fas fa-microchip"></i> Device Specifications</h4>
    <p class="section-description">Choose a device type first. Only the relevant specification fields will appear.</p>
    <div class="form-grid">
        <div class="form-group">
            <label for="device_type">Device Type *</label>
            <select name="device_type" id="device_type" class="form-control">
                <option value="">Select device type</option>
                <?php foreach ($deviceTypeOptions as $slug => $label): ?>
                    <option value="<?= esc($slug) ?>" <?= $selectedDeviceType === $slug ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group device-spec-field" data-device-field="battery_capacity" style="display: <?= $deviceFieldVisibility['battery_capacity'] ? 'block' : 'none' ?>;">
            <label for="device_battery_capacity">Battery Capacity (mAh)</label>
            <input type="number" name="battery_capacity" id="device_battery_capacity" class="form-control"
                   value="<?= esc(old('battery_capacity', $product['battery_capacity'] ?? '')) ?>" min="0" placeholder="e.g. 650">
        </div>

        <div class="form-group device-spec-field" data-device-field="wattage_range" style="display: <?= $deviceFieldVisibility['wattage_range'] ? 'block' : 'none' ?>;">
            <label for="wattage_range">Wattage / Power Range</label>
            <input type="text" name="wattage_range" id="wattage_range" class="form-control"
                   value="<?= esc(old('wattage_range', $product['wattage_range'] ?? '')) ?>" placeholder="e.g. 5-15W">
        </div>

        <div class="form-group device-spec-field" data-device-field="charging_port" style="display: <?= $deviceFieldVisibility['charging_port'] ? 'block' : 'none' ?>;">
            <label for="charging_port">Charging Port</label>
            <select name="charging_port" id="charging_port" class="form-control">
                <option value="">Select charging port</option>
                <?php foreach ($chargingPortOptions as $port): ?>
                    <option value="<?= esc($port) ?>" <?= (string) old('charging_port', $product['charging_port'] ?? '') === $port ? 'selected' : '' ?>><?= esc($port) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group device-spec-field device-spec-field-wide" data-device-field="compatibility" style="display: <?= $deviceFieldVisibility['compatibility'] ? 'block' : 'none' ?>; grid-column: 1 / -1;">
            <label for="compatibility">Compatible With</label>
            <input type="text" name="compatibility" id="compatibility" class="form-control"
                   value="<?= esc(old('compatibility', $product['compatibility'] ?? '')) ?>" placeholder="e.g. BLACK V1 / BLACK ELITE pods">
            <small class="help-text">Pod lines, cartridges, or accessories this device supports.</small>
        </div>
    </div>
</div>
