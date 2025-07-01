<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('ffgc_get_currency_symbol')) {
    function ffgc_get_currency_symbol($currency) {
        $symbols = array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$'
        );
        return isset($symbols[$currency]) ? $symbols[$currency] : '$';
    }
}

if (!function_exists('ffgc_format_price')) {
    function ffgc_format_price($amount) {
        if (function_exists('wc_price')) {
            return wc_price($amount);
        }
        $currency = get_option('ffgc_currency', 'USD');
        $symbol   = ffgc_get_currency_symbol($currency);
        return $symbol . number_format((float) $amount, 2);
    }
}

