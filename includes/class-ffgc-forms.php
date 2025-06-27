<?php
/**
 * Forms Integration Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Forms {
    
    public function __construct() {
        add_action('fluentform_after_form_render', array($this, 'add_gift_certificate_field'));
        add_action('fluentform_before_insert_submission', array($this, 'process_gift_certificate_purchase'));
        add_action('fluentform_before_insert_submission', array($this, 'process_gift_certificate_application'));
        add_filter('fluentform_form_vars', array($this, 'add_form_vars'));
        add_action('wp_ajax_ffgc_validate_certificate', array($this, 'ajax_validate_certificate'));
        add_action('wp_ajax_nopriv_ffgc_validate_certificate', array($this, 'ajax_validate_certificate'));
    }
    
    public function add_gift_certificate_field($form) {
        $enabled_forms = get_option('ffgc_forms_enabled', array());
        
        if (!in_array($form->id, $enabled_forms)) {
            return;
        }
        
        // Check if this is a gift certificate purchase form or application form
        $form_type = $this->get_form_type($form->id);
        
        if ($form_type === 'purchase') {
            $this->add_purchase_fields($form);
        } elseif ($form_type === 'application') {
            $this->add_application_field($form);
        }
    }
    
    private function get_form_type($form_id) {
        // This is a simplified logic - you might want to add a meta field to forms
        // to explicitly mark them as purchase or application forms
        
        // Check if wpFluent function is available
        if (!function_exists('wpFluent')) {
            return 'application';
        }
        
        // For now, we'll check if the form has amount/price fields to determine type
        try {
            $form = wpFluent()->table('fluentform_forms')->where('id', $form_id)->first();
            
            if (!$form) {
                return 'application';
            }
            
            $form_fields = json_decode($form->form_fields, true);
            
            // Check if form has price/amount fields
            foreach ($form_fields as $field) {
                if (isset($field['element']) && in_array($field['element'], ['input_number', 'input_price', 'select'])) {
                    if (isset($field['attributes']['name']) && 
                        (strpos($field['attributes']['name'], 'amount') !== false || 
                         strpos($field['attributes']['name'], 'price') !== false)) {
                        return 'purchase';
                    }
                }
            }
        } catch (Exception $e) {
            // If there's an error, default to application
            return 'application';
        }
        
        return 'application';
    }
    
    private function add_purchase_fields($form) {
        $field_label = get_option('ffgc_gift_certificate_field_label', __('Gift Certificate Code', 'fluentforms-gift-certificates'));
        $placeholder = get_option('ffgc_gift_certificate_field_placeholder', __('Enter your gift certificate code', 'fluentforms-gift-certificates'));
        
        // Add recipient information fields
        echo '<div class="ffgc-purchase-fields">';
        echo '<h3>' . __('Gift Certificate Details', 'fluentforms-gift-certificates') . '</h3>';
        
        // Recipient Name
        echo '<div class="ffgc-field-group">';
        echo '<label for="ffgc_recipient_name">' . __('Recipient Name', 'fluentforms-gift-certificates') . ' *</label>';
        echo '<input type="text" id="ffgc_recipient_name" name="ffgc_recipient_name" required />';
        echo '</div>';
        
        // Recipient Email
        echo '<div class="ffgc-field-group">';
        echo '<label for="ffgc_recipient_email">' . __('Recipient Email', 'fluentforms-gift-certificates') . ' *</label>';
        echo '<input type="email" id="ffgc_recipient_email" name="ffgc_recipient_email" required />';
        echo '</div>';
        
        // Personal Message
        echo '<div class="ffgc-field-group">';
        echo '<label for="ffgc_personal_message">' . __('Personal Message', 'fluentforms-gift-certificates') . '</label>';
        echo '<textarea id="ffgc_personal_message" name="ffgc_personal_message" rows="3"></textarea>';
        echo '</div>';
        
        // Design Selection
        echo '<div class="ffgc-field-group">';
        echo '<label for="ffgc_design_id">' . __('Certificate Design', 'fluentforms-gift-certificates') . '</label>';
        echo '<select id="ffgc_design_id" name="ffgc_design_id">';
        echo '<option value="">' . __('Select a design', 'fluentforms-gift-certificates') . '</option>';
        
        $designs = get_posts(array(
            'post_type' => 'gift_certificate_design',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_is_active',
                    'value' => 'yes',
                    'compare' => '='
                )
            )
        ));
        
        foreach ($designs as $design) {
            echo '<option value="' . esc_attr($design->ID) . '">' . esc_html($design->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
    }
    
    private function add_application_field($form) {
        $field_label = get_option('ffgc_gift_certificate_field_label', __('Gift Certificate Code', 'fluentforms-gift-certificates'));
        $placeholder = get_option('ffgc_gift_certificate_field_placeholder', __('Enter your gift certificate code', 'fluentforms-gift-certificates'));
        
        echo '<div class="ffgc-application-field">';
        echo '<div class="ffgc-field-group">';
        echo '<label for="ffgc_certificate_code">' . esc_html($field_label) . '</label>';
        echo '<input type="text" id="ffgc_certificate_code" name="ffgc_certificate_code" placeholder="' . esc_attr($placeholder) . '" />';
        echo '<button type="button" id="ffgc_check_balance" class="ffgc-check-balance-btn">' . __('Check Balance', 'fluentforms-gift-certificates') . '</button>';
        echo '</div>';
        echo '<div id="ffgc_balance_result" class="ffgc-balance-result" style="display: none;"></div>';
        echo '</div>';
    }
    
    public function process_gift_certificate_purchase($insert_data) {
        $form_id = $insert_data['form_id'];
        $enabled_forms = get_option('ffgc_forms_enabled', array());
        
        if (!in_array($form_id, $enabled_forms)) {
            return;
        }
        
        if ($this->get_form_type($form_id) !== 'purchase') {
            return;
        }
        
        // Get form data
        $form_data = $insert_data['response'];
        
        // Extract gift certificate data
        $recipient_name = sanitize_text_field($form_data['ffgc_recipient_name'] ?? '');
        $recipient_email = sanitize_email($form_data['ffgc_recipient_email'] ?? '');
        $personal_message = sanitize_textarea_field($form_data['ffgc_personal_message'] ?? '');
        $design_id = intval($form_data['ffgc_design_id'] ?? 0);
        
        // Calculate amount from form data
        $amount = $this->calculate_amount_from_form($form_data);
        
        if ($amount <= 0) {
            return;
        }
        
        // Validate amount limits
        $min_amount = get_option('ffgc_min_amount', 10.00);
        $max_amount = get_option('ffgc_max_amount', 1000.00);
        
        if ($amount < $min_amount || $amount > $max_amount) {
            return;
        }
        
        // Create gift certificate
        $certificate_id = $this->create_gift_certificate(array(
            'amount' => $amount,
            'recipient_name' => $recipient_name,
            'recipient_email' => $recipient_email,
            'personal_message' => $personal_message,
            'design_id' => $design_id,
            'submission_id' => $insert_data['id']
        ));
        
        if ($certificate_id) {
            // Send email to recipient
            $this->send_gift_certificate_email($certificate_id);
        }
    }
    
    public function process_gift_certificate_application($insert_data) {
        $form_id = $insert_data['form_id'];
        $enabled_forms = get_option('ffgc_forms_enabled', array());
        
        if (!in_array($form_id, $enabled_forms)) {
            return;
        }
        
        if ($this->get_form_type($form_id) !== 'application') {
            return;
        }
        
        $form_data = $insert_data['response'];
        $certificate_code = sanitize_text_field($form_data['ffgc_certificate_code'] ?? '');
        
        if (empty($certificate_code)) {
            return;
        }
        
        // Validate and apply gift certificate
        $result = $this->apply_gift_certificate($certificate_code, $form_id, $insert_data['id']);
        
        if ($result['success']) {
            // Store the discount information in form data
            $insert_data['response']['gift_certificate_applied'] = true;
            $insert_data['response']['gift_certificate_amount'] = $result['amount'];
            $insert_data['response']['gift_certificate_code'] = $certificate_code;
        }
    }
    
    private function calculate_amount_from_form($form_data) {
        // This is a simplified calculation - you might need to adjust based on your form structure
        $amount = 0;
        
        foreach ($form_data as $key => $value) {
            if (strpos($key, 'amount') !== false || strpos($key, 'price') !== false) {
                $amount += floatval($value);
            }
        }
        
        return $amount;
    }
    
    private function create_gift_certificate($data) {
        $code = $this->generate_unique_code();
        $expiry_days = get_option('ffgc_expiry_days', 365);
        $expiry_date = date('Y-m-d', strtotime("+{$expiry_days} days"));
        
        $post_data = array(
            'post_title' => sprintf(__('Gift Certificate - %s', 'fluentforms-gift-certificates'), $code),
            'post_type' => 'gift_certificate',
            'post_status' => 'publish',
            'post_content' => ''
        );
        
        $certificate_id = wp_insert_post($post_data);
        
        if ($certificate_id) {
            update_post_meta($certificate_id, '_certificate_code', $code);
            update_post_meta($certificate_id, '_certificate_amount', $data['amount']);
            update_post_meta($certificate_id, '_certificate_status', 'unused');
            update_post_meta($certificate_id, '_certificate_used_amount', 0);
            update_post_meta($certificate_id, '_recipient_name', $data['recipient_name']);
            update_post_meta($certificate_id, '_recipient_email', $data['recipient_email']);
            update_post_meta($certificate_id, '_personal_message', $data['personal_message']);
            update_post_meta($certificate_id, '_design_id', $data['design_id']);
            update_post_meta($certificate_id, '_submission_id', $data['submission_id']);
            update_post_meta($certificate_id, '_expiry_date', $expiry_date);
            
            return $certificate_id;
        }
        
        return false;
    }
    
    private function generate_unique_code() {
        do {
            $prefix = 'GC';
            $suffix = strtoupper(substr(md5(uniqid()), 0, 6));
            $code = $prefix . '-' . $suffix;
            
            $existing = get_posts(array(
                'post_type' => 'gift_certificate',
                'meta_query' => array(
                    array(
                        'key' => '_certificate_code',
                        'value' => $code,
                        'compare' => '='
                    )
                ),
                'posts_per_page' => 1
            ));
        } while (!empty($existing));
        
        return $code;
    }
    
    private function apply_gift_certificate($code, $form_id, $submission_id) {
        $certificate = get_posts(array(
            'post_type' => 'gift_certificate',
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
            return array('success' => false, 'message' => __('Invalid gift certificate code.', 'fluentforms-gift-certificates'));
        }
        
        $certificate = $certificate[0];
        $status = get_post_meta($certificate->ID, '_certificate_status', true);
        $amount = get_post_meta($certificate->ID, '_certificate_amount', true);
        $used_amount = get_post_meta($certificate->ID, '_certificate_used_amount', true);
        $expiry_date = get_post_meta($certificate->ID, '_expiry_date', true);
        
        // Check if expired
        if (strtotime($expiry_date) < time()) {
            return array('success' => false, 'message' => __('This gift certificate has expired.', 'fluentforms-gift-certificates'));
        }
        
        // Check if already used
        if ($status === 'used') {
            return array('success' => false, 'message' => __('This gift certificate has already been used.', 'fluentforms-gift-certificates'));
        }
        
        $remaining = $amount - $used_amount;
        
        if ($remaining <= 0) {
            return array('success' => false, 'message' => __('This gift certificate has no remaining balance.', 'fluentforms-gift-certificates'));
        }
        
        // Log the usage
        $this->log_certificate_usage($certificate->ID, $form_id, $submission_id, $remaining);
        
        // Update certificate status
        update_post_meta($certificate->ID, '_certificate_used_amount', $amount);
        update_post_meta($certificate->ID, '_certificate_status', 'used');
        
        return array('success' => true, 'amount' => $remaining);
    }
    
    private function log_certificate_usage($certificate_id, $form_id, $submission_id, $amount_used) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ffgc_usage_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'certificate_id' => $certificate_id,
                'form_id' => $form_id,
                'submission_id' => $submission_id,
                'amount_used' => $amount_used,
                'order_total' => 0, // You might want to calculate this from form data
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%f', '%f', '%s')
        );
    }
    
    private function send_gift_certificate_email($certificate_id) {
        $email = new FFGC_Email();
        $email->send_gift_certificate($certificate_id);
    }
    
    public function add_form_vars($vars) {
        $vars['ffgc_ajax_url'] = admin_url('admin-ajax.php');
        $vars['ffgc_nonce'] = wp_create_nonce('ffgc_nonce');
        return $vars;
    }
    
    public function ajax_validate_certificate() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['code']);
        
        if (empty($code)) {
            wp_send_json_error(__('Please enter a gift certificate code.', 'fluentforms-gift-certificates'));
        }
        
        $certificate = get_posts(array(
            'post_type' => 'gift_certificate',
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
        $status = get_post_meta($certificate->ID, '_certificate_status', true);
        $amount = get_post_meta($certificate->ID, '_certificate_amount', true);
        $used_amount = get_post_meta($certificate->ID, '_certificate_used_amount', true);
        $expiry_date = get_post_meta($certificate->ID, '_expiry_date', true);
        
        if (strtotime($expiry_date) < time()) {
            wp_send_json_error(__('This gift certificate has expired.', 'fluentforms-gift-certificates'));
        }
        
        if ($status === 'used') {
            wp_send_json_error(__('This gift certificate has already been used.', 'fluentforms-gift-certificates'));
        }
        
        $remaining = $amount - $used_amount;
        
        wp_send_json_success(array(
            'balance' => $remaining,
            'total' => $amount,
            'used' => $used_amount,
            'expiry_date' => $expiry_date
        ));
    }
} 