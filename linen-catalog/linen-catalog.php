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
