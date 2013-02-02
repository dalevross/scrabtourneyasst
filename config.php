<?php

//define("FACEBOOK_APP_ID", '163757063643167');
//define("FACEBOOK_API_KEY", '790e39abc7fcdb0975ef12d4ff9e97e2');
//define("FACEBOOK_SECRET_KEY", 'bf6ae88c03b5ff3b8d7f7a68e04517ae');
define("FACEBOOK_APP_ID", '390735480949561');
define("FACEBOOK_API_KEY", '390735480949561');
define("FACEBOOK_SECRET_KEY", '3b58860ceba67a93f313895444db8af2');

define("FACEBOOK_CANVAS_URL", 'http://apps.facebook.com/lexwstournaments/');



function loadXML2($domain, $path, $timeout = 30) {

    /*
        Usage:
       
        $xml = loadXML2("127.0.0.1", "/path/to/xml/server.php?code=do_something");
        if($xml) {
            // xml doc loaded
        } else {
            // failed. show friendly error message.
        }
    */

    $fp = fsockopen($domain, 80, $errno, $errstr, $timeout);
    if($fp) {
        // make request
        $out = "GET $path HTTP/1.1\r\n";
        $out .= "Host: $domain\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
       
        // get response
        $resp = "";
        while (!feof($fp)) {
            $resp .= fgets($fp, 128);
        }
        fclose($fp);
        // check status is 200
        $status_regex = "/HTTP\/1\.\d\s(\d+)/";
        if(preg_match($status_regex, $resp, $matches) && $matches[1] == 200) {   
            // load xml as object
            $parts = explode("\r\n\r\n", $resp);   
            return simplexml_load_string($parts[1]);               
        }
    }
    return false;
   
}


function get_facebook_cookie($app_id, $application_secret) {
  $args = array();
  parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
  ksort($args);
  $payload = '';
  foreach ($args as $key => $value) {
    if ($key != 'sig') {
      $payload .= $key . '=' . $value;
    }
  }
  if (md5($payload . $application_secret) != $args['sig']) {
    return null;
  }
  return $args;
}


function get_insight_access_token()
{
	/*
	curl -F grant_type=client_credentials \
     -F client_id=your_app_id \
     -F client_secret=your_app_secret \
     https://graph.facebook.com/oauth/access_token
	*/
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=" . FACEBOOK_APP_ID . "&client_secret=" . FACEBOOK_SECRET_KEY);
	//curl_setopt($curl, CURLOPT_ENCODING, "");
	//curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	//echo $err;
	//echo $errmsg;
	curl_close($curl);

	return $curlData;
}


function get_access_token($cookiejar)
{
	/*
	curl -F grant_type=client_credentials \
     -F client_id=your_app_id \
     -F client_secret=your_app_secret \
     https://graph.facebook.com/oauth/access_token
     
     https://graph.facebook.com/oauth/access_token?client_id=YOUR_APP_ID&client_secret=YOUR_APP_SECRET&grant_type=client_credentials
	*/
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/oauth/authorize?client_id=' .FACEBOOK_APP_ID. '&redirect_uri=http://www.facebook.com/connect/login_success.html&type=user_agent&display=page');
	//curl_setopt($curl, CURLOPT_URL, "https://graph.facebook.com/oauth/access_token?client_id=" . FACEBOOK_APP_ID . "&client_secret=" . FACEBOOK_SECRET_KEY . "&grant_type=client_credentials");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=" . FACEBOOK_APP_ID . //"&client_secret=" . FACEBOOK_SECRET_KEY);
	//curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$content = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	$response = curl_getinfo($curl) ;
	
	//echo $response;
	//echo $content;
	

	$header['content'] = str_replace("\u00257C", "|",$content);;
	$header['err'] = $err;
	$header['errmsg'] = $errmsg;
	$header['response'] = $response;
	
	curl_close($curl);
	//print_r($header);
	
	return $header;
}




function get_member_list($url)
{
	/*
	curl -F grant_type=client_credentials \
     -F client_id=your_app_id \
     -F client_secret=your_app_secret \
     https://graph.facebook.com/oauth/access_token
	*/
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=" . FACEBOOK_APP_ID . //"&client_secret=" . FACEBOOK_SECRET_KEY);
	//curl_setopt($curl, CURLOPT_ENCODING, "");
	//curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	$info = curl_getinfo($curl) ;
	
	$header['content'] = $curlData;
	$header['err'] = $err;
	$header['errmsg'] = $errmsg;
	$header['info'] = $info;
	
	curl_close($curl);

	return $header;
}

function are_friends($uid1,$uid2,$access_token)
{
	$url = 'https://api.facebook.com/method/friends.areFriends?uids1=' . $uid1 . '&uids2=' . $uid2  . '&access_token=' . $access_token . '&format=JSON';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=" . FACEBOOK_APP_ID . //"&client_secret=" . FACEBOOK_SECRET_KEY);
	//curl_setopt($curl, CURLOPT_ENCODING, "");
	//curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	$info = curl_getinfo($curl) ;
	
	$header['content'] = $curlData;
	$header['err'] = $err;
	$header['errmsg'] = $errmsg;
	$header['info'] = $info;
	
	curl_close($curl);

	return $header;
}



function json_to_xml($obj,$options) {
    $serializer = new XML_Serializer($options);
    //$obj = json_decode($json);
    if ($serializer->serialize($obj)) {
        return $serializer->getSerializedData();
    }
    else {
        return null;
    }
}

function cmp($a, $b) {
    /*if ($a['name'] == $b['name']) {
        return 0;
    }
	//echo $a['name'] . " | " . $b['name'] . "<br />";
    return ($a['name'] < $b['name'])? -1 : 1;
	*/
	//$admina = is_null($a['administrator'])?"0":$a['administrator'];
	//$adminb = is_null($b['administrator'])?"0":$b['administrator'];
	//return ($admina == $adminb)?strcmp($a['name'],$b['name']):$adminb-$admina;	
	return strcmp($a['name'],$b['name']);
	
}

function get_fb_web_page( $url,$curl_data,$cookiejar)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . "?" . $curl_data);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	//curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "charset_test=" . $charsetTest . "&locale=" . $locale . "&non_com_login=&email=" . $username . "&pass=" . $password . "&charset_test=" . $charsetTest . "&lsd=" . $lsd);
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	//echo $err;
	//echo $errmsg;
	curl_close($curl);

	return $curlData;
}

function get_web_page( $url,$curl_data)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl,CURLOPT_HTTPHEADER,array ("Content-type: application/x-www-form-urlencoded;charset=UTF-8"));
	curl_setopt($curl, CURLOPT_POSTFIELDS,$curl_data);
	curl_setopt($curl, CURLOPT_ENCODING, "");
	//curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	//echo $err;
	//echo $errmsg;
	curl_close($curl);

	return $curlData;
}

function get_web_page_np( $url,$curl_data)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . "" . $curl_data);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS,);
	curl_setopt($curl,CURLOPT_HTTPHEADER,array ("Content-Type: text/html; charset=utf-8"));
	//curl_setopt($curl, CURLOPT_ENCODING, "");
	//curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	//curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	$err     = curl_errno($curl);
	$errmsg  = curl_error($curl) ;
	//echo $err;
	//echo $errmsg;
	curl_close($curl);

	return $curlData;
}

function endsWith($haystack,$needle,$case=true) {
    if($case){return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);}
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
}



?>
