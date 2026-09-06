<?php
if (!defined('ABSPATH')) { exit; }
add_action('admin_menu', function () {
    add_menu_page('SIT Enquiries','SIT Enquiries','manage_sit_enquiries','sit-enquiries','sit_core_admin','dashicons-format-chat',26);
    add_submenu_page('sit-enquiries','SIT Settings','Settings','manage_options','sit-settings','sit_core_settings_screen');
});
function sit_core_statuses() { return array('new'=>'New','reviewing'=>'Reviewing','proposal'=>'Proposal','closed'=>'Closed'); }
function sit_core_admin() {
    if (!current_user_can('manage_sit_enquiries')) { return; }
    global $wpdb; $table=sit_core_table('enquiries'); $outbox=sit_core_table('outbox');
    echo '<div class="wrap"><h1>SIT Enquiries</h1><p>Private project enquiries. Email transport acceptance is not proof of delivery.</p>';
    if (!sit_core_ready()) { echo '<div class="notice notice-warning"><p>Online enquiries are disabled. Configure the site in SIT Enquiries → Settings.</p></div>'; }
    $id=absint($_GET['enquiry'] ?? 0);
    if ($id) {
        $row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id));
        if (!$row) { echo '<p>Enquiry not found.</p></div>'; return; }
        echo '<p><a href="'.esc_url(admin_url('admin.php?page=sit-enquiries')).'">← All enquiries</a></p><h2>'.esc_html($row->reference).'</h2><table class="widefat striped"><tbody>';
        foreach(array('name','email','company','goal','service','brief','budget','timeline','consent_at','created_at') as $field){echo '<tr><th style="width:180px">'.esc_html(ucwords(str_replace('_',' ',$field))).'</th><td style="white-space:pre-wrap;overflow-wrap:anywhere">'.esc_html($row->$field).'</td></tr>';}
        echo '</tbody></table><h2>Manage enquiry</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">'; wp_nonce_field('sit_update_'.$id);
        echo '<input type="hidden" name="action" value="sit_update"><input type="hidden" name="id" value="'.esc_attr($id).'"><input type="hidden" name="previous_status" value="'.esc_attr($row->status).'"><input type="hidden" name="previous_assigned" value="'.esc_attr($row->assigned_to).'"><p><label>Status <select name="status">';
        foreach(sit_core_statuses() as $value=>$label){echo '<option value="'.esc_attr($value).'" '.selected($value,$row->status,false).'>'.esc_html($label).'</option>';}
        echo '</select></label></p><p><label>Assigned to <select name="assigned_to"><option value="0">Unassigned</option>';
        foreach(get_users(array('capability'=>'manage_sit_enquiries','fields'=>array('ID','display_name'))) as $user){echo '<option value="'.esc_attr($user->ID).'" '.selected($user->ID,$row->assigned_to,false).'>'.esc_html($user->display_name).'</option>';}
        echo '</select></label></p><button class="button button-primary">Save changes</button></form><h2>Activity</h2><ul>';
        $events=sit_core_table('events');
        foreach($wpdb->get_results($wpdb->prepare("SELECT event,actor_id,created_at FROM $events WHERE enquiry_id=%d ORDER BY id DESC LIMIT 50",$id)) as $e){echo '<li>'.esc_html($e->created_at.' UTC — '.$e->event.' (user '.$e->actor_id.')').'</li>';}
        echo '</ul></div>';return;
    }
    $status=sanitize_key($_GET['status'] ?? ''); $page=max(1,absint($_GET['paged'] ?? 1)); $where='1=1';
    if(array_key_exists($status,sit_core_statuses())){$where=$wpdb->prepare('e.status=%s',$status);}
    echo '<form method="get"><input type="hidden" name="page" value="sit-enquiries"><p><label>Filter <select name="status"><option value="">All statuses</option>';
    foreach(sit_core_statuses() as $value=>$label){echo '<option value="'.esc_attr($value).'" '.selected($value,$status,false).'>'.esc_html($label).'</option>';}
    echo '</select></label> <button class="button">Apply</button></p></form><table class="widefat striped"><thead><tr><th>Reference</th><th>Organisation</th><th>Status</th><th>Received (UTC)</th><th>Notification</th></tr></thead><tbody>';
    $rows=$wpdb->get_results($wpdb->prepare("SELECT e.id,e.reference,e.company,e.status,e.created_at,o.state FROM $table e LEFT JOIN $outbox o ON o.enquiry_id=e.id WHERE $where ORDER BY e.id DESC LIMIT 25 OFFSET %d",($page-1)*25));
    foreach($rows as $r){echo '<tr><td><a href="'.esc_url(add_query_arg(array('page'=>'sit-enquiries','enquiry'=>$r->id),admin_url('admin.php'))).'">'.esc_html($r->reference).'</a></td><td>'.esc_html($r->company).'</td><td>'.esc_html(sit_core_statuses()[$r->status] ?? $r->status).'</td><td>'.esc_html($r->created_at).'</td><td>'.esc_html(str_replace('_',' ',$r->state ?? 'unknown')).'</td></tr>';}
    if(!$rows){echo '<tr><td colspan="5">No enquiries found.</td></tr>';}
    echo '</tbody></table><p>';
    $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM $table e WHERE $where");
    echo wp_kses_post(paginate_links(array('base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$page,'total'=>max(1,(int)ceil($total/25)))));
    echo '</p></div>';
}
add_action('admin_post_sit_update',function(){
    if(!current_user_can('manage_sit_enquiries')){wp_die('Forbidden','',array('response'=>403));}
    $id=absint($_POST['id'] ?? 0);check_admin_referer('sit_update_'.$id);
    $status=sanitize_key($_POST['status'] ?? '');$assigned=absint($_POST['assigned_to'] ?? 0);
    if(!array_key_exists($status,sit_core_statuses()) || ($assigned && !user_can($assigned,'manage_sit_enquiries'))){wp_die('Invalid status or assignee.');}
    global $wpdb;$table=sit_core_table('enquiries');$wpdb->query('START TRANSACTION');
    $row=$wpdb->get_row($wpdb->prepare("SELECT status,assigned_to FROM $table WHERE id=%d FOR UPDATE",$id));
    if(!$row || $row->status!==sanitize_key($_POST['previous_status'] ?? '') || (int)$row->assigned_to!==absint($_POST['previous_assigned'] ?? 0)){$wpdb->query('ROLLBACK');wp_die('This enquiry changed. Reload it before saving.');}
    $result=$wpdb->update($table,array('status'=>$status,'assigned_to'=>$assigned,'updated_at'=>gmdate('Y-m-d H:i:s')),array('id'=>$id));
    if($result===false || sit_core_event($id,'status:'.$row->status.'→'.$status.'; assignee:'.$row->assigned_to.'→'.$assigned,get_current_user_id())===false || $wpdb->query('COMMIT')===false){$wpdb->query('ROLLBACK');wp_die('Changes could not be saved.');}
    wp_safe_redirect(add_query_arg(array('page'=>'sit-enquiries','enquiry'=>$id),admin_url('admin.php')));exit;
});
function sit_core_settings_screen(){
    if(!current_user_can('manage_options')){return;}$s=get_option('sit_core_settings',array());
    echo '<div class="wrap"><h1>SIT Core settings</h1><p>Configure these after reviewing the public privacy notice, hosting, access permissions and email transport. Enquiries are stored in private database tables. No customer data is sent to a CRM by this release.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('sit_settings');
    echo '<input type="hidden" name="action" value="sit_settings"><table class="form-table"><tr><th><label for="notify_to">Team notification email</label></th><td><input id="notify_to" name="notify_to" type="email" class="regular-text" required value="'.esc_attr($s['notify_to'] ?? '').'"><p class="description">Use an authorised SIT inbox. Notifications contain only the reference and a link to the private staff inbox.</p></td></tr><tr><th><label for="retention_days">Enquiry retention (days)</label></th><td><input id="retention_days" name="retention_days" type="number" min="1" max="3650" required value="'.esc_attr($s['retention_days'] ?? 90).'"><p class="description">All enquiries, associated activity and outbox records older than this are deleted, regardless of status. Export any required business records through your agreed process first.</p></td></tr><tr><th>Privacy notice</th><td><label><input type="checkbox" name="privacy_reviewed" value="1" '.checked(!empty($s['privacy_reviewed']),true,false).'> I have replaced the starter privacy copy with the reviewed notice, verified contact details and retention policy.</label></td></tr><tr><th>Live enquiries</th><td><label><input type="checkbox" name="enabled" value="1" '.checked(!empty($s['enabled']),true,false).'> Enable the public enquiry endpoint.</label></td></tr></table><button class="button button-primary">Save settings</button></form><h2>Operations</h2><p>Run WordPress scheduled events through a real scheduler every five minutes. Notification failures retry up to five times and then appear as failed in the inbox. Configure and verify your mail provider separately; wp_mail acceptance is not delivery confirmation.</p><p>CRM synchronisation, file uploads, client accounts and recruitment submissions are planned extensions, not active features.</p></div>';
}
add_action('admin_post_sit_settings',function(){
    if(!current_user_can('manage_options')){wp_die('Forbidden','',array('response'=>403));}check_admin_referer('sit_settings');
    $email=sanitize_email(wp_unslash($_POST['notify_to'] ?? ''));$days=absint($_POST['retention_days'] ?? 0);
    if(!is_email($email) || $days<1 || $days>3650){wp_die('Enter a valid notification email and retention period.');}
    update_option('sit_core_settings',array('notify_to'=>$email,'retention_days'=>$days,'privacy_reviewed'=>!empty($_POST['privacy_reviewed']),'enabled'=>!empty($_POST['enabled']) && !empty($_POST['privacy_reviewed'])));
    wp_safe_redirect(admin_url('admin.php?page=sit-settings&saved=1'));exit;
});
