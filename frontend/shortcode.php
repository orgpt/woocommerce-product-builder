<?php

class VI_WPRODUCTBUILDER_FrontEnd_Shortcode {
	public $settings;
	public static $woopb_id;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_shortcode( 'woocommerce_product_builder', array( $this, 'shortcode' ) );
	}

	public function init() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
	}

	public function shortcode( $atts ) {

		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		global $post, $woopb_id;
		$page_id = $post->ID;

		if ( $post->post_type !== 'page' ) {
			return false;
		}

		extract( shortcode_atts( array( 'id' => '', ), $atts ) );

		$id = absint( $id );

		$custom_post = get_post( $id );

		if ( ! $custom_post || $custom_post->post_type !== 'woo_product_builder' ) {
			return false;
		}

		$woopb_id       = $id;
		self::$woopb_id = $id;
		if ( wc()->session ) {
			$this->settings->set_data_storage( "woopb_{$page_id}_params", [ 'use_shortcode' => true, 'id' => $id ] );
//			wc()->session->set( "woopb_{$page_id}_params", [ 'use_shortcode' => true, 'id' => $id ] );
		}

		$this->settings->enqueue_scripts();
		add_action( 'wp_footer', array( $this, 'load_photoswipe_template' ) );

		ob_start();

		wpb_get_template( 'single-product-builder.php', array( 'id' => $id ) );

		return ob_get_clean();
	}

	public function load_photoswipe_template() {
		$this->settings->load_photoswipe_template();
	}

}
