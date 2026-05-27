<style>
.order-details-shell {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1.25rem 2.5rem;
    display: grid;
    gap: 1rem;
    box-sizing: border-box;
}

.order-details-shell .orders-header {
    display: grid;
    gap: .35rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
}

.order-details-shell .back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: fit-content;
    color: #047857;
    text-decoration: none;
    margin-bottom: .35rem;
    font-weight: 700;
}

.order-details-shell .back-link:hover {
    color: #065f46;
}

.order-details-shell .orders-header h1 {
    color: #111827;
    font-size: 1.65rem;
    line-height: 1.2;
    margin: 0 0 .35rem;
    font-weight: 800;
}

.order-details-shell .orders-header p {
    color: #6b7280;
    margin: 0;
    font-size: .98rem;
}

.order-details-shell .order-detail-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    overflow: visible;
}

.order-details-shell .order-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    background: #ffffff;
}

.order-details-shell .order-info {
    min-width: 0;
}

.order-details-shell .order-info h2 {
    font-size: 1.45rem;
    font-weight: 800;
    color: #047857;
    margin: 0 0 .5rem;
    overflow-wrap: anywhere;
}

.order-details-shell .order-info p,
.order-details-shell .item-details,
.order-details-shell .stage-description {
    color: #6b7280;
    margin: 0 0 1rem;
}

.order-details-shell .order-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
}

.order-details-shell .status-to-pay,
.order-details-shell .status-to_pay {
    background: #fef3c7;
    color: #92400e;
}

.order-details-shell .status-to-ship,
.order-details-shell .status-to_ship,
.order-details-shell .status-ready-for-pickup,
.order-details-shell .status-ready_for_pickup,
.order-details-shell .status-accepted-by-rider,
.order-details-shell .status-accepted_by_rider,
.order-details-shell .status-delivered-to-rider,
.order-details-shell .status-delivered_to_rider {
    background: #dbeafe;
    color: #1d4ed8;
}

.order-details-shell .status-to-receive,
.order-details-shell .status-to_receive,
.order-details-shell .status-delivered {
    background: #e0f2fe;
    color: #0369a1;
}

.order-details-shell .status-completed {
    background: #dcfce7;
    color: #047857;
}

.order-details-shell .status-cancelled,
.order-details-shell .status-failed-delivery,
.order-details-shell .status-failed_delivery {
    background: #fee2e2;
    color: #b91c1c;
}

.order-details-shell .order-total {
    text-align: right;
    min-width: 210px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1rem;
    align-self: flex-start;
}

.order-details-shell .order-total h3 {
    font-size: .9rem;
    color: #6b7280;
    margin: 0 0 .5rem;
    font-weight: 700;
}

.order-details-shell .total-amount {
    font-size: 1.7rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
}

.order-details-shell .tracking-info,
.order-details-shell .shipping-info,
.order-details-shell .delivery-proof-section,
.order-details-shell .delivery-tracker,
.order-details-shell .order-items,
.order-details-shell .order-summary,
.order-details-shell .order-details-actions {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: #ffffff;
}

.order-details-shell .tracking-info h3,
.order-details-shell .shipping-info h3,
.order-details-shell .delivery-proof-section h3,
.order-details-shell .delivery-tracker h3,
.order-details-shell .order-items h3,
.order-details-shell .order-summary h3,
.order-details-shell .order-details-actions h3 {
    font-size: 1rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-details-shell .tracker-progress {
    overflow-x: auto;
    padding-bottom: .25rem;
}

.order-details-shell .tracker-container {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
    min-width: 680px;
    margin: .5rem 0 0;
}

.order-details-shell .tracker-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 110px;
}

.order-details-shell .tracker-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin-bottom: .4rem;
    font-size: .82rem;
}

.order-details-shell .tracker-step.completed .tracker-icon {
    background: #dcfce7;
    color: #047857;
}

.order-details-shell .tracker-line {
    flex: 1;
    height: 3px;
    background: #e5e7eb;
    margin: 17px .25rem 0;
    min-width: 46px;
}

.order-details-shell .tracker-line.completed {
    background: #22c55e;
}

.order-details-shell .check-mark {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 16px;
    height: 16px;
    background: #22c55e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .58rem;
}

.order-details-shell .tracker-label {
    text-align: center;
}

