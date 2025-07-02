<?php
if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Webhooks {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('ffgc/v1', '/purchase', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_purchase'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_purchase(WP_REST_Request $request) {
        $design_id        = intval($request->get_param('design_id'));
        $recipient_name   = sanitize_text_field($request->get_param('recipient_name'));
        $recipient_email  = sanitize_email($request->get_param('recipient_email'));
        $amount           = floatval($request->get_param('amount'));
        $personal_message = sanitize_textarea_field($request->get_param('personal_message'));

        if ($amount <= 0) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Invalid amount', 'fluentforms-gift-certificates'),
            ), 400);
        }

        $active_designs = $this->get_active_design_ids();
        if ($design_id && !in_array($design_id, $active_designs, true)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Invalid design selected', 'fluentforms-gift-certificates'),
            ), 400);
        }

        if ($design_id) {
            $min_amount = get_post_meta($design_id, '_min_amount', true);
            $max_amount = get_post_meta($design_id, '_max_amount', true);
            if ($min_amount !== '' && $amount < floatval($min_amount)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => __('Amount below allowed minimum', 'fluentforms-gift-certificates'),
                ), 400);
            }
            if ($max_amount !== '' && $amount > floatval($max_amount)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => __('Amount exceeds allowed maximum', 'fluentforms-gift-certificates'),
                ), 400);
            }
        }

        $certificate_id = $this->create_gift_certificate(array(
            'amount'           => $amount,
            'recipient_name'   => $recipient_name,
            'recipient_email'  => $recipient_email,
            'personal_message' => $personal_message,
            'design_id'        => $design_id,
        ));

        if (!$certificate_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Failed to create certificate', 'fluentforms-gift-certificates'),
            ), 500);
        }

        $email = new FFGC_Email();
        $email->send_gift_certificate_email($certificate_id);

        return new WP_REST_Response(array(
            'success'        => true,
            'certificate_id' => $certificate_id,
        ));
    }

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
                        'compare' => '=',
                    ),
                ),
                'fields'   => 'ids',
                'nopaging' => true,
            ));
            $ids = $query->posts;
            set_transient('ffgc_active_design_ids', $ids, HOUR_IN_SECONDS);
        }
        return $ids;
    }

    private function create_gift_certificate($data) {
        $code = $this->generate_unique_code();

        $post_data = array(
            'post_title'   => sprintf(__('Gift Certificate - %s', 'fluentforms-gift-certificates'), $code),
            'post_content' => $data['personal_message'],
            'post_status'  => 'publish',
            'post_type'    => 'ffgc_cert',
        );

        $certificate_id = wp_insert_post($post_data);

        if ($certificate_id) {
            update_post_meta($certificate_id, '_certificate_code', $code);
            update_post_meta($certificate_id, '_certificate_amount', $data['amount']);
            update_post_meta($certificate_id, '_certificate_balance', $data['amount']);
            update_post_meta($certificate_id, '_recipient_name', $data['recipient_name']);
            update_post_meta($certificate_id, '_recipient_email', $data['recipient_email']);
            update_post_meta($certificate_id, '_design_id', $data['design_id']);
            update_post_meta($certificate_id, '_created_date', current_time('mysql'));
            update_post_meta($certificate_id, '_expiry_date', date('Y-m-d H:i:s', strtotime('+' . get_option('ffgc_expiry_days', 365) . ' days')));
            update_post_meta($certificate_id, '_status', 'active');
            return $certificate_id;
        }
        return false;
    }

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

        return $code;
    }
}
