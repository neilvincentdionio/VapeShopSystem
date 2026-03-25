<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'VapeShop System') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f6f7fb;
            color: #1f2937;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .flash-wrapper {
            max-width: 1200px;
            margin: 20px auto 0;
            padding: 0 16px;
        }

        .flash-message {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .flash-success {
            background: #e9f9ef;
            color: #126436;
            border: 1px solid #b7ebcb;
        }

        .flash-error {
            background: #fff1f2;
            color: #991b1b;
            border: 1px solid #fecdd3;
        }
    </style>
</head>
<body>
    <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
        <div class="flash-wrapper">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="flash-message flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="flash-message flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
