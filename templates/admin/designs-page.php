<?php
/**
 * Designs Page Template
 * 
 * @package FluentFormsGiftCertificates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Gift Certificate Designs', 'fluentforms-gift-certificates'); ?></h1>
    
    <div class="ffgc-designs-overview">
        <div class="ffgc-designs-header">
            <h2><?php _e('Manage Certificate Designs', 'fluentforms-gift-certificates'); ?></h2>
            <a href="<?php echo admin_url('post-new.php?post_type=ffgc_design'); ?>" class="button button-primary">
                <?php _e('Create New Design', 'fluentforms-gift-certificates'); ?>
            </a>
        </div>
        
        <div class="ffgc-designs-grid">
            <?php
            $designs = get_posts(array(
                'post_type' => 'ffgc_design',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if (empty($designs)) {
                echo '<div class="ffgc-no-designs">';
                echo '<p>' . __('No designs found. ', 'fluentforms-gift-certificates');
                echo '<a href="' . admin_url('post-new.php?post_type=ffgc_design') . '" class="button button-primary">' . __('Create Design', 'fluentforms-gift-certificates') . '</a>';
                echo '</p>';
                echo '</div>';
            } else {
                foreach ($designs as $design) {
                    $min_amount = get_post_meta($design->ID, '_min_amount', true);
                    $max_amount = get_post_meta($design->ID, '_max_amount', true);
                    $is_active = get_post_meta($design->ID, '_is_active', true);
                    $status_class = $is_active === 'yes' ? 'active' : 'inactive';
                    $status_text = $is_active === 'yes' ? __('Active', 'fluentforms-gift-certificates') : __('Inactive', 'fluentforms-gift-certificates');
                    
                    echo '<div class="ffgc-design-card">';
                    
                    if (has_post_thumbnail($design->ID)) {
                        echo '<div class="ffgc-design-image">';
                        echo get_the_post_thumbnail($design->ID, 'medium');
                        echo '</div>';
                    } else {
                        echo '<div class="ffgc-design-image-placeholder">';
                        echo '<span class="dashicons dashicons-art"></span>';
                        echo '</div>';
                    }
                    
                    echo '<div class="ffgc-design-content">';
                    echo '<h3>' . esc_html($design->post_title) . '</h3>';
                    echo '<p>' . esc_html($design->post_content) . '</p>';
                    
                    echo '<div class="ffgc-design-meta">';
                    echo '<div class="ffgc-design-price-range">';
                    echo '<strong>' . __('Amount Range:', 'fluentforms-gift-certificates') . '</strong> ';
                    echo '$' . number_format($min_amount, 2) . ' - $' . number_format($max_amount, 2);
                    echo '</div>';
                    
                    echo '<div class="ffgc-design-status">';
                    echo '<span class="ffgc-status ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="ffgc-design-actions">';
                    echo '<a href="' . admin_url('post.php?post=' . $design->ID . '&action=edit') . '" class="button button-secondary">' . __('Edit', 'fluentforms-gift-certificates') . '</a>';
                    echo '<a href="' . admin_url('post.php?post=' . $design->ID . '&action=edit') . '#email-template" class="button button-secondary">' . __('Email Template', 'fluentforms-gift-certificates') . '</a>';
                    echo '</div>';
                    
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
    
    <div class="ffgc-design-tips">
        <h2><?php _e('Design Tips', 'fluentforms-gift-certificates'); ?></h2>
        
        <div class="ffgc-tips-grid">
            <div class="ffgc-tip">
                <h3><?php _e('Email Templates', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('Use HTML and CSS to create beautiful email templates. Available placeholders: {recipient_name}, {certificate_code}, {amount}, {personal_message}, {expiry_date}, {site_name}, {site_url}', 'fluentforms-gift-certificates'); ?></p>
            </div>
            
            <div class="ffgc-tip">
                <h3><?php _e('Amount Ranges', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('Set different minimum and maximum amounts for each design to offer various gift certificate options to your customers.', 'fluentforms-gift-certificates'); ?></p>
            </div>
            
            <div class="ffgc-tip">
                <h3><?php _e('Featured Images', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('Add featured images to your designs to showcase them in the frontend shortcodes and admin interface.', 'fluentforms-gift-certificates'); ?></p>
            </div>
            
            <div class="ffgc-tip">
                <h3><?php _e('Active Status', 'fluentforms-gift-certificates'); ?></h3>
                <p><?php _e('Only active designs will be available for selection in purchase forms and shortcodes.', 'fluentforms-gift-certificates'); ?></p>
            </div>
        </div>
    </div>
</div> 