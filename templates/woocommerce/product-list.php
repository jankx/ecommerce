<?php

if ($wp_query->have_posts()) {
    woocommerce_product_loop_start();

    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        wc_get_template_part('content', 'product');
    }

    woocommerce_product_loop_end();
}