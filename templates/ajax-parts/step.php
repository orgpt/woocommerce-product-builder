<?php
defined('ABSPATH') || exit;
/** @var $enable_multi_select */
/** @var $settings */
?>

<div class="woopb-step">
    <div class="woopb-step-header">
        <div class="woopb-step-title"></div>
        <div class="woopb-step-desc"></div>
    </div>
    <div class="woopb-products">
    </div>
    <div class="woopb-step-footer">
        <img class="woopb-step-icon" src="#">
        <?php
        if ($enable_multi_select) {
            $add_more_button_text = $settings->get_param('add_more_button_text');
            $class_button = 'woopb-button-icon';
            $button_type = $settings->get_param('load_step_button_type');

            if (!empty($button_type)) {
                $class_button = 'woopb-button-' . $button_type;
            }
            ?>
            <span class="woopb-load-step woopb-load-step-outer woopb-button woopb-button-primary <?php echo esc_attr($class_button); ?>"><?php echo esc_html($add_more_button_text); ?></span>
            <?php
        }
        ?>
    </div>
</div>