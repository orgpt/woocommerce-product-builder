<?php
/**
 * Plugin Name: Woocommerce Product Builder Premium
 * Plugin URI: https://villatheme.com/extensions/woocommerce-product-builder/
 * Description: Increases sales with Building product configuration for your online store. Help build a complete product from small components
 * Version: 2.3.7
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Requires Plugins: woocommerce
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Elementor tested up to: 3.6.4
 * WC requires at least: 7.0
 * WC tested up to: 10.3
 * Copyright 2017-2025 VillaTheme.com. All rights reserved.
 *
 * Text Domain: woocommerce-product-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VI_WPRODUCTBUILDER_VERSION', '2.3.7' );
/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! class_exists( 'VI_WPRODUCTBUILDER' ) ) {

	class VI_WPRODUCTBUILDER {
		public function __construct() {
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
			//compatible with 'High-Performance order storage (COT)'
			add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
			add_action( 'plugins_loaded', array( $this, 'check_environment' ) );
		}
		public function check_environment() {
			if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
				include_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-product-builder" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . 'support.php';
			}
			$environment = new VillaTheme_Require_Environment( [
					'plugin_name'     => 'Woocommerce Product Builder',
					'php_version'     => '7.0',
					'wp_version'      => '5.0',
					'require_plugins' => [
                        [
                            'slug' => 'woocommerce',
                            'name' => 'WooCommerce',
							'defined_version' => 'WC_VERSION',
                            'version' => '7.0',
                        ],
					]
				]
			);
			if ( $environment->has_error() ) {
				return;
			}
			require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-product-builder" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR  . "define.php";
		}
		public function before_woocommerce_init() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}


		public function install() {
			flush_rewrite_rules();
		}

		public function uninstall() {
			flush_rewrite_rules();
		}
	}

	new VI_WPRODUCTBUILDER();
}