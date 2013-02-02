<?php
set_include_path('.:/usr/local/php5/lib/php:/home/content/15/6983215/html/PEAR');
include_once 'config.php';
require_once 'XML/Serializer.php';
require_once 'Spreadsheet/Excel/Writer.php';
require_once './src/class.phpmailer.php';

date_default_timezone_set('UTC');
$lex_signed_request = null;
$ws_signed_request = null;

function get_game_rating($profileid,$game)
{
		$curl_data = "action=profile&profileid=" . $profileid;
		$url = 'http://apps.facebook.com/' . $game . '/';
		
                 
                try{
                $response = get_fb_web_page($url,$curl_data,"fbk.ck");
                } catch (Exception $e) {
                  echo 'Caught exception: ' .  $e->getMessage() . "\n";
                }

		
		$signed_request = substr($response, strpos($response, "signed_request")-500);
		$signed_request = substr($signed_request, strpos($signed_request, "value=") + 7);
		$signed_request = substr($signed_request, 0, strpos($signed_request, "\""));
		//echo $signed_request;
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
function get_lex_rating($profileid)
{
		global $lex_signed_request;

		if(is_null($lex_signed_request))
		{
			$curl_data = "action=profile&profileid=" . $profileid;
			$url = 'http://apps.facebook.com/lexulous/';
			
					 
					try{
					$response = get_fb_web_page($url,$curl_data,"fbk.ck");
					} catch (Exception $e) {
					  echo 'Caught exception: ' .  $e->getMessage() . "\n";
					}

			
			$lex_signed_request = substr($response, strpos($response, "signed_request")-500);
			$lex_signed_request = substr($lex_signed_request, strpos($lex_signed_request, "value=") + 7);
			$lex_signed_request = substr($lex_signed_request, 0, strpos($lex_signed_request, "\""));
		}
		
		$paltuasub = "facebook";
		
		$url = "http://play.paltua.com/". $paltuasub . "/?action=profile&profileid=" . $profileid;
		$curl_data = "signed_request=" . $lex_signed_request;
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
		  echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
				

		preg_match("/Rating:\s(?P<rating>\d+)\S*span>/",$response,$match);
		
		return $match['rating'];

}


function get_ws_rating($profileid)
{
		global $ws_signed_request;

		if(is_null($ws_signed_request))
		{
			$curl_data = "action=profile&profileid=" . $profileid;
			$url = 'http://apps.facebook.com/wordscraper/';
			
					 
					try{
					$response = get_fb_web_page($url,$curl_data,"fbk.ck");
					} catch (Exception $e) {
					  echo 'Caught exception: ' .  $e->getMessage() . "\n";
					}

			
			$ws_signed_request = substr($response, strpos($response, "signed_request")-500);
			$ws_signed_request = substr($ws_signed_request, strpos($ws_signed_request, "value=") + 7);
			$ws_signed_request = substr($ws_signed_request, 0, strpos($ws_signed_request, "\""));
		}
		
		$paltuasub = "wordscraper";
		
		$url = "http://play.paltua.com/". $paltuasub . "/?action=profile&profileid=" . $profileid;
		$curl_data = "signed_request=" . $ws_signed_request;
		try{
			$response = get_web_page($url,$curl_data);
		} catch (Exception $e) {
		  echo 'Caught exception: ' .  $e->getMessage() . "\n";
		}
				

		preg_match("/Rating:\s(?P<rating>\d+)\S*span>/",$response,$match);
		
		return $match['rating'];

}

function time_diff_conv($start, $s) {
    $t = array( //suffixes
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
    );
    $s = abs($s - $start);
    foreach($t as $key => &$val) {
        $$key = floor($s/$val);
        $s -= ($$key*$val);
        $string .= ($$key==0) ? '' : $$key . "$key ";
    }
    return $string . $s. 's';
}

$start = time();

$header = get_access_token('fbk.ck');
preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match);;
$url  = "https://graph.facebook.com/234142543348049/feed?access_token=" . $match['access_token'] ;

