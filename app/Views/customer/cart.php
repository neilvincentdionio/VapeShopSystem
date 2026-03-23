<?= $this->include('customer/partials/header') ?>

<style>
    .cart-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        text-align: left;
    }
    
    .cart-panel h1 {
        font-size: 1.35rem;
        margin-bottom: .25rem;
        color: #333333;
        font-weight: 700;
    }

    .cart-panel p {
        color: #666666;
        margin-bottom: .9rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .cart-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #27c56f, #7ef0b2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #ffffff;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .cart-empty {
        padding: 2rem;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        margin-top: 1.5rem;
        text-align: center;
    }
    
    .continue-shopping {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 8px;
        padding: .7rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .55px;
        font-size: .74rem;
        font-weight: 700;
        border: 2px solid #27c56f;
        color: #27c56f;
        background: transparent;
        transition: all 0.2s ease;
    }
    
    .continue-shopping:hover {
        background: #27c56f;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }

    .cart-items {
        display: grid;
        gap: 1rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 72px 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        background: #ffffff;
    }

    .cart-item-image {
        width: 72px;
        height: 56px;
        border-radius: 12px;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 22px;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-title {
        font-weight: 700;
        color: #333333;
        margin-bottom: .35rem;
    }

    .cart-item-meta {
        color: #666666;
        font-size: .92rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .cart-item-right {
        display: grid;
        justify-items: end;
        gap: .6rem;
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
        font-size: 1.1rem;
        font-weight: 800;
        color: #333333;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 8px;
        padding: .7rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: .74rem;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        background: transparent;
        color: #333333;
    }

    .btn-primary {
        border-color: #27c56f;
        background: #27c56f;
        color: #ffffff;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }

    .btn-danger {
        border-color: #dc3545;
        color: #dc3545;
        background: transparent;
    }

    .btn-danger:hover {
        background: rgba(220, 53, 69, 0.1);
    }
</style>

<section class="panel cart-panel">
    <h1>Shopping Cart</h1>
    <p>Review your items before checkout.</p>
    
    <?php if (!empty($cart_items)): ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= base_url('uploads/products/' . $item['image']) ?>" alt="<?= esc($item['name']) ?>">
                        <?php else: ?>
                            🛒
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="cart-item-title"><?= esc($item['name']) ?></div>
                        <div class="cart-item-meta">
                            <span>Price: ₱<?= number_format((float) ($item['price'] ?? 0), 2) ?></span>
                            <span>Stock: <?= (int) ($item['stock'] ?? 0) ?></span>
                            <span>Quantity: <?= (int) ($item['quantity'] ?? 0) ?></span>
                        </div>
                    </div>
                    <div class="cart-item-right">
                        <div style="font-weight: 800; color:#333333;">
                            ₱<?= number_format((float) ($item['amount'] ?? 0), 2) ?>
                        </div>
                        <form method="post" action="<?= site_url('customer/cart/remove') ?>">
                            <input type="hidden" name="product_id" value="<?= (int) ($item['id'] ?? 0) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Remove this item from cart?')">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <div class="cart-total">
                Total: ₱<?= number_format((float) $estimated_total, 2) ?>
            </div>
            <a href="<?= site_url('customer/checkout') ?>" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <div class="cart-icon">🛒</div>
        <div class="cart-empty">
            <h3>No Items Yet</h3>
            <p>Your cart is empty. Start shopping to add products.</p>
            <a href="<?= site_url('customer/products') ?>" class="continue-shopping">Continue Shopping</a>
        </div>
    <?php endif; ?>
</section>

<?= $this->include('customer/partials/footer') ?>
