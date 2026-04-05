jQuery(document).ready(function ($) {
    'use strict';

    $(document).on('keydown', function (e) {
        if (e.keyCode === 13 && $(e.target).is('.wpb-search-field')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('.woopb-search-button').trigger('click');
        }
    });

    $('.woopb-compatible-field').select2({
        placeholder: "Select depend steps",
    });
    $('.woopb-attr-compatible-field').select2({
        placeholder: "Select depend attributes",
    });

    $('.woopb-sortable').sortable();

    // Color picker
    jQuery('.color-picker').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            var ele = jQuery(this).data('ele');
            if (ele == 'highlight') {
                jQuery('#message-purchased').find('a').css({'color': ui.color.toString()});
            } else if (ele == 'textcolor') {
                jQuery('#message-purchased').css({'color': ui.color.toString()});
            } else {
                jQuery('#message-purchased').css({backgroundColor: ui.color.toString()});
            }
        },
        hide: true,
        border: true
    }).click(function () {
        jQuery('.iris-picker').hide();
        jQuery(this).closest('td').find('.iris-picker').show();
    });
    jQuery('body').click(function () {
        jQuery('.iris-picker').hide();
    });
    jQuery('.color-picker').click(function (event) {
        event.stopPropagation();
    });
    jQuery('select.vi-ui.dropdown').dropdown();
    /*End setup tab*/
    jQuery('.vi-ui.checkbox').checkbox();
    jQuery('.vi-ui.radio').checkbox();

    /*Check item added*/
    var tab_id = jQuery('.menu .item').first().attr('data-tab');
    woo_product_buider.check_added(tab_id);

    /*Clear all compatible select*/
    woo_product_buider.compatible_remove_all();

    /*Product Configuration*/
    woo_product_buider.init();
    woo_product_buider.ajax_search();

    $('#post').on('submit', function(e) {
            // Nếu là post type woo_product_builder mới xử lý
           
            const postType = $('#post_type').val();
            if (postType === 'woo_product_builder') {
                // Lấy tất cả input có name bắt đầu bằng 'woopb-param'
            const inputs = $('[name^="woopb-param"]', this).serializeArray();

            let data = {};


            inputs.forEach(item => {
                // item.name = "woopb-param[tab_title][first]"
                // item.value = "Some value"
                const name = item.name.replace(/^woopb-param\[/, '').replace(/\]$/g, '');
                const keys = name.split(']['); // ["tab_title", "first"]

                // Gán vào object lồng nhau
                let ref = data;
                keys.forEach((key, i) => {
                    if (i === keys.length - 1) {
                        ref[key] = item.value;
                    } else {
                        if (!ref[key]) ref[key] = {};
                        ref = ref[key];
                    }
                });
            });
            let tabs = data.tab_title;
            let list_content = data.list_content;
            console.log(list_content);
        
            let is_valid = true;
            let tab_empty = [];
            if(list_content) {
            for (let index in tabs) {
                if (Object.prototype.hasOwnProperty.call(tabs, index)) {
                    const tab = tabs[index];
                        if (typeof list_content == undefined || typeof list_content[index] == 'undefined') {
                            is_valid = false;
                            tab_empty.push(tab);
                        }
                    }
                }
            }else {
                is_valid = false;
            }


            if(!is_valid) {
                let confirm_message;
                if(tab_empty.length == 0) {
                    confirm_message  = _woopb_params.all_step_empty_message;
                }else {
                    let step_empty = tab_empty.join(', ');
                    confirm_message  = _woopb_params.has_step_empty_message;
                    confirm_message = confirm_message.replace('%step%', step_empty);
                }
                let is_confirm = confirm(confirm_message);
                if(!is_confirm) {
                    e.preventDefault();
                    return false;
                }
                
            }
            }
        });
});

