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
        // Fluent Forms hooks
        add_action('fluentform_loaded', array($this, 'register_custom_fields'));
        // Automatic field injection deprecated
        // add_action('fluentform_after_form_render', array($this, 'add_gift_certificate_field'));
        // Legacy purchase handling hooks removed
        // add_action('fluentform_before_insert_submission', array($this, 'process_gift_certificate_purchase'));
        add_action('fluentform_before_insert_submission', array($this, 'process_gift_certificate_application'));
        add_filter('fluentform_form_vars', array($this, 'add_form_vars'));
        // add_action('fluentform_after_payment_success', array($this, 'process_payment_success'));
        
        // AJAX handlers
        add_action('wp_ajax_ffgc_validate_certificate', array($this, 'ajax_validate_certificate'));
        add_action('wp_ajax_nopriv_ffgc_validate_certificate', array($this, 'ajax_validate_certificate'));
        add_action('wp_ajax_ffgc_get_designs', array($this, 'ajax_get_designs'));
        add_action('wp_ajax_nopriv_ffgc_get_designs', array($this, 'ajax_get_designs'));
        add_action('wp_ajax_ffgc_toggle_design_status', array($this, 'ajax_toggle_design_status'));
        add_action('wp_ajax_ffgc_update_certificate_status', array($this, 'ajax_update_certificate_status'));
        add_action('wp_ajax_ffgc_bulk_action', array($this, 'ajax_bulk_action'));

        // New AJAX handlers
        add_action('wp_ajax_ffgc_get_design_details', array($this, 'ajax_get_design_details'));
        add_action('wp_ajax_nopriv_ffgc_get_design_details', array($this, 'ajax_get_design_details'));
        add_action('wp_ajax_ffgc_preview_design', array($this, 'ajax_preview_design'));
        add_action('wp_ajax_nopriv_ffgc_preview_design', array($this, 'ajax_preview_design'));
        add_action('wp_ajax_ffgc_get_usage_history', array($this, 'ajax_get_usage_history'));
        add_action('wp_ajax_nopriv_ffgc_get_usage_history', array($this, 'ajax_get_usage_history'));
        add_action('wp_ajax_ffgc_resend_email', array($this, 'ajax_resend_email'));

        // Admin hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('save_post_ffgc_design', array($this, 'clear_design_cache'));
        add_action('delete_post', array($this, 'maybe_clear_design_cache'));
        add_action('trashed_post', array($this, 'maybe_clear_design_cache'));

        // Coupon integration
        add_filter('fluentform/validate_coupon', array($this, 'validate_coupon'), 10, 2);
        add_action('fluentform_coupon_applied', array($this, 'coupon_applied'), 10, 3);
    }
    
    /**
     * Register custom Fluent Forms field types
     */
    public function register_custom_fields() {
        if (!class_exists('FluentForm')) {
            return;
        }
        
        // Register custom field types with Fluent Forms
        add_filter('fluentform_editor_components', array($this, 'add_custom_components'));
        
        // Add custom field renderers
        add_action('fluentform_render_item_gift_certificate_design', array($this, 'render_design_field'), 10, 2);
        add_action('fluentform_render_item_gift_certificate_redemption', array($this, 'render_redemption_field'), 10, 2);
        
        // Add field templates for the editor in admin area
        add_action('admin_footer', array($this, 'add_field_templates'));
        
        // Add custom field scripts and styles
        add_action('fluentform_editor_scripts', array($this, 'add_editor_scripts'));
    }
    
    /**
     * Add custom components to Fluent Forms editor
     */
    public function add_custom_components($components) {
        $components['gift_certificate_design'] = array(
            'index' => 15,
            'element' => 'gift_certificate_design',
            'attributes' => array(
                'name' => 'gift_certificate_design',
                'class' => '',
                'value' => '',
                'placeholder' => '',
                'required' => false,
                'label' => __('Gift Certificate Design', 'fluentforms-gift-certificates'),
                'help_message' => __('Select a design for your gift certificate', 'fluentforms-gift-certificates'),
                'validation_rules' => array(
                    'required' => array(
                        'value' => false,
                        'message' => __('This field is required', 'fluentforms-gift-certificates')
                    )
                )
            ),
            'settings' => array(
                'container_class' => '',
                'label_placement' => 'top',
                'help_message' => '',
                'admin_field_label' => '',
                'label_placement_options' => array(
                    'top' => 'Top',
                    'bottom' => 'Bottom',
                    'left' => 'Left',
                    'right' => 'Right',
                    'hidden' => 'Hidden'
                ),
                'display_type' => 'grid', // grid or dropdown
                'columns' => 3,
                'show_design_info' => true
            ),
            'editor_options' => array(
                'title' => __('Gift Certificate Design', 'fluentforms-gift-certificates'),
                'icon' => 'el-icon-picture',
                'template' => 'giftCertificateDesign'
            )
        );
        
        $components['gift_certificate_redemption'] = array(
            'index' => 16,
            'element' => 'gift_certificate_redemption',
            'attributes' => array(
                'name' => 'gift_certificate_redemption',
                'class' => '',
                'value' => '',
                'placeholder' => __('Enter gift certificate code', 'fluentforms-gift-certificates'),
                'required' => false,
                'label' => __('Gift Certificate Code', 'fluentforms-gift-certificates'),
                'help_message' => __('Enter your gift certificate code to apply discount', 'fluentforms-gift-certificates'),
                'validation_rules' => array(
                    'required' => array(
                        'value' => false,
                        'message' => __('This field is required', 'fluentforms-gift-certificates')
                    )
                )
            ),
            'settings' => array(
                'container_class' => '',
                'label_placement' => 'top',
                'help_message' => '',
                'admin_field_label' => '',
                'label_placement_options' => array(
                    'top' => 'Top',
                    'bottom' => 'Bottom',
                    'left' => 'Left',
                    'right' => 'Right',
                    'hidden' => 'Hidden'
                ),
                'show_balance_check' => true,
                'auto_apply' => false
            ),
            'editor_options' => array(
                'title' => __('Gift Certificate Redemption', 'fluentforms-gift-certificates'),
                'icon' => 'el-icon-ticket',
                'template' => 'giftCertificateRedemption'
            )
        );
        
        return $components;
    }
    
    /**
     * Add field templates for Fluent Forms editor
     */
    public function add_field_templates() {
        if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'fluent_forms') {
            return;
        }
        ?>
        <script type="text/template" id="ffgc-design-field-template">
            <div class="ffgc-design-field" data-field-id="{{attributes.name}}">
                <input type="hidden" name="{{attributes.name}}" value="{{attributes.value}}" {{#if attributes.required}}required{{/if}} />
                <div class="ffgc-design-grid" style="grid-template-columns: repeat({{settings.columns}}, 1fr);">
                    <!-- Design options will be populated by JavaScript -->
                </div>
            </div>
        </script>
        
        <script type="text/template" id="ffgc-redemption-field-template">
            <div class="ffgc-redemption-field" data-field-id="{{attributes.name}}">
                <div class="ffgc-code-input-group">
                    <input type="text" name="{{attributes.name}}" value="{{attributes.value}}" 
                           placeholder="{{attributes.placeholder}}" {{#if attributes.required}}required{{/if}} 
                           class="ffgc-certificate-code" data-auto-apply="{{settings.auto_apply}}" />
                    {{#if settings.show_balance_check}}
                    <button type="button" class="ffgc-check-balance-btn"><?php _e('Check Balance', 'fluentforms-gift-certificates'); ?></button>
                    {{/if}}
                </div>
                <div class="ffgc-balance-result" style="display: none;"></div>
                <div class="ffgc-redemption-result" style="display: none;"></div>
            </div>
        </script>
        <?php
    }
    
    /**
     * Render gift certificate design selection field
     */
    public function render_design_field($field, $form) {
        $field_name = $field['attributes']['name'];
        $field_id = 'ffgc_design_' . $form->id . '_' . $field_name;
        $required = $field['attributes']['required'] ?? false;
        $display_type = $field['settings']['display_type'] ?? 'grid';
        $columns = $field['settings']['columns'] ?? 3;
        $show_info = $field['settings']['show_design_info'] ?? true;
        
        $designs = $this->get_active_design_data();
        
        echo '<div class="ffgc-design-field" data-field-id="' . esc_attr($field_id) . '">';
        echo '<input type="hidden" name="' . esc_attr($field_name) . '" id="' . esc_attr($field_id) . '" value="" ' . ($required ? 'required' : '') . ' />';
        
        if ($display_type === 'grid') {
            echo '<div class="ffgc-design-grid" style="grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr);">';
            foreach ($designs as $design) {
                $image_url = $design['image_url'];
                $min_amount = $design['min_amount'];
                $max_amount = $design['max_amount'];

                echo '<div class="ffgc-design-option" data-design-id="' . esc_attr($design['id']) . '">';
                if ($image_url) {
                    echo '<div class="ffgc-design-image">';
                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($design['title']) . '" />';
                    echo '</div>';
                }
                echo '<div class="ffgc-design-info">';
                echo '<h4>' . esc_html($design['title']) . '</h4>';
                if ($show_info && ($min_amount || $max_amount)) {
                    echo '<p class="ffgc-design-range">';
                    if ($min_amount && $max_amount) {
                        echo sprintf(__('Range: %s - %s', 'fluentforms-gift-certificates'),
                            ffgc_format_price($min_amount), ffgc_format_price($max_amount));
                    } elseif ($min_amount) {
                        echo sprintf(__('Minimum: %s', 'fluentforms-gift-certificates'), ffgc_format_price($min_amount));
                    } elseif ($max_amount) {
                        echo sprintf(__('Maximum: %s', 'fluentforms-gift-certificates'), ffgc_format_price($max_amount));
                    }
                    echo '</p>';
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<select name="' . esc_attr($field_name) . '" id="' . esc_attr($field_id) . '" ' . ($required ? 'required' : '') . '>';
            echo '<option value="">' . __('Select a design', 'fluentforms-gift-certificates') . '</option>';
            foreach ($designs as $design) {
                echo '<option value="' . esc_attr($design['id']) . '">' . esc_html($design['title']) . '</option>';
            }
            echo '</select>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render gift certificate redemption field
     */
    public function render_redemption_field($field, $form) {
        $field_name = $field['attributes']['name'];
        $field_id = 'ffgc_redemption_' . $form->id . '_' . $field_name;
        $placeholder = $field['attributes']['placeholder'] ?? __('Enter gift certificate code', 'fluentforms-gift-certificates');
        $required = $field['attributes']['required'] ?? false;
        $show_balance_check = $field['settings']['show_balance_check'] ?? true;
        $auto_apply = $field['settings']['auto_apply'] ?? false;
        
        echo '<div class="ffgc-redemption-field" data-field-id="' . esc_attr($field_id) . '">';
        echo '<div class="ffgc-code-input-group">';
        echo '<input type="text" name="' . esc_attr($field_name) . '" id="' . esc_attr($field_id) . '" 
              placeholder="' . esc_attr($placeholder) . '" ' . ($required ? 'required' : '') . ' 
              class="ffgc-certificate-code" data-auto-apply="' . ($auto_apply ? 'true' : 'false') . '" />';
        
        if ($show_balance_check) {
            echo '<button type="button" class="ffgc-check-balance-btn">' . __('Check Balance', 'fluentforms-gift-certificates') . '</button>';
        }
        echo '</div>';
        
        echo '<div id="ffgc_balance_result_' . esc_attr($field_id) . '" class="ffgc-balance-result" style="display: none;"></div>';
        echo '<div id="ffgc_redemption_result_' . esc_attr($field_id) . '" class="ffgc-redemption-result" style="display: none;"></div>';
        echo '</div>';
    }
    
    /**
     * Add gift certificate field to enabled forms
     */
    public function add_gift_certificate_field($form) {
        // Deprecated: fields should be added via the form builder.
        return;
    }
    
    /**
     * Determine form type based on form configuration
     */
    private function get_form_type($form_id) {
        // Check if wpFluent function is available
        if (!function_exists('wpFluent')) {
            return 'application';
        }
        
        try {
            $form = wpFluent()->table('fluentform_forms')->where('id', $form_id)->first();
            
            if (!$form) {
                return 'application';
            }
            
            $form_fields = json_decode($form->form_fields, true);
            
            // Check if form has payment fields or custom payment amount
            foreach ($form_fields as $field) {
                if (isset($field['element']) && in_array($field['element'], ['payment_method', 'custom_payment_amount', 'input_price'])) {
                    return 'purchase';
                }
            }
        } catch (Exception $e) {
            return 'application';
        }
        
        return 'application';
    }
    
    /**
     * Add purchase-specific fields
     */
    private function add_purchase_fields($form) {
        // These fields are now handled by custom field types
        // The design selection and other fields will be added via the form builder
    }
    
    /**
     * Add application field
     */
    private function add_application_field($form) {
        // This is now handled by the custom redemption field type
    }
    
    
    /**
     * Process gift certificate application
     */
    public function process_gift_certificate_application($insert_data) {
        $form_id = $insert_data['form_id'];
        
        if ($this->get_form_type($form_id) !== 'application') {
            return;
        }
        
        // Get form data
        $form_data = $insert_data['response'];
        
        // Look for gift certificate code in form data
        $certificate_code = '';
        foreach ($form_data as $key => $value) {
            if (strpos($key, 'gift_certificate_redemption') !== false || 
                strpos($key, 'certificate_code') !== false) {
                $certificate_code = sanitize_text_field($value);
                break;
            }
        }
        
        if (!$certificate_code) {
            return;
        }
        
        // Apply gift certificate
        $this->apply_gift_certificate($certificate_code, $form_id, $insert_data['id']);
    }
    
    /**
     * Calculate amount from form data
     */
    private function calculate_amount_from_form($form_data) {
        $amount = 0;
        
        // Look for payment fields
        foreach ($form_data as $key => $value) {
            if (strpos($key, 'payment_amount') !== false || 
                strpos($key, 'custom_payment_amount') !== false ||
                strpos($key, 'amount') !== false) {
                $amount = floatval($value);
                break;
            }
        }
        
        return $amount;
    }
    
    /**
     * Create gift certificate
     */
    private function create_gift_certificate($data) {
        $code   = $this->generate_unique_code();
        $amount = apply_filters('ffgc_certificate_amount', $data['amount'], $data);
        
        $post_data = array(
            'post_title' => sprintf(__('Gift Certificate - %s', 'fluentforms-gift-certificates'), $code),
            'post_content' => $data['personal_message'],
            'post_status' => 'publish',
            'post_type' => 'ffgc_cert'
        );
        
        $certificate_id = wp_insert_post($post_data);
        
        if ($certificate_id) {
            $expiry = date('Y-m-d H:i:s', strtotime('+' . get_option('ffgc_expiry_days', 365) . ' days'));

            update_post_meta($certificate_id, '_certificate_code', $code);
            update_post_meta($certificate_id, '_certificate_amount', $amount);
            update_post_meta($certificate_id, '_certificate_balance', $amount);
            update_post_meta($certificate_id, '_certificate_used_amount', 0);
            update_post_meta($certificate_id, '_recipient_name', $data['recipient_name']);
            update_post_meta($certificate_id, '_recipient_email', $data['recipient_email']);
            update_post_meta($certificate_id, '_design_id', $data['design_id']);
            update_post_meta($certificate_id, '_submission_id', $data['submission_id']);
            update_post_meta($certificate_id, '_created_date', current_time('mysql'));
            update_post_meta($certificate_id, '_expiry_date', $expiry);
            update_post_meta($certificate_id, '_status', 'active');

            // Create matching Fluent Forms coupon
            ffgc_create_coupon($code, $amount, $expiry);

            do_action('ffgc_certificate_created', $certificate_id, $data);

            return $certificate_id;
        }
        
        return false;
    }
    
    /**
     * Generate unique certificate code
     */
    private function generate_unique_code() {
        do {
            $code = strtoupper(bin2hex(random_bytes(6)));
            $existing = get_posts(array(
                'post_type'      => 'ffgc_cert',
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => '_certificate_code',
                        'value'   => $code,
                        'compare' => '=',
                    ),
                ),
                'posts_per_page' => 1,
            ));
        } while (!empty($existing));
        
        return apply_filters('ffgc_certificate_code', $code);
    }
    
    /**
     * Apply gift certificate
     */
    private function apply_gift_certificate($code, $form_id, $submission_id) {
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
            return false;
        }
        
        $certificate = $certificate[0];
        $balance = get_post_meta($certificate->ID, '_certificate_balance', true);
        $status = get_post_meta($certificate->ID, '_status', true);
        $expiry_date = get_post_meta($certificate->ID, '_expiry_date', true);
        
        // Check if certificate is valid
        if ($status !== 'active' || $balance <= 0) {
            return false;
        }
        
        if ($expiry_date && strtotime($expiry_date) < time()) {
            update_post_meta($certificate->ID, '_status', 'expired');
            ffgc_delete_coupon($code);
            do_action('ffgc_certificate_expired', $certificate->ID);
            return false;
        }
        
        // Calculate amount to apply (for now, apply full balance)
        $amount_to_apply = $balance;
        
        // Update balance
        $new_balance = $balance - $amount_to_apply;
        update_post_meta($certificate->ID, '_certificate_balance', $new_balance);
        $used_amount = get_post_meta($certificate->ID, '_certificate_used_amount', true);
        $used_amount = $used_amount ? floatval($used_amount) : 0;
        update_post_meta($certificate->ID, '_certificate_used_amount', $used_amount + $amount_to_apply);

        if ($new_balance <= 0) {
            update_post_meta($certificate->ID, '_status', 'used');
            ffgc_delete_coupon($code);
        }
        
        // Log usage
        $this->log_certificate_usage($certificate->ID, $form_id, $submission_id, $amount_to_apply);

        do_action('ffgc_certificate_applied', $certificate->ID, $amount_to_apply, $new_balance, $form_id, $submission_id);

        return $amount_to_apply;
    }
    
    /**
     * Log certificate usage
     */
    private function log_certificate_usage($certificate_id, $form_id, $submission_id, $amount_used) {
        // Insert into the dedicated usage log table
        $this->add_usage_log($certificate_id, $form_id, $submission_id, $amount_used);
    }
    
    /**
     * Send gift certificate email
     */
    private function send_gift_certificate_email($certificate_id) {
        $email_handler = new FFGC_Email();
        $email_handler->send_gift_certificate_email($certificate_id);
    }

    /**
     * Insert a usage log entry
     */
    private function add_usage_log($certificate_id, $form_id, $submission_id, $amount_used, $order_total = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffgc_usage_log';

        $wpdb->insert(
            $table,
            array(
                'certificate_id' => $certificate_id,
                'form_id'        => $form_id,
                'submission_id'  => $submission_id,
                'amount_used'    => $amount_used,
                'order_total'    => $order_total,
                'created_at'     => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%f', '%f', '%s')
        );
    }

    /**
     * Retrieve usage log entries for a certificate
     */
    private function get_usage_logs($certificate_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffgc_usage_log';

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE certificate_id = %d ORDER BY created_at ASC", $certificate_id),
            ARRAY_A
        );

        if ($rows) {
            return array_map(function($row) {
                return array(
                    'date'         => $row['created_at'],
                    'form_id'      => intval($row['form_id']),
                    'submission_id'=> intval($row['submission_id']),
                    'amount_used'  => floatval($row['amount_used']),
                    'order_total'  => floatval($row['order_total'])
                );
            }, $rows);
        }

        // Fallback to old post meta logs if no rows found
        $meta_log = get_post_meta($certificate_id, '_usage_log', true);
        return is_array($meta_log) ? $meta_log : array();
    }

    /**
     * Delete usage logs for a certificate
     */
    private function delete_usage_logs($certificate_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffgc_usage_log';
        $wpdb->delete($table, array('certificate_id' => $certificate_id), array('%d'));
    }
    
    /**
     * Add form variables
     */
    public function add_form_vars($vars) {
        $vars['ffgc_ajax_url'] = admin_url('admin-ajax.php');
        $vars['ffgc_nonce'] = wp_create_nonce('ffgc_nonce');
        return $vars;
    }

    /**
     * Get active design IDs, cached via transient
     */
    private function get_active_design_ids() {
        $ids = get_transient('ffgc_active_design_ids');
        if ($ids === false) {
            $query = new WP_Query(array(
                'post_type'   => 'ffgc_design',
                'post_status' => 'publish',
                'meta_query'  => array(
                    array(
                        'key'     => '_is_active',
                        'value'   => 'yes',
                        'compare' => '='
                    )
                ),
                'fields'   => 'ids',
                'nopaging' => true
            ));
            $ids = $query->posts;
            set_transient('ffgc_active_design_ids', $ids, HOUR_IN_SECONDS);
        }
        return $ids;
    }

    /**
     * Get active design data array cached via transient
     */
    private function get_active_design_data() {
        $data = get_transient('ffgc_active_design_data');
        if ($data === false) {
            $data = array();
            foreach ($this->get_active_design_ids() as $id) {
                $post       = get_post($id);
                $image_id   = get_post_meta($id, '_design_image', true);
                $data[] = array(
                    'id'          => $id,
                    'title'       => $post->post_title,
                    'description' => $post->post_content,
                    'image_url'   => wp_get_attachment_image_url($image_id, 'medium'),
                    'min_amount'  => get_post_meta($id, '_min_amount', true),
                    'max_amount'  => get_post_meta($id, '_max_amount', true)
                );
            }
            set_transient('ffgc_active_design_data', $data, HOUR_IN_SECONDS);
        }
        return $data;
    }

    /**
     * Clear cached design IDs
     */
    public function clear_design_cache() {
        delete_transient('ffgc_active_design_ids');
        delete_transient('ffgc_active_design_data');
    }

    /**
     * Clear design cache when a design is deleted or trashed
     */
    public function maybe_clear_design_cache($post_id) {
        if (get_post_type($post_id) === 'ffgc_design') {
            $this->clear_design_cache();
        }
    }
    
    /**
     * AJAX: Validate certificate
     */
    public function ajax_validate_certificate() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['code'] ?? '');
        
        if (!$code) {
            wp_send_json_error(__('Please enter a certificate code', 'fluentforms-gift-certificates'));
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
            wp_send_json_error(__('Invalid certificate code', 'fluentforms-gift-certificates'));
        }
        
        $certificate = $certificate[0];
        $balance = get_post_meta($certificate->ID, '_certificate_balance', true);
        $status = get_post_meta($certificate->ID, '_status', true);
        $expiry_date = get_post_meta($certificate->ID, '_expiry_date', true);
        
        if ($status !== 'active') {
            wp_send_json_error(__('Certificate is not active', 'fluentforms-gift-certificates'));
        }
        
        if ($balance <= 0) {
            wp_send_json_error(__('Certificate has no remaining balance', 'fluentforms-gift-certificates'));
        }
        
        if ($expiry_date && strtotime($expiry_date) < time()) {
            wp_send_json_error(__('Certificate has expired', 'fluentforms-gift-certificates'));
        }
        
        wp_send_json_success(array(
            'balance' => $balance,
            'expiry_date' => $expiry_date,
            'message' => sprintf(__('Certificate valid. Balance: %s', 'fluentforms-gift-certificates'), ffgc_format_price($balance))
        ));
    }
    
    /**
     * AJAX: Get designs
     */
    public function ajax_get_designs() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        $design_data = $this->get_active_design_data();

        wp_send_json_success($design_data);
    }
    
    /**
     * AJAX: Toggle design status
     */
    public function ajax_toggle_design_status() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $design_id = intval($_POST['design_id'] ?? 0);

        if (!isset($_POST['is_active'])) {
            wp_send_json_error('Missing design status');
        }

        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === 'true';
        
        if (!$design_id) {
            wp_send_json_error('Invalid design ID');
        }
        
        $status = $is_active ? 'yes' : 'no';
        update_post_meta($design_id, '_is_active', $status);
        $this->clear_design_cache();

        wp_send_json_success('Design status updated successfully');
    }
    
    /**
     * AJAX: Update certificate status
     */
    public function ajax_update_certificate_status() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $certificate_id = intval($_POST['certificate_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$certificate_id || !in_array($status, array('active', 'used', 'expired', 'cancelled'))) {
            wp_send_json_error('Invalid certificate ID or status');
        }
        
        update_post_meta($certificate_id, '_status', $status);
        
        wp_send_json_success('Certificate status updated successfully');
    }
    
    /**
     * AJAX: Bulk actions
     */
    public function ajax_bulk_action() {
        check_ajax_referer('ffgc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $bulk_action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $certificate_ids = array_map('intval', $_POST['certificate_ids'] ?? array());
        
        if (empty($certificate_ids)) {
            wp_send_json_error('No certificates selected');
        }
        
        $updated_count = 0;
        
        switch ($bulk_action) {
            case 'activate':
                foreach ($certificate_ids as $certificate_id) {
                    update_post_meta($certificate_id, '_status', 'active');
                    $updated_count++;
                }
                break;
                
            case 'deactivate':
                foreach ($certificate_ids as $certificate_id) {
                    update_post_meta($certificate_id, '_status', 'cancelled');
                    $updated_count++;
                }
                break;
                
            case 'mark_expired':
                foreach ($certificate_ids as $certificate_id) {
                    update_post_meta($certificate_id, '_status', 'expired');
                    $updated_count++;
                }
                break;
                
            case 'resend_email':
                foreach ($certificate_ids as $certificate_id) {
                    $this->send_gift_certificate_email($certificate_id);
                    $updated_count++;
                }
                break;
                
            default:
                wp_send_json_error('Invalid bulk action');
        }
        
        wp_send_json_success(array(
            'message' => sprintf('%d certificate(s) updated successfully', $updated_count),
            'updated_count' => $updated_count
        ));
    }

    /**
     * AJAX: Purchase certificate
     */

    /**
     * AJAX: Get design details
     */
    public function ajax_get_design_details() {
        check_ajax_referer('ffgc_nonce', 'nonce');

        $design_id = intval($_POST['design_id'] ?? 0);
        if (!$design_id) {
            wp_send_json_error('');
        }

        $min = get_post_meta($design_id, '_min_amount', true);
        $max = get_post_meta($design_id, '_max_amount', true);

        wp_send_json_success(array(
            'min_amount' => $min ?: '',
            'max_amount' => $max ?: ''
        ));
    }

    /**
     * AJAX: Preview design
     */
    public function ajax_preview_design() {
        check_ajax_referer('ffgc_nonce', 'nonce');

        $design_id = intval($_POST['design_id'] ?? 0);
        if (!$design_id) {
            wp_send_json_error('');
        }

        $image_id  = get_post_meta($design_id, '_design_image', true);
        $image_url = wp_get_attachment_image_url($image_id, 'large');
        $title     = get_the_title($design_id);
        $content   = get_post_field('post_content', $design_id);

        $html  = '';
        if ($image_url) {
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '" />';
        }
        if ($content) {
            $html .= '<p>' . esc_html($content) . '</p>';
        }

        wp_send_json_success($html);
    }

    /**
     * AJAX: Get usage history
     */
    public function ajax_get_usage_history() {
        check_ajax_referer('ffgc_nonce', 'nonce');

        $code = sanitize_text_field($_POST['code'] ?? '');
        if (!$code) {
            wp_send_json_error('');
        }

        $certificate = get_posts(array(
            'post_type' => 'ffgc_cert',
            'meta_query' => array(
                array(
                    'key'   => '_certificate_code',
                    'value' => $code,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));

        if (empty($certificate)) {
            wp_send_json_error('');
        }

        $certificate   = $certificate[0];
        $usage_log     = $this->get_usage_logs($certificate->ID);
        if (!is_array($usage_log)) {
            wp_send_json_success(array());
        }

        $history = array();
        foreach ($usage_log as $entry) {
            $form_title = '';
            if (!empty($entry['form_id'])) {
                $form = get_post($entry['form_id']);
                if ($form) {
                    $form_title = $form->post_title;
                }
            }
            $history[] = array(
                'date'       => date_i18n(get_option('date_format'), strtotime($entry['date'])),
                'amount'     => number_format($entry['amount_used'], 2),
                'form_title' => $form_title
            );
        }

        wp_send_json_success($history);
    }

    /**
     * AJAX: Resend certificate email
     */
    public function ajax_resend_email() {
        check_ajax_referer('ffgc_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('');
        }

        $certificate_id = intval($_POST['certificate_id'] ?? 0);
        if (!$certificate_id) {
            wp_send_json_error('');
        }

        $this->send_gift_certificate_email($certificate_id);

        wp_send_json_success(true);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'fluent_forms') !== false) {
            wp_enqueue_script('ffgc-admin', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery'), FFGC_VERSION, true);
            wp_enqueue_style('ffgc-admin', plugin_dir_url(__FILE__) . '../assets/css/admin.css', array(), FFGC_VERSION);
            wp_localize_script('ffgc-admin', 'ffgc_strings', ffgc_get_script_strings());
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function frontend_scripts() {
        wp_enqueue_script('ffgc-frontend', plugin_dir_url(__FILE__) . '../assets/js/frontend.js', array('jquery'), FFGC_VERSION, true);
        wp_enqueue_style('ffgc-frontend', plugin_dir_url(__FILE__) . '../assets/css/frontend.css', array(), FFGC_VERSION);

        $currency = get_option('ffgc_currency', 'USD');
        wp_localize_script('ffgc-frontend', 'ffgc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ffgc_nonce'),
            'currency_symbol' => ffgc_get_currency_symbol($currency)
        ));
        wp_localize_script('ffgc-frontend', 'ffgc_strings', ffgc_get_script_strings());
    }

    /**
     * Validate Fluent Forms coupon codes against gift certificate data.
     *
     * @param bool  $is_valid   Whether the coupon is valid so far.
     * @param array $coupon     Coupon record from Fluent Forms.
     * @return bool
     */
    public function validate_coupon($is_valid, $coupon) {
        $code = $coupon['coupon_code'] ?? '';
        if (!$code) {
            return $is_valid;
        }

        $certificate = get_posts(array(
            'post_type'      => 'ffgc_cert',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => '_certificate_code',
                    'value'   => $code,
                    'compare' => '=',
                ),
            ),
        ));

        if (empty($certificate)) {
            return false;
        }

        $cert       = $certificate[0];
        $balance    = get_post_meta($cert->ID, '_certificate_balance', true);
        $status     = get_post_meta($cert->ID, '_status', true);
        $expiry     = get_post_meta($cert->ID, '_expiry_date', true);

        if ($status !== 'active' || $balance <= 0) {
            return false;
        }

        if ($expiry && strtotime($expiry) < time()) {
            update_post_meta($cert->ID, '_status', 'expired');
            ffgc_delete_coupon($code);
            return false;
        }

        return $is_valid;
    }

    /**
     * Handle coupon application and deduct the certificate balance.
     *
     * @param string $code          Coupon code applied.
     * @param int    $form_id       Form ID.
     * @param int    $submission_id Submission ID.
     * @return void
     */
    public function coupon_applied($code, $form_id, $submission_id) {
        $this->apply_gift_certificate($code, $form_id, $submission_id);
    }
    
    /**
     * Add editor scripts for custom fields
     */
    public function add_editor_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Register custom field types with Fluent Forms editor
            if (typeof window.FluentFormEditor !== 'undefined') {
                // Gift Certificate Design field
                window.FluentFormEditor.addFieldType('gift_certificate_design', {
                    title: '<?php _e('Gift Certificate Design', 'fluentforms-gift-certificates'); ?>',
                    icon: 'el-icon-picture',
                    category: 'Advanced Fields',
                    template: function(field) {
                        return '<div class="ffgc-design-field" data-field-id="' + field.attributes.name + '">' +
                               '<input type="hidden" name="' + field.attributes.name + '" value="' + (field.attributes.value || '') + '" ' + (field.attributes.required ? 'required' : '') + ' />' +
                               '<div class="ffgc-design-grid" style="grid-template-columns: repeat(' + (field.settings.columns || 3) + ', 1fr);">' +
                               '<div class="ffgc-loading-designs"><?php _e('Loading designs...', 'fluentforms-gift-certificates'); ?></div>' +
                               '</div>' +
                               '</div>';
                    },
                    getValue: function(field) {
                        return field.$el.find('input[type="hidden"]').val();
                    },
                    setValue: function(field, value) {
                        field.$el.find('input[type="hidden"]').val(value);
                        field.$el.find('.ffgc-design-option').removeClass('selected');
                        field.$el.find('[data-design-id="' + value + '"]').addClass('selected');
                    }
                });

                // Gift Certificate Redemption field
                window.FluentFormEditor.addFieldType('gift_certificate_redemption', {
                    title: '<?php _e('Gift Certificate Redemption', 'fluentforms-gift-certificates'); ?>',
                    icon: 'el-icon-ticket',
                    category: 'Advanced Fields',
                    template: function(field) {
                        return '<div class="ffgc-redemption-field" data-field-id="' + field.attributes.name + '">' +
                               '<div class="ffgc-code-input-group">' +
                               '<input type="text" name="' + field.attributes.name + '" value="' + (field.attributes.value || '') + '" ' +
                               'placeholder="' + (field.attributes.placeholder || '<?php _e('Enter gift certificate code', 'fluentforms-gift-certificates'); ?>') + '" ' +
                               (field.attributes.required ? 'required' : '') + ' class="ffgc-certificate-code" ' +
                               'data-auto-apply="' + (field.settings.auto_apply ? 'true' : 'false') + '" />' +
                               (field.settings.show_balance_check !== false ? '<button type="button" class="ffgc-check-balance-btn"><?php _e('Check Balance', 'fluentforms-gift-certificates'); ?></button>' : '') +
                               '</div>' +
                               '<div class="ffgc-balance-result" style="display: none;"></div>' +
                               '<div class="ffgc-redemption-result" style="display: none;"></div>' +
                               '</div>';
                    },
                    getValue: function(field) {
                        return field.$el.find('.ffgc-certificate-code').val();
                    },
                    setValue: function(field, value) {
                        field.$el.find('.ffgc-certificate-code').val(value);
                    }
                });
            }
        });
        </script>
        <?php
    }
} 