<?php
if (!defined('ABSPATH')) { exit; }

function sit_theme_office_details($key) {
    if (!in_array($key, array('uk','liberia','cote-divoire'), true)) { return ''; }
    $locations = function_exists('sit_core_public_locations') ? sit_core_public_locations() : array();
    $row = $locations[$key] ?? array();
    if (empty($row['confirmed'])) {
        return '<p class="location-pending"><strong>Address details awaiting confirmation.</strong><br>Please contact us before planning a visit.</p>';
    }
    $kinds = array('office'=>'Office address', 'registered'=>'Registered office', 'correspondence'=>'Correspondence address');
    $html = '<p class="location-kind">' . esc_html($kinds[$row['kind']] ?? 'Business address') . '</p><address>' . nl2br(esc_html($row['address'])) . '</address>';
    if (!empty($row['email'])) { $html .= '<a href="' . esc_url('mailto:' . $row['email']) . '">' . esc_html($row['email']) . '</a>'; }
    if (!empty($row['phone'])) { $html .= '<a href="' . esc_url('tel:' . preg_replace('/[^+0-9]/', '', $row['phone'])) . '">' . esc_html($row['phone']) . '</a>'; }
    if (!empty($row['hours'])) { $html .= '<p><strong>Contact hours</strong><br>' . esc_html($row['hours']) . '</p>'; }
    if (!empty($row['visiting'])) { $html .= '<p>' . esc_html($row['visiting']) . '</p>'; }
    if ($row['kind'] !== 'office') { $html .= '<p class="company-note">This address is not an invitation to visit. Contact us to agree any meeting.</p>'; }
    $html .= '<div class="location-actions"><button type="button" class="text-link" data-copy-address="' . esc_attr($row['address']) . '">Copy address</button>';
    if (!empty($row['directions']) && $row['kind'] === 'office') {
        $html .= '<a href="' . esc_url('https://www.google.com/maps/search/?api=1&query=' . rawurlencode($row['address'])) . '" target="_blank" rel="noopener noreferrer">View map <span class="screen-reader-text">(Google Maps, opens in a new tab)</span>↗</a>';
    }
    return $html . '</div>';
}

/** Add only missing pages. Never replace copy or select a new home page. */
function sit_theme_add_company_pages() {
    if (!current_user_can('manage_options') || !current_user_can('edit_pages') || !current_user_can('publish_pages')) {
        return new WP_Error('sit_forbidden', 'You do not have permission to publish these pages.');
    }
    $result = array();
    $pages = sit_theme_pages();
    foreach (array('contact','team') as $slug) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        if ($existing) { $result[$slug] = 'preserved'; continue; }
        if (empty($pages[$slug])) { return new WP_Error('sit_missing_page', 'The packaged company page is missing. Reinstall the theme.'); }
        $page = $pages[$slug];
        $id = wp_insert_post(array('post_type'=>'page', 'post_status'=>'publish', 'post_name'=>$slug, 'post_title'=>$page['title'], 'post_content'=>'[sit_page name="' . $slug . '"]'), true);
        if (is_wp_error($id)) { return $id; }
        update_post_meta($id, '_sit_page', $slug);
        update_post_meta($id, '_sit_description', $page['description']);
        $result[$slug] = 'created';
    }
    return $result;
}
function sit_theme_company_setup_panel() {
    echo '<hr><h2>Add Contact and Team pages</h2><p>Publish the two missing pages using the current packaged design. Existing pages at these paths are preserved, including custom content. This action does not change the homepage or refresh other pages. The Team page openly labels every role as a mockup.</p><ul>';
    foreach (array('contact'=>'Contact Us','team'=>'Team') as $slug=>$label) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        echo '<li>' . esc_html($label) . ': ';
        if ($existing) { echo '<a href="' . esc_url(get_edit_post_link($existing->ID, 'raw')) . '">Existing page — review in the editor</a>'; }
        else { echo 'Will be created and published'; }
        echo '</li>';
    }
    echo '</ul><p>If you use a custom WordPress navigation menu, add Contact and Team to that menu after publishing. Configure verified public addresses under <strong>SIT Requests → Office details</strong> in Core 0.4.0 or newer. Online enquiries require the Core intake settings to be ready.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
    wp_nonce_field('sit_add_company_pages');
    echo '<input type="hidden" name="action" value="sit_add_company_pages"><p><button class="button button-primary">Add missing Contact and Team pages</button></p></form>';
}
add_action('admin_post_sit_add_company_pages', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Use the site setup form.', '', array('response'=>405)); }
    check_admin_referer('sit_add_company_pages');
    $result = sit_theme_add_company_pages();
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', array('response'=>400)); }
    wp_safe_redirect(admin_url('themes.php?page=sit-setup&company_added=1'));
    exit;
});
