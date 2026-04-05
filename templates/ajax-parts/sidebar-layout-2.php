<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 ** @var $post_id
 ** @var $settings
 ** @var $args_more

 */
?>
<div class="woopb-sidebar-panel woopb-sidebar-layout-2">
    <div class="woopb-added-products-total">
            <span class="woopb-added-products-label">
                <?php esc_html_e('Estimated Costs:', 'woocommerce-product-builder'); ?>
            </span>
        <span class="woopb-added-products-value">
				<?php echo wc_price(0);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
    </div>
    <?php
    $use_icon = $settings->get_param('button_icon');
    $use_only_icon_class = $use_icon ? 'woopb-icons-flex' : '';
    ?>
    <div class="woopb-sidebar-groups-button">
        <div class="woopb-sidebar-group-button group-tool-button">
            <div class="woopb-tool-buttons <?php echo esc_attr($use_only_icon_class) ?>">
                <?php
                if ($settings->get_param('get_short_share_link') || $settings->get_param('share_link')) {
                    ?>
                    <div class="woopb-get-share-link woopb-button">
                        <?php
                        if ($use_icon) {
                            ?>
                            <span class="woopb-icon woopb-icon-svg woopb-icon-share-new"> </span>
                            <?php
                        } else {
                            esc_html_e('Get share link', 'woocommerce-product-builder');
                        }
                        ?>
                    </div>
                    <?php
                }

                if ($settings->enable_email()) {
                    ?>
                    <div class="woopb-send-to-friend woopb-button">
                        <?php
                        if ($use_icon) {
                            ?>
                            <span class="woopb-icon woopb-icon-svg woopb-icon-email-new"> </span>
                            <?php
                        } else {
                            esc_html_e('Send to your friends', 'woocommerce-product-builder');
                        }
                        ?>
                    </div>
                    <?php
                }

                if ($settings->get_param('print_button')) {
                    ?>
                    <div class="woopb-button woopb-print-button">
                        <?php
                        if ($use_icon) {
                            ?>
                            <span class="woopb-icon woopb-icon-svg woopb-icon-print-new"> </span>
                            <?php
                        } else {
                            esc_html_e('Print', 'woocommerce-product-builder');
                        }
                        ?>
                    </div>
                    <?php
                }

                if ($settings->get_param('download_pdf')) {
                    ?>
                    <div class="woopb-button woopb-download-pdf-button">
                        <?php
                        if ($use_icon) {
                            ?>
                            <span class="woopb-icon woopb-icon-svg woopb-icon-file-pdf-new"> </span>
                            <?php
                        } else {
                            esc_html_e('Download PDF', 'woocommerce-product-builder');
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
        <div class="woopb-sidebar-group-button group-action-button">
            <?php
            if ($args_more['remove_all_button']) {
                ?>
                <div class="woopb-remove-all woopb-button">
                    <?php esc_html_e('Remove all', 'woocommerce-product-builder'); ?>
                </div>
                <?php
            }
            ?>
            <div class="woopb-add-products-to-cart woopb-button-primary woopb-button">
                <?php esc_html_e('Add to cart', 'woocommerce-product-builder'); ?>
            </div>

        </div>
    </div>


</div>