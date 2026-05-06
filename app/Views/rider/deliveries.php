<?= $this->include('rider/partials/header') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

    .deliveries-table td:last-child {
        vertical-align: middle;
        width: 180px;
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
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        font-family: var(--main-font, 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif);
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
        min-width: 145px;
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
        background: #4caf50;
        color: white;
        border: none;
    }

    .btn-delivered:hover {
        background: #388e3c;
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

    .action-stack {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
        width: 145px;
    }

    .action-stack .status-badge,
    .action-stack .action-btn {
        width: 100%;
        min-width: 0;
        justify-content: center;
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
        'ready_for_pickup' => 'Rider Assigned',
        'accepted_by_rider' => 'Accepted by Rider',
        'delivered_to_rider' => 'Picked Up',
        'delivered' => 'Delivered',
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
                <option value="filter-ready_for_pickup">Rider Assigned</option>
                <option value="filter-accepted_by_rider">Accepted by Rider</option>
                <option value="filter-delivered_to_rider">Picked Up</option>
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
                    <tr data-delivery-status="<?= esc($status) ?>" data-order-id="<?= (int) ($delivery['id'] ?? 0) ?>">
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
                            <div class="action-stack">
                            <?php if (in_array($status, ['completed', 'delivered'], true)): ?>
                                <span class="status-badge" style="background: #e8f5e8; color: #2e7d2e; border: 1px solid #4caf50;">
                                    <i class="fas fa-check-circle"></i> Order Completed
                                </span>
                            <?php else: ?>
                                <button type="button" class="action-btn btn-pickup" onclick="openOrderDetailsModal(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button type="button" class="action-btn btn-pickup" onclick="openRouteMap(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-map"></i> View Map
                                </button>
                            <?php endif; ?>
                            <?php if ($status === 'ready_for_pickup'): ?>
                                <button type="button" class="action-btn btn-start" onclick="acceptDelivery(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-check"></i> Accept Delivery
                                </button>
                            <?php elseif ($status === 'accepted_by_rider'): ?>
                                <button type="button" class="action-btn btn-pickup" onclick="markPickedUp(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-box"></i> Mark Picked Up
                                </button>
                            <?php elseif ($status === 'delivered_to_rider'): ?>
                                <button type="button" class="action-btn btn-start" onclick="startDelivery(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-play"></i> Start Delivery
                                </button>
                            <?php elseif ($status === 'failed_delivery' || $status === 'failed'): ?>
                                <button type="button" class="action-btn btn-retry" onclick="retryDelivery(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-redo"></i> Retry Deliver
                                </button>
                            <?php elseif ($status === 'to_receive'): ?>
                                <button type="button" class="action-btn btn-delivered" onclick="showDeliveryProofForm(<?= (int) $delivery['id'] ?>)">
                                    <i class="fas fa-check-circle"></i> Order Delivered
                                </button>
                            <?php elseif ($status === 'to_ship' || $status === 'for_pickup'): ?>
                                <span class="status-badge" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107;">
                                    <i class="fas fa-clock"></i> Waiting for Rider Assignment
                                </span>
                            <?php endif; ?>
                            </div>
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
                reloadDeliveriesPage();
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

function updateRiderDeliveryStatus(orderId, status, successMessage, confirmMessage = '') {
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }

    fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${encodeURIComponent(status)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(successMessage);
            reloadDeliveriesPage();
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    });
}

function startDelivery(orderId) {
    if (hasOtherActiveDelivery(orderId)) {
        alert('You cannot run two deliveries at the same time. Please complete your current Out for Delivery order before starting a new one.');
        return;
    }

    if (!navigator.geolocation) {
        updateRiderDeliveryStatus(orderId, 'to_receive', 'Delivery started.');
        return;
    }
    navigator.geolocation.getCurrentPosition((position) => {
        const body = `order_id=${orderId}&status=to_receive&rider_latitude=${encodeURIComponent(position.coords.latitude)}&rider_longitude=${encodeURIComponent(position.coords.longitude)}`;
        fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        }).then(r => r.json()).then(data => {
            if (data.success) {
                startLocationTracking(orderId);
                alert('Delivery started.');
                reloadDeliveriesPage();
            } else {
                alert(data.message || 'Failed to update status');
            }
        });
    }, () => {
        updateRiderDeliveryStatus(orderId, 'to_receive', 'Delivery started.');
    });
}

