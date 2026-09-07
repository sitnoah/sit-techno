<?php
// Real request validation/workflow functions; helper and database fault doubles.
// This does not substitute for a real WordPress/InnoDB integration check.
require __DIR__.'/intake.php';
$before = $count;
$expected = ['name'=>'Sample Person','email'=>'sample@example.com','company'=>'Example Company','brief'=>'Build an accessible project coordination tool.','goal'=>'build','service'=>'software-engineering','budget'=>'discuss','timeline'=>'flexible'];
check(wp_json_encode(sit_core_validate($valid)) === wp_json_encode($expected), 'Legacy payload and canonical hash input remain byte-compatible');
$examples = [
    'enquiry'=>[],
    'consultation'=>['contact_format'=>'video','timezone'=>'Europe/London','availability'=>'Weekday afternoons'],
    'software-project'=>['project_stage'=>'prototype','product_type'=>'web'],
    'dedicated-team'=>['roles'=>'Python engineer and data scientist','team_size'=>'2-3','engagement_length'=>'6-plus'],
];
foreach ($examples as $type=>$details) {
    $data = $valid + ['request_type'=>$type] + $details;
    $clean = sit_core_validate($data);
    check(!is_wp_error($clean) && $clean['request_type'] === $type, 'Accept '.$type);
    check(json_decode($clean['details'],true) === $details, 'Store only canonical '.$type.' details');
    check(sit_core_validate(array_reverse($data,true)) === $clean, 'Stable key order for retries');
    foreach (array_keys($details) as $field) { $bad=$data; unset($bad[$field]); check(is_wp_error(sit_core_validate($bad)), 'Reject missing '.$field); }
}
foreach (['status'=>'won','assigned_to'=>'1','version'=>'99','details'=>'{}','actor_id'=>'1'] as $field=>$value) { check(is_wp_error(sit_core_validate($valid+['request_type'=>'enquiry',$field=>$value])), 'Reject forged '.$field); }
check(is_wp_error(sit_core_validate($valid+['request_type'=>'recruitment'])), 'Reject unknown type');
check(is_wp_error(sit_core_validate($valid+['request_type'=>['enquiry']])), 'Reject array type');
check(is_wp_error(sit_core_validate($valid+['request_type'=>null])), 'Reject null type');
check(is_wp_error(sit_core_validate($valid+['request_type'=>'enquiry','roles'=>'Python'])), 'Reject details from another request type');
$data=$valid+['request_type'=>'consultation']+$examples['consultation'];
foreach (['timezone'=>'','availability'=>str_repeat('x',301),'contact_format'=>'sms'] as $field=>$value) { $bad=$data; $bad[$field]=$value; check(is_wp_error(sit_core_validate($bad)), 'Reject invalid '.$field); }
$data['availability']='';check(!is_wp_error(sit_core_validate($data)), 'Availability may be blank');
$data=$valid+['request_type'=>'dedicated-team']+$examples['dedicated-team'];
foreach (['roles'=>'<b></b>','team_size'=>'10000','engagement_length'=>[]] as $field=>$value) { $bad=$data; $bad[$field]=$value; check(is_wp_error(sit_core_validate($bad)), 'Reject malformed team '.$field); }
$data['roles']='<b>Python engineer</b>';$clean=sit_core_validate($data);
check(json_decode($clean['details'],true)['roles']==='Python engineer','Sanitize typed details');
check(sit_core_detail_values((object)$clean)['Roles and skills']==='Python engineer','Privacy export and desk use safe known detail fields');
check(sit_core_detail_values((object)['request_type'=>'enquiry','details'=>'{"password":"hidden"}'])===[], 'Do not expose unexpected stored keys');
check(isset(sit_core_allowed_statuses('closed')['reviewing']), 'Closed request can be reopened');
check(!isset(sit_core_allowed_statuses('new')['won']), 'New request cannot skip review and proposal');
check(!sit_core_can_update((object)['status'=>'reviewing','version'=>3],2,'proposal'), 'Stale version cannot overwrite work');
check(!sit_core_can_update(null,1,'reviewing'), 'Missing record cannot update');

