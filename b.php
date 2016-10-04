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

$con = mysqli_connect($host,$username,$password,$dbname);

// Kiem tra ket noi
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// Perform queries 
$result = mysqli_query($con,'SELECT * FROM youtube WHERE ytb=0 AND name="'.$_GET['name'].'"');
$row = $result->fetch_array(MYSQLI_ASSOC);

$Email = $row['email'];
$Passwd = $row['passwd'];
$keyword = ucfirst(strtolower($row['keyword']));

// Lay GALX,gxf
$page = get('https://www.google.com/accounts/ServiceLogin?uilel=3&service=youtube&passive=true&continue=http://www.youtube.com/signin?action_handle_signin=true&nomobiletemp=1&hl=en_US&next=%2Findex&hl=en_US&ltmpl=sso','http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

preg_match('/<input type="hidden" name="gxf" value="([^"]+)">/',$page,$matches);
$gxf = $matches[1];

preg_match('/<input type="hidden" name="GALX" value="([^"]+)">/',$page,$matches);
$GALX = $matches[1];

// Dang nhap youtube
$post = 'galx='.$GALX.'&Email='.$Email.'&Passwd='.$Passwd.'&gxf='.$gxf;
post('https://www.google.com/accounts/ServiceLoginAuth?service=youtube',$post,'http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

// Lay thong tin youtube
$page = get('https://www.youtube.com/view_all_playlists','http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

preg_match('/<span id="creator-subheader-item-count" class="yt-badge-creator">(\d+)<\/span>/',$page,$matches);
$playlist_count = $matches[1];

preg_match('/.XSRF_TOKEN.: "([^"]+)"/',$page,$matches);
$session_token = $matches[1];

// Lay video theo tu khoa
$page = get('https://www.youtube.com/results?search_query='.rawurlencode($keyword).'&sp=EgIQAQ%253D%253D','http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

preg_match_all('/<a href="\/watch\?v=([^"]+)"/',$page,$matches);	
$video_ids = join(',',array_merge(array_merge(array($matches[1][0]),explode(',',$row['video_ids'])),$matches[1]));

// Tao playlist
$post = 'video_ids='.$video_ids.'&source_playlist_id=&n='.$keyword.'&session_token='.$session_token;
$page = post('https://www.youtube.com/playlist_ajax?action_create_playlist=1',$post,'http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

preg_match('/"playlistId":"([^"]+)"/',$page,$matches);
$playlist_id = $matches[1];

// Them mo ta playlist
$post = 'playlist_id='.$playlist_id.'&playlist_description='.$keyword.'&session_token='.$session_token;
post('https://www.youtube.com/playlist_edit_service_ajax?action_set_playlist_description=1',$post,'http://www.youtube.com/',$COOKIEJAR,$USERAGENT);

mysqli_query($con,'UPDATE youtube SET ytb=1,playlist_id="'.$playlist_id.'" WHERE id='.$row['id']);

unlink($COOKIEJAR);
?>