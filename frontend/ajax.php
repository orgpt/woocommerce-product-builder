<?php

defined( 'ABSPATH' ) || exit;

class VI_WPRODUCTBUILDER_FrontEnd_Ajax {
	public $class_data;
	public $post_id;
	public $session_key;
	public $step;
	public $search;
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
//		add_action( 'wp_ajax_woopb_action', [ $this, 'ajax' ] );
//		add_action( 'wp_ajax_nopriv_woopb_action', [ $this, 'ajax' ] );
		add_action( 'wp_ajax_woocommerce_woopb_action' , array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_woopb_action' , array( $this, 'ajax' ) );
		// WC AJAX can be used for frontend ajax requests
		add_action( 'wc_ajax_woopb_action' , array( $this, 'ajax' ) );
	}

	public function ajax() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(wc_clean(wp_unslash($_POST['nonce'])), 'woopb_nonce')){
            wp_send_json(array(
	            'success' => false,
                'message' => 'nonce error',
                'nonce' => wc_clean(wp_unslash($_POST['nonce'])),
                'nonce1' => $_POST['nonce'],
                'current_nonce' => wp_create_nonce( 'woopb_nonce' ),
            ));
        }

		$action = ! empty( $_POST['_action'] ) ? sanitize_text_field( $_POST['_action'] ) : '';

		if ( ! method_exists( $this, $action ) ) {
			wp_send_json_error( esc_html__( 'Method is not exist', 'woocommerce-product-builder' ) );
		}

		if ( empty( $_POST['woopb_id'] ) ) {
			wp_send_json_error( esc_html__( 'Builder is not exist', 'woocommerce-product-builder' ) );
		}
		add_filter( 'woocommerce_locate_template', function ( $template, $template_name ) {
			if ( $template_name === 'single-product/product-image.php' ) {
				$template = WC()->plugin_path() . '/templates/' . $template_name;
			}

			return $template;
		}, PHP_INT_MAX, 2 );
		global $post;
		$this->post_id     = absint( $_POST['woopb_id'] );
		$post              = get_post( $this->post_id );
		$this->step        = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : '';
		$this->session_key = "woopb_{$this->post_id}";
		$this->class_data  = VI_WPRODUCTBUILDER_Data::instance();
		$this->$action();

		wp_die();
	}

	public function load_step_products() {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce','woocommerce-product-builder' ] );
		}
		if ( isset( $_POST['step'] ) ) {
			$step        = absint( $_POST['step'] );
			$page        = absint( $_POST['page'] ?? 1 );
			$form_action = isset( $_POST['form_action'] ) ? esc_url_raw( wp_unslash( $_POST['form_action'] ) ) : '';
			set_query_var( 'step', $step );
			if ( ! empty( $_POST['search'] ) ) {
				$sort_by       = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
				$search        = sanitize_text_field( wp_unslash( $_POST['search'] ) );
				$post_per_page = $this->class_data->get_data( $this->post_id, 'product_per_page', 10 );

				$filtered    = $this->class_data->get_product_filters( $this->post_id, $step, '', false );
				$product_ids = $filtered->posts;

				$arg = array(
					'limit'    => $post_per_page,
					'status'   => 'publish',
					'include'  => $product_ids,
					'paged'    => $page,
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

				if ( empty( $products ) ) {
					wp_send_json_success( [ 'message' => esc_html__( 'No product was found', 'woocommerce-product-builder' ) ] );
				}
				global $product;
                global $post;
				ob_start();

				foreach ( $products as $product ) {
					$post = get_post($product->get_id());
					wpb_get_template( 'ajax-parts/product-modal.php', [
						'post_id' => $this->post_id,
						'step'    => $step
					] );
				}
				$html = ob_get_clean();

			} else {
				$query = $this->class_data->get_product_filters( $this->post_id, $step, $page );

				if ( ! $query ) {
					wp_send_json_success( [ 'message' => esc_html__( 'No product was found', 'woocommerce-product-builder' ) ] );
				}

				$pids     = $query->posts;
				$max_page = $query->max_num_pages;
				global $product;
                global $post;
				ob_start();
				if ( ! empty( $pids ) ) {
					foreach ( $pids as $pid ) {
						$product = wc_get_product( $pid );
                        $post = get_post($pid);
						if ( ! $product ) {
							continue;
						}
						wpb_get_template( 'ajax-parts/product-modal.php', [
							'post_id' => $this->post_id,
							'step'    => $step
						] );
					}
				}
				$html = ob_get_clean();
			}

			global $post , $woopb_post_id;
			$tmp_post = $post;
			$post =get_post( $this->post_id );
			VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id = $woopb_post_id = $this->post_id;
			ob_start();
			if ( is_active_sidebar( 'woopb-sidebar' ) ) {
				dynamic_sidebar( 'woopb-sidebar' );
				?>
                <div>
                    <button type="button" class="woopb-button woopb-clear-filter">
						<?php esc_html_e( 'Clear', 'woocommerce-product-builder' ); ?>
                    </button>
                </div>
                <div class="woopb-close-filter">&times;</div>
				<?php
			}
			$filter = ob_get_clean();
            $post = $tmp_post;
			wp_send_json_success( [ 'html' => $html, 'filter' => $filter, 'maxPage' => $max_page ] );
		}
	}

	public function get_session() {
		$steps = $this->settings->get_data_storage( $this->session_key );
//		$steps = wc()->session->get( $this->session_key );

		if ( ! empty( $steps ) && is_array( $steps ) ) {
			foreach ( $steps as $key => $step ) {
				if ( ! empty( $step ) && is_array( $step ) ) {

					$steps[ $key ] = $this->get_detail_step( $step );
				}
			}
		}
		wp_send_json_success( [
			'steps'       => $steps,
			'total'       => $this->get_session_total(),
			'checkDepend' => $this->check_depend()
		] );
	}

	public function get_session_total() {
		$total         = 0;
		$selected_data = $this->settings->get_data_storage( $this->session_key );
//		$selected_data = wc()->session->get( $this->session_key );

		if ( empty( $selected_data ) || ! is_array( $selected_data ) ) {
			return wc_price( $total );
		}

		foreach ( $selected_data as $step ) {
			if ( empty( $step ) || ! is_array( $step ) ) {
				continue;
			}
			foreach ( $step as $pid => $item ) {
				if ( ! $pid || empty( $item['quantity'] ) ) {
					continue;
				}
                $product = wc_get_product( $pid );
                $price_string = 0;
                $price_decimals = wc_get_price_decimals();
                if ( $product ) {
                    if($product->is_on_sale()){
                        $price_string = wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) );
                    }else{
                        $price_string = wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
                    }
                    $total += floatval( $item['quantity']) * apply_filters('woopb_added_sub_price',round($price_string,$price_decimals)  ,$pid,$item);
				}
			}
		}

		return wc_price( $total );
	}

	public function add_item() {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce', 'woocommerce-product-builder' ] );
		}
		if ( isset( $_POST['step'] ) ) {
			$step = absint( $_POST['step'] );

			$atc_data = htmlspecialchars_decode( wp_kses_post( wp_unslash( $_POST['data'] ) ) );
			parse_str( $atc_data, $data );
			if ( ! empty( $data ) && is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( in_array( $key, [ '_nonce'] )  ) {
						unset( $data[ $key ] );
					}
				}
			}
			if ( empty( $data['quantity'] ) ) {
				$data['quantity'] = 1;
			}
