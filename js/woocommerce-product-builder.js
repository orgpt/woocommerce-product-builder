if (typeof wc_single_product_params !== 'undefined') wc_single_product_params.zoom_enabled = 1;
if (typeof wc_single_product_params !== 'undefined') wc_single_product_params.photoswipe_enabled = 1;

//Woodmart theme v:5.3.6
if (typeof woodmartThemeModule !== "undefined") {
    woodmart_settings.ajax_add_to_cart = false;
}

jQuery(document).ready(function ($) {
    'use strict';
    //Fix style with theme playerx

    /*List ajax template*/
    const list_ajax_template = ['ajax', 'ajax-layout-1', 'ajax-layout-2'];

    let body = $('body');
    if (body.hasClass('theme-playerx')) body.addClass('woocommerce-page');
    $('body').on('change', '.variation_id', function (e) {
        e.preventDefault();
        let wrap_form = $(e.target).closest('form');
        if ($(e.target).val() === '') {
            wrap_form.find('.woopb-add-to-list-btn').addClass('woopb-button-deactivated').attr('disabled', 'disabled');
            wrap_form.bind('submit', function (e) {
                e.preventDefault();
            });
        } else {
            wrap_form.find('.woopb-add-to-list-btn').removeClass('woopb-button-deactivated').removeAttr('disabled', 'disabled');
            wrap_form.unbind('submit');
        }
    });
    /*Add total price*/
    let total_price = $('#woopb-step-total .woocommerce-Price-amount').html();
    $('.woopb-step-total .woopb-special-position').html(`<span class="woocommerce-Price-amount amount">${total_price}</span`);

    
    /*Button toggle show selected product*/
    $('body').on('click', '.woopb-step-show-selected-product', function (e) {
        e.preventDefault();
        let vi_wpb_wrapper = $('.vi-wpb-wrapper');

        if (vi_wpb_wrapper.hasClass('vi-wpb-modern-layout-2')) {
            let selected_container = $('.woopb-steps-all-product.woopb-steps-modern-layout-2');
            if (!selected_container.hasClass('woopb-opened')) {
                selected_container.addClass('woopb-opened');
            }
        } else if (vi_wpb_wrapper.hasClass('vi-wpb-classic-layout-2') || vi_wpb_wrapper.hasClass('vi-wpb-modern-layout-3')) {
            let closest_t = $(this).closest('.woocommerce-product-builder-right');

            if (closest_t.hasClass('woopb-opened')) {
                localStorage.removeItem('woopb-opened');
                closest_t.removeClass('woopb-opened');
            } else {
                closest_t.addClass('woopb-opened');
                localStorage.setItem('woopb-opened', 'true');
            }
        }

    });
    // 🔥 Khi load lại trang, kiểm tra localStorage và mở lại nếu cần
    $(document).ready(function () {
        let vi_wpb_wrapper = $('.vi-wpb-wrapper');

        // Nếu localStorage có "woopb-opened" => mở modal lại
        if (localStorage.getItem('woopb-opened') === 'true' && viWpbSettings !== undefined && viWpbSettings.remember_modal_state!= undefined && viWpbSettings.remember_modal_state == 1) {
            if (
                vi_wpb_wrapper.hasClass('vi-wpb-classic-layout-2') ||
                vi_wpb_wrapper.hasClass('vi-wpb-modern-layout-3')
            ) {
                $('.woocommerce-product-builder-right').addClass('woopb-opened');
            } else if (vi_wpb_wrapper.hasClass('vi-wpb-modern-layout-2')) {
                $('.woopb-steps-all-product.woopb-steps-modern-layout-2').addClass('woopb-opened');
            }
        }
    });
    $('body').on('click', '.woopb-step-hide-selected-product', function (e) {
        e.preventDefault();
        let selected_container = $('.woopb-steps-all-product.woopb-steps-modern-layout-2');
        if (selected_container.hasClass('woopb-opened')) {
            selected_container.removeClass('woopb-opened');
        }
    });

    const reloadWooScript = () => {
        $('.woocommerce-product-gallery').each(function () {
            $(this).trigger('wc-product-gallery-before-init', [this, wc_single_product_params]);
            if (jQuery().wc_product_gallery) $(this).wc_product_gallery(wc_single_product_params);
            $(this).trigger('wc-product-gallery-after-init', [this, wc_single_product_params]);
        });
        $('.woopb-modal-body').on('scroll', function () {
            if (typeof woodmartThemeModule !== "undefined") {
                woodmartThemeModule.$document.trigger('wood-images-loaded');
            }
        });
        $('.variations_form').each(function () {
            $(this).wc_variation_form();
        });
        $('body').trigger('quick-view-displayed');
    };

    const Print = (data) => {
        let printIframe = $(`<iframe id="woopb-print-frame" name="woopb_print_frame" style="display:none;"></iframe>`);

        printIframe.remove();

        body.append(printIframe);

        let printSection = frames['woopb_print_frame'];
        let style = _woo_product_builder_params.printStyle;
        let printDesc = +_woo_product_builder_params.printDesc;
        let total = $('.woopb-added-products-value').html();
        let totalQty = 0;
        let temp = $('<div></div>');

        data = Object.values(data);

        let html = data.map((el) => {
            let row = '';

            for (let id in el) {
                let item = el[id],
                    qty = item.quantity || 1;
                totalQty = +totalQty + parseInt(qty);

                let itemDesc = printDesc ? `<div><small>${item.desc}</small></div>` : '';

                row += `<tr>
                            <td class="woopb-print-img">
                                <img src="${item.image}" width="100%">
                            </td>
                            <td class='woopb-print-title'>
                                <div>${item.title}</div>
                                ${itemDesc}
                            </td>
                            <td class="woopb-print-quantity-col">${qty}</td>
                            <td class="woopb-print-price-col">${item.price}</td>
                            <td class="woopb-print-subtotal-col">${item.subtotal}</td>
                        </tr>`;
            }

            return row;
        }).join('');

        temp.append($('head meta').clone());
        temp.append($('head title').clone());

        html = `<div id="woopb-print-frame">
                            ${_woo_product_builder_params.printHeader}
                            <table>
                                <thead >
                                <tr>
                                    <th colspan="2" class="woopb-print-product-col">${_woo_product_builder_params.print_product_col}</th>
                                    <th class="woopb-print-quantity-col">${_woo_product_builder_params.print_qty_col}</th>
                                    <th class="woopb-print-price-col">${_woo_product_builder_params.print_price_col}</th>
                                    <th class="woopb-print-subtotal-col">${_woo_product_builder_params.print_subtotal_col}</th>
                                </tr>
                                </thead>
                                <tbody>
                                ${html}
                                </tbody>
                                 <tfoot style="display: table-row-group; ">
                                    <tr>
                                        <th colspan="2">${_woo_product_builder_params.print_total_title}</th>
                                        <th>${totalQty}</th>
                                        <th colspan="2" class="woopb-print-footer-total">${total}</th>
                                    </tr>
                                </tfoot>
                            </table>
                            ${_woo_product_builder_params.printFooter}
                        </div>`;

        html = `<!DOCTYPE html>
                        <html>
                            <head>
                                ${temp.html()}
                                ${style}
                            </head>
                            <body>${html}</body>
                        </html>`;

        printSection.document.write(html);

        let images = $(html).find('img');
        let totalImages = images.length;
        let imagesLoaded = 0;

        images.on('load', function (e) {
            imagesLoaded++;
            if (imagesLoaded === totalImages) {
                printSection.print();
            }
        });

        printSection.document.close();
    };

    $.fn.woopbLoading = function () {
        this.addClass('woopb-loading')
    };

    $.fn.woopbDisableLoading = function () {
        this.removeClass('woopb-loading')
    };

    if (list_ajax_template.includes(_woo_product_builder_params.templateStyle)) {
        let timer,
            spinner = $('<span class="woopb-loading"> </span>'),
            watingScreen = $('<div id="woopb-waiting-screen"><div class="woopb-waiting-screen-inner"><span class="woopb-loading"> </span></div></div>'),
            {productTmpl, stepTmpl, emptyTmpl, loadStepButton} = _woo_product_builder_params;

        productTmpl = $(productTmpl);
        stepTmpl = $(stepTmpl);
        emptyTmpl = $(emptyTmpl);
        loadStepButton = $(loadStepButton);


        const ajax = (options) => {
            let {data} = options;
            delete options.data;

            $.ajax({
                url: _woo_product_builder_params.ajax_url,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'woopb_action',
                    nonce: _woo_product_builder_params.nonce,
                    woopb_id: _woo_product_builder_params.post_id,
                    page_id: _woo_product_builder_params.page_id,
                    step: Store.currentStep,
                    ...data
                },
                error(res) {
                    console.log(res)
                },
                ...options
            });
        };

        const Store = {
            currentStep: '',
            page: 1,
            sortBy: '',
            searchKey: '',
            minPrice: '',
            maxPrice: '',
            replace: '',
            currentProductQty: '',
            filterAttributes: {},
            steps: [],
            checkDepend: [],

            getStep(step) {
                return this.steps[step] || '';
            },

            setCheckDepend(data = []) {
                this.checkDepend = data;
            },

            setStep(products, append = true) {
                let step = this.currentStep;

                this.steps[step] = products;
                if (typeof this.onChange === 'function' && append) this.onChange();
            }
        };

        const Builder = {
            modalLoading: {
                isOpen: false,
                open() {
                    Builder.modal.append(spinner);
                    this.isOpen = true;
                },
                close() {
                    spinner.remove();
                    this.isOpen = false;
                }
            },

            init() {
                let multiSelect = this.getSetting('enable_multi_select');
                if (multiSelect) productTmpl.find('.woopb-product-select').remove();

                this.modal = $('#woopb-modal')
                    .on('click', this.closeModalByClick)
                    .on('click', '.woopb-close-modal', this.closeModal)
                    .on('submit', 'form.variations_form.cart', this.selectVariation)
                    .on('submit', 'form.cart', this.addSingleItem)
                    .on('click', '.woopb-page-item:not(.woopb-active-page)', this.changePage)
                    .on('click', '.woopb-mobile-filters-control', this.toggleFilter)
                    .on('click', '.woopb-close-filter', this.toggleFilter)
                    .on('click', '.woopb-clear-filter', this.clearFilterAction)
                    .on('change', '.variation_id', function (e) {
                        e.preventDefault();
                        let wrap_form = $(e.target).closest('form');
                        if ($(e.target).val() === '') {
                            wrap_form.find('.woopb-add-to-list-btn').addClass('woopb-button-deactivated').attr('disabled', 'disabled');
                            wrap_form.bind('submit', function (e) {
                                e.preventDefault();
                            });
                        } else {
                            wrap_form.find('.woopb-add-to-list-btn').removeClass('woopb-button-deactivated').removeAttr('disabled', 'disabled');
                            wrap_form.unbind('submit');
                        }
                    });

                this.searchForm = $('.woopb-search').on('keyup', this.search);

                this.sortForm = $('.woopb-sort').on('change', () => this.changeSort());

                this.main = $('#woopb-main')
                    .on('click', '.woopb-load-step:not(.woopb-loading)', this.loadStepProducts)
                    .on('change', '.woopb-product-quantity-value', this.changeQuantity)
                    .on('click', '.woopb-product-remove:not(.woopb-loading)', this.removeProduct)
                    .on('click', '.woopb-product-edit:not(.woopb-loading)', this.loadStepProducts);

                this.sidebar = $('#woopb-sidebar')
                    .on('click', '.woopb-add-products-to-cart:not(.woopb-loading)', this.addToCart)
                    .on('click', '.woopb-get-share-link:not(.woopb-loading)', this.getShareLink)
                    .on('click', '.woopb-send-to-friend', this.openSendToFriend)
                    .on('click', '.woopb-remove-all:not(.woopb-loading)', this.removeAll)
                    .on('click', '.woopb-print-button', this.print)
                    .on('click', '.woopb-download-pdf-button:not(.woopb-loading)', this.downloadPDF);

                this.sendMailPopup = $('#vi_wpb_popup_email')
                    .on('click', '.woopb-close', this.closeSendToFriend)
                    .on('click', '.vi-wpb_overlay', this.closeSendToFriend);

                this.filters = $('.woopb-modal-left')
                    .on('submit', '.woopb-price-filter-form', this.filterPrice)
                    .on('click', '.woopb-filter-attributes', this.filterAttributes)
                    .on('click', '.woocommerce-widget-layered-nav-list__item', this.triggerFilterAttributes)
                    .on('click', '.woopb-filter-rating', this.filterRating);

                this.total = $('.woopb-added-products-value');

                this.waitingLoad();
                this.renderSteps();

                Store.minPrice = this.filters.find('#min_price').val();
                Store.maxPrice = this.filters.find('#max_price').val();
                Store.sortBy = this.sortForm.val();
                Store.onChange = this.replaceStep;
            },

            waitingLoad() {
                $('body').append(watingScreen);
            },

            getSetting(key) {
                return _woo_product_builder_params.config[key] || '';
            },

            replaceStep() {
                let step = this.currentStep,
                    thisStep = Builder.main.find(`.woopb-step[data-step_id=${step}] .woopb-products`),
                    products = this.getStep(step);

                Builder.renderStep(thisStep, products);
            },

            setTotal(total) {
                this.total.html(total)
            },

            renderEmptySteps() {
                this.main.empty();
                let steps = this.getSetting('tab_title');
                let descs = this.getSetting('step_desc');

                for (let $i in steps) {
                    let stepTitle = steps[$i],
                        stepDesc = descs[$i],
                        stepHtml = stepTmpl.clone(),
                        productsHtml = stepHtml.find('.woopb-products'),
                        products = Store.getStep($i);

                    if (stepDesc) stepDesc = stepDesc.replace(/\r\n/g, "<br />");

                    stepHtml.attr('data-step_id', +$i + 1);
                    stepHtml.find('.woopb-step-title').text(stepTitle);
                    stepHtml.find('.woopb-step-desc').html(stepDesc);

                    Store.currentStep = +$i + 1;
                    Builder.renderStep(productsHtml, products);

                    this.main.append(stepHtml);
                }
            },

            renderStep(step, products) {
                step.empty().hide();
                let stepContainer = step.closest('.woopb-step');
                let loadStepBtn = stepContainer.find('.woopb-load-step-outer');
                let stepFooter = stepContainer.find('.woopb-step-footer');
                let multiSelect = this.getSetting('enable_multi_select');

                if (Object.keys(products).length) {
                    for (let id in products) {
                        let product = products[id];
                        step.append(Builder.renderProduct(id, product.title, product.quantity, product.image, product.price));
                    }
                    loadStepBtn.show();

                    stepFooter.find('.woopb-product-buttons').hide();

                } else {
                    let emptyProductHtml = emptyTmpl.clone();
                    let loadStepButtonHtml = loadStepButton.clone();
                    let icons = this.getSetting('step_icon');
                    let icon = icons[Store.currentStep - 1];

                    if (icon) {
                        stepFooter.find('img.woopb-step-icon').attr('src', icon);
                    } else {
                        stepFooter.find('img.woopb-step-icon').remove();
                    }

                    stepFooter.append(loadStepButtonHtml);
                    step.append(emptyProductHtml);
                    loadStepBtn.hide();
                }

                step.fadeIn(500);
            },

            renderSteps() {
                this.renderEmptySteps();

                ajax({
                    data: {_action: 'get_session'},
                    success(res) {
                        if (res.success) {
                            watingScreen.remove();
                            let {steps} = res.data;
                            if (steps) {
                                for (let step in steps) {
                                    let stepData = steps[step];
                                    if (Object.keys(stepData).length) {
                                        Store.currentStep = step;
                                        Store.setStep(stepData);
                                    }
                                }
                            }
                            Builder.setTotal(res.data.total);
                            Builder.setDependNotice(res.data.checkDepend);
                        }
                    }
                })
            },

            afterLoadProducts(data, page) {
                let {html, maxPage, message, filter} = data;

                if (message) {
                    this.modal.find('.woopb-modal-products').html(message);
                    this.modal.find('.woopb-step-pagination').html('');
                    return;
                }

                let pagination = this.renderPagination(page, maxPage);
                this.modal.find('.woopb-modal-products').html(html);
                this.modal.find('.woopb-step-pagination').html(pagination);

                if (filter) {
                    this.filters.html(filter);
                    $(document.body).trigger('init_price_filter');
                } else {
                    this.filters.hide();
                }

                this.modal.fadeIn(100);
                $('html,body').addClass('viwcpb_no_scroll');
                reloadWooScript();
            },

            // Load products in a step for modal
            loadStepProducts() {
                let thisBtn = $(this),
                    currentStep = $(this).closest('.woopb-step');
                Store.currentStep = currentStep.attr('data-step_id');

                if (thisBtn.hasClass('woopb-product-edit')) {
                    let thisProduct = thisBtn.closest('.woopb-product');
                    Store.replace = thisProduct.attr('data-product_id');
                    Store.currentProductQty = thisProduct.find('.woopb-product-quantity-value').val() || 1;
                }

                ajax({
                    data: {_action: 'load_step_products', woopb_nonce: _woo_product_builder_params.nonce, sort_by: Store.sortBy},
                    beforeSend: () => thisBtn.woopbLoading(),
                    success(res) {
                        thisBtn.woopbDisableLoading();
                        if (res.success) Builder.afterLoadProducts(res.data, 1);
                    }
                })
            },

            renderProduct(pid, title, quantity, image, price) {
                let productHtml = productTmpl.clone();
                productHtml.attr('data-product_id', pid);
                productHtml.find('.woopb-product-thumbnail img').attr('src', image);
                productHtml.find('.woopb-product-title').text(title);
                productHtml.find('.woopb-product-quantity-value').val(quantity);
                productHtml.find('.woopb-product-price').html(price);
                return productHtml;
            },

            changeQuantity() {
                if (timer) clearTimeout(timer);

                let thisProduct = $(this).closest('.woopb-product'),
                    step = $(this).closest('.woopb-step').attr('data-step_id'),
                    quantity = $(this).val();
                Store.currentStep = step;

                timer = setTimeout(() => {
                    ajax({
                        delay: 500,
                        data: {
                            _action: 'change_quantity',
                            woopb_nonce: _woo_product_builder_params.nonce,
                            step,
                            product_id: thisProduct.attr('data-product_id'),
                            quantity
                        },
                        success(res) {
                            if (res.success) {
                                if (quantity != res.data.qty) {
                                    Store.setStep(res.data.step);
                                } else {
                                    Store.setStep(res.data.step, false);
                                }
                                Builder.setTotal(res.data.total);
                            } else {
                                Store.setStep(Store.steps[step]);
                            }
                        }
                    })
                }, 500);
            },

            removeProduct() {
                let thisBtn = $(this),
                    thisProduct = $(this).closest('.woopb-product'),
                    step = $(this).closest('.woopb-step').attr('data-step_id');

                Store.currentStep = step;

                ajax({
                    delay: 500,
                    data: {_action: 'remove_product', woopb_nonce: _woo_product_builder_params.nonce, step, product_id: thisProduct.attr('data-product_id')},
                    beforeSend() {
                        thisBtn.woopbLoading();
                    },
                    success(res) {
                        if (res.success) {
                            thisProduct.fadeOut(500);
                            setTimeout(() => thisProduct.remove(), 500);
                            let stepData = res.data.step;
                            let append = !Object.keys(stepData).length;
                            Store.setStep(stepData, append);
                            Builder.setTotal(res.data.total);
                            Builder.setDependNotice(res.data.checkDepend);
                        }
                        thisBtn.woopbDisableLoading();
                    }
                })
            },

            addItem(data) {
                if (Builder.modalLoading.isOpen) return;

                ajax({
                    data: {_action: 'add_item', woopb_nonce: _woo_product_builder_params.nonce, data, replace: Store.replace},
                    beforeSend: () => Builder.modalLoading.open(),
                    success(res) {
                        if (res.success) {
                            Store.setStep(res.data.step);
                            Builder.setTotal(res.data.total);
                            Builder.setDependNotice(res.data.checkDepend);
                            Builder.closeModal();
                            Builder.modalLoading.close();
                            Store.replace = '';
                        }
                    }
                });
            },

            setDependNotice(data) {
                $('.woopb-depend-notice').html('');
                for (let step in data) {
                    let products = data[step];
                    if (products.length) {
                        for (let pid of products) {
                            $(`.woopb-step[data-step_id=${step}] .woopb-product[data-product_id=${pid}] .woopb-depend-notice`)
                                .html(`<div class="woopb-depend-notice-text">${_woo_product_builder_params.checkDependNotice}</div>`);
                        }
                    }
                }
            },

            addSingleItem(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let data = $(this).serialize();
                let pid = $(e.target).find('.woopb-add-to-list-btn').val();
                data += `&product_id=${pid}`;

                Builder.addItem(data);
            },

            selectVariation(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let data = $(this).serialize();
                let variationId = $(this).find('.variation_id').val();
                let quantity = $(this).find('[name=quantity]').val();

                if (+Store.replace === +variationId && +Store.currentProductQty === +quantity) {
                    Builder.closeModal();
                    return;
                }

                Builder.addItem(data);
            },

            changePage() {
                Store.page = $(this).attr('data-page_id');
                Builder.loadProducts()
            },

            toggleFilter() {
                $('.woopb-modal-left').slideToggle();
            },

            clearFilterAction() {
                Builder.clearFilter();
                Builder.loadProducts();
            },

            clearFilter() {
                Store.filterAttributes = {};
                Store.minPrice = '';
                Store.maxPrice = '';
            },

            changeSort() {
                Store.sortBy = Builder.sortForm.val();
                Builder.loadProducts()
            },

            loadProducts(page = '') {
                if (Builder.modalLoading.isOpen) return;

                let search = Store.searchKey,
                    sort_by = Store.sortBy,
                    min_price = Store.minPrice,
                    max_price = Store.maxPrice;
                if (!page) page = Store.page;

                ajax({
                    data: {_action: 'load_step_products', page, search, sort_by, min_price, max_price, ...Store.filterAttributes},
                    beforeSend() {
                        Builder.modalLoading.open();
                    },
                    success(res) {
                        if (res.success) Builder.afterLoadProducts(res.data, page);
                        Builder.modalLoading.close();
                        $('.woopb-modal-body').scrollTop(0);
                    }
                })
            },

            search() {
                if (timer) clearTimeout(timer);
                // Builder.clearFilter();
                Store.searchKey = Builder.searchForm.val();
                timer = setTimeout(() => Builder.loadProducts(1), 1000);
            },

            closeModal() {
                Store.replace = '';
                Store.currentProductQty = '';
                Builder.clearSearch();
                Builder.clearFilter();
                $('html,body').removeClass('viwcpb_no_scroll');
                Builder.modal.fadeOut(500);
            },

            closeFilter() {
                Builder.filters.slideUp();
            },

            closeModalByClick(e) {
                if (e.target === e.currentTarget) {
                    Builder.closeModal();
                }
            },

            renderPagination(current, maxPage) {
                let pagination = '';
                if (maxPage > 0) {
                    let prevPage = +current - 1;
                    let nextPage = +current + 1;

                    for (let i = 1; i <= maxPage; i++) {

                        if (i > 1 && i < maxPage) {
                            if (i < prevPage || i > nextPage) {
                                if (i === nextPage + 1 || i === prevPage - 1) {
                                    pagination += `<span class="woopb-page-item" >...</span>`;
                                }
                                continue;
                            }
                        }

                        let active = +current === i ? 'woopb-active-page' : '';
                        pagination += `<span class="woopb-page-item ${active}" data-page_id="${i}">${i}</span>`;
                    }
                }

                return pagination;
            },

            addToCart() {
                let thisBtn = $(this);
                let t_sidebar = thisBtn.closest('.woopb-sidebar-panel'),
                    t_check_fee = t_sidebar.find('#woopb_step_fee'),
                    woopb_step_fee = 0;
                if (t_check_fee.prop('checked')) {
                    woopb_step_fee = t_check_fee.val();
                }

                $('.woopb-product-require-notice').remove();
                ajax({
                    data: {_action: 'add_to_cart', woopb_nonce: _woo_product_builder_params.nonce, woopb_step_fee: woopb_step_fee},
                    beforeSend: () => thisBtn.woopbLoading(),
                    success(res) {
                        thisBtn.woopbDisableLoading();
                        if (res.success) {
                            window.location.href = res.data.url;
                        } else if (res.data && res.data.message) {
                            $('#woopb-main').prepend(`<div class="woopb-product-require-notice">${res.data.message}</div>`);
                        }
                    }
                });
            },

            getShareLink() {
                let thisBtn = $(this);

                if (Builder.shareLinkInput) Builder.shareLinkInput.remove();

                ajax({
                    data: {_action: 'get_share_link', woopb_nonce: _woo_product_builder_params.nonce},
                    beforeSend: () => thisBtn.woopbLoading(),
                    success(res) {
                        if (res.success) {
                            let {shareUrl} = res.data;
                            let oldText = thisBtn.html();
                            let input = $(`<input type="text" class="woopb-share-link-container" value="${shareUrl}"/>`);
                            Builder.shareLinkInput = input;

                            if (shareUrl) {
                                thisBtn.after(input);
                                input.select();

                                let isSafari = navigator.vendor && navigator.vendor.indexOf('Apple') > -1 && navigator.userAgent && navigator.userAgent.indexOf('CriOS') === -1 && navigator.userAgent.indexOf('FxiOS') === -1;

                                if (!isSafari) {
                                    document.execCommand("copy");
                                    input.remove();
                                    thisBtn.text(_woo_product_builder_params.textCopied);
                                }

                            } else {
                                thisBtn.text(_woo_product_builder_params.textNoUrl);
                            }

                            setTimeout(() => thisBtn.html(oldText), 3000)
                        }
                        thisBtn.woopbDisableLoading();
                    }
                });
            },

            openSendToFriend: () => Builder.sendMailPopup.fadeIn(300),

            closeSendToFriend: () => Builder.sendMailPopup.fadeOut(300),

            clearSearch() {
                Store.searchKey = '';
                Builder.searchForm.val('');
            },

            filterPrice(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                Builder.clearSearch();

                Store.minPrice = $(this).find('#min_price').val();
                Store.maxPrice = $(this).find('#max_price').val();

                Builder.loadProducts(1);
                if (window.innerWidth <= 768) Builder.closeFilter();
            },

            triggerFilterAttributes() {
                $(this).find('.woopb-filter-attributes').trigger('click');
            },

            filterAttributes(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                Builder.clearSearch();

                let filterName = $(this).attr('data-attr_name');
                Store.filterAttributes[filterName] = $(this).attr('data-attr_value');
                Builder.loadProducts(1);

                if (window.innerWidth <= 768) Builder.closeFilter();

            },

            filterRating(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                Store.filterAttributes['rating_filter'] = $(this).attr('data-rating');
                Builder.loadProducts();

                if (window.innerWidth <= 768) Builder.closeFilter();
            },

            removeAll() {
                let thisBtn = $(this);

                ajax({
                    data: {_action: 'remove_all'},
                    beforeSend: () => thisBtn.woopbLoading(),
                    success(res) {
                        if (res.success) {
                            Store.steps = {};
                            Builder.renderEmptySteps();
                            Builder.setTotal(res.data.total);
                        }
                    },
                    complete: () => thisBtn.woopbDisableLoading(),
                });
            },

            print() {
                if (!Store.steps.length) return;
                Print(Store.steps);
            },

            downloadPDF() {
                let thisBtn = $(this);
                ajax({
                    data: {_action: 'download_pdf'},
                    dataType: '',
                    xhr: function () {
                        let xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 2) {
                                if (xhr.status === 200) {
                                    xhr.responseType = "blob";
                                } else {
                                    xhr.responseType = "text";
                                }
                            }
                        };
                        return xhr;
                    },
                    beforeSend: () => thisBtn.woopbLoading(),
                    success(data) {
                        let fileName = 'ProductBuilder.pdf';
                        //Convert the Byte Data to BLOB object.
                        let blob = new Blob([data], {type: "application/octetstream"});
                        //Check the Browser type and download the File.
                        let isIE = !!document.documentMode;
                        if (isIE) {
                            window.navigator.msSaveBlob(blob, fileName);
                        } else {
                            let url = window.URL || window.webkitURL,
                                link = url.createObjectURL(blob),
                                a = $(`<a download="${fileName}" class="no-fade viwcpb-download-pdf" href="${link}"/>`),
                                body = $('.woopb-tool-buttons');

                            body.append(a);
                            a[0].click();
                            setTimeout(function () {
                                $('.viwcpb-download-pdf').remove();
                            }, 150)
                        }
                        thisBtn.woopbDisableLoading();
                    }
                })
            }
        };

        Builder.init();

    } else {
        // if (typeof wc_single_product_params !== 'undefined') wc_single_product_params.zoom_enabled = 1;
        // if (typeof wc_single_product_params !== 'undefined') wc_single_product_params.photoswipe_enabled = 1;

        //Woodmart theme v:5.3.6
        if (typeof woodmartThemeModule !== "undefined") woodmart_settings.ajax_add_to_cart = false;

        function parse_str(str, array) {
            const _fixStr = (str) => decodeURIComponent(str.replace(/\+/g, '%20'));
            const strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&');
            const sal = strArr.length;
            let i, j, ct, p, lastObj, obj, chr, tmp, key, value, postLeftBracketPos, keys, keysLen;
            const $global = (typeof window !== 'undefined' ? window : global);
            $global.$locutus = $global.$locutus || {};
            const $locutus = $global.$locutus;
            $locutus.php = $locutus.php || {};

            if (!array) array = $global;

            for (i = 0; i < sal; i++) {
                tmp = strArr[i].split('=');
                key = _fixStr(tmp[0]);
                value = (tmp.length < 2) ? '' : _fixStr(tmp[1]);

                if (key.includes('__proto__') || key.includes('constructor') || key.includes('prototype')) break;

                while (key.charAt(0) === ' ') key = key.slice(1);

                if (key.indexOf('\x00') > -1) {
                    key = key.slice(0, key.indexOf('\x00'))
                }

                if (key && key.charAt(0) !== '[') {
                    keys = [];
                    postLeftBracketPos = 0;

                    for (j = 0; j < key.length; j++) {
                        if (key.charAt(j) === '[' && !postLeftBracketPos) {
                            postLeftBracketPos = j + 1
                        } else if (key.charAt(j) === ']') {
                            if (postLeftBracketPos) {
                                if (!keys.length) keys.push(key.slice(0, postLeftBracketPos - 1));

                                keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
                                postLeftBracketPos = 0;

                                if (key.charAt(j + 1) !== '[') break;
                            }
                        }
                    }

                    if (!keys.length) keys = [key];

                    for (j = 0; j < keys[0].length; j++) {
                        chr = keys[0].charAt(j);

                        if (chr === ' ' || chr === '.' || chr === '[') {
                            keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1)
                        }

                        if (chr === '[') break;
                    }

                    obj = array;

                    for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                        key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '');
                        lastObj = obj;
                        if ((key === '' || key === ' ') && j !== 0) {
                            // Insert new dimension
                            ct = -1
                            for (p in obj) {
                                if (obj.hasOwnProperty(p)) {
                                    if (+p > ct && p.match(/^\d+$/g)) {
                                        ct = +p
                                    }
                                }
                            }
                            key = ct + 1
                        }

                        // if primitive value, replace with object
                        if (Object(obj[key]) !== obj[key]) obj[key] = {};

                        obj = obj[key];
                    }
                    lastObj[key] = value;
                }
            }
        }

        window.woo_product_builder = {
            init: function () {
                this.sort_by();
                this.review_popup();
                this.review_total_price();
                this.events();
                this.mobileControlBar();
                this.getShortShareLink();
                this.imgLightBox();
                this.pcFilterBar();
                this.search();
                this.print();
                this.downloadPDF();
            },

            sort_by: function () {
                jQuery('.woopb-sort-by-button').on('change', function () {
                    let href = jQuery(this).val();
                    if ($('.woopb-search-products-input').val()) {
                        href += '&name_filter=' + $('.woopb-search-products-input').val() + '&ppaged=' + ($('.woopb-search-pagination').data('page_id') || 1);
                    }
                    window.location.href = href
                })
            },

            review_popup: function () {
                jQuery('#vi_wpb_sendtofriend').on('click', function () {
                    woo_product_builder.review_popup_show();
                });
                jQuery('#vi_wpb_popup_email .vi-wpb_overlay, #vi_wpb_popup_email .woopb-close').on('click', function () {
                    woo_product_builder.review_popup_hide();
                });
            },

            review_popup_show: function () {
                jQuery('html').css({'overflow': 'hidden'});
                jQuery('#vi_wpb_popup_email').fadeIn(500);
            },

            review_popup_hide: function () {
                jQuery('#vi_wpb_popup_email').fadeOut(300);
                jQuery('html').css({'overflow': 'inherit'});
            },

            review_total_price: function () {
                jQuery('.woopb-qty-input').on('change', function () {
                    var quantity = parseInt(jQuery(this).val());
                    var price = parseFloat(jQuery(this).closest('td').attr('data-price'));
                    var total_html = jQuery(this).closest('tr').find('.woopb-total .woocommerce-Price-amount').contents();

                    if (price > 0) {
                        var total = quantity * price;
                        total_html.filter(function (index) {
                            return this.nodeType == 3;
                        }).each(function () {
                            this.textContent = total;
                        })
                    } else {
                        return;
                    }
                })
            },

            events: function () {
                jQuery('.woopb-share-link').on('click', function () {
                    jQuery(this).select();
                    document.execCommand("copy");
                });
            },

            pcFilterBar() {
                $('.woopb-pc-filters-control').on('click', function () {
                    let t_sidebar = $('.woocommerce-product-builder-sidebar-outer');
                    if ($('.vi-wpb-wrapper').hasClass('vi-wpb-classic-layout-2')) {
                        if (t_sidebar.hasClass('woopb-opened')) {
                            t_sidebar.removeClass('woopb-opened').animate({"left": "-880px"}, "normal");
                        } else {
                            t_sidebar.addClass('woopb-opened').animate({"left": "0px"}, "normal");
                        }
                    } else {
                        t_sidebar.slideToggle();
                        if (t_sidebar.hasClass('woopb-opened')) {
                            t_sidebar.removeClass('woopb-opened');
                        } else {
                            t_sidebar.addClass('woopb-opened');
                        }
                    }

                });
            },

            mobileControlBar() {
                let overlay = jQuery('.woopb-overlay'),
                    steps = jQuery('.vi-wpb-wrapper .woopb-steps'),
                    viewStepsBtn = jQuery('.woopb-steps-detail-btn'),
                    viewFilterBtn = jQuery('.woopb-mobile-filters-control'),
                    close = jQuery('.woopb-close-modal');

                let sidebar;
                switch (_woo_product_builder_params.templateStyle) {
                    case 'modern':
                        sidebar = jQuery('.woocommerce-product-builder-sidebar-outer');
                        break;
                    case 'modern-layout-2':
                        sidebar = jQuery('.woocommerce-product-builder-sidebar-outer');
                        viewFilterBtn.on('click', function () {
                            if (sidebar.hasClass('woopb-opened')) {
                                sidebar.removeClass('woopb-opened').slideToggle();
                            } else {
                                sidebar.addClass('woopb-opened').slideToggle();
                            }
                            steps.fadeOut();
                        });
                        break;
                    default:
                        sidebar = jQuery('.woocommerce-product-builder-sidebar');
                        viewFilterBtn.on('click', function () {
                            sidebar.slideToggle();
                            steps.fadeOut();
                        });
                        break;
                }

                /*let stepInstance = $('.woocommerce-product-builder-right');
                if (stepInstance.length) {
                    steps = stepInstance;
                }

                let sidebarInstance = $('.woocommerce-product-builder-left');
                if (sidebarInstance.length) {
                    sidebar = sidebarInstance;
                }*/

                viewStepsBtn.on('click', function () {
                    steps.slideToggle();
                    sidebar.fadeOut();
                });

                viewStepsBtn.on('mouseup', function () {
                    steps.css('display') === 'none' ? overlay.show('slow') : overlay.hide();
                    steps.css('display') === 'none' ? close.show() : close.hide();
                });


                viewFilterBtn.on('mouseup', function () {
                    sidebar.css('display') === 'none' ? overlay.show('slow') : overlay.hide();
                    sidebar.css('display') === 'none' ? close.show() : close.hide();
                });

                function hideAll() {
                    sidebar.hide('slow');
                    steps.hide('show');
                    overlay.hide();
                    close.hide();
                }

                overlay.on('click', function () {
                    hideAll();
                });

                close.on('click', function () {
                    hideAll();
                });
            },

            getShortShareLink() {
                let shortLinkResult = $(`<div class="woopb-short-share-link-inner">
                                        <span class="woopb-short-share-link-text"></span>
                                        <i class="dashicons dashicons-admin-page woopb-copy-short-link"></i>
                                    </div>`);

                $('.vi-wpb-wrapper').on('click', '#vi-wpb-get-short-share-link:not(.woopb-loading)', function () {
                    let _thisBtn = $(this);
                    $.ajax({
                        url: _woo_product_builder_params.ajax_url,
                        type: 'post',
                        dataType: 'json',
                        data: {action: 'woopb_get_short_share_link', nonce: $('#_nonce').val(), woopb_id: $('[name=woopb_id]').val()},
                        beforeSend: function () {
                            _thisBtn.addClass('woopb-loading');
                        },
                        success: function (res) {
                            if (res.success && res.data) {
                                $('.woopb-share').before(shortLinkResult);
                                shortLinkResult.find('.woopb-short-share-link-text').text(res.data);

                                shortLinkResult.on('click', function () {
                                    let node = $(this).find('.woopb-short-share-link-text').get(0);
                                    if (window.getSelection) {
                                        let selection = window.getSelection();
                                        let range = document.createRange();
                                        range.selectNode(node);
                                        selection.removeAllRanges();
                                        selection.addRange(range);
                                        document.execCommand("copy");
                                    } else if (document.selection) {
                                        let range = document.body.createTextRange();
                                        range.moveToElementText(node);
                                        range.select().createTextRange();
                                        document.execCommand("copy");
                                    }
                                });
                            }
                        },
                        complete() {
                            _thisBtn.removeClass('woopb-loading');
                        }
                    });
                });
            },

            imgLightBox() {
                $('.woopb-product-image').on('click', 'a', function (e) {
                    // e.preventDefault();
                    // e.stopImmediatePropagation();
                });
            },

            search() {
                let woopbTimeout = null;
                $('.woopb-search-products-input').on('keyup', function () {
                    let $this = $(this), search = $this.val();

                    if (woopbTimeout) clearTimeout(woopbTimeout);

                    woopbTimeout = setTimeout(function () {
                        if (search) {
                            woo_product_builder.ajaxSearch(search, 1);
                        } else {
                            $('.woopb-products, .woopb-products-pagination').show();
                            $('.woopb-products-searched').html(' ');
                            $('.woopb-search-pagination').html('');
                        }

                    }, 1000);

                });

                $('.woopb-search-pagination').on('click', '.woopb-page:not(.woopb-active)', function () {
                    let paged = $(this).data('page_id');
                    let search = $('.woopb-search-products-input').val();
                    if (paged) woo_product_builder.ajaxSearch(search, paged);
                });
            },

            ajaxSearch(search, paged) {
                let searchForm = $('.woopb-search-products-input');
                let newFormData = {};
                let form_data = $('form.cart').attr('action');
                let sort_by = $('.woopb-sort-by-button').val().split('=')[1].split('&')[0] || 'title_az';
                parse_str(form_data, newFormData);
                let data = {
                    action: 'woopb_search_product_in_step',
                    woopb_nonce: _woo_product_builder_params.nonce,
                    search: search,
                    sort_by: sort_by,
                    post_id: searchForm.attr('data-post'),
                    step: searchForm.attr('data-step'),
                    form_action: form_data,
                    referer: $('input[name=_wp_http_referer]').val(),
                    nonce: $('#_nonce').val(),
                    paged: paged
                };

                if (Object.keys(newFormData).length) {
                    for (let key in newFormData) {
                        if (key.indexOf('filter_') === -1) continue;
                        data[key] = newFormData[key];
                    }
                }

                $.ajax({
                    url: _woo_product_builder_params.wp_ajax_url,
                    type: 'post',
                    data: data,
                    success: function (response) {

                        if (response.success) {
                            let {products, pagination} = response.data;
                            $('.woopb-products-searched').html(products);
                            $('.woopb-search-pagination').html(pagination);

                            reloadWooScript();

                            $('.woopb-products, .woopb-products-pagination').hide();
                            $('.woocommerce-product-gallery').each(function () {

                                $(this).trigger('wc-product-gallery-before-init', [this, wc_single_product_params]);

                                $(this).wc_product_gallery(wc_single_product_params);

                                $(this).trigger('wc-product-gallery-after-init', [this, wc_single_product_params]);

                            });
                        }
                    },
                    error: function (response) {
                    },
                    beforeSend: function () {
                        $('.woopb-spinner-inner').removeClass('woopb-hidden');
                    },
                    complete: function () {
                        $('.woopb-spinner-inner').addClass('woopb-hidden');
                    }
                })
            },

            print() {
                $('#woopb-print').on('click', function () {
                    Print(_woo_product_builder_params.stepsData);
                });
            },

            downloadPDF() {
                $('.vi-wpb-wrapper').on('click', '#woopb-download-pdf:not(.woopb-loading)', function () {
                    let thisBtn = $(this);

                    $.ajax({
                        url: _woo_product_builder_params.ajax_url,
                        type: 'post',
                        data: {
                            action: 'woopb_action',
                            nonce: _woo_product_builder_params.nonce,
                            woopb_id: _woo_product_builder_params.post_id,
                            _action: 'download_pdf'
                        },
                        xhr: function () {
                            let xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 2) {
                                    if (xhr.status === 200) {
                                        xhr.responseType = "blob";
                                    } else {
                                        xhr.responseType = "text";
                                    }
                                }
                            };
                            return xhr;
                        },
                        beforeSend: () => thisBtn.woopbLoading(),
                        success(data) {
                            let fileName = 'ProductBuilder.pdf';
                            //Convert the Byte Data to BLOB object.
                            let blob = new Blob([data], {type: "application/octetstream"});
                            //Check the Browser type and download the File.
                            let isIE = !!document.documentMode;
                            if (isIE) {
                                window.navigator.msSaveBlob(blob, fileName);
                            } else {
                                let url = window.URL || window.webkitURL,
                                    link = url.createObjectURL(blob),
                                    a = $(`<a download="${fileName}" href="${link}"/>`),
                                    body = $('body');

                                body.append(a);
                                a[0].click();
                                body.remove(a);
                            }
                            thisBtn.woopbDisableLoading();
                        },
                        error(res) {
                            console.log(res)
                        },
                    });

                });
            }
        };

        woo_product_builder.init();
    }

});
