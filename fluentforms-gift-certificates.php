<?php
/**
 * Plugin Name: Fluent Forms Gift Certificates
 * Plugin URI: https://github.com/your-username/fluentforms-gift-certificates
 * Description: Generate and manage gift certificates for Fluent Forms with customizable designs and automatic email delivery.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
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

// Define plugin constants
define('FFGC_VERSION', '1.0.0');
define('FFGC_PLUGIN_FILE', __FILE__);
define('FFGC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FFGC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FFGC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if Fluent Forms is active
function ffgc_check_fluent_forms() {
    if (!class_exists('FluentForm')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('Fluent Forms Gift Certificates requires Fluent Forms to be installed and activated.', 'fluentforms-gift-certificates') . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

// Initialize plugin
function ffgc_init() {
    if (!ffgc_check_fluent_forms()) {
        return;
    }
    
    // Load plugin classes
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-core.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-post-types.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-settings.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-forms.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-email.php';
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-shortcodes.php';
    
    // Initialize core plugin
    new FFGC_Core();
}
add_action('plugins_loaded', 'ffgc_init');

// Activation hook
register_activation_hook(__FILE__, 'ffgc_activate');
function ffgc_activate() {
    if (!ffgc_check_fluent_forms()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Fluent Forms Gift Certificates requires Fluent Forms to be installed and activated.', 'fluentforms-gift-certificates'));
    }
    
    // Create database tables and default options
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-installer.php';
    $installer = new FFGC_Installer();
    $installer->install();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ffgc_deactivate');
function ffgc_deactivate() {
    // Cleanup if needed
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'ffgc_uninstall');
function ffgc_uninstall() {
    // Remove all plugin data
    require_once FFGC_PLUGIN_DIR . 'includes/class-ffgc-installer.php';
    $installer = new FFGC_Installer();
    $installer->uninstall();
} 
