<?php
if (!defined('ABSPATH')) { exit; }
add_action('rest_api_init', function () {
    register_rest_route('sit/v1', '/enquiry-token', array('methods'=>'GET', 'permission_callback'=>'__return_true', 'callback'=>'sit_core_token'));
    register_rest_route('sit/v1', '/enquiries', array('methods'=>'POST', 'permission_callback'=>'sit_core_intake_permission', 'callback'=>'sit_core_submit'));
});
function sit_core_error($code, $message, $status = 400) { return new WP_Error($code, $message, array('status'=>$status)); }
function sit_core_token() {
    if (!sit_core_ready()) { return sit_core_error('sit_closed', 'Online enquiries are not open yet.', 503); }
    $value = time() . '.' . wp_generate_password(24, false, false);
    $token = $value . '.' . hash_hmac('sha256', $value, wp_salt('nonce'));
    return new WP_REST_Response(array('token'=>$token), 200, array('Cache-Control'=>'no-store, private', 'Vary'=>'Origin'));
}
function sit_core_origin($url) {
    $p = wp_parse_url($url);
    if (!$p || empty($p['scheme']) || empty($p['host'])) { return ''; }
    $scheme = strtolower($p['scheme']); $port = $p['port'] ?? ($scheme === 'https' ? 443 : 80);
    return $scheme . '://' . strtolower($p['host']) . ':' . $port;
}
function sit_core_intake_permission($request) {
    if (!sit_core_ready()) { return sit_core_error('sit_closed', 'Online enquiries are not open yet.', 503); }
    $origin = sit_core_origin($request->get_header('origin'));
    if (!$origin || !hash_equals(sit_core_origin(home_url()), $origin)) { return sit_core_error('sit_origin', 'Please send this enquiry from our website.', 403); }
    $parts = explode('.', (string) $request->get_header('x-sit-token'));
    if (count($parts)!==3 || !ctype_digit($parts[0]) || (int)$parts[0] > time()+30 || time()-(int)$parts[0] > 1800 || !hash_equals(hash_hmac('sha256', $parts[0].'.'.$parts[1], wp_salt('nonce')), $parts[2])) { return sit_core_error('sit_token', 'Please try again to refresh this form.', 403); }
    return true;
}
function sit_core_text_length($value) { return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value); }
function sit_core_validate($data) {
    if (!is_array($data)) { return sit_core_error('sit_invalid', 'Please complete the required fields.'); }
    $limits = array('name'=>120,'email'=>190,'company'=>190,'brief'=>3000,'goal'=>40,'service'=>80,'budget'=>40,'timeline'=>40,'consent'=>1,'website'=>190);
    foreach ($limits as $field=>$max) {
        if (!isset($data[$field]) || !is_string($data[$field]) || strlen($data[$field]) > $max*4) { return sit_core_error('sit_invalid', 'Please check the ' . $field . ' field.'); }
        $data[$field] = trim($data[$field]);
        $length = function_exists('mb_strlen') ? mb_strlen($data[$field], 'UTF-8') : strlen($data[$field]);
        if ($length > $max) { return sit_core_error('sit_long', 'Please shorten the ' . $field . ' field.'); }
    }
    if ($data['website'] !== '') { return sit_core_error('sit_invalid', 'This enquiry could not be accepted.'); }
    if (!$data['name'] || !$data['company'] || !is_email($data['email']) || sit_core_text_length($data['brief'])<20 || $data['consent']!=='1') { return sit_core_error('sit_invalid', 'Please complete your details, a project overview and the consent field.'); }
    $allowed = array(
        'goal'=>array('modernise','build','team','explore'),
        'service'=>array('explore','technology-strategy','software-engineering','ai-and-automation','data-and-analytics','cloud-and-devops','dedicated-teams','enterprise-solutions','quality-and-security','managed-services'),
        'budget'=>array('discuss','under-10k','10-25k','25-50k','50-100k','100k-plus'),
        'timeline'=>array('flexible','soon','1-3-months','3-plus-months'),
    );
    foreach ($allowed as $key=>$values) { if (!in_array($data[$key],$values,true)) { return sit_core_error('sit_option', 'Please choose a valid ' . $key . '.'); } }
    $clean = array();
    foreach ($limits as $field=>$max) { if (!in_array($field,array('consent','website'),true)) { $clean[$field] = $field==='brief' ? sanitize_textarea_field($data[$field]) : sanitize_text_field($data[$field]); } }
    if (sit_core_text_length($clean['brief']) < 20 || !$clean['name'] || !$clean['company']) { return sit_core_error('sit_invalid', 'Please use plain text to describe your project and organisation.'); }
    $clean['email'] = strtolower(sanitize_email($clean['email']));
    return $clean;
}
function sit_core_rate_limit() {
    global $wpdb;
    // Trust the connection address only. A reverse proxy must be configured by the hosting operator.
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $bucket = hash_hmac('sha256', $ip . ':' . floor(time()/900), wp_salt('auth'));
    $table = sit_core_table('rates');
    $ok = $wpdb->query($wpdb->prepare("INSERT INTO $table (bucket,hits,expires_at) VALUES (%s,1,%s) ON DUPLICATE KEY UPDATE hits=hits+1", $bucket, gmdate('Y-m-d H:i:s',time()+1800)));
    if ($ok === false) { return false; }
    return (int)$wpdb->get_var($wpdb->prepare("SELECT hits FROM $table WHERE bucket=%s",$bucket)) <= 8;
}
function sit_core_duplicate($key, $hash) {
    global $wpdb; $table = sit_core_table('enquiries');
    $row = $wpdb->get_row($wpdb->prepare("SELECT reference,payload_hash FROM $table WHERE idempotency_hash=%s",$key));
    if (!$row) { return null; }
    if (!hash_equals($row->payload_hash,$hash)) { return sit_core_error('sit_conflict','This submission changed. Please start again.',409); }
    return new WP_REST_Response(array('reference'=>$row->reference),200,array('Cache-Control'=>'no-store, private'));
}
function sit_core_submit($request) {
    global $wpdb;
    if (strlen($request->get_body()) > 24000) { return sit_core_error('sit_large','Please shorten your enquiry.',413); }
    if (!sit_core_rate_limit()) { return sit_core_error('sit_rate','Too many attempts. Please try again in 15 minutes.',429); }
    $data = sit_core_validate($request->get_json_params()); if (is_wp_error($data)) { return $data; }
    $key = (string)$request->get_header('idempotency-key');
    if (!preg_match('/^[a-f0-9-]{36}$/i',$key)) { return sit_core_error('sit_key','Please refresh the form and try again.'); }
    $key = hash('sha256',$key); $hash = hash('sha256',wp_json_encode($data));
    $duplicate = sit_core_duplicate($key,$hash); if ($duplicate) { return $duplicate; }
    $now = gmdate('Y-m-d H:i:s');
    $row = array_merge($data,array('reference'=>'SIT-'.strtoupper(bin2hex(random_bytes(8))), 'idempotency_hash'=>$key,'payload_hash'=>$hash,'consent_at'=>$now,'created_at'=>$now,'updated_at'=>$now));
    if ($wpdb->query('START TRANSACTION') === false) { return sit_core_error('sit_storage','Your enquiry could not be saved. Please try again.',503); }
    if ($wpdb->insert(sit_core_table('enquiries'),$row) === false) {
        $wpdb->query('ROLLBACK'); $duplicate=sit_core_duplicate($key,$hash);
        return $duplicate ?: sit_core_error('sit_storage','Your enquiry could not be saved. Please try again.',503);
    }
    $id=(int)$wpdb->insert_id;
    if (sit_core_event($id,'created') === false || $wpdb->insert(sit_core_table('outbox'),array('enquiry_id'=>$id,'available_at'=>$now,'created_at'=>$now)) === false || $wpdb->query('COMMIT') === false) {
        $wpdb->query('ROLLBACK'); return sit_core_error('sit_storage','Your enquiry could not be saved. Please try again.',503);
    }
    return new WP_REST_Response(array('reference'=>$row['reference']),201,array('Cache-Control'=>'no-store, private'));
}