function hasOtherActiveDelivery(orderId) {
    const activeRows = Array.from(document.querySelectorAll('tr[data-delivery-status="to_receive"]'));
    return activeRows.some((row) => parseInt(row.dataset.orderId || '0', 10) !== parseInt(orderId, 10));
}

let pendingCancelOrderId = null;
function openDeliveryCancelPrompt(orderId, message) {
    pendingCancelOrderId = parseInt(orderId || '0', 10);
    const modal = document.getElementById('deliveryCancelPromptModal');
    const messageEl = document.getElementById('deliveryCancelPromptMessage');
    const confirmBtn = document.getElementById('deliveryCancelPromptConfirmBtn');
    if (messageEl) {
        messageEl.textContent = message || 'Do you want to cancel this delivery?';
    }
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            const id = pendingCancelOrderId;
            closeDeliveryCancelPrompt();
            if (id > 0) {
                cancelDelivery(id);
            }
        };
    }
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeDeliveryCancelPrompt() {
    const modal = document.getElementById('deliveryCancelPromptModal');
    if (modal) {
        modal.style.display = 'none';
    }
    pendingCancelOrderId = null;
}

function startLocationTracking(orderId) {
    if (!navigator.geolocation) return;
    navigator.geolocation.watchPosition((position) => {
        const body = `order_id=${orderId}&rider_latitude=${encodeURIComponent(position.coords.latitude)}&rider_longitude=${encodeURIComponent(position.coords.longitude)}`;
        fetch('<?= site_url('dashboard/updateRiderLocation') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        }).catch(() => {});
    }, () => {}, { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 });
}

function startActiveDeliveriesLocationLoop() {
    if (!navigator.geolocation) {
        return;
    }

    const activeOrderIds = Array.from(document.querySelectorAll('tr[data-delivery-status="to_receive"]'))
        .map((row) => parseInt(row.dataset.orderId || '0', 10))
        .filter((id) => Number.isInteger(id) && id > 0);

    if (activeOrderIds.length === 0) {
        return;
    }

    const pushLocation = (lat, lng) => {
        activeOrderIds.forEach((orderId) => {
            const body = `order_id=${orderId}&rider_latitude=${encodeURIComponent(lat)}&rider_longitude=${encodeURIComponent(lng)}`;
            fetch('<?= site_url('dashboard/updateRiderLocation') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            }).catch(() => {});
        });
    };

    navigator.geolocation.getCurrentPosition((position) => {
        pushLocation(position.coords.latitude, position.coords.longitude);
    }, () => {}, { enableHighAccuracy: true, timeout: 10000 });

    navigator.geolocation.watchPosition((position) => {
        pushLocation(position.coords.latitude, position.coords.longitude);
    }, () => {}, { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 });
}

function retryDelivery(orderId) {
    updateRiderDeliveryStatus(orderId, 'to_receive', 'Delivery retry started.', 'Retry this failed delivery now?');
}

function markPickedUp(orderId) {
    updateRiderDeliveryStatus(orderId, 'delivered_to_rider', 'Order marked as picked up.');
}

function acceptDelivery(orderId) {
    updateRiderDeliveryStatus(orderId, 'accepted_by_rider', 'Delivery accepted.');
}

function cancelDelivery(orderId) {
    const reason = prompt('Enter cancellation reason (optional):', '');
    if (reason === null) {
        return;
    }

    const doSubmit = (lat = '', lng = '') => {
        const params = new URLSearchParams();
        params.set('order_id', String(orderId));
        params.set('status', 'failed_delivery');
        if (reason.trim()) {
            params.set('cancel_reason', reason.trim());
        }
        if (lat !== '' && lng !== '') {
            params.set('rider_latitude', String(lat));
            params.set('rider_longitude', String(lng));
        }

        fetch('<?= site_url('dashboard/riderUpdateDeliveryStatus') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Delivery cancelled.');
                reloadDeliveriesPage();
            } else {
                alert(data.message || 'Failed to cancel delivery');
            }
        })
        .catch(() => {
            alert('An error occurred while cancelling delivery');
        });
    };

    if (!confirm('Cancel this delivery?')) {
        return;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            doSubmit(position.coords.latitude, position.coords.longitude);
        }, () => {
            doSubmit();
        }, { enableHighAccuracy: true, timeout: 4000, maximumAge: 15000 });
    } else {
        doSubmit();
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

    startActiveDeliveriesLocationLoop();
    restoreRiderDeliveriesUiState();
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
                <div id="proofSubmitStatus" style="margin-top:.55rem;font-size:.85rem;color:#666;"></div>
            </form>
        </div>
    </div>
</div>

<div id="orderDetailsModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:760px;">
        <div class="modal-header">
            <h3>Order Details</h3>
            <span class="close" onclick="closeOrderDetailsModal()">&times;</span>
        </div>
        <div class="modal-body" id="orderDetailsBody"></div>
    </div>
</div>

<div id="routeMapModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:860px;">
        <div class="modal-header">
            <h3>Delivery Route</h3>
            <span class="close" onclick="closeRouteMap()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="routeMapLabel" style="font-weight:700;margin-bottom:.5rem;"></div>
            <div id="routeMapCanvas" style="height:360px;border:1px solid #e0e0e0;border-radius:8px;"></div>
            <div id="routeMapMeta" style="margin-top:.5rem;color:#555;"></div>
            <div id="routeMapDirections" style="margin-top:.6rem; max-height:180px; overflow:auto; border:1px solid #e0e0e0; border-radius:8px; padding:.55rem .65rem; font-size:.9rem; color:#333;"></div>
        </div>
    </div>
</div>

<div id="deliveryCancelPromptModal" class="modal" style="display:none;">
    <div class="modal-content delivery-action-modal">
        <div class="modal-header">
            <h3>Delivery Action</h3>
            <span class="close" onclick="closeDeliveryCancelPrompt()">&times;</span>
        </div>
        <div class="modal-body">
            <p id="deliveryCancelPromptMessage" style="margin-top:0;">Do you want to cancel this delivery?</p>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeDeliveryCancelPrompt()">Keep Order</button>
                <button type="button" class="btn-submit" id="deliveryCancelPromptConfirmBtn">Cancel Delivery</button>
            </div>
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

.delivery-action-modal {
    width: min(92vw, 760px);
    max-width: 760px;
}

.delivery-action-modal .modal-body {
    padding: 24px 24px 22px;
}

.delivery-action-modal #deliveryCancelPromptMessage {
    font-size: 1rem;
    line-height: 1.65;
    color: #2f2f2f;
}

