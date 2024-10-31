<?php
/*
Plugin Name: Red Prunus
Plugin URI: http://huizhe.name/creations/redprunus
Description: Kill the GFW. Prevent its keyword scanning; unblock facebook/picasa/flickr images.
Version: 1.2
Author: Huizhe Xiao
Author URI: http://huizhe.name/
*/
require_once 'functions.php';

$redprunus_banned_urls = array(
	'https?://lh\d\.ggpht\.com/[^"\']*?\.jpg',
	'https?://www\.youtube\.com/v/[^"\']*',
	'https?://youtube\.com/v/[^"\']*',
	'https?://s\.ytimg\.com/[^"\']*',
	'https?://[^"\']*?.fbcdn.net/[^"\']*?\.jpg',
	'https?://cl\.ly/.*?\.(jpg|png|jpeg|gif)'
	
);

// See http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

$redprunus_proxy_url = WP_PLUGIN_URL. '/' . plugin_basename(dirname(__FILE__)) . '/proxy.php';

function redprunus_filter_the_content_images($result){
	global $redprunus_banned_urls;
	foreach($redprunus_banned_urls as $url)
		$result = preg_replace_callback("#$url#i", create_function(
            '$matches',
            'return $GLOBALS["redprunus_proxy_url"] . "?url=" . urlencode(encrypt(html_entity_decode_utf8($matches[0]))) ;'
        ), $result);
	return $result;
}

function redprunus_filter_text($t,$is_html_attribute = false){
	$result = UTF8entities($t);
	if(!$is_html_attribute){
		$codes = array('x2060', 'xfeff', 'x200b');
		$result = preg_replace_callback('|&#(\d+);|i', create_function(
			'$m',
			'if ($m[1]=="10"){ return $m[0]; } else { return $m[0] . "&#x200b;&#x2060;&#xfeff;"; } '
		), $result);
	}
	return $result;
}

function _redprunus_replace_callback_htmlattributes($matches){
	//return $matches[1].$matches[2].$matches[3];
	
	return $matches[1]. preg_replace_callback('#(alt|title|href)=([\'"]?)(.*?)([\'"]?)#i', create_function(
		'$m',
		'return $m[1]. "=". $m[2]. redprunus_filter_text(html_entity_decode_utf8($m[3]), true) . $m[4];'
	), $matches[2]) . $matches[3];
	
}
function _redprunus_replace_callback_htmltext($matches){
	$m = $matches[1];
	if($m{0} == '<') // html
		return $m;// return "(". $m. ")";
	$t = "";
	$lastC = strlen($m)-1;
	if($m{$lastC} == '<'){
		$m = substr($m, 0, $lastC);
		$t = $m{$lastC};
	}
	$m = redprunus_filter_text(html_entity_decode_utf8($m));
	return $m.$t;//return "[". $m. $t. "]";
}

function redprunus_filter_the_content_encode_html($result){
	
	// html attributes
	$result = preg_replace_callback('#(<[a-z]+)([^<>]*)(/?>)#i', '_redprunus_replace_callback_htmlattributes', $result);
	
	// html texts
	$result = preg_replace_callback('#(</?[a-z]+[^<>]*>|[^<>]*)#i', '_redprunus_replace_callback_htmltext', $result);
	
	return $result;
}


function redprunus_filter_the_content_encode($result){
	return redprunus_filter_text(html_entity_decode_utf8($result));
}

if(!is_admin()){

	add_filter('the_content', 'redprunus_filter_the_content_images');
	add_filter('the_content_rss', 'redprunus_filter_the_content_images');

	$items_to_encode = array(
		//'localization' => 'text',
		//'language' => 'text',
		'bloginfo' => 'text','get_bloginfo_rss' => 'text',
		'the_content' => 'html','the_content_rss' => 'html','the_excerpt' => 'html','the_excerpt_rss' => 'html','single_post_title' => 'html','the_title' => 'html',
		'term_name' => 'html','term_name_rss' => 'html', // Terms
		'link_name' => 'text','link_notes' => 'text', // Blogroll
		'single_tag_title' => 'text', // Tag
		'comment_author' => 'text', 'comment_author_rss' => 'text', 'comment_link' => 'text', 'comment_text' => 'html', 'comment_text_rss' => 'html', // Comment
		'trackback_url' => 'text', 'trackback_title' => 'text',
		'single_cat_title' => 'text','list_cats' => 'html', // Category
		'widget_text' => 'text',
		'description' => 'text',
		'term_description' => 'text','term_description_rss' => 'text','link_description' => 'text','category_description' => 'text','widget_title' => 'text'
	);
	foreach($items_to_encode as $i => $t)
		add_filter($i, $t == 'html' ? 'redprunus_filter_the_content_encode_html': 'redprunus_filter_the_content_encode', 99);
		

}
?>