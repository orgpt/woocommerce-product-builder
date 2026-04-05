<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WPRODUCTBUILDER_Widget_Price_Filter extends WC_Widget {
	var $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce-product-builder-widget widget_price_filter';
		$this->widget_description = __( 'Display a slider to filter products in builder page by price.', 'woocommerce-product-builder' );
		$this->widget_id          = 'woopb_price_filter';
		$this->widget_name        = __( 'WC Product Builder Filter Price', 'woocommerce-product-builder' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Filter by price', 'woocommerce-product-builder' ),
				'label' => __( 'Title', 'woocommerce-product-builder' ),
			),
		);
		$suffix                   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'wc-accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), WC_VERSION, false );
		wp_register_script( 'wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch' . $suffix . '.js', array( 'jquery-ui-slider' ), WC_VERSION, true );
		wp_register_script( 'wc-price-slider', WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js', array(
			'jquery-ui-slider',
			'wc-jquery-ui-touchpunch',
			'wc-accounting'
		), WC_VERSION, true );
		wp_localize_script( 'wc-price-slider', 'woocommerce_price_slider_params', array(
			'currency_format_num_decimals' => 0,
			'currency_format_symbol'       => get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
			'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
			'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array(
				'%s',
				'%v'
			), get_woocommerce_price_format() ) ),
		) );

		if ( is_customize_preview() ) {
			wp_enqueue_script( 'wc-price-slider' );
		}

		parent::__construct();
		$this->setting_data = new VI_WPRODUCTBUILDER_Data();

	}

	/**
	 * Output widget.
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @see WP_Widget
	 *
	 */
	public function widget( $args, $instance ) {
		global $wp;
//		if ( get_post_type() != 'woo_product_builder' || ! is_single() ) {
//			return;
//		}
		wp_enqueue_script( 'wc-price-slider' );

		// Find min and max price in current result set.
		$prices = $this->get_filtered_price();
		$min    = floor( $prices->min_price ?? 0 );
		$max    = ceil( $prices->max_price ?? 0 );
		if ( $min === $max ) {
			return;
		}

		$this->widget_start( $args, $instance );

		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array( 'page', 'ppaged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}

		$min_price  = !empty( $_REQUEST['min_price'] ) ? esc_attr( $_REQUEST['min_price'] ) : apply_filters( 'woocommerce_price_filter_widget_min_amount', $min );// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$max_price  = !empty( $_REQUEST['max_price'] ) ? esc_attr( $_REQUEST['max_price'] ) : apply_filters( 'woocommerce_price_filter_widget_max_amount', $max );// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$step_id    = get_query_var( 'step' );
		$clear_link = add_query_arg( array( 'step' => $step_id ), get_the_permalink() );

		echo '<form method="get" action="' . esc_url( $form_action ) . '" class="woopb-price-filter-form">
			<div class="price_slider_wrapper">
				<div class="price_slider" style="display:none;"></div>
				<div class="price_slider_amount">
					<input type="text" id="min_price" name="min_price" value="' . esc_attr( $min_price ) . '" data-min="' . esc_attr( apply_filters( 'woocommerce_price_filter_widget_min_amount', $min ) ) . '" placeholder="' . esc_attr__( 'Min price', 'woocommerce-product-builder' ) . '" />
					<input type="text" id="max_price" name="max_price" value="' . esc_attr( $max_price ) . '" data-max="' . esc_attr( apply_filters( 'woocommerce_price_filter_widget_max_amount', $max ) ) . '" placeholder="' . esc_attr__( 'Max price', 'woocommerce-product-builder' ) . '" />
					<button type="submit" class="woopb-button">' . esc_html__( 'Filter', 'woocommerce-product-builder' ) . '</button>
					<div class="price_label" style="display:none;">
						' . esc_html__( 'Price:', 'woocommerce-product-builder' ) . ' <span class="from"></span> &mdash; <span class="to"></span>
					</div>
					' . wc_query_string_form_fields( null, array( 'min_price', 'max_price' ), '', true ) .// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		            '<div class="clear"></div>
				</div>
			</div>
		</form>'; //<a href="' . esc_url( $clear_link ) . '" class="woopb-button">' . esc_html__( 'Clear', 'woocommerce-product-builder' ) . '</a>

		$this->widget_end( $args );
	}

	/**
	 * Get filtered min price for current products.
	 * @return int
	 */
	protected function get_filtered_price() {
		global $wpdb, $post;
		$post_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id : ( $post->ID ?? '' );

		if ( ! $post_id && isset( $_POST['post_id'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$post_id = absint( $_POST['post_id'] );// phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		$tax_query  = array();
		$meta_query = array();


		$meta_query = new WP_Meta_Query( $meta_query );
		$tax_query  = new WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$product_ids    = $this->setting_data->get_products( $post_id );

		$sql = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
		$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
					AND {$wpdb->posts}.ID IN ('" . implode( "','", $product_ids ) . "')
					AND {$wpdb->posts}.post_status = 'publish'
					AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
					AND price_meta.meta_value > '' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		return $wpdb->get_row( $sql );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

}