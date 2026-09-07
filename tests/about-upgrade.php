<?php
// Isolated checks for the explicit About-only migration. No live WordPress writes.
define('ABSPATH', __DIR__); define('OBJECT', 'OBJECT'); define('SIT_THEME_VERSION', '0.5.0');
class WP_Error { function __construct(public $code, public $message) {} }
function is_wp_error($value) { return $value instanceof WP_Error; }
function add_action(...$args) {}
function current_user_can($cap, ...$args) { global $admin, $editable; return $cap === 'manage_options' ? $admin : $editable; }
function get_page_by_path(...$args) { global $page; return $page; }
function get_post_meta(...$args) { global $marker; return $marker; }
function current_time(...$args) { return '2026-09-07 12:00:00'; }
function wp_slash($value) { return is_array($value) ? array_map('wp_slash', $value) : (is_string($value) ? addslashes($value) : $value); }
function wp_unslash($value) { return is_array($value) ? array_map('wp_unslash', $value) : (is_string($value) ? stripslashes($value) : $value); }
function add_post_meta($id, $key, $value) { global $backups, $backup_ok; if (!$backup_ok) { return false; } $backups[] = [$id, $key, wp_unslash($value)]; return count($backups); }
function wp_save_post_revision($id) { global $revisions; $revisions[] = $id; return 0; }
function wp_update_post($value, $error) { global $updates, $update_ok; $updates[] = wp_unslash($value); return $update_ok ? $value['ID'] : new WP_Error('db_error', 'Save failed'); }
require dirname(__DIR__) . '/wordpress/themes/sit-technology/includes/about-upgrade.php';
$count = 0;
function check_about($yes, $why) { global $count; $count++; if (!$yes) { throw new RuntimeException($why); } }
function reset_about() {
    global $page, $marker, $admin, $editable, $backups, $updates, $revisions, $backup_ok, $update_ok;
    $page = (object) ['ID'=>9, 'post_content'=>'<p>Edited About copy with "quotes" and \\slashes.</p>', 'post_status'=>'private'];
    $marker='about'; $admin=true; $editable=true; $backups=[]; $updates=[]; $revisions=[]; $backup_ok=true; $update_ok=true;
}
function upgrade_about($id=9) { global $page; return sit_theme_about_upgrade($id, hash('sha256', $page->post_content)); }
reset_about(); $admin=false; check_about(is_wp_error(upgrade_about()), 'Deny non-administrator'); check_about(!$updates && !$backups, 'Denied operation never writes');
reset_about(); $editable=false; check_about(is_wp_error(upgrade_about()), 'Require page editing capability');
reset_about(); check_about(is_wp_error(upgrade_about(8)), 'Reject a different page ID');
reset_about(); $marker='home'; check_about(is_wp_error(upgrade_about()), 'Reject another SIT starter');
reset_about(); $marker=''; check_about(is_wp_error(upgrade_about()), 'Preserve custom About page');
reset_about(); $page->post_status='trash'; check_about(is_wp_error(upgrade_about()), 'Do not restore trashed pages');
reset_about(); check_about(is_wp_error(sit_theme_about_upgrade(9, 'stale')), 'Reject a stale copy'); check_about(!$updates && !$backups, 'Stale action never writes');
reset_about(); $page=null; check_about(is_wp_error(sit_theme_about_upgrade(9, 'unused')), 'Reject missing About page');
reset_about(); $backup_ok=false; check_about(is_wp_error(upgrade_about()), 'Stop when backup fails'); check_about(!$updates && !$revisions, 'Failed backup never changes copy');
reset_about(); $original=$page->post_content; check_about(upgrade_about()===9, 'Update intended page');
check_about($backups[0][2]['post_content']===$original, 'Preserve exact quotes and backslashes in backup');
check_about($updates===[['ID'=>9, 'post_content'=>'[sit_page name="about"]']], 'Change body only; no title, status, parent or SEO writes');
check_about($revisions===[9], 'Request native WordPress revision');
reset_about(); $page->post_content='[sit_page name="about"]'; check_about(upgrade_about()===9 && !$updates && !$backups, 'Already managed page is a no-op');
reset_about(); $update_ok=false; check_about(is_wp_error(upgrade_about()), 'Report failed page save'); check_about(count($backups)===1, 'Keep backup after a failed save');
echo "PASS: $count About-only upgrade checks.\n";
