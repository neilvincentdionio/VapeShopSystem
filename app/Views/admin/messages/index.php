<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Support Conversations') ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; background: #f6f8fb; color: #111827; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: none; margin: 0 auto; padding: 1.5rem; }
        .page-shell { display: grid; gap: 1rem; }
        .page-header, .conversation-card, .empty-state { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; }
        .page-header { padding: 1.25rem; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
        .page-header h1 { font-size: 1.65rem; margin-bottom: .3rem; }
        .page-header p { color: #6b7280; line-height: 1.55; }
        .notify-pill { border-radius: 999px; background: #dcfce7; color: #047857; padding: .38rem .7rem; font-weight: 800; white-space: nowrap; }
        .filters { display: flex; gap: .5rem; flex-wrap: wrap; }
        .filter-link { color: #374151; text-decoration: none; border: 1px solid #d1d5db; background: #fff; border-radius: 999px; padding: .5rem .8rem; font-weight: 700; }
        .filter-link.active, .filter-link:hover { border-color: #27c56f; color: #047857; background: #ecfdf5; }
        .alert { border-radius: 10px; padding: .8rem 1rem; border: 1px solid #d1fae5; background: #ecfdf5; color: #047857; }
        .alert-error { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
        .inbox-list { display: grid; gap: .75rem; }
        .conversation-card {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            color: inherit;
            text-decoration: none;
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .conversation-card:hover { border-color: #27c56f; transform: translateY(-1px); box-shadow: 0 10px 26px rgba(15, 23, 42, .08); }
        .conversation-title { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; margin-bottom: .35rem; }
        .conversation-title h2 { font-size: 1rem; }
        .badge { display: inline-flex; border-radius: 999px; padding: .22rem .55rem; background: #eef2ff; color: #4338ca; font-size: .76rem; font-weight: 800; }
        .badge-open { background: #dcfce7; color: #047857; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-resolved { background: #e5e7eb; color: #374151; }
        .preview { color: #4b5563; line-height: 1.45; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .meta { color: #6b7280; font-size: .86rem; text-align: right; white-space: nowrap; }
        .empty-state { padding: 2rem; text-align: center; color: #6b7280; }
        @media (max-width: 760px) { .page-header, .conversation-card { grid-template-columns: 1fr; flex-direction: column; } .meta { text-align: left; white-space: normal; } }
    </style>
</head>
<body>
    <?= $this->include('admin/partials/sidebar_styles') ?>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <main class="page-shell">
            <section class="page-header">
                <div>
                    <h1>Support Conversations</h1>
                    <p>Manage escalated chatbot conversations, reply to customers, review linked orders, and add riders for delivery issues.</p>
                </div>
                <span class="notify-pill"><?= (int) ($unread_notifications ?? 0) ?> notifications</span>
            </section>

            <nav class="filters">
                <?php foreach (['all' => 'All', 'pending' => 'Pending', 'open' => 'Open', 'resolved' => 'Resolved'] as $key => $label): ?>
                    <a class="filter-link <?= ($active_status ?? 'all') === $key ? 'active' : '' ?>" href="<?= site_url('admin/messages' . ($key === 'all' ? '' : '?status=' . $key)) ?>"><?= esc($label) ?></a>
                <?php endforeach; ?>
            </nav>

            <?php if (session()->getFlashdata('success')): ?><div class="alert"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

            <section class="inbox-list">
                <?php if (($conversations ?? []) === []): ?>
                    <div class="empty-state">No escalated conversations in this view.</div>
                <?php endif; ?>

                <?php foreach (($conversations ?? []) as $conversation): ?>
                    <?php
                        $status = (string) ($conversation['status'] ?? 'open');
                        $unreadCount = (int) ($conversation['unread_count'] ?? 0);
                        $latestAt = $conversation['last_message_at'] ?? $conversation['updated_at'] ?? null;
                    ?>
                    <a class="conversation-card" href="<?= site_url('admin/messages/' . (int) $conversation['id']) ?>">
                        <div>
                            <div class="conversation-title">
                                <h2><?= esc($conversation['customer_name'] ?? 'Customer') ?></h2>
                                <span class="badge badge-<?= esc($status) ?>"><?= esc(ucfirst($status)) ?></span>
                                <?php if ($unreadCount > 0): ?><span class="badge badge-open"><?= $unreadCount ?> unread</span><?php endif; ?>
                                <?php if (! empty($conversation['reference_number'])): ?><span class="badge">Order <?= esc($conversation['reference_number']) ?></span><?php endif; ?>
                                <?php if (! empty($conversation['rider_name'])): ?><span class="badge">Rider <?= esc($conversation['rider_name']) ?></span><?php endif; ?>
                            </div>
                            <p class="preview"><?= esc($conversation['latest_message'] ?? 'Waiting for messages.') ?></p>
                        </div>
                        <div class="meta">
                            <div><?= esc($conversation['customer_email'] ?? '') ?></div>
                            <?php if ($latestAt): ?><div><?= esc(date('M d, Y h:i A', strtotime((string) $latestAt))) ?></div><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</body>
</html>
