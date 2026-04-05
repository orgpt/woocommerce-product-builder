'use strict'
jQuery(document).ready(function ($) {
	$('.woopb-search-product-widget').select2()
	$('.woopb-search-product-widget').on('change', function () {
		$(this).closest('form').submit()
	})
})