// Optional brief context is additive, bounded and visible only through known fields.
$base = $valid + ['request_type'=>'enquiry'];
foreach (['audience'=>500,'systems'=>600,'integrations'=>600,'outcomes'=>800,'constraints'=>800] as $field=>$max) {
    $data = $base + [$field=>str_repeat('a',$max)];
    $clean = sit_core_validate($data);
    check(!is_wp_error($clean) && json_decode($clean['details'],true)[$field]===str_repeat('a',$max), 'Accept maximum '.$field);
    $data[$field].='a'; check(is_wp_error(sit_core_validate($data)), 'Reject long '.$field);
    foreach ([[],null,42] as $value) { $data[$field]=$value; check(is_wp_error(sit_core_validate($data)), 'Reject non-text '.$field); }
    check(sit_core_validate($base+[$field=>'  '])===sit_core_validate($base), 'Blank context preserves old hash '.$field);
}
$context=['audience'=>'<b>Support staff</b>','outcomes'=>"Reduce duplicate work.\nMeasure handling steps."];
$clean=sit_core_validate($base+$context);
check(sit_core_detail_values((object)$clean)['Intended users']==='Support staff', 'Desk and export include sanitized context');
check(str_contains(sit_core_detail_values((object)$clean)['Desired outcomes'],"\n"), 'Retain multiline outcome context');
check(sit_core_validate(array_reverse($base+$context,true))===$clean,'Context key order is canonical');
check(is_wp_error(sit_core_validate($valid+$context)), 'Untyped legacy payload cannot inject new details');

define('SIT_CORE_VERSION','0.4.0');
define('SIT_CORE_SCHEMA_VERSION','0.3.0');
$schema='0.3.0';
function get_option($name){global $schema;return $name==='sit_core_schema'?$schema:null;}
function user_can($id,$cap){return in_array($id,[1,2],true) && $cap==='manage_sit_enquiries';}
require __DIR__.'/../wordpress/plugins/sit-technology-core/includes/storage.php';
require __DIR__.'/../wordpress/plugins/sit-technology-core/includes/admin.php';
class RequestDB {
    public $prefix='wp_', $fail='', $commands=[], $events=[], $snapshot=null;
    public $row;
    function __construct(){$this->row=(object)['status'=>'new','assigned_to'=>0,'version'=>1];}
    function prepare($sql,...$args){return $sql;}
    function get_row($sql){return $this->row ? clone $this->row : null;}
    function query($sql){
        $this->commands[]=$sql;
        if($this->fail===$sql){return false;}
        if($sql==='START TRANSACTION'){$this->snapshot=[clone $this->row,$this->events];}
        if($sql==='ROLLBACK' && $this->snapshot){[$this->row,$this->events]=$this->snapshot;}
        return 1;
    }
    function update($table,$data,$where){if($this->fail==='update'){return false;}foreach($data as $k=>$v){$this->row->$k=$v;}return 1;}
    function insert($table,$data){if($this->fail==='event'){return false;}$this->events[]=$data;return 1;}
}
$wpdb=new RequestDB();
check(is_wp_error(sit_core_update_request(5,1,'reviewing',1,99)) && !$wpdb->commands,'Unauthorised actor rejected before database access');
check(is_wp_error(sit_core_update_request(5,1,'reviewing',99,1)) && !$wpdb->commands,'Ineligible owner rejected before database access');
$schema='0.1.0';check(is_wp_error(sit_core_update_request(5,1,'reviewing',1,1)) && !$wpdb->commands,'Unmigrated schema blocks writes');$schema='0.3.0';
check(sit_core_update_request(5,1,'reviewing',2,1)===true,'Review and assignment saved');
check($wpdb->row->version===2 && $wpdb->row->assigned_to===2 && count($wpdb->events)===1,'Version and audit change atomically');
check(is_wp_error(sit_core_update_request(5,1,'closed',1,1)) && $wpdb->row->status==='reviewing' && count($wpdb->events)===1,'Second staff save cannot overwrite the first');
foreach (['START TRANSACTION','update','event','COMMIT'] as $fault) {
    $wpdb=new RequestDB();$wpdb->fail=$fault;
    check(is_wp_error(sit_core_update_request(5,1,'reviewing',2,1)),'Report '.$fault.' failure');
    check($wpdb->row->version===1 && $wpdb->row->status==='new' && !$wpdb->events,'No partial state after '.$fault.' failure');
}
$wpdb=new RequestDB();check(is_wp_error(sit_core_update_request(5,1,'won',2,1)) && $wpdb->row->version===1,'Server rejects skipped status');
echo 'PASS: '.($count-$before)." typed request, compatibility and staff workflow checks (database fault doubles).\n";
