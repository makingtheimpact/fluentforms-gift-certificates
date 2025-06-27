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
                    $total_certificates = wp_count_posts('gift_certificate');
                    echo esc_html($total_certificates->publish);
                    ?>
                </div>
            </div>
            
            <div class="ffgc-stat-card">
                <h3><?php _e('Unused Certificates', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $unused_certificates = get_posts(array(
                        'post_type' => 'gift_certificate',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'meta_query' => array(
                            array(
                                'key' => '_certificate_status',
                                'value' => 'unused',
                                'compare' => '='
                            )
                        )
                    ));
                    echo esc_html(count($unused_certificates));
                    ?>
                </div>
            </div>
            
            <div class="ffgc-stat-card">
                <h3><?php _e('Total Value', 'fluentforms-gift-certificates'); ?></h3>
                <div class="ffgc-stat-number">
                    <?php
                    $total_value = 0;
                    $certificates = get_posts(array(
                        'post_type' => 'gift_certificate',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    ));
                    
                    foreach ($certificates as $certificate) {
                        $amount = get_post_meta($certificate->ID, '_certificate_amount', true);
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
                    echo esc_html(count($active_designs));
                    ?>
                </div>
            </div>
        </div>
        
        <div class="ffgc-quick-actions">
            <h2><?php _e('Quick Actions', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-action-buttons">
                <a href="<?php echo admin_url('post-new.php?post_type=gift_certificate'); ?>" class="button button-primary">
                    <?php _e('Create New Certificate', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('post-new.php?post_type=gift_certificate_design'); ?>" class="button button-secondary">
                    <?php _e('Create New Design', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=gift_certificate'); ?>" class="button button-secondary">
                    <?php _e('View All Certificates', 'fluentforms-gift-certificates'); ?>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=gift_certificate_design'); ?>" class="button button-secondary">
                    <?php _e('View All Designs', 'fluentforms-gift-certificates'); ?>
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
                    'post_type' => 'gift_certificate',
                    'posts_per_page' => 5,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                if (empty($recent_certificates)) {
                    echo '<p>' . __('No recent activity.', 'fluentforms-gift-certificates') . '</p>';
                } else {
                    foreach ($recent_certificates as $certificate) {
                        $code = get_post_meta($certificate->ID, '_certificate_code', true);
                        $amount = get_post_meta($certificate->ID, '_certificate_amount', true);
                        $status = get_post_meta($certificate->ID, '_certificate_status', true);
                        $recipient_name = get_post_meta($certificate->ID, '_recipient_name', true);
                        
                        echo '<div class="ffgc-activity-item">';
                        echo '<div class="ffgc-activity-icon">🎁</div>';
                        echo '<div class="ffgc-activity-content">';
                        echo '<strong>' . esc_html($recipient_name) . '</strong> - ';
                        echo esc_html($code) . ' ($' . number_format($amount, 2) . ')';
                        echo '<br><small>' . get_the_date('', $certificate->ID) . ' - ' . ucfirst($status) . '</small>';
                        echo '</div>';
                        echo '<div class="ffgc-activity-actions">';
                        echo '<a href="' . admin_url('post.php?post=' . $certificate->ID . '&action=edit') . '" class="button button-small">' . __('Edit', 'fluentforms-gift-certificates') . '</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        
        <div class="ffgc-shortcodes-info">
            <h2><?php _e('Shortcodes', 'fluentforms-gift-certificates'); ?></h2>
            
            <div class="ffgc-shortcode-list">
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