<?php
if (!defined('ABSPATH')) { exit; }
add_action('admin_menu', function () {
    add_menu_page('SIT Requests','SIT Requests','manage_sit_enquiries','sit-enquiries','sit_core_admin','dashicons-format-chat',26);
    add_submenu_page('sit-enquiries','SIT Settings','Settings','manage_options','sit-settings','sit_core_settings_screen');
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_sit-enquiries') {
        wp_enqueue_style('sit-request-desk', plugins_url('assets/admin.css', dirname(__DIR__) . '/sit-technology-core.php'), array(), SIT_CORE_VERSION);
    }
});
function sit_core_input($source, $key) {
    return isset($source[$key]) && is_string($source[$key]) ? wp_unslash($source[$key]) : '';
}
function sit_core_select($name, $label, $options, $value) {
    echo '<label>' . esc_html($label) . ' <select name="' . esc_attr($name) . '">';
    foreach ($options as $key=>$text) { echo '<option value="' . esc_attr($key) . '" ' . selected((string)$key,(string)$value,false) . '>' . esc_html($text) . '</option>'; }
    echo '</select></label>';
}
function sit_core_staff() {
    $staff = array(0=>'Unassigned');
    foreach (get_users(array('capability'=>'manage_sit_enquiries','fields'=>array('ID','display_name'))) as $user) { $staff[$user->ID] = $user->display_name; }
    return $staff;
}
function sit_core_admin() {
    if (!current_user_can('manage_sit_enquiries')) { return; }
    echo '<div class="wrap sit-desk"><div class="sit-desk-heading"><p class="sit-kicker">SIT CONSULTANCY / WORKSPACE</p><h1>Good conversations.<br>Clear next steps.</h1><p>One place to review enquiries, consultations, projects and team requests.</p></div>';
    if (get_option('sit_core_schema') !== SIT_CORE_SCHEMA_VERSION) { echo '<p>The request database needs an update by a site administrator before this desk can open.</p></div>'; return; }
    if (!sit_core_ready()) { echo '<div class="notice notice-warning inline"><p>Online requests are paused. An administrator can configure intake in SIT Requests → Settings.</p></div>'; }
    global $wpdb;
    $table = sit_core_table('enquiries');
    $id = absint(sit_core_input($_GET,'enquiry'));
    if ($id) { sit_core_admin_detail($id); echo '</div>'; return; }
    $counts = $wpdb->get_results("SELECT status,COUNT(*) AS total FROM $table GROUP BY status",OBJECT_K);
    echo '<div class="sit-metrics" aria-label="All requests by status">';
    foreach (sit_core_statuses() as $key=>$label) { echo '<a href="' . esc_url(add_query_arg(array('page'=>'sit-enquiries','status'=>$key),admin_url('admin.php'))) . '"><span>' . esc_html($label) . '</span><strong>' . esc_html($counts[$key]->total ?? 0) . '</strong></a>'; }
    echo '</div>';
    $status = sanitize_key(sit_core_input($_GET,'status'));
    $type = sanitize_key(sit_core_input($_GET,'request_type'));
    $assignment = sanitize_key(sit_core_input($_GET,'assignment'));
    $search = sanitize_text_field(substr(sit_core_input($_GET,'q'),0,80));
    $page = max(1,absint(sit_core_input($_GET,'paged')));
    $where = array('1=1');
    if (isset(sit_core_statuses()[$status])) { $where[] = $wpdb->prepare('e.status=%s',$status); }
    if (isset(sit_core_request_types()[$type])) { $where[] = $wpdb->prepare('e.request_type=%s',$type); }
    if ($assignment === 'mine') { $where[] = $wpdb->prepare('e.assigned_to=%d',get_current_user_id()); }
    elseif ($assignment === 'unassigned') { $where[] = 'e.assigned_to=0'; }
    if ($search !== '') { $like = '%' . $wpdb->esc_like($search) . '%'; $where[] = $wpdb->prepare('(e.reference LIKE %s OR e.company LIKE %s)',$like,$like); }
    $where = implode(' AND ',$where);
    echo '<form method="get" class="sit-filters"><input type="hidden" name="page" value="sit-enquiries">';
    sit_core_select('request_type','Request',array(''=>'All types') + sit_core_request_types(),$type);
    sit_core_select('status','Status',array(''=>'All statuses') + sit_core_statuses(),$status);
    sit_core_select('assignment','Owner',array(''=>'Anyone','mine'=>'Assigned to me','unassigned'=>'Unassigned'),$assignment);
    echo '<label>Reference or organisation <input type="search" name="q" maxlength="80" value="' . esc_attr($search) . '"></label><button class="button button-primary">Apply filters</button><a href="' . esc_url(admin_url('admin.php?page=sit-enquiries')) . '">Reset</a></form>';
    $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table e WHERE $where");
    $page = min($page,max(1,(int)ceil($total/25)));
    echo '<p>' . esc_html($total) . ' matching request' . ($total === 1 ? '' : 's') . ' · All received times are UTC</p><div class="sit-table-scroll"><table class="widefat striped"><thead><tr><th scope="col">Request</th><th scope="col">Organisation</th><th scope="col">Status</th><th scope="col">Owner</th><th scope="col">Received</th><th scope="col">Team notification</th></tr></thead><tbody>';
    $outbox = sit_core_table('outbox');
    $rows = $wpdb->get_results($wpdb->prepare("SELECT e.id,e.reference,e.request_type,e.company,e.status,e.assigned_to,e.created_at,o.state FROM $table e LEFT JOIN $outbox o ON o.enquiry_id=e.id WHERE $where ORDER BY e.id DESC LIMIT 25 OFFSET %d",($page-1)*25));
    $staff = sit_core_staff();
    foreach ($rows as $row) {
        $url = add_query_arg(array('page'=>'sit-enquiries','enquiry'=>$row->id),admin_url('admin.php'));
        echo '<tr><td><a href="' . esc_url($url) . '"><strong>' . esc_html($row->reference) . '</strong></a><br>' . esc_html(sit_core_request_types()[$row->request_type] ?? $row->request_type) . '</td><td>' . esc_html($row->company) . '</td><td><span class="sit-status">' . esc_html(sit_core_statuses()[$row->status] ?? $row->status) . '</span></td><td>' . esc_html($staff[$row->assigned_to] ?? 'Former staff member') . '</td><td>' . esc_html($row->created_at) . '</td><td>' . esc_html(str_replace('_',' ',$row->state ?? 'unknown')) . '</td></tr>';
    }
    if (!$rows) { echo '<tr><td colspan="6"><h2>No requests here yet.</h2><p>Try changing the filters, or return when a new request arrives.</p></td></tr>'; }
    echo '</tbody></table></div><p>' . wp_kses_post(paginate_links(array('base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$page,'total'=>max(1,(int)ceil($total/25))))) . '</p><p class="description">Notification acceptance is recorded by the mail transport; it does not confirm delivery. Staff with request access can review all requests.</p></div>';
}
function sit_core_admin_detail($id) {
    global $wpdb;
    $table = sit_core_table('enquiries');
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id));
    if (!$row) { echo '<p>Request not found.</p>'; return; }
    $staff = sit_core_staff();
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=sit-enquiries')) . '">← All requests</a></p><h2>' . esc_html($row->reference) . '</h2><p>' . esc_html(sit_core_request_types()[$row->request_type] ?? $row->request_type) . '</p><div class="sit-detail"><section><h3>Request details</h3><table class="widefat striped"><tbody>';
    $labels = array('name'=>'Name','email'=>'Email','company'=>'Organisation','goal'=>'Ambition','service'=>'Area of interest','brief'=>'Challenge','budget'=>'Budget (GBP)','timeline'=>'Ideal start','consent_at'=>'Consent received (UTC)','created_at'=>'Received (UTC)');
    foreach ($labels as $field=>$label) {
        $value = $row->$field;
        if (in_array($field,array('goal','service','budget','timeline'),true)) { $value = ucwords(str_replace('-',' ',$value)); }
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td class="sit-value">' . esc_html($value) . '</td></tr>';
    }
    foreach (sit_core_detail_values($row) as $label=>$value) { echo '<tr><th scope="row">' . esc_html($label) . '</th><td class="sit-value">' . esc_html($value ?: 'Not specified') . '</td></tr>'; }
    echo '</tbody></table>';
    if ($row->request_type === 'consultation') { echo '<p class="description">Availability is a preference. Agree the time and meeting details directly before confirming a booking.</p>'; }
    echo '</section><section class="sit-manage"><h3>Move the conversation forward</h3><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('sit_update_'.$id);
    echo '<input type="hidden" name="action" value="sit_update"><input type="hidden" name="id" value="' . esc_attr($id) . '"><input type="hidden" name="version" value="' . esc_attr($row->version) . '">';
    sit_core_select('status','Status',sit_core_allowed_statuses($row->status),$row->status);
    if (!isset($staff[$row->assigned_to])) { echo '<p>Previously assigned to user ' . esc_html($row->assigned_to) . ', who no longer has request access. Choose a new owner.</p>'; }
    sit_core_select('assigned_to','Owner',$staff,$row->assigned_to);
    echo '<p><button class="button button-primary">Save changes</button></p><p class="description">Only valid next statuses are shown. Closed requests can be reopened for review.</p></form><h3>Activity</h3><ol class="sit-activity">';
    $events = sit_core_table('events');
    foreach ($wpdb->get_results($wpdb->prepare("SELECT event,actor_id,created_at FROM $events WHERE enquiry_id=%d ORDER BY id DESC LIMIT 50",$id)) as $event) { echo '<li><strong>' . esc_html($event->event) . '</strong><br>' . esc_html($event->created_at . ' UTC · ' . ($event->actor_id ? ($staff[$event->actor_id] ?? 'User '.$event->actor_id) : 'System')) . '</li>'; }
    echo '</ol><p class="description">Latest 50 events.</p></section></div>';
}
// Kept separate from the HTTP handler to exercise transaction and stale-write failures.
function sit_core_update_request($id,$version,$status,$assigned,$actor) {
    global $wpdb;
    if (!user_can($actor,'manage_sit_enquiries')) { return sit_core_error('sit_forbidden','You cannot manage requests.',403); }
    if (get_option('sit_core_schema') !== SIT_CORE_SCHEMA_VERSION) { return sit_core_error('sit_schema','Update the request database first.',503); }
    if (!isset(sit_core_statuses()[$status]) || ($assigned && !user_can($assigned,'manage_sit_enquiries'))) { return sit_core_error('sit_update','Invalid status or assignee.'); }
    if ($wpdb->query('START TRANSACTION') === false) { return sit_core_error('sit_storage','Changes could not be saved.',503); }
    $table = sit_core_table('enquiries');
    $row = $wpdb->get_row($wpdb->prepare("SELECT status,assigned_to,version FROM $table WHERE id=%d FOR UPDATE",$id));
    if (!sit_core_can_update($row,$version,$status)) { $wpdb->query('ROLLBACK'); return sit_core_error('sit_conflict','This request changed or the transition is unavailable. Reload it before saving.',409); }
    $result = $wpdb->update($table,array('status'=>$status,'assigned_to'=>$assigned,'version'=>$version+1,'updated_at'=>gmdate('Y-m-d H:i:s')),array('id'=>$id));
    if ($result !== 1 || sit_core_event($id,'status:'.$row->status.'→'.$status.'; assignee:'.$row->assigned_to.'→'.$assigned,$actor) === false || $wpdb->query('COMMIT') === false) { $wpdb->query('ROLLBACK'); return sit_core_error('sit_storage','Changes could not be saved.',503); }
    return true;
}
add_action('admin_post_sit_update', function () {
    if (!current_user_can('manage_sit_enquiries')) { wp_die('Forbidden','',array('response'=>403)); }
    $id = absint(sit_core_input($_POST,'id'));
    check_admin_referer('sit_update_'.$id);
    $result = sit_core_update_request($id,absint(sit_core_input($_POST,'version')),sanitize_key(sit_core_input($_POST,'status')),absint(sit_core_input($_POST,'assigned_to')),get_current_user_id());
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()),'',array('response'=>$result->get_error_data()['status'])); }
    wp_safe_redirect(add_query_arg(array('page'=>'sit-enquiries','enquiry'=>$id),admin_url('admin.php')));
    exit;
});

