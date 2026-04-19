<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin">🧹 Clean Old Menu Options</h4>
                <hr class="hr-panel-heading">
                
                <div class="alert alert-info">
                    <strong>ℹ️ Information:</strong> This will delete old JSON-based menu options from the database.
                    All menu data is now stored in database tables (<code>tblpoly_utilities_custom_menus</code>).
                </div>
                
                <?php if (!empty($results['deleted'])): ?>
                <div class="alert alert-success">
                    <strong> Deleted Options:</strong>
                    <ul>
                        <?php foreach ($results['deleted'] as $option): ?>
                        <li><code><?php echo e($option); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($results['not_found'])): ?>
                <div class="alert alert-warning">
                    <strong> Not Found (Already Deleted):</strong>
                    <ul>
                        <?php foreach ($results['not_found'] as $option): ?>
                        <li><code><?php echo e($option); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($results['errors'])): ?>
                <div class="alert alert-danger">
                    <strong> Errors:</strong>
                    <ul>
                        <?php foreach ($results['errors'] as $error): ?>
                        <li>
                            <code><?php echo e($error['option']); ?></code>: 
                            <?php echo e($error['error']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="tw-mt-4">
                    <a href="<?php echo admin_url('poly_utilities/custom_menu'); ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Custom Menu
                    </a>
                    
                    <button type="button" class="btn btn-primary" onclick="runCleanup()">
                        <i class="fa fa-refresh"></i> Run Cleanup Again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runCleanup() {
    if (confirm('Are you sure you want to delete old menu options?')) {
        $.ajax({
            url: admin_url + 'poly_utilities/clean_old_options/ajax_clean',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(' ' + response.message);
                    location.reload();
                } else {
                    alert(' Error: ' + response.message);
                }
            },
            error: function() {
                alert(' AJAX Error');
            }
        });
    }
}
</script>