.delivery-action-modal .form-actions {
    margin-top: 16px;
    gap: 12px;
}

.delivery-action-modal .btn-cancel,
.delivery-action-modal .btn-submit {
    min-width: 130px;
    padding: 11px 18px;
    font-size: 0.95rem;
}
</style>

<script>
function showDeliveryProofForm(orderId) {
    const form = document.getElementById('deliveryProofForm');
    form.reset();
    document.getElementById('proofOrderId').value = orderId;
    const statusHint = document.getElementById('proofSubmitStatus');
    if (statusHint) statusHint.textContent = '';
    document.getElementById('deliveryProofModal').style.display = 'block';
}

function closeDeliveryProofForm() {
    const statusHint = document.getElementById('proofSubmitStatus');
    if (statusHint) statusHint.textContent = '';
    document.getElementById('deliveryProofModal').style.display = 'none';
    if (typeof window.__triggerLiveReloadCheck === 'function') {
        window.__triggerLiveReloadCheck();
    }
}

// Handle form submission
document.getElementById('deliveryProofForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = document.getElementById('proofOrderId').value;
    const appendAndSubmit = () => {
        fetch('<?= site_url('dashboard/submitDeliveryProof') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeDeliveryProofForm();
                alert('Delivery proof submitted successfully!');
                reloadDeliveriesPage();
            } else {
                const message = data.message || 'Failed to submit delivery proof';
                alert(message);
                const lower = String(message).toLowerCase();
                if (lower.includes('too far from customer location')) {
                    const orderToCancel = parseInt(orderId || '0', 10);
                    if (orderToCancel > 0) {
                        openDeliveryCancelPrompt(
                            orderToCancel,
                            'You are still too far from the customer location. Do you want to cancel this delivery now?'
                        );
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting delivery proof');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Proof';
            }
            if (statusHint) {
                statusHint.textContent = '';
            }
        });
    };
    const submitButton = this.querySelector('.btn-submit');
    const statusHint = document.getElementById('proofSubmitStatus');

    if (!orderId) {
        alert('Order ID is missing. Please close and reopen the proof form.');
        return;
    }

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
    }
    if (statusHint) {
        statusHint.textContent = 'Uploading proof...';
    }
    
    appendAndSubmit();
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deliveryProofModal');
    const detailsModal = document.getElementById('orderDetailsModal');
    const cancelPromptModal = document.getElementById('deliveryCancelPromptModal');
    if (event.target == modal) {
        closeDeliveryProofForm();
    } else if (event.target == detailsModal) {
        closeOrderDetailsModal();
    } else if (event.target == cancelPromptModal) {
        closeDeliveryCancelPrompt();
    }
}

