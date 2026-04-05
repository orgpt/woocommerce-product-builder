<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div id="woopb-wrapper" class="woopb-style-ajax-ver-2">
    <div id="woopb-sidebar">
        <div class="woopb-sidebar-panel">
            <?php do_action( 'woopb_sidebar', $id ); ?>
        </div>
    </div>
    <div id="woopb-wrap-main">
        <div class="woopb-main-top-title">
            <div class="woopb-main-title-item woopb-main-title-item-product"><?php esc_html_e('Product', 'woocommerce-product-builder'); ?></div>
            <div class="woopb-main-title-item woopb-main-title-item-quantity"><?php esc_html_e('Quantity', 'woocommerce-product-builder'); ?></div>
            <div class="woopb-main-title-item woopb-main-title-item-price"><?php esc_html_e('Price', 'woocommerce-product-builder'); ?></div>
            <div class="woopb-main-title-item woopb-main-title-item-action"><?php esc_html_e('Action', 'woocommerce-product-builder'); ?></div>
        </div>
        <div id="woopb-main">

        </div>
    </div>

</div>

<div id="woopb-modal">
    <div class="woopb-modal-inner">
        <div class="woopb-modal-left">
			<?php do_action( 'woopb_load_step_products_modal_left' ); ?>
        </div>
        <div class="woopb-modal-right">
			<?php do_action( 'woopb_load_step_products_modal_right' ); ?>

            <div class="woopb-modal-header">
				<?php if ( is_active_sidebar( 'woopb-sidebar' ) ) { ?>
                    <span class="woopb-mobile-filters-control"> </span>
				<?php } ?>

				<?php do_action( 'woopb_load_step_products_modal_right_header', $id ); ?>

            </div>

            <div class="woopb-modal-body">
				<?php do_action( 'woopb_load_step_products_modal_right_body' ); ?>
                <div class="woopb-modal-products">

                </div>
            </div>

            <div class="woopb-modal-footer">
                <div class="woopb-step-pagination"></div>
				<?php do_action( 'woopb_load_step_products_modal_right_footer' ); ?>
            </div>
        </div>
        <div class="woopb-close-modal">&times;</div>
    </div>
</div>