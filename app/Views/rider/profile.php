<?= $this->include('rider/partials/header') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    .profile-row-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
    }

    .profile-value {
        font-size: .96rem;
        font-weight: 600;
        color: #333333;
        flex: 1;
    }

    .profile-value-input {
        display: none;
        flex: 1;
    }

    .profile-edit-btn {
        border: 1px solid #d0d3d8;
        background: #ffffff;
        color: #4d5561;
        border-radius: 8px;
        padding: .32rem .7rem;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
    }

    .profile-edit-btn:hover {
        background: #f3f4f6;
        border-color: #c5c9cf;
    }

    .profile-input {
        width: 100%;
        padding: .56rem .7rem;
        border-radius: 8px;
        border: 1px solid #d7d9dd;
        background: #ffffff;
        color: #333333;
        font-size: .95rem;
    }

    .profile-row.is-editing .profile-value { display: none; }
    .profile-row.is-editing .profile-value-input { display: block; }
    .profile-row.is-editing .profile-row-main { justify-content: flex-end; }

    .profile-help {
        margin-top: .35rem;
        color: #666666;
        font-size: .8rem;
    }

    .profile-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: flex-end;
    }

    .profile-save-btn {
        border: 1px solid #27c56f;
        background: #27c56f;
        color: #ffffff;
        border-radius: 8px;
        padding: .6rem .9rem;
        font-size: .92rem;
        font-weight: 600;
        cursor: pointer;
    }

    .validation-errors {
        margin-bottom: .9rem;
        padding: .86rem 1rem;
        border-radius: 10px;
        border: 1px solid rgba(220, 53, 69, 0.3);
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
    }

    .validation-errors ul { margin: .4rem 0 0 1.1rem; }

    .profile-clear-card {
        margin-top: 1.25rem;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        background: #f8f9fa;
    }

    .profile-clear-card h2 {
        font-size: 1.05rem;
        margin: 0 0 .35rem;
        color: #333333;
    }

    .profile-clear-card p {
        margin: 0 0 .85rem;
        font-size: .88rem;
        color: #666666;
    }

    .profile-clear-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
    }

    .profile-clear-btn {
        border: 0;
        border-radius: 999px;
        padding: .55rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        background: #fee2e2;
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .profile-clear-btn:hover { background: #fecaca; }

    .profile-flash-success {
        margin-bottom: .9rem;
        padding: .75rem 1rem;
        border-radius: 10px;
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }
</style>

<section class="panel profile-card">
    <?php
    $riderAccount = $rider_account ?? [];
    $hasErrors = !empty(session()->getFlashdata('errors'));
    ?>
    <h1>Rider Profile</h1>
    <p>Manage your rider account details and keep your contact information updated.</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="profile-flash-success"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="validation-errors">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('dashboard/profile/update') ?>" method="post">
        <?= csrf_field() ?>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="name">
            <span class="profile-label">Name</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($user_name ?? '') ?></span>
                <input class="profile-input profile-value-input" type="text" name="name" data-safe-input="person_name" value="<?= esc(old('name', $riderAccount['name'] ?? ($user_name ?? ''))) ?>" required>
                <button type="button" class="profile-edit-btn" data-edit-target="name">Edit</button>
            </div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="email">
            <span class="profile-label">Email</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($user_email ?? '') ?></span>
                <input class="profile-input profile-value-input" type="email" name="email" value="<?= esc(old('email', $riderAccount['email'] ?? ($user_email ?? ''))) ?>" required>
                <button type="button" class="profile-edit-btn" data-edit-target="email">Edit</button>
            </div>
        </div>

        <div class="profile-row" data-row="role">
            <span class="profile-label">Role</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc(ucfirst((string) ($user_role ?? 'rider'))) ?></span>
                <button type="button" class="profile-edit-btn" data-role-locked="1" title="Role is managed by admin.">Edit</button>
            </div>
            <div class="profile-help">Role is managed by admin.</div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="phone">
            <span class="profile-label">Phone Number</span>
            <div class="profile-row-main">
                <span class="profile-value"><?= esc($riderAccount['phone_number'] ?? 'Not set') ?></span>
                <input class="profile-input profile-value-input" type="text" name="phone_number" value="<?= esc(old('phone_number', $riderAccount['phone_number'] ?? '')) ?>" placeholder="+63 900 000 0000">
                <button type="button" class="profile-edit-btn" data-edit-target="phone">Edit</button>
            </div>
        </div>

        <div class="profile-row<?= $hasErrors ? ' is-editing' : '' ?>" data-row="password">
            <span class="profile-label">Password</span>
            <div class="profile-row-main">
                <span class="profile-value">********</span>
                <input class="profile-input profile-value-input" type="password" name="new_password" value="" placeholder="New password (min 8 chars)">
                <button type="button" class="profile-edit-btn" data-edit-target="password">Edit</button>
            </div>
            <div class="profile-help">Leave blank to keep your current password.</div>
            <div style="margin-top:.55rem;">
                <input class="profile-input" type="password" name="confirm_password" value="" placeholder="Confirm new password">
            </div>
        </div>

        <input type="hidden" name="address_line" value="<?= esc(old('address_line', $riderAccount['address_line'] ?? '')) ?>">
        <input type="hidden" name="city" value="<?= esc(old('city', $riderAccount['city'] ?? '')) ?>">
        <input type="hidden" name="country" value="<?= esc(old('country', $riderAccount['country'] ?? '')) ?>">
        <input type="hidden" name="barangay" value="<?= esc(old('barangay', $riderAccount['barangay'] ?? '')) ?>">
        <input type="hidden" name="province" value="<?= esc(old('province', $riderAccount['province'] ?? '')) ?>">
        <input type="hidden" name="postal_code" value="<?= esc(old('postal_code', $riderAccount['postal_code'] ?? '')) ?>">

        <div class="profile-actions">
            <button type="submit" class="profile-save-btn">Save Profile</button>
        </div>
    </form>

    <div class="profile-clear-card">
        <h2>Clear completed lists</h2>
        <p>Remove completed items from My Deliveries and Return Pickups. This only hides them from your rider lists; order records stay in the system.</p>
        <div class="profile-clear-actions">
            <button type="button" class="profile-clear-btn" id="btnDismissCompletedDeliveries">
                <i class="fas fa-trash-alt"></i>
                Delete All Completed Deliveries<?= ($completed_delivery_count ?? 0) > 0 ? ' (' . (int) $completed_delivery_count . ')' : '' ?>
            </button>
            <button type="button" class="profile-clear-btn" id="btnDismissCompletedReturns">
                <i class="fas fa-trash-alt"></i>
                Delete All Completed Returns<?= ($completed_return_count ?? 0) > 0 ? ' (' . (int) $completed_return_count . ')' : '' ?>
            </button>
        </div>
    </div>
</section>

<script>
    (function() {
        document.querySelectorAll('[data-edit-target]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const target = btn.getAttribute('data-edit-target');
                const row = document.querySelector('[data-row="' + target + '"]');
                if (!row) return;
                row.classList.toggle('is-editing');
                if (row.classList.contains('is-editing')) {
                    const input = row.querySelector('.profile-value-input');
                    if (input) input.focus();
                }
            });
        });

        document.querySelectorAll('[data-role-locked]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                alert('Role can only be changed by admin.');
            });
        });

        function dismissCompleted(url, confirmText) {
            if (!confirm(confirmText)) {
                return;
            }
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                alert(data.message || 'Done');
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(function() {
                alert('Unable to clear completed items.');
            });
        }

        document.getElementById('btnDismissCompletedDeliveries')?.addEventListener('click', function() {
            dismissCompleted(
                '<?= site_url('rider/deliveries/dismiss-completed') ?>',
                'Remove all completed deliveries from your list?'
            );
        });

        document.getElementById('btnDismissCompletedReturns')?.addEventListener('click', function() {
            dismissCompleted(
                '<?= site_url('rider/returns/dismiss-completed') ?>',
                'Remove all completed returns from your list?'
            );
        });
    })();
</script>
<script src="<?= base_url('assets/js/safe-input.js') ?>"></script>

<?= $this->include('rider/partials/footer') ?>