var woo_product_buider = {
    init: function () {
        this.tab_init();
        this.open_modal();
        this.add_item();
        this.remove_item();
        this.addStepIcon();
    },

    /**
     * Reinit tab
     */
    tab_init: function () {
        /*Init tab */
        jQuery('.menu .item').unbind();
        jQuery('.vi-ui.tabular.menu .item').vi_tab({
            history: true,
            historyType: 'hash'
        });
        jQuery('.menu .item').on('click', function () {
            /*Check item added*/
            var tab_id = jQuery(this).attr('data-tab');
            woo_product_buider.check_added(tab_id);
        });

        /*Add new tab*/
        jQuery('.woopb-add-tab').unbind();
        jQuery('.woopb-add-tab').on('click', function () {
            var tab_id = Date.now();
            var title = prompt(_woopb_params.tab_title);
            if (title == null || title == '') {
                return;
            }
            /*Menu*/
            var tab_data = jQuery('.woopb-tabs .menu a:first-child').clone();
            tab_data.find('.woopb-tab-title').text(title);
            tab_data.addClass('active').attr('data-tab', tab_id).find('input').val(title);
            jQuery('.woopb-tabs .menu').find('a').removeClass('active');
            jQuery('.woopb-tabs .menu').append(tab_data);
            tab_data.find('.woopb-save-name').attr('name', `woopb-param[tab_title][${tab_id}]`);

            /*Tab content*/
            var tab_content = jQuery('.woopb-tabs-content .tab').first().clone();
            tab_content.find('.woopb-tab-inner').html('');
            tab_content.find('.woopb-step-icon-id').attr('name', `woopb-param[step_icon][${tab_id}]`);

            tab_content.find('textarea[name^="woopb-param[step_desc"]').each(function () {
                let oldName = jQuery(this).attr('name');
                // ví dụ: woopb-param[step_desc_vi][0]
                let newName = oldName.replace(/\[\d+\]$/, `[${tab_id}]`);
                jQuery(this).attr('name', newName).val(''); // reset value nếu cần
            });

            jQuery('.woopb-tabs-content .tab').removeClass('active');
            tab_content.addClass('active').attr('data-tab', tab_id);
            jQuery('.woopb-tabs-content').append(tab_content);
            woo_product_buider.check_added(tab_id);
            woo_product_buider.tab_init();
        });

        /*Edit tab title*/
        jQuery('.woopb-edit').unbind();
        jQuery('.woopb-edit').on('click', function () {
            var current_tab_item = jQuery(this).closest('a');
            var title = prompt(_woopb_params.tab_title_change);
            if (title == null || title == '') {
                return;
            }
            current_tab_item.find('.woopb-tab-title').text(title);
            current_tab_item.find('input').val(title);
        });

        /*Remove tab*/
        jQuery('.woopb-remove').unbind();
        jQuery('.woopb-remove').on('click', function () {
            var r = confirm(_woopb_params.tab_notice_remove);
            if (r == true && jQuery('.woopb-tabs .menu .item').length > 1) {
                var tab_id = jQuery(this).closest('a').attr('data-tab');
                jQuery('a[data-tab="' + tab_id + '"],div[data-tab="' + tab_id + '"]').remove();
                if (jQuery(this).closest('a').hasClass('active')) {
                    var new_tab_id = jQuery('.woopb-tabs-content .tab').first().attr('data-tab');
                    jQuery('.woopb-tabs .menu .item').first().addClass('active');
                    jQuery('.woopb-tabs-content .tab').first().addClass('active');
                    woo_product_buider.check_added(new_tab_id);
                }
            }
        });
    },

    /**
     * Add item to tab
     */
    open_modal: function () {

        jQuery('body').on('click', '.woopb-tab-selected-items', function (e) {
            e.stopPropagation();
            let t = jQuery(this),
                t_type_choose = t.data('type_choose');
            jQuery('.woobp-select-product-popup-container').modal('show', function () {
                let selector_woopb_type = jQuery('.woopb-type'),
                    current_selector_woopb_type = selector_woopb_type.val();

                if (t_type_choose === 'categories' && current_selector_woopb_type !== '0') {
                    selector_woopb_type.val('0').trigger('change');

                } else if (t_type_choose === 'products' && current_selector_woopb_type !== '1') {
                    selector_woopb_type.val('1').trigger('change');

                }

            })
        });
    },
    add_item: function () {

        jQuery('.woopb-product-select .woopb-item').unbind();
        jQuery('.woopb-product-select .woopb-item:not(".woopb-exist")').on('click', function (e) {
            e.stopPropagation();
            var item_html = jQuery(this).clone();
            item_html.find('.woopb-item-top').remove();/*remove thumb*/
            var current_tab = jQuery('.woopb-tabs-content .active.tab');
            var tab_id = current_tab.attr('data-tab');
            var item_id = jQuery(this).attr('data-id');
            if (jQuery(this).hasClass('woopb-item-category')) {
                item_id = 'cate_' + item_id;
            }
            item_html.append('<input type="hidden" name="woopb-param[list_content][' + tab_id + '][]" value="' + item_id + '"/>');
            if (jQuery(this).hasClass('woopb-item-category')) {
                current_tab.find('.woopb-tab-selected-categories').append(item_html);
            } else {
                current_tab.find('.woopb-tab-selected-products').append(item_html);
            }
            jQuery(this).addClass('woopb-exist');
            woo_product_buider.init();
        });
    },

    /**
     * Toggle item in tab
     */
    remove_item: function () {
        jQuery('.woopb-tabs-content .tab .woopb-item').on('click', function (e) {
            e.stopPropagation();
            var item_id = jQuery(this).attr('data-id');
            if (jQuery(this).hasClass('woopb-item-category')) {
                jQuery('.woopb-product-select .woopb-item-category[data-id="' + item_id + '"]').removeClass('woopb-exist');
            } else {
                jQuery('.woopb-product-select .woopb-item-product[data-id="' + item_id + '"]').removeClass('woopb-exist');
            }
            jQuery(this).remove();
            woo_product_buider.add_item();
        })
    },

    /**
     * Check tab what added in tab
     * @param tab_id
     */
    check_added: function (tab_id) {
        jQuery('.woopb-product-select .woopb-item').removeClass('woopb-exist');
        jQuery('.woopb-tabs-content .tab[data-tab="' + tab_id + '"]').find('.woopb-item').each(function () {
            var item_id = jQuery(this).attr('data-id');
            if (jQuery(this).hasClass('woopb-item-category')) {
                jQuery('.woopb-product-select .woopb-item-category[data-id="' + item_id + '"]').addClass('woopb-exist');
            } else {
                jQuery('.woopb-product-select .woopb-item-product[data-id="' + item_id + '"]').addClass('woopb-exist');
            }
        });
        woo_product_buider.add_item();
    },

    ajax_search: function () {
        jQuery('.wpb-search-field,.woopb-type').on('change', function () {
            woo_product_buider.send_ajax();
        });
        jQuery('.woopb-search-button').on('click', function () {
            woo_product_buider.send_ajax();
        });
    },

    send_ajax: function () {
        var keyword = jQuery('.wpb-search-field').val();

        var type = jQuery('.woopb-type option:selected').val();
        var str_data = '&type=' + type + '&keyword=' + keyword;
        var template = wp.template('woopb-item-template');
        jQuery('.woopb-items').html('');
        jQuery('.woopb-search-button').addClass('loading');
        jQuery('.woopb-search-form').addClass('loading');
        jQuery.ajax({
            type: 'POST',
            data: 'action=woopb_get_data&woopb_nonce=' + _woopb_params.nonce + str_data,
            url: _woopb_params.ajax_url,
            success: function (data) {
                jQuery('.woopb-search-form').removeClass('loading');
                jQuery('.woopb-search-button').removeClass('loading');
                jQuery.each(data, function (index, value) {
                    if (type == 0) {
                        var html = template({
                            id: value.id,
                            name: value.title,
                            item_class: 'category',
                            thumb: '',
                        });
                        jQuery('.woopb-items').append(html);

                    } else {
                        if (value.thumb_url == false) {
                            var html = template({
                                id: value.id,
                                name: value.title,
                                item_class: 'product',
                                thumb: '',
                            });
                            jQuery('.woopb-items').append(html);
                        } else {
                            var html = template({
                                id: value.id,
                                name: value.title,
                                item_class: 'product woopb-img',
                                thumb: '<img src="' + value.thumb_url + '"/>',
                            });
                            jQuery('.woopb-items').append(html);
                        }
                    }
                });
                var current_tab = jQuery('.woopb-tabs-content .active.tab').attr('data-tab');
                woo_product_buider.check_added(current_tab);
                woo_product_buider.init();
            },
            error: function (html) {
                jQuery('.woopb-search-button').removeClass('loading');
                jQuery('.woopb-search-form').removeClass('loading');
            }
        })
    },

    compatible_remove_all: function () {
        jQuery('.woopb-compatible-clear-all').on('click', function () {
            var r = confirm(_woopb_params.compatible_notice_remove);
            if (r == true) {
                jQuery('.woopb-compatible-field option:selected').removeAttr("selected");
                jQuery('.select2-selection__choice').remove();
            }
        })
        jQuery('.woopb-attr-compatible-clear-all').on('click', function () {
            var r = confirm(_woopb_params.compatible_notice_remove);
            if (r == true) {
                jQuery('.woopb-attr-compatible-field option:selected').removeAttr("selected");
                jQuery('.select2-selection__choice').remove();
            }
        })
    },

    addStepIcon() {
        const media = wp.media();
        jQuery('.woopb-step-icon')
            .on('click', '.woopb-select-step-icon', function (e) {
                let thisImg = jQuery(this), input = jQuery(e.delegateTarget).find('.woopb-step-icon-id');
                media.open().off('select').on('select', function () {
                    let uploadedImages = media.state().get('selection').first();
                    let selectedImages = uploadedImages.toJSON();
                    let {id, url} = selectedImages;
                    if (id && url) {
                        thisImg.attr('src', url);
                        input.val(id)
                    }
                })
            })
            .on('click', '.woopb-remove-step-icon', function (e) {
                jQuery(e.delegateTarget).find('.woopb-step-icon-id').val('');
                jQuery(e.delegateTarget).find('.woopb-select-step-icon').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAQAAADa613fAAAAaUlEQVR42u3PQREAAAgDINc/9Izg34MGpJ0XIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiJyWYprx532021aAAAAAElFTkSuQmCC');
            });

    }
};