function openOrderDetailsModal(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const body = document.getElementById('orderDetailsBody');
    body.innerHTML = '<p>Loading details...</p>';
    modal.style.display = 'block';

    fetch(`<?= site_url('dashboard/order-details-json') ?>/${orderId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.order) {
            body.innerHTML = `<p>${data.message || 'Unable to load details.'}</p>`;
            return;
        }
        const o = data.order;
        const items = (o.items || []).map(item => `
            <div style="display:flex;justify-content:space-between;gap:1rem;padding:.55rem 0;border-bottom:1px solid #f0f0f0;">
                <div>${item.name}<div style="font-size:.85rem;color:#666;">Qty: ${item.qty}</div></div>
                <div><strong>₱${(item.unit_price * item.qty).toFixed(2)}</strong></div>
            </div>
        `).join('') || '<p>No items found.</p>';

        body.innerHTML = `
            <div style="display:grid;gap:.7rem;">
                <div><strong>Order:</strong> ${o.reference_number}</div>
                <div><strong>Customer:</strong> ${o.customer_name} ${o.customer_email ? `(${o.customer_email})` : ''}</div>
                <div><strong>Address:</strong> ${o.shipping_address || 'Not provided'}</div>
                <div><strong>Coordinates:</strong> ${o.delivery_latitude && o.delivery_longitude ? `${o.delivery_latitude}, ${o.delivery_longitude}` : 'Not set'}</div>
                <div><strong>Contact:</strong> ${o.contact_number || 'Not provided'}</div>
                <div><strong>Notes:</strong> ${o.shipment_notes || 'None'}</div>
                <div><strong>Status:</strong> ${String(o.delivery_status || '').replaceAll('_', ' ')}</div>
                <div style="margin-top:.35rem;"><strong>Items</strong></div>
                <div>${items}</div>
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = '<p>An error occurred while loading details.</p>';
    });
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
    if (typeof window.__triggerLiveReloadCheck === 'function') {
        window.__triggerLiveReloadCheck();
    }
}

