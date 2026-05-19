<?php
/**
 * @var array<string, mixed> $returnMeta
 */
$evidenceFiles = return_evidence_list($returnMeta ?? []);
if ($evidenceFiles === []) {
    return;
}
?>
<div class="return-evidence-gallery" style="display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.65rem;">
    <?php foreach ($evidenceFiles as $file): ?>
        <?php
            $filename = (string) ($file['filename'] ?? '');
            if ($filename === '') {
                continue;
            }
            $url = return_evidence_url($filename);
            $label = (string) ($file['original_name'] ?? $filename);
        ?>
        <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f9fafb;max-width:160px;">
            <?php if (return_evidence_is_video($filename)): ?>
                <video src="<?= esc($url, 'attr') ?>" controls style="width:160px;height:120px;object-fit:cover;display:block;background:#000;"></video>
            <?php else: ?>
                <a href="<?= esc($url) ?>" target="_blank" rel="noopener">
                    <img src="<?= esc($url, 'attr') ?>" alt="Return evidence" style="width:160px;height:120px;object-fit:cover;display:block;">
                </a>
            <?php endif; ?>
            <div style="padding:.35rem .45rem;font-size:.72rem;color:#6b7280;word-break:break-all;"><?= esc($label) ?></div>
        </div>
    <?php endforeach; ?>
</div>
