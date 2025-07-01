<?php
/**
 * Forms Configuration Page Template
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get available forms
$forms = array();
if (function_exists('wpFluent')) {
    try {
        $forms = wpFluent()->table('fluentform_forms')->select(['id', 'title'])->get();
    } catch (Exception $e) {
        $forms = array();
    }
}

$purchase_forms = get_option('ffgc_purchase_forms', array());
$redemption_forms = get_option('ffgc_redemption_forms', array());
?>

<div class="wrap">
    <h1><?php _e('Gift Certificate Forms Configuration', 'fluentforms-gift-certificates'); ?></h1>
    
    <div class="ffgc-forms-config">
        <div class="ffgc-config-section">
            <h2><?php _e('Purchase Forms', 'fluentforms-gift-certificates'); ?></h2>
            <p><?php _e('Configure forms that will be used for purchasing gift certificates. These forms should include payment fields and the Gift Certificate Design field type.', 'fluentforms-gift-certificates'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields('ffgc_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Purchase Forms', 'fluentforms-gift-certificates'); ?></th>
                        <td>
                            <select name="ffgc_purchase_forms[]" multiple style="width: 100%; min-height: 150px;">
                                <?php foreach ($forms as $form): ?>
                                    <option value="<?php echo esc_attr($form->id); ?>" 
                                            <?php echo in_array($form->id, $purchase_forms) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($form->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php _e('Hold Ctrl/Cmd to select multiple forms. These forms should include:', 'fluentforms-gift-certificates'); ?>
                            </p>
                            <ul class="description">
                                <li><?php _e('Payment fields (Payment Method, Custom Payment Amount, etc.)', 'fluentforms-gift-certificates'); ?></li>
                                <li><?php _e('Gift Certificate Design field type', 'fluentforms-gift-certificates'); ?></li>
                                <li><?php _e('Recipient information fields (Name, Email, Message)', 'fluentforms-gift-certificates'); ?></li>
                            </ul>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Purchase Forms', 'fluentforms-gift-certificates')); ?>
            </form>
        </div>
        
        <div class="ffgc-config-section">
            <h2><?php _e('Redemption Forms', 'fluentforms-gift-certificates'); ?></h2>
            <p><?php _e('Configure forms that will allow users to redeem gift certificates. These forms should include the Gift Certificate Redemption field type.', 'fluentforms-gift-certificates'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields('ffgc_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Redemption Forms', 'fluentforms-gift-certificates'); ?></th>
                        <td>
                            <select name="ffgc_redemption_forms[]" multiple style="width: 100%; min-height: 150px;">
                                <?php foreach ($forms as $form): ?>
                                    <option value="<?php echo esc_attr($form->id); ?>" 
                                            <?php echo in_array($form->id, $redemption_forms) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($form->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php _e('Hold Ctrl/Cmd to select multiple forms. These forms should include:', 'fluentforms-gift-certificates'); ?>
                            </p>
                            <ul class="description">
                                <li><?php _e('Gift Certificate Redemption field type', 'fluentforms-gift-certificates'); ?></li>
                                <li><?php _e('Product/service selection fields', 'fluentforms-gift-certificates'); ?></li>
                                <li><?php _e('Payment fields (if additional payment is required)', 'fluentforms-gift-certificates'); ?></li>
                            </ul>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Redemption Forms', 'fluentforms-gift-certificates')); ?>
            </form>
        </div>
        
        <div class="ffgc-config-section">
            <h2><?php _e('How to Set Up Forms', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-setup-guide">
                <h3><?php _e('Purchase Form Setup', 'fluentforms-gift-certificates'); ?></h3>
                <ol>
                    <li><?php _e('Create a new Fluent Form or edit an existing one', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Add the following field types:', 'fluentforms-gift-certificates'); ?>
                        <ul>
                            <li><strong><?php _e('Gift Certificate Design', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Allows users to select a design', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Text Input', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For recipient name (name: recipient_name)', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Email Input', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For recipient email (name: recipient_email)', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Textarea', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For personal message (name: personal_message)', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Custom Payment Amount', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For the gift certificate amount', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Payment Method', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For payment processing', 'fluentforms-gift-certificates'); ?></li>
                        </ul>
                    </li>
                    <li><?php _e('Configure the Custom Payment Amount field with appropriate minimum/maximum values', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Set up payment methods and gateways', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Save the form and add it to the Purchase Forms list above', 'fluentforms-gift-certificates'); ?></li>
                </ol>
                
                <h3><?php _e('Redemption Form Setup', 'fluentforms-gift-certificates'); ?></h3>
                <ol>
                    <li><?php _e('Create a new Fluent Form or edit an existing one', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Add the following field types:', 'fluentforms-gift-certificates'); ?>
                        <ul>
                            <li><strong><?php _e('Gift Certificate Redemption', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Allows users to enter and validate certificate codes', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Product/Service Selection', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('For items to purchase with the certificate', 'fluentforms-gift-certificates'); ?></li>
                            <li><strong><?php _e('Payment Fields', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('If additional payment is required beyond certificate balance', 'fluentforms-gift-certificates'); ?></li>
                        </ul>
                    </li>
                    <li><?php _e('Configure the Gift Certificate Redemption field settings', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Set up form calculations to handle certificate discounts', 'fluentforms-gift-certificates'); ?></li>
                    <li><?php _e('Save the form and add it to the Redemption Forms list above', 'fluentforms-gift-certificates'); ?></li>
                </ol>
            </div>
        </div>
        
        <div class="ffgc-config-section">
            <h2><?php _e('Field Type Reference', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-field-reference">
                <h3><?php _e('Gift Certificate Design Field', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('This field displays available gift certificate designs in a grid or dropdown format.', 'fluentforms-gift-certificates'); ?></p>
                
                <h4><?php _e('Settings:', 'fluentforms-gift-certificates'); ?></h4>
                <ul>
                    <li><strong><?php _e('Display Type', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Grid or Dropdown', 'fluentforms-gift-certificates'); ?></li>
                    <li><strong><?php _e('Columns', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Number of columns in grid (1-6)', 'fluentforms-gift-certificates'); ?></li>
                    <li><strong><?php _e('Show Design Info', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Display price range and description', 'fluentforms-gift-certificates'); ?></li>
                </ul>
                
                <h3><?php _e('Gift Certificate Redemption Field', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('This field allows users to enter gift certificate codes and check their balance.', 'fluentforms-gift-certificates'); ?></p>
                
                <h4><?php _e('Settings:', 'fluentforms-gift-certificates'); ?></h4>
                <ul>
                    <li><strong><?php _e('Show Balance Check', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Display "Check Balance" button', 'fluentforms-gift-certificates'); ?></li>
                    <li><strong><?php _e('Auto Apply', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Automatically apply valid certificates', 'fluentforms-gift-certificates'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.ffgc-forms-config {
    max-width: 1200px;
}

.ffgc-config-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.ffgc-config-section h2 {
    margin-top: 0;
    color: #23282d;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.ffgc-setup-guide ol {
    margin-left: 20px;
}

.ffgc-setup-guide ul {
    margin-left: 20px;
    margin-top: 5px;
}

.ffgc-field-reference h3 {
    color: #0073aa;
    margin-top: 20px;
}

.ffgc-field-reference h4 {
    color: #666;
    margin-top: 15px;
}

.ffgc-field-reference ul {
    margin-left: 20px;
}
</style> 