<?= $this->include('customer/partials/header') ?>

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
    <div class="checkout-subtitle">Cashiering: enter cash amount, we will calculate change and generate a receipt.</div>

    <div class="items">
        <div class="items-head">
            <div>Item</div>
            <div>Qty</div>
            <div>Unit</div>
            <div class="col-hide">Total</div>
        </div>

        <?php foreach ($cart_items as $item): ?>
            <div class="items-row">
                <div style="font-weight:800;"><?= esc($item['name']) ?></div>
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
                    <label class="field-label" for="cash_given">Cash Given (for payment)</label>
                    <input
                        class="input"
                        type="number"
                        step="0.01"
                        min="<?= number_format((float) $estimated_total, 2, '.', '') ?>"
                        id="cash_given"
                        name="cash_given"
                        value="<?= number_format((float) $estimated_total, 2, '.', '') ?>"
                    >

                    <div class="change-box" id="change_box">Change: ₱0.00</div>
                    
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
                        <strong>Note:</strong> Payment will be collected upon delivery. Cash amount is for reference only.
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

                <button class="btn" type="submit" id="submit_btn" disabled>
                    Select Payment Method
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const totalAmount = <?= json_encode((float) $estimated_total) ?>;
    const paymentMethodSelect = document.getElementById('payment_method');
    const cashFields = document.getElementById('cash_fields');
    const gcashFields = document.getElementById('gcash_fields');
    const cashInput = document.getElementById('cash_given');
    const changeBox = document.getElementById('change_box');
    const submitBtn = document.getElementById('submit_btn');

    function formatMoney(n) {
        return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
    }

    function togglePaymentFields() {
        const selectedMethod = paymentMethodSelect.value;
        
        // Hide all payment fields
        cashFields.style.display = 'none';
        gcashFields.style.display = 'none';
        
        // Show relevant fields based on selection
        if (selectedMethod === 'cash_on_delivery') {
            cashFields.style.display = 'block';
            submitBtn.textContent = 'Place Order (COD)';
            computeChange();
        } else if (selectedMethod === 'gcash') {
            gcashFields.style.display = 'block';
            submitBtn.textContent = 'Place Order (GCash)';
        } else {
            submitBtn.textContent = 'Select Payment Method';
        }
        
        // Enable/disable submit button
        submitBtn.disabled = !selectedMethod;
    }

    function computeChange() {
        const cash = parseFloat(cashInput.value || '0');
        const change = cash - totalAmount;
        if (change < 0) {
            changeBox.textContent = `Change: ₱0.00 (cash is short)`;
        } else {
            changeBox.textContent = `Change: ₱${formatMoney(change)}`;
        }
        return change;
    }

    function validatePayment() {
        const selectedMethod = paymentMethodSelect.value;
        
        if (!selectedMethod) {
            alert('Please select a payment method.');
            return false;
        }
        
        if (selectedMethod === 'cash_on_delivery') {
            const cash = parseFloat(cashInput.value || '0');
            if (cash < totalAmount) {
                alert('Cash amount is not enough. Please enter sufficient cash.');
                return false;
            }
        } else if (selectedMethod === 'gcash') {
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
        
        return true;
    }

    // Event listeners
    paymentMethodSelect.addEventListener('change', togglePaymentFields);
    if (cashInput) {
        cashInput.addEventListener('input', computeChange);
    }
    
    // Initialize
    togglePaymentFields();
</script>

<?= $this->include('customer/partials/footer') ?>

