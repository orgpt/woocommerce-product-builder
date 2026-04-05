<?php
defined('ABSPATH') || exit;
?>
<div class="woopb-product">
    <div class="woopb-mobile">
        <div class="woopb-product-title-wrapper">
            <div class="woopb-product-title"></div>
            <div class="woopb-depend-notice"></div>
        </div>
    </div>

    <div class="woopb-desktop">
        <div class="woopb-product-thumbnail">
            <img src="#"/>
        </div>
        <div class="woopb-product-title-wrapper">
            <div class="woopb-product-title"></div>
            <div class="woopb-depend-notice"></div>
        </div>

        <div class="woopb-product-quantity">
            <?php
            if ($qty_field) {
                ?>
                <input type="number" class="woopb-product-quantity-value" min="1" step="1">
                <?php
            } else {
            }
            ?>
        </div>

        <div class="woopb-product-price price">
        </div>

        <div class="woopb-product-buttons">
            <span class="woopb-product-edit"></span>
            <span class="woopb-product-remove"></span>
        </div>
    </div>
</div>