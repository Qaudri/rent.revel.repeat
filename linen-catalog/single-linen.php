<?php get_header(); ?>

<div class="single-linen-container">
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
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
        
        // Description from ACF field instead of content
        $description = get_field('description');
        if ($description) {
            echo '<div class="single-linen-description">' . wpautop($description) . '</div>';
        }

        // ACF Fields
        $colors = get_field('color');
        $sizes = get_field('size');

        echo '<div class="single-linen-fields">';
        
        // Colors Field
        if ($colors && is_array($colors) && count($colors) > 0) {
            echo '<div class="field-group">';
            echo '<label for="linen-color">Color:</label>';
            echo '<select name="linen-color" id="linen-color" class="linen-select">';
            foreach ($colors as $color_option) {
                $color_value = isset($color_option['color_value']) ? $color_option['color_value'] : '';
                if (!empty($color_value)) {
                    echo '<option value="' . esc_attr($color_value) . '">' . esc_html($color_value) . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';
        }

        // Sizes Field
        if ($sizes && is_array($sizes) && count($sizes) > 0) {
            echo '<div class="field-group">';
            echo '<label for="linen-size">Size:</label>';
            echo '<select name="linen-size" id="linen-size" class="linen-select">';
            foreach ($sizes as $size_option) {
                $size_value = isset($size_option['size_value']) ? $size_option['size_value'] : '';
                if (!empty($size_value)) {
                    echo '<option value="' . esc_attr($size_value) . '">' . esc_html($size_value) . '</option>';
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