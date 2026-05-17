<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Support Conversation') ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; background: #f6f8fb; color: #111827; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: none; margin: 0 auto; padding: 1.5rem; }
        .chat-shell { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1rem; align-items: start; }
        .page-header, .chat-panel, .side-panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .page-header { grid-column: 1 / -1; padding: 1.25rem; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
        .page-header h1 { font-size: 1.5rem; margin-bottom: .3rem; }
        .page-header p, .side-panel p { color: #6b7280; line-height: 1.5; }
        .header-actions { display: flex; gap: .55rem; flex-wrap: wrap; justify-content: flex-end; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; border-radius: 9px; padding: .62rem .9rem; background: #ffffff; color: #111827; text-decoration: none; font-weight: 700; cursor: pointer; }
        .btn-primary { border-color: #27c56f; background: #27c56f; color: #ffffff; }
        .alert { grid-column: 1 / -1; border-radius: 10px; padding: .8rem 1rem; border: 1px solid #d1fae5; background: #ecfdf5; color: #047857; }
        .alert-error { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
        .chat-thread { min-height: 520px; max-height: 64vh; overflow-y: auto; padding: 1.2rem; background: #f8fafc; display: flex; flex-direction: column; gap: .85rem; }
        .message-row { display: flex; }
        .message-row.admin, .message-row.rider { justify-content: flex-end; }
        .message-row.system { justify-content: center; }
        .message-bubble { max-width: min(680px, 90%); border: 1px solid #e5e7eb; border-radius: 14px; padding: .85rem .95rem; background: #ffffff; box-shadow: 0 6px 16px rgba(15, 23, 42, .05); }
        .message-row.admin .message-bubble { background: #ecfdf5; border-color: #bbf7d0; }
        .message-row.rider .message-bubble { background: #eff6ff; border-color: #bfdbfe; }
        .message-row.chatbot .message-bubble { background: #eef2ff; border-color: #c7d2fe; }
        .message-row.system .message-bubble { background: #fff7ed; border-color: #fed7aa; }
        .message-meta { display: flex; gap: .45rem; flex-wrap: wrap; color: #6b7280; font-size: .78rem; margin-bottom: .35rem; }
        .message-text { white-space: pre-wrap; line-height: 1.5; }
        .reply-form { display: grid; gap: .75rem; padding: 1rem; border-top: 1px solid #e5e7eb; background: #ffffff; }
        textarea, select { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: .8rem; font: inherit; }
        textarea { min-height: 110px; resize: vertical; line-height: 1.5; }
        .reply-actions { display: flex; justify-content: flex-end; }
        .side-panel { padding: 1rem; display: grid; gap: 1rem; }
        .side-panel h2 { font-size: 1rem; margin-bottom: .45rem; }
        .info-list { display: grid; gap: .45rem; font-size: .92rem; }
        .info-list span { color: #6b7280; }
        .side-form { display: grid; gap: .55rem; }
        @media (max-width: 980px) { .chat-shell { grid-template-columns: 1fr; } .page-header { flex-direction: column; } .header-actions { justify-content: flex-start; } }
    </style>
</head>
<body>
    <?= $this->include('admin/partials/sidebar_styles') ?>
    <?= $this->include('admin/partials/sidebar') ?>

    <?php
    $messages = $messages ?? [];
    $lastMessageId = 0;
    foreach ($messages as $message) {
        $lastMessageId = max($lastMessageId, (int) ($message['id'] ?? 0));
    }
    ?>

    <div class="container">
        <main class="chat-shell">
            <section class="page-header">
                <div>
                    <h1><?= esc($conversation['customer_name'] ?? 'Customer') ?></h1>
                    <p><?= esc($conversation['customer_email'] ?? '') ?></p>
                </div>
                <div class="header-actions">
                    <a class="btn" href="<?= site_url('admin/messages') ?>">Back to Inbox</a>
                </div>
            </section>

            <?php if (session()->getFlashdata('success')): ?><div class="alert"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

            <section class="chat-panel">
                <div class="chat-thread" id="chatThread" data-last-id="<?= $lastMessageId ?>">
                    <?php foreach ($messages as $message): ?>
                        <?php
                            $role = (string) ($message['sender_role'] ?? '');
                            $rowClass = (($message['message_type'] ?? '') === 'system') ? 'system' : $role;
                            $name = match ($role) {
                                'customer' => $conversation['customer_name'] ?? 'Customer',
                                'chatbot' => 'Chatbot',
                                'rider' => $message['sender_name'] ?? 'Rider',
                                default => $message['sender_name'] ?? 'Admin',
                            };
                            if (($message['message_type'] ?? '') === 'system') {
                                $name = 'System';
                            }
                        ?>
                        <div class="message-row <?= esc($rowClass) ?>" data-message-id="<?= (int) ($message['id'] ?? 0) ?>">
                            <div class="message-bubble">
                                <div class="message-meta">
                                    <strong><?= esc($name) ?></strong>
                                    <span><?= esc(date('M d, Y h:i A', strtotime((string) ($message['created_at'] ?? 'now')))) ?></span>
                                </div>
                                <div class="message-text"><?= esc($message['message'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="reply-form" method="post" action="<?= site_url('admin/messages/' . (int) $conversation['id'] . '/reply') ?>">
                    <textarea name="message" maxlength="2000" required placeholder="Reply to the customer..."></textarea>
                    <div class="reply-actions"><button class="btn btn-primary" type="submit">Send Reply</button></div>
                </form>
            </section>

            <aside class="side-panel">
                <section>
                    <h2>Chat Status</h2>
                    <form class="side-form" method="post" action="<?= site_url('admin/messages/' . (int) $conversation['id'] . '/status') ?>">
                        <select name="status">
                            <?php foreach (['open' => 'Open', 'pending' => 'Pending', 'resolved' => 'Resolved'] as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= (($conversation['status'] ?? '') === $value) ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Update Status</button>
                    </form>
                </section>

                <section>
                    <h2>Order Details</h2>
                    <?php if ($order): ?>
                        <div class="info-list">
                            <div><span>Reference:</span> <?= esc($order['reference_number'] ?? '') ?></div>
                            <div><span>Total:</span> ₱<?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></div>
                            <div><span>Payment:</span> <?= esc(ucwords(str_replace('_', ' ', (string) ($order['payment_status'] ?? 'unpaid')))) ?></div>
                            <div><span>Delivery:</span> <?= esc(ucwords(str_replace('_', ' ', (string) ($order['delivery_status'] ?? 'to_pay')))) ?></div>
                            <a class="btn" href="<?= site_url('admin/order-details/' . (int) $order['id']) ?>">View Order</a>
                        </div>
                    <?php else: ?>
                        <p>No order is linked to this conversation.</p>
                    <?php endif; ?>
                </section>

                <section>
                    <h2>Delivery Rider</h2>
                    <?php if (! empty($conversation['rider_name'])): ?>
                        <p>Assigned to <?= esc($conversation['rider_name']) ?>.</p>
                    <?php endif; ?>
                    <form class="side-form" method="post" action="<?= site_url('admin/messages/' . (int) $conversation['id'] . '/assign-rider') ?>">
                        <select name="rider_id" required>
                            <option value="">Choose rider</option>
                            <?php foreach (($riders ?? []) as $rider): ?>
                                <option value="<?= (int) $rider['id'] ?>" <?= (int) ($conversation['assigned_rider_id'] ?? 0) === (int) $rider['id'] ? 'selected' : '' ?>>
                                    <?= esc($rider['name'] ?? 'Rider') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn" type="submit">Add Rider</button>
                    </form>
                </section>
            </aside>
        </main>
    </div>

    <script>
    (() => {
        const thread = document.getElementById('chatThread');
        if (!thread) return;
        function scrollDown() { thread.scrollTop = thread.scrollHeight; }
        function roleName(message) {
            if (message.message_type === 'system') return 'System';
            if (message.sender_role === 'customer') return '<?= esc($conversation['customer_name'] ?? 'Customer') ?>';
            if (message.sender_role === 'chatbot') return 'Chatbot';
            if (message.sender_role === 'rider') return message.sender_name || 'Rider';
            return message.sender_name || 'Admin';
        }
        function addMessage(message) {
            if (thread.querySelector(`[data-message-id="${message.id}"]`)) return;
            const rowClass = message.message_type === 'system' ? 'system' : message.sender_role;
            const row = document.createElement('div');
            row.className = `message-row ${rowClass}`;
            row.dataset.messageId = message.id;
            row.innerHTML = `<div class="message-bubble"><div class="message-meta"><strong></strong><span></span></div><div class="message-text"></div></div>`;
            row.querySelector('strong').textContent = roleName(message);
            row.querySelector('span').textContent = message.created_label;
            row.querySelector('.message-text').textContent = message.message;
            thread.appendChild(row);
            thread.dataset.lastId = String(Math.max(Number(thread.dataset.lastId || 0), Number(message.id || 0)));
        }
        async function poll() {
            try {
                const response = await fetch('<?= site_url('messages/' . (int) ($conversation['id'] ?? 0) . '/poll') ?>?after_id=' + encodeURIComponent(thread.dataset.lastId || '0'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) return;
                data.messages.forEach(addMessage);
                scrollDown();
            } catch (error) {
                console.debug('Chat polling failed:', error);
            }
        }
        scrollDown();
        setInterval(poll, 4000);
        window.addEventListener('focus', poll);
    })();
    </script>
</body>
</html>
