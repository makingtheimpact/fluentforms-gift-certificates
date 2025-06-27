<?php
/**
 * Settings Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('ffgc_settings', 'ffgc_min_amount', array(
            'type' => 'number',
            'sanitize_callback' => 'floatval',
            'default' => 10.00
        ));
        
        register_setting('ffgc_settings', 'ffgc_max_amount', array(
            'type' => 'number',
            'sanitize_callback' => 'floatval',
            'default' => 1000.00
        ));
        
        register_setting('ffgc_settings', 'ffgc_currency', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'USD'
        ));
        
        register_setting('ffgc_settings', 'ffgc_expiry_days', array(
            'type' => 'number',
            'sanitize_callback' => 'intval',
            'default' => 365
        ));
        
        register_setting('ffgc_settings', 'ffgc_email_from_name', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => get_bloginfo('name')
        ));
        
        register_setting('ffgc_settings', 'ffgc_email_from_address', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => get_option('admin_email')
        ));
        
        register_setting('ffgc_settings', 'ffgc_email_subject', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Your Gift Certificate is Ready!', 'fluentforms-gift-certificates')
        ));
        
        register_setting('ffgc_settings', 'ffgc_forms_enabled', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_forms_array'),
            'default' => array()
        ));
        
        register_setting('ffgc_settings', 'ffgc_gift_certificate_field_label', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Gift Certificate Code', 'fluentforms-gift-certificates')
        ));
        
        register_setting('ffgc_settings', 'ffgc_gift_certificate_field_placeholder', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Enter your gift certificate code', 'fluentforms-gift-certificates')
        ));
        
        // Add settings sections
        add_settings_section(
            'ffgc_general_settings',
            __('General Settings', 'fluentforms-gift-certificates'),
            array($this, 'general_settings_section_callback'),
            'ffgc_settings'
        );
        
        add_settings_section(
            'ffgc_email_settings',
            __('Email Settings', 'fluentforms-gift-certificates'),
            array($this, 'email_settings_section_callback'),
            'ffgc_settings'
        );
        
        add_settings_section(
            'ffgc_form_settings',
            __('Form Integration Settings', 'fluentforms-gift-certificates'),
            array($this, 'form_settings_section_callback'),
            'ffgc_settings'
        );
        
        // Add settings fields
        add_settings_field(
            'ffgc_min_amount',
            __('Minimum Amount', 'fluentforms-gift-certificates'),
            array($this, 'min_amount_field_callback'),
            'ffgc_settings',
            'ffgc_general_settings'
        );
        
        add_settings_field(
            'ffgc_max_amount',
            __('Maximum Amount', 'fluentforms-gift-certificates'),
            array($this, 'max_amount_field_callback'),
            'ffgc_settings',
            'ffgc_general_settings'
        );
        
        add_settings_field(
            'ffgc_currency',
            __('Currency', 'fluentforms-gift-certificates'),
            array($this, 'currency_field_callback'),
            'ffgc_settings',
            'ffgc_general_settings'
        );
        
        add_settings_field(
            'ffgc_expiry_days',
            __('Expiry Days', 'fluentforms-gift-certificates'),
            array($this, 'expiry_days_field_callback'),
            'ffgc_settings',
            'ffgc_general_settings'
        );
        
        add_settings_field(
            'ffgc_email_from_name',
            __('From Name', 'fluentforms-gift-certificates'),
            array($this, 'email_from_name_field_callback'),
            'ffgc_settings',
            'ffgc_email_settings'
        );
        
        add_settings_field(
            'ffgc_email_from_address',
            __('From Email', 'fluentforms-gift-certificates'),
            array($this, 'email_from_address_field_callback'),
            'ffgc_settings',
            'ffgc_email_settings'
        );
        
        add_settings_field(
            'ffgc_email_subject',
            __('Email Subject', 'fluentforms-gift-certificates'),
            array($this, 'email_subject_field_callback'),
            'ffgc_settings',
            'ffgc_email_settings'
        );
        
        add_settings_field(
            'ffgc_forms_enabled',
            __('Enable Forms', 'fluentforms-gift-certificates'),
            array($this, 'forms_enabled_field_callback'),
            'ffgc_settings',
            'ffgc_form_settings'
        );
        
        add_settings_field(
            'ffgc_gift_certificate_field_label',
            __('Field Label', 'fluentforms-gift-certificates'),
            array($this, 'field_label_callback'),
            'ffgc_settings',
            'ffgc_form_settings'
        );
        
        add_settings_field(
            'ffgc_gift_certificate_field_placeholder',
            __('Field Placeholder', 'fluentforms-gift-certificates'),
            array($this, 'field_placeholder_callback'),
            'ffgc_settings',
            'ffgc_form_settings'
        );
    }
    
    public function sanitize_forms_array($input) {
        if (!is_array($input)) {
            return array();
        }
        
        return array_map('intval', $input);
    }
    
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure general gift certificate settings.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function email_settings_section_callback() {
        echo '<p>' . __('Configure email settings for gift certificate delivery.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function form_settings_section_callback() {
        echo '<p>' . __('Configure which forms should have gift certificate functionality.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function min_amount_field_callback() {
        $value = get_option('ffgc_min_amount', 10.00);
        echo '<input type="number" step="0.01" min="0" name="ffgc_min_amount" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Minimum amount allowed for gift certificates.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function max_amount_field_callback() {
        $value = get_option('ffgc_max_amount', 1000.00);
        echo '<input type="number" step="0.01" min="0" name="ffgc_max_amount" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Maximum amount allowed for gift certificates.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function currency_field_callback() {
        $value = get_option('ffgc_currency', 'USD');
        $currencies = array(
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)'
        );
        
        echo '<select name="ffgc_currency">';
        foreach ($currencies as $code => $name) {
            $selected = ($value === $code) ? 'selected' : '';
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Currency for gift certificate amounts.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function expiry_days_field_callback() {
        $value = get_option('ffgc_expiry_days', 365);
        echo '<input type="number" min="1" name="ffgc_expiry_days" value="' . esc_attr($value) . '" class="small-text" />';
        echo '<p class="description">' . __('Number of days until gift certificates expire.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function email_from_name_field_callback() {
        $value = get_option('ffgc_email_from_name', get_bloginfo('name'));
        echo '<input type="text" name="ffgc_email_from_name" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Name that appears in the "From" field of gift certificate emails.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function email_from_address_field_callback() {
        $value = get_option('ffgc_email_from_address', get_option('admin_email'));
        echo '<input type="email" name="ffgc_email_from_address" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Email address that appears in the "From" field of gift certificate emails.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function email_subject_field_callback() {
        $value = get_option('ffgc_email_subject', __('Your Gift Certificate is Ready!', 'fluentforms-gift-certificates'));
        echo '<input type="text" name="ffgc_email_subject" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Subject line for gift certificate emails.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function forms_enabled_field_callback() {
        $enabled_forms = get_option('ffgc_forms_enabled', array());
        
        // Check if wpFluent function is available
        if (!function_exists('wpFluent')) {
            echo '<p>' . __('Fluent Forms is not properly loaded. Please refresh the page.', 'fluentforms-gift-certificates') . '</p>';
            return;
        }
        
        // Get all Fluent Forms
        try {
            $forms = wpFluent()->table('fluentform_forms')->select(['id', 'title'])->get();
            
            if (empty($forms)) {
                echo '<p>' . __('No Fluent Forms found.', 'fluentforms-gift-certificates') . '</p>';
                return;
            }
            
            echo '<fieldset>';
            foreach ($forms as $form) {
                $checked = in_array($form->id, $enabled_forms) ? 'checked' : '';
                echo '<label><input type="checkbox" name="ffgc_forms_enabled[]" value="' . esc_attr($form->id) . '" ' . $checked . ' /> ';
                echo esc_html($form->title) . '</label><br>';
            }
            echo '</fieldset>';
            echo '<p class="description">' . __('Select which forms should have gift certificate functionality.', 'fluentforms-gift-certificates') . '</p>';
        } catch (Exception $e) {
            echo '<p>' . __('Error loading Fluent Forms. Please refresh the page.', 'fluentforms-gift-certificates') . '</p>';
        }
    }
    
    public function field_label_callback() {
        $value = get_option('ffgc_gift_certificate_field_label', __('Gift Certificate Code', 'fluentforms-gift-certificates'));
        echo '<input type="text" name="ffgc_gift_certificate_field_label" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Label for the gift certificate field in forms.', 'fluentforms-gift-certificates') . '</p>';
    }
    
    public function field_placeholder_callback() {
        $value = get_option('ffgc_gift_certificate_field_placeholder', __('Enter your gift certificate code', 'fluentforms-gift-certificates'));
        echo '<input type="text" name="ffgc_gift_certificate_field_placeholder" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Placeholder text for the gift certificate field in forms.', 'fluentforms-gift-certificates') . '</p>';
    }
} 