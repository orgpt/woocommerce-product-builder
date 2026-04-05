<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
$button_icon = $button_icon ? 'woopb-icon' : '';
?>
<div class="woocommerce-product-builder-variation-add-to-cart variations_button">
    <div class="woocommerce-product-builder-before-add-to-cart">
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
    </div>
	<?php
	do_action( 'woocommerce_product_builder_quantity_field', $product, $post_id );

	do_action( 'litespeed_nonce', '_woopb_add_to_cart' );
	wp_nonce_field( '_woopb_add_to_cart', '_nonce', false );
	//single_add_to_cart_button
	?>
    <button type="submit" class="button alt woopb-add-to-list-btn woopb-button-deactivated <?php echo esc_attr( $button_icon ) ?>" disabled="disabled"><?php echo esc_html__( 'Select', 'woocommerce-product-builder' ) ?></button>
    <input type="hidden" name="woopb-add-to-cart" value="<?php echo absint( $product->get_id() ); ?>"/>
    <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>"/>
    <input type="hidden" name="variation_id" class="variation_id" value="0"/>
    <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
    <div class="woocommerce-product-builder-after-add-to-cart">
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    </div>
</div>
