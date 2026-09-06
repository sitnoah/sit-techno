<?php
// Deliberately preserve business records on uninstall. Erase through the verified
// WordPress privacy workflow or the agreed retention setting before removal.
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
wp_clear_scheduled_hook('sit_core_tick');
if ($role=get_role('administrator')) { $role->remove_cap('manage_sit_enquiries'); }
