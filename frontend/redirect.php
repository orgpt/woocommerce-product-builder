<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Redirect {
	/**
	 * Stores chosen attributes
	 * @var array
	 */
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
		add_filter( 'template_include', array( $this, 'archive_template_function' ), 1 );
		add_filter( 'single_template', array( $this, 'single_template_function' ), 2 );
		add_filter( 'woopb_redirect_url', array( $this, 'add_query_arg_to_redirect_url' ), 2 );
		add_action( 'template_redirect', array( $this, 'check_each_step_require' ), 99 );
	}

	public function check_each_step_require() {
		$this->check_full_added();
	}

	/**
	 * Redirect to archive page
	 *
	 * @param $template_path
	 *
	 * @return string
	 */
	public function archive_template_function( $template_path ) {
		if ( get_post_type() == 'woo_product_builder' ) {
			do_action( 'woocommerce_product_builder_template_load' );
			if ( is_archive() ) {
				if ( $theme_file = locate_template( array( 'archive-product-builder.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = VI_WPRODUCTBUILDER_TEMPLATES . 'archive-product-builder.php';
				}
			}
		}

		return $template_path;
	}

	public function check_full_added() {
		global $post, $wp_query;
		$post_id = ! empty( $post->ID ) ? $post->ID : 0;

		if ( is_woopb_shortcode() ) {
			$params  = $this->settings->get_data_storage( "woopb_{$post->ID}_params" );
//			$params  = wc()->session->get( "woopb_{$post->ID}_params" );
			$post_id = ! empty( $params['id'] ) ? $params['id'] : $post_id;
		}

		$param = get_post_meta( $post_id, 'woopb-param', true );

		if ( isset( $param['require_product'] ) && $param['require_product'] ) {
			$session_id  = 'woopb_' . $post_id;
			$miss_step   = array();
			$count       = $sum_step = $target_step = $stop = 0;
			$added_array = $this->settings->get_data_storage( $session_id );
//			$added_array = WC()->session->get( $session_id );
			$sum_step    = is_array( $param['list_content'] ) ? count( $param['list_content'] ) : 0;
			$step        = get_query_var( 'step' );

			if ( is_array( $added_array ) ) {
				ksort( $added_array );
			}

			if ( $step > 1 || isset( $wp_query->query_vars['woopb_preview'] ) ) {
				if ( ! empty( $added_array ) ) {
					for ( $i = 1; $i <= $sum_step; $i ++ ) {
						if ( ! empty( $added_array[ $i ] ) ) {
							$count ++;
						} else {
							$miss_step[] = $i;
						}
					}
					$target_step = current( $miss_step );
				} else {
					$target_step = 1;
				}

				if ( $target_step && $target_step < $step ) {
					$url = add_query_arg( array( 'step' => $target_step, 'notice' => 1 ), get_the_permalink( $post->ID ) );
					wp_safe_redirect( $url );
					exit;
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * Register Custom Template
	 */
	public function single_template_function( $template_path ) {
		$this->check_full_added();
		if ( get_post_type() == 'woo_product_builder' ) {
			do_action( 'woocommerce_product_builder_template_load' );
			if ( is_single() ) {
				if ( $theme_file = locate_template( array( VI_WOOPB_TEMPLATE_PATH . '/single-product-builder.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = VI_WPRODUCTBUILDER_TEMPLATES . 'single-product-builder.php';
				}
			}
		}

		return $template_path;
	}

	public function add_query_arg_to_redirect_url( $arg ) {
		if ( isset( $_REQUEST['woopb_field_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['woopb_field_nonce'] ), 'woopb_field_nonce' ) ) {
			return $arg;
		}
		foreach ( array( 'min_price', 'max_price', 'name_filter', 'rating_filter' ) as $item ) {
			$value = isset( $_GET[ $item ] ) ? sanitize_text_field( $_GET[ $item ] ) : '';
			if ( $value ) {
				$arg[ $item ] = $value;
			}
		}

		$settings = get_option( 'widget_woopb_layered_nav' );
		if ( is_array( $settings ) && count( $settings ) ) {
			foreach ( $settings as $type ) {
				$attribute = isset( $type['attribute'] ) ? 'filter_' . $type['attribute'] : '';
				if ( $attribute ) {
					$value = isset( $_GET[ $attribute ] ) ? sanitize_text_field( $_GET[ $attribute ] ) : '';
					if ( $value ) {
						$arg[ $attribute ] = $value;
					}
				}
			}
		}

		return $arg;
	}
}


