<?php

include_once 'config.php';
require_once 'XML/Unserializer.php';
require_once './src/class.phpmailer.php';

function VisitorIP()
{
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	$TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
	else $TheIp=$_SERVER['REMOTE_ADDR'];

	return trim($TheIp);
}

function sendsuspectinfo($subject,$user,$ip,$id)
{
	$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
	//$mail->IsSMTP(); // telling the class to use SMTP

	try {

		$html = "<span style='font-weight:bold'>Name: </span>$user<br/><span style='font-weight:bold'>IP: </span>$ip<br/><span style='font-weight:bold'>ID: </span>$id<br/>";
		$html = $html . "<img src='http://graph.facebook.com/$id/picture?type=large'/>"; 
		$mail->AddReplyTo('kingdale16@hotmail.com', 'Dale Ross');
		$mail->AddAddress('kingdale16@hotmail.com', 'Dale Ross');
		//$mail->AddAddress('nataliez@supanet.com', 'Natalie Zolty');
		//$mail->AddAddress('biddleco@usc.edu', 'Susan Biddlecom');
		//$mail->AddAddress('wendymidge@hotmail.com', 'Midge Midgley');
		//$mail->AddAddress('andrewsmomma63@sbcglobal.net', 'Lori Martinez');
		//$mail->AddAddress('biddleco@usc.edu', 'Susan Biddlecom');
		//		nataliez@supanet.com;biddleco@uphc.usc.edu; kingdale16@hotmail.com; willson_liz@hotmail.com; crlbwr4@gmail.com; ethelhumphreys@hotmail.com; kawijomo@xsinet.co.za; midgemidgley-01@hotmail.com; wendymidge@hotmail.com; davidmallick@gmail.com

		$mail->SetFrom('scrabtourneyasst@dalevross.com', 'Scrabulous Tournament Assistant');
		$mail->Subject = $subject . " - " . $user;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($html);
		//$mail->AddAttachment('tourneyasst75x75.png');      // attachment
		//mail->AddAttachment('EventAttendees_Rating.xls'); // attachment
		$mail->Send();
		//echo "Message Sent OK\nRuntime: " . time_diff_conv($start,$end);
	} catch (Exception $e) {
		
	}

}

