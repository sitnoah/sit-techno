<?php
if (!defined('ABSPATH')) { exit; }

function sit_core_location_countries() {
    return array('uk'=>'United Kingdom', 'liberia'=>'Liberia', 'cote-divoire'=>'Côte d’Ivoire');
}
function sit_core_location_fields() {
    return array('address'=>500, 'email'=>190, 'phone'=>60, 'hours'=>200, 'visiting'=>300);
}
function sit_core_validate_locations($input) {
    if (!is_array($input) || array_diff(array_keys($input), array_keys(sit_core_location_countries()))) {
        return new WP_Error('sit_locations', 'Please use the three supported location records.');
    }
    $result = array();
    foreach (sit_core_location_countries() as $key=>$country) {
        $row = $input[$key] ?? array();
        if (!is_array($row) || array_diff(array_keys($row), array_merge(array_keys(sit_core_location_fields()), array('kind','confirmed','directions')))) {
            return new WP_Error('sit_locations', 'Please check the fields for ' . $country . '.');
        }
        $clean = array();
        foreach (sit_core_location_fields() as $field=>$max) {
            $value = $row[$field] ?? '';
            if (!is_string($value) || strlen($value) > $max*4 || sit_core_text_length($value) > $max) {
                return new WP_Error('sit_locations', 'Please check ' . $country . ': ' . $field . '.');
            }
            $clean[$field] = trim($field === 'address' ? sanitize_textarea_field($value) : sanitize_text_field($value));
        }
        if ($clean['email'] !== '' && !is_email($clean['email'])) { return new WP_Error('sit_locations', 'Enter a valid public email address for ' . $country . '.'); }
        if ($clean['phone'] !== '' && (!preg_match('/^\+?[0-9 ()\-.]{5,60}$/D', $clean['phone']) || strlen(preg_replace('/[^0-9]/', '', $clean['phone'])) < 5)) { return new WP_Error('sit_locations', 'Enter a public phone number using digits, spaces and an optional international + prefix.'); }
        $clean['kind'] = $row['kind'] ?? 'office';
        if (!is_string($clean['kind']) || !in_array($clean['kind'], array('office','registered','correspondence'), true)) { return new WP_Error('sit_locations', 'Choose a valid address type.'); }
        foreach (array('confirmed','directions') as $flag) {
            if (isset($row[$flag]) && !in_array($row[$flag], array('1','0',true,false), true)) { return new WP_Error('sit_locations', 'Invalid location confirmation.'); }
            $clean[$flag] = isset($row[$flag]) && in_array($row[$flag], array('1',true), true);
        }
        if ($clean['confirmed'] && $clean['address'] === '') { return new WP_Error('sit_locations', 'Add the confirmed public business address for ' . $country . ' before publishing its details.'); }
        $clean['directions'] = $clean['directions'] && $clean['confirmed'] && $clean['kind'] === 'office';
        $result[$key] = $clean;
    }
    return $result;
}

/** Draft contact details never leave the administration area. */
function sit_core_public_locations() {
    $saved = get_option('sit_core_locations', array());
    $valid = sit_core_validate_locations($saved);
    $result = array();
    foreach (sit_core_location_countries() as $key=>$country) {
        $row = !is_wp_error($valid) ? $valid[$key] : array();
        $result[$key] = !empty($row['confirmed']) ? $row : array('confirmed'=>false);
    }
    return $result;
}
add_action('admin_menu', function () {
    add_submenu_page('sit-enquiries', 'Office details', 'Office details', 'manage_options', 'sit-locations', 'sit_core_locations_screen');
});
function sit_core_locations_screen() {
    if (!current_user_can('manage_options')) { return; }
    $saved = get_option('sit_core_locations', array());
    $saved = is_array($saved) ? $saved : array();
    echo '<div class="wrap"><h1>SIT public office details</h1><p>Publish only verified business contact details that you are authorised to share. Do not use a personal address. Each location stays marked as awaiting confirmation until its confirmation box is selected. Saving here does not change the request-desk notification recipient.</p><p>After saving, clear the hosting/page cache so the Contact page shows the current details.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
    wp_nonce_field('sit_save_locations');
    echo '<input type="hidden" name="action" value="sit_save_locations">';
    foreach (sit_core_location_countries() as $key=>$country) {
        $row = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();
        echo '<h2>' . esc_html($country) . '</h2><table class="form-table" role="presentation">';
        $labels = array('address'=>'Public business address', 'email'=>'Public email address', 'phone'=>'Public phone number', 'hours'=>'Contact / opening hours', 'visiting'=>'Visiting and accessibility information');
        foreach (sit_core_location_fields() as $field=>$max) {
            $id = 'sit-' . $key . '-' . $field;
            $value = isset($row[$field]) && is_string($row[$field]) ? $row[$field] : '';
            echo '<tr><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($labels[$field]) . '</label></th><td>';
            $attrs = ' id="' . esc_attr($id) . '" name="locations[' . esc_attr($key) . '][' . esc_attr($field) . ']" class="regular-text" maxlength="' . (int)$max . '"';
            echo $field === 'address' ? '<textarea rows="4"' . $attrs . '>' . esc_textarea($value) . '</textarea>' : '<input type="' . ($field==='email' ? 'email' : 'text') . '"' . $attrs . ' value="' . esc_attr($value) . '">';
            echo '</td></tr>';
        }
        echo '<tr><th scope="row"><label for="sit-kind-' . esc_attr($key) . '">Address type</label></th><td><select id="sit-kind-' . esc_attr($key) . '" name="locations[' . esc_attr($key) . '][kind]">';
        foreach (array('office'=>'Office — visiting arrangements below', 'registered'=>'Registered office — no visitor invitation', 'correspondence'=>'Correspondence address — no visitor invitation') as $value=>$label) {
            echo '<option value="' . esc_attr($value) . '"' . selected($row['kind'] ?? 'office', $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></td></tr></table><p><label><input type="checkbox" name="locations[' . esc_attr($key) . '][confirmed]" value="1"' . checked(!empty($row['confirmed']), true, false) . '> These are confirmed public business details. Publish them on the Contact page.</label></p><p><label><input type="checkbox" name="locations[' . esc_attr($key) . '][directions]" value="1"' . checked(!empty($row['directions']), true, false) . '> Offer a Google Maps directions link (only for a confirmed visiting office).</label></p><hr>';
    }
    submit_button('Save office details');
    echo '</form></div>';
}
add_action('admin_post_sit_save_locations', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden', '', array('response'=>403)); }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Use the office settings form.', '', array('response'=>405)); }
    check_admin_referer('sit_save_locations');
    $result = sit_core_validate_locations(wp_unslash($_POST['locations'] ?? null));
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()), '', array('response'=>400, 'back_link'=>true)); }
    update_option('sit_core_locations', $result, false);
    wp_safe_redirect(admin_url('admin.php?page=sit-locations&saved=1'));
    exit;
});
