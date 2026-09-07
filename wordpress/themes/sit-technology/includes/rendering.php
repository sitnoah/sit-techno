<?php
if (!defined('ABSPATH')) { exit; }

/** Render only packaged, allowlisted page content after WordPress text formatting. */
function sit_theme_packaged_page($name) {
    $slug = $name === 'home' ? '' : $name;
    $pages = sit_theme_pages();
    if (!is_string($slug) || !isset($pages[$slug])) { return ''; }
    return do_shortcode(sit_theme_resolve($pages[$slug]['html']));
}
add_shortcode('sit_page', function ($atts) {
    $name = $atts['name'] ?? '';
    return is_string($name) && $name !== '' ? sit_theme_packaged_page($name) : '';
});

/**
 * Existing SIT starters contain complete HTML layouts, not plain paragraphs.
 * wpautop splits block-level links and inserts grid items. Suspend only that
 * filter for this render, preserving password gates, shortcodes and other hooks.
 * Ordinary WordPress pages/posts retain their normal formatting.
 */
function sit_theme_page_content() {
    $name = get_post_meta(get_the_ID(), '_sit_page', true);
    $slug = $name === 'home' ? '' : $name;
    $starter = is_page() && is_string($name) && $name !== '' && isset(sit_theme_pages()[$slug]);
    $priority = $starter ? has_filter('the_content', 'wpautop') : false;
    if ($priority !== false) { remove_filter('the_content', 'wpautop', $priority); }
    try {
        the_content();
    } finally {
        if ($priority !== false) { add_filter('the_content', 'wpautop', $priority); }
    }
    if ($starter && $name === 'start-a-project' && !post_password_required() && !has_shortcode(get_post_field('post_content', get_the_ID()), 'sit_page')) {
        $page = sit_theme_pages()['start-a-project']['html'];
        preg_match('/<section class="wrap project-layout">.*$/s', $page, $matches);
        echo sit_theme_resolve($matches[0] ?? ''); // Trusted packaged form only.
    }
}
