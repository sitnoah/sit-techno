<?php
// Test the actual plugin bootstrap/readiness without a database or email provider.
define('ABSPATH',__DIR__.'/');
$settings=['enabled'=>true,'privacy_reviewed'=>true,'notify_to'=>'sample@example.test','retention_days'=>90];$schema='0.3.0';
function add_action(...$args){} function add_filter(...$args){}
function register_activation_hook(...$args){} function register_deactivation_hook(...$args){}
function get_option($key,$default=false){global $settings,$schema;return $key==='sit_core_settings'?$settings:($key==='sit_core_schema'?$schema:$default);}
function is_email($email){return filter_var($email,FILTER_VALIDATE_EMAIL)!==false;}
require dirname(__DIR__).'/wordpress/plugins/sit-technology-core/sit-technology-core.php';
$n=0;function verify_ready($value,$label){global $n;$n++;if(!$value){throw new RuntimeException($label);}}
verify_ready(SIT_CORE_VERSION!==SIT_CORE_SCHEMA_VERSION,'Patch version differs from schema version');
verify_ready(sit_core_ready(),'Configured Core 0.3.0 schema stays ready after patch upgrade');
foreach(['enabled','privacy_reviewed','notify_to','retention_days'] as $field){$saved=$settings[$field];unset($settings[$field]);verify_ready(!sit_core_ready() && count(sit_core_readiness_issues())===1,'Identify missing '.$field);$settings[$field]=$saved;}
$settings['retention_days']=3651;verify_ready(!sit_core_ready(),'Reject out-of-range retention');$settings['retention_days']=90;
$schema='0.2.0';verify_ready(isset(sit_core_readiness_issues()['database']),'Older schema requires explicit upgrade');
$schema='0.3.0';verify_ready(sit_core_ready(),'Restored schema resumes configured intake');
$settings=[];verify_ready(count(sit_core_readiness_issues())===4,'Fresh installation stays closed with specific setup guidance');
echo "PASS: $n plugin readiness and patch-upgrade checks.\n";