//			if ( ! WC()->session ) {
//				WC()->session = new WC_Session_Handler();
//				WC()->session->init();
//			}
			$selected_data = $this->settings->get_data_storage( $this->session_key );
//			$selected_data = wc()->session->get( $this->session_key );
//			error_log(print_r('add_item',true));
//			error_log(print_r($this->session_key,true));
//			error_log(print_r($selected_data,true));
			$selected_data = ! empty( $selected_data ) ? $selected_data : [];

			$pid = ! empty( $data['variation_id'] ) ? $data['variation_id'] : $data['product_id'];
			$enable_multi_select = $this->class_data->get_data( $this->post_id, 'enable_multi_select' );

			if ( ! $enable_multi_select ) {
				$selected_data[ $step ] = [];
			}

			if ( ! empty( $_POST['replace'] ) ) {
				$replace_id = absint( $_POST['replace'] );
				unset( $selected_data[ $step ][ $replace_id ] );
			}

			$selected_data[ $step ][ $pid ] = $data;
			$this->settings->set_data_storage( $this->session_key, $selected_data );
//			wc()->session->set( $this->session_key, $selected_data );

			$total = $this->get_session_total();
			$step  = $this->get_detail_step( $selected_data[ $step ] );

			wp_send_json_success( [ 'step' => $step, 'total' => $total, 'checkDepend' => $this->check_depend() ] );
		}
	}

	public function check_depend() {
		$params       = get_post_meta( $this->post_id, 'woopb-param', true );
		$check_depend = [];

		if ( ! isset( $params['enable_compatible'] ) ) {
			return $check_depend;
		}

		$selected_data      = $this->settings->get_data_storage( $this->session_key );
//		$selected_data      = wc()->session->get( $this->session_key );
		$attr_compatible    = $params['attr_compatible'] ?? array();
		$product_compatible = $params['product_compatible'] ?? array();

		if ( ! empty( $selected_data ) ) {
			foreach ( $selected_data as $step => $selected ) {
				if ( empty( $selected ) ) {
					continue;
				}

				$step_index = $step - 1;

				if ( empty( $product_compatible[ $step_index ] ) ) {
					continue;
				}

				$depend_steps = $product_compatible[ $step_index ];
				if ( ! empty( $depend_steps ) && is_array( $depend_steps ) ) {
					$added_products = [];

					foreach ( $depend_steps as $depend_step ) {
						$added_products = array_merge( $added_products, array_keys( $selected_data[ $depend_step + 1 ] ?? [] ) );
					}

					$added_products = array_unique( $added_products );

					if ( empty( $added_products ) ) {
						continue;
					}

					$list_attrs = [];
					foreach ( $added_products as $pid ) {
						$product = wc_get_product( $pid );
						if ( ! $product ) {
							continue;
						}

						if ( $product->get_type() == 'variation' ) {
							$parent_id = $product->get_parent_id();
							$product   = wc_get_product( $parent_id );
						}

						$attrs = $product->get_attributes();
						if ( ! empty( $attrs ) && is_array( $attrs ) ) {
							foreach ( $attrs as $key => $attr ) {
								if ( is_object( $attr ) && $attr->get_id() ) {
									$list_attrs[ $key ] = isset( $list_attrs[ $key ] ) ? array_unique( array_merge( $list_attrs[ $key ], $attr->get_options() ) ) : $attr->get_options();
								}
							}
						}
					}

					$depend_attrs   = $attr_compatible[ $step_index ];
					$term_ids       = [];
					$new_list_attrs = [];

					if ( ! empty( $depend_attrs ) && is_array( $depend_attrs ) ) {
						foreach ( $depend_attrs as $attr_id ) {

							$slug = wc_get_attribute( $attr_id )->slug;

							if ( ! empty( $list_attrs[ $slug ] ) ) {
								$new_list_attrs = array_merge( $new_list_attrs, $list_attrs[ $slug ] );
							}

							$terms = get_terms( array( 'taxonomy' => $slug, 'hide_empty' => false, ) );

							foreach ( $terms as $term ) {
								$term_ids[] = $term->term_id;
							}
						}

						$new_list_attrs = array_intersect( $term_ids, $new_list_attrs );
					}

					foreach ( $selected as $pid => $detail ) {
						$product = wc_get_product( $pid );
						if ( ! $product ) {
							continue;
						}

						if ( $product->get_type() === 'variation' ) {
							$parent_id = $product->get_parent_id();
							$product   = wc_get_product( $parent_id );
						}

						$attributes = $product->get_attributes();
						$check      = [];

						if ( ! empty( $attributes ) && is_array( $attributes ) ) {
							foreach ( $attributes as $attr ) {
								$attr_id = $attr->get_id();
								if ( in_array( $attr_id, $depend_attrs ) ) {
									$options   = $attr->get_options();
									$intersect = array_intersect( $new_list_attrs, $options );
									$check[]   = ! empty( $intersect ) ? 1 : 0;
								}
							}
						}

						if ( count( $check ) !== array_sum( $check ) ) {
							$check_depend[ $step ][] = $pid;
						}
					}
				}
			}
		}

		return $check_depend;
	}

	public function get_detail_step( $step ) {
		return $this->class_data->get_detail_step( $step );
	}

	public function change_quantity() {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce','woocommerce-product-builder' ] );
		}
		if ( isset( $_POST['product_id'], $_POST['quantity'] ) ) {
			$pid      = absint( $_POST['product_id'] );
			$quantity = absint( $_POST['quantity'] );
			$session  = $this->settings->get_data_storage( $this->session_key );
//			$session  = wc()->session->get( $this->session_key );
            $product = wc_get_product($pid);
            if ($product->get_max_purchase_quantity() > 0 && $quantity > $product->get_max_purchase_quantity()){
                $quantity = $product->get_max_purchase_quantity();
            }elseif ($quantity < $product->get_min_purchase_quantity()){
                $quantity = $product->get_min_purchase_quantity();
            }
			if ( isset( $session[ $this->step ][ $pid ]['quantity'] ) ) {
				$session[ $this->step ][ $pid ]['quantity'] = $quantity;
				$this->settings->set_data_storage( $this->session_key, $session );
//				wc()->session->set( $this->session_key, $session );

				$total = $this->get_session_total();
				$step  = $this->get_detail_step( $session[ $this->step ] );

				wp_send_json_success( [ 'step' => $step, 'total' => $total ,'qty' => $quantity] );
			}
		}
	}

	public function remove_product() {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce','woocommerce-product-builder' ] );
		}
		if ( isset( $_POST['product_id'] ) ) {
			$pid     = absint( $_POST['product_id'] );
			$session = $this->settings->get_data_storage( $this->session_key );
//			$session = wc()->session->get( $this->session_key );

			if ( isset( $session[ $this->step ][ $pid ] ) ) {
				unset ( $session[ $this->step ][ $pid ] );

				$this->settings->set_data_storage( $this->session_key, $session );
//				wc()->session->set( $this->session_key, $session );

				$total = $this->get_session_total();
				$step  = $this->get_detail_step( $session[ $this->step ] );

				wp_send_json_success( [ 'step' => $step, 'total' => $total, 'checkDepend' => $this->check_depend() ] );
			}
		}
	}

	public function search_product() {
		$this->load_step_products();
	}

	public function add_search( $where ) {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			return $where;
		}
		if ( ! empty( $_POST['search'] ) ) {
			global $wpdb;
			$search = $wpdb->esc_like( sanitize_text_field( $_POST['search'] ) );
			$where  .= " AND ({$wpdb->posts}.post_title LIKE '%{$search}%' OR {$wpdb->posts}.post_excerpt LIKE '%{$search}%' OR {$wpdb->posts}.post_content LIKE '%{$search}%')";
		}

		return $where;
	}

	public function sort_product() {
		$this->load_step_products();
	}

	public function add_to_cart() {
		if ( isset( $_REQUEST['woopb_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_nonce'] ), 'woopb_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce','woocommerce-product-builder' ] );
		}
		$session_data = $this->settings->get_data_storage( $this->session_key );
