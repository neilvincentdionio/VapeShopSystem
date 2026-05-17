<?php
$notificationRole = strtolower((string) (session()->get('user_role') ?? ''));
$notificationId = 'notificationBell-' . preg_replace('/[^a-z0-9_-]/i', '', $notificationRole ?: 'user');
?>
<div class="notification-bell" id="<?= esc($notificationId) ?>" data-notification-bell>
    <button class="notification-bell__button" type="button" aria-label="Notifications" aria-expanded="false">
        <span class="notification-bell__icon" aria-hidden="true"></span>
        <span class="notification-bell__count" data-notification-count hidden>0</span>
    </button>
    <div class="notification-bell__panel" data-notification-panel hidden>
        <div class="notification-bell__header">
            <strong>Notifications</strong>
            <button type="button" class="notification-bell__mark-all" data-notification-mark-all>Mark all read</button>
        </div>
        <div class="notification-bell__list" data-notification-list>
            <div class="notification-bell__empty">No notifications yet.</div>
        </div>
    </div>
</div>

<style>
    .notification-bell {
        position: relative;
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        z-index: 10050;
    }

    .notification-bell__button {
        position: relative;
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        padding: 0 !important;
        border-radius: 999px;
        border: 1px solid #d9dee3;
        background: #ffffff;
        color: #000000;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: border-color .2s ease, background .2s ease;
    }

    .notification-bell__button:hover,
    .notification-bell__button:focus {
        border-color: #000000;
        background: #ffffff;
        box-shadow: none;
        outline: none;
    }

    .notification-bell__icon {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        padding: 0 !important;
        line-height: 1;
        display: block;
        font-size: 0;
        color: #000000;
        background: currentColor;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 22a2.7 2.7 0 0 0 2.65-2.25h-5.3A2.7 2.7 0 0 0 12 22ZM19.2 16.1l-1.35-1.55V10.2c0-3.05-1.62-5.62-4.45-6.3v-.55C13.4 2.58 12.77 2 12 2s-1.4.58-1.4 1.35v.55c-2.83.68-4.45 3.25-4.45 6.3v4.35L4.8 16.1c-.55.63-.1 1.65.74 1.65h12.92c.84 0 1.29-1.02.74-1.65Z'/%3E%3C/svg%3E") center / contain no-repeat;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 22a2.7 2.7 0 0 0 2.65-2.25h-5.3A2.7 2.7 0 0 0 12 22ZM19.2 16.1l-1.35-1.55V10.2c0-3.05-1.62-5.62-4.45-6.3v-.55C13.4 2.58 12.77 2 12 2s-1.4.58-1.4 1.35v.55c-2.83.68-4.45 3.25-4.45 6.3v4.35L4.8 16.1c-.55.63-.1 1.65.74 1.65h12.92c.84 0 1.29-1.02.74-1.65Z'/%3E%3C/svg%3E") center / contain no-repeat;
    }

    .notification-bell__count {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: #dc3545;
        color: #ffffff;
        border: 2px solid #ffffff;
        font-size: .68rem;
        font-weight: 700;
        line-height: 14px;
        text-align: center;
    }

    .notification-bell__panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: min(360px, calc(100vw - 24px));
        max-height: 430px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.16);
        z-index: 10060;
    }

    .admin-sidebar .notification-bell {
        align-self: center;
        margin-left: auto;
    }

    .admin-sidebar .notification-bell__panel {
        position: fixed;
        left: 286px;
        right: auto;
        top: auto;
        bottom: 24px;
        width: min(380px, calc(100vw - 310px));
        max-height: min(430px, calc(100vh - 48px));
        z-index: 10060;
    }

    .notification-bell__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem .95rem;
        border-bottom: 1px solid #edf0f2;
        color: #1f2937;
    }

    .notification-bell__mark-all {
        border: none;
        background: transparent;
        color: var(--accent, #27c56f);
        cursor: pointer;
        font: inherit;
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .notification-bell__list {
        max-height: 360px;
        overflow-y: auto;
    }

    .admin-sidebar .notification-bell__list {
        max-height: calc(min(430px, calc(100vh - 48px)) - 52px);
    }

    .notification-bell__item {
        display: block;
        width: 100%;
        border: none;
        border-bottom: 1px solid #f1f3f5;
        background: #ffffff;
        color: #374151;
        cursor: pointer;
        padding: .78rem .95rem;
        text-align: left;
        text-decoration: none;
    }

    .notification-bell__item:hover,
    .notification-bell__item:focus {
        background: #f8f9fa;
        outline: none;
    }

    .notification-bell__item.is-unread {
        background: rgba(39, 197, 111, 0.08);
    }

    .notification-bell__item-title {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .25rem;
        color: #1f2937;
        font-size: .88rem;
        font-weight: 700;
    }

    .notification-bell__item-message {
        color: #4b5563;
        font-size: .8rem;
        line-height: 1.35;
    }

    .notification-bell__item-time,
    .notification-bell__item-type {
        color: #6b7280;
        font-size: .72rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .notification-bell__empty {
        padding: 1rem;
        color: #6b7280;
        font-size: .85rem;
        text-align: center;
    }

    @media (max-width: 992px) {
        .admin-sidebar .notification-bell__panel {
            position: absolute;
            left: 0;
            right: auto;
            top: calc(100% + 10px);
            bottom: auto;
            width: min(360px, calc(100vw - 24px));
        }
    }

    @media (max-width: 420px) {
        .notification-bell__header {
            align-items: flex-start;
            flex-direction: column;
        }

        .notification-bell__mark-all {
            align-self: flex-start;
        }
    }
</style>

<script>
(() => {
    const root = document.currentScript.previousElementSibling.previousElementSibling;
    if (!root || root.dataset.notificationReady === '1') return;
    root.dataset.notificationReady = '1';

    const button = root.querySelector('.notification-bell__button');
    const panel = root.querySelector('[data-notification-panel]');
    const list = root.querySelector('[data-notification-list]');
    const count = root.querySelector('[data-notification-count]');
    const markAll = root.querySelector('[data-notification-mark-all]');
    const recentUrl = '<?= site_url('notifications/recent') ?>';
    const markUrl = '<?= site_url('notifications/mark-read') ?>';
    const markAllUrl = '<?= site_url('notifications/mark-all-read') ?>';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));

    const updateCount = (value) => {
        const unread = Number(value || 0);
        count.textContent = unread > 99 ? '99+' : String(unread);
        count.hidden = unread <= 0;
    };

    const render = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            list.innerHTML = '<div class="notification-bell__empty">No notifications yet.</div>';
            return;
        }

        list.innerHTML = items.map((item) => `
            <button type="button" class="notification-bell__item ${item.is_read ? '' : 'is-unread'}" data-id="${Number(item.id)}" data-link="${escapeHtml(item.link || '')}">
                <span class="notification-bell__item-title">
                    <span>${escapeHtml(item.title)}</span>
                    <span class="notification-bell__item-time">${escapeHtml(item.created_label)}</span>
                </span>
                <span class="notification-bell__item-message">${escapeHtml(item.message)}</span>
                <span class="notification-bell__item-type">${escapeHtml(item.category)}</span>
            </button>
        `).join('');
    };

    const loadNotifications = async () => {
        try {
            const response = await fetch(recentUrl, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            if (!data.success) return;
            updateCount(data.unread_count);
            render(data.notifications);
        } catch (error) {
            // Keep the header usable if notifications cannot load.
        }
    };

    button.addEventListener('click', () => {
        const isOpen = !panel.hidden;
        panel.hidden = isOpen;
        button.setAttribute('aria-expanded', String(!isOpen));
        if (!isOpen) loadNotifications();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            panel.hidden = true;
            button.setAttribute('aria-expanded', 'false');
        }
    });

    list.addEventListener('click', async (event) => {
        const item = event.target.closest('[data-id]');
        if (!item) return;

        const id = item.dataset.id;
        const link = item.dataset.link || '';
        try {
            await fetch(`${markUrl}/${id}`, { method: 'POST', headers: { 'Accept': 'application/json' } });
        } catch (error) {
            // Redirect still matters more than the read-state update.
        }

        if (link) {
            window.location.href = link;
        } else {
            await loadNotifications();
        }
    });

    markAll.addEventListener('click', async () => {
        try {
            const response = await fetch(markAllUrl, { method: 'POST', headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.success) {
                updateCount(0);
                await loadNotifications();
            }
        } catch (error) {
            // No-op.
        }
    });

    loadNotifications();
    window.setInterval(loadNotifications, 60000);
})();
</script>
