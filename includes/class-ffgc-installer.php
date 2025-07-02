<?php
/**
 * Installer Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Installer {
    
    public function install() {
        $this->create_tables();
        $this->set_default_options();
        $this->create_default_designs();
        $this->migrate_existing_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function uninstall() {
        $this->remove_tables();
        $this->remove_options();
        $this->remove_posts();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Gift certificates usage log table
        $table_name = $wpdb->prefix . 'ffgc_usage_log';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            certificate_id bigint(20) NOT NULL,
            form_id bigint(20) NOT NULL,
            submission_id bigint(20) NOT NULL,
            amount_used decimal(10,2) NOT NULL,
            order_total decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY certificate_id (certificate_id),
            KEY form_id (form_id),
            KEY submission_id (submission_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function set_default_options() {
        $default_options = array(
            'ffgc_min_amount' => 10.00,
            'ffgc_max_amount' => 1000.00,
            'ffgc_currency' => 'USD',
            'ffgc_expiry_days' => 365,
            'ffgc_email_from_name' => get_bloginfo('name'),
            'ffgc_email_from_address' => get_option('admin_email'),
            'ffgc_email_subject' => __('Your Gift Certificate is Ready!', 'fluentforms-gift-certificates'),
            'ffgc_default_email_template' => $this->get_default_email_template(),
            'ffgc_forms_enabled' => array(),
            'ffgc_gift_certificate_field_label' => __('Gift Certificate Code', 'fluentforms-gift-certificates'),
            'ffgc_gift_certificate_field_placeholder' => __('Enter your gift certificate code', 'fluentforms-gift-certificates'),
            'ffgc_balance_page' => 0,
        );
        
        foreach ($default_options as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    private function create_default_designs() {
        // Create default designs
        $default_design = array(
            'post_title' => __('Default Design', 'fluentforms-gift-certificates'),
            'post_content' => __('Default gift certificate design', 'fluentforms-gift-certificates'),
            'post_status' => 'publish',
            'post_type' => 'ffgc_design',
            'post_author' => 1
        );
        
        $design_id = wp_insert_post($default_design);
        
        if ($design_id) {
            update_post_meta($design_id, '_min_amount', 10.00);
            update_post_meta($design_id, '_max_amount', 1000.00);
            update_post_meta($design_id, '_is_active', 'yes');
            update_post_meta($design_id, '_email_template', $this->get_default_email_template());
        }
    }
    
    private function get_default_email_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px; }
        .content { padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-top: 20px; }
        .code { background: #e9ecef; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .amount { font-size: 18px; color: #28a745; font-weight: bold; }
        .message { background: #f8f9fa; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎁 Gift Certificate</h1>
        </div>
        
        <div class="content">
            <p>Dear {recipient_name},</p>
            
            <p>You have received a gift certificate worth <span class="amount">${amount}</span>!</p>
            
            <div class="code">
                {certificate_code}
            </div>
            
            <p><strong>How to use your gift certificate:</strong></p>
            <ol>
                <li>Visit our website</li>
                <li>Add items to your cart</li>
                <li>Enter the code above during checkout</li>
                <li>Enjoy your purchase!</li>
            </ol>

            <p>You can check your remaining balance at <a href="{balance_url}">{balance_url}</a>.</p>

            {personal_message}
            
            <p>This gift certificate is valid until {expiry_date}.</p>
            
            <p>Thank you for choosing us!</p>
        </div>
        
        <div class="footer">
            <p>If you have any questions, please contact us.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function remove_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ffgc_usage_log';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
    
    private function remove_options() {
        $options = array(
            'ffgc_min_amount',
            'ffgc_max_amount',
            'ffgc_currency',
            'ffgc_expiry_days',
            'ffgc_email_from_name',
            'ffgc_email_from_address',
            'ffgc_email_subject',
            'ffgc_default_email_template',
            'ffgc_forms_enabled',
            'ffgc_gift_certificate_field_label',
            'ffgc_gift_certificate_field_placeholder',
            'ffgc_balance_page',
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
    }
    
    private function remove_posts() {
        // Remove all gift certificates
        $certificates = get_posts(array(
            'post_type' => 'ffgc_cert',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));
        
        foreach ($certificates as $certificate) {
            wp_delete_post($certificate->ID, true);
        }
        
        // Remove all designs
        $designs = get_posts(array(
            'post_type' => 'ffgc_design',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));
        
        foreach ($designs as $design) {
            wp_delete_post($design->ID, true);
        }
    }
    
    private function migrate_existing_data() {
        $current_version = get_option('ffgc_version', '0.0.0');
        
        // Only migrate if upgrading from a version before 1.0.0
        if (version_compare($current_version, '1.0.0', '<')) {
            // Migrate existing gift certificates from old post type to new
            $old_certificates = get_posts(array(
                'post_type' => 'gift_certificate',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            
            foreach ($old_certificates as $certificate) {
                // Update post type
                wp_update_post(array(
                    'ID' => $certificate->ID,
                    'post_type' => 'ffgc_cert'
                ));
            }
            
            // Migrate existing designs from old post type to new
            $old_designs = get_posts(array(
                'post_type' => 'gift_certificate_design',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            
            foreach ($old_designs as $design) {
                // Update post type
                wp_update_post(array(
                    'ID' => $design->ID,
                    'post_type' => 'ffgc_design'
                ));
            }
        }

        // Migrate meta keys for certificates
        if (version_compare($current_version, '1.0.1', '<')) {
            $certificates = get_posts(array(
                'post_type' => 'ffgc_certificate',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));

            foreach ($certificates as $certificate) {
                $amount = get_post_meta($certificate->ID, '_amount', true);
                if ($amount !== '') {
                    update_post_meta($certificate->ID, '_certificate_amount', $amount);
                    delete_post_meta($certificate->ID, '_amount');
                }

                $balance = get_post_meta($certificate->ID, '_balance', true);
                if ($balance !== '') {
                    update_post_meta($certificate->ID, '_certificate_balance', $balance);
                    delete_post_meta($certificate->ID, '_balance');
                }
            }
        }

        // Migrate usage logs from post meta to custom table
        if (version_compare($current_version, '1.0.2', '<')) {
            global $wpdb;
            $table = $wpdb->prefix . 'ffgc_usage_log';

            $certificates = get_posts(array(
                'post_type'      => 'ffgc_cert',
                'posts_per_page' => -1,
                'post_status'    => 'any',
            ));

            foreach ($certificates as $certificate) {
                $logs = get_post_meta($certificate->ID, '_usage_log', true);
                if (!is_array($logs)) {
                    continue;
                }

                foreach ($logs as $log) {
                    $wpdb->insert(
                        $table,
                        array(
                            'certificate_id' => $certificate->ID,
                            'form_id'        => intval($log['form_id'] ?? 0),
                            'submission_id'  => intval($log['submission_id'] ?? 0),
                            'amount_used'    => floatval($log['amount_used'] ?? 0),
                            'order_total'    => floatval($log['order_total'] ?? 0),
                            'created_at'     => isset($log['date']) ? $log['date'] : current_time('mysql'),
                        ),
                        array('%d','%d','%d','%f','%f','%s')
                    );
                }

                delete_post_meta($certificate->ID, '_usage_log');
            }
        }
        
        // Update version
        update_option('ffgc_version', FFGC_VERSION);
    }
} 