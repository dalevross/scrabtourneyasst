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

		preg_match_all("/(?P<link>http\S+gid=(?P<gid>\d+)\S*).*?profileid=(?P<profileid>\d+).*?>(?<name>\w+\s\w)..a>/",$response,$matches);
				
		$gcount = count($matches['link']);
		$color = ($game=="wordscraper")?"red":"blue";
			
		if($gcount > 0)
		{
			echo "<p style='margin-left:auto;margin-right:auto;text-align:left;color:". $color ."'>namepat</p>";
			echo "<table style='margin-left:auto;margin-right:auto;width:300px;text-align:left'><tr><th>Game</th><th>Opponent</th></tr>";
			
			for ($i = 0; $i < $gcount; $i++) {
				echo "<tr>";
				echo "<td border=0><a href='http://apps.facebook.com/". $game."/?action=viewboard&gid=" .  $matches['gid'][$i] ."&pid=1' target='_blank'>" . $matches['gid'][$i] ."</a></td>";
				echo "<td border=0><a href='http://apps.facebook.com/".$game ."/?action=profile&profileid=" .$matches['profileid'][$i] ."' target='_blank'>" . $matches['name'][$i] ."</a></td>";
				echo "</tr>";
			}
			echo "</table>";
		}
		else
		{
			echo "<p style='margin-left:auto;margin-right:auto;text-align:center;color:". $color ."'>No games found!</p>";
		}
		//echo "<p style='margin-left:auto;margin-right:auto;width:50px;text-align:center'><a id='". $game ."close' href='#null'>close</a></p>";
              
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