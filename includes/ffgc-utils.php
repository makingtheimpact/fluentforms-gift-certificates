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

if (!function_exists('ffgc_get_script_strings')) {
    function ffgc_get_script_strings() {
        return array(
            'loading_designs'          => __('Loading designs...', 'fluentforms-gift-certificates'),
            'check_balance'            => __('Check Balance', 'fluentforms-gift-certificates'),
            'checking'                 => __('Checking...', 'fluentforms-gift-certificates'),
            'error_occurred'           => __('An error occurred', 'fluentforms-gift-certificates'),
            'enter_code'               => __('Please enter a certificate code', 'fluentforms-gift-certificates'),
            'design_status_updated'    => __('Design status updated successfully', 'fluentforms-gift-certificates'),
            'failed_update_design'     => __('Failed to update design status', 'fluentforms-gift-certificates'),
            'certificate_status_updated' => __('Certificate status updated successfully', 'fluentforms-gift-certificates'),
            'failed_update_certificate' => __('Failed to update certificate status', 'fluentforms-gift-certificates'),
            'select_certificates'      => __('Please select certificates to perform this action', 'fluentforms-gift-certificates'),
            'bulk_action_completed'    => __('Bulk action completed successfully', 'fluentforms-gift-certificates'),
            'failed_bulk_action'       => __('Failed to perform bulk action', 'fluentforms-gift-certificates'),
            'confirm_bulk_action'      => __('Are you sure you want to perform this action on %d certificate(s)?', 'fluentforms-gift-certificates'),
            'checking_error'           => __('An error occurred while checking the certificate', 'fluentforms-gift-certificates'),
            'apply_error'              => __('An error occurred while applying the certificate', 'fluentforms-gift-certificates'),
            'checking_balance'         => __('Checking balance...', 'fluentforms-gift-certificates'),
            'processing'               => __('Processing...', 'fluentforms-gift-certificates'),
            'copied'                   => __('Copied!', 'fluentforms-gift-certificates'),
            'copy_code'                => __('Copy Code', 'fluentforms-gift-certificates'),
            'enter_gift_code'          => __('Enter gift certificate code', 'fluentforms-gift-certificates'),
            'select_design_image'      => __('Select Design Image', 'fluentforms-gift-certificates'),
            'use_this_image'           => __('Use this image', 'fluentforms-gift-certificates')
        );
    }
}

if (!function_exists('ffgc_sanitize_email_template')) {
    /**
     * Sanitize email template HTML while allowing style tags.
     *
     * @param string $html Raw HTML from the editor.
     * @return string Sanitized HTML.
     */
    function ffgc_sanitize_email_template($html) {
        $allowed = wp_kses_allowed_html('post');
        $allowed['style'] = array('type' => true);

        return wp_kses($html, $allowed);
    }
}

