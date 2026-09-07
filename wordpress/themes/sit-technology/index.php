<?php
if (!defined('ABSPATH')) { exit; }
get_header();
if (is_404()) {
    echo '<section class="wrap error-page"><p class="eyebrow">404 / A DIFFERENT DIRECTION</p><h1>Let’s get you back on track.</h1><p>We couldn’t find that page.</p><a class="button" href="' . esc_url(home_url('/')) . '">Back to home ↗</a></section>';
} elseif (have_posts()) {
    while (have_posts()) {
        the_post();
        if (is_page() && get_post_meta(get_the_ID(), '_sit_page', true)) {
            sit_theme_page_content();
        } else {
            echo '<article class="wrap section"><h1>' . esc_html(get_the_title()) . '</h1><div class="text-block">';
            the_content();
            echo '</div></article>';
        }
    }
    the_posts_pagination();
} else {
    echo '<section class="wrap section"><h1>Welcome to SIT Consultancy.</h1><p>Use Appearance → SIT Site Setup to create the starter pages.</p></section>';
}
get_footer();
