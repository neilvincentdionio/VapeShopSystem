<?= $this->include('customer/partials/header') ?>

<style>
    <?= $this->include('customer/partials/checkout_modal_styles') ?>

    .cart-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .cart-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .cart-head h1 {
        font-size: 1.45rem;
        margin: 0 0 .35rem;
        color: #333333;
        font-weight: 800;
    }

    .cart-head p {
        color: #666666;
        margin: 0;
        line-height: 1.5;
    }

    .cart-items {
        display: grid;
        gap: .85rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 72px 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e8ecef;
        border-radius: 14px;
        background: #fafbfc;
    }

    .cart-item-image {
        width: 72px;
        height: 56px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-title {
        font-weight: 700;
        color: #333333;
        margin-bottom: .25rem;
        line-height: 1.35;
    }

    .cart-item-meta {
        color: #666666;
        font-size: .86rem;
    }

    .cart-item-right {
        display: grid;
        justify-items: end;
        gap: .55rem;
        min-width: 150px;
    }

    .cart-line-total {
        font-weight: 800;
        color: #333333;
        font-size: 1rem;
    }

    .qty-control {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d7dce1;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .qty-btn {
        width: 34px;
        height: 34px;
        border: none;
        background: #f3f4f6;
        color: #333;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        line-height: 1;
    }

    .qty-btn:hover {
        background: #e8f5ee;
        color: #1d9f57;
    }

    .qty-input {
        width: 48px;
        height: 34px;
        border: none;
        border-left: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        text-align: center;
        font-weight: 700;
        font-size: .9rem;
        color: #333;
        -moz-appearance: textfield;
    }

    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-summary {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e0e0e0;
        display: flex;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .cart-total {
        font-size: 1.2rem;
        font-weight: 800;
        color: #333333;
    }

    .cart-actions {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 10px;
        padding: .7rem 1.1rem;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .02em;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all .15s ease;
        text-transform: uppercase;
    }

    .btn-primary {
        background: #27c56f;
        border-color: #27c56f;
        color: #fff;
    }

    .btn-primary:hover {
        background: #219653;
        border-color: #219653;
    }

    .btn-outline {
        background: #fff;
        border-color: #27c56f;
        color: #1d9f57;
    }

    .btn-outline:hover {
        background: #ecfdf3;
    }

    .btn-danger {
        background: #fff;
        border-color: #ef4444;
        color: #dc2626;
        padding: .45rem .75rem;
        font-size: .72rem;
    }

    .btn-danger:hover {
        background: #fef2f2;
    }

    .cart-empty {
        padding: 2.5rem 1.5rem;
        background: #f8f9fa;
        border: 1px dashed #d7dce1;
        border-radius: 14px;
        text-align: center;
    }

    .cart-empty h3 {
        margin: .75rem 0 .35rem;
        color: #333;
    }

    @media (max-width: 720px) {
        .cart-item {
            grid-template-columns: 64px 1fr;
        }

        .cart-item-right {
            grid-column: 1 / -1;
            justify-items: stretch;
            min-width: 0;
        }

        .cart-line-total {
            text-align: right;
        }
    }
</style>

<section class="panel cart-panel">
    <div class="cart-head">
        <div>
            <h1>Shopping Cart</h1>
            <p>Review quantities before checkout. Changes save automatically.</p>
        </div>
        <a href="<?= site_url('customer/products') ?>" class="btn btn-outline">Continue Shopping</a>
    </div>

    <?php if (! empty($cart_items)): ?>
        <div class="cart-items" id="cartItemsList">
            <?php foreach ($cart_items as $item): ?>
                <?php
                    $cartKey = (string) ($item['cart_key'] ?? (string) ($item['id'] ?? ''));
                    $maxStock = max(1, (int) ($item['stock'] ?? 1));
                    $qty = (int) ($item['quantity'] ?? 1);
                ?>
                <div class="cart-item" data-cart-key="<?= esc($cartKey, 'attr') ?>">
                    <div class="cart-item-image">
                        <?php if (! empty($item['image'])): ?>
                            <img src="<?= esc(product_image_url($item['image'])) ?>" alt="<?= esc($item['name']) ?>">
                        <?php else: ?>
                            🛒
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="cart-item-title"><?= esc($item['display_name'] ?? $item['name']) ?></div>
                        <div class="cart-item-meta">
                            Unit: ₱<?= number_format((float) ($item['price'] ?? 0), 2) ?>
                            · Stock: <?= $maxStock ?>
                        </div>
                    </div>
                    <div class="cart-item-right">
                        <div class="cart-line-total" data-line-total>₱<?= number_format((float) ($item['amount'] ?? 0), 2) ?></div>
                        <div style="display:flex; gap:.45rem; align-items:center; flex-wrap:wrap; justify-content:flex-end;">
                            <div class="qty-control" data-max="<?= $maxStock ?>" data-cart-key="<?= esc($cartKey, 'attr') ?>">
                                <button type="button" class="qty-btn" data-qty-step="-1" aria-label="Decrease quantity">−</button>
                                <input
                                    type="number"
                                    class="qty-input"
                                    value="<?= $qty ?>"
                                    min="1"
                                    max="<?= $maxStock ?>"
                                    required
                                >
                                <button type="button" class="qty-btn" data-qty-step="1" aria-label="Increase quantity">+</button>
                            </div>
                            <form method="post" action="<?= site_url('customer/cart/remove') ?>" onsubmit="return confirm('Remove this item from cart?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_key" value="<?= esc($cartKey) ?>">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="cart-total" id="cartPageTotal">
                Total: ₱<?= number_format((float) $estimated_total, 2) ?>
            </div>
            <div class="cart-actions">
                <?php if (! empty($age_allowed)): ?>
                    <button type="button" class="btn btn-primary" onclick="processDirectOrder()">Proceed to Checkout</button>
                <?php else: ?>
                    <a href="<?= site_url('customer/age-verification') ?>" class="btn btn-outline">Verify 18+ to Order</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-empty">
            <div style="font-size:2.5rem;">🛒</div>
            <h3>Your cart is empty</h3>
            <p style="color:#666; margin-bottom:1rem;">Add products from the shop to get started.</p>
            <a href="<?= site_url('customer/products') ?>" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</section>

<?= $this->include('customer/partials/checkout_modal') ?>
<?= $this->include('customer/partials/checkout_modal_scripts') ?>

<script>
(function () {
    const updateUrl = <?= json_encode(site_url('customer/cart/update')) ?>;
    const csrfName = <?= json_encode(csrf_token()) ?>;
    let csrfHash = <?= json_encode(csrf_hash()) ?>;
    const saveTimers = new Map();

    function formatMoney(amount) {
        return '₱' + Number(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function scheduleSave(cartKey, quantity, row) {
        const key = cartKey;
        if (saveTimers.has(key)) {
            clearTimeout(saveTimers.get(key));
        }
        saveTimers.set(key, setTimeout(() => saveCartQty(cartKey, quantity, row), 350));
    }

    async function saveCartQty(cartKey, quantity, row) {
        const body = new FormData();
        body.append(csrfName, csrfHash);
        body.append('cart_key', cartKey);
        body.append('quantity', String(quantity));

        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                body,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            if (!data || !data.success) {
                return;
            }

            if (data.csrf_hash) {
                csrfHash = data.csrf_hash;
            }

            if (data.removed || data.empty) {
                window.location.reload();
                return;
            }

            const lineTotal = row ? row.querySelector('[data-line-total]') : null;
            if (lineTotal) {
                lineTotal.textContent = formatMoney(data.line_amount);
            }

            const pageTotal = document.getElementById('cartPageTotal');
            if (pageTotal) {
                pageTotal.textContent = 'Total: ' + formatMoney(data.cart_total);
            }
        } catch (err) {
            console.error('Cart update failed', err);
        }
    }

    document.querySelectorAll('.qty-control').forEach((control) => {
        const input = control.querySelector('.qty-input');
        const max = parseInt(control.dataset.max || '99', 10);
        const cartKey = control.dataset.cartKey || '';
        const row = control.closest('.cart-item');

        const applyQty = (persist) => {
            let value = parseInt(input.value || '1', 10);
            if (!Number.isFinite(value) || value < 1) {
                value = 1;
            }
            value = Math.min(max, value);
            input.value = String(value);
            if (persist && cartKey) {
                scheduleSave(cartKey, value, row);
            }
        };

        control.querySelectorAll('[data-qty-step]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const step = parseInt(btn.dataset.qtyStep || '0', 10);
                let value = parseInt(input.value || '1', 10);
                value = Math.min(max, Math.max(1, value + step));
                input.value = String(value);
                applyQty(true);
            });
        });

        input.addEventListener('change', () => applyQty(true));
        input.addEventListener('blur', () => applyQty(true));
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('checkout') === '1' && typeof processDirectOrder === 'function') {
        setTimeout(() => processDirectOrder(), 300);
        params.delete('checkout');
        const query = params.toString();
        const cleanUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
        if (window.history.replaceState) {
            window.history.replaceState({}, '', cleanUrl);
        }
    }
})();
</script>

<?= $this->include('customer/partials/footer') ?>