.order-details-shell .stage-name {
    display: block;
    font-weight: 800;
    color: #111827;
    font-size: .82rem;
}

.order-details-shell .stage-description {
    display: block;
    font-size: .74rem;
    line-height: 1.3;
    margin-top: .25rem;
}

.order-details-shell .order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eef2f7;
}

.order-details-shell .order-item:last-child {
    border-bottom: none;
}

.order-details-shell .item-name,
.order-details-shell .item-price {
    color: #111827;
    font-weight: 800;
}

.order-details-shell .summary-row {
    display: flex;
    justify-content: space-between;
    padding: .5rem 0;
    color: #374151;
}

.order-details-shell .summary-row.total {
    border-top: 1px solid #d1d5db;
    font-weight: 800;
    font-size: 1.1rem;
    padding-top: 1rem;
    margin-top: .5rem;
    color: #111827;
}

.order-details-shell .order-details-actions {
    background: #f9fafb;
    border-bottom: 0;
}

.order-details-shell .btn-checkout,
.order-details-shell .btn-action {
    background: #27c56f;
    color: #fff;
    border: none;
    padding: .75rem 1.5rem;
    border-radius: 9px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s ease;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    margin-right: .65rem;
    margin-bottom: .5rem;
    text-decoration: none;
}

.order-details-shell .btn-checkout:hover,
.order-details-shell .btn-action:hover {
    background: #16a34a;
    box-shadow: 0 8px 18px rgba(22, 163, 74, .18);
}

.order-details-shell .btn-action-secondary {
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
}

.order-details-shell .btn-action-secondary:hover {
    background: #f3f4f6;
    box-shadow: none;
}

.order-details-shell .btn-action-danger {
    background: #dc2626;
}

.order-details-shell .btn-action-danger:hover {
    background: #b91c1c;
}

.order-details-shell .completed-notice {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .75rem 1.5rem;
    background: #dcfce7;
    color: #047857;
    border-radius: 8px;
    font-weight: 700;
}

.order-details-shell #order_details_map,
.order-details-shell #customer_tracking_map,
.order-details-shell #rider_delivery_map {
    height: 220px;
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    position: relative;
    z-index: 1;
}

.order-details-shell #rider_delivery_map {
    height: 320px;
    border-radius: 12px;
    box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .08);
}

.order-details-shell .rider-contact-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem .65rem;
    margin: .35rem 0 0;
}

.order-details-shell .rider-contact-value {
    font-weight: 700;
    color: #111827;
    font-size: 1.02rem;
    user-select: all;
    cursor: pointer;
}

.order-details-shell .rider-copy-contact-btn {
    border: 1px solid #99f6e4;
    background: #ecfdf5;
    color: #0f766e;
    border-radius: 999px;
    padding: .3rem .75rem;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}

.order-details-shell .rider-copy-contact-btn:hover {
    background: #d1fae5;
}

.order-details-shell .map-meta {
    display: grid;
    gap: .35rem;
    margin-top: .75rem;
    color: #4b5563;
    font-size: .88rem;
}

.order-details-shell .delivery-proof-grid {
    display: grid;
    grid-template-columns: minmax(220px, 360px) 1fr;
    gap: 1rem;
    align-items: start;
}

.order-details-shell .delivery-proof-image-link {
    display: block;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    overflow: hidden;
}

.order-details-shell .delivery-proof-image-link img {
    display: block;
    width: 100%;
    max-height: 320px;
    object-fit: contain;
}

.order-details-shell .btn-proof-open {
    width: fit-content;
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .62rem .9rem;
    border-radius: 8px;
    background: #111827;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: .88rem;
}

.order-details-shell .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

@media (max-width: 760px) {
    .order-details-shell .order-header {
        flex-direction: column;
    }

    .order-details-shell .order-total {
        width: 100%;
        text-align: left;
    }

    .order-details-shell .tracker-container {
        flex-direction: column;
        min-width: 0;
    }

    .order-details-shell .tracker-step {
        flex-direction: row;
        align-items: flex-start;
        gap: .75rem;
        width: 100%;
    }

    .order-details-shell .tracker-line {
        display: none;
    }

    .order-details-shell .delivery-proof-grid {
        grid-template-columns: 1fr;
    }
}
</style>
