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
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333333;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .navbar-brand:hover {
            color: #00bcd4;
        }

        .navbar-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #666666;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: #00bcd4;
            background: rgba(0, 188, 212, 0.1);
        }

        .nav-link.active {
            color: #00bcd4;
            background: rgba(0, 188, 212, 0.1);
            font-weight: 600;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bcd4, #0097a7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }

        .user-name {
            font-weight: 600;
            color: #333333;
            text-decoration: none;
        }

        .user-name:hover {
            color: #00bcd4;
        }

        .badge {
            background: #f0f0f0;
            color: #666666;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .cashier-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .order-section {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .cashier-section {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            height: fit-content;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #00bcd4;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #00bcd4;
        }

        .order-date {
            color: #666;
            font-size: 0.9rem;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .items-table th {
            background: #f8f9fa;
            padding: 0.75rem;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .item-name {
            font-weight: 600;
            color: #333;
        }

        .item-quantity {
            text-align: center;
            color: #666;
        }

        .item-price {
            text-align: right;
            font-weight: 600;
            color: #333;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid #e0e0e0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #00bcd4;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            cursor: pointer;
            font-weight: 600;
            color: #333;
        }

        .payment-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.1rem;
            padding-top: 0.5rem;
            border-top: 2px solid #e0e0e0;
        }

        .change-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #27c56f;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: #27c56f;
            color: white;
        }

        .btn-primary:hover {
            background: #219653;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .receipt-preview {
            background: #ffffff;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            display: none;
        }

        .receipt-preview.show {
            display: block;
        }

        .receipt-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #333;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .receipt-total {
            border-top: 2px solid #333;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: bold;
        }

        .warning-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #856404;
        }

        .warning-message i {
            margin-right: 0.5rem;
        }

        @media (max-width: 1024px) {
            .cashier-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .cashier-container {
                padding: 0 1rem;
            }
            
            .items-table {
                font-size: 0.8rem;
            }
            
            .items-table th,
            .items-table td {
                padding: 0.5rem;
            }
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
                    <a href="<?= site_url('products') ?>" class="nav-link">Products</a>
                    <a href="<?= site_url('orders') ?>" class="nav-link active">Orders</a>
                    <a href="<?= site_url('records') ?>" class="nav-link">Records</a>
                    <a href="<?= site_url('user-management') ?>" class="nav-link">User Management</a>
                    <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                </div>
            </div>

            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?></div>
                    <a href="<?= site_url('dashboard/profile') ?>" class="user-name user-profile-link">
                        <?= esc(session()->get('user_name') ?? 'Administrator') ?>
                    </a>
                    <span class="badge"><?= esc(strtoupper(session()->get('user_role') ?? 'admin')) ?></span>
                    <?php if (!empty(session()->get('user_shop_name'))): ?>
                        <span class="badge"><?= esc(session()->get('user_shop_name')) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="cashier-container">
        <!-- Order Details Section -->
        <div class="order-section">
            <h2 class="section-title">Order Details</h2>
            
            <div class="order-header">
                <div>
                    <div class="order-number">Order #<?= esc($reference_number) ?></div>
                    <div class="order-date">Placed on <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="item-name"><?= esc($item['name']) ?></td>
                            <td class="item-quantity"><?= (int) $item['qty'] ?></td>
                            <td class="item-price">₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                            <td class="item-price">₱<?= number_format((float) $item['unit_price'] * (int) $item['qty'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="order-total">
                <span>Total Amount</span>
                <span id="totalAmount">₱<?= number_format((float) $total, 2) ?></span>
            </div>
        </div>

        <!-- Cashier Section -->
        <div class="cashier-section">
            <h2 class="section-title">Cashier</h2>
            
            <div class="warning-message">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning:</strong> This action will update stock levels and complete the order.
            </div>

            <form method="POST" action="<?= site_url('orders/checkout-submit/' . $order['id']) ?>" id="cashierForm">
                <!-- Age Verification -->
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="ageVerified" name="age_verified" required>
                        <label for="ageVerified">Customer age verified (18+)</label>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-group">
                    <label class="form-label" for="paymentMethod">Payment Method</label>
                    <select class="form-input" id="paymentMethod" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <!-- Amount Received -->
                <div class="form-group">
                    <label class="form-label" for="amountReceived">Amount Received</label>
                    <input type="number" class="form-input" id="amountReceived" name="amount_received" 
                           step="0.01" min="0" required>
                </div>

                <!-- Payment Summary -->
                <div class="payment-summary">
                    <div class="summary-row">
                        <span>Total Amount:</span>
                        <span id="summaryTotal">₱<?= number_format((float) $total, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Amount Received:</span>
                        <span id="summaryReceived">₱0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Change:</span>
                        <span id="summaryChange" class="change-amount">₱0.00</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <button type="submit" class="btn btn-primary" id="processBtn" disabled>
                    <i class="fas fa-cash-register"></i>
                    Process Payment
                </button>
                
                <button type="button" class="btn btn-secondary" onclick="printReceipt()" id="printBtn" style="display: none;">
                    <i class="fas fa-print"></i>
                    Print Receipt
                </button>

                <a href="<?= site_url('orders') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Orders
                </a>
            </form>

            <!-- Receipt Preview -->
            <div class="receipt-preview" id="receiptPreview">
                <div class="receipt-header">
                    E-COMMERCE VAPE SHOP
                    <br>Official Receipt
                </div>
                <div>
                    <strong>Order #:</strong> <?= esc($reference_number) ?>
                    <br><strong>Date:</strong> <?= date('Y-m-d H:i:s') ?>
                    <br><strong>Cashier:</strong> <?= esc(session()->get('user_name')) ?>
                    <br>================================
                </div>
                <?php foreach ($items as $item): ?>
                    <div class="receipt-item">
                        <span><?= esc($item['name']) ?> x<?= (int) $item['qty'] ?></span>
                        <span>₱<?= number_format((float) $item['unit_price'] * (int) $item['qty'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="receipt-total">
                    <div class="receipt-item">
                        <span>Total:</span>
                        <span id="receiptTotal">₱<?= number_format((float) $total, 2) ?></span>
                    </div>
                    <div class="receipt-item">
                        <span>Received:</span>
                        <span id="receiptReceived">₱0.00</span>
                    </div>
                    <div class="receipt-item">
                        <span>Change:</span>
                        <span id="receiptChange">₱0.00</span>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #333;">
                    Thank you for your purchase!
                </div>
            </div>
        </div>
    </div>

    <script>
        const totalAmount = <?= (float) $total ?>;
        const amountReceivedInput = document.getElementById('amountReceived');
        const ageVerifiedCheckbox = document.getElementById('ageVerified');
        const processBtn = document.getElementById('processBtn');
        const printBtn = document.getElementById('printBtn');
        const receiptPreview = document.getElementById('receiptPreview');

        // Update payment summary
        function updatePaymentSummary() {
            const amountReceived = parseFloat(amountReceivedInput.value) || 0;
            const change = amountReceived - totalAmount;
            
            document.getElementById('summaryReceived').textContent = `₱${amountReceived.toFixed(2)}`;
            document.getElementById('summaryChange').textContent = `₱${Math.max(0, change).toFixed(2)}`;
            
            // Update receipt preview
            document.getElementById('receiptReceived').textContent = `₱${amountReceived.toFixed(2)}`;
            document.getElementById('receiptChange').textContent = `₱${Math.max(0, change).toFixed(2)}`;
            
            // Enable/disable process button
            const canProcess = ageVerifiedCheckbox.checked && amountReceived >= totalAmount;
            processBtn.disabled = !canProcess;
        }

        // Event listeners
        amountReceivedInput.addEventListener('input', updatePaymentSummary);
        ageVerifiedCheckbox.addEventListener('change', updatePaymentSummary);

        // Set initial amount received to total amount
        amountReceivedInput.value = totalAmount;
        updatePaymentSummary();

        // Handle form submission
        document.getElementById('cashierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const amountReceived = parseFloat(formData.get('amount_received'));
            
            if (amountReceived < totalAmount) {
                alert('Amount received is insufficient!');
                return;
            }
            
            // Submit the form via AJAX
            fetch('<?= site_url('orders/checkout-submit/' . $order['id']) ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show receipt immediately
                    printBtn.style.display = 'inline-flex';
                    receiptPreview.classList.add('show');
                    
                    // Update receipt with actual payment data
                    document.getElementById('receiptReceived').textContent = `₱${amountReceived.toFixed(2)}`;
                    document.getElementById('receiptChange').textContent = `₱${(amountReceived - totalAmount).toFixed(2)}`;
                    
                    // Hide process button and show success message
                    processBtn.style.display = 'none';
                    
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'alert alert-success';
                    successDiv.style.cssText = 'background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #c3e6cb;';
                    successDiv.innerHTML = '<i class="fas fa-check-circle"></i> <strong>Payment Processed Successfully!</strong> Order completed and stock updated.';
                    
                    const form = document.getElementById('cashierForm');
                    form.insertBefore(successDiv, form.firstChild);
                    
                    // Auto-scroll to receipt
                    receiptPreview.scrollIntoView({ behavior: 'smooth' });
                    
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing payment.');
            });
        });

        // Print receipt function
        function printReceipt() {
            const receiptContent = document.getElementById('receiptPreview').innerHTML;
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt - Order #<?= esc($reference_number) ?></title>
                    <style>
                        @page {
                            size: 80mm auto;
                            margin: 5mm;
                        }
                        body { 
                            font-family: 'Courier New', monospace; 
                            padding: 5px; 
                            margin: 0;
                            width: 100%;
                            font-size: 10px;
                            line-height: 1.2;
                        }
                        .receipt-header { 
                            text-align: center; 
                            font-weight: bold; 
                            margin-bottom: 10px; 
                            font-size: 12px;
                        }
                        .receipt-item { 
                            display: flex; 
                            justify-content: space-between; 
                            margin: 2px 0;
                            font-size: 10px;
                        }
                        .receipt-total { 
                            border-top: 1px solid #000; 
                            padding-top: 5px; 
                            margin-top: 5px; 
                            font-weight: bold;
                            font-size: 10px;
                        }
                        @media print {
                            body { 
                                margin: 0; 
                                padding: 5px;
                                width: 100%;
                                height: auto;
                                overflow: hidden;
                            }
                            @page {
                                size: 80mm auto;
                                margin: 2mm;
                            }
                        }
                    </style>
                </head>
                <body>${receiptContent}</body>
                </html>
            `);
            printWindow.document.close();
            
            // Wait for content to load then print
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }

        // Show print button after successful payment (this would be set after form submission)
        <?php if (session()->getFlashdata('success')): ?>
            printBtn.style.display = 'inline-flex';
            receiptPreview.classList.add('show');
        <?php endif; ?>
    </script>

</body>
</html>
