<?php get_header(); ?>

<div class="linen-product-page">
    <?php
    if (have_posts()) : while (have_posts()) : the_post();
        // Get the featured image
        if (has_post_thumbnail()) {
            echo '<div class="linen-image">';
            the_post_thumbnail('full'); // You can change the size if needed
            echo '</div>';
        }

        // Get the product title
        echo '<h1>' . get_the_title() . '</h1>';

        // Display the ACF fields (Color and Size)
        $color = get_field('color');
        $size = get_field('size');
        
        if ($color) {
            echo '<p><strong>Color:</strong> ' . esc_html($color) . '</p>';
        }
        
        if ($size) {
            echo '<p><strong>Size:</strong> ' . esc_html($size) . '</p>';
        }
        
        // Display the content of the post (description)
        the_content();

    endwhile; endif;
    ?>
</div>

<?php get_footer(); ?>