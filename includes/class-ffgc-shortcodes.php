<?php
/**
 * Shortcodes Class
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}

class FFGC_Shortcodes {
    
    public function __construct() {
        add_shortcode('gift_certificate_balance', array($this, 'balance_checker_shortcode'));
        add_shortcode('gift_certificate_designs', array($this, 'designs_showcase_shortcode'));
    }
    
    public function balance_checker_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Check Gift Certificate Balance', 'fluentforms-gift-certificates'),
            'button_text' => __('Check Balance', 'fluentforms-gift-certificates'),
            'placeholder' => __('Enter your gift certificate code', 'fluentforms-gift-certificates'),
            'show_history' => 'yes'
        ), $atts);
        
        ob_start();
        ?>
        <div class="ffgc-balance-checker">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            
            <form id="ffgc-balance-form" class="ffgc-form">
                <div class="ffgc-field-group">
                    <label for="ffgc-balance-code"><?php _e('Gift Certificate Code', 'fluentforms-gift-certificates'); ?></label>
                    <input type="text" id="ffgc-balance-code" name="code" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" required />
                </div>
                
                <button type="submit" class="ffgc-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
            
            <div id="ffgc-balance-result" class="ffgc-result" style="display: none;"></div>
            
            <?php if ($atts['show_history'] === 'yes'): ?>
                <div id="ffgc-usage-history" class="ffgc-history" style="display: none;">
                    <h4><?php _e('Usage History', 'fluentforms-gift-certificates'); ?></h4>
                    <div id="ffgc-history-content"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#ffgc-balance-form').on('submit', function(e) {
                e.preventDefault();
                
                var code = $('#ffgc-balance-code').val();
                var resultDiv = $('#ffgc-balance-result');
                var historyDiv = $('#ffgc-usage-history');
                
                if (!code) {
                    resultDiv.html('<div class="ffgc-error"><?php _e('Please enter a gift certificate code.', 'fluentforms-gift-certificates'); ?></div>').show();
                    return;
                }
                
                resultDiv.html('<div class="ffgc-loading"><?php _e('Checking balance...', 'fluentforms-gift-certificates'); ?></div>').show();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ffgc_check_balance',
                        code: code,
                        nonce: '<?php echo wp_create_nonce('ffgc_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<div class="ffgc-success">';
                            html += '<h4><?php _e('Certificate Found!', 'fluentforms-gift-certificates'); ?></h4>';
                            html += '<p><strong><?php _e('Balance:', 'fluentforms-gift-certificates'); ?></strong> $' + response.data.balance + '</p>';
                            html += '<p><strong><?php _e('Total Value:', 'fluentforms-gift-certificates'); ?></strong> $' + response.data.total + '</p>';
                            html += '<p><strong><?php _e('Used Amount:', 'fluentforms-gift-certificates'); ?></strong> $' + response.data.used + '</p>';
                            html += '</div>';
                            
                            resultDiv.html(html);
                            
                            // Show usage history if enabled
                            if ('<?php echo $atts['show_history']; ?>' === 'yes') {
                                loadUsageHistory(code);
                            }
                        } else {
                            resultDiv.html('<div class="ffgc-error">' + response.data + '</div>');
                        }
                    },
                    error: function() {
                        resultDiv.html('<div class="ffgc-error"><?php _e('An error occurred. Please try again.', 'fluentforms-gift-certificates'); ?></div>');
                    }
                });
            });
            
            function loadUsageHistory(code) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ffgc_get_usage_history',
                        code: code,
                        nonce: '<?php echo wp_create_nonce('ffgc_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = '<table class="ffgc-history-table">';
                            html += '<thead><tr><th><?php _e('Date', 'fluentforms-gift-certificates'); ?></th><th><?php _e('Amount Used', 'fluentforms-gift-certificates'); ?></th><th><?php _e('Form', 'fluentforms-gift-certificates'); ?></th></tr></thead>';
                            html += '<tbody>';
                            
                            response.data.forEach(function(usage) {
                                html += '<tr>';
                                html += '<td>' + usage.date + '</td>';
                                html += '<td>$' + usage.amount + '</td>';
                                html += '<td>' + usage.form_title + '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table>';
                            $('#ffgc-history-content').html(html);
                            $('#ffgc-usage-history').show();
                        }
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    
    public function designs_showcase_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Gift Certificate Designs', 'fluentforms-gift-certificates'),
            'columns' => '3',
            'show_prices' => 'yes'
        ), $atts);
        
        $designs = get_posts(array(
            'post_type' => 'ffgc_design',
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
        
        if (empty($designs)) {
            return '<p>' . __('No gift certificate designs available.', 'fluentforms-gift-certificates') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="ffgc-designs-showcase">
            <?php if (!empty($atts['title'])): ?>
                <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            
            <div class="ffgc-designs-grid" style="grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);">
                <?php foreach ($designs as $design): ?>
                    <div class="ffgc-design-item">
                        <?php if (has_post_thumbnail($design->ID)): ?>
                            <div class="ffgc-design-image">
                                <?php echo get_the_post_thumbnail($design->ID, 'medium'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="ffgc-design-content">
                            <h4><?php echo esc_html($design->post_title); ?></h4>
                            <p><?php echo esc_html($design->post_content); ?></p>
                            
                            <?php if ($atts['show_prices'] === 'yes'): ?>
                                <?php
                                $min_amount = get_post_meta($design->ID, '_min_amount', true);
                                $max_amount = get_post_meta($design->ID, '_max_amount', true);
                                ?>
                                <div class="ffgc-design-price">
                                    <span class="ffgc-price-range">
                                        $<?php echo number_format($min_amount, 2); ?> - $<?php echo number_format($max_amount, 2); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
} 