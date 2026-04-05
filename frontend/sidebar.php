<?php

if (!defined('ABSPATH')) {
    exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Sidebar {
    protected $data;

    public function __construct() {
        /*Register sidebar*/
        add_action('woocommerce_product_builder_single_top', array($this, 'register_sidebar'), 20);
        add_action('woocommerce_product_builder_before_content', array($this, 'register_sidebar'), 10);
        add_action('widgets_init', array($this, 'widgets_init'), 11);
    }

    /**
     * Register sidebar
     */
    public function widgets_init() {
        /*Include widget class price filter*/
        if (is_file(VI_WPRODUCTBUILDER_INCLUDES . "class-widget-price-filter.php")) {
            require_once VI_WPRODUCTBUILDER_INCLUDES . "class-widget-price-filter.php";
        }
        /*Include widget class rate filter*/
        if (is_file(VI_WPRODUCTBUILDER_INCLUDES . "class-widget-rating-filter.php")) {
            require_once VI_WPRODUCTBUILDER_INCLUDES . "class-widget-rating-filter.php";
        }
        /*Include widget class attribute filter*/
        if (is_file(VI_WPRODUCTBUILDER_INCLUDES . "class-widget-layered-nav.php")) {
            require_once VI_WPRODUCTBUILDER_INCLUDES . "class-widget-layered-nav.php";
        }
        if (is_file(VI_WPRODUCTBUILDER_INCLUDES . "class-widget-name-filter.php")) {
            require_once VI_WPRODUCTBUILDER_INCLUDES . "class-widget-name-filter.php";
        }
        register_sidebar(array(
            'name' => __('WC Product Builder Sidebar', 'woocommerce-product-builder'),
            'id' => 'woopb-sidebar',
            'description' => __('Widgets in this area will be shown on product builder page.', 'woocommerce-product-builder'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h2 class="widget-title woocommerce-product-builder-title">',
            'after_title' => '</h2>',
        ));
        register_widget('VI_WPRODUCTBUILDER_Widget_Price_Filter');
        register_widget('VI_WPRODUCTBUILDER_Widget_Rating_Filter');
        register_widget('VI_WPRODUCTBUILDER_Widget_Layered_Nav');
        register_widget('VI_WPRODUCTBUILDER_Widget_Name_Filter');
    }

    /**
     * Sidebar area
     */
    public function register_sidebar() {
        if (is_active_sidebar('woopb-sidebar')) {
            $step_id = get_query_var('step');
            $clear_link = add_query_arg(array('step' => $step_id), get_the_permalink());
            $list_template_classic = ['classic', 'classic-layout-2'];
            $list_template_modern = ['modern', 'modern-layout-2','modern-layout-3'];
            ?>
            <div class="woocommerce-product-builder-sidebar">
                <?php
                dynamic_sidebar('woopb-sidebar');

                if (in_array(VI_WOOPBTEMPLATE_STYLE, $list_template_classic)) {
                    ?>
                    <a class="woopb-button woopb-clear-filter" href="<?php echo esc_attr($clear_link); ?>">
                        <?php esc_html_e('Clear filter', 'woocommerce-product-builder'); ?>
                    </a>
                    <?php
                }
                ?>
            </div>

            <?php
            if (in_array(VI_WOOPBTEMPLATE_STYLE, $list_template_modern)) {
                ?>
                <a class="woopb-button woopb-clear-filter" href="<?php echo esc_attr($clear_link); ?>">
                    <?php esc_html_e('Clear filter', 'woocommerce-product-builder'); ?>
                </a>
                <?php
            }
            ?>

        <?php }
    }
}