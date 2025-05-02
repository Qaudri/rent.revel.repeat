<?php
/*
Plugin Name: Linen Catalog
Description: Custom post type and shortcode to display linens with ACF integration and inquiry page.
Version: 1.4
Author: Muhammad AbdulQuadir Akanfe
*/

// Register 'linen' post type
function linen_register_post_type() {
    register_post_type('linen', array(
        'labels' => array(
            'name' => 'Linens',
            'singular_name' => 'Linen',
        ),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-archive',
        'supports' => array('title', 'thumbnail'), // Removed 'editor' support
        'rewrite' => array('slug' => 'linens'),
    ));
}
add_action('init', 'linen_register_post_type');

// Shortcode to display linens in the catalog
function linen_shortcode_output($atts) {
    $args = array(
        'post_type' => 'linen',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    $output = '<div class="linen-catalog">';

    while ($query->have_posts()) {
        $query->the_post();
        $permalink = get_permalink();

        $output .= '<div class="linen-item">';
        if (has_post_thumbnail()) {
            $output .= '<div class="linen-item-image">';
            $output .= get_the_post_thumbnail(null, 'medium');
            $output .= '</div>';
        }
        $output .= '<h3 class="linen-item-title">' . esc_html(get_the_title()) . '</h3>';
        $output .= '<a href="' . esc_url($permalink) . '" class="linen-details-button">More details</a>';
        $output .= '</div>';
    }
    wp_reset_postdata();

    $output .= '</div>';
    return $output;
}
add_shortcode('linens', 'linen_shortcode_output');

// Handle inquiry page
function linen_inquiry_page() {
    $output = '';
    
    if (isset($_GET['linen_id'])) {
        $linen_id = intval($_GET['linen_id']);
        $linen = get_post($linen_id);
        
        if ($linen) {
            $color = isset($_GET['color']) ? sanitize_text_field($_GET['color']) : '';
            $size = isset($_GET['size']) ? sanitize_text_field($_GET['size']) : '';
            
            $output .= '<div class="linen-inquiry-container">';
            $output .= '<h1>Inquiry for ' . esc_html($linen->post_title) . '</h1>';
            
            if (!empty($color)) {
                $output .= '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
            }
            
            if (!empty($size)) {
                $output .= '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';
            }
            
            // Add a form to submit the inquiry
            $output .= '<form class="linen-inquiry-form" method="post">';
            $output .= '<div class="form-group">';
            $output .= '<label for="name">Your Name</label>';
            $output .= '<input type="text" name="name" id="name" required>';
            $output .= '</div>';
            
            $output .= '<div class="form-group">';
            $output .= '<label for="email">Your Email</label>';
            $output .= '<input type="email" name="email" id="email" required>';
            $output .= '</div>';
            
            $output .= '<div class="form-group">';
            $output .= '<label for="message">Message</label>';
            $output .= '<textarea name="message" id="message" rows="4"></textarea>';
            $output .= '</div>';
            
            $output .= '<button type="submit" class="submit-inquiry">Submit Inquiry</button>';
            $output .= '</form>';
            $output .= '</div>';
        } else {
            $output .= '<p>Linen not found.</p>';
        }
    } else {
        $output .= '<p>No linen selected for inquiry.</p>';
    }
    
    return $output;
}
add_shortcode('linen_inquiry', 'linen_inquiry_page');

// Register meta boxes for linen post type (simpler approach than ACF)
function linen_register_meta_boxes() {
    add_meta_box(
        'linen_details',
        'Linen Details',
        'linen_details_callback',
        'linen',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'linen_register_meta_boxes');

// Meta box callback function
function linen_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('linen_save_meta', 'linen_meta_nonce');
    
    // Get saved values
    $description = get_post_meta($post->ID, '_linen_description', true);
    $colors = get_post_meta($post->ID, '_linen_colors', true);
    $sizes = get_post_meta($post->ID, '_linen_sizes', true);
    
    // Parse arrays
    $colors_array = !empty($colors) ? explode(',', $colors) : array('');
    $sizes_array = !empty($sizes) ? explode(',', $sizes) : array('');
    
    // Output description field
    echo '<p><label for="linen_description"><strong>Description:</strong></label></p>';
    echo '<textarea id="linen_description" name="linen_description" style="width: 100%; height: 150px;">' . esc_textarea($description) . '</textarea>';
    
    // Output colors field
    echo '<p><label><strong>Colors:</strong></label> (comma-separated list)</p>';
    echo '<input type="text" name="linen_colors" value="' . esc_attr($colors) . '" style="width: 100%;">';
    
    // Output sizes field
    echo '<p><label><strong>Sizes:</strong></label> (comma-separated list)</p>';
    echo '<input type="text" name="linen_sizes" value="' . esc_attr($sizes) . '" style="width: 100%;">';
}

// Save meta box data
function linen_save_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['linen_meta_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['linen_meta_nonce'], 'linen_save_meta')) {
        return;
    }
    
    // Don't save on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save description
    if (isset($_POST['linen_description'])) {
        update_post_meta($post_id, '_linen_description', sanitize_textarea_field($_POST['linen_description']));
    }
    
    // Save colors
    if (isset($_POST['linen_colors'])) {
        update_post_meta($post_id, '_linen_colors', sanitize_text_field($_POST['linen_colors']));
    }
    
    // Save sizes
    if (isset($_POST['linen_sizes'])) {
        update_post_meta($post_id, '_linen_sizes', sanitize_text_field($_POST['linen_sizes']));
    }
}
add_action('save_post', 'linen_save_meta');

// Admin notice if ACF is not active
function linen_check_acf_dependency() {
    if (!class_exists('ACF') && current_user_can('activate_plugins')) {
        ?>
        <div class="notice notice-warning">
            <p>Advanced Custom Fields is not activated. Linen Catalog is using a simplified field system instead.</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'linen_check_acf_dependency');

// Enqueue necessary styles
function linen_enqueue_styles() {
    wp_enqueue_style('linen-catalog-styles', plugin_dir_url(__FILE__) . 'css/linen-catalog.css', array(), '1.3');
}
add_action('wp_enqueue_scripts', 'linen_enqueue_styles');

?>