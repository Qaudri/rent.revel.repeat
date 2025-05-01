<?php
/*
Plugin Name: Linen Catalog
Description: Custom post type and shortcode to display linens with ACF integration.
Version: 1.1
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Linen_Catalog {
    
    /**
     * Constructor - initialize the plugin
     */
    public function __construct() {
        // Register post type
        add_action('init', array($this, 'register_post_type'));
        
        // Register shortcode
        add_shortcode('linens', array($this, 'shortcode_output'));
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
        // Add admin notice if ACF is not active
        add_action('admin_notices', array($this, 'check_acf_dependency'));
    }
    
    /**
     * Register 'linen' post type
     */
    public function register_post_type() {
        register_post_type('linen', array(
            'labels' => array(
                'name' => 'Linens',
                'singular_name' => 'Linen',
                'add_new' => 'Add New Linen',
                'add_new_item' => 'Add New Linen',
                'edit_item' => 'Edit Linen',
                'view_item' => 'View Linen',
                'search_items' => 'Search Linens',
                'not_found' => 'No linens found',
            ),
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-archive',
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'linens'),
        ));
    }
    
    /**
     * Check if ACF is active
     */
    public function is_acf_active() {
        return class_exists('ACF');
    }
    
    /**
     * Admin notice if ACF is not active
     */
    public function check_acf_dependency() {
        if (!$this->is_acf_active() && current_user_can('activate_plugins')) {
            ?>
            <div class="notice notice-error">
                <p>Linen Catalog plugin requires <a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> to be installed and activated.</p>
            </div>
            <?php
        }
    }
    
    /**
     * Enqueue CSS for the linen catalog
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'linen-catalog-styles',
            plugin_dir_url(__FILE__) . 'css/linen-catalog.css',
            array(),
            '1.0.0'
        );
    }
    
    /**
     * Shortcode to display linens
     */
    public function shortcode_output($atts) {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'orderby' => 'title', 
            'order' => 'ASC',
        ), $atts);
        
        $args = array(
            'post_type' => 'linen',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p>No linens found.</p>';
        }
        
        $output = '<div class="linen-catalog">';
        
        while ($query->have_posts()) {
            $query->the_post();
            
            // Check if ACF is active before trying to get fields
            $color = $this->is_acf_active() ? get_field('color') : '';
            $size = $this->is_acf_active() ? get_field('size') : '';
            
            $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            $permalink = get_permalink();
            
            // Build the inquiry URL using site URL for portability
            $inquiry_url = add_query_arg(
                array('linen' => get_the_ID()),
                site_url('/inquiry/')
            );
            
            $output .= '<div class="linen-item">';
            
            if ($image) {
                $output .= '<img src="' . esc_url($image) . '" alt="' . esc_attr(get_the_title()) . '">';
            }
            
            $output .= '<h3>' . esc_html(get_the_title()) . '</h3>';
            
            if ($color) {
                $output .= '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
            }
            
            if ($size) {
                $output .= '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';
            }
            
            $output .= '<div class="linen-buttons">';
            $output .= '<a class="button" href="' . esc_url($permalink) . '">More details</a> ';
            $output .= '<a class="button" href="' . esc_url($inquiry_url) . '">Make inquiry</a>';
            $output .= '</div>';
            
            $output .= '</div>';
        }
        
        wp_reset_postdata();
        $output .= '</div>';
        
        return $output;
    }
}

// Initialize the plugin
$linen_catalog = new Linen_Catalog();