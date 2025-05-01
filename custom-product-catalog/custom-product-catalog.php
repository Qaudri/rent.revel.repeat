<?php
/**
 * Plugin Name: Custom Product Catalog
 * Description: A simple product catalog for rental products with custom attributes like size and color.
 * Version: 1.0
 * Author: Muhammad AbdulQuadir Akanfe
 */

// Register custom post type 'rental_product'
function cpc_register_product_post_type() {
    register_post_type('rental_product', array(
        'labels' => array(
            'name' => 'Rental Products',
            'singular_name' => 'Rental Product',
            'add_new_item' => 'Add New Product',
            'edit_item' => 'Edit Product',
            'new_item' => 'New Product',
            'view_item' => 'View Product',
            'search_items' => 'Search Products',
            'not_found' => 'No products found',
            'all_items' => 'All Products',
            'menu_name' => 'Rental Products',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-archive',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'products'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'cpc_register_product_post_type');

// Register custom meta fields (size, color)
function cpc_register_product_meta_fields() {
    register_post_meta('rental_product', '_custom_size', array(
        'type' => 'string',
        'description' => 'Size of the product',
        'single' => true,
        'show_in_rest' => true,
    ));
    register_post_meta('rental_product', '_custom_color', array(
        'type' => 'string',
        'description' => 'Color of the product',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'cpc_register_product_meta_fields');

// Add meta boxes for admin UI
function cpc_add_product_meta_boxes() {
    add_meta_box(
        'cpc_product_details',
        'Product Details',
        'cpc_product_details_callback',
        'rental_product',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'cpc_add_product_meta_boxes');

// Meta box callback function
function cpc_product_details_callback($post) {
    wp_nonce_field('cpc_save_product_details', 'cpc_product_details_nonce');
    $size = get_post_meta($post->ID, '_custom_size', true);
    $color = get_post_meta($post->ID, '_custom_color', true);
    ?>
    <p>
        <label for="custom_size">Size:</label>
        <input type="text" id="custom_size" name="custom_size" value="<?php echo esc_attr($size); ?>">
    </p>
    <p>
        <label for="custom_color">Color:</label>
        <input type="text" id="custom_color" name="custom_color" value="<?php echo esc_attr($color); ?>">
    </p>
    <?php
}

// Save meta box data
function cpc_save_product_details($post_id) {
    if (!isset($_POST['cpc_product_details_nonce']) || 
        !wp_verify_nonce($_POST['cpc_product_details_nonce'], 'cpc_save_product_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['custom_size'])) {
        update_post_meta($post_id, '_custom_size', sanitize_text_field($_POST['custom_size']));
    }
    
    if (isset($_POST['custom_color'])) {
        update_post_meta($post_id, '_custom_color', sanitize_text_field($_POST['custom_color']));
    }
}
add_action('save_post_rental_product', 'cpc_save_product_details');

// Shortcode to display product catalog with pagination
function cpc_product_catalog_shortcode($atts) {
    $args = array(
        'post_type' => 'rental_product',
        'posts_per_page' => 10,  // Adjust number of products per page
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        echo '<div class="product-catalog">';
        while ($query->have_posts()) {
            $query->the_post();
            $size = get_post_meta(get_the_ID(), '_custom_size', true);
            $color = get_post_meta(get_the_ID(), '_custom_color', true);
            $product_url = get_permalink();

            echo '<div class="product-item">';
            echo '<a href="' . esc_url($product_url) . '">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';
            echo '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
            echo '</a>';
            echo '</div>';
        }
        wp_reset_postdata();
        
        // Pagination
        echo '<div class="pagination">';
        echo paginate_links(array(
            'total' => $query->max_num_pages,
        ));
        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    } else {
        return '<p>No products found.</p>';
    }
}
add_shortcode('custom_product_catalog', 'cpc_product_catalog_shortcode');

// Display product details with size, color, and inquiry button on single product pages
function cpc_product_details_on_single_product($content) {
    if (is_singular('rental_product')) {
        $size = get_post_meta(get_the_ID(), '_custom_size', true);
        $color = get_post_meta(get_the_ID(), '_custom_color', true);
        $inquiry_url = '/request-quote';  // Update with your actual URL

        $content .= '<div class="product-details">';
        $content .= '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';
        $content .= '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
        $content .= '<a href="' . esc_url($inquiry_url) . '" class="inquiry-button">Request a Quote</a>';
        $content .= '</div>';
    }
    return $content;
}
add_filter('the_content', 'cpc_product_details_on_single_product');