if (isset($_GET["gid"]) && isset($_GET["game"]) )
{

	$gid=$_GET["gid"];
	$game=$_GET["game"];
	$version = isset($_GET["version"])?$_GET["version"]:1;

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

		//echo $url;
			
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}


		$suspects = array('1348004942'=>'Irwan B. Indrakesuma',
		'1662503811'=>'Ibi Aja',
		'100001704386765'=>'Nerwa Bri',
		'1559923780'=>'Dwi Agus Waskito');
		//,
		//'593170373'=>'Dale V Ross');

		$ignore = array('1672951989','593170373','550831444','1352616772','615116932','1133757381','1322720576','765113484','1055604406','1027011861','1459272510');
		
		$users_ip_address = VisitorIP();
		
		$options = array('complexType' => 'object');

		$us = new XML_Unserializer($options);
		$result = $us->unserialize($response, false);
		$obj = $us->getUnserializedData();

		$currentuser = eval("return \$obj->gameinfo->p" . $pid . "email;");
		$currentusername = eval("return \$obj->gameinfo->p" . $pid . ";");
		
		if(array_key_exists($currentuser, $suspects))
		{
			//sendsuspectinfo('Suspected Multiprofile',$suspects[$currentuser], $users_ip_address,$currentuser);		
		}
		else 
		{
			if(!in_array($currentuser,$ignore))
			{
				//sendsuspectinfo('Tracker User Log',$currentusername, $users_ip_address,$currentuser);
			}
		}
		
		if($game=="lexulous")
		{
			$distribution = "a,8|b,2|c,2|d,3|e,11|f,2|g,2|h,2|i,8|j,1|k,1|l,3|m,2|n,5|o,7|p,2|q,1|r,5|s,3|t,5|u,3|v,2|w,2|x,1|y,3|z,1|blank,2";

		}
		else
		{
			$distribution = $obj->gameinfo->tile_count;
		}

		$letterpairs = explode("|",$distribution);
		$arrdistribution = array();
		$arrused = array();
		$arrleft = array();
		$response = array();
		foreach ($letterpairs as $letterpair) {
			list($letter,$count) = explode(",",$letterpair);
			$letter = ($letter=='blank')?'?':strtolower($letter);
			$arrdistribution[$letter]=(int)$count;
			$arrused[$letter]=0;
		}
		$response['distribution'] = $arrdistribution;


		$nodevals = $obj->boardnode->nodeval;
		if(isset($nodevals))
		{
			$tiles = explode("|",$nodevals);
			foreach ($tiles as $vals) {
				list($letter,$x,$y,$turn) = explode(",",$vals);
				$letter = (ctype_lower($letter))?'?':strtolower($letter);
				$arrused[$letter]++;
			}
		}

		$myrack = $obj->gameinfo->myrack;
		$status = (strtoupper(trim($obj->gameinfo->status))=="F")?'Game completed':((strtoupper(trim($obj->gameinfo->myturn))=="Y")?'<span style="font-weight:bold">It\'s now your turn!</span>':'Opponent\'s turn.');
		if(isset($myrack))
		{
			if(trim($myrack)!="")
			{
				$mytiles = str_split($myrack);
				foreach ($mytiles as $letter) {
					$letter = ($letter=="*")?'?':strtolower($letter);
					$arrused[$letter]++;
				}
			}

		}

		$arrleft = array_filter(array_combine(array_keys($arrdistribution),array_map(difference,$arrdistribution,$arrused)),nonzero);

		$response['remaining'] = $arrleft;
		$response['tilecount'] = array_sum($arrleft);

		$response['status'] = $status;
		$html = '';
		/*if($currentuser=="593170373")
		{
			$html = $html . '<span style="font-weight:bold;">Hi ' . $suspects[$currentuser] .'</span><br/>';
		}
		*/
		$color = ($game=="lexulous")?"#2BB0E8":"red";
		$inbag = ($response['tilecount']>8)?$response['tilecount']-8:0;
		switch($version)
		{
			case 2:
				$index = 0;
				//$html = $html + '<table style="border-style:solid;border-width:1px;border-color:brown;">';
				//if($game=="lexulous")
				//{
					$html = $html . '<span style="font-weight:bold;">Tile Count: ' . $response['tilecount'] . '</span><span> ('. $inbag . ' in bag)</span><br/>';
					foreach ($arrleft as $letter => $count) {
						if(($index % 8)===0)
						{
							$html = $html . '<div style="float:left;padding:10px">';
						}
						$html = $html . '<span style="color:' . $color . '" title="Total: ' . $arrdistribution[$letter] .  '">' . strtoupper($letter) . ' - ' . $count . '</span><br/>';
						$index++;
						if((($index)%8===0 && $index!==0)|| $index===count($arrleft) )
						{
							$html = $html . '</div>';
						}
					}
					
				//}
				
				//$html = $html . 'The tile tracker is currently down for maintenance. <br/>';
				
				

				$html = $html . '<div style="clear:both"/>';

				$html = $html . '<span id="trackerstat">' . $status . '</span><br/><br/>';


				$html = $html . '<span>Brought to you by<br/><a href="http://www.facebook.com/lexandws?ref=ts" target="_blank" ><span style="text-decoration:underline;color:blue;">Lexulous/Wordscraper Tournaments</span></a></span>';
				
				$html = $html . '<br/><br/><span>Contact <a href="http://www.facebook.com/dvross" target="_blank" ><span style="text-decoration:underline;color:blue;">Dale V. Ross</span></a> for support or suggestions</span>';
							
				//$html = $html + '<tr style="text-align:center;background-color:brown;color:white;"><td>' + 'Tile Count' + '</td><td>' + $response['tilecount'] + '</td></tr></table>';
				$actualrespose = array('html'=>$html);
				break;
			default:
				$actualrespose = $response;
				break;
					
		}

		if(isset($_GET['callback']))
		{
			generate_jsonp($actualrespose);
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($actualrespose);
		}





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

function difference($a,$b)
{
	return $a-$b;
}

function nonzero($element)
{
	return $element !=0;
}



function generate_jsonp($data) {
	if (preg_match('/\W/', $_GET['callback'])) {
		// if $_GET['callback'] contains a non-word character,
		// this could be an XSS attack.
		header('HTTP/1.1 400 Bad Request');
		exit();
	}
	//header('Content-type: application/javascript; charset=utf-8');
	header('Content-Type: application/json');
	print sprintf('%s(%s);', $_GET['callback'], json_encode($data));
}




?>