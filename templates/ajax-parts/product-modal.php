<?php defined( 'ABSPATH' ) || exit; ?>

<div class="woopb-modal-product product">
	<?php do_action( 'woopb_modal_single_product_content_left', $post_id, $step ); ?>

    <div class="woopb-product-right">
		<?php do_action( 'woopb_modal_single_product_content_right', $post_id, $step ); ?>
    </div>
</div>
