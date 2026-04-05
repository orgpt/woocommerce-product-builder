<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_Short_Share_Link {
	protected static $instance = null;
	protected $settings;

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	protected function __construct() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
		add_action( 'wp_ajax_woopb_set_short_share_link_status', array( $this, 'set_short_share_link_status' ) );
		add_action( 'wp_ajax_woopb_get_short_share_link', array( $this, 'get_short_share_link' ) );
		add_action( 'wp_ajax_nopriv_woopb_get_short_share_link', array( $this, 'get_short_share_link' ) );

		add_action( 'template_redirect', array( $this, 'save_short_link_edit' ), 1 );

		add_filter( 'manage_woo_pb_share_link_posts_columns', array( $this, 'define_cpt_columns' ) );
		add_action( 'manage_woo_pb_share_link_posts_custom_column', array( $this, 'column_content' ), 10, 2 );

		add_action( 'add_meta_boxes_woo_pb_share_link', array( $this, 'add_meta_box' ) );

		//Cron to delete shortlink record
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
		if ( ! wp_next_scheduled( 'woopb_remove_short_link' ) ) {
			wp_schedule_event( time(), 'daily', 'woopb_remove_short_link' );
		}

		add_action( 'woopb_remove_short_link', array( $this, 'remove_short_link' ) );
	}

	public function add_meta_box() {
		add_meta_box( 'data', esc_html__( 'Products', 'woocommerce-product-builder' ), array( $this, 'meta_box_callback' ) );
	}

	public function meta_box_callback( $post ) {
		$data = get_post_meta( $post->ID, '_product_array', true );
		$data = $data['data'] ?? '';
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		echo "<table width='100%' class='vi-ui celled table'>";
		printf( "<thead><tr><th>%s</th><th>%s</th><th>%s</th></tr></thead>",
			esc_html__( 'Image', 'woocommerce-product-builder' ),
			esc_html__( 'Name', 'woocommerce-product-builder' ),
			esc_html__( 'Quantity', 'woocommerce-product-builder' )
		);

		foreach ( $data as $step => $products ) {
			printf( "<tr><td colspan='3'>%s %s</td></tr>", esc_html__( 'Step', 'woocommerce-product-builder' ), esc_html( $step ) );
			if ( ! empty( $products ) && is_array( $products ) ) {
				foreach ( $products as $id => $info ) {
					$p = wc_get_product( $id );
					if ( ! $p ) {
						continue;
					}
					ob_start();
					?>
                    <tr>
                        <td>%s</td>
                        <td><a href="%s">%s</a></td>
                        <td>%s</td>
                    </tr>
					<?php
					$format = ob_get_clean();
					printf( $format, $p->get_image( 'thumbnail' ), wp_kses_post( $p->get_permalink() ), wp_kses_post( $p->get_formatted_name() ), esc_html( $info['quantity'] ?? '' ) );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
		echo "</table>";
		printf( "<a class='vi-ui primary button' href='%s'>%s</a>", esc_url( site_url( '?woopbUrl=' . $post->post_title . '&woopb_edit_short_link=' . $post->ID ) ), esc_html__( 'Edit', 'woocommerce-product-builder' ) );
	}

	public function save_short_link_edit() {
		if ( current_user_can( 'manage_options' ) && isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'woopb_nonce' ) && ! empty( $_POST['woopb_save_edit_short_link'] ) && ! empty( $_POST['woopb_id'] ) ) {
			$id      = sanitize_text_field( $_POST['woopb_save_edit_short_link'] );
			$page_id = sanitize_text_field( $_POST['woopb_id'] );
			$data    = $this->settings->get_data_storage( 'woopb_' . $page_id );
			$this->settings->unset_data_storage( 'woopb_edit_short_link' );
//			$data    = wc()->session->get( 'woopb_' . $page_id );
//			wc()->session->__unset( 'woopb_edit_short_link' );

			if ( ! $data ) {
				return;
			}

			$meta_data = array( 'page_id' => $page_id, 'data' => $data );
			update_post_meta( $id, '_product_array', $meta_data );
			wp_safe_redirect( admin_url( "post.php?post={$id}&action=edit" ) );
		}
	}

	public function set_short_share_link_status() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'woopb_admin_ajax' ) ) ) {
			return;
		}

		$id     = ! empty( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$status = ! empty( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

		if ( $id && $status ) {
			update_post_meta( $id, '_woopb_no_remove', $status );
		}

		wp_die();
	}

	public function get_short_share_link() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'woopb_nonce' ) ) ) {
			return;
		}

		$woopb_id = isset( $_POST['woopb_id'] ) ? sanitize_text_field( $_POST['woopb_id'] ) : '';

		if ( ! $woopb_id ) {
			return;
		}

		$data = $this->settings->get_data_storage( 'woopb_' . $woopb_id );
