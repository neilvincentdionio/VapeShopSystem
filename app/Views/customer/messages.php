<?= $this->include('customer/partials/header') ?>

<?php
$messages = $messages ?? [];
$orders = $orders ?? [];
$selectedOrderId = (int) ($selected_order_id ?? 0);
$lastMessageId = 0;
foreach ($messages as $message) {
    $lastMessageId = max($lastMessageId, (int) ($message['id'] ?? 0));
}
?>

<style>
    .support-shell { display: grid; gap: 1rem; max-width: 980px; margin: 0 auto; }
    .support-header, .chat-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }
    .support-header {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }
    .support-header h1 { font-size: 1.55rem; margin-bottom: .35rem; }
    .support-header p { color: var(--text-muted); line-height: 1.55; }
    .status-stack { display: flex; gap: .45rem; flex-wrap: wrap; justify-content: flex-end; }
    .pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .35rem .7rem;
        background: rgba(39, 197, 111, .12);
        color: var(--accent);
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .pill-muted { background: #f1f5f9; color: #475569; }
    .quick-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        background: #ffffff;
    }
    .quick-action {
        border: 1px solid var(--border);
        background: var(--surface-soft);
        border-radius: 999px;
        padding: .5rem .75rem;
        cursor: pointer;
        font: inherit;
        font-weight: 600;
        color: var(--text-main);
    }
    .quick-action:hover { border-color: var(--accent); color: var(--accent); }
    .chat-thread {
        min-height: 430px;
        max-height: 62vh;
        overflow-y: auto;
        padding: 1.2rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }
    .message-row { display: flex; }
    .message-row.customer { justify-content: flex-end; }
    .message-row.system { justify-content: center; }
    .message-bubble {
        max-width: min(660px, 90%);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: .85rem .95rem;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .05);
    }
    .message-row.customer .message-bubble { background: #e9f9f0; border-color: rgba(39, 197, 111, .25); }
    .message-row.chatbot .message-bubble { background: #eef2ff; border-color: #c7d2fe; }
    .message-row.system .message-bubble { background: #fff7ed; border-color: #fed7aa; max-width: min(760px, 96%); }
    .message-meta {
        display: flex;
        gap: .45rem;
        flex-wrap: wrap;
        color: var(--text-muted);
        font-size: .78rem;
        margin-bottom: .35rem;
    }
    .message-text { white-space: pre-wrap; line-height: 1.5; }
    .reply-form {
        display: grid;
        gap: .75rem;
        padding: 1rem;
        border-top: 1px solid var(--border);
        background: #ffffff;
    }
    .form-row { display: grid; grid-template-columns: minmax(180px, 260px) 1fr auto; gap: .75rem; align-items: end; }
    .reply-form select, .reply-form textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: .8rem;
        font: inherit;
    }
    .reply-form textarea { min-height: 52px; resize: vertical; line-height: 1.45; }
    .field-label { display: block; margin-bottom: .35rem; color: var(--text-muted); font-size: .84rem; font-weight: 700; }
    .btn-send {
        border: 0;
        border-radius: 10px;
        background: var(--accent);
        color: #ffffff;
        padding: .82rem 1.15rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }
    @media (max-width: 760px) {
        .support-header, .form-row { grid-template-columns: 1fr; flex-direction: column; }
        .status-stack { justify-content: flex-start; }
    }
</style>

<div class="support-shell">
    <section class="support-header">
        <div>
            <h1>Support Chat</h1>
            <p>Start with the FAQ bot for order status, delivery, payments, and refunds. Type human support anytime to contact an admin/seller.</p>
        </div>
        <div class="status-stack">
            <span class="pill"><?= esc(ucfirst((string) ($conversation['status'] ?? 'open'))) ?></span>
            <span class="pill pill-muted"><?= (($conversation['support_mode'] ?? 'bot') === 'human') ? 'Admin Support' : 'Chatbot' ?></span>
        </div>
    </section>

    <section class="chat-panel">
        <div class="quick-actions">
            <button class="quick-action" type="button" data-target="bot" data-message="What is my order status?">Order Status</button>
            <button class="quick-action" type="button" data-target="bot" data-message="I need help with delivery.">Delivery</button>
            <button class="quick-action" type="button" data-target="bot" data-message="I need help with payment.">Payment</button>
            <button class="quick-action" type="button" data-target="bot" data-message="I need help with refund or return.">Refund</button>
            <button class="quick-action" type="button" data-target="human" data-message="Human support please.">Human Support</button>
        </div>

        <div class="chat-thread" id="chatThread" data-last-id="<?= $lastMessageId ?>">
            <?php if ($messages === []): ?>
                <div class="message-row chatbot">
                    <div class="message-bubble">
                        <div class="message-meta"><strong>Chatbot</strong></div>
                        <div class="message-text">Hi! Ask me about order status, delivery, payments, or refunds. I can connect you to an admin/seller if needed.</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($messages as $message): ?>
                <?php
                    $role = (string) ($message['sender_role'] ?? '');
                    $rowClass = (($message['message_type'] ?? '') === 'system') ? 'system' : $role;
                    $name = match ($role) {
                        'customer' => 'You',
                        'chatbot' => 'Chatbot',
                        'rider' => 'Rider',
                        default => 'Admin',
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

        <form class="reply-form" method="post" action="<?= site_url('customer/messages/send') ?>">
            <input type="hidden" id="supportTarget" name="support_target" value="">
            <div class="form-row">
                <label>
                    <span class="field-label">Related order</span>
                    <select name="order_id">
                        <option value="">No specific order</option>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?= (int) $order['id'] ?>" <?= $selectedOrderId === (int) $order['id'] ? 'selected' : '' ?>>
                                <?= esc(($order['reference_number'] ?? ('Order #' . $order['id'])) . ' - ' . ($order['delivery_status'] ?? 'to_pay')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Message</span>
                    <textarea id="messageInput" name="message" maxlength="2000" required placeholder="Type your message..."></textarea>
                </label>
                <button class="btn-send" type="submit">Send</button>
            </div>
        </form>
    </section>
</div>

<script>
(() => {
    const thread = document.getElementById('chatThread');
    const input = document.getElementById('messageInput');
    const supportTarget = document.getElementById('supportTarget');
    if (!thread || !input || !supportTarget) return;

    function scrollDown() { thread.scrollTop = thread.scrollHeight; }
    function roleName(role, type) {
        if (type === 'system') return 'System';
        if (role === 'customer') return 'You';
        if (role === 'chatbot') return 'Chatbot';
        if (role === 'rider') return 'Rider';
        return 'Admin';
    }
    function addMessage(message) {
        if (thread.querySelector(`[data-message-id="${message.id}"]`)) return;
        const row = document.createElement('div');
        const rowClass = message.message_type === 'system' ? 'system' : message.sender_role;
        row.className = `message-row ${rowClass}`;
        row.dataset.messageId = message.id;
        row.innerHTML = `<div class="message-bubble"><div class="message-meta"><strong></strong><span></span></div><div class="message-text"></div></div>`;
        row.querySelector('strong').textContent = roleName(message.sender_role, message.message_type);
        row.querySelector('span').textContent = message.created_label;
        row.querySelector('.message-text').textContent = message.message;
        thread.appendChild(row);
        thread.dataset.lastId = String(Math.max(Number(thread.dataset.lastId || 0), Number(message.id || 0)));
    }

    document.querySelectorAll('.quick-action').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = button.dataset.message || '';
            supportTarget.value = button.dataset.target || '';
            input.focus();
        });
    });

    input.addEventListener('input', () => {
        supportTarget.value = '';
    });

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

<?= $this->include('customer/partials/footer') ?>
