<?php


include_once 'config.php';
require_once 'XML/Serializer.php';
require_once 'Spreadsheet/Excel/Writer.php';
ini_set("max_execution_time", "600");


$lex_signed_request = null;
$ws_signed_request = null;

function get_game_rating($profileid,$game)
{
		global $signed_request;

		if(is_null($signed_request))
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
		}
		
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

function downloadFile( $fullPath ){

  // Must be fresh start
  if( headers_sent() )
    die('Headers Sent');

  // Required for some browsers
  if(ini_get('zlib.output_compression'))
    ini_set('zlib.output_compression', 'Off');

  // File Exists?
  if( file_exists($fullPath) ){
   
    // Parse Info / Get Extension
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);
   
    // Determine Content Type
    switch ($ext) {
      case "pdf": $ctype="application/pdf"; break;
      case "exe": $ctype="application/octet-stream"; break;
      case "zip": $ctype="application/zip"; break;
      case "doc": $ctype="application/msword"; break;
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
      case "gif": $ctype="image/gif"; break;
      case "png": $ctype="image/png"; break;
      case "jpeg":
      case "jpg": $ctype="image/jpg"; break;
      default: $ctype="application/force-download";
    }

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$fsize);
    ob_clean();
    flush();
    readfile( $fullPath );

  } else
    die('File Not Found');

}



$start = time();

if (isset($_GET['eventid']))
{

	$eventid = $_GET['eventid'];
	
	$includerating = isset($_GET['includerating']);
	$dontorder = isset($_GET['dontorder']);
	
	if($includerating)
	{
		downloadFile("EventAttendees_Rating.xls");
	}
	else
	{
		$header = get_access_token('fbk.ck');
		
		//echo '<pre>' . $header['content'] .'</pre>';
		
		preg_match("/access_token=(?P<access_token>.*?)&/",$header['response']['url'],$match);;
		
		//$graph_url = "https://graph.facebook.com/me/accounts?access_token=" . $match['access_token'] ;
       
		$url  = "https://graph.facebook.com/" . $eventid . "/attending?access_token=" . $match['access_token'] ;
		
		//print_r($header);//print_r($match);
		//echo $url;
		//return;
		
		$response = get_member_list($url);
		$json = $response['content'];
		//echo $json;
		$obj = json_decode($json,true);
		$obj = $obj['data'];

		if(!$dontorder)
		{
			usort($obj, 'cmp');
		}


		// We give the path to our file here
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
		$workbook->send('EventAttendees.xls');

		$worksheet =& $workbook->addWorksheet('Members');

		$worksheet->setInputEncoding('UTF-8');

		// Creating the format
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();

		$numFormat =& $workbook->addFormat();
		$numFormat->setNumFormat('0');

		$worksheet->setColumn(0,0, 40);
		$worksheet->setColumn(1,1, 20);
		$worksheet->setColumn(2,3, 70);

		$worksheet->write(0, 0, 'Name',$format_bold);
		$worksheet->write(0, 1, 'Id',$format_bold);
		$worksheet->write(0, 2, 'Lexulous Link',$format_bold);
		$worksheet->write(0, 3, 'Wordscraper Link',$format_bold);
		/* if($includerating)
		{
			$worksheet->write(0, 4, 'Lexulous Rating',$format_bold);
			$worksheet->write(0, 5, 'Wordscraper Rating',$format_bold);	
		} */

		$format_admin =& $workbook->addFormat();
		$format_admin->setBold();
		$format_admin->setFgColor('yellow');

		$count = 1;

		foreach($obj as $row) {
			
			/* if($includerating)
			{
				$lexrating = get_lex_rating($row['id']);
				$lexrating = is_null($lexrating)?"0":$lexrating;
				
				if($lexrating=="0")
				{
					$lexrating = get_lex_rating($row['id']);
					$lexrating = is_null($lexrating)?"0":$lexrating;
				}
					
				$wsrating = get_ws_rating($row['id']);
				$wsrating = is_null($wsrating)?"0":$wsrating;
				
				if($wsrating=="0")
				{
					$wsrating = get_ws_rating($row['id']);
					$wsrating = is_null($wsrating)?"0":$wsrating;
				}
			}
			 */
			
			$worksheet->write($count, 0, $row['name']);
			$worksheet->write($count, 1,"'" . $row['id']);
			$worksheet->writeUrl($count, 2, 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['id']);
			$worksheet->writeUrl($count, 3, 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['id']);
			/* if($includerating)
			{
				$worksheet->write($count, 4, $lexrating);
				$worksheet->write($count, 5,$wsrating);		
			} 
			*/
			$count++;
			
		}
		$end = time();
	}
	//$worksheet->write($count + 1, 0,"Runtime");
	//$worksheet->write($count + 1, 1,time_diff_conv($start,$end));
	$workbook->close();
 
}
else
{
	echo "An event id is required";

}



?>