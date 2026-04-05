<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Search {
	public $settings;

	public function __construct() {
		add_action( 'wp_ajax_woopb_search_product_in_step', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_nopriv_woopb_search_product_in_step', array( $this, 'search_products' ) );
	}

	public function search_products() {
        check_ajax_referer('woopb_nonce', 'woopb_nonce');

		$this->settings = new VI_WPRODUCTBUILDER_Data();
		$post_id        = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : '';
		$step           = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : '';
		$search         = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$sort_by        = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
		$form_action    = isset( $_POST['form_action'] ) ? esc_url_raw( wp_unslash( $_POST['form_action'] ) ) : '';
		$paged          = ! empty( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		if ( $post_id && $step ) {
			$post_per_page    = $this->settings->get_data( $post_id, 'product_per_page', 10 );
			$step_error_descs = $this->settings->get_data( $post_id, 'step_error_desc' );
			$step_error       = $step_error_descs[ $step - 1 ] ?? '';

			//			$product_ids = $this->get_products( $post_id, $step );
			$filtered    = $this->settings->get_product_filters( $post_id, $step, '', false );
			$product_ids = $filtered->posts;

			$arg = array(
				'limit'    => $post_per_page,
				'status'   => 'publish',
				'include'  => $product_ids,
				'paged'    => $paged,
				's'        => $search,
				'paginate' => true,
			);
			switch ( $sort_by ) {

				case 'title_za':
					$arg['orderby'] = 'title';
					$arg['order']   = 'DESC';
					break;

				case 'price_low':
					$arg['orderby']  = 'meta_value_num';
					$arg['order']    = 'ASC';
					$arg['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;

				case 'price_high':
					$arg['orderby']  = 'meta_value_num';
					$arg['order']    = 'DESC';
					$arg['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;
				case 'latest':
					$arg['orderby'] = 'ID';
					$arg['order']   = 'DESC';
					break;
				case 'title_az':
                default:
					$arg['orderby'] = 'title';
					$arg['order']   = 'ASC';
			}

			$products_query = wc_get_products( $arg );
			$products       = $products_query->products;
			$max_page       = $products_query->max_num_pages;

			global $viwpb_form_action;
			$viwpb_form_action = $form_action;

			ob_start();
			wpb_get_template( 'product-template.php', array( 'id' => $post_id, 'products' => $products, 'step_error' => $step_error ) );
			$out = ob_get_clean();

			ob_start();
			$this->get_pagination( $paged, $step, $max_page );
			$pagination = ob_get_clean();

			wp_send_json_success( [ 'products' => $out, 'pagination' => $pagination ] );
		}

		wp_die();
	}

	public function get_products( $post_id, $step_id ) {
		/*Get current step*/
		$items = $this->settings->get_data( $post_id, 'list_content', array() );
		if ( $step_id > count( $items ) ) {
			$step_id = count( $items ) - 1;
		}
		$item_data = isset( $items[ $step_id - 1 ] ) ? $items[ $step_id - 1 ] : array();
		$terms     = $product_ids = $product_ids_of_term = array();

		foreach ( $item_data as $item ) {
			if ( strpos( trim( $item ), 'cate_' ) === false ) {
				$product_ids[] = $item;
			} else {
				$terms[] = str_replace( 'cate_', '', trim( $item ) );
			}
		}

		$args = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'tax_query'      => array(// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $terms,
					'operator' => 'IN'
				),
			),

			'fields' => 'ids'
		);

		$out_of_stock = $this->settings->get_data( $post_id, 'out_of_stock_product' );

		if ( ! $out_of_stock ) {
			$args['meta_query'][] = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => '!=',
			);
		}

		$zero_price_product = $this->settings->get_data( $post_id, 'zero_price_product' );
		if ( $zero_price_product ) {
			$args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC'
			);
		}

		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$product_ids_of_term = $the_query->posts;
		}
		wp_reset_postdata();
		$product_ids = array_unique( array_merge( $product_ids, $product_ids_of_term ) );

		return $product_ids;
	}

	public function get_pagination( $paged, $step, $max_page ) {

		if ( $paged > 2 ) {
			$i = 1;
			?>
            <div class="woopb-page" data-page_id="<?php echo esc_attr( $i ) ?>">
                <span><?php echo esc_html( $i ) ?></span>
            </div>
			<?php
			if ( $paged - 2 > 1 ) {
				?>
                <div class="woopb-page">
                    <span>...</span>
                </div>
				<?php
			}
		}

		if ( $paged - 1 > 0 ) {
			$i = $paged - 1;

			?>
            <div class="woopb-page" data-page_id="<?php echo esc_attr( $i ) ?>">
                <span><?php echo esc_html( $i ) ?></span>
            </div>
			<?php
		}
		?>
        <div class="woopb-page woopb-active">
            <span><?php echo esc_html( $paged ) ?></span>
        </div>
		<?php
		if ( $paged + 1 < $max_page ) {
			$i = $paged + 1;

			?>
            <div class="woopb-page" data-page_id="<?php echo esc_attr( $i ) ?>">
                <span><?php echo esc_html( $i ) ?></span>
            </div>
			<?php
		}
		if ( $paged < $max_page ) {
			if ( $paged < ( $max_page - 2 ) ) {
				?>
                <div class="woopb-page">
                    <span>...</span>
                </div>
				<?php
			}
			$i = $max_page;

			?>
            <div class="woopb-page" data-page_id="<?php echo esc_attr( $i ) ?>">
                <span><?php echo esc_html( $i ) ?></span>
            </div>
			<?php
		}
	}

}

