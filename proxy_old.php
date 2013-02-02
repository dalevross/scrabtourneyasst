<?php

include_once 'config.php';





function get_fb_web_page( $url,$curl_data,$cookiejar)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . "?" . $curl_data);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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



if (isset($_GET["profileid"]) && isset($_GET["game"]) )
{

	$profileid=$_GET["profileid"];
	$game=$_GET["game"];

	if(filter_var($profileid,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d+$/")))
	&&
	filter_var($game,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^(wordscraper|lexulous)$/"))))
	{


		$curl_data = "action=profile&profileid=" . $profileid;
		$url = 'http://apps.facebook.com/' . $game . '/';
		
                 
                try{
                $response = get_fb_web_page($url,$curl_data,"fbk.ck");
                } catch (Exception $e) {
                  echo 'Caught exception: ' .  $e->getMessage() . "\n";
                }

		$gameslist = substr($response, strpos($response, "<div class=\"gamelist\">"));
		$gameslist = substr($gameslist, strpos($gameslist, "<ul>"));
		$gameslist = substr($gameslist, 0, strpos($gameslist, "</ul>") +5);
		echo $gameslist;

		$ratingstable = substr($response, strpos($response, "<table><tr><td><b>Rating"));
		$ratingstable = substr($ratingstable, 0, strpos($ratingstable, "</table>") +8);
		echo $ratingstable;
		//echo $response;
                
	}
	else
	{
		echo "Invalid profileid or game";
	}
}
else
{
	echo "Invalid Request! What are you trying to do exactly?";
}



?>