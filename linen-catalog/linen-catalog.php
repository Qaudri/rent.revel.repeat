<?php
/*
Plugin Name: Linen Catalog
Description: Custom post type and shortcode to display linens with ACF integration and inquiry page.
Version: 1.6
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
    
    // Check for linen_name parameter (the key parameter we need)
    if (isset($_GET['linen_name'])) {
        $linen_name = sanitize_text_field($_GET['linen_name']);
        $color = isset($_GET['linen_color']) ? sanitize_text_field($_GET['linen_color']) : '';
        $size = isset($_GET['linen_size']) ? sanitize_text_field($_GET['linen_size']) : '';
        
        $output .= '<div class="linen-inquiry-container">';
        $output .= '<h1>Inquiry for ' . esc_html($linen_name) . '</h1>';
        
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
        
        // Add hidden fields to keep track of the linen details
        $output .= '<input type="hidden" name="linen_name" value="' . esc_attr($linen_name) . '">';
        if (!empty($color)) {
            $output .= '<input type="hidden" name="linen_color" value="' . esc_attr($color) . '">';
        }
        if (!empty($size)) {
            $output .= '<input type="hidden" name="linen_size" value="' . esc_attr($size) . '">';
        }
        
        $output .= '<button type="submit" class="submit-inquiry">Submit Inquiry</button>';
        $output .= '</form>';
        $output .= '</div>';
    } else {
        $output .= '<p>No linen selected for inquiry. Please go back to the <a href="' . esc_url(get_post_type_archive_link('linen')) . '">linens catalog</a>.</p>';
    }
    
    // For debugging purposes, uncomment to see all GET parameters
    // $output .= '<div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd;">';
    // $output .= '<h3>Debug Information</h3>';
    // $output .= '<pre>' . print_r($_GET, true) . '</pre>';
    // $output .= '</div>';
    
    return $output;
}
add_shortcode('linen_inquiry', 'linen_inquiry_page');

// Register meta boxes for linen post type
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

// Create a template file for single-linen.php if it doesn't exist
function linen_setup_template() {
    $theme_dir = get_stylesheet_directory();
    $template_path = $theme_dir . '/single-linen.php';
    
    // Only create the file if it doesn't exist
    if (!file_exists($template_path)) {
        $template_content = '<?php
/*
 * Template for displaying single linen posts
 * Automatically generated by Linen Catalog plugin
 */

get_header(); ?>

