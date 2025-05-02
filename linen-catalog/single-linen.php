<?php get_header(); ?>

<div class="single-linen-container">
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
        // Left side - Image
        echo '<div class="single-linen-image">';
        if (has_post_thumbnail()) {
            the_post_thumbnail('full');
        }
        echo '</div>';

        // Right side - Details
        echo '<div class="single-linen-details">';
        echo '<h1 class="single-linen-title">' . get_the_title() . '</h1>';
        echo '<div class="single-linen-description">';
        the_content();
        echo '</div>';

        // ACF Fields
        $color = get_field('color');
        $size = get_field('size');

        if ($color || $size) {
            echo '<div class="single-linen-fields">';
            if ($color) {
                echo '<div class="field-group">';
                echo '<label for="color">Color:</label>';
                echo '<select name="color" id="color">';
                foreach ($color as $color_option) {
                    echo '<option value="' . esc_attr($color_option) . '">' . esc_html($color_option) . '</option>';
                }
                echo '</select>';
                echo '</div>';
            }

            if ($size) {
                echo '<div class="field-group">';
                echo '<label for="size">Size:</label>';
                echo '<select name="size" id="size">';
                foreach ($size as $size_option) {
                    echo '<option value="' . esc_attr($size_option) . '">' . esc_html($size_option) . '</option>';
                }
                echo '</select>';
                echo '</div>';
            }
            echo '</div>';
        }

        // Inquiry form
        echo '<form action="' . esc_url(get_site_url() . '/inquiry/') . '" method="GET" class="single-linen-form">';
        echo '<input type="hidden" name="linen_id" value="' . get_the_ID() . '">';
        echo '<button type="submit">Make Inquiry</button>';
        echo '</form>';

        echo '</div>'; // End single-linen-details
    endwhile; endif;
    ?>
</div>

<?php get_footer(); ?>