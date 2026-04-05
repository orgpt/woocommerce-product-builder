<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WPRODUCTBUILDER_Widget_Name_Filter extends WC_Widget {
	var $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce-product-builder-widget widget_name_filter';
		$this->widget_description = __( 'Display a search box.', 'woocommerce-product-builder' );
		$this->widget_id          = 'woopb_name_filter';
		$this->widget_name        = __( 'WC Product Builder Name Filter', 'woocommerce-product-builder' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Search product', 'woocommerce-product-builder' ),
				'label' => __( 'Title', 'woocommerce-product-builder' ),
			),
		);
		parent::__construct();
		$this->setting_data = new VI_WPRODUCTBUILDER_Data();
//		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * widget function.
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @see WP_Widget
	 *
	 */
	public function widget( $args, $instance ) {
		global $woopb_post_id;
		$post_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id :  $woopb_post_id;
		if (!$post_id){
			global $post;
            $post_id = $post->ID ??'';
        }
        wp_enqueue_script( 'woocommerce-product-builder-widget-select2', VI_WPRODUCTBUILDER_JS . 'select2.js', [], VI_WPRODUCTBUILDER_VERSION, false );
//		wp_enqueue_script( 'woocommerce-product-builder-widget-search-product', VI_WPRODUCTBUILDER_JS . 'search-product.js', array( 'jquery' ), true, true );
		wp_enqueue_style( 'woocommerce-product-builder-widget-select2', VI_WPRODUCTBUILDER_CSS . 'select2.min.css', [], VI_WPRODUCTBUILDER_VERSION );
		ob_start();

		$rating_filter = isset( $_GET['name_filter'] ) ? sanitize_text_field( $_GET['name_filter'] ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->widget_start( $args, $instance );
		?>
        <div class="woopb-product-filter">
            <form method="get" action="<?php echo esc_url( get_the_permalink() ) ?>">

                <select name="name_filter" class="woopb-search-product-widget">
                    <option><?php esc_html_e( 'Search product', 'woocommerce-product-builder' ); ?></option>
					<?php
					$pids = $this->setting_data->get_products( $post_id );
					if ( is_array( $pids ) && count( $pids ) ) {
						foreach ( $pids as $pid ) {
							$product = wc_get_product( $pid );
							if ( ! $product ) {
								continue;
							}
							printf( "<option>%s</option>", esc_html( $product->get_name() ) );
						}
					}
					?>
                </select>
				<?php echo wc_query_string_form_fields( null, array( 'name_filter' ), '', true );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </form>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.woopb-search-product-widget').select2({
                    placeholder: 'Select a state',
                })
                $('.woopb-search-product-widget').on('change', function () {
                    $(this).closest('form').submit()
                })
            })
        </script>
		<?php

		$this->widget_end( $args );

		echo ob_get_clean();// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}

}
