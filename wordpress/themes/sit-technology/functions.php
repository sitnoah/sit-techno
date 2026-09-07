<?php
if (!defined('ABSPATH')) { exit; }
define('SIT_THEME_VERSION', '0.4.2');
require_once __DIR__ . '/includes/rendering.php';
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_editor_style(array('assets/site.css', 'assets/redesign.css', 'assets/consultancy.css', 'assets/mobile.css'));
    register_nav_menus(array('primary' => __('Primary navigation', 'sit-technology')));
});
function sit_theme_pages() {
    static $pages;
    if ($pages === null) { $pages = json_decode(file_get_contents(get_template_directory() . '/content/pages.json'), true) ?: array(); }
    return $pages;
}
function sit_theme_resolve($html) {
    $html = preg_replace_callback('/\{\{url:(.*?)\}\}/', function ($m) {
        return esc_url(home_url('/' . $m[1]));
    }, $html);
    $html = preg_replace_callback('/\{\{asset:(.*?)\}\}/', function ($m) {
        return esc_url(get_template_directory_uri() . '/assets/' . $m[1]);
    }, $html);
    return str_replace('{{year}}', esc_html(wp_date('Y')), $html);
}
function sit_theme_markup($name) {
    if (!in_array($name, array('header', 'footer'), true)) { return; }
    $markup = sit_theme_resolve(file_get_contents(get_template_directory() . '/content/' . $name . '.html'));
    if ($name === 'header' && has_nav_menu('primary')) {
        $menu = wp_nav_menu(array('theme_location'=>'primary','container'=>false,'echo'=>false,'fallback_cb'=>false,'items_wrap'=>'<ul>%3$s</ul>','depth'=>1));
        $nav = '<nav id="primary-nav" class="primary-nav" aria-label="Main navigation">' . $menu . '<button class="search-open" aria-label="Search SIT Consultancy">Search</button><a class="button nav-cta" href="' . esc_url(home_url('/start-a-project/')) . '">Discuss a project ↗</a></nav>';
        $markup = preg_replace_callback('/<nav id="primary-nav".*?<\/nav>/s', function () use ($nav) { return $nav; }, $markup);
    }
    echo $markup; // Trusted theme template; generated URLs and menu content escaped by WordPress.
}
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('sit-theme', get_template_directory_uri() . '/assets/site.css', array(), SIT_THEME_VERSION);
    wp_enqueue_style('sit-redesign', get_template_directory_uri() . '/assets/redesign.css', array('sit-theme'), SIT_THEME_VERSION);
    wp_enqueue_style('sit-consultancy', get_template_directory_uri() . '/assets/consultancy.css', array('sit-redesign'), SIT_THEME_VERSION);
    wp_enqueue_style('sit-mobile', get_template_directory_uri() . '/assets/mobile.css', array('sit-consultancy'), SIT_THEME_VERSION);
    wp_enqueue_script('sit-discovery', get_template_directory_uri() . '/assets/discovery.js', array('sit-theme'), SIT_THEME_VERSION, array('strategy'=>'defer','in_footer'=>true));
    wp_enqueue_script('sit-theme', get_template_directory_uri() . '/assets/site.js', array(), SIT_THEME_VERSION, array('strategy' => 'defer', 'in_footer' => true));
    wp_localize_script('sit-theme', 'SIT_CONFIG', array(
        'enabled' => defined('SIT_CORE_VERSION') && version_compare(SIT_CORE_VERSION, '0.3.0', '>=') && function_exists('sit_core_ready') && sit_core_ready(),
        'endpoint' => rest_url('sit/v1/enquiries'),
        'tokenEndpoint' => rest_url('sit/v1/enquiry-token'),
    ));
});
add_action('wp_head', function () {
    if (is_singular()) {
        $description = get_post_meta(get_queried_object_id(), '_sit_description', true);
        if ($description) { echo '<meta name="description" content="' . esc_attr($description) . '">'; }
    }
    echo '<meta name="theme-color" content="#f4d63b">';
    if (!has_site_icon()) { echo '<link rel="icon" type="image/svg+xml" href="' . esc_url(get_template_directory_uri() . '/assets/sit-technology-icon.svg') . '">'; }
}, 2);
// Reusable page sections remain in the theme; editable page content is created only on an explicit admin action.
add_action('admin_menu', function () { add_theme_page('SIT Site Setup', 'SIT Site Setup', 'manage_options', 'sit-setup', 'sit_theme_setup_screen'); });
function sit_theme_setup_screen() {
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="wrap"><h1>SIT Consultancy site setup</h1><p>Create the 32 starter pages on a fresh WordPress installation. Existing pages with matching paths are preserved. This publishes the approved starter copy and selects Home as the front page. Review the privacy page before opening enquiries.</p><p>New and refreshed starter pages use a packaged design shortcode, so layouts and forms match the preview and update with the theme. Existing HTML starters keep their stored copy and receive the rendering repair automatically. The optional Core plugin adds private enquiry management.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
    wp_nonce_field('sit_theme_seed');
    echo '<input type="hidden" name="action" value="sit_theme_seed"><p><label><input type="checkbox" name="refresh_sit" value="1"> Apply the latest design to existing SIT starter pages. This replaces their copy with the current packaged design and keeps those pages aligned with future theme updates. Back up editorial changes first. Other pages are preserved.</label></p><p><button class="button button-primary">Create starter pages</button></p></form></div>';
}
add_action('admin_post_sit_theme_seed', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden', '', array('response' => 403)); }
    check_admin_referer('sit_theme_seed');
    $created = array(); $home_id = 0;
    foreach (sit_theme_pages() as $slug => $page) {
        $path = $slug ?: 'home';
        $existing = get_page_by_path($path);
        if ($existing && (empty($_POST['refresh_sit']) || !get_post_meta($existing->ID, '_sit_page', true))) { $created[$path] = $existing->ID; if (!$slug) { $home_id = $existing->ID; } continue; }
        $parts = explode('/', $path); $name = array_pop($parts); $parent = implode('/', $parts);
        $content = '[sit_page name="' . ($slug ?: 'home') . '"]';
        if ($existing) { wp_save_post_revision($existing->ID); }
        $id = wp_insert_post(array('ID' => $existing ? $existing->ID : 0, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $slug ? $page['title'] : 'Home', 'post_name' => $name, 'post_parent' => $created[$parent] ?? 0, 'post_content' => sit_theme_resolve($content)), true);
        if (is_wp_error($id)) { wp_die(esc_html($id->get_error_message())); }
        update_post_meta($id, '_sit_description', $page['description']);
        update_post_meta($id, '_sit_page', $slug ?: 'home');
        $created[$path] = $id; if (!$slug) { $home_id = $id; }
    }
    if ($home_id) { update_option('show_on_front', 'page'); update_option('page_on_front', $home_id); }
    wp_safe_redirect(admin_url('themes.php?page=sit-setup&created=1')); exit;
});

// Keep interactive controls in trusted templates while page copy remains editable.
add_shortcode('sit_component', function ($atts) {
    $name = $atts['name'] ?? '';
    if (!in_array($name, array('ambition-explorer','service-finder','industry-explorer','ai-planner'), true)) { return ''; }
    return sit_theme_resolve(file_get_contents(get_template_directory() . '/content/components/' . $name . '.html'));
});
add_action('wp_enqueue_scripts', function () {
    $records = array();
    foreach (get_posts(array('post_type'=>array('page','post'),'post_status'=>'publish','posts_per_page'=>200,'orderby'=>'title','order'=>'ASC','has_password'=>false)) as $post) {
        $description = get_post_meta($post->ID, '_sit_description', true);
        if (!$description) { $description = wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 28); }
        $records[] = array('title'=>get_the_title($post), 'description'=>$description, 'url'=>get_permalink($post), 'category'=>$post->post_type==='post' ? 'Insight' : 'Company');
    }
    wp_localize_script('sit-discovery', 'SIT_SEARCH_PAGES', $records);
}, 20);
