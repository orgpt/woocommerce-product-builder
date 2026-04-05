<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( count( $products ) ) {
    global $post, $product, $first_product;
    if ( wp_doing_ajax() ) {
        $original_post_id = $id;
    } else {
        $original_post_id = is_woopb_shortcode() ? $id : $post->ID;
    }

    $first_product = current( $products );
    foreach ( $products as $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            continue;
        }
        $post = get_post( $product_id );

        ?>
        <div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'woopb-product', $product ); ?>>
            <?php
            do_action( 'woocommerce_product_builder_single_product_content', $original_post_id, $first_product );
            ?>
        </div>
    <?php }
    wp_reset_postdata();
} else {
    echo '<h2>' . esc_html__( 'Products are not found.', 'woocommerce-product-builder' ) . '</h2>';
    if ( $step_error ) {
        echo '<p>' . wp_kses_post( $step_error ) . '</p>';
    }
}
