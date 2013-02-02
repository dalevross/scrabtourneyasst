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
		
		$url = (($game=="lexulous")?"http://aws.rjs.in/fblexulous/engine":"http://aws.rjs.in/wordscraper/engine") . "/xmlv3.php";
		
		
                 
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
		header('Content-Type: application/json');
		
		//echo $response;
		
		$options = array('complexType' => 'object');

		$us = new XML_Unserializer($options);
		$result = $us->unserialize($response, false);
		$obj = $us->getUnserializedData();
		
		//var_dump(isset($obj->gameinfo->p3email));
		//$gcount = $obj->gameinfo->;
               
		$jsonresponse = array('dictionary'=>$obj->gameinfo->dictionary,'winner'=>$obj->gameinfo->winner); 
		
			
		
		
		for ($i = 1; $i <= 4; $i++) {
			$res = eval("return isset(\$obj->gameinfo->p" . $i . "email);");
			
			if($res)
			{
				$winner =  (eval("return \$obj->gameinfo->p" . $i . ";")==$obj->gameinfo->winner)?"yes":"no";
				
				$players[$i] = array();
				$players[$i]['pid'] = $i;			
				$players[$i]['pname'] = eval("return \$obj->gameinfo->p" . $i . ";");	
				$players[$i]['pscore'] = eval("return \$obj->gameinfo->p" . $i . "score;");
				$players[$i]['winner'] = $winner;
				$players[$i]['pemail'] = eval("return \$obj->gameinfo->p" . $i . "email;");
				$players[$i]['pic'] = 'https://graph.facebook.com/' . eval("return \$obj->gameinfo->p" . $i . "email;") . '/picture';
				$players[$i]['pracklen'] = eval("return \$obj->gameinfo->p" . $i . "racklen;");			
			
			
			}
			else
			{
				
				break;
			}
			
		}
		
		$countval = $i - 1;
		$jsonresponse['count'] = $countval;
		$jsonresponse['players'] = $players;
		
		// get completed xml document
		//echo $xml_string;
		
		echo json_encode($jsonresponse);
		
                
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