//		$session_data = wc()->session->get( $this->session_key );

		if ( empty( $session_data ) || ! is_array( $session_data ) ) {
			wp_send_json_error( [ 'message' => 'Can not find product builder data','woocommerce-product-builder' ] );
		}
		$param = get_post_meta( $this->post_id, 'woopb-param', true );
        if (!empty($param['require_product'])){
            $tab_title = $param['tab_title'] ?? array();
            $step_total = is_array($tab_title) ? count($tab_title) : 0;
            if ($step_total > count($session_data)){
	            wp_send_json_error( [ 'message' => esc_html__('You must choose a product at all step','woocommerce-product-builder') ] );
            }
        }
        $global_request= $_REQUEST;
        $global_post= $_POST;
		foreach ( $session_data as $step ) {
			if ( empty( $step ) || ! is_array( $step ) ) {
				continue;
			}

			foreach ( $step as $pid => $item ) {
				if ( empty( $item['quantity'] ) ) {
					continue;
				}
                if (empty($item['add-to-cart'])){
                    $item['add-to-cart'] = $pid;
                }
				if(isset( $_REQUEST['woopb_step_fee'] )){
					$item['woopb_step_fee'] = sanitize_text_field( $_REQUEST['woopb_step_fee'] );
				}
				$_REQUEST = $_POST = $item;
				WC_Form_Handler::add_to_cart_action();
                /*
                $variations = [];
				foreach ( $item as $key => $value ) {
					if ( 'attribute_' !== substr( $key, 0, 10 ) ) {
						continue;
					}

					$variations[ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
				}

				if ( ! empty( $variations ) ) {
					WC()->cart->add_to_cart( $pid, $item['quantity'], $pid, $variations );
				} else {
					WC()->cart->add_to_cart( $pid, $item['quantity'] );
				}*/
			}
		}
		$_REQUEST = $global_request;
        $_POST = $global_post;

		if ( $this->class_data->get_param( 'remove_session' ) ) {
			$this->settings->set_data_storage( $this->session_key, [] );
//			wc()->session->set( $this->session_key, [] );
		}

		wp_send_json_success( [ 'url' => wc_get_cart_url() ] );
	}

	public function get_share_link() {
	    check_ajax_referer('woopb_nonce', 'woopb_nonce');
		$data    = $this->settings->get_data_storage( $this->session_key );
//		$data    = WC()->session->get( $this->session_key );
		$page_id = ! empty( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : '';

		if ( $this->class_data->get_param( 'get_short_share_link' ) ) {
			$sl        = VI_WPRODUCTBUILDER_Short_Share_Link::instance();
			$share_url = $sl->build_short_link( $data, $this->post_id, $page_id );
		} else {
			$data      = array( 'page_id' => $page_id, 'woopb_id' => $this->post_id, 'data' => $data );
			$share     = base64_encode( wp_json_encode( $data ) );
			$share     = strtr( $share, '+/=', '-_,' );
			$share_url = site_url( "?woopb_share={$share}" );
		}

		wp_send_json_success( [ 'shareUrl' => $share_url ] );
	}

	public function remove_all() {
		$this->settings->set_data_storage( $this->session_key, [] );
//		wc()->session->set( $this->session_key, [] );
		$this->get_session();
	}

	public function download_pdf() {
		include_once VI_WPRODUCTBUILDER_VENDOR . 'autoload.php';
		$html = $this->get_pdf_html();

		if ( empty( $html ) ) {
			wp_send_json_error( '', 201 );
		}

		try {
			ob_start();
			wpb_get_template( 'ajax-parts/print-style.php' );
			$print_style = ob_get_clean();
			$mpdf = new \Mpdf\Mpdf( [
				'tempDir'  => WP_CONTENT_DIR . '/cache',
				'fontdata' => [
					'dejavusanscondensed' =>
						[
							'R'          => 'DejaVuSansCondensed.ttf',
							'B'          => 'DejaVuSansCondensed-Bold.ttf',
							'I'          => 'DejaVuSansCondensed-Oblique.ttf',
							'BI'         => 'DejaVuSansCondensed-BoldOblique.ttf',
							'useOTL'     => 255,
							'useKashida' => 75,
						]
				]
			] );

			$mpdf->curlAllowUnsafeSslRequests = true;
			$dest = \Mpdf\Output\Destination::DOWNLOAD;

			$mpdf->WriteHTML( $print_style, 1 );
			$mpdf->WriteHTML( $html, 2 );
			$mpdf->Output( 'ProductBuilder.pdf', $dest );
		} catch ( \Exception $e ) {
			echo wp_kses_post( $e->getMessage() );
		}

		wp_die();
	}

	public function get_pdf_html() {
		$html      = '';
		$total_qty = 0;
		$steps     = $this->settings->get_data_storage( $this->session_key );
//		$steps     = wc()->session->get( $this->session_key );
		$show_desc = $this->class_data->get_param( 'show_short_desc' );

		if ( ! empty( $steps ) && is_array( $steps ) ) {
			foreach ( $steps as $key => $step ) {

				if ( empty( $step ) || ! is_array( $step ) ) {
					continue;
				}

				$items = $this->get_detail_step( $step );

				if ( empty( $items ) ) {
					continue;
				}

				foreach ( $items as $item ) {
					$total_qty += $item['quantity'] ?? 0;
					$image     = $item['image'] ?? '';
					$desc      = $show_desc ? "<div><small>{$item['desc']}</small></div>" : '';
					$quantity  = $item['quantity'] ?? 1;

					$html .= "<tr>
                                <td class='woopb-print-img' >
                                    <img src='{$image}' width='100'>
                                </td>
                                <td class='woopb-print-title'>
                                    <div>{$item['title']}</div>
                                    {$desc}
                                </td>
                                <td class='woopb-print-quantity-col'>{$quantity}</td>
                                <td class='woopb-print-price-col'>{$item['price']}</td>
                                <td class='woopb-print-subtotal-col'>{$item['subtotal']}</td>
                            </tr>";
				}
			}
		}

		if ( $html ) {
			$layouts = $this->class_data->get_print_layouts();
			$total   = $this->get_session_total();

			$html = "<div id='woopb-print-frame'>
                        {$layouts['header']}
                        <table>
                            <thead>
                            <tr>
                                <th colspan='2' class='woopb-print-product-col'>" . esc_html__( 'Product', 'woocommerce-product-builder' ) . "</th>
                                <th class='woopb-print-quantity-col'>" . esc_html__( 'Quantity', 'woocommerce-product-builder' ) . "</th>
                                <th class='woopb-print-price-col'>" . esc_html__( 'Price', 'woocommerce-product-builder' ) . "</th>
                                <th class='woopb-print-subtotal-col'>" . esc_html__( 'Subtotal', 'woocommerce-product-builder' ) . "</th>
                            </tr>
                            </thead>
                            <tbody>
                            {$html}
                            </tbody>
                             <tfoot>
                                <tr>
                                    <th colspan='2'>" . esc_html__( 'Total', 'woocommerce-product-builder' ) . "</th>
                                    <th>{$total_qty}</th>
                                    <th colspan='2' class='woopb-print-footer-total'>{$total}</th>
                                </tr>
                            </tfoot>
                        </table>
                        {$layouts['footer']}
                    </div>";
		}

		return $html;
	}
}