//		$data = wc()->session->get( 'woopb_' . $woopb_id );

		$url = $this->build_short_link( $data, $woopb_id );

		wp_send_json_success( $url );
	}

	public function build_short_link( $data, $woopb_id, $page_id = 0 ) {

		if ( ! $data ) {
			return '';
		}

		$meta_data = array( 'page_id' => $page_id, 'woopb_id' => $woopb_id, 'data' => $data );

		$posts = get_posts( array(
			'post_type'  => 'woo_pb_share_link',
			'meta_key'   => '_product_array',// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => serialize( $meta_data ),// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		) );

		if ( count( $posts ) ) {
			$post = current( $posts );
			update_post_meta( $post->ID, '_product_array', $meta_data );
			$code = $post->post_title;
		} else {
			$code    = $this->generate_code();
			$user_id = get_current_user_id();

			$args = array(
				'post_author' => $user_id,
				'post_title'  => $code,
				'post_type'   => 'woo_pb_share_link',
				'post_status' => 'publish'
			);

			$post_id = wp_insert_post( $args );
			if ( ! $post_id ) {
				return '';
			}

			update_post_meta( $post_id, '_product_array', $meta_data );

			if ( current_user_can( 'manage_options' ) ) {
				update_post_meta( $post_id, '_woopb_no_remove', 'true' );
			}

		}

		return site_url( "?woopbUrl={$code}" );
	}

	public function generate_code() {
		$code  = str_replace( [ '+', '/', '=' ], '', base64_encode( current_time( 'U' ) ) );
		$posts = get_posts( array( 'post_type' => 'woo_pb_share_link', 's' => $code ) );
		if ( count( $posts ) ) {
			$list = wp_list_pluck( $posts, 'post_title' );
			if ( in_array( $code, $list ) ) {
				$code = $this->generate_code();
			}
		}

		return $code;
	}

	public function define_cpt_columns( $cols ) {
		$date_title = $cols['date'];
		unset( $cols['date'] );
		unset( $cols['title'] );
		$cols['short_link'] = esc_html__( "Short share link", 'woocommerce-product-builder' );
		$cols['clicked']    = esc_html__( "Clicked", 'woocommerce-product-builder' );
		$cols['no_remove']  = esc_html__( "Don't remove by cron", 'woocommerce-product-builder' );
		$cols['date']       = $date_title;

		return $cols;
	}

	public function column_content( $column, $id ) {

		if ( $column === 'no_remove' ) {
			$checked = get_post_meta( $id, '_woopb_no_remove', true );
			$checked = $checked === 'true' ? 'checked' : '';
			printf( '<input type="checkbox" class="woopb-no-remove" value="%s" %s>', esc_attr( $id ), esc_attr( $checked ) );
		}

		if ( $column === 'clicked' ) {
			$clicked = get_post_meta( $id, '_clicked', true );
			echo esc_html( $clicked );
		}

		if ( $column === 'short_link' ) {
			$post = get_post( $id );
			printf( '<input class="woopb-short-share-link"  readonly type="text" value="%s">', esc_url( site_url( '?woopbUrl=' . $post->post_title ) ) );
		}
	}

	public function add_cron_schedule( $sch ) {
		if ( empty( $sch['daily'] ) ) {
			$sch['daily'] = array(
				'interval' => DAY_IN_SECONDS,
				'display'  => esc_html__( 'Daily', 'woocommerce-product-builder' ),
			);
		}

		return $sch;
	}

	public function remove_short_link() {
		$data = new VI_WPRODUCTBUILDER_Data();
		$time = $data->get_param( 'time_to_remove_short_share_link' );
		$time = current_time( 'U' ) - $time * DAY_IN_SECONDS;
		$args = array(
			'post_type'   => 'woo_pb_share_link',
			'numberposts' => - 1,
			'date_query'  => array(
				'after'     => '1980-01-01',
				'before'    => date( 'Y-m-d H:i', $time ),// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'inclusive' => true,
			),

			'meta_query' => array(// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => '_woopb_no_remove',
					'value'   => 'true',
					'compare' => '!='
				),
				array(
					'key'     => '_woopb_no_remove',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		$posts = get_posts( $args );

		if ( count( $posts ) ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID );
			}
		}
	}
}

VI_WPRODUCTBUILDER_Short_Share_Link::instance();