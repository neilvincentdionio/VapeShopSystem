<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - E-Commerce Vape Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #333333;
        }

        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 2rem 2rem;
        }

        .page-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 0.95fr);
            gap: 1.5rem;
            align-items: start;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.45rem;
        }

        .page-header p {
            color: #666666;
        }

        .section {
            margin-bottom: 1.2rem;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f3b2f;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .detail-box {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 0.9rem 1rem;
        }

        .detail-label {
            display: block;
            font-size: 0.8rem;
            color: #666666;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-value {
            color: #333333;
            font-weight: 600;
            line-height: 1.5;
            word-break: break-word;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .badge-status-active,
        .badge-approval-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge-status-inactive,
        .badge-approval-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge-approval-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .id-preview {
            width: 100%;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
            min-height: 260px;
            object-fit: contain;
        }

        .id-empty {
            min-height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #666666;
            background: #f8f9fa;
            border: 1px dashed #cfd8d3;
            border-radius: 16px;
            padding: 1.5rem;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            border: 1px solid #e0e0e0;
            background: #ffffff;
            color: #333333;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: #27c56f;
            color: #27c56f;
            background: #f8f9fa;
        }

        .btn-primary {
            background: #27c56f;
            border-color: #27c56f;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #218838;
            border-color: #218838;
            color: #ffffff;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 900px) {
            .page-shell {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: 0 1rem 1.5rem;
            }

            .details-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <?php $approvalStatus = $user['approval_status'] ?? 'approved'; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>Customer Review</h1>
            <p>Review the submitted customer details and verification ID before approval.</p>
        </div>

        <div class="page-shell">
            <section class="card">
                <div class="section">
                    <div class="section-title">Account Details</div>
                    <div class="details-grid">
                        <div class="detail-box">
                            <span class="detail-label">Full Name</span>
                            <div class="detail-value"><?= esc($user['name'] ?? '') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Email Address</span>
                            <div class="detail-value"><?= esc($user['email'] ?? '') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Role</span>
                            <div class="detail-value"><?= esc(ucfirst((string) ($user['role'] ?? 'customer'))) ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Registered</span>
                            <div class="detail-value">
                                <?= !empty($user['created_at']) ? esc(date('M d, Y h:i A', strtotime($user['created_at']))) : 'Not available' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Contact Details</div>
                    <div class="details-grid">
                        <div class="detail-box">
                            <span class="detail-label">Phone Number</span>
                            <div class="detail-value"><?= esc($user['phone_number'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Street Address</span>
                            <div class="detail-value"><?= esc($user['address_line'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">City</span>
                            <div class="detail-value"><?= esc($user['city'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Province</span>
                            <div class="detail-value"><?= esc($user['province'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Postal Code</span>
                            <div class="detail-value"><?= esc($user['postal_code'] ?? 'Not provided') ?></div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Approval Status</div>
                    <div class="details-grid">
                        <div class="detail-box">
                            <span class="detail-label">Approval</span>
                            <div class="detail-value">
                                <span class="badge badge-approval-<?= esc($approvalStatus) ?>"><?= esc(ucfirst($approvalStatus)) ?></span>
                            </div>
                        </div>
                        <div class="detail-box">
                            <span class="detail-label">Account Status</span>
                            <div class="detail-value">
                                <span class="badge badge-status-<?= ($user['is_active'] ?? 0) ? 'active' : 'inactive' ?>">
                                    <?= ($user['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="<?= site_url('user-management') ?>" class="btn">Back to User Management</a>
                    <?php if (($user['role'] ?? '') === 'customer' && $approvalStatus === 'pending'): ?>
                        <form action="<?= site_url('user-management/approve/' . $user['id']) ?>" method="post" style="margin:0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">Approve Customer</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="card">
                <div class="section">
                    <div class="section-title">Verification ID</div>
                    <?php if (!empty($user['verification_id_path'])): ?>
                        <img
                            src="<?= site_url('user-management/verification-id/' . $user['id']) ?>"
                            alt="Customer Verification ID"
                            class="id-preview"
                        >
                        <div class="actions">
                            <a href="<?= site_url('user-management/verification-id/' . $user['id']) ?>" target="_blank" rel="noopener noreferrer" class="btn">
                                Open Full Size ID
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="id-empty">
                            No verification ID was uploaded for this customer.
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
