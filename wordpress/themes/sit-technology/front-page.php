<?php
if (!defined('ABSPATH')) { exit; }
get_header();
if (get_option('show_on_front') === 'page' && have_posts()) {
    while (have_posts()) { the_post(); sit_theme_page_content(); }
} else { echo sit_theme_packaged_page('home'); }
get_footer();
