<?php
/*
Plugin Name: Linen Catalog
Description: Custom post type and shortcode to display linens with ACF integration and inquiry page.
Version: 1.2
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
        'supports' => array('title', 'editor', 'thumbnail'),
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
        $output .= '<a class="button" href="' . esc_url($permalink) . '">More details</a>';
        $output .= '</div>';
    }
    wp_reset_postdata();

    $output .= '</div>';
    return $output;
}
add_shortcode('linens', 'linen_shortcode_output');

// Display the image, name, description, size, color and inquiry button on the product page
function linen_single_product_display($content) {
    if ('linen' === get_post_type()) {
        $output = '';
        
        // Get featured image
        if (has_post_thumbnail()) {
            $output .= '<div class="linen-image">';
            $output .= get_the_post_thumbnail(null, 'full');
            $output .= '</div>';
        }

        // Get product name and description
        $output .= '<h1>' . get_the_title() . '</h1>';
        $output .= $content;

        // Get custom fields (Color and Size)
        $color = get_field('color');
        $size = get_field('size');

        $output .= '<form action="' . esc_url(get_site_url() . '/inquiry/') . '" method="GET">';
        
        // Display color selection if available
        if ($color) {
            $output .= '<label for="color">Color:</label>';
            $output .= '<select name="color" id="color">';
            foreach ($color as $color_option) {
                $output .= '<option value="' . esc_attr($color_option) . '">' . esc_html($color_option) . '</option>';
            }
            $output .= '</select>';
        }

        // Display size selection if available
        if ($size) {
            $output .= '<label for="size">Size:</label>';
            $output .= '<select name="size" id="size">';
            foreach ($size as $size_option) {
                $output .= '<option value="' . esc_attr($size_option) . '">' . esc_html($size_option) . '</option>';
            }
            $output .= '</select>';
        }

        $output .= '<input type="hidden" name="linen_id" value="' . get_the_ID() . '">'; // Add linen ID for inquiry
        
        // Inquiry button
        $output .= '<button type="submit" class="button">Make Inquiry</button>';
        
        $output .= '</form>';
        
        return $output;
    }
    return $content;
}
add_filter('the_content', 'linen_single_product_display');

// Handle inquiry page (send color, size, and linen id)
function linen_inquiry_page() {
    if (isset($_GET['linen_id'])) {
        $linen_id = intval($_GET['linen_id']);
        $linen = get_post($linen_id);
        $color = isset($_GET['color']) ? sanitize_text_field($_GET['color']) : '';
        $size = isset($_GET['size']) ? sanitize_text_field($_GET['size']) : '';

        echo '<h1>Inquiry for ' . esc_html($linen->post_title) . '</h1>';
        echo '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
        echo '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';

        // You can also add more details here or a form to submit the inquiry
    }
}
add_shortcode('linen_inquiry', 'linen_inquiry_page');

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
    wp_enqueue_style('linen-catalog-styles', plugin_dir_url(__FILE__) . 'css/linen-catalog.css');
}
add_action('wp_enqueue_scripts', 'linen_enqueue_styles');

?>
