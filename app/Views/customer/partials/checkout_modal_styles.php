    /* Toast notification styles */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.processing {
        background: #00bcd4;
    }

    .toast.success {
        background: #27c56f;
    }

    .toast.error {
        background: #dc3545;
    }

    .toast-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .checkout-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1200;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .checkout-modal.show {
        display: flex;
    }

    .checkout-modal-card {
        width: 100%;
        max-width: 520px;
        max-height: 92vh;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 18px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .checkout-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.1rem 1.25rem .75rem;
        flex-shrink: 0;
    }

    .checkout-modal-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
    }

    .checkout-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #f1f5f9;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: #64748b;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .checkout-modal-close:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .checkout-summary-banner {
        margin: 0 1.25rem .9rem;
        padding: .9rem 1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #e0f7fa 0%, #f0fdfa 100%);
        border: 1px solid #b2ebf2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-shrink: 0;
    }

    .checkout-summary-label {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #0e7490;
        margin-bottom: .15rem;
    }

    .checkout-summary-meta {
        font-size: .82rem;
        color: #64748b;
    }

    .checkout-summary-amount {
        font-size: 1.35rem;
        font-weight: 800;
        color: #006064;
        white-space: nowrap;
    }

    .checkout-form-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 0 1.25rem .25rem;
        min-height: 0;
    }

    .checkout-form-footer {
        flex-shrink: 0;
        padding: .85rem 1.25rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #fff;
        box-shadow: 0 -6px 16px rgba(15, 23, 42, 0.06);
    }

    .checkout-place-btn {
        width: 100%;
        padding: .78rem 1rem;
        font-size: .95rem;
        font-weight: 700;
        border-radius: 10px;
    }

    .checkout-field {
        margin-bottom: 0.8rem;
    }

    .checkout-label {
        display: block;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
        color: #333333;
    }

    .checkout-input {
        width: 100%;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.7rem 0.85rem;
        font-size: 0.9rem;
        outline: none;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .checkout-input:focus {
        border-color: #00bcd4;
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.12);
    }

    textarea.checkout-input {
        resize: vertical;
        min-height: 72px;
    }

    .checkout-section-title {
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        margin: 1rem 0 .55rem;
    }

    .checkout-section-title:first-child {
        margin-top: 0;
    }

    .checkout-address-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .85rem;
        margin-bottom: .5rem;
        background: #f8fafc;
    }

    .checkout-address-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .65rem;
    }

    .checkout-address-grid .full {
        grid-column: 1 / -1;
    }

    .address-mode-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .35rem;
        background: #e2e8f0;
        padding: .3rem;
        border-radius: 10px;
        margin-top: .35rem;
    }

    .address-mode-tab {
        position: relative;
        cursor: pointer;
        margin: 0;
    }

    .address-mode-tab input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .address-mode-tab span {
        display: block;
        text-align: center;
        padding: .5rem .45rem;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 700;
        color: #64748b;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .address-mode-tab input:checked + span {
        background: #ffffff;
        color: #00838f;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
    }

    .address-mode-tab input:disabled + span {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .saved-address-card {
        margin-top: .65rem;
        padding: .75rem .85rem;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #cfe8ef;
    }

    .saved-address-label {
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #0e7490;
        margin-bottom: .35rem;
    }

    .saved-address-text {
        font-size: .88rem;
        color: #334155;
        line-height: 1.45;
        font-weight: 600;
    }

    .saved-address-note {
        margin-top: .45rem;
        font-size: .8rem;
        color: #64748b;
        line-height: 1.4;
    }

    .btn-location {
        border: 1px solid #27c56f;
        background: rgba(39, 197, 111, 0.1);
        color: #1d9f57;
        border-radius: 8px;
        padding: .6rem .8rem;
        font-weight: 700;
        cursor: pointer;
    }

    .location-status {
        color: #666666;
        font-size: .86rem;
        line-height: 1.4;
    }

    @media (max-width: 560px) {
        .checkout-address-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .gcash-box {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.8rem;
        color: #1e3a8a;
        font-size: 0.88rem;
        line-height: 1.4;
    }

    .gcash-qr-wrap {
        text-align: center;
        margin: 0.5rem 0 0.8rem;
    }

    .gcash-qr {
        width: 210px;
        height: 210px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #fff;
        object-fit: contain;
    }

    .btn-open-gcash {
        width: 100%;
        margin-top: 0.6rem;
        background: #0057d9;
        border-color: #0057d9;
        color: #ffffff;
        font-weight: 700;
    }

    .btn-open-gcash:hover {
        background: #0047b1;
        border-color: #0047b1;
    }
