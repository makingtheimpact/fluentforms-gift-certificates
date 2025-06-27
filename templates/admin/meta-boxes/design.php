<?php
/**
 * Design Meta Box Template
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
            <label for="_min_amount"><?php _e('Minimum Amount', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="number" id="_min_amount" name="_min_amount" value="<?php echo esc_attr($min_amount); ?>" step="0.01" min="0" class="regular-text" />
            <p class="description"><?php _e('Minimum amount allowed for this design.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_max_amount"><?php _e('Maximum Amount', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <input type="number" id="_max_amount" name="_max_amount" value="<?php echo esc_attr($max_amount); ?>" step="0.01" min="0" class="regular-text" />
            <p class="description"><?php _e('Maximum amount allowed for this design.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <label for="_is_active"><?php _e('Active Status', 'fluentforms-gift-certificates'); ?></label>
        </th>
        <td>
            <select id="_is_active" name="_is_active">
                <option value="yes" <?php selected($is_active, 'yes'); ?>><?php _e('Active', 'fluentforms-gift-certificates'); ?></option>
                <option value="no" <?php selected($is_active, 'no'); ?>><?php _e('Inactive', 'fluentforms-gift-certificates'); ?></option>
            </select>
            <p class="description"><?php _e('Only active designs will be available for selection.', 'fluentforms-gift-certificates'); ?></p>
        </td>
    </tr>
</table>

<div class="ffgc-email-template-section">
    <h3><?php _e('Email Template', 'fluentforms-gift-certificates'); ?></h3>
    <p><?php _e('HTML email template for this design. Use the following placeholders:', 'fluentforms-gift-certificates'); ?></p>
    
    <div class="ffgc-placeholders">
        <code>{recipient_name}</code> - <?php _e('Recipient name', 'fluentforms-gift-certificates'); ?><br>
        <code>{certificate_code}</code> - <?php _e('Gift certificate code', 'fluentforms-gift-certificates'); ?><br>
        <code>{amount}</code> - <?php _e('Certificate amount', 'fluentforms-gift-certificates'); ?><br>
        <code>{personal_message}</code> - <?php _e('Personal message (if provided)', 'fluentforms-gift-certificates'); ?><br>
        <code>{expiry_date}</code> - <?php _e('Expiry date', 'fluentforms-gift-certificates'); ?><br>
        <code>{site_name}</code> - <?php _e('Website name', 'fluentforms-gift-certificates'); ?><br>
        <code>{site_url}</code> - <?php _e('Website URL', 'fluentforms-gift-certificates'); ?>
    </div>
    
    <?php
    wp_editor(
        $email_template,
        '_email_template',
        array(
            'textarea_name' => '_email_template',
            'textarea_rows' => 20,
            'media_buttons' => false,
            'tinymce' => array(
                'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help'
            ),
            'quicktags' => true
        )
    );
    ?>
    
    <p class="description">
        <?php _e('This template will be used for gift certificates with this design. Leave empty to use the default template.', 'fluentforms-gift-certificates'); ?>
    </p>
</div>

<style>
.ffgc-email-template-section {
    margin-top: 20px;
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.ffgc-placeholders {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 10px 0;
    font-family: monospace;
    font-size: 13px;
    line-height: 1.6;
}

.ffgc-placeholders code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    color: #d73a49;
}
</style> 