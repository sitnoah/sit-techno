<?php
if (!defined('ABSPATH')) { exit; }
get_header();
if (get_option('show_on_front') === 'page' && have_posts()) {
    while (have_posts()) { the_post(); the_content(); }
} else { echo sit_theme_resolve(sit_theme_pages()['']['html']); }
get_footer();
