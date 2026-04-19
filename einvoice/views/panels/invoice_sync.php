<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $record = einvoice_module_get_sync_record((int) $invoice->id); ?>
<div class="mtop10">
    <p class="no-mbot">
        <span class="bold"><?= _l('einvoice_ksef_sync_label'); ?>:</span>
        <?= einvoice_module_get_sync_badge_html((int) $invoice->id); ?>
    </p>
    <?php if (! empty($record['external_reference'])) { ?>
    <p class="no-mbot">
        <span class="bold"><?= _l('einvoice_ksef_reference'); ?>:</span>
        <?= e($record['external_reference']); ?>
    </p>
    <?php } ?>
    <?php if (! empty($record['synced_at'])) { ?>
    <p class="no-mbot">
        <span class="bold"><?= _l('einvoice_ksef_last_sync'); ?>:</span>
        <?= e(_dt($record['synced_at'])); ?>
    </p>
    <?php } ?>
    <?php if (! empty($record['last_error']) && $record['sync_status'] === 'error') { ?>
    <p class="no-mbot text-danger">
        <?= e($record['last_error']); ?>
    </p>
    <?php } ?>
</div>
