<?php
/**
 * Main Admin Page Template
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Gift Certificates', 'fluentforms-gift-certificates'); ?></h1>
    
    <div class="ffgc-admin-dashboard">
        <div class="ffgc-stats-grid">
            <div class="ffgc-stat-card">
                <h3><?php _e('Total Certificates', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $total_certificates = wp_count_posts('ffgc_cert');
                    $publish_count = isset($total_certificates->publish) ? $total_certificates->publish : 0;
                    echo esc_html($publish_count);
                    ?>
                </div>
            </div>
            
            <div class="ffgc-stat-card">
                <h3><?php _e('Active Certificates', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $active_certificates = get_posts(array(
                        'post_type' => 'ffgc_cert',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'meta_query' => array(
                            array(
                                'key' => '_status',
                                'value' => 'active',
                                'compare' => '='
                            )
                        )
                    ));
                    echo esc_html(count($active_certificates));
                    ?>
                </div>
            </div>
            
            <div class="ffgc-stat-card">
                <h3><?php _e('Total Value', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $total_value = 0;
                    $certificates = get_posts(array(
                        'post_type' => 'ffgc_cert',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    ));
                    
                    foreach ($certificates as $certificate) {
                        $amount = get_post_meta($certificate->ID, '_amount', true);
                        $total_value += floatval($amount);
                    }
                    
                    echo '$' . number_format($total_value, 2);
                    ?>
                </div>
            </div>
            
            <div class="ffgc-stat-card">
                <h3><?php _e('Active Designs', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $active_designs = get_posts(array(
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
                    echo esc_html(count($active_designs));
                    ?>
                </div>
            </div>
        </div>
        
        <div class="ffgc-quick-actions">
            <h2><?php _e('Quick Actions', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-action-buttons">
                <a href="<?php echo admin_url('post-new.php?post_type=ffgc_cert'); ?>" class="button button-primary">
                    <?php _e('Create Certificate', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('post-new.php?post_type=ffgc_design'); ?>" class="button button-secondary">
                    <?php _e('Create Design', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=ffgc_cert'); ?>" class="button button-secondary">
                    <?php _e('View All Certificates', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=ffgc_design'); ?>" class="button button-secondary">
                    <?php _e('View All Designs', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=fluentforms-gift-certificates-forms'); ?>" class="button button-secondary">
                    <?php _e('Configure Forms', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=fluentforms-gift-certificates-settings'); ?>" class="button button-secondary">
                    <?php _e('Settings', 'fluentforms-gift-certificates'); ?>
                </a>
            </div>
        </div>
        
        <div class="ffgc-recent-activity">
            <h2><?php _e('Recent Activity', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-activity-list">
                <?php
                $recent_certificates = get_posts(array(
                    'post_type' => 'ffgc_cert',
                    'posts_per_page' => 5,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                if (!empty($recent_certificates)) {
                    echo '<ul>';
                    foreach ($recent_certificates as $certificate) {
                        $code = get_post_meta($certificate->ID, '_certificate_code', true);
                        $amount = get_post_meta($certificate->ID, '_amount', true);
                        echo '<li><strong>' . esc_html($certificate->post_title) . '</strong> - ' . esc_html($code) . ' ($' . number_format($amount, 2) . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . __('No certificates found.', 'fluentforms-gift-certificates') . '</p>';
                }
                ?>
            </div>
        </div>
        
        <div class="ffgc-shortcodes-info">
            <h2><?php _e('Shortcodes & Integration', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-shortcode-list">
                <div class="ffgc-shortcode-item">
                    <h4><?php _e('Fluent Forms Integration', 'fluentforms-gift-certificates'); ?></h4>
                    <p><?php _e('Use the new field types in Fluent Forms:', 'fluentforms-gift-certificates'); ?></p>
                    <ul>
                        <li><strong><?php _e('Gift Certificate Design', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Visual design selection with grid layout', 'fluentforms-gift-certificates'); ?></li>
                        <li><strong><?php _e('Gift Certificate Redemption', 'fluentforms-gift-certificates'); ?></strong> - <?php _e('Code entry with balance checking', 'fluentforms-gift-certificates'); ?></li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=fluentforms-gift-certificates-forms'); ?>" class="button button-small"><?php _e('Configure Forms', 'fluentforms-gift-certificates'); ?></a>
                </div>
                
                <div class="ffgc-shortcode-item">
                    <h4><?php _e('Balance Checker', 'fluentforms-gift-certificates'); ?></h4>
                    <code>[gift_certificate_balance]</code>
                    <p><?php _e('Displays a form to check gift certificate balance.', 'fluentforms-gift-certificates'); ?></p>
                </div>
                
                <div class="ffgc-shortcode-item">
                    <h4><?php _e('Purchase Form', 'fluentforms-gift-certificates'); ?></h4>
                    <code>[gift_certificate_purchase]</code>
                    <p><?php _e('Displays a form to purchase gift certificates.', 'fluentforms-gift-certificates'); ?></p>
                </div>
                
                <div class="ffgc-shortcode-item">
                    <h4><?php _e('Designs Showcase', 'fluentforms-gift-certificates'); ?></h4>
                    <code>[gift_certificate_designs]</code>
                    <p><?php _e('Displays available gift certificate designs.', 'fluentforms-gift-certificates'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div> 