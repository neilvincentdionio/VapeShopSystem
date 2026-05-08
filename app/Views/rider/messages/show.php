<?= $this->include('rider/partials/header') ?>

<?php
$messages = $messages ?? [];
$lastMessageId = 0;
foreach ($messages as $message) {
    $lastMessageId = max($lastMessageId, (int) ($message['id'] ?? 0));
}
?>

<style>
    .chat-shell { display: grid; gap: 1rem; max-width: 920px; margin: 0 auto; }
    .chat-header, .chat-panel { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 16px; overflow: hidden; }
    .chat-header { padding: 1.25rem; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
    .chat-header h1 { font-size: 1.4rem; margin-bottom: .3rem; }
    .chat-header p { color: #666666; line-height: 1.5; }
    .btn { border: 1px solid #d1d5db; background: #fff; color: #333; border-radius: 9px; padding: .62rem .9rem; text-decoration: none; font-weight: 700; cursor: pointer; }
    .btn-primary { border-color: #27c56f; background: #27c56f; color: #ffffff; }
    .chat-thread { min-height: 480px; max-height: 62vh; overflow-y: auto; padding: 1.2rem; background: #f8fafc; display: flex; flex-direction: column; gap: .85rem; }
    .message-row { display: flex; }
    .message-row.rider { justify-content: flex-end; }
    .message-row.system { justify-content: center; }
    .message-bubble { max-width: min(660px, 90%); border: 1px solid #e5e7eb; border-radius: 14px; padding: .85rem .95rem; background: #ffffff; box-shadow: 0 6px 16px rgba(15, 23, 42, .05); }
    .message-row.rider .message-bubble { background: #e9f9f0; border-color: rgba(39, 197, 111, .25); }
    .message-row.admin .message-bubble { background: #eef2ff; border-color: #c7d2fe; }
    .message-row.system .message-bubble { background: #fff7ed; border-color: #fed7aa; }
    .message-meta { display: flex; gap: .45rem; flex-wrap: wrap; color: #666666; font-size: .78rem; margin-bottom: .35rem; }
    .message-text { white-space: pre-wrap; line-height: 1.5; }
    .reply-form { display: grid; gap: .75rem; padding: 1rem; border-top: 1px solid #e5e7eb; }
    textarea { width: 100%; min-height: 100px; resize: vertical; border: 1px solid #d1d5db; border-radius: 10px; padding: .85rem; font: inherit; }
    .reply-actions { display: flex; justify-content: flex-end; }
</style>

<div class="chat-shell">
    <section class="chat-header">
        <div>
            <h1><?= esc($conversation['customer_name'] ?? 'Customer') ?></h1>
            <p>
                Delivery support chat
                <?php if (! empty($conversation['reference_number'])): ?>
                    <br>Order <?= esc($conversation['reference_number']) ?>
                <?php endif; ?>
            </p>
        </div>
        <a class="btn" href="<?= site_url('rider/messages') ?>">Back</a>
    </section>

    <section class="chat-panel">
        <div class="chat-thread" id="chatThread" data-last-id="<?= $lastMessageId ?>">
            <?php foreach ($messages as $message): ?>
                <?php
                    $role = (string) ($message['sender_role'] ?? '');
                    $rowClass = (($message['message_type'] ?? '') === 'system') ? 'system' : $role;
                    $name = match ($role) {
                        'rider' => 'You',
                        'admin' => 'Admin',
                        'chatbot' => 'Chatbot',
                        default => $conversation['customer_name'] ?? 'Customer',
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

        <form class="reply-form" method="post" action="<?= site_url('rider/messages/' . (int) $conversation['id'] . '/reply') ?>">
            <textarea name="message" maxlength="2000" required placeholder="Reply about this delivery..."></textarea>
            <div class="reply-actions"><button class="btn btn-primary" type="submit">Send Reply</button></div>
        </form>
    </section>
</div>

<script>
(() => {
    const thread = document.getElementById('chatThread');
    if (!thread) return;
    function scrollDown() { thread.scrollTop = thread.scrollHeight; }
    function roleName(message) {
        if (message.message_type === 'system') return 'System';
        if (message.sender_role === 'rider') return 'You';
        if (message.sender_role === 'admin') return 'Admin';
        if (message.sender_role === 'chatbot') return 'Chatbot';
        return '<?= esc($conversation['customer_name'] ?? 'Customer') ?>';
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

<?= $this->include('rider/partials/footer') ?>