<div class="single-linen-container">
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
        $post_id = get_the_ID();
        
        // Left side - Image
        echo \'<div class="single-linen-image">\';
        if (has_post_thumbnail()) {
            the_post_thumbnail(\'full\');
        } else {
            echo \'<div class="no-image">No image available</div>\';
        }
        echo \'</div>\';

        // Right side - Details
        echo \'<div class="single-linen-details">\';
        echo \'<h1 class="single-linen-title">\' . get_the_title() . \'</h1>\';
        
        // Description (from custom meta)
        $description = get_post_meta($post_id, \'_linen_description\', true);
        if (!empty($description)) {
            echo \'<div class="single-linen-description">\' . wpautop(esc_html($description)) . \'</div>\';
        } else {
            echo \'<div class="single-linen-description"><p><em>No description available</em></p></div>\';
        }
        
        // Get colors and sizes (from custom meta)
        $colors_raw = get_post_meta($post_id, \'_linen_colors\', true);
        $sizes_raw = get_post_meta($post_id, \'_linen_sizes\', true);
        
        $colors = !empty($colors_raw) ? array_map(\'trim\', explode(\',\', $colors_raw)) : array();
        $sizes = !empty($sizes_raw) ? array_map(\'trim\', explode(\',\', $sizes_raw)) : array();
        
        echo \'<div class="single-linen-fields">\';
        
        // Colors Field
        if (!empty($colors) && $colors[0] != \'\') {
            echo \'<div class="field-group">\';
            echo \'<label for="linen-color">Color:</label>\';
            echo \'<select name="linen-color" id="linen-color" class="linen-select">\';
            foreach ($colors as $color) {
                if (!empty($color)) {
                    echo \'<option value="\' . esc_attr($color) . \'">\' . esc_html($color) . \'</option>\';
                }
            }
            echo \'</select>\';
            echo \'</div>\';
        }
        
        // Sizes Field
        if (!empty($sizes) && $sizes[0] != \'\') {
            echo \'<div class="field-group">\';
            echo \'<label for="linen-size">Size:</label>\';
            echo \'<select name="linen-size" id="linen-size" class="linen-select">\';
            foreach ($sizes as $size) {
                if (!empty($size)) {
                    echo \'<option value="\' . esc_attr($size) . \'">\' . esc_html($size) . \'</option>\';
                }
            }
            echo \'</select>\';
            echo \'</div>\';
        }
        
        echo \'</div>\'; // End single-linen-fields
        
        // Inquiry form with GET method to redirect to inquiry page
        echo \'<div class="linen-inquiry-section">\';
        echo \'<form id="linen-inquiry-form" action="\' . esc_url(get_site_url() . \'/inquiry/\') . \'" method="GET" class="single-linen-form">\';
        echo \'<input type="hidden" name="linen_name" value="\' . esc_attr(get_the_title()) . \'">\';
        echo \'<button type="submit" class="inquiry-button">Make Inquiry</button>\';
        echo \'</form>\';
        echo \'</div>\';
        
        // JavaScript to handle form submission with selected values
        ?>
        <script type="text/javascript">
        document.addEventListener(\'DOMContentLoaded\', function() {
            var form = document.getElementById(\'linen-inquiry-form\');
            if (form) {
                form.addEventListener(\'submit\', function(e) {
                    // Get selected color and size
                    var colorSelect = document.getElementById(\'linen-color\');
                    var sizeSelect = document.getElementById(\'linen-size\');
                    
                    // Add color to form if exists
                    if (colorSelect && colorSelect.value) {
                        var colorInput = document.createElement(\'input\');
                        colorInput.type = \'hidden\';
                        colorInput.name = \'linen_color\';
                        colorInput.value = colorSelect.value;
                        form.appendChild(colorInput);
                    }
                    
                    // Add size to form if exists
                    if (sizeSelect && sizeSelect.value) {
                        var sizeInput = document.createElement(\'input\');
                        sizeInput.type = \'hidden\';
                        sizeInput.name = \'linen_size\';
                        sizeInput.value = sizeSelect.value;
                        form.appendChild(sizeInput);
                    }
                });
            }
        });
        </script>
        <?php
        
        echo \'</div>\'; // End single-linen-details
    endwhile; 
    else:
        echo \'<p>No linen product found.</p>\';
    endif;
    ?>
</div>

<?php get_footer(); ?>';
        
        // Create the template file
        file_put_contents($template_path, $template_content);
    }
}
add_action('after_setup_theme', 'linen_setup_template');

// Add template debugging function
function linen_debug_template() {
    global $template;
    if (is_singular('linen') && current_user_can('administrator')) {
        echo '<div style="position:fixed; bottom:0; left:0; background:rgba(0,0,0,0.8); color:white; padding:10px; z-index:9999; font-size:12px;">
            Template: ' . esc_html($template) . '
        </div>';
    }
}
add_action('wp_footer', 'linen_debug_template');

// Make sure the template file is actually used
function linen_use_custom_template($template) {
    if (is_singular('linen')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'single-linen.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'linen_use_custom_template');

// Enqueue necessary styles
function linen_enqueue_styles() {
    wp_enqueue_style('linen-catalog-styles', plugin_dir_url(__FILE__) . 'css/linen-catalog.css', array(), '1.5');
}
add_action('wp_enqueue_scripts', 'linen_enqueue_styles');

// Flush rewrite rules on plugin activation to ensure custom post type URLs work
function linen_plugin_activation() {
    linen_register_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'linen_plugin_activation');