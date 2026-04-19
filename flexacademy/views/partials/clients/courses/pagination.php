<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Expected variables: $total_pages, $current_page, $current_category, $current_pricing, $current_search
if($total_pages > 1): 
?>
    <div class="flexacademy-pagination">
        <!-- First Page -->
        <a href="?page=1<?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $current_pricing ? '&pricing='.$current_pricing : ''; ?><?php echo $current_search ? '&search='.urlencode($current_search) : ''; ?>" 
           class="flexacademy-pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
            &lt;&lt;<?php echo _flexacademy_lang('first'); ?>
        </a>

        <!-- Previous Page -->
        <a href="?page=<?php echo max(1, $current_page - 1); ?><?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $current_pricing ? '&pricing='.$current_pricing : ''; ?><?php echo $current_search ? '&search='.urlencode($current_search) : ''; ?>" 
           class="flexacademy-pagination-link <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
            <?php echo _flexacademy_lang('prev'); ?>
        </a>

        <!-- Page Info -->
        <div class="flexacademy-pagination-info">
            <?php echo _flexacademy_lang('page'); ?> <strong><?php echo $current_page; ?></strong> <?php echo _flexacademy_lang('of'); ?> <strong><?php echo $total_pages; ?></strong>
        </div>

        <!-- Go to Page -->
        <div class="flexacademy-pagination-goto">
            <span class="flexacademy-pagination-label"><?php echo _flexacademy_lang('go-to-page'); ?>:</span>
            <input type="number" 
                   id="flexacademy-goto-page" 
                   min="1" 
                   max="<?php echo $total_pages; ?>" 
                   value="<?php echo $current_page; ?>"
                   class="flexacademy-pagination-input">
        </div>

        <!-- Next Page -->
        <a href="?page=<?php echo min($total_pages, $current_page + 1); ?><?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $current_pricing ? '&pricing='.$current_pricing : ''; ?><?php echo $current_search ? '&search='.urlencode($current_search) : ''; ?>" 
           class="flexacademy-pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
            <?php echo _flexacademy_lang('next'); ?>
        </a>

        <!-- Last Page -->
        <a href="?page=<?php echo $total_pages; ?><?php echo $current_category ? '&category='.$current_category : ''; ?><?php echo $current_pricing ? '&pricing='.$current_pricing : ''; ?><?php echo $current_search ? '&search='.urlencode($current_search) : ''; ?>" 
           class="flexacademy-pagination-link <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
            <?php echo _flexacademy_lang('last'); ?>&gt;&gt;
        </a>
    </div>
<?php endif; ?>

