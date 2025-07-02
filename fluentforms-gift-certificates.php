<?php
/**
 * Plugin Name: Fluent Forms Gift Certificates
 * Plugin URI: https://github.com/your-username/fluentforms-gift-certificates
 * Description: Generate and manage gift certificates for Fluent Forms with customizable designs and automatic email delivery.
 * Version: 1.0.2
 * Author: Making The Impact LLC
 * Author URI: https://makingtheimpact.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fluentforms-gift-certificates
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress plugin functions
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Define plugin constants
define('FFGC_VERSION', '1.0.2');
define('FFGC_PLUGIN_FILE', __FILE__);
define('FFGC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FFGC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FFGC_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once FFGC_PLUGIN_DIR . 'includes/ffgc-utils.php';

// Global flag to prevent multiple initializations
global $ffgc_initialized;
$ffgc_initialized = false;

// Debug function for troubleshooting
function ffgc_debug_fluent_forms_status() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info"><p><strong>Fluent Forms Gift Certificates Debug Info:</strong></p>';
    echo '<ul>';
    echo '<li>Fluent Forms Plugin Active: ' . (is_plugin_active('fluentform/fluentform.php') ? 'Yes' : 'No') . '</li>';
    echo '<li>Fluent Forms Pro Plugin Active: ' . (is_plugin_active('fluentform-pro/fluentform-pro.php') ? 'Yes' : 'No') . '</li>';
    echo '<li>wpFluent Function Available: ' . (function_exists('wpFluent') ? 'Yes' : 'No') . '</li>';
    echo '<li>FluentForm Class Available: ' . (class_exists('FluentForm') ? 'Yes' : 'No') . '</li>';
    echo '<li>FluentForm\App\Modules\Form\Form Class Available: ' . (class_exists('FluentForm\App\Modules\Form\Form') ? 'Yes' : 'No') . '</li>';
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'fluentform_forms';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    echo '<li>Fluent Forms Database Table Exists: ' . ($table_exists ? 'Yes' : 'No') . '</li>';
    
    // Check post types
    $post_types = get_post_types(array(), 'names');
    echo '<li>ffgc_cert Post Type Registered: ' . (in_array('ffgc_cert', $post_types) ? 'Yes' : 'No') . '</li>';
    echo '<li>ffgc_design Post Type Registered: ' . (in_array('ffgc_design', $post_types) ? 'Yes' : 'No') . '</li>';
    echo '<li>Old gift_certificate Post Type Found: ' . (in_array('gift_certificate', $post_types) ? 'Yes' : 'No') . '</li>';
    echo '<li>Old gift_certificate_design Post Type Found: ' . (in_array('gift_certificate_design', $post_types) ? 'Yes' : 'No') . '</li>';
    echo '</ul></div>';
}

// Add debug info to admin notices if WP_DEBUG is enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_notices', 'ffgc_debug_fluent_forms_status');
}

// Check if Fluent Forms is active
function ffgc_check_fluent_forms() {
    // Check if Fluent Forms plugin is active
    if (!is_plugin_active('fluentform/fluentform.php') && !is_plugin_active('fluentform-pro/fluentform-pro.php')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('Fluent Forms Gift Certificates requires Fluent Forms to be installed and activated.', 'fluentforms-gift-certificates') . 
                 '</p></div>';
        });
        return false;
    }
    
    // Check if Fluent Forms database tables exist
    global $wpdb;
    $table_name = $wpdb->prefix . 'fluentform_forms';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('Fluent Forms Gift Certificates detected Fluent Forms but the database tables are not properly set up. Please deactivate and reactivate Fluent Forms.', 'fluentforms-gift-certificates') . 
                 '</p></div>';
        });
        return false;
    }
    
    // Check if wpFluent function is available
    if (!function_exists('wpFluent')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>' . 
                 __('Fluent Forms Gift Certificates is waiting for Fluent Forms to fully load...', 'fluentforms-gift-certificates') . 
                 '</p></div>';
        });
        return false;
    }
    
    // Check for Fluent Forms classes (multiple possible class names)
    if (!class_exists('FluentForm') && !class_exists('FluentForm\App\Modules\Form\Form')) {
        // If classes aren't loaded yet, we'll check again later
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>' . 
                 __('Fluent Forms Gift Certificates is checking for Fluent Forms compatibility...', 'fluentforms-gift-certificates') . 
                 '</p></div>';
        });
        return false;
    }
    
    return true;
}

// Initialize plugin
function ffgc_init() {
    global $ffgc_initialized;

    // Prevent multiple initializations
    if ($ffgc_initialized) {
        return;
    }

    if (!ffgc_check_fluent_forms()) {
        return;
    }

    // Load plugin textdomain for translations
    load_plugin_textdomain(
        'fluentforms-gift-certificates',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
    
    // Load plugin classes
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-core.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-post-types.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-settings.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-forms.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-email.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-webhooks.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-shortcodes.php';
    
    // Initialize core plugin
    new FFGC_Core();
    
    // Mark as initialized
    $ffgc_initialized = true;
    do_action('ffgc_initialized');
}

// Try to initialize on plugins_loaded, but also try on init if needed
add_action('plugins_loaded', 'ffgc_init', 20);
add_action('init', function() {
    global $ffgc_initialized;
    // If not already initialized and Fluent Forms is active, try again
    if (!$ffgc_initialized && ffgc_check_fluent_forms()) {
        ffgc_init();
    }
    
    // Force re-register post types if old ones are still present
    $post_types = get_post_types(array(), 'names');
    if (in_array('gift_certificate', $post_types) || in_array('gift_certificate_design', $post_types)) {
        // Clear the post type cache and re-register
        unset($GLOBALS['wp_post_types']['gift_certificate']);
        unset($GLOBALS['wp_post_types']['gift_certificate_design']);
        
        // Force re-register our post types
        if (!$ffgc_initialized) {
            ffgc_init();
        }
    }
}, 20);

// Activation hook
register_activation_hook(__FILE__, 'ffgc_activate');
function ffgc_activate() {
    // Check if Fluent Forms plugin is active
    if (!is_plugin_active('fluentform/fluentform.php') && !is_plugin_active('fluentform-pro/fluentform-pro.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('Fluent Forms Gift Certificates requires Fluent Forms to be installed and activated.', 'fluentforms-gift-certificates'),
            __('Plugin Activation Error', 'fluentforms-gift-certificates'),
            array('back_link' => true)
        );
    }
    
    // Check if Fluent Forms database tables exist
    global $wpdb;
    $table_name = $wpdb->prefix . 'fluentform_forms';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('Fluent Forms Gift Certificates detected Fluent Forms but the database tables are not properly set up. Please deactivate and reactivate Fluent Forms first.', 'fluentforms-gift-certificates'),
            __('Plugin Activation Error', 'fluentforms-gift-certificates'),
            array('back_link' => true)
        );
    }
    
    // Clear any cached data
    wp_cache_flush();

    // Clear object cache if available
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('posts');
        wp_cache_flush_group('post_meta');
    }

    // Clear transients
    delete_transient('ffgc_post_types');
    
    // Create database tables and default options
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-installer.php';
    $installer = new FFGC_Installer();
    $installer->install();
    
    // Force refresh post types
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ffgc_deactivate');
function ffgc_deactivate() {
    // Clear rewrite rules
    flush_rewrite_rules();
    
    // Clear any cached data
    wp_cache_flush();
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'ffgc_uninstall');
function ffgc_uninstall() {
    // Remove all plugin data
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-installer.php';
    $installer = new FFGC_Installer();
    $installer->uninstall();
} 
