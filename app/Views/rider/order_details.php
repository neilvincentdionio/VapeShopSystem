<?= $this->include('rider/partials/header') ?>

<style>
.order-details-wrap{max-width:980px;margin:0 auto;display:grid;gap:1rem}
.details-card{background:#fff;border:1px solid #e0e0e0;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.06);padding:1.1rem}
.details-card h2{margin:0 0 .4rem;color:#223}
.meta{color:#666;font-size:.92rem}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.info-line{margin:.35rem 0}
.label{font-weight:700;color:#333}
.item-row{display:flex;justify-content:space-between;gap:1rem;border:1px solid #eef1f4;border-radius:10px;padding:.7rem;margin:.55rem 0}
.back-link{display:inline-flex;align-items:center;gap:.45rem;color:#2a7a4b;text-decoration:none;font-weight:600}
@media (max-width:780px){.grid{grid-template-columns:1fr}}
</style>

<section class="order-details-wrap">
    <a class="back-link" href="<?= site_url('rider/deliveries') ?>"><i class="fas fa-arrow-left"></i> Back to Deliveries</a>

    <article class="details-card">
        <h2><?= esc($order['reference_number'] ?? ('Order #' . ($order['id'] ?? ''))) ?></h2>
        <div class="meta">Status: <?= esc(ucwords(str_replace('_', ' ', (string) ($order['delivery_status'] ?? 'to_pay')))) ?></div>
    </article>

    <article class="details-card grid">
        <div>
            <div class="info-line"><span class="label">Customer:</span> <?= esc($order['customer']['name'] ?? 'Customer') ?></div>
            <div class="info-line"><span class="label">Email:</span> <?= esc($order['customer']['email'] ?? 'N/A') ?></div>
            <div class="info-line"><span class="label">Contact:</span> <?= esc($order['contact_number'] ?? ($order['customer']['phone'] ?? 'Not provided')) ?></div>
        </div>
        <div>
            <div class="info-line"><span class="label">Shipping Address:</span></div>
            <div class="meta"><?= esc($order['shipping_address'] ?? 'No address provided') ?></div>
            <?php if (!empty($order['shipment_notes'])): ?>
                <div class="info-line"><span class="label">Delivery Notes:</span> <?= esc($order['shipment_notes']) ?></div>
            <?php endif; ?>
        </div>
    </article>

    <article class="details-card">
        <h2>Order Items</h2>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <div>
                        <div><?= esc($item['name'] ?? 'Product') ?></div>
                        <div class="meta">Qty: <?= (int) ($item['qty'] ?? 0) ?></div>
                    </div>
                    <div><strong>&#8369;<?= number_format(((float) ($item['unit_price'] ?? 0)) * ((int) ($item['qty'] ?? 0)), 2) ?></strong></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="meta">No items found.</div>
        <?php endif; ?>
    </article>
</section>

<?= $this->include('rider/partials/footer') ?>

