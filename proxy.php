<?php 
require_once 'functions.php';

//if(!preg_match("|^https?://" . preg_quote($_SERVER['HTTP_HOST']) . "/|i",  $_SERVER['HTTP_REFERER']))
//	die( "Http referer invalid: " . $_SERVER['HTTP_REFERER'] );


function curlHeaderCallback($resURL, $strHeader) {
	$length = strlen($strHeader);
	$strHeader = trim($strHeader);
	if(strlen($strHeader))
		header($strHeader);
    return $length; 
}
$strURL = decrypt($_REQUEST["url"]); 
header("_OrigionalLocation: " . $strURL);


$resURL = curl_init(); 
curl_setopt($resURL, CURLOPT_URL, $strURL); 



curl_setopt($resURL, CURLOPT_BINARYTRANSFER, 1); 
curl_setopt($resURL, CURLOPT_FOLLOWLOCATION , 1); 
curl_setopt($resURL, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback'); 
curl_setopt($resURL, CURLOPT_FAILONERROR, 1); 

$result = curl_exec ($resURL); 
if($result === false){
	echo 'error: no result returned.';
}
echo $result;

$intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE); 
curl_close ($resURL); 

if ($intReturnCode != 200) { 
    print 'error: ' . $intReturnCode; 
} 
?>