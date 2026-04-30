<?= $this->include('rider/partials/header') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .page-header,
    .deliveries-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .page-header {
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: #333333;
        margin-bottom: .6rem;
    }

    .page-header p {
        color: #666666;
        line-height: 1.6;
    }

    .deliveries-panel {
        overflow: hidden;
    }

    .deliveries-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: .75rem;
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
        background: #ffffff;
    }

    .delivery-search-box {
        position: relative;
        width: min(100%, 320px);
    }

    .delivery-search-box input {
        width: 100%;
        height: 42px;
        padding: 0 2.5rem 0 1rem;
        border: 1px solid #d7dce1;
        border-radius: 8px;
        color: #333333;
        font-size: .95rem;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .delivery-search-box input:focus {
        border-color: #27c56f;
        box-shadow: 0 0 0 3px rgba(39, 197, 111, .14);
    }

    .delivery-search-box i {
        position: absolute;
        right: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #8a8f98;
        pointer-events: none;
    }

    .delivery-sort-select {
        height: 42px;
        min-width: 180px;
        padding: 0 2.25rem 0 .9rem;
        border: 1px solid #d7dce1;
        border-radius: 8px;
        background: #ffffff;
        color: #333333;
        font-size: .95rem;
        outline: none;
    }

    .delivery-sort-select:focus {
        border-color: #27c56f;
        box-shadow: 0 0 0 3px rgba(39, 197, 111, .14);
    }

    .deliveries-table {
        width: 100%;
        border-collapse: collapse;
    }

    .deliveries-table th,
    .deliveries-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: top;
    }

    .deliveries-table th {
        background: #f8f9fa;
        color: #333333;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .deliveries-table td {
        color: #333333;
    }

    .muted {
        color: #666666;
        font-size: .9rem;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: .35rem .7rem;
        border: 1px solid #27c56f;
        background: rgba(39, 197, 111, 0.1);
        color: #1d9f57;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }
    
    .action-btn i {
        font-size: 0.8rem;
    }
    
    .btn-complete {
        background: #e8f5e8;
        color: #2e7d2e;
        border: 1px solid #4caf50;
        cursor: default;
    }
    
    .btn-retry {
        background: #ff9800;
        color: white;
        border: none;
    }
    .btn-retry:hover {
        background: #f57c00;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
    }
    
    .btn-pickup {
        background: #2196f3;
        color: white;
        border: none;
    }
    .btn-pickup:hover {
        background: #1976d2;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
    }
    
    .btn-delivered {
        background: #e8f5e8;
        color: #2e7d2e;
        border: 1px solid #4caf50;
    }

    .btn-delivered:hover {
        background: #c8e6c9;
        color: #1b5e20;
        border: 1px solid #388e3c;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    }
    
    .btn-start {
        background: #27c56f;
        color: white;
        border: none;
    }
    .btn-start:hover {
        background: #219653;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(39, 197, 111, 0.3);
    }

    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #666666;
    }

    .delivery-empty-row td {
        border-bottom: 0;
    }

    @media (max-width: 820px) {
        .deliveries-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .delivery-search-box {
            width: 100%;
        }

        .delivery-sort-select {
            width: 100%;
        }

        .deliveries-panel {
            overflow-x: auto;
        }

        .deliveries-table {
            min-width: 760px;
        }
    }
</style>

<?php
    $statusLabels = [
        'to_ship' => 'For Pickup',
        'to_receive' => 'Out for Delivery',
        'completed' => 'Delivered',
        'failed_delivery' => 'Failed Delivery',
        'ready_for_pickup' => 'Waiting for Admin',
        'delivered_to_rider' => 'Order Delivered',
    ];
?>

<section class="page-header">
    <h1>My Deliveries</h1>
    <p>Review delivery-ready orders, customer contacts, shipping addresses, and current delivery status.</p>
</section>

<section class="deliveries-panel">
    <?php if (empty($deliveries)): ?>
        <div class="empty-state">No deliveries are assigned or ready right now.</div>
    <?php else: ?>
        <div class="deliveries-toolbar">
            <div class="delivery-search-box">
                <input type="text"
                       id="deliverySearch"
                       placeholder="Search deliveries..."
                       onkeyup="filterDeliveries()">
                <i class="fas fa-search"></i>
            </div>
            <select id="deliverySortOptions" onchange="sortDeliveries()" class="delivery-sort-select">
                <option value="default">Sort by</option>
                <option value="date-desc">Newest First</option>
                <option value="date-asc">Oldest First</option>
                <option value="customer-asc">Customer (A-Z)</option>
                <option value="customer-desc">Customer (Z-A)</option>
                <option value="status-asc">Status (A-Z)</option>
                <option value="status-desc">Status (Z-A)</option>
                <option value="filter-to_ship">For Pickup</option>
                <option value="filter-to_receive">Out for Delivery</option>
                <option value="filter-completed">Delivered</option>
                <option value="filter-failed_delivery">Failed Delivery</option>
                <option value="filter-ready_for_pickup">Waiting for Admin</option>
                <option value="filter-delivered_to_rider">Order Delivered</option>
            </select>
        </div>
        <table class="deliveries-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $delivery): ?>
                    <?php $status = (string) ($delivery['delivery_status'] ?? 'to_ship'); ?>
                    <tr data-delivery-status="<?= esc($status) ?>">
                        <td>
                            <strong><?= esc($delivery['reference_number'] ?? ('Order #' . ($delivery['id'] ?? ''))) ?></strong>
                            <div class="muted"><?= esc(date('M d, Y', strtotime((string) ($delivery['created_at'] ?? 'now')))) ?></div>
                        </td>
                        <td>
                            <?= esc($delivery['customer']['name'] ?? 'Customer') ?>
                            <div class="muted"><?= esc($delivery['customer']['email'] ?? '') ?></div>
                        </td>
                        <td class="muted"><?= esc($delivery['shipping_address'] ?? 'No delivery address provided') ?></td>
                        <td class="muted"><?= esc($delivery['contact_number'] ?? ($delivery['customer']['phone'] ?? 'Not provided')) ?></td>
                        <td class="muted"><?= esc($delivery['shipment_notes'] ?? 'None') ?></td>
                        <td><span class="status-badge"><?= esc($statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status))) ?></span></td>
                        <td>
                            <?php if ($status === 'completed'): ?>
                                <span class="status-badge" style="background: #e8f5e8; color: #2e7d2e; border: 1px solid #4caf50;">
                                    <i class="fas fa-check-circle"></i> Order Completed
                                </span>
                            <?php elseif ($status === 'delivered_to_rider'): ?>
                                <button class="action-btn btn-delivered" onclick="showDeliveryProofForm(<?= $delivery['id'] ?>)">
                                    <i class="fas fa-check-circle"></i> Order Delivered
                                </button>
                            <?php elseif ($status === 'failed_delivery' || $status === 'failed'): ?>
                                <button class="action-btn btn-retry">
                                    <i class="fas fa-redo"></i> Retry Deliver
                                </button>
                            <?php elseif ($status === 'ready_for_pickup'): ?>
                                <span class="status-badge" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107;">
                                    <i class="fas fa-clock"></i> Waiting for Admin
                                </span>
                            <?php elseif ($status === 'to_ship' || $status === 'for_pickup'): ?>
                                <button class="action-btn btn-pickup" onclick="markReadyForPickup(<?= $delivery['id'] ?>)">
                                    <i class="fas fa-box"></i> Ready to Pickup
                                </button>
                            <?php else: ?>
                                <button class="action-btn btn-start">
                                    <i class="fas fa-play"></i> Start Delivery
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<script>
let originalDeliveries = [];

