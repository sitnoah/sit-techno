<?php
if (!defined('ABSPATH')) { exit; }
function sit_core_delete_enquiry($id){
    global $wpdb;$wpdb->query('START TRANSACTION');
    foreach(array('events','outbox','enquiries') as $name){if($wpdb->delete(sit_core_table($name),array($name==='enquiries'?'id':'enquiry_id'=>$id))===false){$wpdb->query('ROLLBACK');return false;}}
    if($wpdb->query('COMMIT')===false){$wpdb->query('ROLLBACK');return false;}return true;
}
add_filter('wp_privacy_personal_data_exporters',function($exporters){$exporters['sit-enquiries']=array('exporter_friendly_name'=>'SIT requests','callback'=>'sit_core_export');return $exporters;});
function sit_core_export($email,$page=1){
    global $wpdb;$table=sit_core_table('enquiries');$rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE email=%s ORDER BY id LIMIT 50 OFFSET %d",strtolower($email),(max(1,(int)$page)-1)*50));$data=array();
    foreach($rows as $row){$fields=array();foreach(array('reference','request_type','name','email','company','goal','service','brief','budget','timeline','consent_at','status','created_at','updated_at') as $name){$fields[]=array('name'=>ucwords(str_replace('_',' ',$name)),'value'=>$row->$name ?? ($name==='request_type'?'enquiry':''));}foreach(sit_core_detail_values($row) as $label=>$value){$fields[]=array('name'=>$label,'value'=>$value);}$data[]=array('group_id'=>'sit-enquiries','group_label'=>'SIT requests','item_id'=>'sit-enquiry-'.$row->id,'data'=>$fields);}
    return array('data'=>$data,'done'=>count($rows)<50);
}
add_filter('wp_privacy_personal_data_erasers',function($erasers){$erasers['sit-enquiries']=array('eraser_friendly_name'=>'SIT requests','callback'=>'sit_core_erase');return $erasers;});
function sit_core_erase($email,$page=1){
    global $wpdb;$table=sit_core_table('enquiries');$ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM $table WHERE email=%s ORDER BY id LIMIT 50",strtolower($email)));$removed=false;$failed=false;
    foreach($ids as $id){if(sit_core_delete_enquiry((int)$id)){$removed=true;}else{$failed=true;}}
    return array('items_removed'=>$removed,'items_retained'=>$failed,'messages'=>$failed?array('Some enquiries could not be erased. Please ask the administrator to retry.'):array(),'done'=>$failed || count($ids)<50);
}
