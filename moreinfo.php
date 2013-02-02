<?php

include_once 'config.php';


function get_game_rating($profileid,$game)
{
		$curl_data = "action=profile&profileid=" . $profileid;
		$url = 'http://apps.facebook.com/' . $game . '/';
		
                 
                try{
                $response = get_fb_web_page($url,$curl_data,"fbk.ck");
                } catch (Exception $e) {
                  echo 'Caught exception: ' .  $e->getMessage() . "\n";
                }

		
		$signed_request = substr($response, strpos($response, "signed_request"));
		$signed_request = substr($signed_request, strpos($signed_request, "value=") + 8);
		$signed_request = substr($signed_request, 0, strpos($signed_request, "\"")-1);
		
		$paltuasub = ($game=="wordscraper")?$game:"facebook";
		
		$url = "http://play.paltua.com/". $paltuasub . "/?action=profile&profileid=" . $profileid;
		$curl_data = "signed_request=" . $signed_request;
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
		  echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
				

		preg_match("/Rating:\s(?P<rating>\d+)\S*span>/",$response,$match);
		
		return $match['rating'];

}

if (isset($_GET["profileid"]))
{

	$profileid=$_GET["profileid"];
	if(filter_var($profileid,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d+$/"))))
	{


		/*$curl_data = "action=profile&profileid=" . $profileid;
		$url = 'http://apps.facebook.com/' . 'lexulous' . '/';
		
                 
                try{
                $response = get_fb_web_page($url,$curl_data,"fbk.ck");
                } catch (Exception $e) {
                  echo 'Caught exception: ' .  $e->getMessage() . "\n";
                }

		
		preg_match(" /Best\sBingo\S+<\Std><\Str><tr><td>(?P<rating>\d+)<\Std>/",$response,$match);
		$lexrating = $match['rating'];
		$lexrating = is_null($lexrating)?"0":$lexrating;		
		$curl_data = "action=profile&profileid=" . $profileid;
		$url = 'http://apps.facebook.com/' . 'wordscraper' . '/';
		
                 
                try{
                $response = get_fb_web_page($url,$curl_data,"fbk.ck");
                } catch (Exception $e) {
                  echo 'Caught exception: ' .  $e->getMessage() . "\n";
                }

		
		preg_match(" /Best\sBingo\S+<\Std><\Str><tr><td>(?P<rating>\d+)<\Std>/",$response,$match);
		$wsrating = $match['rating'];
		$wsrating = is_null($wsrating)?"0":$wsrating;
		*/
		
		$lexrating = get_game_rating($profileid,'lexulous');
		$lexrating = is_null($lexrating)?"0":$lexrating;
		
		$wsrating = get_game_rating($profileid,'wordscraper');
		$wsrating = is_null($wsrating)?"0":$wsrating;
		
		//echo $match['rating'];
		$header = get_access_token('fbk.ck');

		preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match2);
		$access_token = $match2['access_token'] ;

		$response = are_friends($profileid,'1374234116',$access_token);
		$json = $response['content'];

		$obj = json_decode($json,true);
		$text = (($obj[0]['are_friends']=='1')?"":"not ") . "friends with Scrab Tournament";
		
		$response = are_friends($profileid,'100001252263396',$access_token);
		$json = $response['content'];

		$obj = json_decode($json,true);
		$text2 = (($obj[0]['are_friends']=='1')?"":"not ") . "friends with Carrie Banks";
		
		
		$info = "Lexulous Rating: <span title='click for details' style='text-decoration:underline;color:blue' class='lexrating". $profileid."'>" . $lexrating . "</span><br/>Wordscaper Rating: <span style='text-decoration:underline;color:red'  title='click for details' class='wsrating". $profileid."'>" . $wsrating . "</span><br/> <b>namepat" . $text . "</b>" ;
		//$info = $info . "<br/> <font color='red'><b>namepat" . $text2 . "</b></font>";
		echo $info;
				
	}
	else
	{
		echo "Unable to load profile";
	}
}
else
{
	echo "Unable to load profile";
}

//echo $json;
?>