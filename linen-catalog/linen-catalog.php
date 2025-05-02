<?php
/*
Plugin Name: Linen Catalog
Description: Custom post type and shortcode to display linens with ACF integration and inquiry page.
Version: 1.3
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

// Shortcode to display linens in the catalog (only name and More Details button)
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

// Handle inquiry page (send color, size, and linen id)
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

// Add description field for linen post type to replace editor
function linen_register_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_linen_details',
            'title' => 'Linen Details',
            'fields' => array(
                array(
                    'key' => 'field_linen_description',
                    'label' => 'Description',
                    'name' => 'description',
                    'type' => 'textarea',
                    'instructions' => 'Enter the description for this linen item',
                    'required' => 0,
                ),
                array(
                    'key' => 'field_linen_color',
                    'label' => 'Colors Available',
                    'name' => 'color',
                    'type' => 'repeater',
                    'instructions' => 'Add the available colors',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_linen_color_value',
                            'label' => 'Color',
                            'name' => 'color_value',
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_linen_size',
                    'label' => 'Sizes Available',
                    'name' => 'size',
                    'type' => 'repeater',
                    'instructions' => 'Add the available sizes',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_linen_size_value',
                            'label' => 'Size',
                            'name' => 'size_value',
                            'type' => 'text',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'linen',
                    ),
                ),
            ),
        ));
    }
}
add_action('acf/init', 'linen_register_acf_fields');

// Admin notice if ACF is not active
function linen_check_acf_dependency() {
    if (!class_exists('ACF') && current_user_can('activate_plugins')) {
        ?>
        <div class="notice notice-error">
            <p>Linen Catalog plugin requires <a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> to be installed and activated.</p>
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