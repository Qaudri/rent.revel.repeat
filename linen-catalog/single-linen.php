<?php get_header(); ?>

<div class="single-linen-container">
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
        $post_id = get_the_ID();
        
        // Left side - Image
        echo '<div class="single-linen-image">';
        if (has_post_thumbnail()) {
            the_post_thumbnail('full');
        } else {
            echo '<div class="no-image">No image available</div>';
        }
        echo '</div>';

        // Right side - Details
        echo '<div class="single-linen-details">';
        echo '<h1 class="single-linen-title">' . get_the_title() . '</h1>';
        
        // Description (from custom meta)
        $description = get_post_meta($post_id, '_linen_description', true);
        if (!empty($description)) {
            echo '<div class="single-linen-description">' . wpautop($description) . '</div>';
        }
        
        // Get colors and sizes (from custom meta)
        $colors_raw = get_post_meta($post_id, '_linen_colors', true);
        $sizes_raw = get_post_meta($post_id, '_linen_sizes', true);
        
        $colors = !empty($colors_raw) ? array_map('trim', explode(',', $colors_raw)) : array();
        $sizes = !empty($sizes_raw) ? array_map('trim', explode(',', $sizes_raw)) : array();
        
        echo '<div class="single-linen-fields">';
        
        // Colors Field
        if (!empty($colors)) {
            echo '<div class="field-group">';
            echo '<label for="linen-color">Color:</label>';
            echo '<select name="linen-color" id="linen-color" class="linen-select">';
            foreach ($colors as $color) {
                if (!empty($color)) {
                    echo '<option value="' . esc_attr($color) . '">' . esc_html($color) . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';
        }
        
        // Sizes Field
        if (!empty($sizes)) {
            echo '<div class="field-group">';
            echo '<label for="linen-size">Size:</label>';
            echo '<select name="linen-size" id="linen-size" class="linen-select">';
            foreach ($sizes as $size) {
                if (!empty($size)) {
                    echo '<option value="' . esc_attr($size) . '">' . esc_html($size) . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';
        }
        
        echo '</div>'; // End single-linen-fields
        
        // Inquiry form with JavaScript to handle selection values
        echo '<div class="linen-inquiry-section">';
        echo '<form id="linen-inquiry-form" action="' . esc_url(get_site_url() . '/inquiry/') . '" method="GET" class="single-linen-form">';
        echo '<input type="hidden" name="linen_id" value="' . get_the_ID() . '">';
        echo '<button type="submit" class="inquiry-button">Make Inquiry</button>';
        echo '</form>';
        echo '</div>';
        
        // JavaScript to handle form submission with selected values
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('linen-inquiry-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Get selected color and size
                    var colorSelect = document.getElementById('linen-color');
                    var sizeSelect = document.getElementById('linen-size');
                    
                    // Add color to form if exists
                    if (colorSelect && colorSelect.value) {
                        var colorInput = document.createElement('input');
                        colorInput.type = 'hidden';
                        colorInput.name = 'color';
                        colorInput.value = colorSelect.value;
                        form.appendChild(colorInput);
                    }
                    
                    // Add size to form if exists
                    if (sizeSelect && sizeSelect.value) {
                        var sizeInput = document.createElement('input');
                        sizeInput.type = 'hidden';
                        sizeInput.name = 'size';
                        sizeInput.value = sizeSelect.value;
                        form.appendChild(sizeInput);
                    }
                });
            }
        });
        </script>
        <?php
        
        echo '</div>'; // End single-linen-details
    endwhile; endif;
    ?>
</div>

<?php get_footer(); ?>