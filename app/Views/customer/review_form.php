<?= $this->include('customer/partials/header') ?>

<div class="review-page">
    <div class="review-card">
        <a href="<?= site_url('customer/orders') ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>
        <h1><?= !empty($existingReview) ? 'Edit Review' : 'Write a Review' ?></h1>
        <p class="subtext">Order: <strong><?= esc($order['reference_number'] ?? 'Order') ?></strong></p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc((string) session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('customer/review/submit') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= (int) ($order['id'] ?? 0) ?>">

            <div class="field">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" required>
                    <option value="">Select rating</option>
                    <?php
                        $selectedRating = (int) old('rating', (int) ($existingReview['rating'] ?? 0));
                        for ($i = 5; $i >= 1; $i--):
                    ?>
                        <option value="<?= $i ?>" <?= $selectedRating === $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="field">
                <label for="review_text">Review</label>
                <textarea id="review_text" name="review_text" rows="5" maxlength="1000" placeholder="Share your feedback about this order..."><?= esc((string) old('review_text', (string) ($existingReview['review_text'] ?? ''))) ?></textarea>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary"><?= !empty($existingReview) ? 'Update Review' : 'Submit Review' ?></button>
                <a href="<?= site_url('customer/orders?tab=completed') ?>" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.review-page{max-width:760px;margin:2rem auto;padding:0 1rem;}
.review-card{background:#fff;border:1px solid #e0e0e0;border-radius:16px;padding:1.25rem;box-shadow:0 4px 16px rgba(0,0,0,.06);}
.back-link{display:inline-flex;align-items:center;gap:.4rem;color:#555;text-decoration:none;margin-bottom:.75rem;}
.subtext{color:#666;margin-bottom:1rem;}
.field{margin-bottom:1rem;}
.field label{display:block;font-weight:600;margin-bottom:.35rem;}
.field select,.field textarea{width:100%;border:1px solid #d7dce1;border-radius:8px;padding:.65rem;font-size:.95rem;}
.actions{display:flex;gap:.5rem;}
.btn-primary{background:#27c56f;color:#fff;border:none;border-radius:8px;padding:.6rem 1rem;font-weight:600;cursor:pointer;}
.btn-secondary{background:#f5f5f5;color:#444;border:1px solid #ddd;border-radius:8px;padding:.58rem 1rem;text-decoration:none;}
.alert{padding:.6rem .8rem;border-radius:8px;margin-bottom:1rem;}
.alert-error{background:#fdecea;color:#b42318;border:1px solid #f5c2c0;}
</style>

<?= $this->include('customer/partials/footer') ?>

