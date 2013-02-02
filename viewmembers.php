<?php

include_once 'config.php';
require_once 'XML/Serializer.php';




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

$serializer_options = array (
   'addDecl' => TRUE,
   'encoding' => 'UTF-8',
   'indent' => '  ',
   'rootName' => 'Members',
   'defaultTagName' => 'GroupMember',
   'tagMap'         => array( 'name' => 'label','id'=>'data' )
);

$obj = json_decode($json,true);
$obj = $obj['data'];

usort($obj, 'cmp');

$obj = array_merge(array(0=>array('name'=>'You','id'=>'1')),$obj);
 
$xml = json_to_xml($obj,$serializer_options);

echo $xml;


?>