<?php
defined( 'ABSPATH' ) || exit;
?>
<style type="text/css">
    body {
        font-family: arial, sans-serif;
        font-size: 16px;
    }

    table, th, td, #woopb-print-frame {
        font-family: arial, sans-serif;
        font-size: 14px;
    }

    #woopb-print-frame h1 {
        font-size: x-large;
    }
    #woopb-print-frame .aligncenter {
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    #woopb-print-frame .alignright {
        float: right;
        margin: 0.5em 0 0.5em 1em;
    }
    #woopb-print-frame .alignleft {
        float: left;
        margin: 0.5em 1em 0.5em 0;
    }

    #woopb-print-frame table {
        width: 100%;
        border: 1px solid #cccccc;
        border-collapse: collapse;
    }

    #woopb-print-frame table tr td:first-child,
    #woopb-print-frame table tr th:first-child {
        border-left: none;
    }

    #woopb-print-frame table tr td, #woopb-print-frame table tr th {
        border-left: 1px solid #cccccc;
    }

    #woopb-print-frame table tr th {
        background-color: #f9fafb;
        border-bottom: 1px solid #cccccc;
    }

    #woopb-print-frame table tr td,
    #woopb-print-frame table tfoot tr th {
        border-top: 1px solid #cccccc;
    }

    #woopb-print-frame table tr:first-child td {
        border-top: none;
    }

    #woopb-print-frame table tr th,
    #woopb-print-frame table tr td {
        padding: 10px;
    }

    #woopb-print-frame .woopb-print-product-col {
        width: 50%;
        text-align: left;
    }

    #woopb-print-frame table td.woopb-print-img {
        border-right: none;
    }

    #woopb-print-frame table td.woopb-print-title {
        width: 35%;
        border-left: none;
    }

    #woopb-print-frame .woopb-print-quantity-col,
    #woopb-print-frame .woopb-print-price-col {
        text-align: center;
        white-space: nowrap;
    }

    #woopb-print-frame .woopb-print-subtotal-col,
    #woopb-print-frame .woopb-print-footer-total {
        text-align: right;
    }

</style>