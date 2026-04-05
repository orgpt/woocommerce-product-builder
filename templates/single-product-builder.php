<?php

/**
 * Template Name: Woocommerce Product Builder
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!is_woopb_shortcode()) {
    get_header();
}
$settings = VI_WPRODUCTBUILDER_Data::instance();
$class_template = 'vi-wpb-' . $settings->get_param('template');
?>
    <div class="vi-wpb-wrapper <?php echo esc_attr($class_template); ?>">
        <article id="post-<?php the_ID(); ?>" <?php post_class('vi-wpb-single_container'); ?>>
            <?php $class = is_active_sidebar('woopb-sidebar') ? 'woopb-has-sidebar' : ''; ?>
            <?php
            if (!is_woopb_shortcode()) {
                ?>
                <header class="woopb-entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>
                <?php
            }
            ?>

            <div class="woopb-entry-content  <?php echo esc_attr($class) ?>">
                <?php do_action('woocommerce_product_builder_single_content', $id ?? ''); ?>
            </div>
        </article>
    </div>
<?php

if (!is_woopb_shortcode()) {
    get_footer();
}

