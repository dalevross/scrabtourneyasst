<?php

require './src/facebook.php';

//
// search an array using a callback function
// returns the key if found, FALSE if not
function array_usearch($callback, $needle, $haystack, $strict = false) {
    // loop through the array
    foreach ($haystack as $key => $value) {
        if (!$strict && $callback($value) == $needle ||
            $strict && $callback($value) === $needle) return $key;
    }
    // not found
    return false;
}

function d($d){
	echo '<pre>';
	print_r($d);
	echo '</pre>';
}
  

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '163757063643167',
  'secret' => 'bf6ae88c03b5ff3b8d7f7a68e04517ae',
  'cookie' => true,
));

// We may or may not have this data based on a $_GET or $_COOKIE based session.
//
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.
/*$uid = $facebook->getUser();
$url = $facebook->getLoginUrl();


// login or logout url will be needed depending on current user state.
if ($uid) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

// This call will always work since we are fetching public data.
$naitik = $facebook->api('/naitik');
$me = $facebook->api('/me');
*/
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=8." />
  <script type="text/javascript" src="swfobject.js"></script>
    <title>Scrab Tourney Demo</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <!--
      We use the JS SDK to provide a richer user experience. For more info,
      look here: http://github.com/facebook/connect-js
    -->
    
	<div id="fb-root"></div>
    <script type="text/javascript" src="http://connect.facebook.net/en_US/all.js"></script>
     <script type="text/javascript">
       FB.init({
         appId  : '<?=$fbconfig['appid']?>',
         status : true, // check login status
         cookie : true, // enable cookies to allow the server to access the session
         xfbml  : true  // parse XFBML
       });
       
     </script>


   
    <?php if (true): ?>
   
    <?php

	function findmemberid($value) {
		return $value["id"];
	}
	
	$admin = '1016261582';
	$non_admin = '520192343';
	$general = '596718224';
	
	
	/*$you = $non_admin;
	$members = $facebook->api('24006165517/members');
	$arr = $members['data'];
	
	$key = array_usearch("findmemberid",$you, $arr);
	if ($key !== false) {
		$flashfile = array_key_exists('administrator',$arr[$key])?"ad1main.swf":"main.swf";
	} else {
		$flashfile = "g1main.swf";
	}  
	
	
	
     $scrab = $facebook->api('/1374234116');
	 
	 //echo '<br />' . $scrab['name'];    
    
     $friendwith = $facebook->api(Array('method'=>'friends.arefriends','uids1'=>'1374234116','uids2'=>$me['id']));
     echo "<br />";
     //$friendwithar = $friendwith[0];
     //print_r($friendwith[0]);
     //echo "You are " .  (($friendwith[0]['are_friends']=='1')?"":"not ") . "friends with " .  $scrab['name'];
     
	 */
	 $me = array('id' => '593170373','name'=>'Dale Ross');


    ?>
	
	<h3>Demo Version</h3>
	
    <script type="text/javascript">
	var flashvars = {
	   profileid: "<?php echo $me['id']; ?>",
	  name:"<?php echo $me['name']; ?>",
	  winningcolor: 0x8BDB40,
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("dem1main.swf", "myDemoContent", "800", "800", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>
	<script type="text/javascript" src="swfobject.js">
	alert('test');
	</script>
	<div id="myDemoContent">
      <p>Alternative content</p>
    </div>
	
	
	<h3>Admin Version</h3>
	
    <script type="text/javascript">
	var flashvars = {
	  profileid: "<?php echo $me['id']; ?>",
	  name:"<?php echo $me['name']; ?>"
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("ad1main.swf", "myAdminContent", "800", "800", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>
	<script type="text/javascript" src="swfobject.js">
	alert('test');
	</script>
	<div id="myAdminContent">
      <p>Alternative content</p>
    </div>
	
	
	<h3>Non Admin Version</h3>
	
	<script type="text/javascript">
	var flashvars = {
	   profileid: "<?php echo $me['id']; ?>",
	   name:"<?php echo $me['name']; ?>",
	   winningcolor: 0x0000FF,
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("main.swf", "myContent", "800", "800", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>
	<script type="text/javascript" src="swfobject.js">
	alert('test');
	</script>
	<div id="myContent">
      <p>Alternative content</p>
    </div>
	
	
	<h3>General Version</h3>
	<script type="text/javascript">
	var flashvars = {
	   profileid: "<?php echo $me['id']; ?>",
	  name:"<?php echo $me['name']; ?>"
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("g1main.swf", "myGeneralContent", "800", "800", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>	
	<div id="myGeneralContent">
      <p>Alternative content</p>
    </div>
	
	
    
    <?php else: ?>
    <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

    
	<script type="text/javascript">
	var flashvars = {
	  profileid: "<?php echo $me['id']; ?>"
	};
	var params = {};
	var attributes = {};

	swfobject.embedSWF("<?php echo $flashfile;?>", "myContent", "800", "800", "10.0.0", "expressInstall.swf",flashvars,params,attributes);
	</script>
	<script type="text/javascript" src="swfobject.js">
	alert('test');
	</script>
	<div id="myContent">
      <p>Alternative content</p>
    </div>
	
	
  </body>
</html>
