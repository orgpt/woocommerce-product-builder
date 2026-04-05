<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class VI_WPRODUCTBUILDER_Data {
	protected $data;
	protected $params;
	protected $product_filters_cache = array();
	protected static $instance = null;

	public function __construct() {
		global $woocommerce_product_builder_settings;

		if ( ! $woocommerce_product_builder_settings ) {
			$woocommerce_product_builder_settings = get_option( 'woopb_option-param', array() );
		}
		$this->params = $woocommerce_product_builder_settings;
		$args         = apply_filters( 'woopb_default_data', array(
			'template'        => 'ajax-layout-1',
			'enable_email'    => 0,
			'email_header'    => '',
			'email_from'      => '',
			'email_subject'   => '',
			'message_body'    => '',
			'message_success' => '',
			'data_storage'    => 'wc_session',

			'load_step_button_type'             => 'text',
			'add_more_button_text'              => esc_html( 'Select More' ),
			'add_button_text'                   => esc_html( 'Select' ),
			'load_step_button_text_color'       => '#0b57d0',
			'load_step_button_bg_color'         => '#ffffff',
			'load_step_button_hover_text_color' => '#0b57d0',
			'load_step_button_hover_bg_color'   => '#ffffff',
			'load_step_button_border_width'     => 1,

			'preview_page_button_text_color'       => '#0b57d0',
			'preview_page_button_bg_color'         => '#ffffff',
			'preview_page_button_hover_text_color' => '#0b57d0',
			'preview_page_button_hover_bg_color'   => '#ffffff',
			'preview_page_button_border_width'     => 1,

			'button_text_color' => '#ffffff',
			'button_bg_color'   => '#3c90ff',

			'button_main_text_color'          => '#ffffff',
			'button_main_bg_color'            => '#0b57d0',
			'button_icon'                     => '0',
			'share_link'                      => 0,
			'get_short_share_link'            => 0,
			'remember_modal_state'            => 0,
			'time_to_remove_short_share_link' => 30,
			'custom_css'                      => '',
			'remove_session'                  => 0,
			'clear_filter'                    => 0,
			'mobile_bar_text_color'           => '#414141',
			'mobile_bar_bg_color'             => '#fff',
			'mobile_bar_position'             => 0,
			'print_button'                    => 0,
			'download_pdf'                    => 0,
			'keep_filter_when_search'         => 0,
			'pagination_collapse'             => 0,
			'show_short_desc'                 => 0,
		) );
		$this->params = apply_filters( 'woocoommerce_product_builder_settings_args', wp_parse_args( $this->params, $args ) );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_option( $key ) {
		if ( ! $key ) {
			return '';
		}

		return isset( $this->params[ $key ] ) ? $this->params[ $key ] : '';
	}

	public function get_sort_options() {
		return apply_filters( 'woopb_sort_by_events', array(
			'title_az'   => esc_html__( 'Title A-Z', 'woocommerce-product-builder' ),
			'title_za'   => esc_html__( 'Title Z-A', 'woocommerce-product-builder' ),
			'price_low'  => esc_html__( 'Price low to high', 'woocommerce-product-builder' ),
			'price_high' => esc_html__( 'Price high to low', 'woocommerce-product-builder' ),
			'latest'     => esc_html__( 'Sort by latest', 'woocommerce-product-builder' ),
		) );
	}

	/**
	 * Get Custom CSS
	 * @return mixed|void
	 */
	public function get_custom_css() {
		return apply_filters( 'woocoommerce_product_builder_get_custom_css', $this->params['custom_css'] );

	}

	/**
	 * Change icon
	 * @return mixed|void
	 */
	public function get_button_icon() {
		return apply_filters( 'woocoommerce_product_builder_get_button_icon', $this->params['button_icon'] );

	}

	/**
	 * Check enable send email on review page
	 * @return mixed|void
	 */
	public function enable_email() {
		return apply_filters( 'woocoommerce_product_builder_enable_email', $this->params['enable_email'] );
	}

	/**
	 * Get main background color
	 * @return mixed|void
	 */
	public function get_button_text_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_text_color', $this->params['button_text_color'] );
	}

	/**
	 * Get  background color
	 * @return mixed|void
	 */
	public function get_button_bg_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_bg_color', $this->params['button_bg_color'] );
	}

	/**
	 * Get main text color
	 * @return mixed|void
	 */
	public function get_button_main_text_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_main_text_color', $this->params['button_main_text_color'] );
	}

	/**
	 * Get main background color
	 * @return mixed|void
	 */
	public function get_button_main_bg_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_main_bg_color', $this->params['button_main_bg_color'] );
	}

	/**
	 * Get message successful when send email to friends.
	 * @return mixed|void
	 */
	public function get_message_success() {
		return apply_filters( 'woocoommerce_product_builder_get_message_success', $this->params['message_success'] );
	}

	/**
	 * Get email body
	 * @return mixed|void
	 */
	public function get_message_body() {
		return apply_filters( 'woocoommerce_product_builder_get_message_body', $this->params['message_body'] );
	}

	/**
	 * Get email subject
	 * @return mixed|void
	 */
	public function get_email_subject() {
		return apply_filters( 'woocoommerce_product_builder_get_email_subject', $this->params['email_subject'] );
	}

	/**
	 * Get email from
	 * @return mixed|void
	 */
	public function get_email_header() {
		return apply_filters( 'woocoommerce_product_builder_get_email_header', $this->params['email_header'] );
	}


	public function get_email_from() {
		return apply_filters( 'woocoommerce_product_builder_get_email_from', $this->params['email_from'] );
	}

	public function get_remove_session() {
		return $this->params['remove_session'];
	}

	public function get_param( $key ) {
		return $this->params[ $key ] ?? '';
	}

	/**
	 * Check products added in all steps
	 *
	 * @param     $post_id
	 * @param int $step_id
	 *
	 * @return bool
	 */
	public function has_step_added( $post_id, $step_id = 0 ) {
		$session_id = 'woopb_' . $post_id;
		if ( ! wc()->session ) {
			return false;
		}
		$data_session = $this->get_data_storage( $session_id );;
//        $data_session = WC()->session->get($session_id);
		$tabs  = $this->get_data( $post_id, 'tab_title' );
		$count = count( array_filter( $tabs ) );
		if ( $step_id ) {
			if ( isset( $data_session[ $step_id ] ) && is_array( $data_session[ $step_id ] ) && count( array_filter( $data_session[ $step_id ] ) ) ) {
				$products_added = array_filter( $data_session[ $step_id ] );
			} else {
				return false;
			}
			if ( count( $products_added ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( isset( $data_session ) && is_array( $data_session ) && count( array_filter( $data_session ) ) ) {
				$products_added = array_filter( $data_session );

			} else {
				return false;
			}
			if ( count( $products_added ) == $count ) {
				foreach ( $products_added as $step ) {
					if ( is_array( $step ) && count( array_filter( $step ) ) ) {
					} else {
						return false;
					}
				}

				return true;
			} else {
				return false;
			}
		}

	}

	/**
	 * Get Post Meta
	 *
	 * @param $post_id
	 * @param $field
	 * @param $default
	 *
	 * @return bool
	 */
	public function get_data( $post_id, $field, $default = '' ) {

		if ( isset( $this->data[ $post_id ] ) && $this->data[ $post_id ] ) {
			$params = $this->data[ $post_id ];
		} else {
			$this->data[ $post_id ] = get_post_meta( $post_id, 'woopb-param', true );
			$params                 = $this->data[ $post_id ];
		}
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	public function set_data_storage( $name, $value, $time = 86400, $path = '/' ) {
		switch ( $this->get_param( 'data_storage' ) ) {
			case 'cookie':
				$encode_val = base64_encode( wp_json_encode( $value ) );
				$domain     = apply_filters( 'woopb_setcookie_domain', '' );
				@setcookie( $name, $encode_val, $time, $path, $domain );
				$_COOKIE[ $name ] = $encode_val;
				break;
			case 'session':
				@session_start();
				$encode_val        = base64_encode( wp_json_encode( $value ) );
				$_SESSION[ $name ] = $encode_val;
				session_write_close();
				break;
			default:
				if ( is_user_logged_in() ) {
					$wbpss_user_id = get_current_user_id();
					if ( $wbpss_user_id ) {
						update_user_meta( $wbpss_user_id, $name, $value );
					} else {
						WC()->session->set( $name, $value );
					}
				} else {
					WC()->session->set( $name, $value );
				}
		}
	}

	public function get_data_storage( $name ) {
		$value = null;

		switch ( $this->get_param( 'data_storage' ) ) {
			case 'cookie':
				$encode_value = isset( $_COOKIE[ $name ] ) ? $_COOKIE[ $name ] : false;
				$value        = $encode_value ? json_decode( base64_decode( $encode_value ) ) : [];
				break;
			case 'session':
				if ( ! session_id() && ! $this->is_request_to_rest_api() ) {
					@session_start();
				}
				$encode_value = isset( $_SESSION[ $name ] ) ? $_SESSION[ $name ] : false;
				$value        = $encode_value ? is_array( $encode_value ) ? $encode_value : $this->object_to_array( json_decode( base64_decode( $encode_value ) ) ) : [];
				break;
			default:
				if ( is_user_logged_in() ) {
					$wbpss_user_id = get_current_user_id();
					if ( $wbpss_user_id ) {
						$value = get_user_meta( $wbpss_user_id, $name, true );
					} else {
						$value = WC()->session->get( $name );
					}
				} else {
					$value = WC()->session->get( $name );
				}
		}

		return $value;
	}

	public function unset_data_storage( $session_id ) {
		switch ( $this->get_param( 'data_storage' ) ) {
			case 'cookie':
				break;
			case 'session':
				break;
			default:
				if ( is_user_logged_in() ) {
					$wbpss_user_id = get_current_user_id();
					if ( $wbpss_user_id ) {
						delete_user_meta( get_current_user_id(), $session_id );
					} else {
						WC()->session->__unset( $session_id );
					}
				} else {
					WC()->session->__unset( $session_id );
				}
		}
	}

	public function is_request_to_rest_api() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix = '/' . untrailingslashit( rest_get_url_prefix() ) . '/';
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		return false !== strpos( $request_uri, $rest_prefix );
	}

	function object_to_array( $obj ) {
		if ( is_object( $obj ) ) {
			$obj = (array) $obj;
		}
		if ( is_array( $obj ) ) {
			return array_map( [ $this, 'object_to_array' ], $obj );
		} else {
			return $obj;
		}
	}

	/**
	 * Set Products added by Session
	 *
	 * @param     $post_id
	 * @param     $data
	 * @param int $step_id
	 *
	 * @return bool
	 */
	public function set_products_added( $post_id, $data, $step_id = 0 ) {
		if ( $post_id && is_array( $data ) && count( array_filter( $data ) ) ) {
			$session_id   = 'woopb_' . $post_id;
			$data_session = $this->get_data_storage( $session_id );
//            $data_session = WC()->session->get($session_id);
			if ( ! WC()->session ) {
				WC()->session = new WC_Session_Handler();
				WC()->session->init();
			}

			$cookie = WC()->session->get_session_cookie();
			if ( ! $cookie ) {
				WC()->session->set_customer_session_cookie( true );
			}

			if ( $step_id ) {
//            	echo '<pre>' . print_r($data_session, true) . '</pre>';
				$data_session[ $step_id ] = $data;
			} else {
				$data_session = $data;
			}

			$this->set_data_storage( $session_id, $data_session );

//            WC()->session->set($session_id, $data_session);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove products in Product Builder
	 *
	 * @param $post_id
	 * @param $product_id
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function remove_products( $post_id ) {
		if ( $post_id ) {
			$session_id = 'woopb_' . $post_id;
			$this->unset_data_storage( $session_id );

//            WC()->session->__unset($session_id);

			return true;
		}

		return false;
	}

	/**
	 * Remove product in Session
	 *
	 * @param $post_id
	 * @param $product_id
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function remove_product( $post_id, $product_id, $step_id ) {
		if ( $post_id && $product_id && $step_id ) {
			$session_id   = 'woopb_' . $post_id;
			$data_session = $this->get_data_storage( $session_id );;
//            $data_session = WC()->session->get($session_id);
			unset( $data_session[ $step_id ][ $product_id ] );
			$data_session = apply_filters( 'woopb_get_products', $data_session, $product_id, $step_id, $post_id );
			$this->set_data_storage( $session_id, $data_session );
//            WC()->session->set($session_id, $data_session);
			$sum = 0;
			if ( is_array( $data_session ) && count( $data_session ) ) {
				foreach ( $data_session as $step ) {
					if ( is_array( $step ) && ! empty( $step ) ) {
						$sum += array_sum( $step );
					}
				}
			}
			if ( $sum ) {
				return true;
			}
		}

		return false;
	}

	public function remove_no_product_found( $post_id, $step_id ) {
		if ( ! WC()->session ) {
			return;
		}

		$session_id   = 'woopb_' . $post_id;
		$data_session = $this->get_data_storage( $session_id );;
//        $data_session = WC()->session->get($session_id);
		if ( ! is_array( $data_session ) ) {
			$this->set_data_storage( $session_id, [] );
		} else {
			if ( isset( $data_session[ $step_id ][0] ) ) {
				unset( $data_session[ $step_id ][0] );
				$this->set_data_storage( $session_id, $data_session );
				//            WC()->session->set($session_id, $data_session);
			}
		}
	}

	/**
	 * Check product added in Session
	 *
	 * @param $post_id
	 * @param $step_id
	 * @param $product_id
	 *
	 * @return bool
	 */
	public function check_product_added( $post_id, $step_id, $product_id ) {
		if ( ! $post_id || ! $step_id || ! $product_id ) {
			return false;
		}
		$products_added = $this->get_products_added( $post_id, $step_id );
		if ( isset( $products_added[ $product_id ] ) && $products_added[ $product_id ] > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get product added
	 *
	 * @param $post_id
	 * @param bool $step_id
	 *
	 * @return array|string
	 */
	public function get_products_added( $post_id, $step_id = false ) {
		$session_id     = 'woopb_' . $post_id;
		$products_added = [];
		if ( ! wc()->session ) {
			return $products_added;
		}
		$data_session = $this->get_data_storage( $session_id );;
//        $data_session = WC()->session->get($session_id);
//		echo '<pre>'.print_r($data_session,true).'</pre>';
		if ( $step_id !== false ) {
			if ( isset( $data_session[ $step_id ] ) && is_array( $data_session[ $step_id ] ) && count( array_filter( $data_session[ $step_id ] ) ) ) {
				$products_added = array_filter( $data_session[ $step_id ] );
			} else {
				$products_added = array();
			}
		} else {
			if ( isset( $data_session ) && is_array( $data_session ) && count( array_filter( $data_session ) ) ) {
				$products_added = array_filter( $data_session );
			} else {
				$products_added = array();
			}
		}

		return $products_added;
	}

	/**
	 * Get list product in Product Builder page
	 * @return array
	 */
	public function get_products( $post_id ) {
		/*Get current step*/
		$step_id = get_query_var( 'step' );
		if ( ! $step_id ) {
			$step_id = absint( $_POST['step'] ?? 0 ) + 1;// phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( ! $step_id ) {
			$step_id = 1;
		}

		$items = $this->get_data( $post_id, 'list_content', array() );
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

		$args      = array(
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
			'fields'         => 'ids'
		);
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$product_ids_of_term = $the_query->posts;
		}
		wp_reset_postdata();

		$product_ids = array_unique( array_merge( $product_ids, $product_ids_of_term ) );

		return $product_ids;
	}

	/**
	 * Get list product in Product Builder page
	 * @return array
	 */
	public function get_product_filters( $post_id, $step_id = '', $paged = '', $pagination = true ) {
		if ( isset( $_REQUEST['woopb_field_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_field_nonce'] ), 'woopb_field_nonce' ) ) {
			return [];
		}
		global $wpdb;
		/*Get current step*/
		if ( $step_id === '' || $step_id === false ) {
			$step_id = ! empty( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : '';
		}

		if ( ! $step_id ) {
			$step_id = 1;
		}

		/*Get pagination*/
		if ( $paged === '' ) {
			$paged = ! empty( $_REQUEST['ppaged'] ) ? absint( $_REQUEST['ppaged'] ) : '';
		}

		if ( ! $paged ) {
			$paged = 1;
		}

		$cache_request = array(
			'min_price'     => sanitize_text_field( $_REQUEST['min_price'] ?? '' ),
			'max_price'     => sanitize_text_field( $_REQUEST['max_price'] ?? '' ),
			'rating_filter' => sanitize_text_field( $_REQUEST['rating_filter'] ?? '' ),
			'sort_by'       => sanitize_text_field( wp_unslash( $_POST['sort_by'] ?? get_query_var( 'sort_by' ) ) ),
			'name_filter'   => sanitize_text_field( get_query_var( 'name_filter' ) ),
		);

		foreach ( $_REQUEST as $request_key => $request_value ) {
			if ( 0 === strpos( $request_key, 'filter_' ) || 0 === strpos( $request_key, 'query_type_' ) ) {
				$cache_request[ $request_key ] = is_array( $request_value )
					? array_map( 'sanitize_text_field', wp_unslash( $request_value ) )
					: sanitize_text_field( wp_unslash( $request_value ) );
			}
		}

		$cache_key = md5( wp_json_encode( array(
			'post_id'     => (int) $post_id,
			'step_id'     => (int) $step_id,
			'paged'       => (int) $paged,
			'pagination'  => (bool) $pagination,
			'request'     => $cache_request,
		) ) );

		if ( array_key_exists( $cache_key, $this->product_filters_cache ) ) {
			return $this->product_filters_cache[ $cache_key ];
		}

		$post_per_page = $this->get_data( $post_id, 'product_per_page', 10 );
		$items         = $this->get_data( $post_id, 'list_content', array() ); //list steps

		if ( $step_id > count( $items ) ) {
			$step_id = count( $items ) - 1;
		}

		$item_data = isset( $items[ $step_id - 1 ] ) ? $items[ $step_id - 1 ] : array(); //items in this step
		if ( ! empty( $item_data ) ) {
			$terms = $product_ids = $product_ids_of_term = array();

			foreach ( $item_data as $item ) {
				if ( strpos( trim( $item ), 'cate_' ) === false ) {
					$product_ids[] = $item;
				} else {
					$terms[] = str_replace( 'cate_', '', trim( $item ) );
				}
			}

			$select[] = "SELECT p.ID FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id LEFT JOIN {$wpdb->term_taxonomy} AS tt1 ON tt1.term_taxonomy_id = tr.term_taxonomy_id";
			$where[]  = "p.post_type = 'product' AND p.post_status = 'publish'";
			$order    = "GROUP BY p.ID ORDER BY p.post_date DESC ";
			/*Process attributes*/
			$list_attrs = array();
			if ( $this->get_data( $post_id, 'enable_compatible' ) ) {
				$compatible_data          = $this->get_data( $post_id, 'product_compatible' );
				$current_compatible_steps = isset( $compatible_data[ $step_id - 1 ] ) ? $compatible_data[ $step_id - 1 ] : array();
				/*Get attributes from products added in Session*/
				if ( is_array( $current_compatible_steps ) && ! empty( $current_compatible_steps ) ) {
//					$compatible_and          = $this->get_data( $post_id, 'product_compatible_and' );
					$compatible_products = array();//product added data
					foreach ( $current_compatible_steps as $compatible_step ) {
						$temp_data = $this->get_products_added( $post_id, $compatible_step + 1 ); //difference between step & array key
						if ( is_array( $temp_data ) && ! empty( $temp_data ) ) {
							$compatible_products = array_merge( $compatible_products, array_values( $temp_data ) );
						}
					}
					if ( is_array( $compatible_products ) && ! empty( $compatible_products ) ) {
						foreach ( $compatible_products as $compatible_product ) {
							$product = wc_get_product( $compatible_product['product_id'] ?? $compatible_product['woopb-add-to-cart'] ?? 0 );
							if ( ! $product ) {
								continue;
							}
							$attrs = $product->get_attributes();
							if ( is_array( $attrs ) && ! empty( $attrs ) ) {
								foreach ( $attrs as $key => $attr ) {
									$options = $attr->get_options();
									if ( is_object( $attr ) && ( $attr->get_id() ) ) {
										$list_attrs[ $key ] = isset( $list_attrs[ $key ] ) ? array_unique( array_merge( $list_attrs[ $key ], $options ) ) : $options;
									}
								}
							}
						}

					}

				}
					if ( is_array( $list_attrs ) && ! empty( $list_attrs ) ) {
						$list_attrs = $this->convert_single_array( $list_attrs );
						$list_attrs = array_map( 'trim', $list_attrs );
						$list_attrs = apply_filters( 'woopb_list_attrs_depend', $list_attrs, $step_id );
						if ( empty( $list_attrs ) ) {
							$this->product_filters_cache[ $cache_key ] = false;
							return false;
						}

						$where[] = "tt1.term_id IN (" . implode( ',', $list_attrs ) . ")";
					}
				}
			$where_products      = $where;
			$product_ids_of_term = $result_product_ids = array();

			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$all_terms = array();
				if ( $this->get_data( $post_id, 'child_cat' ) ) {
					foreach ( $terms as $term ) {
						$children = get_term_children( $term, 'product_cat' );
						if ( is_array( $children ) && ! empty( $children ) ) {
							foreach ( $children as $child ) {
								$all_terms[] = $child;
							}
						}
					}
					$all_terms = array_unique( array_merge( $all_terms, $terms ) );
				} else {
					$all_terms = $terms;
				}

				$select[] = "LEFT JOIN {$wpdb->term_relationships} AS tr1 ON p.ID = tr1.object_id LEFT JOIN {$wpdb->term_taxonomy} AS tt2 ON tt2.term_taxonomy_id = tr1.term_taxonomy_id";
				$where[]  = $wpdb->prepare( "tt2.term_id IN (%1s)", implode( ',', $all_terms ) );// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				$query    = implode( ' ', $select ) . ' WHERE ' . implode( ' AND ', $where ) . ' ' . $order;

				$product_ids_of_term = $wpdb->get_col( $query );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			}

			/*Process compatible with specify products*/
			if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
				$where_products[] = 'p.ID IN (' . implode( ',', $product_ids ) . ')';
				$query            = implode( ' ', $select ) . ' WHERE ' . implode( ' AND ', $where_products ) . ' ' . $order;

				$result_product_ids = $wpdb->get_col( $query );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			}

			if ( is_array( $product_ids_of_term ) && ! empty( $product_ids_of_term ) && is_array( $result_product_ids ) && ! empty( $result_product_ids ) ) {
				$product_ids = array_unique( array_merge( $result_product_ids, $product_ids_of_term ) );
			} else if ( is_array( $product_ids_of_term ) && ! empty( $product_ids_of_term ) ) {
				$product_ids = array_unique( $product_ids_of_term );
			} else if ( is_array( $result_product_ids ) && ! empty( $result_product_ids ) ) {
				$product_ids = array_unique( $result_product_ids );
			} else {
				return false;
			}

			$product_ids = apply_filters( 'woopb_list_product_ids', $product_ids, $step_id, $list_attrs );

			/*Show products on step*/
			if ( count( $product_ids ) < 1 ) {
				$this->product_filters_cache[ $cache_key ] = false;
				return false;
			} elseif ( $pagination ) {

				$product_args = array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'posts_per_page' => $post_per_page,
					'post__in'       => $product_ids,
					'paged'          => $paged,
					'tax_query'      => array(// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
							'operator' => 'IN'
						)
					),
					'fields'         => 'ids'
				);
			} else {
				$product_args = array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'post__in'       => $product_ids,
					'tax_query'      => array(// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
							'operator' => 'IN'
						)
					),
					'fields'         => 'ids'
				);
			}

			$out_of_stock = $this->get_data( $post_id, 'out_of_stock_product' );

			if ( ! $out_of_stock ) {
				$product_args['meta_query'][] = array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!=',
				);
			}

			/*Check filter price*/
			$filter_price[] = ! empty( $_REQUEST['min_price'] ) ? sanitize_text_field( $_REQUEST['min_price'] ) : '';
			$filter_price[] = ! empty( $_REQUEST['max_price'] ) ? sanitize_text_field( $_REQUEST['max_price'] ) : '';

			if ( count( array_filter( $filter_price ) ) ) {
				$product_args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => $filter_price,
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC'
				);
			}

			$zero_price_product = $this->get_data( $post_id, 'zero_price_product' );
			if ( $zero_price_product ) {
				$product_args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC'
				);
			}

			/*Check filter by rating*/
			$filter_rating = ! empty( $_REQUEST['rating_filter'] ) ? sanitize_text_field( $_REQUEST['rating_filter'] ) : '';

			if ( $filter_rating ) {
				$filter_rating            = explode( ',', $filter_rating );
				$product_visibility_terms = wc_get_product_visibility_term_ids();
				$rate_ids                 = array();
				if ( count( $product_visibility_terms ) ) {
					foreach ( $filter_rating as $rate_value ) {
						$rate_id = trim( $rate_value );
						if ( isset( $product_visibility_terms[ 'rated-' . $rate_id ] ) && $product_visibility_terms[ 'rated-' . $rate_id ] ) {
							$rate_ids[] = $product_visibility_terms[ 'rated-' . $rate_value ];
						}
					}

					$product_args['tax_query'][] = array(
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'term_id',
							'terms'    => $rate_ids,
							'operator' => 'IN'
						)
					);
				}
			}

			/*Check Attribute filter*/
			$chosen_attributes = $this->get_layered_nav_chosen_attributes();
			if ( count( $chosen_attributes ) ) {
				foreach ( $chosen_attributes as $taxonomy => $terms_slug ) {
					$product_args['tax_query'][] = array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $terms_slug['terms'],
							'operator' => 'IN'
						)
					);
				}
			}

			/*Sort by*/
			$sort_by = get_query_var( 'sort_by' );
			if ( isset( $_POST['sort_by'] ) ) {
				$sort_by = sanitize_text_field( wp_unslash( $_POST['sort_by'] ) );
			}

			$sort_by = $sort_by ? $sort_by : $this->get_data( $post_id, 'sort_default' );

			switch ( $sort_by ) {
				case '':
				case 'title_az':
					$product_args['orderby'] = 'title';
					$product_args['order']   = 'ASC';
					break;

				case 'title_za':
					$product_args['orderby'] = 'title';
					$product_args['order']   = 'DESC';
					break;

				case 'price_low':
					$product_args['orderby']  = 'meta_value_num';
					$product_args['order']    = 'ASC';
					$product_args['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;

				case 'price_high':
					$product_args['orderby']  = 'meta_value_num';
					$product_args['order']    = 'DESC';
					$product_args['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;
				case 'latest':
					$product_args['orderby'] = 'ID';
					$product_args['order']   = 'DESC';
					break;
			}

			/*Check filter by search name*/
			$filter_product = get_query_var( 'name_filter' );
			if ( $filter_product ) {
				$product_args['s'] = $filter_product;
			}

			$the_product = new WP_Query( $product_args );
			if ( $the_product->have_posts() ) {
				$this->product_filters_cache[ $cache_key ] = $the_product;
				return $the_product;
			}
			wp_reset_postdata();
		}

		$this->product_filters_cache[ $cache_key ] = false;
		return false;
	}

	/**
	 * Convert multi array to single array keep number key
	 *
	 * @param $array
	 *
	 * @return array|bool
	 */
	private function convert_single_array( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$result = array();
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) && count( $value ) ) {
				foreach ( $value as $key_2 => $value_2 ) {
					$result[] = $value_2;
				}
			}
		}

		return $result;
	}

	public function get_share_link_enable() {
		return $this->params['share_link'];
	}
	public function get_remember_modal_state_enable() {
		return $this->params['remember_modal_state'];
	}

	public function get_share_link() {

		global $post;
		if ( empty( $post ) ) {
			return;
		}

		$woopb_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id : $post->ID;

		$data = $this->get_data_storage( 'woopb_' . $woopb_id );;
//        $data = WC()->session->get('woopb_' . $woopb_id);
		$data      = array( 'page_id' => $post->ID, 'woopb_id' => $woopb_id, 'data' => $data );
		$share     = base64_encode( wp_json_encode( $data ) );
		$share     = strtr( $share, '+/=', '-_,' );
		$share_url = site_url( "?woopb_share={$share}" );

		return $share_url;
	}

	public function load_photoswipe_template() {
		wc_get_template( 'single-product/photoswipe.php' );
	}

	public function enqueue_scripts() {
		wp_dequeue_script( 'klb-single-ajax' );
		wp_enqueue_style( 'photoswipe' );
		wp_enqueue_style( 'photoswipe-default-skin' );
		wp_enqueue_style( 'woocommerce-product-builder-icon' );
		wp_enqueue_style( 'woocommerce-product-builder' );
		wp_enqueue_style( 'woocommerce-product-builder-preview' );
		wp_enqueue_style( 'woocommerce-product-builder-rtl' );
		wp_enqueue_script( 'wc-price-slider' );
		wp_enqueue_script( 'zoom' );
		wp_enqueue_script( 'photoswipe-ui-default' );
		wp_enqueue_script( 'wc-single-product' );
		wp_enqueue_script( 'wc-add-to-cart' );
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		wp_enqueue_script( 'woocommerce-product-builder' );

		$button_text_color      = $this->get_button_text_color();
		$button_bg_color        = $this->get_button_bg_color();
		$button_main_text_color = $this->get_button_main_text_color();
		$button_main_bg_color   = $this->get_button_main_bg_color();

		$mobile_text_color   = $this->get_param( 'mobile_bar_text_color' );
		$mobile_bg_color     = $this->get_param( 'mobile_bar_bg_color' );
		$mobile_bar_position = $this->get_param( 'mobile_bar_position' );

		$load_step_button_text_color       = apply_filters( 'woocoommerce_product_builder_get_load_step_button_text_color', $this->params['load_step_button_text_color'] );
		$load_step_button_bg_color         = apply_filters( 'woocoommerce_product_builder_get_load_step_button_bg_color', $this->params['load_step_button_bg_color'] );
		$load_step_button_hover_text_color = apply_filters( 'woocoommerce_product_builder_get_load_step_button_hover_text_color', $this->params['load_step_button_hover_text_color'] );
		$load_step_button_hover_bg_color   = apply_filters( 'woocoommerce_product_builder_get_load_step_button_hover_bg_color', $this->params['load_step_button_hover_bg_color'] );
		$load_step_button_border_width     = apply_filters( 'woocoommerce_product_builder_get_load_step_button_border_width', $this->params['load_step_button_border_width'] );

		$load_step_button_border_style       = '';
		$load_step_button_border_hover_style = '';
		if ( $load_step_button_border_width >= 1 ) {
			$load_step_button_border_style       = "border:solid {$load_step_button_border_width}px {$load_step_button_text_color}";
			$load_step_button_border_hover_style = "border:solid {$load_step_button_border_width}px {$load_step_button_hover_text_color}";
		}

		$preview_page_button_text_color       = apply_filters( 'woocoommerce_product_builder_get_preview_page_button_text_color', $this->params['preview_page_button_text_color'] );
		$preview_page_button_bg_color         = apply_filters( 'woocoommerce_product_builder_get_preview_page_button_bg_color', $this->params['preview_page_button_bg_color'] );
		$preview_page_button_hover_text_color = apply_filters( 'woocoommerce_product_builder_get_preview_page_button_hover_text_color', $this->params['preview_page_button_hover_text_color'] );
		$preview_page_button_hover_bg_color   = apply_filters( 'woocoommerce_product_builder_get_preview_page_button_hover_bg_color', $this->params['preview_page_button_hover_bg_color'] );
		$preview_page_button_border_width     = apply_filters( 'woocoommerce_product_builder_get_preview_page_button_border_width', $this->params['preview_page_button_border_width'] );

		$preview_page_button_border_style       = '';
		$preview_page_button_border_hover_style = '';
		if ( $preview_page_button_border_width >= 1 ) {
			$preview_page_button_border_style       = "border:solid {$preview_page_button_border_width}px {$preview_page_button_text_color}";
			$preview_page_button_border_hover_style = "border:solid {$preview_page_button_border_width}px {$preview_page_button_hover_text_color}";
		}
		$custom_css = ":root {
        --button_text_color:{$button_text_color};
        --button_bg_color:{$button_bg_color};
        --button_main_text_color:{$button_main_text_color};
        --button_main_bg_color:{$button_main_bg_color};
        --load_step_button_text_color:{$load_step_button_text_color};
        --load_step_button_bg_color:{$load_step_button_bg_color};
        --load_step_button_hover_text_color:{$load_step_button_hover_text_color};
        --load_step_button_hover_bg_color:{$load_step_button_hover_bg_color};
        --load_step_button_border_width:{$load_step_button_border_width};
        --preview_page_button_text_color:{$preview_page_button_text_color};
        --preview_page_button_bg_color:{$preview_page_button_bg_color};
        --preview_page_button_hover_text_color:{$preview_page_button_hover_text_color};
        --preview_page_button_hover_bg_color:{$preview_page_button_hover_bg_color};
        --preview_page_button_border_width:{$preview_page_button_border_width};
        }
        .vi-wpb-wrapper .woopb-products-pagination .woopb-page.woopb-active,
        .vi-wpb-wrapper .woopb-search-pagination .woopb-page.woopb-active,
        .vi-wpb-wrapper .woopb-products-pagination .woopb-page:hover,
        .vi-wpb-wrapper .woopb-search-pagination .woopb-page:hover,
        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:hover,
        .woopb-button.woopb-button-primary,
        .woopb-button:hover,
        .woocommerce-product-builder-widget.widget_price_filter .ui-slider .ui-slider-range, 
        .woocommerce-product-builder-widget.widget_price_filter .ui-slider .ui-slider-handle,
        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active,
        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active a,
        #woopb-modal .woopb-active-page,
        #woopb-modal .woopb-add-to-list-btn{	
            color:{$button_main_text_color};
            background-color:{$button_main_bg_color};
            border: 1px solid {$button_main_bg_color};
        }
        .vi-wpb-wrapper .woopb-products-pagination .woopb-page,
        .vi-wpb-wrapper .woopb-search-pagination .woopb-page,
        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button,
        .woopb-button{
            color:{$button_text_color};
            background-color:{$button_bg_color};
            border: 1px solid {$button_bg_color};
        }
        .woopb-button.woopb-icon-svg::before,
        .woopb-button .woopb-icon-svg::before{
            background-color:{$button_text_color};
        }
         .woopb-button-preview-page{
            color:{$preview_page_button_text_color};
            background-color:{$preview_page_button_bg_color};
            {$preview_page_button_border_style}
        }
        .woopb-button-preview-page:hover{
            color:{$preview_page_button_hover_text_color};
            background-color:{$preview_page_button_hover_bg_color};
            {$preview_page_button_border_hover_style}
        }
        .woopb-button-preview-page .woopb-icon-svg{
            background-color:{$preview_page_button_text_color};
        }
        .woopb-button-preview-page.woopb-icon-svg:hover::before{
            background-color:{$preview_page_button_hover_text_color};
        }
        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:before, 
        .woocommerce-product-builder-widget .woocommerce-widget-layered-nav-list li > a.woopb-add-to-list-btn, 
        .woocommerce-product-builder-widget .woocommerce-widget-layered-nav-list li.chosen > a.woopb-add-to-list-btn{
            color:{$button_text_color};
            background-color:{$button_bg_color};
        }
       .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:hover:before,
       .vi-wpb-wrapper .woopb-step-heading-active a,.vi-wpb-wrapper a:not(.woopb-button):hover{
            color:$button_main_bg_color;
        }
        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active:before{
            background-color:$button_main_bg_color;
        }
        #woopb-main .woopb-load-step{
            color:{$load_step_button_text_color};
            background-color:{$load_step_button_bg_color};
            {$load_step_button_border_style}
        }
        #woopb-main .woopb-load-step:hover{
            color:{$load_step_button_hover_text_color};
            background-color:{$load_step_button_hover_bg_color};
            {$load_step_button_border_hover_style}
        }
        ";

		$custom_css .= ".vi-wpb-wrapper .woopb-mobile-control-bar{color:{$mobile_text_color};background-color:{$mobile_bg_color};bottom:{$mobile_bar_position}px;}";

		$custom_css .= $this->get_custom_css();
		$custom_css = apply_filters( 'woopb_custom_css_inline', $custom_css );

		wp_add_inline_style( 'woocommerce-product-builder', $custom_css );

		// Localize the script with new data
		ob_start();
		wpb_get_template( 'ajax-parts/print-style.php' );
		$print_style = ob_get_clean();

		$layouts = $this->get_print_layouts();

		do_action( 'litespeed_nonce', '_woopb_add_to_cart private' );
		$translation_array = array(
			'wp_ajax_url'        => admin_url( 'admin-ajax.php' ),
			'ajax_url'           => WC_AJAX::get_endpoint( 'woopb_action' ),
			'pluginURL'          => plugins_url(),
			'templateStyle'      => ( VI_WOOPBTEMPLATE_STYLE == 'ajax' ) ? 'ajax-layout-1' : VI_WOOPBTEMPLATE_STYLE,
			'print_product_col'  => esc_html__( 'Product', 'woocommerce-product-builder' ),
			'print_qty_col'      => esc_html__( 'Quantity', 'woocommerce-product-builder' ),
			'print_price_col'    => esc_html__( 'Price', 'woocommerce-product-builder' ),
			'print_subtotal_col' => esc_html__( 'Subtotal', 'woocommerce-product-builder' ),
			'print_total_title'  => esc_html__( 'Total', 'woocommerce-product-builder' ),
			'nonce'              => wp_create_nonce( 'woopb_nonce' )
		);

		global $post, $woopb_id;
		$post_id = is_woopb_shortcode() ? $woopb_id : $post->ID;
		/*List ajax template*/
		$ajax_template = [ 'ajax', 'ajax-layout-1', 'ajax-layout-2' ];
		if ( in_array( VI_WOOPBTEMPLATE_STYLE, $ajax_template ) ) {
			$page    = get_post();
			$page_id = 0;

			if ( $page && $page->post_type === 'page' ) {
				$page_id = $page->ID;
			}

			$config              = get_post_meta( $post_id, 'woopb-param', true );
			$enable_multi_select = $this->get_data( $post_id, 'enable_multi_select' );
			$qty_field           = $this->get_data( $post_id, 'enable_quantity' );

			if ( ! empty( $config['step_icon'] ) && is_array( $config['step_icon'] ) ) {
				foreach ( $config['step_icon'] as $key => $id ) {
					$icon_url                    = wp_get_attachment_url( $id );
					$config['step_icon'][ $key ] = $icon_url;
				}
			}

			ob_start();
			wpb_get_template( 'ajax-parts/product.php', [
				'enable_multi_select' => $enable_multi_select,
				'qty_field'           => $qty_field
			] );
			$product_tmpl = ob_get_clean();

			ob_start();
			wpb_get_template( 'ajax-parts/step.php', [
				'enable_multi_select' => $enable_multi_select,
				'settings'            => $this
			] );
			$step_tmpl = ob_get_clean();

			ob_start();
			wpb_get_template( 'ajax-parts/empty.php', [ 'enable_multi_select' => $enable_multi_select ] );
			$empty_tmpl = ob_get_clean();

			ob_start();
			wpb_get_template( 'ajax-parts/load-step-button.php', [
				'enable_multi_select' => $enable_multi_select,
				'post_id'             => $post_id,
				'settings'            => $this
			] );
			$load_step_button = ob_get_clean();

			$translation_array['page_id']           = $page_id;
			$translation_array['post_id']           = $post_id;
			$translation_array['config']            = $config;
			$translation_array['productTmpl']       = $product_tmpl;
			$translation_array['stepTmpl']          = $step_tmpl;
			$translation_array['emptyTmpl']         = $empty_tmpl;
			$translation_array['loadStepButton']    = $load_step_button;
			$translation_array['textCopied']        = esc_html__( 'Copied', 'woocommerce-product-builder' );
			$translation_array['textNoUrl']         = esc_html__( 'No link was created', 'woocommerce-product-builder' );
			$translation_array['checkDependNotice'] = esc_html__( "This product is dependent on a product from the previous step(s) which may be missing/removed", 'woocommerce-product-builder' );
			$translation_array['printStyle']        = $print_style;
			$translation_array['printHeader']       = $layouts['header'];
			$translation_array['printFooter']       = $layouts['footer'];
			$translation_array['printDesc']         = $this->get_param( 'show_short_desc' );
		}

		if ( ! empty( $_GET['woopb_preview'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$session_key = "woopb_{$post_id}";
			$steps       = $this->get_data_storage( $session_key );;
//            $steps = wc()->session->get($session_key);
			if ( ! empty( $steps ) && is_array( $steps ) ) {
				foreach ( $steps as $key => $step ) {
					if ( ! empty( $step ) && is_array( $step ) ) {
						$steps[ $key ] = $this->get_detail_step( $step );
					}
				}
			}

			$translation_array['post_id']     = $post_id;
			$translation_array['printStyle']  = $print_style;
			$translation_array['printHeader'] = $layouts['header'];
			$translation_array['printFooter'] = $layouts['footer'];
			$translation_array['stepsData']   = $steps;
			$translation_array['printDesc']   = $this->get_param( 'show_short_desc' );
		}

		wp_localize_script( 'woocommerce-product-builder', '_woo_product_builder_params', $translation_array );
	}

	public function get_store_address() {

		$countries = WC()->countries->get_countries();
		$states    = WC()->countries->get_states();

		$country_code = WC()->countries->get_base_country();
		$country_name = $countries[ $country_code ] ?? '';
		$state_code   = WC()->countries->get_base_state();
		$state_name   = $states[ $country_code ][ $state_code ] ?? '';

		$address_els = [
			WC()->countries->get_base_address(),
			WC()->countries->get_base_address_2(),
			WC()->countries->get_base_city(),
			$state_name,
			$country_name
		];

		$address_els = array_filter( $address_els );

		return implode( ' - ', $address_els );
	}

	public function get_layered_nav_chosen_attributes() {
		$chosen_attributes = [];
		if ( ! empty( $_REQUEST ) ) {
			foreach ( $_REQUEST as $key => $value ) {
				if ( 0 === strpos( $key, 'filter_' ) ) {
					$attribute    = wc_sanitize_taxonomy_name( str_replace( 'filter_', '', $key ) );
					$taxonomy     = wc_attribute_taxonomy_name( $attribute );
					$filter_terms = ! empty( $value ) ? explode( ',', wc_clean( wp_unslash( $value ) ) ) : array();

					if ( empty( $filter_terms ) || ! taxonomy_exists( $taxonomy ) || ! wc_attribute_taxonomy_id_by_name( $attribute ) ) {
						continue;
					}

					$query_type = ! empty( $_REQUEST[ 'query_type_' . $attribute ] ) && in_array( $_GET[ 'query_type_' . $attribute ], [
						'and',
						'or'
					], true )
						? wc_clean( wp_unslash( $_GET[ 'query_type_' . $attribute ] ) ) : '';

					$chosen_attributes[ $taxonomy ]['terms']      = array_map( 'sanitize_title', $filter_terms ); // Ensures correct encoding.
					$chosen_attributes[ $taxonomy ]['query_type'] = $query_type ? $query_type : apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
				}
			}
		}

		return $chosen_attributes;
	}

	public function get_print_layouts() {
		$replace = [
			'{admin_email}'   => get_option( 'admin_email' ),
			'{site_title}'    => get_bloginfo( 'name' ),
			'{site_url}'      => site_url(),
			'{home_url}'      => home_url(),
			'{store_address}' => $this->get_store_address(),
		];

		$print_header = str_replace( array_keys( $replace ), array_values( $replace ), $this->get_param( 'layout_header' ) );
		$print_footer = str_replace( array_keys( $replace ), array_values( $replace ), $this->get_param( 'layout_footer' ) );

		return [
			'header' => wpautop( $print_header ),
			'footer' => wpautop( $print_footer ),
		];
	}

	public function get_detail_step( $step ) {
		if ( ! empty( $step ) && is_array( $step ) ) {
			foreach ( $step as $pid => $data ) {
				$product = wc_get_product( $pid );

				if ( ! $product ) {
					unset( $step[ $pid ] );
					continue;
				}

				$img_id = $product->get_image_id();
				$image  = $img_id ? wp_get_attachment_url( $img_id ) : wc_placeholder_img_src();

				if ( ! is_array( $step[ $pid ] ) ) {
					$step[ $pid ] = [];
				}
				$price_decimals = wc_get_price_decimals();
//				$product_price = preg_replace('/<span class="screen-reader-text"[^>]*>(.*)<\/span><i/is','<i',$product->get_price_html());
//				$product_price = preg_replace('/<span class="screen-reader-text"[^>]*>(.*)<\/span>/is','',$product_price);
				if ( $product->is_on_sale() ) {
					$price_string = wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) );
				} else {
					$price_string = wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
				}

				$product_price            = apply_filters( 'woopb_ajax_get_step_product_price', wc_price( apply_filters( 'woopb_added_sub_price', round( $price_string, $price_decimals ), $pid, $data ) ), $product );
				$product_title            = apply_filters( 'woopb_ajax_get_step_product_title', VI_WPRODUCTBUILDER_Data::get_product_name( $product, $data ), $product );
				$product_desc             = apply_filters( 'woopb_ajax_get_step_product_desc', $product->get_short_description(), $product );
				$step[ $pid ]['price']    = $product_price;
				$step[ $pid ]['title']    = $product_title;
				$step[ $pid ]['desc']     = $product_desc;
				$step[ $pid ]['image']    = $image;
				$step_pid_quantity        = isset( $step[ $pid ]['quantity'] ) && $step[ $pid ]['quantity'] ? absint( $step[ $pid ]['quantity'] ) : 1;
				$step[ $pid ]['subtotal'] = wc_price( round( $price_string, $price_decimals ) * $step_pid_quantity ) . $product->get_price_suffix();
			}
		}

		return $step;
	}

	/**
	 * $product->get_name() only returns variation name with attributes if number of attributes is smaller than 3
	 * Search generate_product_title for more details
	 *
	 * @param $product WC_Product
	 * @param $detail
	 *
	 * @return string
	 */
	public static function get_product_name( $product, $detail ) {
		$attributes    = [];
		$product_title = $product->get_title();
		if ( $product->is_type( 'variation' ) ) {
			if ( isset( $detail['attributes'] ) ) {
				$selected_attributes = $detail['attributes'];
			} else {
				$selected_attributes = [];
				foreach ( $detail as $key => $value ) {
					if ( strpos( $key, 'attribute_' ) === 0 ) {
						$selected_attributes[ $key ] = $value;
					}
				}
			}
			if ( ! empty( $selected_attributes ) ) {
				foreach ( $selected_attributes as $name => $value ) {
					$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

					if ( taxonomy_exists( $taxonomy ) ) {
						// If this is a term slug, get the term's nice name.
						$term = get_term_by( 'slug', $value, $taxonomy );
						if ( ! is_wp_error( $term ) && $term && $term->name ) {
							$value = $term->name;
						}

						$attributes[] = $value;
					} elseif ( strpos( $name, 'attribute_' ) !== false ) {
						$attributes[] = $value;
					}
				}
				if ( ! empty( $attributes ) ) {
					$product_title .= ' (' . implode( ', ', $attributes ) . ')';
				}
			}
		}

		return $product_title;
	}
}
