<?php
/**
 * @var object{id: $invoice int, hash:string}
 */
?>
<div class="btn-group">
    <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <?= _l('einvoice'); ?> <?= einvoice_module_get_sync_badge_html((int) $invoice->id); ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right">
        <li class="hidden-xs"><a target="_blank"
                href="<?= admin_url('einvoice/output/invoice/' . $invoice->id . '?output_type=view'); ?>"><?= _l('view'); ?></a>
        </li>
        <li><a
                href="<?= admin_url('einvoice/output/invoice/' . $invoice->id) ?>"><?= _l('download'); ?></a>
        </li>
        <li>
            <a href="<?= admin_url('einvoice/sync_invoice/' . $invoice->id); ?>">
                <?= _l('einvoice_ksef_sync_now'); ?>
            </a>
        </li>
    </ul>
</div>
