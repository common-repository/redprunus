<?php
define('SALT', 'RedPrunus'); 

function encrypt($text) 
{
	//return $text;
	return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
}

function decrypt($text) 
{ 
	//return $text;
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
} 

// Reference: http://us3.php.net/manual/en/function.htmlentities.php

function UTF8entities($content="") { 
	$contents = unicode_string_to_array($content);
	$swap = "";
	$iCount = count($contents);
	for ($o=0;$o<$iCount;$o++) {
		$contents[$o] = unicode_entity_replace($contents[$o]);
		$swap .= $contents[$o];
	}
	return mb_convert_encoding($swap,"UTF-8");
}

function unicode_string_to_array( $string ) {
	$strlen = mb_strlen($string);
	while ($strlen) {
		$array[] = mb_substr( $string, 0, 1, "UTF-8" );
		$string = mb_substr( $string, 1, $strlen, "UTF-8" );
		$strlen = mb_strlen( $string );
	}
	return $array;
}

function unicode_entity_replace($c) {
	$h = ord($c{0});
	if ($h <= 0x7F) { 
		return "&#". $h. ";";
	} else if ($h < 0xC2) { 
		return "&#". $h. ";";
	}
	
	if ($h <= 0xDF) {
		$h = ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
		$h = "&#" . $h . ";";
		return $h; 
	} else if ($h <= 0xEF) {
		$h = ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6 | (ord($c{2}) & 0x3F);
		$h = "&#" . $h . ";";
		return $h;
	} else if ($h <= 0xF4) {
		$h = ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12 | (ord($c{2}) & 0x3F) << 6 | (ord($c{3}) & 0x3F);
		$h = "&#" . $h . ";";
		return $h;
	}
}

// Reference: http://us3.php.net/manual/en/function.html-entity-decode.php

function html_entity_decode_utf8($string)
{
    static $trans_tbl;
    
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

    // replace literal entities
    if (!isset($trans_tbl))
    {
        $trans_tbl = array();
        
        foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
            $trans_tbl[$key] = utf8_encode($val);
    }
    
    return strtr($string, $trans_tbl);
}

// Returns the utf string corresponding to the unicode value (from php.net, courtesy - romans@void.lv)
function code2utf($num)
{
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}
?>