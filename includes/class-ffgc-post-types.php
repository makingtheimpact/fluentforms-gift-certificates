<?php
/**
 * Post Types Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Post_Types {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('manage_ffgc_cert_posts_columns', array($this, 'gift_certificate_columns'));
        add_action('manage_ffgc_cert_posts_custom_column', array($this, 'gift_certificate_column_content'), 10, 2);
        add_filter('manage_ffgc_design_posts_columns', array($this, 'design_columns'));
        add_action('manage_ffgc_design_posts_custom_column', array($this, 'design_column_content'), 10, 2);
    }
    
    public function register_post_types() {
        // Gift Certificate post type
        register_post_type('ffgc_cert', array(
            'labels' => array(
                'name' => __('Gift Certificates', 'fluentforms-gift-certificates'),
                'singular_name' => __('Gift Certificate', 'fluentforms-gift-certificates'),
                'add_new' => __('Add New Certificate', 'fluentforms-gift-certificates'),
                'add_new_item' => __('Add New Gift Certificate', 'fluentforms-gift-certificates'),
                'edit_item' => __('Edit Gift Certificate', 'fluentforms-gift-certificates'),
                'new_item' => __('New Gift Certificate', 'fluentforms-gift-certificates'),
                'view_item' => __('View Gift Certificate', 'fluentforms-gift-certificates'),
                'search_items' => __('Search Gift Certificates', 'fluentforms-gift-certificates'),
                'not_found' => __('No gift certificates found', 'fluentforms-gift-certificates'),
                'not_found_in_trash' => __('No gift certificates found in trash', 'fluentforms-gift-certificates'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-tickets-alt',
        ));
        
        // Gift Certificate Design post type
        register_post_type('ffgc_design', array(
            'labels' => array(
                'name' => __('Certificate Designs', 'fluentforms-gift-certificates'),
                'singular_name' => __('Certificate Design', 'fluentforms-gift-certificates'),
                'add_new' => __('Add New Design', 'fluentforms-gift-certificates'),
                'add_new_item' => __('Add New Certificate Design', 'fluentforms-gift-certificates'),
                'edit_item' => __('Edit Certificate Design', 'fluentforms-gift-certificates'),
                'new_item' => __('New Certificate Design', 'fluentforms-gift-certificates'),
                'view_item' => __('View Certificate Design', 'fluentforms-gift-certificates'),
                'search_items' => __('Search Certificate Designs', 'fluentforms-gift-certificates'),
                'not_found' => __('No certificate designs found', 'fluentforms-gift-certificates'),
                'not_found_in_trash' => __('No certificate designs found in trash', 'fluentforms-gift-certificates'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => false,
            'supports' => array('title', 'thumbnail'),
            'menu_icon' => 'dashicons-art',
        ));
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'gift_certificate_details',
            __('Gift Certificate Details', 'fluentforms-gift-certificates'),
            array($this, 'gift_certificate_meta_box'),
            'ffgc_cert',
            'normal',
            'high'
        );
        
        add_meta_box(
            'gift_certificate_design_details',
            __('Design Details', 'fluentforms-gift-certificates'),
            array($this, 'design_meta_box'),
            'ffgc_design',
            'normal',
            'high'
        );
    }
    
    public function gift_certificate_meta_box($post) {
        wp_nonce_field('ffgc_save_meta', 'ffgc_meta_nonce');
        
        $code = get_post_meta($post->ID, '_certificate_code', true);
        $amount = get_post_meta($post->ID, '_certificate_amount', true);
        $status = get_post_meta($post->ID, '_certificate_status', true);
        $used_amount = get_post_meta($post->ID, '_certificate_used_amount', true);
        $recipient_email = get_post_meta($post->ID, '_recipient_email', true);
        $recipient_name = get_post_meta($post->ID, '_recipient_name', true);
        $personal_message = get_post_meta($post->ID, '_personal_message', true);
        $design_id = get_post_meta($post->ID, '_design_id', true);
        $submission_id = get_post_meta($post->ID, '_submission_id', true);
        $expiry_date = get_post_meta($post->ID, '_expiry_date', true);
        
        if (empty($code)) {
            $code = $this->generate_certificate_code();
        }
        
        if (empty($status)) {
            $status = 'unused';
        }
        
        include FFGC_PLUGIN_DIR . 'templates/admin/meta-boxes/gift-certificate.php';
    }
    
    public function design_meta_box($post) {
        wp_nonce_field('ffgc_save_meta', 'ffgc_meta_nonce');
        
        $min_amount = get_post_meta($post->ID, '_min_amount', true);
        $max_amount = get_post_meta($post->ID, '_max_amount', true);
        $email_template = get_post_meta($post->ID, '_email_template', true);
        $is_active = get_post_meta($post->ID, '_is_active', true);
        
        if (empty($is_active)) {
            $is_active = 'yes';
        }
        
        include FFGC_PLUGIN_DIR . 'templates/admin/meta-boxes/design.php';
    }
    
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['ffgc_meta_nonce']) || !wp_verify_nonce($_POST['ffgc_meta_nonce'], 'ffgc_save_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $post_type = get_post_type($post_id);
        
        if ($post_type === 'ffgc_cert') {
            $this->save_gift_certificate_meta($post_id);
        } elseif ($post_type === 'ffgc_design') {
            $this->save_design_meta($post_id);
        }
    }
    
    private function save_gift_certificate_meta($post_id) {
        $fields = array(
            '_certificate_code' => 'text',
            '_certificate_amount' => 'float',
            '_certificate_status' => 'text',
            '_certificate_used_amount' => 'float',
            '_recipient_email' => 'email',
            '_recipient_name' => 'text',
            '_personal_message' => 'textarea',
            '_design_id' => 'int',
            '_submission_id' => 'int',
            '_expiry_date' => 'text'
        );
        
        foreach ($fields as $key => $type) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                
                switch ($type) {
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'email':
                        $value = sanitize_email($value);
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    default:
                        $value = sanitize_text_field($value);
                }
                
                update_post_meta($post_id, $key, $value);
            }
        }
    }
    
    private function save_design_meta($post_id) {
        $fields = array(
            '_min_amount' => 'float',
            '_max_amount' => 'float',
            '_email_template' => 'textarea',
            '_is_active' => 'text'
        );
        
        foreach ($fields as $key => $type) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                
                switch ($type) {
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'textarea':
                        $value = wp_kses_post($value);
                        break;
                    default:
                        $value = sanitize_text_field($value);
                }
                
                update_post_meta($post_id, $key, $value);
            }
        }
    }
    
    public function gift_certificate_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['code'] = __('Code', 'fluentforms-gift-certificates');
        $new_columns['amount'] = __('Amount', 'fluentforms-gift-certificates');
        $new_columns['status'] = __('Status', 'fluentforms-gift-certificates');
        $new_columns['recipient'] = __('Recipient', 'fluentforms-gift-certificates');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    public function gift_certificate_column_content($column, $post_id) {
        switch ($column) {
            case 'code':
                $code = get_post_meta($post_id, '_certificate_code', true);
                echo '<code>' . esc_html($code) . '</code>';
                break;
            case 'amount':
                $amount = get_post_meta($post_id, '_certificate_amount', true);
                $used = get_post_meta($post_id, '_certificate_used_amount', true);
                echo esc_html(number_format($amount, 2)) . ' (' . esc_html(number_format($used, 2)) . ' used)';
                break;
            case 'status':
                $status = get_post_meta($post_id, '_certificate_status', true);
                $status_class = $status === 'used' ? 'used' : 'unused';
                echo '<span class="ffgc-status ' . esc_attr($status_class) . '">' . esc_html(ucfirst($status)) . '</span>';
                break;
            case 'recipient':
                $name = get_post_meta($post_id, '_recipient_name', true);
                $email = get_post_meta($post_id, '_recipient_email', true);
                echo esc_html($name) . '<br><small>' . esc_html($email) . '</small>';
                break;
        }
    }
    
    public function design_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['thumbnail'] = __('Preview', 'fluentforms-gift-certificates');
        $new_columns['amount_range'] = __('Amount Range', 'fluentforms-gift-certificates');
        $new_columns['status'] = __('Status', 'fluentforms-gift-certificates');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    public function design_column_content($column, $post_id) {
        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, 'thumbnail');
                } else {
                    echo '<span class="no-image">' . __('No Image', 'fluentforms-gift-certificates') . '</span>';
                }
                break;
            case 'amount_range':
                $min = get_post_meta($post_id, '_min_amount', true);
                $max = get_post_meta($post_id, '_max_amount', true);
                echo esc_html(number_format($min, 2)) . ' - ' . esc_html(number_format($max, 2));
                break;
            case 'status':
                $status = get_post_meta($post_id, '_is_active', true);
                $status_class = $status === 'yes' ? 'active' : 'inactive';
                $status_text = $status === 'yes' ? __('Active', 'fluentforms-gift-certificates') : __('Inactive', 'fluentforms-gift-certificates');
                echo '<span class="ffgc-status ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>';
                break;
        }
    }
    
    private function generate_certificate_code() {
        $prefix = 'GC';
        $suffix = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . '-' . $suffix;
    }
} 