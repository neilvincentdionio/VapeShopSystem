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

    .profile-value {
        font-size: .96rem;
        font-weight: 600;
        color: #333333;
    }
</style>

<section class="panel profile-card">
    <?php $customerAccount = $customer_account ?? []; ?>
    <h1>Profile</h1>
    <p>Manage your customer details and keep account information updated.</p>

    <div class="profile-row">
        <span class="profile-label">Name</span>
        <span class="profile-value"><?= esc($user_name ?? '') ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Email</span>
        <span class="profile-value"><?= esc($user_email ?? '') ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Role</span>
        <span class="profile-value"><?= esc(ucfirst((string) ($user_role ?? 'customer'))) ?></span>
    </div>

    <?php if (!empty($customerAccount['phone_number'])): ?>
        <div class="profile-row">
            <span class="profile-label">Phone Number</span>
            <span class="profile-value"><?= esc($customerAccount['phone_number']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($customerAccount['address_line']) || !empty($customerAccount['city']) || !empty($customerAccount['province']) || !empty($customerAccount['postal_code'])): ?>
        <div class="profile-row">
            <span class="profile-label">Address</span>
            <span class="profile-value">
                <?= esc(implode(', ', array_filter([
                    $customerAccount['address_line'] ?? '',
                    $customerAccount['city'] ?? '',
                    $customerAccount['province'] ?? '',
                    $customerAccount['postal_code'] ?? '',
                ]))) ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="profile-row">
        <span class="profile-label">ID Verification</span>
        <span class="profile-value"><?= !empty($customerAccount['verification_id_path']) ? 'ID uploaded' : 'No ID uploaded' ?></span>
    </div>

    <?php if (!empty($user_shop_name)): ?>
        <div class="profile-row">
            <span class="profile-label">Shop Name</span>
            <span class="profile-value"><?= esc($user_shop_name) ?></span>
        </div>
    <?php endif; ?>
</section>

<?= $this->include('customer/partials/footer') ?>
