<?php

include_once 'config.php';
require_once 'XML/Unserializer.php';

if (isset($_GET["gid"]) && isset($_GET["game"]) )
{

	$gid=$_GET["gid"];
	$game=$_GET["game"];
	
	
	if(filter_var($gid,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d+$/")))
	&&
	filter_var($game,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^(wordscraper|lexulous)$/"))))
	{


		$curl_data = "action=gameinfo&gid=" . $gid;
		
		if(isset($_GET["pid"]) && isset($_GET["password"]) )
		{
			$pid=$_GET["pid"];
			$password=$_GET["password"];
			if(filter_var($pid,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d$/")))
			&&
			filter_var($password,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\w+$/"))))
			{
				$curl_data = $curl_data . "&pid=" . $pid . "&password=" . $password;
				
			}
			else
			{
				$curl_data = $curl_data . "&pid=1";
			
			}
		
		}
		else
		{
			$curl_data = $curl_data . "&pid=1";
		
		}
		
		$url = "http://74.54.87.124/" . (($game=="lexulous")?"lexulous":"wordscraper/engine") . "/xmlv3.php";
		
		
                 
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
		header('Content-Type: text/xml');
		
		//echo $response;
		
		$options = array('complexType' => 'object');

		$us = new XML_Unserializer($options);
		$result = $us->unserialize($response, false);
		$obj = $us->getUnserializedData();
		
		//var_dump(isset($obj->gameinfo->p3email));
		//$gcount = $obj->gameinfo->;
               
		$doc = new DomDocument('1.0','UTF-8');
		// create root node
		$root = $doc->createElement('xml');
		$root = $doc->appendChild($root);

		$gameinfo = $doc->createElement('gameinfo');
		$gameinfo = $root->appendChild($gameinfo);
		
		
		$players = $doc->createElement('players');
		$players = $gameinfo->appendChild($players);
		
		
		
		for ($i = 1; $i <= 4; $i++) {
			$res = eval("return isset(\$obj->gameinfo->p" . $i . "email);");
			
			if($res)
			{
				$winner =  (eval("return \$obj->gameinfo->p" . $i . ";")==$obj->gameinfo->winner)?"yes":"no";
				
				$player = $doc->createElement('player');
				$player = $players->appendChild($player);
				
				$child = $doc->createElement('pid');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode('' . $i);
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('pname');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode(eval("return \$obj->gameinfo->p" . $i . ";"));
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('pscore');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode(eval("return \$obj->gameinfo->p" . $i . "score;"));
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('winner');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode($winner);
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('pemail');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode(eval("return \$obj->gameinfo->p" . $i . "email;"));
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('pic');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode('https://graph.facebook.com/' . eval("return \$obj->gameinfo->p" . $i . "email;") . '/picture');
				$value = $child->appendChild($value);
				
				$child = $doc->createElement('pracklen');
				$child = $player->appendChild($child);
				$value = $doc->createTextNode(eval("return \$obj->gameinfo->p" . $i . "racklen;"));
				$value = $child->appendChild($value);
				
			}
			else
			{
				
				break;
			}
			
		}
		
		$countval = $i - 1;
		
		$dictionary = $doc->createElement('dictionary');
		$dictionary = $gameinfo->appendChild($dictionary);		
		$value = $doc->createTextNode($obj->gameinfo->dictionary);
		$value = $dictionary->appendChild($value);
		
		$winner = $doc->createElement('winner');
		$winner = $gameinfo->appendChild($winner);		
		$value = $doc->createTextNode($obj->gameinfo->winner);
		$value = $winner->appendChild($value);
		
		$count = $doc->createElement('count');
		$count = $gameinfo->appendChild($count);		
		$value = $doc->createTextNode($countval);
		$value = $count->appendChild($value);
		
		// get completed xml document
		$xml_string = $doc->saveXML();
		echo $xml_string;
		
		
                
	}
	else
	{
		echo "Invalid gid or game";
	}
}
else
{
	echo "Invalid Request! What are you trying to do exactly?";
}



?>