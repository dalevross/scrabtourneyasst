<?php

include_once 'config.php';
require_once 'XML/Serializer.php';
require_once 'Spreadsheet/Excel/Writer.php';


//$result = get_access_token();
//$arr = explode("=", $result);
//echo $arr[1];

//$url  = "https://graph.facebook.com/24006165517/members?access_token=" . "2227470867%7C2.y1C3pSoGipa_y_D01HuFiA__.3600.1290499200-593170373%7Cw3bI6-KvIat1aaRUw6H5ZE_eyHE";//$arr[1];
//$reponse = get_member_list($url);
//$header = get_url('fbk.ck','https://graph.facebook.com/oauth/authorize?client_id=' .FACEBOOK_APP_ID. '&redirect_uri=http://www.facebook.com/connect/login_success.html&type=user_agent&display=page');
$header = get_access_token('fbk.ck');
//echo "test";
//echo "<!--";
preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match);;
//echo "-->";
//print_r($header['response']);

//echo 'Access token: ' . $match['access_token'];
$url  = "https://graph.facebook.com/24006165517/members?access_token=" . $match['access_token'] ;
$response = get_member_list($url);
$json = $response['content'];

$obj = json_decode($json,true);
$obj = $obj['data'];

usort($obj, 'cmp');

// We give the path to our file here
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);
$workbook->send('Members.xls');

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
$worksheet->write(0, 4, 'Administrator',$format_bold);
/*
$worksheet->write(1, 0, 'John Smith');
$worksheet->write(1, 1, 30);
$worksheet->write(2, 0, 'Johann Schmidt');
$worksheet->write(2, 1, 31);
$worksheet->write(3, 0, 'Juan Herrera');
$worksheet->write(3, 1, 32);
*/

$format_admin =& $workbook->addFormat();
$format_admin->setBold();
$format_admin->setFgColor('yellow');

$count = 1;

foreach($obj as $row) {
	if(array_key_exists('administrator', $row))
	{
		$worksheet->write($count, 0, $row['name'],$format_admin);
		$worksheet->write($count, 1,"'" . $row['id'],$format_admin);
		$worksheet->writeUrl($count, 2, 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['id'],'',$format_admin);
		$worksheet->writeUrl($count, 3, 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['id'],'',$format_admin);
		$worksheet->write($count, 4,'Y',$format_admin);
	}
	else
	{
		$worksheet->write($count, 0, $row['name']);
		$worksheet->write($count, 1,"'" . $row['id']);
		$worksheet->writeUrl($count, 2, 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['id']);
		$worksheet->writeUrl($count, 3, 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['id']);
		$worksheet->write($count, 4,'N');
	}

	$count++;
}

$workbook->close();



?>