<?php

use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class VI_WPRODUCTBUILDER_Elementor_Widget extends Widget_Base {

	public static $slug = 'woopb-product-builder-elementor-widget';

	public function get_name() {
		return 'woocommerce-product-builder';
	}

	public function get_title() {
		return __( 'Product Builder', 'woocommerce-product-builder' );
	}

	public function get_icon() {
		return "dashicons-before dashicons-feedback";
	}

	public function get_categories() {
		return [ 'woocommerce-elements' ];
	}

	protected function register_controls() {
		$this->_register_controls();
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'general',
			[
				'label' => __( 'General', 'woocommerce-multi-currency' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$ids     = $this->load_shortcode_list();
		$default = current( array_keys( $ids ) );
		$this->add_control(
			'shortcode_id',
			[
				'label'       => __( 'Select shortcode', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => $default,
				'options'     => $ids,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$id        = $settings['shortcode_id'] ? $settings['shortcode_id'] : '';
		$shortcode = "[woocommerce_product_builder id={$id}]";
		$shortcode = do_shortcode( shortcode_unautop( $shortcode ) );
		echo $shortcode;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function render_plain_content() {
		$settings  = $this->get_settings_for_display();
		$id        = $settings['shortcode_id'] ? $settings['shortcode_id'] : '';
		$shortcode = "[woocommerce_product_builder id={$id}]";
		echo $shortcode;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function load_shortcode_list() {
		$list  = [];
		$args  = [
			'numberposts' => - 1,
			'orderby'     => 'date',
			'order'       => 'desc',
			'post_type'   => 'woo_product_builder',
			'post_status' => 'publish'
		];
		$posts = get_posts( $args );
		if ( is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( is_object( $post ) ) {
					$list[ $post->ID ] = $post->post_title;
				}
			}
		}

		return $list;
	}

}

