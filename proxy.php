<?php

include_once 'config.php';


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

		
		//if($game=="wordscraper")
		//{
		$signed_request = substr($response, strpos($response, "signed_request")-500);
		$signed_request = substr($signed_request, strpos($signed_request, "value=") + 7);
		$signed_request = substr($signed_request, 0, strpos($signed_request, "\""));
		
		$paltuasub = ($game=="wordscraper")?$game:"facebook";
		//echo $signed_request;
		$url = "http://play.paltua.com/". $paltuasub . "/?action=profile&profileid=" . $profileid;
		$curl_data = "signed_request=" . $signed_request;
		try{
		$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
		  echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
		//}
		
		$pattern = '/<a href=\s?\Shttp:\S\Sapps\.facebook\.com\S\w+\S\Saction=profile&profileid=\d+\S.*?>(\S+\s(\S\.?\s?)+?)<\Sa>/';
		$replacement = '$1';
		
		
		$response = preg_replace($pattern,$replacement,$response);
		
		preg_match_all("/(?P<link>http\S+gid=(?P<gid>\d+)).*?>(?P<name>(((\w|\p{L})+\s((\w|\p{L})\.?\s?)+)(,\s)?)+)\S+span>/u",$response,$matches);
				
				

		preg_match("/Rating:\s(?P<rating>\d+)\S*span>/",$response,$match);
		
		
		$header = get_access_token('fbk.ck');

		preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match2);
		$access_token = $match2['access_token'] ;

		$response = are_friends($profileid,'1374234116',$access_token);
		$json = $response['content'];

		$obj = json_decode($json,true);
		$text = (($obj[0]['are_friends']=='1')?"":"not ") . "friends with Scrab Tournament";
				
		$gcount = count($matches['link']);
               
		$doc = new DomDocument('1.0','UTF-8');
		// create root node
		$root = $doc->createElement('Profile');
		$root = $doc->appendChild($root);

		$games = $doc->createElement('Games');
		$games = $root->appendChild($games);
		
		for ($i = 0; $i < $gcount; $i++) {
			$occ = $doc->createElement('Game');
			$occ = $games->appendChild($occ);
			$child = $doc->createElement('link');
			$child = $occ->appendChild($child);
			$value = $doc->createTextNode(str_replace("\/","/",(htmlspecialchars_decode($matches['link'][$i]))));
			$value = $child->appendChild($value);
			$child = $doc->createElement('gid');
			$child = $occ->appendChild($child);
			$value = $doc->createTextNode($matches['gid'][$i]);
			$value = $child->appendChild($value);
			
			//$child = $doc->createElement('profileid');
			//$child = $occ->appendChild($child);
			//$value = $doc->createTextNode($matches['profileid'][$i]);
			//$value = $child->appendChild($value);
			$child = $doc->createElement('name');
			$child = $occ->appendChild($child);
			$value = $doc->createTextNode($matches['name'][$i]);
			$value = $child->appendChild($value);
			$child = $doc->createElement('label');
			$child = $occ->appendChild($child);
			$label = sprintf("[%10s vs %-15s]",trim($matches['gid'][$i]),trim($matches['name'][$i]));
			$value = $doc->createTextNode($label);
			$value = $child->appendChild($value);
		}

		$rating = $doc->createElement('Rating');
		$rating = $root->appendChild($rating);		
		$value = $doc->createTextNode($match['rating']);
		$value = $rating->appendChild($value);
		
		$sft = $doc->createElement('ScrabFriendText');
		$sft = $root->appendChild($sft);		
		$value = $doc->createTextNode($text);
		$value = $sft->appendChild($value);
		
		
		// get completed xml document
		$xml_string = $doc->saveXML();
		header('Content-Type: text/xml');
		echo $xml_string;
		
		/*
		print_r($matches['link']);
		echo "<br/><br/>";
		print_r($matches['gid']);
		echo "<br/><br/>";		
		print_r($matches['profileid']);
		echo "<br/><br/>";
		print_r($matches['name']);
		echo "<br/><br/>";		
		print_r($matches['date']);
		echo "<br/><br/>";
		*/
		/*$ratingstable = substr($response, strpos($response, "<table><tr><td><b>Rating"));
		$ratingstable = substr($ratingstable, 0, strpos($ratingstable, "</table>") +8);*/
		
		//echo $ratingstable;
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