function markReadyForPickup(orderId) {
    if (confirm('Are you sure you want to mark this order as ready for pickup?')) {
        fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&status=ready_for_pickup`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the updated status badge
                location.reload();
            } else {
                alert(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('.deliveries-table tbody');

    if (!tableBody) {
        return;
    }

    tableBody.querySelectorAll('tr').forEach(row => {
        originalDeliveries.push({
            element: row,
            orderId: row.querySelector('td:nth-child(1)')?.textContent?.toLowerCase() || '',
            date: row.querySelector('td:nth-child(1) .muted')?.textContent || '',
            customer: row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '',
            address: row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '',
            contact: row.querySelector('td:nth-child(4)')?.textContent?.toLowerCase() || '',
            description: row.querySelector('td:nth-child(5)')?.textContent?.toLowerCase() || '',
            status: row.querySelector('td:nth-child(6)')?.textContent?.toLowerCase() || '',
            statusKey: row.dataset.deliveryStatus || ''
        });
    });
});

function getFilteredDeliveries() {
    const searchTerm = document.getElementById('deliverySearch')?.value?.toLowerCase() || '';

    return originalDeliveries.filter(delivery => {
        return delivery.orderId.includes(searchTerm) ||
               delivery.customer.includes(searchTerm) ||
               delivery.address.includes(searchTerm) ||
               delivery.contact.includes(searchTerm) ||
               delivery.description.includes(searchTerm) ||
               delivery.status.includes(searchTerm);
    });
}

function renderDeliveries(deliveries, emptyTitle = 'No deliveries found', emptyMessage = 'Try adjusting your search criteria.') {
    const tableBody = document.querySelector('.deliveries-table tbody');

    if (!tableBody) {
        return;
    }

    tableBody.innerHTML = '';

    if (deliveries.length === 0) {
        tableBody.innerHTML = `
            <tr class="delivery-empty-row">
                <td colspan="7">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>${emptyTitle}</h3>
                        <p>${emptyMessage}</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    deliveries.forEach(delivery => {
        tableBody.appendChild(delivery.element);
    });
}

function filterDeliveries() {
    const sortValue = document.getElementById('deliverySortOptions')?.value || 'default';

    if (sortValue.startsWith('filter-')) {
        sortDeliveries();
        return;
    }

    renderDeliveries(getFilteredDeliveries());
}

function sortDeliveries() {
    const sortValue = document.getElementById('deliverySortOptions')?.value || 'default';
    let deliveries = getFilteredDeliveries();

    if (sortValue.startsWith('filter-')) {
        const statusFilter = sortValue.replace('filter-', '');
        deliveries = deliveries.filter(delivery => delivery.statusKey === statusFilter);
        const statusLabel = document.querySelector(`#deliverySortOptions option[value="${sortValue}"]`)?.textContent || 'selected';

        renderDeliveries(
            deliveries,
            `No ${statusLabel} deliveries found`,
            'There are no deliveries with this status.'
        );
        return;
    }

    deliveries.sort((a, b) => {
        switch (sortValue) {
            case 'date-desc':
                return new Date(b.date) - new Date(a.date);
            case 'date-asc':
                return new Date(a.date) - new Date(b.date);
            case 'customer-asc':
                return a.customer.localeCompare(b.customer);
            case 'customer-desc':
                return b.customer.localeCompare(a.customer);
            case 'status-asc':
                return a.status.localeCompare(b.status);
            case 'status-desc':
                return b.status.localeCompare(a.status);
            default:
                return originalDeliveries.indexOf(a) - originalDeliveries.indexOf(b);
        }
    });

    renderDeliveries(deliveries);
}
</script>

<!-- Delivery Proof Modal -->
<div id="deliveryProofModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Proof of Delivery</h3>
            <span class="close" onclick="closeDeliveryProofForm()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="deliveryProofForm" enctype="multipart/form-data">
                <input type="hidden" id="proofOrderId" name="order_id">
                
                <div class="form-group">
                    <label for="deliveryProof">Upload Delivery Proof Photo:</label>
                    <input type="file" id="deliveryProof" name="delivery_proof" accept="image/*" capture="environment" required>
                    <small>Take a photo or select from your device</small>
                </div>
                
                <div class="form-group">
                    <label for="deliveryNotes">Delivery Notes (Optional):</label>
                    <textarea id="deliveryNotes" name="delivery_notes" rows="3" placeholder="Add any notes about the delivery..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeDeliveryProofForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Submit Proof</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input[type="file"],
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-cancel, .btn-submit {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-cancel {
    background: #f5f5f5;
    color: #666;
}

.btn-cancel:hover {
    background: #e0e0e0;
}

.btn-submit {
    background: #27c56f;
    color: white;
}

.btn-submit:hover {
    background: #219653;
}
</style>

<script>
function showDeliveryProofForm(orderId) {
    document.getElementById('proofOrderId').value = orderId;
    document.getElementById('deliveryProofModal').style.display = 'block';
    document.getElementById('deliveryProofForm').reset();
}

function closeDeliveryProofForm() {
    document.getElementById('deliveryProofModal').style.display = 'none';
}

// Handle form submission
document.getElementById('deliveryProofForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = document.getElementById('proofOrderId').value;
    
    fetch('<?= site_url('dashboard/submitDeliveryProof') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Delivery proof submitted successfully!');
            closeDeliveryProofForm();
            location.reload();
        } else {
            alert(data.message || 'Failed to submit delivery proof');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting delivery proof');
    });
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deliveryProofModal');
    if (event.target == modal) {
        closeDeliveryProofForm();
    }
}
</script>

<?= $this->include('rider/partials/footer') ?>
