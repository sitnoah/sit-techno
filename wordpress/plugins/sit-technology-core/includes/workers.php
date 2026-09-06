<?php
if (!defined('ABSPATH')) { exit; }
function sit_core_worker(){
    if (get_option('sit_core_schema') !== SIT_CORE_VERSION) { return; }
    global $wpdb;$outbox=sit_core_table('outbox');$requests=sit_core_table('enquiries');$rates=sit_core_table('rates');$now=gmdate('Y-m-d H:i:s');
    $wpdb->query($wpdb->prepare("DELETE FROM $rates WHERE expires_at < %s",$now));
    $s=get_option('sit_core_settings',array());
    if(sit_core_ready()){
        $rows=$wpdb->get_results($wpdb->prepare("SELECT id,enquiry_id FROM $outbox WHERE attempts<5 AND ((state='pending' AND available_at<=%s) OR (state='processing' AND locked_until<%s)) ORDER BY id LIMIT 10",$now,$now));
        foreach($rows as $job){
            $claim=$wpdb->query($wpdb->prepare("UPDATE $outbox SET state='processing',attempts=attempts+1,locked_until=%s WHERE id=%d AND attempts<5 AND ((state='pending' AND available_at<=%s) OR (state='processing' AND locked_until<%s))",gmdate('Y-m-d H:i:s',time()+600),$job->id,$now,$now));
            if($claim!==1){continue;}
            $reference=$wpdb->get_var($wpdb->prepare("SELECT reference FROM $requests WHERE id=%d",$job->enquiry_id));
            if(!$reference){$wpdb->delete($outbox,array('id'=>$job->id));continue;}
            $url=add_query_arg(array('page'=>'sit-enquiries','enquiry'=>$job->enquiry_id),admin_url('admin.php'));
            $accepted=wp_mail($s['notify_to'],'New SIT request '.$reference,"A business request is ready for review.\nReference: ".$reference."\nOpen the private inbox: ".$url."\nSign in with your authorised WordPress account.");
            if($accepted){$wpdb->update($outbox,array('state'=>'accepted_by_transport','locked_until'=>null,'last_error'=>''),array('id'=>$job->id));}
            else{$attempts=(int)$wpdb->get_var($wpdb->prepare("SELECT attempts FROM $outbox WHERE id=%d",$job->id));$wpdb->update($outbox,array('state'=>$attempts>=5?'failed':'pending','locked_until'=>null,'available_at'=>gmdate('Y-m-d H:i:s',time()+min(86400,300*(2**$attempts))),'last_error'=>'Mail transport did not accept the notification.'),array('id'=>$job->id));}
        }
        $wpdb->query($wpdb->prepare("UPDATE $outbox SET state='failed',locked_until=NULL,last_error='Worker lease expired after final attempt.' WHERE state='processing' AND attempts>=5 AND locked_until<%s",$now));
    }
    if(!empty($s['privacy_reviewed']) && !empty($s['retention_days'])){
        $cutoff=gmdate('Y-m-d H:i:s',time()-(int)$s['retention_days']*DAY_IN_SECONDS);
        $ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM $requests WHERE created_at<%s ORDER BY id LIMIT 100",$cutoff));
        foreach($ids as $id){sit_core_delete_enquiry((int)$id);}
    }
}
