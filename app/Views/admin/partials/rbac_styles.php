<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body.rbac-page {
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f6f7fb;
        min-height: 100vh;
        color: #1f2937;
    }
    .rbac-container {
        margin-left: 270px;
        width: calc(100% - 270px);
        max-width: none;
        padding: 1.5rem 2rem 2rem;
        min-height: 100vh;
    }
    .rbac-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        width: 100%;
    }
    .rbac-card-header {
        padding: 1.35rem 1.75rem;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .rbac-card-title { font-size: 1.35rem; font-weight: 700; }
    .rbac-card-sub { color: #6b7280; font-size: .92rem; margin-top: .25rem; }
    .rbac-card-body { padding: 0; }
    .rbac-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .rbac-table {
        width: 100%;
        min-width: 880px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .rbac-table th,
    .rbac-table td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eef2f7;
        text-align: left;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .rbac-table th {
        background: #f9fafb;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        font-weight: 600;
    }
    .rbac-table tbody tr:last-child td { border-bottom: none; }
    .rbac-table tr:hover td { background: #fafafa; }
    .rbac-table .col-name { width: 14%; }
    .rbac-table .col-desc { width: 28%; }
    .rbac-table .col-level { width: 10%; text-align: center; }
    .rbac-table .col-count { width: 12%; text-align: center; }
    .rbac-table .col-actions { width: 22%; text-align: right; }
    .rbac-table-permissions { min-width: 720px; }
    .rbac-table-permissions .col-name { width: 24%; }
    .rbac-table-permissions .col-desc { width: 40%; }
    .rbac-table-permissions .col-count { width: 14%; }
    .rbac-table-permissions .col-actions { width: 22%; }
    .rbac-table td.col-level,
    .rbac-table td.col-count { text-align: center; }
    .rbac-table td.col-actions { text-align: right; }
    .rbac-table td.col-desc { color: #4b5563; line-height: 1.45; }
    .level-badge {
        display: inline-block;
        background: #dc2626;
        color: #fff;
        border-radius: 999px;
        padding: .2rem .65rem;
        font-size: .8rem;
        font-weight: 600;
    }
    .btn {
        border: 0;
        border-radius: 999px;
        padding: .65rem 1rem;
        font-size: .9rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .btn-primary { background: #166534; color: #fff; }
    .btn-secondary { background: #f3f4f6; color: #111827; border: 1px solid #d1d5db; }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-link { background: transparent; color: #374151; border: 1px solid #d1d5db; }
    .btn-sm { padding: .45rem .85rem; font-size: .82rem; }
    .actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }
    .actions form { margin: 0; display: inline-flex; }
    .rbac-form-card .rbac-card-body,
    .rbac-card-body.rbac-padded { padding: 1.5rem 1.75rem; }
    @media (max-width: 992px) {
        .rbac-container {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
    }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-weight: 600; margin-bottom: .35rem; font-size: .9rem; }
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: .7rem .85rem;
        font-size: .95rem;
    }
    .form-hint { color: #6b7280; font-size: .82rem; margin-top: .25rem; }
    .permission-list {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: .75rem;
    }
    .permission-item {
        display: flex;
        gap: .6rem;
        padding: .5rem .25rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .permission-item:last-child { border-bottom: 0; }
    .permission-item strong { display: block; font-size: .9rem; }
    .permission-item span { color: #6b7280; font-size: .82rem; }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 1.25rem;
    }
    @media (max-width: 900px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
    .detail-box {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem 1.1rem;
    }
    .detail-label { font-size: .75rem; text-transform: uppercase; color: #6b7280; letter-spacing: .04em; }
    .detail-value { font-size: 1.05rem; font-weight: 600; margin-top: .25rem; }
    .perm-chip {
        display: inline-block;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: .35rem .6rem;
        margin: .25rem .35rem 0 0;
        font-size: .85rem;
    }
    .flash { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: 10px; }
    .flash-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .flash-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .errors-list { color: #b91c1c; margin-bottom: 1rem; font-size: .9rem; }
</style>
