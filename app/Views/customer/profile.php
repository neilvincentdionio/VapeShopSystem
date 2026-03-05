<?= $this->include('customer/partials/header') ?>

<style>
    .profile-card h1 {
        font-size: 1.35rem;
        margin-bottom: .3rem;
    }

    .profile-card p {
        color: #c9d2ea;
        margin-bottom: .9rem;
    }

    .profile-row {
        margin-top: .72rem;
        padding: .65rem .75rem;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .profile-label {
        display: block;
        color: #c9d2ea;
        font-size: .82rem;
        margin-bottom: .2rem;
    }

    .profile-value {
        font-size: .96rem;
        font-weight: 600;
    }
</style>

<section class="panel profile-card">
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

    <?php if (!empty($user_shop_name)): ?>
        <div class="profile-row">
            <span class="profile-label">Shop Name</span>
            <span class="profile-value"><?= esc($user_shop_name) ?></span>
        </div>
    <?php endif; ?>
</section>

<?= $this->include('customer/partials/footer') ?>
