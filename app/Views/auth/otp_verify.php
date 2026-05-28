<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { width:100%; max-width:440px; background:#fff; border:1px solid #e6e6e6; border-radius:14px; padding:22px; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
        h1 { margin:0 0 8px; font-size:20px; }
        p { margin:0 0 12px; color:#555; font-size:14px; }
        .meta { margin:0 0 16px; color:#666; font-size:13px; }
        .alert { padding:10px 12px; border-radius:10px; margin:0 0 12px; font-size:14px; }
        .alert-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-error { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        label { display:block; font-size:14px; margin:10px 0 6px; }
        input { width:100%; padding:12px; font-size:18px; letter-spacing:6px; text-align:center; border:1px solid #ddd; border-radius:10px; }
        button { width:100%; margin-top:12px; padding:12px; border-radius:10px; border:0; cursor:pointer; font-size:15px; }
        .btn-primary { background:#27c56f; color:#fff; }
        .btn-secondary { background:#eef1f4; color:#333; }
        .btn-secondary[disabled] { opacity: .6; cursor:not-allowed; }
        .row { display:flex; gap:10px; margin-top:10px; }
        .row form { flex:1; }
        .small { font-size:12px; color:#666; margin-top:10px; }
    </style>
</head>
<body>
    <?php
        $remainingAttempts = isset($remaining_attempts) ? (int) $remaining_attempts : 3;
        $maxAttempts = isset($max_attempts) ? (int) $max_attempts : 3;
        $resendCooldown = isset($resend_cooldown) ? (int) $resend_cooldown : 0;
        $otpTtlMinutes = isset($otp_ttl_minutes) ? (int) $otp_ttl_minutes : 5;
    ?>
    <div class="card">
        <h1>OTP Verification</h1>
        <p>We sent a 6-digit code to your email. It expires in <?= esc((string) $otpTtlMinutes) ?> minute(s).</p>
        <p class="meta">Remaining attempts: <strong><?= esc((string) $remainingAttempts) ?></strong> / <?= esc((string) $maxAttempts) ?></p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= site_url('otp/verify') ?>" method="post">
            <?= csrf_field() ?>
            <label for="otp_code">Enter OTP</label>
            <input id="otp_code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
            <button class="btn-primary" type="submit">Verify</button>
        </form>

        <div class="row">
            <form action="<?= site_url('otp/resend') ?>" method="post" id="resend-form">
                <?= csrf_field() ?>
                <button class="btn-secondary" type="submit" id="resend-btn">Resend OTP</button>
                <div class="small" id="resend-note"></div>
            </form>
            <form action="<?= site_url('auth/logout') ?>" method="get">
                <button class="btn-secondary" type="submit">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var cooldown = <?= $resendCooldown ?>;
            var resendBtn = document.getElementById('resend-btn');
            var resendNote = document.getElementById('resend-note');

            function renderCooldown() {
                if (cooldown <= 0) {
                    resendBtn.disabled = false;
                    resendNote.textContent = '';
                    return;
                }

                resendBtn.disabled = true;
                resendNote.textContent = 'Resend available in ' + cooldown + 's';
                cooldown -= 1;
                setTimeout(renderCooldown, 1000);
            }

            renderCooldown();
        })();
    </script>
</body>
</html>