let routeMap = null;
let routeLayer = null;
let riderLiveWatchId = null;
let routeGuideLine = null;
let activeRouteOrderId = null;

function reloadDeliveriesPage() {
    try {
        if (typeof window.__beforeLiveReload === 'function') {
            window.__beforeLiveReload();
        }
    } catch (e) {}
    location.reload();
}
function openRouteMap(orderId) {
    activeRouteOrderId = Number(orderId) || null;
    const modal = document.getElementById('routeMapModal');
    modal.style.display = 'block';
    fetch(`<?= site_url('dashboard/order-details-json') ?>/${orderId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (!data.success || !data.order) return;
        const o = data.order;
        const pickupPhase = ['ready_for_pickup', 'accepted_by_rider'].includes(String(o.delivery_status || ''));
        const targetLat = pickupPhase ? o.store_latitude : o.delivery_latitude;
        const targetLng = pickupPhase ? o.store_longitude : o.delivery_longitude;
        const label = pickupPhase ? 'Going to Pickup (Store)' : 'Out for Delivery (Customer)';
        document.getElementById('routeMapLabel').textContent = label;
        if (!targetLat || !targetLng) {
            document.getElementById('routeMapMeta').textContent = 'Destination coordinates are not set.';
            return;
        }
        if (!routeMap) {
            routeMap = L.map('routeMapCanvas').setView([targetLat, targetLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(routeMap);
        } else {
            routeMap.invalidateSize();
            routeMap.setView([targetLat, targetLng], 13);
        }
        if (routeLayer) {
            routeLayer.forEach((l) => routeMap.removeLayer(l));
        }
        if (routeGuideLine) {
            routeMap.removeLayer(routeGuideLine);
            routeGuideLine = null;
        }
        routeLayer = [];
        routeLayer.push(L.marker([targetLat, targetLng]).addTo(routeMap).bindPopup(pickupPhase ? 'Pickup Location' : 'Customer Location'));

        let riderMarker = null;
        const setRiderMarker = async (lat, lng, pushBackend = false) => {
            if (!riderMarker) {
                riderMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'rider-motor-icon',
                        html: '<div style="font-size:20px;line-height:20px;">🏍️</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(routeMap).bindPopup('Your current location');
                routeLayer.push(riderMarker);
            } else {
                riderMarker.setLatLng([lat, lng]);
            }

            const km = haversineKm(lat, lng, Number(targetLat), Number(targetLng));
            const eta = Math.max(2, Math.round((km / 25) * 60));
            document.getElementById('routeMapMeta').textContent = `Your distance to destination: ${km.toFixed(2)} km | ETA: ~${eta} min`;
            routeMap.fitBounds(L.latLngBounds([[lat, lng], [targetLat, targetLng]]).pad(0.18));
            await drawRoadGuide(lat, lng, Number(targetLat), Number(targetLng));

            if (pushBackend) {
                const body = `order_id=${orderId}&rider_latitude=${encodeURIComponent(lat)}&rider_longitude=${encodeURIComponent(lng)}`;
                fetch('<?= site_url('dashboard/updateRiderLocation') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                }).catch(() => {});
            }
        };

        if (o.rider_latitude && o.rider_longitude) {
            setRiderMarker(Number(o.rider_latitude), Number(o.rider_longitude), false);
        } else {
            document.getElementById('routeMapMeta').textContent = 'Getting your current rider location...';
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const rLat = pos.coords.latitude;
                const rLng = pos.coords.longitude;
                setRiderMarker(rLat, rLng, true);
            });

            if (riderLiveWatchId !== null) {
                navigator.geolocation.clearWatch(riderLiveWatchId);
            }
            riderLiveWatchId = navigator.geolocation.watchPosition((pos) => {
                setRiderMarker(pos.coords.latitude, pos.coords.longitude, true);
            }, () => {}, { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 });
        }
    }).catch(() => {});
}
function closeRouteMap() {
    document.getElementById('routeMapModal').style.display = 'none';
    activeRouteOrderId = null;
    if (riderLiveWatchId !== null && navigator.geolocation) {
        navigator.geolocation.clearWatch(riderLiveWatchId);
        riderLiveWatchId = null;
    }
    if (typeof window.__triggerLiveReloadCheck === 'function') {
        window.__triggerLiveReloadCheck();
    }
}

function persistRiderDeliveriesUiState() {
    const payload = {
        ts: Date.now(),
        routeMapOpen: document.getElementById('routeMapModal')?.style.display === 'block',
        routeOrderId: activeRouteOrderId
    };
    sessionStorage.setItem('rider_deliveries_ui_state', JSON.stringify(payload));
}

function restoreRiderDeliveriesUiState() {
    const raw = sessionStorage.getItem('rider_deliveries_ui_state');
    if (!raw) return;
    sessionStorage.removeItem('rider_deliveries_ui_state');
    try {
        const state = JSON.parse(raw);
        if (!state || !state.routeMapOpen || !state.routeOrderId) return;
        if (Date.now() - Number(state.ts || 0) > 25000) return;
        setTimeout(() => openRouteMap(Number(state.routeOrderId)), 500);
    } catch (e) {}
}

window.__beforeLiveReload = persistRiderDeliveriesUiState;
window.__suspendLiveReload = function() {
    const modals = ['deliveryProofModal', 'orderDetailsModal', 'routeMapModal', 'deliveryCancelPromptModal'];
    return modals.some((id) => {
        const el = document.getElementById(id);
        return el && el.style.display === 'block';
    });
};

function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatManeuver(step) {
    const m = step?.maneuver || {};
    const type = String(m.type || '').replaceAll('_', ' ').trim();
    const mod = String(m.modifier || '').replaceAll('_', ' ').trim();
    const name = String(step?.name || '').trim();
    const base = type ? type.charAt(0).toUpperCase() + type.slice(1) : 'Continue';
    if (mod && name) return `${base} ${mod} to ${name}`;
    if (name) return `${base} to ${name}`;
    if (mod) return `${base} ${mod}`;
    return base;
}

async function drawRoadGuide(fromLat, fromLng, toLat, toLng) {
    const panel = document.getElementById('routeMapDirections');
    if (panel) {
        panel.textContent = 'Loading road guide...';
    }

    try {
        const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&steps=true`;
        const res = await fetch(url);
        const data = await res.json();
        if (!data || data.code !== 'Ok' || !Array.isArray(data.routes) || data.routes.length === 0) {
            throw new Error('No route');
        }

        const route = data.routes[0];
        const coords = (route.geometry?.coordinates || []).map((c) => [c[1], c[0]]);
        if (routeGuideLine) {
            routeMap.removeLayer(routeGuideLine);
        }
        routeGuideLine = L.polyline(coords, { color: '#1976d2', weight: 5, opacity: 0.9 }).addTo(routeMap);

        const steps = (route.legs?.[0]?.steps || []).slice(0, 12);
        if (panel) {
            if (steps.length === 0) {
                panel.textContent = 'Road guide unavailable. Using direct line fallback.';
            } else {
                panel.innerHTML = steps.map((s, i) => `${i + 1}. ${formatManeuver(s)} (${Math.max(1, Math.round((s.distance || 0))) }m)`).join('<br>');
            }
        }
    } catch (e) {
        if (routeGuideLine) {
            routeMap.removeLayer(routeGuideLine);
            routeGuideLine = null;
        }
        routeGuideLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], { color: '#27c56f', dashArray: '8 8', weight: 4 }).addTo(routeMap);
        if (panel) {
            panel.textContent = 'Road routing service unavailable. Showing direct guide line.';
        }
    }
}
</script>

<?= $this->include('rider/partials/footer') ?>

