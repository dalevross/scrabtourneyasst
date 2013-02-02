<?php

include_once 'config.php';
require_once "XML/Unserializer.php";

function logintoFb($cookiejar)
{
	$username = "kingdale16@hotmail.com";
	$password = "Rtau66cursion";

	// access to facebook home page (to get the cookies)
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "http://www.facebook.com");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$curlData = curl_exec($curl);
	curl_close($curl);
	//echo $curlData;
	// do get some parameters for login to facebook
	$charsetTest = substr($curlData, strpos($curlData, "name=\"charset_test\""));
	$charsetTest = substr($charsetTest, strpos($charsetTest, "value=") + 7);
	$charsetTest = substr($charsetTest, 0, strpos($charsetTest, "\""));

	$locale = substr($curlData, strpos($curlData, "name=\"locale\""));
	$locale = substr($locale, strpos($locale, "value=") + 7);
	$locale = substr($locale, 0, strpos($locale, "\""));


	$lsd = substr($curlData, strpos($curlData, "name=\"lsd\""));
	$lsd = substr($lsd, strpos($lsd, "value=") + 7);
	$lsd = substr($lsd, 0, strpos($lsd, "\""));

	
	
	//echo $locale;
	//echo $lsd;

	// do login to facebook
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/login.php?login_attempt=1");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "charset_test=" . $charsetTest . "&locale=" . $locale . "&non_com_login=&email=" . $username . "&pass=" . $password . "&charset_test=" . $charsetTest . "&lsd=" . $lsd . "&persistent=1");
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$curlData = curl_exec($curl);
	curl_close($curl);
	
	    
	//fb_dtsg
	
	
	/*
	$post_form_id = substr($curlData, strpos($curlData, "name=\"post_form_id\"")-100);
	$post_form_id = substr($post_form_id, strpos($post_form_id, "value=") + 7);
	$post_form_id = substr($post_form_id, 0, strpos($post_form_id, "\""));
	
	
	$lsd = substr($curlData, strpos($curlData, "name=\"lsd\"")-20);
	$lsd = substr($lsd, strpos($lsd, "value=") + 7);
	$lsd = substr($lsd, 0, strpos($lsd, "\""));
	*/
	$fb_dtsg = substr($curlData, strpos($curlData, "name=\"fb_dtsg\"")-100);
	$fb_dtsg = substr($fb_dtsg, strpos($fb_dtsg, "value=") + 7);
	$fb_dtsg = substr($fb_dtsg, 0, strpos($fb_dtsg, "\""));
	
	
	$nh = substr($curlData, strpos($curlData, "name=\"nh\"")-60);
	$nh = substr($nh, strpos($nh, "value=") + 7);
	$nh = substr($nh, 0, strpos($nh, "\""));
	
	
	echo "fb_dtsg=$fb_dtsg\n";
	echo "nh=$nh\n";		
	// Review Recent Login
	$curlData="";
	$curl = curl_init();
	//curl_setopt($curl, CURLOPT_URL, "http://www.dalevross.com/checkpoint/");
	curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/checkpoint/");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&lsd=" . $lsd . "&nh=" . $nh . "&submit[Continue]=Continue");
	curl_setopt($curl, CURLOPT_POSTFIELDS, "fb_dtsg=" . $fb_dtsg . "&nh=" . $nh . "&submit[Continue]=Continue");
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)");
	$curlData = curl_exec($curl);
	curl_close($curl);
	
	
	// Allow Login
	$curlData="";
	$curl = curl_init();
	//curl_setopt($curl, CURLOPT_URL, "http://www.dalevross.com/checkpoint/");
	curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/checkpoint/");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&lsd=" . $lsd . "&nh=" . $nh . "&submit[This is Okay]=This is Okay");
	curl_setopt($curl, CURLOPT_POSTFIELDS, "fb_dtsg=" . $fb_dtsg . "&nh=" . $nh . "&submit[This is Okay]=This is Okay");
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$curlData = curl_exec($curl);
	curl_close($curl);

	
	
	/*$post_form_id = substr($curlData, strpos($curlData, "name=\"post_form_id\"")-100);
	$post_form_id = substr($post_form_id, strpos($post_form_id, "value=") + 7);
	$post_form_id = substr($post_form_id, 0, strpos($post_form_id, "\""));
	
	
	$lsd = substr($curlData, strpos($curlData, "name=\"lsd\"")-20);
	$lsd = substr($lsd, strpos($lsd, "value=") + 7);
	$lsd = substr($lsd, 0, strpos($lsd, "\""));
	
	$nh = substr($curlData, strpos($curlData, "name=\"nh\"")-100);
	$nh = substr($nh, strpos($nh, "value=") + 7);
	$nh = substr($nh, 0, strpos($nh, "\""));
	*/
	//echo $nh;

	// do name machine Array ( ) Array ( [post_form_id] => c742f761d0e5868190ac4c0129933783 [lsd] => DZKp8 [machine_name] => e.g. Home or Library [submit] => Array ( [Save Device] => Save Device ) ) 
    //                 Array ( ) Array ( [post_form_id] => c742f761d0e5868190ac4c0129933783 [machine_name] => scrabtourneyasst [lsd] => GQD8Z [submit] => Array ( [Save Device] => Save Device ) ) 
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/checkpoint/");
	//curl_setopt($curl, CURLOPT_URL, "http://www.dalevross.com/checkpoint/");
	//curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/loginnotify/setup_machine.php?persistent=1");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//submit[Save Device]
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&email=" . $username . "&machine_name=" . "scrabtourneyasst" . "&remembercomputer=" . "1" . "&lsd=" . $lsd . "&persistent=1");
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&machine_name=" . "scrabtourneyasst" . "&lsd=" . $lsd .  "&nh=" . $nh  . "&submit[Save Device]=Save Device");
	curl_setopt($curl, CURLOPT_POSTFIELDS,  "fb_dtsg=" . $fb_dtsg . "&machine_name=" . "scrabtourneyasst" .  "&nh=" . $nh  . "&submit[Save Device]=Save Device");
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$curlData = curl_exec($curl);
	curl_close($curl);
	
		
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/checkpoint/");
	//curl_setopt($curl, CURLOPT_URL, "http://www.dalevross.com/checkpoint/");
	//curl_setopt($curl, CURLOPT_URL, "https://login.facebook.com/loginnotify/setup_machine.php?persistent=1");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//submit[Save Device]
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&email=" . $username . "&machine_name=" . "scrabtourneyasst" . "&remembercomputer=" . "1" . "&lsd=" . $lsd . "&persistent=1");
	//curl_setopt($curl, CURLOPT_POSTFIELDS, "post_form_id=" . $post_form_id . "&machine_name=" . "scrabtourneyasst" . "&lsd=" . $lsd .  "&nh=" . $nh  . "&submit[Save Device]=Save Device");
	curl_setopt($curl, CURLOPT_POSTFIELDS,  "fb_dtsg=" . $fb_dtsg . "&machine_name=" . "scrabtourneyasst" .  "&nh=" . $nh  . "&submit[Save Device]=Save Device");
	curl_setopt($curl, CURLOPT_ENCODING, "");
	curl_setopt($curl, CURLOPT_COOKIEFILE,$cookiejar);
	curl_setopt($curl, CURLOPT_COOKIEJAR,$cookiejar);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0");
	$curlData = curl_exec($curl);
	curl_close($curl);
	
	
	//echo $post_form_id;
	echo $curlData;
	
	
}

