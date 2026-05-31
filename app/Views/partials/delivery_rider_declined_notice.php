<?php
    $shipmentNotes = (string) ($shipmentNotes ?? '');
    $deliveryStatus = (string) ($deliveryStatus ?? '');
    $compact = ! empty($compact);

    if (function_exists('delivery_show_rider_declined_notice') && ! delivery_show_rider_declined_notice($deliveryStatus, $shipmentNotes)) {
        return;
    }

    $declineMeta = function_exists('delivery_rider_decline_meta') ? delivery_rider_decline_meta($shipmentNotes) : null;
    if ($declineMeta === null) {
        return;
    }
    static $declinedNoticeStylesLoaded = false;
?>
<?php if (! $declinedNoticeStylesLoaded): $declinedNoticeStylesLoaded = true; ?>
<style>
    .delivery-declined-notice {
        margin-top: .5rem;
        padding: .65rem .75rem;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        color: #991b1b;
        font-size: .88rem;
        line-height: 1.45;
    }
    .delivery-declined-notice--compact {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .2rem .35rem;
        margin-top: .25rem;
        padding: .2rem .45rem;
        font-size: .7rem;
        line-height: 1.25;
        border-radius: 6px;
        max-width: 100%;
    }
    .delivery-declined-notice--compact .delivery-declined-notice__title {
        margin-bottom: 0;
        gap: .25rem;
        font-size: .7rem;
        white-space: nowrap;
    }
    .delivery-declined-notice--compact .delivery-declined-notice__title i {
        font-size: .65rem;
    }
    .delivery-declined-notice__title {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .delivery-declined-notice__row {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem .4rem;
    }
    .delivery-declined-notice__label {
        font-weight: 600;
    }
</style>
<?php endif; ?>
<div class="delivery-declined-notice<?= $compact ? ' delivery-declined-notice--compact' : '' ?>">
    <div class="delivery-declined-notice__title">
        <i class="fas fa-user-times" aria-hidden="true"></i>
        <span><?= $compact ? 'Rider declined' : 'Rider declined delivery' ?></span>
    </div>
    <?php if (! empty($declineMeta['reason'])): ?>
        <div class="delivery-declined-notice__row">
            <?php if (! $compact): ?><span class="delivery-declined-notice__label">Reason:</span><?php endif; ?>
            <span><?= esc($declineMeta['reason']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (! $compact): ?>
        <div class="delivery-declined-notice__row">
            <span class="delivery-declined-notice__label">Action:</span>
            <span>Assign another rider.</span>
        </div>
    <?php endif; ?>
</div>
