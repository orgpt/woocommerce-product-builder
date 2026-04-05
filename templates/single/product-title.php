<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $product;
?>
<div class="woopb-product-title">
	<?php if ( $remove_link ) {
		echo '<div>' . esc_html( $product->get_name() ) . '</div>';
	} else {
		?>
        <a target="_blank" href="<?php echo esc_url( $product->get_permalink() ) ?>">
			<?php echo esc_html( $product->get_name() ) ?>
        </a>
	<?php } ?>

</div>
