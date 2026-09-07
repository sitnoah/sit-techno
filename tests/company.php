<?php
// Uses the real validators and renderers with isolated WordPress helpers.
require __DIR__ . '/intake.php';
$before = $count;
define('OBJECT', 'OBJECT');
$location_option = [];
function get_option($name, $default=[]) { global $location_option; return $name === 'sit_core_locations' ? $location_option : $default; }
function esc_html($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function esc_attr($v) { return esc_html($v); }
function esc_url($v) { return esc_html($v); }
$can_publish = true; $existing = []; $insertions = []; $meta = [];
function current_user_can($cap, ...$args) { global $can_publish; return $can_publish; }
function get_page_by_path($path, ...$args) { global $existing; return $existing[$path] ?? null; }
function sit_theme_pages() { return json_decode(file_get_contents(__DIR__ . '/../wordpress/themes/sit-technology/content/pages.json'), true); }
function wp_insert_post($post, $error=false) { global $existing, $insertions; $id = count($insertions)+100; $insertions[]=$post; $existing[$post['post_name']]=(object)($post+['ID'=>$id]); return $id; }
function update_post_meta($id, $key, $value) { global $meta; $meta[$id][$key]=$value; }
require __DIR__.'/../wordpress/plugins/sit-technology-core/includes/locations.php';
require __DIR__.'/../wordpress/themes/sit-technology/includes/company.php';
$base = $valid + ['request_type'=>'enquiry'];
check(sit_core_validate($base)['details'] === '{}', 'Old typed enquiry remains byte-compatible');
foreach (['uk','liberia','cote-divoire','any'] as $region) {
    $clean = sit_core_validate($base+['contact_region'=>$region,'contact_topic'=>'procurement']);
    check(!is_wp_error($clean), 'Accept supported location');
    check(sit_core_detail_values((object)$clean)['Enquiry topic'] === 'Procurement / NDA', 'Desk and privacy export include topic');
    check(sit_core_validate(array_reverse($base+['contact_region'=>$region,'contact_topic'=>'procurement'],true)) === $clean, 'Stable detail order');
}
foreach (['contact_region'=>['nowhere',[],null], 'contact_topic'=>['admin',[],null]] as $field=>$values) {
    foreach ($values as $value) check(is_wp_error(sit_core_validate($base+[$field=>$value])), 'Reject malformed contact context');
}
check(is_wp_error(sit_core_validate($valid+['contact_region'=>'uk'])), 'Untyped client cannot inject a region');
$draft = ['address'=>"Example business address\nSample town",'email'=>'office@example.com','phone'=>'+44 20 0000 0000','hours'=>'Weekdays by arrangement','visiting'=>'Contact us first.','kind'=>'office','confirmed'=>'0','directions'=>'1'];
$location_option = sit_core_validate_locations(['uk'=>$draft]);
check(!is_wp_error($location_option), 'Accept draft location');
check(sit_core_public_locations()['uk'] === ['confirmed'=>false], 'No draft address, email or phone is exposed');
check(!str_contains(sit_theme_office_details('uk'),'example.com'), 'Pending renderer excludes draft data');
check(str_contains(sit_theme_office_details('uk'),'awaiting confirmation'), 'Pending renderer tells visitor details are unconfirmed');
foreach ([null,['elsewhere'=>$draft],['uk'=>['email'=>[]]],['uk'=>['email'=>'bad']],['uk'=>['phone'=>'javascript:alert(1)']],['uk'=>['phone'=>'-----']],['uk'=>['address'=>str_repeat('x',501)]],['uk'=>['kind'=>'home']],['uk'=>['confirmed'=>[]]],['uk'=>['confirmed'=>'1']],['uk'=>['map_url'=>'https://attacker.example']]] as $bad) {
    check(is_wp_error(sit_core_validate_locations($bad)), 'Reject invalid location settings');
}
$draft['confirmed']='1'; $draft['address']="Example office & co.\n\"Quoted street\"";
$location_option = sit_core_validate_locations(['uk'=>$draft]);
$html=sit_theme_office_details('uk');
check(str_contains($html,'&amp; co.') && str_contains($html,'&quot;Quoted street&quot;'), 'Escape address text and copy attribute');
check(str_contains($html,'mailto:office@example.com') && str_contains($html,'tel:+442000000000'), 'Public email and telephone links');
check(str_contains($html,'https://www.google.com/maps/search/?api=1'), 'Maps is a click-through link');
check(!str_contains($html,'iframe'), 'No embedded map loads automatically');
$draft['kind']='registered'; $location_option=sit_core_validate_locations(['uk'=>$draft]);
$html=sit_theme_office_details('uk');
check(!str_contains($html,'google.com') && str_contains($html,'not an invitation to visit'), 'Registered addresses are not treated as visiting offices');
check(sit_theme_office_details('../elsewhere') === '', 'Unknown office marker rejected');
$can_publish=false;
check(is_wp_error(sit_theme_add_company_pages()) && !$insertions, 'Unauthorised setup never creates pages');
$can_publish=true; $existing=['contact'=>(object)['ID'=>2,'post_content'=>'My edited contact page']];
$result=sit_theme_add_company_pages();
check($result === ['contact'=>'preserved','team'=>'created'], 'Create only the missing company page');
check($existing['contact']->post_content==='My edited contact page', 'Preserve custom existing copy');
check(count($insertions)===1 && $insertions[0]['post_name']==='team' && $insertions[0]['post_content']==='[sit_page name="team"]', 'Published page follows future packaged updates');
check(sit_theme_add_company_pages()===['contact'=>'preserved','team'=>'preserved'] && count($insertions)===1, 'Repeated setup does not duplicate or overwrite pages');
echo 'PASS: '.($count-$before)." contact details, public office rendering and targeted page setup checks.\n";