function sit_core_status_panel() {
    if (!current_user_can('manage_options')) { return; }
    $theme = defined('SIT_THEME_VERSION') ? SIT_THEME_VERSION : 'Another theme is active';
    $schema = get_option('sit_core_schema', 'Not installed');
    echo '<h2>Installation status</h2><table class="widefat striped"><tbody><tr><th>Core plugin</th><td>' . esc_html(SIT_CORE_VERSION) . '</td></tr><tr><th>SIT theme</th><td>' . esc_html($theme) . '</td></tr><tr><th>Request database</th><td>' . esc_html($schema) . ' / required ' . esc_html(SIT_CORE_SCHEMA_VERSION) . '</td></tr></tbody></table>';
    $issues = sit_core_readiness_issues();
    if (defined('SIT_THEME_VERSION') && version_compare(SIT_THEME_VERSION, '0.4.1', '<')) { $issues['theme'] = 'Install theme 0.4.1 or newer for the WordPress layout repair.'; }
    if ($issues) {
        echo '<h3>Items to review</h3><ul>';
        foreach ($issues as $issue) { echo '<li>' . esc_html($issue) . '</li>'; }
        echo '</ul>';
    } else { echo '<p>Request intake is configured. Verify the form and mail transport on staging before accepting real enquiries.</p>'; }
}

