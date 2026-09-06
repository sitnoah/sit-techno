<?php
/**
 * Plugin Name: SIT Technology Core
 * Description: Private business requests, validation, administration, audit events and a notification outbox for SIT Technology.
 * Version: 0.2.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: SIT Technology
 * License: GPL-2.0-or-later
 * Text Domain: sit-technology-core
 */
if (!defined('ABSPATH')) { exit; }
define('SIT_CORE_VERSION', '0.2.0');
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/requests.php';
require_once __DIR__ . '/includes/intake.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/workers.php';
require_once __DIR__ . '/includes/privacy.php';
register_activation_hook(__FILE__, 'sit_core_activate');
register_deactivation_hook(__FILE__, function () { wp_clear_scheduled_hook('sit_core_tick'); });
add_filter('cron_schedules', function ($s) { $s['sit_five_minutes'] = array('interval' => 300, 'display' => 'Every five minutes'); return $s; });
add_action('sit_core_tick', 'sit_core_worker');
function sit_core_ready() {
    $s = get_option('sit_core_settings', array());
    return get_option('sit_core_schema') === SIT_CORE_VERSION && !empty($s['enabled']) && is_email($s['notify_to'] ?? '') && !empty($s['privacy_reviewed']) && (int) ($s['retention_days'] ?? 0) > 0;
}
