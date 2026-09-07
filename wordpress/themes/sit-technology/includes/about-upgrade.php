<?php
if (!defined('ABSPATH')) { exit; }

/** Opt-in, page-scoped adoption for old HTML starters. No activation-time writes. */
function sit_theme_about_upgrade($id, $expected_hash) {
    if (!current_user_can('manage_options') || !current_user_can('edit_post', $id)) {
        return new WP_Error('sit_forbidden', 'You do not have permission to update this page.');
    }
    $page = get_page_by_path('about', OBJECT, 'page');
    if (!$page || (int) $page->ID !== (int) $id || $page->post_status === 'trash' || get_post_meta($id, '_sit_page', true) !== 'about') {
        return new WP_Error('sit_not_starter', 'Only the existing SIT About starter page can be updated here.');
    }
    if (!is_string($expected_hash) || !hash_equals(hash('sha256', $page->post_content), $expected_hash)) {
        return new WP_Error('sit_stale_page', 'The About page has changed. Reload this screen and review it before trying again.');
    }
    $content = '[sit_page name="about"]';
    if (trim($page->post_content) === $content) { return $id; }
    // Preserve the exact prior copy even when WordPress revisions are disabled.
    $backup = add_post_meta($id, '_sit_about_design_backup', wp_slash(array(
        'theme_version' => SIT_THEME_VERSION,
        'saved_at' => current_time('mysql', true),
        'post_content' => $page->post_content,
    )));
    if (!$backup) { return new WP_Error('sit_backup_failed', 'The existing About copy could not be backed up. No page content was changed.'); }
    wp_save_post_revision($id);
    // The title, publication state, SEO metadata, parent and other pages stay intact.
    return wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $content)), true);
}

function sit_theme_about_upgrade_panel() {
    if (!current_user_can('manage_options')) { return; }
    $page = get_page_by_path('about', OBJECT, 'page');
    echo '<hr><h2>Update only the About page</h2>';
    if (!$page || get_post_meta($page->ID, '_sit_page', true) !== 'about' || $page->post_status === 'trash') {
        echo '<p>No matching SIT About starter page was found. Custom pages are never replaced by this action.</p>';
        return;
    }
    if (trim($page->post_content) === '[sit_page name="about"]') {
        echo '<p>Your About page already uses the packaged design and will receive theme updates automatically. Clear the hosting cache if the public page still looks unchanged.</p>';
        return;
    }
    echo '<p>Use the new company story, delivery explorer, principles, resources and FAQs without refreshing any other page. This replaces the current About-page body. Its title, visibility and SEO metadata stay unchanged. A copy is saved in protected page metadata and a WordPress revision is requested before replacement.</p>';
    echo '<p><a href="' . esc_url(get_edit_post_link($page->ID, 'raw')) . '">Review your current About copy first</a>. Keep a site backup before applying the change.</p>';
    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
    wp_nonce_field('sit_theme_update_about');
    echo '<input type="hidden" name="action" value="sit_theme_update_about"><input type="hidden" name="page_id" value="' . esc_attr($page->ID) . '"><input type="hidden" name="content_hash" value="' . esc_attr(hash('sha256', $page->post_content)) . '">';
    echo '<p><label><input type="checkbox" name="confirm_about" value="1" required> Replace only the About page body with the packaged design. I have reviewed and backed up any editorial changes.</label></p><p><button class="button button-primary">Apply the new About design</button></p></form>';
}

add_action('admin_post_sit_theme_update_about', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden', '', array('response' => 403)); }
    check_admin_referer('sit_theme_update_about');
    if (!isset($_POST['confirm_about']) || $_POST['confirm_about'] !== '1') {
        wp_die('Confirm that you want to replace only the About page body.', '', array('response' => 400));
    }
    $id = isset($_POST['page_id']) && is_scalar($_POST['page_id']) ? absint($_POST['page_id']) : 0;
    $hash = isset($_POST['content_hash']) && is_string($_POST['content_hash']) ? wp_unslash($_POST['content_hash']) : '';
    $result = sit_theme_about_upgrade($id, $hash);
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', array('response' => 409)); }
    wp_safe_redirect(admin_url('themes.php?page=sit-setup&about_updated=1'));
    exit;
});
