jQuery(document).ready(function ($) {
    'use strict';

    var woo_product_builder = {
        init: function () {
            /*Save loading when submit*/
            this.save_submit();
            /*Load color picker*/
            this.color_picker();

            /*Init tab */
            jQuery('.menu .item').unbind();
            jQuery('.vi-ui.tabular.menu .item').vi_tab({
                history: true,
                historyType: 'hash'
            });
            if ($('.vi-ui.accordion').length) {
                $('.vi-ui.accordion:not(.woopb-accordion-init)').addClass('woopb-accordion-init').vi_accordion('refresh');
            }

            jQuery('.vi-ui.checkbox').checkbox();
            jQuery('.vi-ui.radio').checkbox();
            jQuery('.vi-ui.dropdown').dropdown();
            /**
             * Start Get download key
             */
            jQuery('.villatheme-get-key-button').one('click', function (e) {
                let v_button = jQuery(this)
                v_button.addClass('loading');
                let data = v_button.data();
                let item_id = data.id;
                let app_url = data.href;
                let main_domain = window.location.hostname;
                main_domain = main_domain.toLowerCase();
                let popup_frame;
                e.preventDefault();
                let download_url = v_button.attr('data-download');
                popup_frame = window.open(app_url, 'myWindow', 'width=380,height=600');
                window.addEventListener('message', function (event) {
                    /*Callback when data send from child popup*/
                    let obj = jQuery.parseJSON(event.data);
                    let update_key = '';
                    let message = obj.message;
                    let support_until = '';
                    let check_key = '';
                    if (obj['data'].length > 0) {
                        for (let i = 0; i < obj['data'].length; i++) {
                            if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                                if (update_key == '') {
                                    update_key = obj['data'][i].download_key;
                                    support_until = obj['data'][i].support_until;
                                } else if (support_until < obj['data'][i].support_until) {
                                    update_key = obj['data'][i].download_key;
                                    support_until = obj['data'][i].support_until;
                                }
                                if (obj['data'][i].domain == main_domain) {
                                    update_key = obj['data'][i].download_key;
                                    break;
                                }
                            }
                        }
                        if (update_key) {
                            check_key = 1;
                            jQuery('.villatheme-autoupdate-key-field').val(update_key);
                        }
                    }
                    v_button.removeClass('loading');
                    if (check_key) {
                        jQuery('<p><strong>' + message + '</strong></p>').insertAfter('.villatheme-autoupdate-key-field');
                        jQuery(v_button).closest('form').submit();
                    } else {
                        jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter('.villatheme-autoupdate-key-field');
                    }
                })
            });
            /**
             * End get download key
             */

            $('input[name="woopb_option-param[template]"]').on('change', function () {
                let t= $(this),
                    t_val = t.val(),
                    t_closest = t.closest('.woopb-option-frontend-style'),
                    t_label = t_closest.find('label');
                $('.woopb-option-frontend-style').find('label').removeClass('woopb-option-active');
                t_label.addClass('woopb-option-active');

            });

        },
        save_submit: function () {
            jQuery('.woopb-button-save').one('click', function () {
                jQuery(this).addClass('loading');
            })
        },
        color_picker: function () {
            // Color picker
           jQuery('.color-picker').iris({
                change: function (event, ui) {
                    let this_parent = $(this).closest('.wp-picker-container');
                    this_parent.find('.color-picker').css({backgroundColor: ui.color.toString()});
                    var ele = jQuery(this).data('ele');
                    if (ele == 'highlight') {
                        this_parent.find('.wp-color-result').find('a').css({'color': ui.color.toString()});
                    } else if (ele == 'textcolor') {
                        this_parent.find('.wp-color-result').css({'color': ui.color.toString()});
                    } else {
                        this_parent.find('.wp-color-result').css({backgroundColor: ui.color.toString()});
                    }
                },
                hide: true,
                border: true
            }).click(function () {
                jQuery('.iris-picker').hide();
                jQuery(this).closest('.field').find('.iris-picker').show();/*Class "field" is wrap */
            });
            jQuery('body').click(function () {
                jQuery('.iris-picker').hide();
            });
            jQuery('.color-picker').click(function (event) {
                event.stopPropagation();
            });
            jQuery('.color-picker').wpColorPicker().on('click', function () {
                jQuery('.iris-picker').hide();
                jQuery(this).closest('.field').find('.iris-picker').show();/*Class name "field" is wrap */
            });
        }
    };

    woo_product_builder.init();

    const shortShareLinkApp = {
        init() {
            this.setStatus();
            this.copyURL();
        },
        setStatus() {
            $('.woopb-no-remove').on('click', function () {
                let id = $(this).val(), status = $(this).is(':checked');
                $.ajax({
                    url: _woopb_params.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {action: 'woopb_set_short_share_link_status', id: id, status: status, nonce: _woopb_params.nonce},
                    beforeSend: function () {
                    },
                    success: function (res) {
                    },
                    error: function (res) {
                    }
                });
            });
        },
        copyURL() {
            $('.woopb-short-share-link').on('click', function () {
                $(this).select();
                document.execCommand('copy');
            });
        }
    };

    shortShareLinkApp.init();
});


