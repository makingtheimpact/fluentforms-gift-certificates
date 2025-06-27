<?php
/**
 * Gift Certificate Meta Box Template
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="form-table">
    <tr>
        <th scope="row">
            <label for="_certificate_code"><?php _e('Certificate Code', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="text" id="_certificate_code" name="_certificate_code" value="<?php echo esc_attr($code); ?>" class="regular-text" readonly />
            <p class="description"><?php _e('Unique code for this gift certificate. Auto-generated if left empty.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_certificate_amount"><?php _e('Amount', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="number" id="_certificate_amount" name="_certificate_amount" value="<?php echo esc_attr($amount); ?>" step="0.01" min="0" class="regular-text" />
            <p class="description"><?php _e('Total value of the gift certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_certificate_status"><?php _e('Status', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <select id="_certificate_status" name="_certificate_status">
                <option value="unused" <?php selected($status, 'unused'); ?>><?php _e('Unused', 'fluentforms-gift-certificates'); ?></option>
                <option value="used" <?php selected($status, 'used'); ?>><?php _e('Used', 'fluentforms-gift-certificates'); ?></option>
                <option value="expired" <?php selected($status, 'expired'); ?>><?php _e('Expired', 'fluentforms-gift-certificates'); ?></option>
            </select>
            <p class="description"><?php _e('Current status of the gift certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_certificate_used_amount"><?php _e('Used Amount', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="number" id="_certificate_used_amount" name="_certificate_used_amount" value="<?php echo esc_attr($used_amount); ?>" step="0.01" min="0" class="regular-text" />
            <p class="description"><?php _e('Amount that has been used from this certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_recipient_name"><?php _e('Recipient Name', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="text" id="_recipient_name" name="_recipient_name" value="<?php echo esc_attr($recipient_name); ?>" class="regular-text" />
            <p class="description"><?php _e('Name of the person receiving the gift certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_recipient_email"><?php _e('Recipient Email', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="email" id="_recipient_email" name="_recipient_email" value="<?php echo esc_attr($recipient_email); ?>" class="regular-text" />
            <p class="description"><?php _e('Email address where the gift certificate will be sent.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_personal_message"><?php _e('Personal Message', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <textarea id="_personal_message" name="_personal_message" rows="3" class="large-text"><?php echo esc_textarea($personal_message); ?></textarea>
            <p class="description"><?php _e('Personal message to include with the gift certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_design_id"><?php _e('Design', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <select id="_design_id" name="_design_id">
                <option value=""><?php _e('Select a design', 'fluentforms-gift-certificates'); ?></option>
                <?php
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
                    $selected = ($design_id == $design->ID) ? 'selected' : '';
                    echo '<option value="' . esc_attr($design->ID) . '" ' . $selected . '>' . esc_html($design->post_title) . '</option>';
                }
                ?>
            </select>
            <p class="description"><?php _e('Design template for this gift certificate.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_submission_id"><?php _e('Submission ID', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="number" id="_submission_id" name="_submission_id" value="<?php echo esc_attr($submission_id); ?>" class="regular-text" />
            <p class="description"><?php _e('Related Fluent Forms submission ID (if applicable).', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_expiry_date"><?php _e('Expiry Date', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="date" id="_expiry_date" name="_expiry_date" value="<?php echo esc_attr($expiry_date); ?>" class="regular-text" />
            <p class="description"><?php _e('Date when this gift certificate expires.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
</table>

<?php if ($post->ID): ?>
<div class="ffgc-certificate-actions">
    <h3><?php _e('Actions', 'fluentforms-gift-certificates'); ?></h3>
    
    <p>
        <button type="button" id="ffgc-resend-email" class="button button-secondary">
            <?php _e('Resend Email', 'fluentforms-gift-certificates'); ?>
        </button>
        <span class="spinner" style="float: none; margin-left: 10px;"></span>
    </p>
    
    <p class="description"><?php _e('Resend the gift certificate email to the recipient.', 'fluentforms-gift-certificates'); ?></p>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ffgc-resend-email').on('click', function() {
        var button = $(this);
        var spinner = button.next('.spinner');
        
        button.prop('disabled', true);
        spinner.css('visibility', 'visible');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ffgc_resend_email',
                certificate_id: <?php echo $post->ID; ?>,
                nonce: '<?php echo wp_create_nonce('ffgc_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Email sent successfully!', 'fluentforms-gift-certificates'); ?>');
                } else {
                    alert('<?php _e('Failed to send email.', 'fluentforms-gift-certificates'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('An error occurred.', 'fluentforms-gift-certificates'); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
                spinner.css('visibility', 'hidden');
            }
        });
    });
});
</script>
<?php endif; ?> 