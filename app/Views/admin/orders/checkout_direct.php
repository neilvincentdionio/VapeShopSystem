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
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
        }

        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #4a90e2;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .order-summary, .payment-form {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 25px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item-name {
            font-weight: 500;
        }

        .item-price {
            color: #666;
        }

        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #4a90e2;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-label {
            font-weight: 500;
        }

        .total-amount {
            font-weight: 600;
            color: #4a90e2;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            background: #4a90e2;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn:hover {
            background: #357abd;
            transform: translateY(-2px);
        }

        .change-display {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-weight: 600;
            color: #d63384;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/dashboard" class="nav-brand">Quick Puff Vape Shop</a>
            <div class="nav-links">
                <a href="/dashboard" class="nav-link">Dashboard</a>
                <a href="/orders" class="nav-link">Orders</a>
                <a href="/dashboard" class="nav-link">Back</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Order Checkout</h1>
        
        <div class="checkout-container">
            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="section-title">Order Summary</h2>
                
                <div class="order-item">
                    <span class="item-name"><?= esc($items[0]['name']) ?></span>
                    <span class="item-price">₱<?= number_format($items[0]['price'], 2) ?></span>
                </div>
                
                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">Subtotal:</span>
                        <span class="total-amount">₱<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total:</span>
                        <span class="total-amount">₱<?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="payment-form">
                <h2 class="section-title">Payment Information</h2>
                
                <form id="checkoutForm">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="ageVerified" required>
                            <label for="ageVerified" class="form-label">Confirm customer is 18+ years old</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select id="paymentMethod" class="form-control" required>
                            <option value="">Select payment method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="gcash">GCash</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="amountReceived" class="form-label">Amount Received</label>
                        <input type="number" id="amountReceived" class="form-control" 
                               placeholder="Enter amount received" step="0.01" min="0" required>
                    </div>
                    
                    <button type="submit" class="btn">Process Payment</button>
                    
                    <div id="changeDisplay" class="change-display" style="display: none;">
                        Change: ₱<span id="changeAmount">0.00</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ageVerified = document.getElementById('ageVerified').checked;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const amountReceived = parseFloat(document.getElementById('amountReceived').value);
            const total = <?= $total ?>;
            
            if (!ageVerified) {
                alert('Please confirm customer is 18+ years old');
                return;
            }
            
            if (!paymentMethod) {
                alert('Please select a payment method');
                return;
            }
            
            if (amountReceived < total) {
                alert('Amount received is insufficient');
                return;
            }
            
            alert('Payment processed successfully! Change: ₱' + (amountReceived - total).toFixed(2));
        });
        
        // Calculate change
        document.getElementById('amountReceived').addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const total = <?= $total ?>;
            const change = Math.max(0, amount - total);
            
            const changeDisplay = document.getElementById('changeDisplay');
            const changeAmount = document.getElementById('changeAmount');
            
            if (amount > 0) {
                changeDisplay.style.display = 'block';
                changeAmount.textContent = change.toFixed(2);
            } else {
                changeDisplay.style.display = 'none';
            }
        });
    </script>
</body>
</html>
