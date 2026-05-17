<?= $this->include('rider/partials/header') ?>

<style>
    .messages-shell { display: grid; gap: 1rem; }
    .messages-header, .conversation-card, .empty-state {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }
    .messages-header { padding: 1.5rem; }
    .messages-header h1 { font-size: 1.5rem; margin-bottom: .35rem; }
    .messages-header p { color: #666666; line-height: 1.55; }
    .conversation-list { display: grid; gap: .75rem; }
    .conversation-card {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        color: inherit;
        text-decoration: none;
    }
    .conversation-card:hover { border-color: #27c56f; }
    .conversation-card h2 { font-size: 1rem; margin-bottom: .3rem; }
    .preview { color: #666666; line-height: 1.45; }
    .meta { color: #666666; text-align: right; font-size: .88rem; }
    .badge { display: inline-flex; border-radius: 999px; padding: .22rem .55rem; background: #dcfce7; color: #047857; font-size: .76rem; font-weight: 800; margin-left: .4rem; }
    .empty-state { padding: 2rem; text-align: center; color: #666666; }
    @media (max-width: 720px) { .conversation-card { grid-template-columns: 1fr; } .meta { text-align: left; } }
</style>

<div class="messages-shell">
    <section class="messages-header">
        <h1>Delivery Support Chats</h1>
        <p>Conversations assigned by admin for delivery concerns.</p>
    </section>

    <section class="conversation-list">
        <?php if (($conversations ?? []) === []): ?>
            <div class="empty-state">No delivery support chats assigned yet.</div>
        <?php endif; ?>

        <?php foreach (($conversations ?? []) as $conversation): ?>
            <?php $latestAt = $conversation['last_message_at'] ?? $conversation['updated_at'] ?? null; ?>
            <a class="conversation-card" href="<?= site_url('rider/messages/' . (int) $conversation['id']) ?>">
                <div>
                    <h2>
                        <?= esc($conversation['customer_name'] ?? 'Customer') ?>
                        <?php if ((int) ($conversation['unread_count'] ?? 0) > 0): ?>
                            <span class="badge"><?= (int) $conversation['unread_count'] ?> unread</span>
                        <?php endif; ?>
                    </h2>
                    <p class="preview"><?= esc($conversation['latest_message'] ?? 'No messages yet.') ?></p>
                </div>
                <div class="meta">
                    <?php if (! empty($conversation['reference_number'])): ?><div>Order <?= esc($conversation['reference_number']) ?></div><?php endif; ?>
                    <?php if ($latestAt): ?><div><?= esc(date('M d, Y h:i A', strtotime((string) $latestAt))) ?></div><?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </section>
</div>

<?= $this->include('rider/partials/footer') ?>
