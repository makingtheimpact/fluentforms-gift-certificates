<?php
/**
 * Core Plugin Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Core {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_ffgc_check_balance', array($this, 'ajax_check_balance'));
        add_action('wp_ajax_nopriv_ffgc_check_balance', array($this, 'ajax_check_balance'));
        
        // Initialize components
        $this->init_components();
    }
    
    private function init_components() {
        // Initialize post types
        new FFGC_Post_Types();
        
        // Initialize settings
        new FFGC_Settings();
        
        // Initialize forms integration
        new FFGC_Forms();
        
        // Initialize email system
        new FFGC_Email();

        // Initialize REST webhooks
        new FFGC_Webhooks();

        // Initialize shortcodes
        new FFGC_Shortcodes();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Gift Certificates', 'fluentforms-gift-certificates'),
            __('Gift Certificates', 'fluentforms-gift-certificates'),
            'manage_options',
            'fluentforms-gift-certificates',
            array($this, 'admin_page'),
            'dashicons-tickets-alt',
            30
        );
        
        add_submenu_page(
            'fluentforms-gift-certificates',
            __('Settings', 'fluentforms-gift-certificates'),
            __('Settings', 'fluentforms-gift-certificates'),
            'manage_options',
            'fluentforms-gift-certificates-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'fluentforms-gift-certificates',
            __('Configure Forms', 'fluentforms-gift-certificates'),
            __('Configure Forms', 'fluentforms-gift-certificates'),
            'manage_options',
            'fluentforms-gift-certificates-forms',
            array($this, 'forms_page')
        );
        
        add_submenu_page(
            'fluentforms-gift-certificates',
            __('Designs', 'fluentforms-gift-certificates'),
            __('Designs', 'fluentforms-gift-certificates'),
            'manage_options',
            'fluentforms-gift-certificates-designs',
            array($this, 'designs_page')
        );
    }
    
    public function admin_page() {
        include FFGC_PLUGIN_DIR . 'templates/admin/main-page.php';
    }
    
    public function settings_page() {
        include FFGC_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
    
    public function forms_page() {
        include FFGC_PLUGIN_DIR . 'templates/admin/forms-page.php';
    }
    
    public function designs_page() {
        include FFGC_PLUGIN_DIR . 'templates/admin/designs-page.php';
    }
    
    public function admin_scripts($hook) {
        if (strpos($hook, 'fluentforms-gift-certificates') === false) {
            return;
        }
        
        wp_enqueue_style(
            'ffgc-admin',
            FFGC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FFGC_VERSION
        );
        
        wp_enqueue_script(
            'ffgc-admin',
            FFGC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FFGC_VERSION,
            true
        );
        
        wp_localize_script('ffgc-admin', 'ffgc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ffgc_nonce')
        ));
        wp_localize_script('ffgc-admin', 'ffgc_strings', ffgc_get_script_strings());
    }
    
    public function frontend_scripts() {
        wp_enqueue_style(
            'ffgc-frontend',
            FFGC_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FFGC_VERSION
        );
        
        wp_enqueue_script(
            'ffgc-frontend',
            FFGC_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            FFGC_VERSION,
            true
        );
        
        wp_localize_script('ffgc-frontend', 'ffgc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ffgc_nonce')
        ));
        wp_localize_script('ffgc-frontend', 'ffgc_strings', ffgc_get_script_strings());
    }
    
    public function ajax_check_balance() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['code'] ?? '');
        
        if (empty($code)) {
            wp_send_json_error(__('Please enter a gift certificate code.', 'fluentforms-gift-certificates'));
        }
        
        $certificate = get_posts(array(
            'post_type' => 'ffgc_cert',
            'meta_query' => array(
                array(
                    'key' => '_certificate_code',
                    'value' => $code,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));
        
        if (empty($certificate)) {
            wp_send_json_error(__('Invalid gift certificate code.', 'fluentforms-gift-certificates'));
        }
        
        $certificate = $certificate[0];
        $status = get_post_meta($certificate->ID, '_status', true);
        $balance = get_post_meta($certificate->ID, '_certificate_balance', true);
        $amount = get_post_meta($certificate->ID, '_certificate_amount', true);
        
        if ($status !== 'active') {
            wp_send_json_error(__('This gift certificate is not active.', 'fluentforms-gift-certificates'));
        }
        
        if ($balance <= 0) {
            wp_send_json_error(__('This gift certificate has no remaining balance.', 'fluentforms-gift-certificates'));
        }
        
        wp_send_json_success(array(
            'balance' => $balance,
            'total' => $amount,
            'used' => $amount - $balance
        ));
    }
} 