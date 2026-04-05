<?php
/**
 * Layered nav widget
 *
 * @package WooCommerce/Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WPRODUCTBUILDER_Widget_Layered_Nav extends WC_Widget {
	protected $product_ids;
	protected $taxonomy;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce-product-builder-widget widget_layered_nav woopb-widget-layered-nav';
		$this->widget_description = __( 'Display a list of attributes to filter products in your store.', 'woocommerce-product-builder' );
		$this->widget_id          = 'woopb_layered_nav';
		$this->widget_name        = __( 'WC Product Builder Attribute Filter', 'woocommerce-product-builder' );
		parent::__construct();
		$this->setting_data = new VI_WPRODUCTBUILDER_Data();
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 *
	 * @return array
	 * @see WP_Widget->update
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$this->init_settings();

		return parent::update( $new_instance, $old_instance );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Instance.
	 *
	 * @see WP_Widget->form
	 *
	 */
	public function form( $instance ) {
		$this->init_settings();
		parent::form( $instance );
	}

	/**
	 * Init settings after post types are registered.
	 */
	public function init_settings() {
		$attribute_array      = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( ! empty( $attribute_taxonomies ) ) {
			foreach ( $attribute_taxonomies as $tax ) {
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
					$attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
				}
			}
		}

		$this->settings = array(
			'title'     => array(
				'type'  => 'text',
				'std'   => __( 'Filter by', 'woocommerce-product-builder' ),
				'label' => __( 'Title', 'woocommerce-product-builder' ),
			),
			'attribute' => array(
				'type'    => 'select',
				'std'     => '',
				'label'   => __( 'Attribute', 'woocommerce-product-builder' ),
				'options' => $attribute_array,
			)
		);
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
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
		$product_ids = $this->setting_data->get_product_filters( $post_id, false ,'',false);
		if ( ! $product_ids ) {
			return;
		}

		$this->product_ids = $product_ids->posts;
		if ( count( $this->product_ids ) < 1 ) {
			return;
		}

		$_chosen_attributes = $this->setting_data->get_layered_nav_chosen_attributes();
		$taxonomy   = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : ($this->settings['attribute']['std'] ??'');
		$query_type = 'and';
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$get_terms_args = array( 'hide_empty' => '1' );

		$orderby = wc_attribute_orderby( $taxonomy );

		switch ( $orderby ) {
			case 'name' :
				$get_terms_args['orderby']    = 'name';
				$get_terms_args['menu_order'] = false;
				break;
			case 'id' :
				$get_terms_args['orderby']    = 'id';
				$get_terms_args['order']      = 'ASC';
				$get_terms_args['menu_order'] = false;
				break;
			case 'menu_order' :
				$get_terms_args['menu_order'] = 'ASC';
				break;
		}

		$terms = get_terms( $taxonomy, $get_terms_args );// phpcs:ignore WordPress.WP.DeprecatedParameters.Get_termsParam2Found

		if ( 0 === count( $terms ) ) {
			return;
		}

		switch ( $orderby ) {
			case 'name_num' :
				usort( $terms, '_wc_get_product_terms_name_num_usort_callback' );
				break;
			case 'parent' :
				usort( $terms, '_wc_get_product_terms_parent_usort_callback' );
				break;
		}

		ob_start();

		$this->widget_start( $args, $instance );

		$found = $this->layered_nav_list( $terms, $taxonomy, $query_type );

		$this->widget_end( $args );

		// Force found when option is selected - do not force found on taxonomy attributes.
		if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
			$found = true;
		}

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean(); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Return the currently viewed taxonomy name.
	 *
	 * @return string
	 */
	protected function get_current_taxonomy() {
		return is_tax() ? get_queried_object()->taxonomy : '';
	}

	/**
	 * Return the currently viewed term ID.
	 *
	 * @return int
	 */
	protected function get_current_term_id() {
		return absint( is_tax() ? get_queried_object()->term_id : 0 );
	}

	/**
	 * Return the currently viewed term slug.
	 *
	 * @return int
	 */
	protected function get_current_term_slug() {
		return absint( is_tax() ? get_queried_object()->slug : 0 );
	}

	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param array $term_ids Term IDs.
	 * @param string $taxonomy Taxonomy.
	 * @param string $query_type Query Type.
	 *
	 * @return array
	 */
	protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		global $wpdb;

		$tax_query  = array();
		$meta_query = array();

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = array();
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'" . $tax_query_sql['where'] . $meta_query_sql['where'] . 'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';


		/*Filter in list products */
		if ( count( $this->product_ids ) ) {
			$query['where'] .= " AND {$wpdb->posts}.ID IN (" . implode( ',', $this->product_ids ) . ")";
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
		$query             = implode( ' ', $query );
		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query );
		delete_transient( 'wc_layered_nav_counts' );
		$cached_counts = (array) get_transient( 'wc_layered_nav_counts' );
		if ( ! isset( $cached_counts[ $query_hash ] ) ) {

			$results = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine

			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			//			set_transient( 'wc_layered_nav_counts', $cached_counts, DAY_IN_SECONDS );
		}

		//die;
		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}

	/**
	 * Show list based layered nav.
	 *
	 * @param array $terms Terms.
	 * @param string $taxonomy Taxonomy.
	 * @param string $query_type Query Type.
	 *
	 * @return bool   Will nav display?
	 */
	protected function layered_nav_list( $terms, $taxonomy, $query_type ) {
		// List display.
		echo '<ul class="woocommerce-widget-layered-nav-list">';

		$term_counts = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );

		$chosen_attributes = $this->setting_data->get_layered_nav_chosen_attributes();

		$found       = false;
		$filter_args = array();
		foreach ( $terms as $term ) {
			$current_values = isset( $chosen_attributes[ $taxonomy ]['terms'] ) ? $chosen_attributes[ $taxonomy ]['terms'] : array();
			$option_is_set  = in_array( $term->slug, $current_values );
			$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

			// Skip the term for the current archive.
			if ( $this->get_current_term_id() === $term->term_id ) {
				continue;
			}

			// Only show options with count > 0.
			if ( 0 < $count ) {
				$found = true;
			} elseif ( 0 === $count && ! $option_is_set ) {
				continue;
			}

			//			$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
			$filter_name    = 'filter_' . str_replace( 'pa_', '', $taxonomy );
			$current_filter = isset( $_REQUEST[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_REQUEST[ $filter_name ] ) ) ) : array();// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_filter = array_map( 'sanitize_title', $current_filter );
			if ( ! in_array( $term->slug, $current_filter ) ) {
				$current_filter[] = $term->slug;
			}
			$filter_args[] = $filter_name;
			$filter_args[] = 'ppaged';

			$link = remove_query_arg( $filter_args );
			// Add current filters to URL.
			foreach ( $current_filter as $key => $value ) {
				// Exclude query arg for current term archive term.
				if ( $value === $this->get_current_term_slug() ) {
					unset( $current_filter[ $key ] );
				}

				// Exclude self so filter can be unset on click.
				if ( $option_is_set && $value === $term->slug ) {
					unset( $current_filter[ $key ] );
				}
			}

			if ( ! empty( $current_filter ) ) {
				asort( $current_filter );
				$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

				// Add Query type Arg to URL.
				if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
					$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
				}
				$link = str_replace( '%2C', ',', $link );
			}

			if ( $count > 0 || $option_is_set ) {
				$link      = esc_url( apply_filters( 'woocommerce_layered_nav_link', $link, $term, $taxonomy ) );
				$term_html = sprintf( "<a rel='nofollow' class='woopb-filter-attributes' href='%s' data-attr_name='%s' data-attr_value='%s'>%s</a>",
					esc_url( $link ), esc_attr( $filter_name ), esc_attr( implode( ',', $current_filter ) ), esc_html( $term->name ) );
			} else {
				$link      = false;
				$term_html = '<span>' . esc_html( $term->name ) . '</span>';
			}

			$term_html .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );
			$term_html = apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );
			if (($count > 0 || $option_is_set) && !strpos($term_html,'woopb-filter-attributes') ){
				if (!strpos($term_html,'class=')){
					$term_html = str_replace('href=', "class='woopb-filter-attributes' href=", $term_html);
				}else{
					$term_html = str_replace(array("class='",'class="'), array("class='woopb-filter-attributes ",'class="woopb-filter-attributes '), $term_html);
				}
			}
			if (($count > 0 || $option_is_set) && !strpos($term_html,'data-attr_name=')){
				$term_html = str_replace('href=', sprintf("data-attr_name='%s' href=",esc_attr( $filter_name )), $term_html);
			}
			if (($count > 0 || $option_is_set) && !strpos($term_html,'data-attr_value=')){
				$term_html = str_replace('href=', sprintf("data-attr_value='%s' href=",esc_attr( implode( ',', $current_filter ) )), $term_html);
			}
			echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term ' . ( $option_is_set ? 'woocommerce-widget-layered-nav-list__item--chosen chosen' : '' ) . '">';
			echo wp_kses_post( $term_html );
			echo '</li>';
		}

		echo '</ul>';

		return $found;
	}


}