logintoFb("fbk.ck");

//phpinfo();

/*echo iconv("UTF-16","UTF-8","\u00257C");

//("\u00257C");
$header = get_access_token('fbk.ck');
echo "<xmp>";
//echo $header['content'];
echo "</xmp>";
*/
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<script type="text/javascript" src="jquery-1.4.3.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

  /*var g_max_size = 50;

  $("img.small").each(function(i) {
    var w = g_max_size;
    var h = Math.ceil($(this).height() / $(this).width() * g_max_size);
	$(this).css({ height: h, width: w });
  });*/

  $("tr.admin").hover(function(){
    $(this).addClass("admin_hover");
	$(this).removeClass("admin_leave");
  },function(){
    $(this).addClass("admin_leave");
	$(this).removeClass("admin_hover");
  });
 
  $("tr.reg").hover(function(){
    $(this).addClass("reg_hover");
	$(this).removeClass("reg_leave");
  },function(){
    $(this).addClass("reg_leave");
	$(this).removeClass("reg_hover");
  });
  
  $("tr").mouseover(function(){
	var max_size = 200;	
    var w = max_size;
    var h = Math.ceil($('.small',this).height() / $('.small',this).width() * max_size);
	$('.small',this).stop().animate({ height: h, width: w },"slow");
	if($(this).find('span.links').length==0)
	{
		pid = $('span.pid',this).text();
		lexlink = "http://apps.facebook.com/lexulous/?action=profile&profileid=" +  pid;
		wslink = "http://apps.facebook.com/wordscraper/?action=profile&profileid=" +  pid;
		lexlink = "<a href='" + lexlink + "' target='_blank'>" + lexlink + "</a>";
		wslink = "<a href='" + wslink + "' target='_blank'>" + wslink + "</a>";		
		$('span.adminstat',this).after("<span class='links'><br/> Lexulous Link: " + lexlink + "<br/>Worscraper Link: " + wslink + "</span>");
			
	}
	//$('span.links',this).stop().slideDown("slow")'span.adminstat';	
  });
  
  $("tr").mouseout(function(){
	var max_size = 50;	
    var w = max_size;
    var h = Math.ceil($('.small',this).height() / $('.small',this).width() * max_size);
	$('.small',this).stop().animate({ height: h, width: w },"slow");
	//$('span.links',this).stop().slideUp("slow");
	//$('span.links',this).remove();
  });
  
  $("td.name").dblclick(function(){
	pid = $(this).find('.pid').text();
	if(($(this).find('.loading').length==0) && ($(this).find('.moreinfo').length==0))
	{
	   $(this).append("<span class='loading'></br>Loading please wait....</span>");
	
	   $.ajax({url:'moreinfo.php',context:this,data:({ profileid: pid}),success:
	   function(data){
		 name = $(this).find('.pname').text();
		 data = data.replace(/namepat/gi,name + " is ");
		 $(this).find('.loading').remove().end().append("<span class='moreinfo'><br/>" + data + "</span>");    
		 
	   }});
	   
	  
	}
	else
	{
		if($(this).find('.moreinfo').length>0)
		{
			$(this).find('.loading').remove().end().find('.moreinfo').remove();
		}
	}
  });
  
  $('#outer').mouseleave(function() {
	$('#log').append('Handler for .mouseout() called.');
  });
  
});
</script>
<style type="text/css">
.admin_leave
{
  background-color:yellow;
}
.admin_hover
{
  background-color:#A8F7BD;
}
.reg_leave
{
  background-color:#FFFFFF;
}
.reg_hover
{
  background-color:#A6B9F5;
}
table
{	
	border-collapse:collapse;
	border:1px none black;
	
}
th
{
	background-color:green;
	color:white;
}
td,.div
{
border-style:solid;
border-width:1px;
border-top-style:solid;
border-right-style:none;
border-bottom-style:solid;
border-left-style:none;
border-color:green;
}
.div
{
padding-top:25px;
padding-bottom:25px;
padding-right:50px;
padding-left:50px;
}
</style>
</head>
<body>
<!--<div id="outer">
  Outer
  <div id="inner">
    Inner
  </div>
</div>
<div id="other">
  Trigger the handler
</div>
<div id="log"></div>-->
<div style="background:#ff000f;">
<div style="margin:50px; background:#cccccc; border:#000000 2px dashed;">
    Text inside element
</div>
<div style="padding:25px; background:#cccccc; border:#000000 2px dashed;">
    Text inside element
</div>
</div>
</body>