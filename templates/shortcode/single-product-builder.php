<div class="vi-wpb-wrapper">
	<?php $class = is_active_sidebar( 'woopb-sidebar' ) ? 'woopb-has-sidebar' : ''; ?>
    <div class="woopb-entry-content  <?php echo esc_attr( $class ) ?>">
		<?php
		do_action( 'woocommerce_product_builder_single_content', $id );
		?>
    </div>
</div>