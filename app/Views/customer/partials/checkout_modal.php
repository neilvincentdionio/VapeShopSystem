<?php
$cartCount = count($cart_items ?? []);
$checkoutDisplayTotal = (float) ($cart_total ?? $estimated_total ?? 0);
$hasSavedAddress = ! empty($customer_delivery_address);
$hasSavedPin = ! empty($customer_delivery_latitude) && ! empty($customer_delivery_longitude);
$defaultAddressMode = ($hasSavedAddress && $hasSavedPin) ? 'saved_address' : 'manual';
?>
<div class="checkout-modal" id="checkoutModal">
    <div class="checkout-modal-card">
        <div class="checkout-modal-head">
            <div class="checkout-modal-title">Checkout</div>
            <button type="button" class="checkout-modal-close" onclick="closeCheckoutModal()" aria-label="Close checkout">&times;</button>
        </div>

        <div class="checkout-summary-banner">
            <div>
                <div class="checkout-summary-label">Order total</div>
                <div class="checkout-summary-meta"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?> in cart</div>
            </div>
            <div class="checkout-summary-amount">₱<?= number_format($checkoutDisplayTotal, 2) ?></div>
        </div>

        <form method="post" action="<?= site_url('customer/checkout') ?>" id="checkoutModalForm" class="checkout-form-scroll" onsubmit="return validateCheckoutModal();">
            <div class="checkout-section-title">Payment</div>
            <div class="checkout-field">
                <label class="checkout-label" for="popup_payment_method">Payment Method</label>
                <select class="checkout-input" id="popup_payment_method" name="payment_method" onchange="toggleCheckoutModalFields()" required>
                    <option value="">Select Payment Method</option>
                    <option value="cash_on_delivery">Cash on Delivery (COD)</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>

            <div class="checkout-field" id="popup_gcash_wrap" style="display:none;">
                <div class="gcash-box">
                    <strong>Pay to QuickPuff GCash:</strong> +63 9850640073
                </div>
                <div class="gcash-qr-wrap">
                    <img id="popup_gcash_qr" class="gcash-qr" alt="QuickPuff GCash QR">
                </div>
                <button type="button" class="btn btn-open-gcash" onclick="openInGcashApp()">
                    Open in GCash
                </button>
                <label class="checkout-label" for="popup_gcash_reference">GCash Reference Number</label>
                <input class="checkout-input" type="text" id="popup_gcash_reference" name="gcash_reference" maxlength="50" placeholder="Enter GCash reference number">
            </div>

            <div class="checkout-section-title">Delivery</div>
            <div class="checkout-address-card">
                <div class="checkout-field" style="margin-bottom:0;">
                    <label class="checkout-label">Delivery Address</label>
                    <div class="address-mode-tabs">
                        <label class="address-mode-tab">
                            <input type="radio" name="delivery_address_mode" value="manual" <?= $defaultAddressMode === 'manual' ? 'checked' : '' ?> onchange="toggleDeliveryAddressMode()">
                            <span>Enter address</span>
                        </label>
                        <label class="address-mode-tab">
                            <input type="radio" name="delivery_address_mode" value="saved_address" <?= $defaultAddressMode === 'saved_address' ? 'checked' : '' ?> onchange="toggleDeliveryAddressMode()" <?= ! $hasSavedAddress ? 'disabled' : '' ?>>
                            <span>Use My Address</span>
                        </label>
                    </div>
                </div>

                <div id="manual_delivery_fields" class="checkout-address-grid" style="<?= $defaultAddressMode === 'manual' ? '' : 'display:none;' ?>">
                    <div class="checkout-field full">
                        <label class="checkout-label" for="delivery_address_line">Street Address</label>
                        <input class="checkout-input" type="text" id="delivery_address_line" name="delivery_address_line" placeholder="Street / House No.">
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_country">Country</label>
                        <select class="checkout-input" id="delivery_country" name="delivery_country">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_province">Province</label>
                        <select class="checkout-input" id="delivery_province" name="delivery_province">
                            <option value="South Cotabato" selected>South Cotabato</option>
                            <option value="Sarangani">Sarangani</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_city">City / Municipality</label>
                        <select class="checkout-input" id="delivery_city" name="delivery_city">
                            <option value="">Select City / Municipality</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_barangay">Barangay</label>
                        <select class="checkout-input" id="delivery_barangay" name="delivery_barangay">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>

                    <div class="checkout-field">
                        <label class="checkout-label" for="delivery_postal_code">Postal Code</label>
                        <input class="checkout-input" type="text" id="delivery_postal_code" name="delivery_postal_code" placeholder="Postal code">
                    </div>
                </div>

                <div id="saved_address_fields" style="<?= $defaultAddressMode === 'saved_address' ? '' : 'display:none;' ?>">
                    <div class="saved-address-card">
                        <div class="saved-address-label">Saved address</div>
                        <div class="saved-address-text">
                            <?= ! empty($customer_delivery_address)
                                ? esc($customer_delivery_address)
                                : 'No saved address found. Please enter your delivery address manually.' ?>
                        </div>
                        <?php if ($hasSavedPin): ?>
                            <div class="saved-address-note">
                                Your registered map location will be sent to the rider and admin automatically.
                            </div>
                        <?php else: ?>
                            <div class="saved-address-note" style="color:#b45309;">
                                No saved map pin yet. Pin your location on the map below before placing the order.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                <input type="hidden" name="delivery_longitude" id="delivery_longitude">

                <div class="checkout-field" style="margin-top:.8rem;">
                    <label class="checkout-label" for="delivery_description">Description</label>
                    <textarea class="checkout-input" id="delivery_description" name="delivery_description" rows="3" maxlength="255" placeholder="Add delivery notes, landmarks, or instructions"></textarea>
                </div>
                <div id="checkout_pin_section" class="checkout-field" style="margin-top:.8rem;<?= ($defaultAddressMode === 'saved_address' && $hasSavedPin) ? 'display:none;' : '' ?>">
                    <label class="checkout-label">Pin Delivery Location</label>
                    <div style="display:flex;gap:.5rem;margin-bottom:.5rem;flex-wrap:wrap;align-items:center;">
                        <button type="button" class="btn btn-outline" style="padding:.45rem .7rem;" onclick="checkoutUseCurrentLocation()">Use Current Location</button>
                        <span id="checkout_geo_status" class="location-status"></span>
                    </div>
                    <div id="checkout_map" style="height:200px;border:1px solid #e0e0e0;border-radius:10px;"></div>
                </div>
            </div>
        </form>

        <div class="checkout-form-footer">
            <button type="submit" form="checkoutModalForm" class="btn btn-primary checkout-place-btn">Place Order — ₱<?= number_format($checkoutDisplayTotal, 2) ?></button>
        </div>
    </div>
</div>
