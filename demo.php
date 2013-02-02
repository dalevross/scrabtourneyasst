<?php

include_once './src/facebook.php';

include_once 'config.php';

?>


<html>
<head>
<script type="text/javascript" src="jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="bridge/FABridge.js"></script>
<script type="text/javascript" src="swfobject.js"></script>
<script type="text/javascript">
$(document).ready(function(){

	   // jQuery functions go here...

	});

//global variable, holds reference to the Flex application
var flexApp;  
  
var initCallback = function() {  
   flexApp = FABridge.flex.root();
  
	      
   return;  
}  
// register the callback to load reference to the Flex app
FABridge.addInitializationCallback( "flex", initCallback ); 
</script>
<style type=text/css>
body { padding-left: 0px; padding-right: 0px; padding-top: 0px; padding-bottom: 0px; margin: 0px; width: 100%; height: 100%;
overflow: hidden;
text-align: center;
}
#myContent {
margin-left: auto;
margin-right: auto;
width: 50em;
text-align: left;
}
</style> 
</head>

<body>
<?php

$facebook = new Facebook(array(
    'appId'  => FACEBOOK_APP_ID,
    'secret' => FACEBOOK_SECRET_KEY,
    'cookie' => true
    ,'domain' => 'dalevross.com'
));

$me = array("id"=>"547276341");
//$me = array("id"=>"687004264");

$me = array("id"=>"100001252263396");
//$scrab = $facebook->api('/1374234116');
//$friendwith = $facebook->api(Array('method'=>'friends.arefriends','uids1'=>'1374234116','uids2'=>$me['id']));
//$text = "<a href=". $me['link'] ." target='_blank'>". $me['name'] .  "</a>";// is " .  (($friendwith[0]['are_friends']=='1')?"":"not ") . "friends with <a href=". $scrab['link'] ." target='_blank'>". $scrab['name'] .  "</a>";
if (!function_exists('mcrypt_encrypt')) {
	$text =('Page does not have mcrypt_encrypt');
}
else
	$text =('Page has mcrypt_encrypt');

$updated = date("l, F j, Y", strtotime($me['updated_time']));
echo "<iframe src=\"http://www.facebook.com/widgets/like.php?href=http://apps.facebook.com/scrabtourneyasst\"";
echo "scrolling=\"no\" frameborder=\"0\"";
echo "style=\"border:none; width:450px; height:20px\"></iframe>";
echo "<table id='tab' width='100%'><tr>";
echo "<td align='center'>Hello " . $me['name'] . "</td>";
echo "<td align='center'>" . $text ."</td>";
echo "</tr></table>"; 
/*
echo "<table width='100%' height='100%' cellspacing='0' cellpadding='0'><tr><td valign='top'>";
echo "<object id='mySwf' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab' height='100%' width='100%'>";
echo "<param name='src' value='main.swf'/>";
echo "<param name='flashVars' profileid='" . $me['id'] ."'/>";
echo "<embed name='mySwf' src='main.swf' pluginspage='http://www.adobe.com/go/getflashplayer' height='100%' width='100%' flashVars='profileid=" .  $me['id'] . "'/>";
echo "</object>";
echo "</td></tr></table>";
*/
?>
	<script type="text/javascript">
	var flashvars = {
	  profileid: "<?php echo $me['id']; ?>"
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("main.swf", "myContent", "750", "100%", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>
	
	<div id="myContent">
	  <p>Alternative content</p>
	</div>

	
	<?php
	//echo "</td></tr></table>";


?>
</body>
</html>
