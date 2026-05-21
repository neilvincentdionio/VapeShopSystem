<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            font-family: var(--main-font);
            background: #f5f7fa;
            min-height: 100vh; position: relative; color: #333333;
        }
        .navbar {
            position: sticky; top: 0; z-index: 20;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .navbar-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; gap: 1.2rem; align-items: center; }
        .navbar-brand {
            color: #333333;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center { flex: 1 1 auto; display: flex; justify-content: center; min-width: 0; }
        .navbar-menu { display: flex; gap: .75rem; align-items: center; flex-wrap: nowrap; }
        .navbar-menu a, .nav-dropdown-btn {
            color: #333333;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: all .3s;
        }
        .nav-link { padding: .45rem .75rem; border-radius: 6px; }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background: #f8f9fa; color: #27c56f; }
        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { 
            display: block; 
            color: #333333; 
            text-decoration: none; 
            padding: .5rem 1rem; 
            transition: background-color .3s; 
        }
        .nav-dropdown-content a:hover { 
            background-color: #f8f9fa; 
            color: #27c56f; 
        }
        .nav-right { display: flex; align-items: center; gap: .8rem; flex: 0 0 auto; }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #333333; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #27c56f;
            color: #ffffff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .badge {
            border: 1px solid #e0e0e0;
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
            background: #f8f9fa;
            color: #666666;
        }

        .container { max-width: none; margin: 0; padding: 0; position: relative; z-index: 2; }
        .panel {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }
        .row { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
        .container input,
        .container select,
        .container button {
            font-family: inherit;
            border: 1px solid #e0e0e0;
            background: #ffffff;
            color: #333333;
            border-radius: 8px;
            padding: .55rem .7rem;
        }
        select option { color: #000; }
        .btn {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .55rem .85rem;
            border-radius: 8px;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            line-height: 1.1;
            white-space: nowrap;
        }
        .btn-primary { background: #2f6fed; }
        .btn-success { background: #1f9d55; }
        .btn-info { background: #0ea5e9; }
        .btn-warning { background: #d48806; }
        .btn-danger { background: #dc3545; }
        .btn-secondary { background: #6b7280; color: #ffffff; }
        .btn:hover { filter: brightness(0.95); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .7rem; border-bottom: 1px solid #e0e0e0; text-align: left; font-size: .92rem; color: #333333; }
        th { color: #333333; font-weight: 600; background: #f8f9fa; }
        .sort-link { color: #333333; text-decoration: none; }
        .sort-link:hover { text-decoration: underline; }
        .status-active { color: #28a745; font-weight: 600; }
        .status-inactive { color: #dc3545; font-weight: 600; }
        .status-return-refund { color: #6d28d9; font-weight: 600; }
        .alert { padding: .8rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .actions { display: flex; gap: .4rem; flex-wrap: nowrap; align-items: center; }
        .actions form { margin: 0; display: inline-flex; }
        th:last-child, td:last-child { min-width: 220px; }
        .pagination-wrap { margin-top: 1rem; }
        .pagination-wrap ul { list-style: none; display: flex; gap: .35rem; flex-wrap: wrap; }
        .pagination-wrap a, .pagination-wrap span {
            color: #333333; text-decoration: none; padding: .35rem .6rem; border: 1px solid #e0e0e0; border-radius: 6px;
            background: #ffffff;
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
            max-width: 90vw;
            max-height: 85vh;
            overflow: auto;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
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
            grid-template-columns: repeat(2, minmax(200px, 1fr));
            gap: .6rem 1rem;
        }
        .detail-item {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: .6rem;
        }
        .detail-label {
            font-size: .82rem;
            color: #666666;
            margin-bottom: .2rem;
            font-weight: 500;
        }
        .detail-value {
            font-size: .95rem;
            word-break: break-word;
            color: #333333;
            font-weight: 600;
        }
        .modal-note {
            margin-top: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 1rem;
            background: #f8f9fa;
            color: #333333;
            font-size: .9rem;
            line-height: 1.4;
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
            .modal-card { 
                width: 95vw;
                padding: 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?= $this->include('admin/partials/sidebar_styles') ?>
<?= view('admin/partials/records_reports_layout') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>
<?php
    $recordStatusLabels = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'return_refund' => 'Return/Refund',
    ];
    $recordStatusClass = static function (string $status): string {
        if ($status === 'completed') {
            return 'status-active';
        }
        if ($status === 'return_refund') {
            return 'status-return-refund';
        }

        return 'status-inactive';
    };
?>

    <div class="container records-reports-page">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= view('admin/partials/records_reports_nav', ['activeTab' => 'records']) ?>

        <section class="module-shell">
            <div class="module-header">
                <div>
                    <h2>Records Module</h2>
                    <?php if (!empty($user_shop_name)): ?>
                        <p>Shop: <?= htmlspecialchars($user_shop_name) ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('records/create') ?>" class="btn btn-success">Add Record</a>
            </div>

            <div class="module-toolbar">
                <form action="<?= site_url('records') ?>" method="get" class="filter-form">
                    <input type="hidden" name="date_sort" value="<?= htmlspecialchars($date_sort) ?>">
                    <input type="text" class="search-input" name="q" placeholder="Search reference, title, description..." value="<?= htmlspecialchars($search) ?>">
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
                        <option value="return_refund" <?= $status === 'return_refund' ? 'selected' : '' ?>>Return/Refund</option>
                    </select>
                    <div>
                        <label for="from_date">From</label>
                        <input id="from_date" type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" title="From Date">
                    </div>
                    <div>
                        <label for="to_date">To</label>
                        <input id="to_date" type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" title="To Date">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= site_url('records') ?>" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <div class="module-actions-bar">
                <strong>Export Options</strong>
                <div class="module-actions-group">
                    <button type="button" class="btn btn-info" onclick="exportRecords('excel')">Export Excel</button>
                    <button type="button" class="btn btn-info" onclick="exportRecords('pdf')">Export PDF</button>
                    <button type="button" class="btn btn-info" onclick="printRecords()">Print View</button>
                </div>
            </div>

            <div class="module-table-wrap">
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
                                <td class="<?= esc($recordStatusClass((string) ($item['status'] ?? 'pending'))) ?>"><?= htmlspecialchars($recordStatusLabels[(string) ($item['status'] ?? 'pending')] ?? ucfirst((string) ($item['status'] ?? 'pending'))) ?></td>
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
        </section>

        <div id="record-modal" class="modal-overlay" aria-hidden="true">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="record-modal-title">
                <div class="modal-head">
                    <h3 id="record-modal-title">Record Details</h3>
                    <button type="button" class="btn btn-secondary" id="record-modal-close-top">Close</button>
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

            // Export functions
            function exportRecords(format) {
                // Get current filter parameters
                const params = new URLSearchParams(window.location.search);
                params.set('format', format);
                
                let url;
                switch(format) {
                    case 'csv':
                        url = '<?= site_url('records/export-csv') ?>';
                        break;
                    case 'excel':
                        url = '<?= site_url('records/export-excel') ?>';
                        break;
                    case 'pdf':
                        url = '<?= site_url('records/generate-pdf') ?>';
                        break;
                    default:
                        return;
                }
                
                // Add filter parameters to export URL
                url += '?' + params.toString();
                
                // Open in new window to download
                window.open(url, '_blank');
            }

            function printRecords() {
                // Get current filter parameters
                const params = new URLSearchParams(window.location.search);
                const url = '<?= site_url('records/print') ?>?' + params.toString();
                window.open(url, '_blank');
            }

            // Make functions globally accessible
            window.exportRecords = exportRecords;
            window.printRecords = printRecords;

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