//echo $url;
$response = get_member_list($url);
$json = $response['content'];
echo $json;
$obj = json_decode($json,true);
$obj = $obj['data'];

print_r($obj);
//usort($obj, 'cmp');


// We give the path to our file here
// $workbook = new Spreadsheet_Excel_Writer('EventAttendees_Rating.xls');
// $worksheet =& $workbook->addWorksheet('TournamentAttendeeInfo');

// $worksheet->setInputEncoding('UTF-8');

// Creating the format
// $format_bold =& $workbook->addFormat();
// $format_bold->setBold();

// $numFormat =& $workbook->addFormat();
// $numFormat->setNumFormat('0');

// $worksheet->setColumn(0,0, 40);
// $worksheet->setColumn(1,1, 20);
// $worksheet->setColumn(2,3, 70);

// $worksheet->write(0, 0, 'Name',$format_bold);
// $worksheet->write(0, 1, 'Id',$format_bold);
// $worksheet->write(0, 2, 'Lexulous Link',$format_bold);
// $worksheet->write(0, 3, 'Wordscraper Link',$format_bold);
// $worksheet->write(0, 4, 'Lexulous Rating',$format_bold);
// $worksheet->write(0, 5, 'Wordscraper Rating',$format_bold);

// $format_admin =& $workbook->addFormat();
// $format_admin->setBold();
// $format_admin->setFgColor('yellow');

// $count = 1;

// foreach($obj as $row) {

	
	// $lexrating = get_lex_rating($row['id']);
	// $lexrating = is_null($lexrating)?"-1":$lexrating;
	
	// if($lexrating=="-1")
	// {
		// $lexrating = get_lex_rating($row['id']);
		// $lexrating = is_null($lexrating)?"-1":$lexrating;
	// }
		
	// $wsrating = get_ws_rating($row['id']);
	// $wsrating = is_null($wsrating)?"-1":$wsrating;
	
	// if($wsrating=="-1")
	// {
		// $wsrating = get_ws_rating($row['id']);
		// $wsrating = is_null($wsrating)?"-1":$wsrating;
	// }

	
	// $worksheet->write($count, 0, $row['name']);
	// $worksheet->write($count, 1,"'" . $row['id']);
	// $worksheet->writeUrl($count, 2, 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['id']);
	// $worksheet->writeUrl($count, 3, 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['id']);
	// $worksheet->write($count, 4, $lexrating);
	// $worksheet->write($count, 5,$wsrating);
	
	// $count++;
	
// }

// $worksheet->write($count + 1, 1, "Generated at");
// $worksheet->write($count +1 , 2,gmdate("M d Y H:i:s") . " GMT/UTC");

// $end=time();

// $workbook->close();


// $mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
// try {
	
	
	  
	// $mail->AddReplyTo('kingdale16@hotmail.com', 'Dale Ross');
	// $mail->AddAddress('kingdale16@hotmail.com', 'Dale Ross');
	// $mail->SetFrom('scrabtourneyasst@dalevross.com', 'Scrabulous Tournament Assistant');
	// $mail->Subject = 'Scrabulous Tournament Group Members generated at ' . gmdate(DATE_RFC822);
	// $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
	// $mail->MsgHTML(file_get_contents('eventcontents.html'));
	// $mail->AddAttachment('tourneyasst75x75.png');      // attachment
	// $mail->AddAttachment('EventAttendees_Rating.xls'); // attachment
	// $mail->Send();
	// echo "Message Sent OK\nRuntime: " . time_diff_conv($start,$end);
// } catch (phpmailerException $e) {
  // echo $e->errorMessage(); //Pretty error messages from PHPMailer
// } catch (Exception $e) {
  // echo $e->getMessage(); //Boring error messages from anything else!
// }



?>