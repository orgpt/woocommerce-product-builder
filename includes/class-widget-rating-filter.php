<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WPRODUCTBUILDER_Widget_Rating_Filter extends WC_Widget {
	var $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce-product-builder-widget widget_rating_filter';
		$this->widget_description = __( 'Display a list of star ratings to filter products in your store.', 'woocommerce-product-builder' );
		$this->widget_id          = 'woopb_rating_filter';
		$this->widget_name        = __( 'WC Product Builder Rating Filter', 'woocommerce-product-builder' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Average rating', 'woocommerce-product-builder' ),
				'label' => __( 'Title', 'woocommerce-product-builder' ),
			),
		);
		parent::__construct();
		$this->setting_data = new VI_WPRODUCTBUILDER_Data();

	}

	/**
	 * Count products after other filters have occurred by adjusting the main query.
	 *
	 * @param int $rating
	 *
	 * @return int
	 */
	protected function get_filtered_product_count( $rating ) {
		global $wpdb, $woopb_post_id;
		$post_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id :  $woopb_post_id;
		if (!$post_id){
			global $post;
			$post_id = $post->ID ??'';
		}
		if ( ! $post_id ) {
			return '';
		}

		$tax_query   = array();
		$meta_query  = array();
		$product_ids = $this->setting_data->get_product_filters( $post_id, false );
		if ( $product_ids ) {
			$product_ids = $product_ids->posts;
		}

		// Unset current rating filter.
		foreach ( $tax_query as $key => $query ) {
			if ( ! empty( $query['rating_filter'] ) ) {
				unset( $tax_query[ $key ] );
				break;
			}
		}

		// Set new rating filter.
		$product_visibility_terms = wc_get_product_visibility_term_ids();

		$tax_query[] = array(
			'taxonomy'      => 'product_visibility',
			'field'         => 'term_taxonomy_id',
			'terms'         => $product_visibility_terms[ 'rated-' . $rating ],
			'operator'      => 'IN',
			'rating_filter' => true,
		);

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		if ( is_array( $product_ids ) && count( $product_ids ) ) {
			$sql .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $product_ids ) . ")";
		}

		return absint( $wpdb->get_var( $sql ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
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

		ob_start();

		$found         = false;
		$rating_filter = isset( $_GET['rating_filter'] ) ? array_filter( array_map( 'absint', explode( ',', $_GET['rating_filter'] ) ) ) : array();// phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->widget_start( $args, $instance );

		echo '<ul>';

		for ( $rating = 5; $rating >= 1; $rating -- ) {

			$count = $this->get_filtered_product_count( $rating );

			if ( empty( $count ) ) {
				continue;
			}
			$found = true;

			if ( in_array( $rating, $rating_filter ) ) {
				$link_ratings = implode( ',', array_diff( $rating_filter, array( $rating ) ) );
			} else {
				$link_ratings = implode( ',', array_merge( $rating_filter, array( $rating ) ) );
			}


			$class       = in_array( $rating, $rating_filter ) ? 'wc-layered-nav-rating chosen' : 'wc-layered-nav-rating';
			$link        = apply_filters( 'woocommerce_rating_filter_link', $link_ratings ? add_query_arg( 'rating_filter', $link_ratings ) : remove_query_arg( 'rating_filter' ) );
			$rating_html = wc_get_star_rating_html( $rating );
			$count_html  = esc_html( apply_filters( 'woocommerce_rating_filter_count', "({$count})", $count, $rating ) );

			printf( '<li class="%s"><a href="%s" class="woopb-filter-rating" data-rating="%s"><span class="star-rating">%s</span> <span class="woopb-count">%s</span></a></li>',
				esc_attr( $class ), esc_url( $link ), esc_attr( $link_ratings ), $rating_html, $count_html );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</ul>';

		$this->widget_end( $args );

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean();// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
