<?php
if (!defined('ABSPATH')) { exit; }
function sit_core_table($name) { global $wpdb; return $wpdb->prefix . 'sit_' . $name; }
function sit_core_activate() {
    global $wpdb;
    $result = sit_core_upgrade();
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message())); }
}
function sit_core_upgrade() {
    global $wpdb;
    if (is_multisite()) { return new WP_Error('sit_schema', 'SIT Core supports a single-site WordPress installation.'); }
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $requests = sit_core_table('enquiries'); $events = sit_core_table('events'); $outbox = sit_core_table('outbox'); $rates = sit_core_table('rates');
    dbDelta("CREATE TABLE $requests (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        reference varchar(40) NOT NULL,
        idempotency_hash char(64) NOT NULL,
        payload_hash char(64) NOT NULL,
        name varchar(120) NOT NULL,
        email varchar(190) NOT NULL,
        company varchar(190) NOT NULL,
        goal varchar(40) NOT NULL,
        service varchar(80) NOT NULL,
        brief text NOT NULL,
        budget varchar(40) NOT NULL,
        timeline varchar(40) NOT NULL,
        consent_at datetime NOT NULL,
        request_type varchar(30) NOT NULL DEFAULT 'enquiry',
        details text DEFAULT NULL,
        version bigint(20) unsigned NOT NULL DEFAULT 1,
        status varchar(30) NOT NULL DEFAULT 'new',
        assigned_to bigint(20) unsigned NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY reference (reference),
        UNIQUE KEY idempotency_hash (idempotency_hash),
        KEY email (email),
        KEY status_created (status,created_at),
        KEY type_status (request_type,status)
    ) ENGINE=InnoDB $charset;");
    dbDelta("CREATE TABLE $events (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        enquiry_id bigint(20) unsigned NOT NULL,
        actor_id bigint(20) unsigned NOT NULL DEFAULT 0,
        event varchar(100) NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY enquiry_id (enquiry_id)
    ) ENGINE=InnoDB $charset;");
    dbDelta("CREATE TABLE $outbox (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        enquiry_id bigint(20) unsigned NOT NULL,
        state varchar(30) NOT NULL DEFAULT 'pending',
        attempts int(11) NOT NULL DEFAULT 0,
        available_at datetime NOT NULL,
        locked_until datetime DEFAULT NULL,
        last_error varchar(190) NOT NULL DEFAULT '',
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY enquiry_id (enquiry_id),
        KEY available (state,available_at)
    ) ENGINE=InnoDB $charset;");
    dbDelta("CREATE TABLE $rates (
        bucket char(64) NOT NULL,
        hits int(11) NOT NULL DEFAULT 1,
        expires_at datetime NOT NULL,
        PRIMARY KEY  (bucket),
        KEY expires_at (expires_at)
    ) ENGINE=InnoDB $charset;");
    foreach (array($requests,$events,$outbox,$rates) as $table) {
        $engine = $wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $table));
        if (strtoupper((string) $engine) !== 'INNODB') { return new WP_Error('sit_schema', 'SIT Core requires InnoDB tables. Online requests remain disabled.'); }
    }
    $columns = $wpdb->get_col("SHOW COLUMNS FROM $requests");
    if (array_diff(array('request_type','details','version'), $columns ?: array())) {
        return new WP_Error('sit_schema', 'The request schema update did not complete. Check database permissions and retry.');
    }
    if ($role = get_role('administrator')) { $role->add_cap('manage_sit_enquiries'); }
    update_option('sit_core_schema', SIT_CORE_VERSION);
    if (!wp_next_scheduled('sit_core_tick')) { wp_schedule_event(time()+300, 'sit_five_minutes', 'sit_core_tick'); }
    return true;
}

// Updates are explicit, capability checked and nonce protected; existing settings stay unchanged.
add_action('admin_post_sit_upgrade', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden', '', array('response'=>403)); }
    check_admin_referer('sit_upgrade');
    $result = sit_core_upgrade();
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message())); }
    wp_safe_redirect(admin_url('admin.php?page=sit-settings&upgraded=1'));
    exit;
});
add_action('admin_notices', function () {
    if (!current_user_can('manage_options') || get_option('sit_core_schema') === SIT_CORE_VERSION) { return; }
    echo '<div class="notice notice-warning"><p>SIT Requests needs a database update. Take a database backup first. Existing requests and settings are preserved; online intake is paused until the update succeeds.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('sit_upgrade');
    echo '<input type="hidden" name="action" value="sit_upgrade"><p><button class="button button-primary">Update SIT request database</button></p></form></div>';
});
function sit_core_event($id, $event, $actor = 0) {
    global $wpdb;
    return $wpdb->insert(sit_core_table('events'), array('enquiry_id' => $id, 'actor_id' => $actor, 'event' => $event, 'created_at' => gmdate('Y-m-d H:i:s')));
}
