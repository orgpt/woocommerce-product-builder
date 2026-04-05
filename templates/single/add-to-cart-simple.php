<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	$ctw = apply_filters( 'accept_external_type', false );
	if ( ! $ctw ) {
		return;
	}
}

echo wc_get_stock_html( $product );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
global $viwpb_form_action;

$button_icon = $button_icon ? 'woopb-icon' : '';

if ( $product->is_in_stock() ) : ?>

    <form class="cart" action="<?php echo esc_url( $viwpb_form_action ?: @add_query_arg( array() ) ) ?>" method="post">
        <div class="woocommerce-product-builder-simple-add-to-cart">
            <div class="woocommerce-product-builder-before-add-to-cart">
	            <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
            </div>
			<?php
			do_action( 'litespeed_nonce', '_woopb_add_to_cart' );
			wp_nonce_field( '_woopb_add_to_cart', '_nonce' , false);
			do_action( 'woocommerce_product_builder_quantity_field', $product, $post_id );
			?>
            <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
            <button type="submit" name="woopb-add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="button alt woopb-add-to-list-btn <?php echo esc_attr( $button_icon ) ?>">
				<?php echo esc_html__( 'Select', 'woocommerce-product-builder' ) ?>
            </button>
            <div class="woocommerce-product-builder-after-add-to-cart">
		        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
            </div>
        </div>
    </form>

<?php endif;
//single_add_to_cart_button