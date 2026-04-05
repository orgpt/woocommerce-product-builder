<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

$short_description = apply_filters( 'woocommerce_short_description', $product->get_short_description() );

if ( ! $short_description ) {
	return;
}

?>
<div class="woopb-product-short-description">
	<?php echo $short_description;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
