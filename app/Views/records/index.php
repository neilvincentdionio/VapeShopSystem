<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - E-Commerce Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            font-family: var(--main-font);
            background: url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
            min-height: 100vh; position: relative; color: #fff;
        }
        body::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.58); z-index: 1; }
        .navbar {
            position: sticky; top: 0; z-index: 20;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            padding: 1rem 2rem;
            backdrop-filter: blur(18px);
        }
        .navbar-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; gap: 1.2rem; align-items: center; }
        .navbar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center { flex: 1 1 auto; display: flex; justify-content: center; min-width: 0; }
        .navbar-menu { display: flex; gap: .75rem; align-items: center; flex-wrap: nowrap; }
        .navbar-menu a, .nav-dropdown-btn {
            color: #fff;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: background-color .3s;
        }
        .nav-link { padding: .45rem .75rem; border-radius: 6px; }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background: rgba(255,255,255,.2); }
        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { display: block; }
        .nav-right { display: flex; align-items: center; gap: .8rem; flex: 0 0 auto; }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #fff; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .badge {
            border: 1px solid rgba(255,255,255,.3);
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger:hover { background-color: #c82333; }

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; position: relative; z-index: 2; }
        .panel {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 16px;
            padding: 1rem;
            backdrop-filter: blur(18px);
            margin-bottom: 1rem;
        }
        .row { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
        input, select, button {
            font-family: inherit;
            border: 1px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.15);
            color: #fff;
            border-radius: 8px;
            padding: .55rem .7rem;
        }
        select option { color: #000; }
        .btn { text-decoration: none; display: inline-block; padding: .55rem .75rem; border-radius: 8px; color: #fff; border: none; cursor: pointer; }
        .btn-primary { background: #2f6fed; }
        .btn-success { background: #1f9d55; }
        .btn-info { background: #0ea5e9; }
        .btn-warning { background: #d48806; }
        .btn-danger { background: #dc3545; }
        .btn-muted { background: rgba(255,255,255,.2); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .7rem; border-bottom: 1px solid rgba(255,255,255,.15); text-align: left; font-size: .92rem; }
        th { color: #f3f3f3; }
        .sort-link { color: #f3f3f3; text-decoration: none; }
        .sort-link:hover { text-decoration: underline; }
        .status-active { color: #8ff0b2; font-weight: 600; }
        .status-inactive { color: #ffc3c3; font-weight: 600; }
        .alert { padding: .8rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: rgba(46, 204, 113, .25); }
        .alert-error { background: rgba(231, 76, 60, .25); }
        .actions { display: flex; gap: .4rem; flex-wrap: wrap; }
        .pagination-wrap { margin-top: 1rem; }
        .pagination-wrap ul { list-style: none; display: flex; gap: .35rem; flex-wrap: wrap; }
        .pagination-wrap a, .pagination-wrap span {
            color: #fff; text-decoration: none; padding: .35rem .6rem; border: 1px solid rgba(255,255,255,.3); border-radius: 6px;
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 80;
            padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal-card {
            width: min(760px, 100%);
            max-height: 85vh;
            overflow: auto;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 16px;
            padding: 1rem;
            backdrop-filter: blur(18px);
        }
        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            margin-bottom: .8rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem 1rem;
        }
        .detail-item {
            border-bottom: 1px solid rgba(255,255,255,.18);
            padding-bottom: .45rem;
        }
        .detail-label {
            font-size: .78rem;
            color: rgba(255,255,255,.75);
            margin-bottom: .15rem;
        }
        .detail-value {
            font-size: .92rem;
            word-break: break-word;
        }
        .modal-note {
            margin-top: .8rem;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 10px;
            padding: .75rem;
            background: rgba(255,255,255,.08);
        }
        .modal-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }
        @media (max-width: 768px) {
            .navbar-content { flex-direction: column; align-items: stretch; gap: .75rem; }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .nav-right { justify-content: space-between; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>
            <div class="navbar-center">
                <div class="navbar-menu">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">Dashboard</a>
                    <a href="<?= site_url('records') ?>" class="nav-link active">Records</a>
                    <a href="<?= site_url('dashboard/profile') ?>" class="nav-link">Profile</a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <?php endif; ?>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-btn">Quick Actions</button>
                        <div class="nav-dropdown-content">
                            <a href="<?= site_url('records/create') ?>">Add Record</a>
                            <a href="<?= site_url('records') ?>">Manage Records</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role)) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="panel">
            <div class="row" style="justify-content: space-between;">
                <h2>Records Module</h2>
                <?php if (!empty($user_shop_name)): ?><span>Shop: <?= htmlspecialchars($user_shop_name) ?></span><?php endif; ?>
                <a href="<?= site_url('records/create') ?>" class="btn btn-success">Add Record</a>
            </div>
        </div>

        <div class="panel">
            <form action="<?= site_url('records') ?>" method="get" class="row">
                <input type="hidden" name="date_sort" value="<?= htmlspecialchars($date_sort) ?>">
                <input type="text" name="q" placeholder="Search reference, title, description..." value="<?= htmlspecialchars($search) ?>">
                <select name="record_type">
                    <option value="">All Types</option>
                    <?php foreach ($record_types as $type): ?>
                        <option value="<?= htmlspecialchars($type['record_type']) ?>" <?= $record_type === $type['record_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type['record_type'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <label for="from_date">From</label>
                <input id="from_date" type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" title="From Date">
                <label for="to_date">To</label>
                <input id="to_date" type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" title="To Date">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= site_url('records') ?>" class="btn btn-muted">Reset</a>
            </form>
        </div>

        <div class="panel" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><a class="sort-link" href="<?= htmlspecialchars($date_sort_url) ?>"><?= htmlspecialchars($date_sort_label) ?></a></th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $item): ?>
                            <tr>
                                <td><?= (int) $item['id'] ?></td>
                                <td>
                                    <?php
                                        $recordDate = $item['date'] ?? ($item['record_date'] ?? '');
                                        $recordDateTs = strtotime((string) $recordDate);
                                    ?>
                                    <?= $recordDateTs !== false ? htmlspecialchars(date('M d, Y', $recordDateTs)) : '-' ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($item['reference_number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($item['record_type'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars((string) ($item['title'] ?? '')) ?></td>
                                <td><?= (int) ($item['quantity'] ?? 0) ?></td>
                                <td>&#8369;<?= number_format((float) ($item['unit_price'] ?? 0), 2) ?></td>
                                <td>&#8369;<?= number_format((float) ($item['total_amount'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($item['payment_status'] ?? 'unpaid'))) ?></td>
                                <td class="<?= ($item['status'] ?? '') === 'completed' ? 'status-active' : 'status-inactive' ?>"><?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'pending'))) ?></td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="btn btn-info js-view-record" data-id="<?= (int) $item['id'] ?>">View</button>
                                        <a href="<?= site_url('records/edit/' . $item['id']) ?>" class="btn btn-warning">Edit</a>
                                        <?php if ($user_role === 'admin'): ?>
                                            <form action="<?= site_url('records/delete/' . $item['id']) ?>" method="post" onsubmit="return confirm('Delete this record?')" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination-wrap">
                <?= $pager->links() ?>
            </div>
        </div>

        <div id="record-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="record-modal-title">
                <div class="modal-head">
                    <h3 id="record-modal-title">Record Details</h3>
                    <button type="button" class="btn btn-muted" id="record-modal-close-top">Close</button>
                </div>
                <div id="record-modal-content">Select a record to view details.</div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" id="record-modal-close">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('record-modal');
            const modalContent = document.getElementById('record-modal-content');
            const closeButtons = [document.getElementById('record-modal-close-top'), document.getElementById('record-modal-close')];
            const viewButtons = document.querySelectorAll('.js-view-record');
            const recordBaseUrl = '<?= site_url('records') ?>';

            const detailFields = [
                ['id', 'ID'],
                ['date', 'Date'],
                ['reference_number', 'Reference Number'],
                ['record_type', 'Record Type'],
                ['title', 'Title'],
                ['description', 'Description'],
                ['quantity', 'Quantity'],
                ['unit_price', 'Unit Price'],
                ['total_amount', 'Total Amount'],
                ['payment_method', 'Payment Method'],
                ['payment_status', 'Payment Status'],
                ['record_date', 'Record Date'],
                ['status', 'Status'],
                ['created_by', 'Created By'],
                ['created_at', 'Created At'],
                ['updated_at', 'Updated At'],
            ];

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function toPeso(value) {
                const amount = Number(value);
                if (Number.isNaN(amount)) {
                    return value ?? '-';
                }
                return '\u20B1' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatDate(value, includeTime = false) {
                if (!value) {
                    return '-';
                }
                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) {
                    return value;
                }
                if (includeTime) {
                    return parsed.toLocaleString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                }
                return parsed.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            }

            function prettify(field, value) {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }
                if (field === 'unit_price' || field === 'total_amount') {
                    return toPeso(value);
                }
                if (field === 'date' || field === 'record_date') {
                    return formatDate(value);
                }
                if (field === 'created_at' || field === 'updated_at') {
                    return formatDate(value, true);
                }
                if (field === 'record_type' || field === 'payment_method' || field === 'payment_status' || field === 'status') {
                    return String(value).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
                }
                return value;
            }

            function openModal() {
                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }

            function renderRecord(record) {
                let html = '<div class="detail-grid">';
                for (const [field, label] of detailFields) {
                    html += '<div class="detail-item">';
                    html += '<div class="detail-label">' + escapeHtml(label) + '</div>';
                    html += '<div class="detail-value">' + escapeHtml(prettify(field, record[field])) + '</div>';
                    html += '</div>';
                }
                html += '</div>';

                html += '<div class="modal-note">';
                html += '<div class="detail-label">Notes</div>';
                html += '<div class="detail-value">' + escapeHtml(record.notes || '-') + '</div>';
                html += '</div>';

                modalContent.innerHTML = html;
            }

            async function fetchRecord(id) {
                modalContent.innerHTML = '<p>Loading record details...</p>';
                openModal();

                try {
                    const response = await fetch(recordBaseUrl + '/' + encodeURIComponent(id), {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await response.json() : null;
                    if (!response.ok) {
                        throw new Error(data && data.message ? data.message : 'Unable to load record.');
                    }
                    if (!data || !data.success) {
                        throw new Error((data && data.message) ? data.message : 'Unable to load record.');
                    }

                    renderRecord(data.record);
                } catch (error) {
                    modalContent.innerHTML = '<p>' + escapeHtml(error.message || 'Unable to load record details.') + '</p>';
                }
            }

            viewButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    fetchRecord(button.dataset.id);
                });
            });

            closeButtons.forEach((button) => {
                if (!button) {
                    return;
                }
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
