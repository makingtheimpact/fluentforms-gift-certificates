<?php
/**
 * Email Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Email {
    
    public function __construct() {
        // Intentionally left blank. Content type filter will be added per email.
    }
    
    /**
     * Send the gift certificate email to the recipient
     *
     * @param int $certificate_id The ID of the certificate post
     *
     * @return bool Whether the email was successfully sent
     */
    public function send_gift_certificate_email($certificate_id) {
        $certificate = get_post($certificate_id);
        
        if (!$certificate || $certificate->post_type !== 'ffgc_cert') {
            return false;
        }
        
        $recipient_email = get_post_meta($certificate_id, '_recipient_email', true);
        $recipient_name = get_post_meta($certificate_id, '_recipient_name', true);
        $certificate_code = get_post_meta($certificate_id, '_certificate_code', true);
        $amount = get_post_meta($certificate_id, '_certificate_amount', true);
        $personal_message = get_post_meta($certificate_id, '_personal_message', true);
        $design_id = get_post_meta($certificate_id, '_design_id', true);
        $expiry_date = get_post_meta($certificate_id, '_expiry_date', true);
        
        if (empty($recipient_email)) {
            return false;
        }
        
        // Get email template
        $email_template = $this->get_email_template($design_id);
        
        // Replace placeholders
        $email_content = $this->replace_placeholders($email_template, array(
            'recipient_name' => $recipient_name,
            'certificate_code' => $certificate_code,
            'amount' => number_format($amount, 2),
            'personal_message' => $this->format_personal_message($personal_message),
            'expiry_date' => date('F j, Y', strtotime($expiry_date)),
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'balance_url' => $this->get_balance_url()
        ));
        
        // Email headers
        $from_name = get_option('ffgc_email_from_name', get_bloginfo('name'));
        $from_email = get_option('ffgc_email_from_address', get_option('admin_email'));
        $subject = get_option('ffgc_email_subject', __('Your Gift Certificate is Ready!', 'fluentforms-gift-certificates'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        // Ensure HTML content type only for this email
        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        // Send email
        $sent = wp_mail($recipient_email, $subject, $email_content, $headers);

        // Reset content type immediately after sending
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        
        return $sent;
    }
    
    private function get_email_template($design_id) {
        if ($design_id) {
            $template = get_post_meta($design_id, '_email_template', true);
            if (!empty($template)) {
                return $template;
            }
        }
        
        // Fallback to default template
        return get_option('ffgc_default_email_template', $this->get_default_template());
    }
    
    private function replace_placeholders($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
    
    private function format_personal_message($message) {
        if (empty($message)) {
            return '';
        }
        
        return '<div class="message">' . nl2br(esc_html($message)) . '</div>';
    }
    
    public function set_html_content_type() {
        return 'text/html';
    }

    private function get_balance_url() {
        $page_id = get_option('ffgc_balance_page', 0);
        if ($page_id) {
            $url = get_permalink($page_id);
            if ($url) {
                return $url;
            }
        }
        return get_site_url();
    }
    
    private function get_default_template() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Certificate</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f4f4f4; 
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: #fff; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            padding: 30px 20px; 
            text-align: center; 
            color: white; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            font-weight: 300; 
        }
        .content { 
            padding: 30px 20px; 
        }
        .code { 
            background: #f8f9fa; 
            padding: 20px; 
            text-align: center; 
            font-size: 24px; 
            font-weight: bold; 
            border-radius: 8px; 
            margin: 25px 0; 
            border: 2px dashed #dee2e6; 
            color: #495057; 
        }
        .amount { 
            font-size: 20px; 
            color: #28a745; 
            font-weight: bold; 
            text-align: center; 
            margin: 20px 0; 
        }
        .message { 
            background: #e3f2fd; 
            padding: 20px; 
            border-left: 4px solid #2196f3; 
            margin: 25px 0; 
            border-radius: 0 8px 8px 0; 
        }
        .instructions { 
            background: #fff3cd; 
            padding: 20px; 
            border-radius: 8px; 
            margin: 25px 0; 
            border-left: 4px solid #ffc107; 
        }
        .instructions ol { 
            margin: 10px 0; 
            padding-left: 20px; 
        }
        .instructions li { 
            margin: 8px 0; 
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            color: #6c757d; 
            font-size: 14px; 
            padding: 20px; 
            background: #f8f9fa; 
        }
        .expiry-notice { 
            background: #fff3cd; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 20px 0; 
            text-align: center; 
            color: #856404; 
        }
        @media only screen and (max-width: 600px) {
            .container { 
                margin: 10px; 
                width: auto; 
            }
            .header h1 { 
                font-size: 24px; 
            }
            .code { 
                font-size: 20px; 
                padding: 15px; 
            }
        }
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
            
            <div class="instructions">
                <strong>How to use your gift certificate:</strong>
                <ol>
                    <li>Visit our website at <a href="{site_url}">{site_name}</a></li>
                    <li>Add items to your cart</li>
                <li>Enter the code above during checkout</li>
                <li>Enjoy your purchase!</li>
                </ol>
            </div>

            <p>Check your remaining balance at <a href="{balance_url}">{balance_url}</a>.</p>

            {personal_message}
            
            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This gift certificate is valid until {expiry_date}.
            </div>
            
            <p>Thank you for choosing {site_name}!</p>
        </div>
        
        <div class="footer">
            <p>If you have any questions, please contact us at {site_url}</p>
            <p>&copy; {site_name} - All rights reserved</p>
        </div>
    </div>
</body>
</html>';
    }
    
    public function send_admin_notification($certificate_id, $action = 'created') {
        $admin_email = get_option('admin_email');
        $certificate = get_post($certificate_id);
        
        if (!$certificate) {
            return false;
        }
        
        $certificate_code = get_post_meta($certificate_id, '_certificate_code', true);
        $amount = get_post_meta($certificate_id, '_certificate_amount', true);
        $recipient_name = get_post_meta($certificate_id, '_recipient_name', true);
        $recipient_email = get_post_meta($certificate_id, '_recipient_email', true);
        
        $subject = sprintf(
            __('Gift Certificate %s - %s', 'fluentforms-gift-certificates'),
            ucfirst($action),
            $certificate_code
        );
        
        $message = sprintf(
            __('A gift certificate has been %s:

Code: %s
Amount: $%s
Recipient: %s (%s)
Date: %s

View in admin: %s', 'fluentforms-gift-certificates'),
            $action,
            $certificate_code,
            number_format($amount, 2),
            $recipient_name,
            $recipient_email,
            current_time('Y-m-d H:i:s'),
            admin_url('edit.php?post_type=ffgc_cert')
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    public function send_usage_notification($certificate_id, $amount_used, $form_id) {
        $admin_email = get_option('admin_email');
        $certificate = get_post($certificate_id);
        
        if (!$certificate) {
            return false;
        }
        
        $certificate_code = get_post_meta($certificate_id, '_certificate_code', true);
        $total_amount = get_post_meta($certificate_id, '_certificate_amount', true);
        $remaining = $total_amount - $amount_used;
        
        $subject = sprintf(
            __('Gift Certificate Used - %s', 'fluentforms-gift-certificates'),
            $certificate_code
        );
        
        $message = sprintf(
            __('A gift certificate has been used:

Code: %s
Amount Used: $%s
Remaining Balance: $%s
Form ID: %s
Date: %s

View in admin: %s', 'fluentforms-gift-certificates'),
            $certificate_code,
            number_format($amount_used, 2),
            number_format($remaining, 2),
            $form_id,
            current_time('Y-m-d H:i:s'),
            admin_url('edit.php?post_type=ffgc_cert')
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
} 