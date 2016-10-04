<?php

include 'c.php';
echo '<meta http-equiv="refresh" content="60; url='.'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'">';

$COOKIEJAR = 'cookies/'.rand().'.txt';
$USERAGENT = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.13) Gecko/2009080315 Ubuntu/9.04 (jaunty) Firefox/3.0.13';

function get($URL,$REFERER,$COOKIEJAR,$USERAGENT)
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_REFERER,$REFERER); 
	curl_setopt($ch,CURLOPT_URL,$URL);
	curl_setopt($ch,CURLOPT_USERAGENT,$USERAGENT);
	curl_setopt($ch,CURLOPT_COOKIEJAR,$COOKIEJAR);
	curl_setopt($ch,CURLOPT_COOKIEFILE,$COOKIEJAR);
	curl_setopt($ch,CURLOPT_PROTOCOLS,CURLPROTO_HTTPS);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch,CURLOPT_TIMEOUT,30);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}

function post($URL,$POSTFIELDS,$REFERER,$COOKIEJAR,$USERAGENT)
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$URL);
	curl_setopt($ch,CURLOPT_USERAGENT,$USERAGENT);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); 
	curl_setopt($ch,CURLOPT_COOKIEJAR,$COOKIEJAR);
	curl_setopt($ch,CURLOPT_COOKIEFILE,$COOKIEJAR);
	curl_setopt($ch,CURLOPT_PROTOCOLS,CURLPROTO_HTTPS);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$POSTFIELDS); 
	curl_setopt($ch,CURLOPT_POST,1); 
	curl_setopt($ch,CURLOPT_TIMEOUT,30);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}

$page = get('https://whatismyipaddress.com/','',$COOKIEJAR,$USERAGENT);
var_dump($page);
?>