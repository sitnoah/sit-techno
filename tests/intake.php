<?php
// Isolated boundary tests against the real intake functions. WordPress helpers
// are narrowly stubbed; database transactions require the documented staging test.
define('ABSPATH',__DIR__);
function add_action(...$args){}
function wp_salt($type){return 'test-only-fixed-salt';}
function home_url(){return 'https://sit.example';}
function wp_parse_url($url){return parse_url($url);}
function sit_core_ready(){return true;}
function is_email($v){return filter_var($v,FILTER_VALIDATE_EMAIL)!==false;}
function sanitize_email($v){return filter_var($v,FILTER_SANITIZE_EMAIL);}
function sanitize_text_field($v){return trim(strip_tags($v));}
function sanitize_textarea_field($v){return trim(strip_tags($v));}
class WP_Error {public function __construct(public $code,public $message,public $data=[]) {}}
function is_wp_error($v){return $v instanceof WP_Error;}
require __DIR__.'/../wordpress/plugins/sit-technology-core/includes/intake.php';
$count=0;
function check($condition,$message){global $count;$count++;if(!$condition){throw new RuntimeException($message);}}
$valid=['name'=>'Sample Person','email'=>'SAMPLE@example.com','company'=>'Example Company','goal'=>'build','service'=>'software-engineering','brief'=>'Build an accessible project coordination tool.','budget'=>'discuss','timeline'=>'flexible','consent'=>'1','website'=>''];
$result=sit_core_validate($valid);check(!is_wp_error($result),'Valid business enquiry should be accepted');check($result['email']==='sample@example.com','Email should be normalised');check(!isset($result['consent'],$result['website']),'Submission-only fields must not enter the stored payload');
foreach(['email'=>'invalid','consent'=>'0','goal'=>'admin','service'=>'not-a-service','budget'=>'free','timeline'=>'yesterday','brief'=>'short','website'=>'spam','name'=>['injected'],'company'=>''] as $key=>$value){$data=$valid;$data[$key]=$value;check(is_wp_error(sit_core_validate($data)),'Reject invalid '.$key);}
$data=$valid;$data['brief']=str_repeat('a',3001);check(is_wp_error(sit_core_validate($data)),'Reject oversized overview');
$data=$valid;unset($data['email']);check(is_wp_error(sit_core_validate($data)),'Reject missing email');
check(is_wp_error(sit_core_validate(null)),'Reject absent JSON body');
$data=$valid;$data['brief']='<b>Build a practical tool for our team.</b>';$data['name']='<b>Sample</b>';$r=sit_core_validate($data);check(!str_contains($r['brief'],'<')&&$r['name']==='Sample','Store plain text');
check(sit_core_origin('https://SIT.example:443/path')===sit_core_origin(home_url()),'Canonical origin handles default ports');
check(sit_core_origin('https://sit.example.evil.test')!==sit_core_origin(home_url()),'Reject deceptive hostname');
class Request {function __construct(public $headers){} function get_header($key){return $this->headers[$key]??'';}}
function token_at($timestamp){$value=$timestamp.'.test-random';return $value.'.'.hash_hmac('sha256',$value,wp_salt('nonce'));}
$headers=['origin'=>'https://sit.example','x-sit-token'=>token_at(time())];check(sit_core_intake_permission(new Request($headers))===true,'Same-origin valid form allowed');
foreach(['https://attacker.example','null','http://sit.example','https://sit.example:444',''] as $origin){$h=$headers;$h['origin']=$origin;check(is_wp_error(sit_core_intake_permission(new Request($h))),'Reject foreign or absent origin');}
foreach([token_at(time()-1900),token_at(time()+60),'broken',$headers['x-sit-token'].'x'] as $token){$h=$headers;$h['x-sit-token']=$token;check(is_wp_error(sit_core_intake_permission(new Request($h))),'Reject stale, future or tampered token');}
echo "PASS: $count intake validation and origin/token boundary checks.\n";