function sit_core_settings_screen(){
    if(!current_user_can('manage_options')){return;}$s=get_option('sit_core_settings',array());
    echo '<div class="wrap"><h1>SIT Core settings</h1>';
    sit_core_status_panel();
    echo '<p>Configure these after reviewing the public privacy notice, hosting, access permissions and email transport. Requests are stored in private database tables. No customer data is sent to a CRM by this release.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('sit_settings');
    echo '<input type="hidden" name="action" value="sit_settings"><table class="form-table"><tr><th><label for="notify_to">Team notification email</label></th><td><input id="notify_to" name="notify_to" type="email" class="regular-text" required value="'.esc_attr($s['notify_to'] ?? '').'"><p class="description">Use an authorised SIT inbox. Notifications contain only the reference and a link to the private staff inbox.</p></td></tr><tr><th><label for="retention_days">Enquiry retention (days)</label></th><td><input id="retention_days" name="retention_days" type="number" min="1" max="3650" required value="'.esc_attr($s['retention_days'] ?? 90).'"><p class="description">All enquiries, associated activity and outbox records older than this are deleted, regardless of status. Export any required business records through your agreed process first.</p></td></tr><tr><th>Privacy notice</th><td><label><input type="checkbox" name="privacy_reviewed" value="1" '.checked(!empty($s['privacy_reviewed']),true,false).'> I have replaced the starter privacy copy with the reviewed notice, verified contact details and retention policy.</label></td></tr><tr><th>Live enquiries</th><td><label><input type="checkbox" name="enabled" value="1" '.checked(!empty($s['enabled']),true,false).'> Enable the public enquiry endpoint.</label></td></tr></table><button class="button button-primary">Save settings</button></form><h2>Operations</h2><p>Run WordPress scheduled events through a real scheduler every five minutes. Notification failures retry up to five times and then appear as failed in the inbox. Configure and verify your mail provider separately; wp_mail acceptance is not delivery confirmation.</p><p>CRM synchronisation, file uploads, client accounts and recruitment submissions are planned extensions, not active features.</p></div>';
}
add_action('admin_post_sit_settings',function(){
    if(!current_user_can('manage_options')){wp_die('Forbidden','',array('response'=>403));}check_admin_referer('sit_settings');
    $email=sanitize_email(wp_unslash($_POST['notify_to'] ?? ''));$days=absint($_POST['retention_days'] ?? 0);
    if(!is_email($email) || $days<1 || $days>3650){wp_die('Enter a valid notification email and retention period.');}
    update_option('sit_core_settings',array('notify_to'=>$email,'retention_days'=>$days,'privacy_reviewed'=>!empty($_POST['privacy_reviewed']),'enabled'=>!empty($_POST['enabled']) && !empty($_POST['privacy_reviewed'])));
    wp_safe_redirect(admin_url('admin.php?page=sit-settings&saved=1'));exit;
});
