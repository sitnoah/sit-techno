<?php
// Uses the official WordPress distribution's real hooks, wpautop and shortcodes.
// Supply its root through SIT_WP_TEST_ROOT; no database or live site is touched.
$wp = getenv('SIT_WP_TEST_ROOT');
if (!$wp || !is_file($wp.'/wp-includes/formatting.php')) { fwrite(STDERR,"Set SIT_WP_TEST_ROOT to an extracted WordPress distribution.\n"); exit(1); }
define('ABSPATH',rtrim($wp,'/').'/'); define('WPINC','wp-includes');
require ABSPATH.'wp-includes/plugin.php';
require ABSPATH.'wp-includes/formatting.php';
require ABSPATH.'wp-includes/shortcodes.php';
function get_option($name,$default=false){return $name==='blog_charset'?'UTF-8':$default;}
function wp_load_alloptions(){return [];}
function _x($text,...$args){return $text;}
function __($text,...$args){return $text;}
function get_the_ID(){return 9;}
function is_page(){return true;}
function get_post_meta(...$args){global $marker;return $marker;}
function get_post_field(...$args){global $content;return $content;}
function post_password_required(){global $password;return $password;}
function the_content(){global $content,$throw; if($throw){throw new RuntimeException('render fault');} echo apply_filters('the_content',post_password_required()?'Password required':$content);}
$theme=dirname(__DIR__).'/wordpress/themes/sit-technology';
function sit_theme_pages(){global $theme;static $pages;return $pages??=json_decode(file_get_contents($theme.'/content/pages.json'),true);}
function sit_theme_resolve($html){return $html;}
require $theme.'/includes/rendering.php';
add_shortcode('sit_component',function($atts){global $theme;return file_get_contents($theme.'/content/components/'.$atts['name'].'.html');});
add_filter('the_content','wpautop');add_filter('the_content','shortcode_unautop');add_filter('the_content','do_shortcode',11);
$count=0;$password=false;$throw=false;
function check_render($yes,$why){global $count;$count++;if(!$yes){throw new RuntimeException($why);}}
function render_current(){ob_start();sit_theme_page_content();return ob_get_clean();}
$raw=sit_theme_pages()['']['html'];
check_render(str_contains(wpautop($raw),'<p><span aria-hidden="true">↗</span>'),'Reproduce live wpautop arrow/grid corruption');
foreach(sit_theme_pages() as $slug=>$p){
 $marker=$slug?:'home';$content=$p['html'];
 if($slug==='start-a-project'){$content=preg_replace('/<section class="wrap project-layout">.*$/s','',$content);}
 $expected=do_shortcode($p['html']);$actual=render_current();
 check_render($actual===$expected,'Legacy raw page matches packaged source: '.$marker);
 check_render(has_filter('the_content','wpautop')===10,'Normal formatting restored after '.$marker);
 $content='[sit_page name="'.$marker.'"]';
 check_render(render_current()===$expected,'Managed page shortcode matches source: '.$marker);
}
$marker='home';$content=str_replace('Build better','Our edited heading',$raw);
check_render(str_contains(render_current(),'Our edited heading'),'Preserve existing editorial copy');
$marker='';$content="First paragraph.\n\nSecond paragraph.";
check_render(render_current()===wpautop($content),'Unmanaged pages keep standard paragraph formatting');
$marker='start-a-project';$password=true;
check_render(!str_contains(render_current(),'project-form'),'Password-protected raw form not appended');
$content='[sit_page name="start-a-project"]';
check_render(!str_contains(render_current(),'project-form'),'Password-protected managed form not exposed');
$password=false;
check_render(do_shortcode('[sit_page name="../../private"]')==='','Reject unknown packaged page path');
$marker='home';$throw=true;
try{render_current();}catch(RuntimeException $e){ob_end_clean();}
check_render(has_filter('the_content','wpautop')===10,'Restore filters after render exception');
echo "PASS: $count rendering checks using WordPress's real formatting, hook and shortcode functions.\n";
