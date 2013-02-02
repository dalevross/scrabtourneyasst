<?php

include_once 'config.php';
require_once 'XML/Serializer.php';
require_once 'Spreadsheet/Excel/Writer.php';

if (isset($_GET['eventid']))
{

	$eventid = $_GET['eventid'];

	$header = get_access_token('fbk.ck');
	preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match);;
	$url  = "https://graph.facebook.com/" . $eventid . "/feed?access_token=" . $match['access_token'] ;
	$response = get_member_list($url);
	$json = $response['content'];
	//echo $json;
	$obj = json_decode($json,true);
	print_r($obj);
	//$obj = $obj['data'];

	//usort($obj, 'cmp');


	
}
else
{
	echo "An event id is required";

}



?>