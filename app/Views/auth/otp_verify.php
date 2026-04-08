<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { width:100%; max-width:420px; background:#fff; border:1px solid #e6e6e6; border-radius:14px; padding:22px; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
        h1 { margin:0 0 8px; font-size:20px; }
        p { margin:0 0 16px; color:#555; font-size:14px; }
        .alert { padding:10px 12px; border-radius:10px; margin:0 0 12px; font-size:14px; }
        .alert-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-error { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        .alert-warn { background:#fff3cd; border:1px solid #ffeeba; color:#856404; }
        label { display:block; font-size:14px; margin:10px 0 6px; }
        input { width:100%; padding:12px; font-size:18px; letter-spacing:6px; text-align:center; border:1px solid #ddd; border-radius:10px; }
        button { width:100%; margin-top:12px; padding:12px; border-radius:10px; border:0; cursor:pointer; font-size:15px; }
        .btn-primary { background:#27c56f; color:#fff; }
        .btn-secondary { background:#eef1f4; color:#333; }
        .row { display:flex; gap:10px; margin-top:10px; }
        .row form { flex:1; }
        .small { font-size:12px; color:#666; margin-top:10px; }
        code { background:#f3f3f3; padding:2px 6px; border-radius:6px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>OTP Verification</h1>
        <p>We sent a 6-digit code to your email. It expires in 5 minutes.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('otp_email_error')): ?>
            <div class="alert alert-warn">
                For testing, use this OTP:
                <strong><code><?= esc(session()->getFlashdata('otp_debug') ?? '') ?></code></strong>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('otp/verify') ?>" method="post">
            <?= csrf_field() ?>
            <label for="otp_code">Enter OTP</label>
            <input id="otp_code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
            <button class="btn-primary" type="submit">Verify</button>
        </form>

        <div class="row">
            <form action="<?= site_url('otp/resend') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn-secondary" type="submit">Resend OTP</button>
            </form>
            <form action="<?= site_url('auth/logout') ?>" method="get">
                <button class="btn-secondary" type="submit">Cancel</button>
            </form>
        </div>

    </div>
</body>
</html>

