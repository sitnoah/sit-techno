<?php
/**
 * Plugin Name: SIT Technology Core
 * Description: Private business requests, validation, administration, audit events and a notification outbox for SIT Technology.
 * Version: 0.4.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: SIT Technology
 * License: GPL-2.0-or-later
 * Text Domain: sit-technology-core
 */
if (!defined('ABSPATH')) { exit; }
define('SIT_CORE_VERSION', '0.4.0');
// Advance only when database structure changes; patch releases keep intake running.
define('SIT_CORE_SCHEMA_VERSION', '0.3.0');
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/requests.php';
require_once __DIR__ . '/includes/intake.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/workers.php';
require_once __DIR__ . '/includes/privacy.php';
require_once __DIR__ . '/includes/locations.php';
register_activation_hook(__FILE__, 'sit_core_activate');
register_deactivation_hook(__FILE__, function () { wp_clear_scheduled_hook('sit_core_tick'); });
add_filter('cron_schedules', function ($s) { $s['sit_five_minutes'] = array('interval' => 300, 'display' => 'Every five minutes'); return $s; });
add_action('sit_core_tick', 'sit_core_worker');
function sit_core_readiness_issues() {
    $s = get_option('sit_core_settings', array());
    if (!is_array($s)) { $s = array(); }
    $issues = array();
    if (get_option('sit_core_schema') !== SIT_CORE_SCHEMA_VERSION) { $issues['database'] = 'Complete the SIT request database update.'; }
    if (empty($s['enabled'])) { $issues['enabled'] = 'Live enquiries are switched off in settings.'; }
    if (!is_email($s['notify_to'] ?? '')) { $issues['email'] = 'Set a valid authorised team notification mailbox.'; }
    if (empty($s['privacy_reviewed'])) { $issues['privacy'] = 'Review the privacy notice and confirm it in settings.'; }
    $days = (int) ($s['retention_days'] ?? 0);
    if ($days < 1 || $days > 3650) { $issues['retention'] = 'Choose an agreed retention period between 1 and 3,650 days.'; }
    return $issues;
}
function sit_core_ready() { return !sit_core_readiness_issues(); }
