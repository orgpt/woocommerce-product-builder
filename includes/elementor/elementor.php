<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
	return;
}
// The Widget_Base class is not available immediately after plugins are loaded, so
// we delay the class' use until Elementor widgets are registered
add_action( 'elementor/widgets/register', function () {
	require_once( 'widget.php' );

	$drop_down_widget = new VI_WPRODUCTBUILDER_Elementor_Widget();

// Let Elementor know about our widget

	Elementor\Plugin::instance()->widgets_manager->register( $drop_down_widget );
} );