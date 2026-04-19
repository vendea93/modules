<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php 
// Get cart count
$cart_count = flexacademy_get_cart_count();
?>

<!-- Fixed Cart Icon -->
<a href="<?php echo site_url('flexacademy/cart'); ?>" 
   class="flexacademy-fixed-cart-icon tw-fixed tw-bottom-6 tw-right-6 tw-z-50 tw-bg-primary tw-text-white tw-rounded-full tw-p-4 tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-300 tw-transform hover:tw-scale-110">
    
    <!-- Cart Icon -->
    <i class="fa fa-shopping-cart tw-text-xl"></i>
    
    <!-- Cart Count Badge -->
    <?php if ($cart_count > 0): ?>
        <span class="flexacademy-cart-count tw-absolute tw--top-1 tw--right-1 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-rounded-full tw-min-w-[20px] tw-h-5 tw-flex tw-items-center tw-justify-center tw-px-1">
            <?php echo $cart_count; ?>
        </span>
    <?php else: ?>
        <span class="flexacademy-cart-count tw-hidden tw-absolute tw--top-1 tw--right-1 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-rounded-full tw-min-w-[20px] tw-h-5 tw-flex tw-items-center tw-justify-center tw-px-1">
            0
        </span>
    <?php endif; ?>
</a>
