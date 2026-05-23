<?php
    $shipmentNotes = (string) ($shipmentNotes ?? '');
    $compact = ! empty($compact);
    $rescheduleMeta = function_exists('delivery_reschedule_meta') ? delivery_reschedule_meta($shipmentNotes) : null;
    if ($rescheduleMeta === null) {
        return;
    }
    $rescheduleDateLabel = delivery_reschedule_scheduled_date_label($shipmentNotes);
    static $rescheduleNoticeStylesLoaded = false;
?>
<?php if (! $rescheduleNoticeStylesLoaded): $rescheduleNoticeStylesLoaded = true; ?>
<style>
    .delivery-reschedule-notice {
        margin-top: .5rem;
        padding: .65rem .75rem;
        background: #fff7ed;
        border: 1px solid #fdba74;
        border-radius: 8px;
        color: #9a3412;
        font-size: .88rem;
        line-height: 1.45;
    }
    .delivery-reschedule-notice--compact {
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
    .delivery-reschedule-notice--compact .delivery-reschedule-notice__title {
        margin-bottom: 0;
        gap: .25rem;
        font-size: .7rem;
        white-space: nowrap;
    }
    .delivery-reschedule-notice--compact .delivery-reschedule-notice__title i {
        font-size: .65rem;
    }
    .delivery-reschedule-notice--compact .delivery-reschedule-notice__title span {
        font-weight: 700;
    }
    .delivery-reschedule-notice--compact .delivery-reschedule-notice__row {
        display: inline;
        gap: 0;
    }
    .delivery-reschedule-notice--compact .delivery-reschedule-notice__label {
        font-weight: 600;
    }
    .delivery-reschedule-notice__title {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .delivery-reschedule-notice__row {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem .4rem;
    }
    .delivery-reschedule-notice__label {
        font-weight: 600;
    }
</style>
<?php endif; ?>
<div class="delivery-reschedule-notice<?= $compact ? ' delivery-reschedule-notice--compact' : '' ?>">
    <div class="delivery-reschedule-notice__title">
        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
        <span><?= $compact ? 'Rescheduled' : 'Rescheduled delivery' ?></span>
    </div>
    <?php if ($rescheduleDateLabel): ?>
        <div class="delivery-reschedule-notice__row">
            <?php if (! $compact): ?><span class="delivery-reschedule-notice__label">New date:</span><?php endif; ?>
            <strong><?= esc($rescheduleDateLabel) ?></strong>
        </div>
    <?php endif; ?>
    <?php if (! empty($rescheduleMeta['reason'])): ?>
        <div class="delivery-reschedule-notice__row">
            <?php if (! $compact): ?><span class="delivery-reschedule-notice__label">Reason:</span><?php endif; ?>
            <span><?= esc($rescheduleMeta['reason']) ?></span>
        </div>
    <?php endif; ?>
</div>
