<?php
defined('ABSPATH') || exit;
/** @var $settings */

$add_button_text = $settings->get_param('add_button_text');

$class_button = 'woopb-button-icon';
$button_type = $settings->get_param('load_step_button_type');
if (!empty($button_type)) {
    $class_button = 'woopb-button-' . $button_type;
}
?>
<div class="woopb-product-buttons">
    <span class="woopb-load-step woopb-button woopb-button-primary <?php echo esc_attr($class_button); ?>"><?php echo esc_html($add_button_text); ?></span>
</div>
