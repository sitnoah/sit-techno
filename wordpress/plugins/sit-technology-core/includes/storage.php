<?php
if (!defined('ABSPATH')) { exit; }
function sit_core_table($name) { global $wpdb; return $wpdb->prefix . 'sit_' . $name; }
function sit_core_activate() {
    global $wpdb;
    if (is_multisite()) { wp_die('SIT Core 0.1 supports a single-site WordPress installation.'); }
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
        status varchar(30) NOT NULL DEFAULT 'new',
        assigned_to bigint(20) unsigned NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY reference (reference),
        UNIQUE KEY idempotency_hash (idempotency_hash),
        KEY email (email),
        KEY status_created (status,created_at)
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
        if (strtoupper((string) $engine) !== 'INNODB') { wp_die('SIT Core requires InnoDB tables. Enquiries have not been enabled.'); }
    }
    if ($role = get_role('administrator')) { $role->add_cap('manage_sit_enquiries'); }
    update_option('sit_core_schema', SIT_CORE_VERSION);
    if (!wp_next_scheduled('sit_core_tick')) { wp_schedule_event(time()+300, 'sit_five_minutes', 'sit_core_tick'); }
}
function sit_core_event($id, $event, $actor = 0) {
    global $wpdb;
    return $wpdb->insert(sit_core_table('events'), array('enquiry_id' => $id, 'actor_id' => $actor, 'event' => $event, 'created_at' => gmdate('Y-m-d H:i:s')));
}
