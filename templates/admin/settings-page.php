<?php
/**
 * Settings Page Template
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Gift Certificate Settings', 'fluentforms-gift-certificates'); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('ffgc_settings');
        do_settings_sections('ffgc_settings');
        submit_button();
        ?>
    </form>
    
    <div class="ffgc-settings-info">
        <h2><?php _e('How to Use', 'fluentforms-gift-certificates'); ?></h2>
        
        <div class="ffgc-info-section">
            <h3><?php _e('1. Enable Forms', 'fluentforms-gift-certificates'); ?></h3>
            <p><?php _e('Select which Fluent Forms should have gift certificate functionality. Forms with amount/price fields will be treated as purchase forms, while others will be application forms.', 'fluentforms-gift-certificates'); ?></p>
        </div>

        <div class="ffgc-info-section">
            <h3><?php _e('2. Enable Fields & Payments', 'fluentforms-gift-certificates'); ?></h3>
            <p><?php _e('Turn on the Gift Certificate fields and connect your payment gateway in Fluent Forms → Global Settings.', 'fluentforms-gift-certificates'); ?></p>
        </div>

        <div class="ffgc-info-section">
            <h3><?php _e('3. Create Designs', 'fluentforms-gift-certificates'); ?></h3>
            <p><?php _e('Create gift certificate designs with different themes, images, and email templates. Each design can have its own minimum and maximum amount range.', 'fluentforms-gift-certificates'); ?></p>
        </div>

        <div class="ffgc-info-section">
            <h3><?php _e('4. Use Shortcodes', 'fluentforms-gift-certificates'); ?></h3>
            <ul>
                <li><code>[gift_certificate_balance]</code> - <?php _e('Check certificate balance', 'fluentforms-gift-certificates'); ?></li>
                <li><code>[gift_certificate_designs]</code> - <?php _e('Show available designs', 'fluentforms-gift-certificates'); ?></li>
            </ul>
        </div>

        <div class="ffgc-info-section">
            <h3><?php _e('5. Monitor Usage', 'fluentforms-gift-certificates'); ?></h3>
            <p><?php _e('Track gift certificate usage, balances, and expiration dates from the main admin dashboard.', 'fluentforms-gift-certificates'); ?></p>
        </div>
    </div>
</div> 