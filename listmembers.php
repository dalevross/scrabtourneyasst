<?php

include_once 'config.php';







//$result = get_access_token();
//$arr = explode("=", $result);
//echo $arr[1];

//$url  = "https://graph.facebook.com/24006165517/members?access_token=" . "2227470867%7C2.y1C3pSoGipa_y_D01HuFiA__.3600.1290499200-593170373%7Cw3bI6-KvIat1aaRUw6H5ZE_eyHE";//$arr[1];
//$reponse = get_member_list($url);
//$header = get_url('fbk.ck','https://graph.facebook.com/oauth/authorize?client_id=' .FACEBOOK_APP_ID. '&redirect_uri=http://www.facebook.com/connect/login_success.html&type=user_agent&display=page');
$header = get_access_token('fbk.ck');
//echo "test";
//echo "<!--";
preg_match(" /access_token=(?P<access_token>.*?)&/",$header['content'],$match);
//echo "<!--";
//print_r($header);
//echo "-->";
//echo 'Access token: ' . $match['access_token'];
$url  = "https://graph.facebook.com/24006165517/members?access_token=" . $match['access_token'] ;
//echo $url;
//$url  = "https://graph.facebook.com/20570624656/members?access_token=" . $match['access_token']; 
$response = get_member_list($url);
$json = $response['content'];
//echo $json;
$obj = json_decode($json,true);
$obj = $obj['data'];

if(isset($_GET["name"]) )
{
	
	$my_value = trim($_GET["name"]);
	$type = "";
	if(isset($_GET["type"]) )
	{
		$type = trim($_GET["type"]);		
	}
	if($my_value!="")
	{
		if($type!="")
		{
			$type = trim($_GET["type"]);
			if($type=="cont")
			{
				$obj = array_filter($obj,  create_function('$x', 'return (stripos($x["name"],"' . $my_value. '")!==false);'));
			}
			else if($type=="str")
			{
				$obj = array_filter($obj,  create_function('$x', 'return (stripos($x["name"],"' . $my_value. '")===0);'));			
			}
			else if($type=="eq")
			{
				$obj = array_filter($obj,  create_function('$x', 'return (strcasecmp($x["name"],"' . $my_value. '")===0);'));			
			}
			else if($type=="end")
			{
				$obj = array_filter($obj,  create_function('$x', 'return (endsWith($x["name"],"' . $my_value. '",false));'));			
			}
		}
		else
		{
			$obj = array_filter($obj,  create_function('$x', 'return (stripos($x["name"],"' . $my_value. '")!==false);'));
		}
	}
	else if($type=="eq")
	{
		$obj = array_filter($obj,  create_function('$x', 'return (strcasecmp($x["name"],"' . $my_value. '")===0);'));			
	}
}

usort($obj, 'cmp');
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="jquery.bubblepopup.v2.3.1.css" rel="stylesheet" type="text/css" />		
<script type="text/javascript" src="jquery-1.4.3.min.js"></script>
<script src="jquery.bubblepopup.v2.3.1.min.js" type="text/javascript"></script>	
<script type="text/javascript">
$(document).ready(function(){

	liveFilter = function(){ 
	var name = $.trim( $('#txtname').val().toLowerCase() ); 
	//console.log(name);
	var type = $("input:radio[name=type]:checked").val();
	//console.log(type);
	
	if( !name ) return $('tbody tr').show(); 
	else return $('tbody tr').each(function(){ 
	var row = $(this), text = row.find('.pname').text().toLowerCase();
	//+ row.find('.pid').text().toLowerCase(); 
	//console.log(text);
	if( (text.indexOf(name) == -1) && type == "cont")
		row.hide();
	else if( (text.indexOf(name) != 0) && type == "str")
		row.hide();
	else if( !((text.lastIndexOf(name) == (text.length-name.length)) && (text.lastIndexOf(name)!= -1)) && type == "end")
		row.hide();
	else if((type == "eq") && !((text.indexOf(name) == 0) && (name.length==text.length)))
		row.hide();
	else
		row.show(); 
	});
	}
	$('#txtname').keydown(function(){ setTimeout(liveFilter, 5); });
	$("input:radio[name=type]").change(liveFilter);
	
  /*var g_max_size = 50;
  $("img.small").each(function(i) {
    var w = g_max_size;
    var h = Math.ceil($(this).height() / $(this).width() * g_max_size);
	$(this).css({ height: h, width: w });
  });*/
  
  <?php
	/*if(isset($_GET["name"]) )
	{	  
		echo '$("#txtname").val("' . $_GET["name"] . '");';
	}
	  
	if(isset($_GET["type"]) )
    {
		//echo '$("[name=type]").filter("[value="' . $_GET["type"] .'"]").attr("checked","checked");';
		echo  '$("input:radio[name=type]").val(["' . $_GET["type"] . '" ]);';
	}
	*/
  
  ?>

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
  
  $("tr.memrow").mouseenter(function(){
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
	//$('span.links',this).stop().slideDown("slow");	
  });
  
  $("tr.memrow").mouseleave(function(){
	var max_size = 50;
    var w = max_size;
    var h = Math.ceil($('.small',this).height() / $('.small',this).width() * max_size);
	$('.small',this).stop().animate({ height: h, width: w },"slow");
	//$('span.links',this).stop().slideUp("slow");
	$('span.links',this).remove();
  });
  
   $("#lexulousclose").click(function(event){
		$('span.lexrating').RemoveBubblePopup();

   });
   
   $("#wordscraperclose").click(function(event){
		$('span.wsrating').RemoveBubblePopup();
	});
  
  $("tr").click(function(event){
	if($(event.target).is('a'))
		return;
	pid = $(this).find('.pid').text();
	name = $(this).find('.pname').text();
		 
	if($(event.target).is('span.lexrating'+pid))
	{	
		//alert($('td.name',this).outerHeight());
		if($('span.lexrating'+pid).HasBubblePopup())
		{
			$('span.lexrating'+pid).RemoveBubblePopup();
		}
		$('span.lexrating'+pid).CreateBubblePopup({position : 'left',align: 'center',innerHtml: '<img src="loading.gif" style="border:0px; vertical-align:middle; margin-right:10px; display:inline;" />loading!'
		,innerHtmlStyle: {color:'#FFFFFF','text-align':'center'},themeName:'azure',selectable:true
		,themePath: 	'jquerybubblepopup-theme',tail:{align: 'left'},alwaysVisible: true,distance:'20px'});	
		$.ajax({url:'gamestable.php',context:this,data:({ profileid: pid,game:'lexulous'}),success:
	   function(data){
		  data = data.replace(/namepat/gi,name + "'s recent lexulous games");		 
		  $('span.lexrating'+pid).SetBubblePopupInnerHtml(data, true);	 
	   }});
		return;
	}
	if($(event.target).is('span.wsrating'+pid))
	{
		if($('span.wsrating'+pid).HasBubblePopup())
		{
			$('span.wsrating'+pid).RemoveBubblePopup();
		}
		
	    $('span.wsrating'+pid).CreateBubblePopup({position : 'left',align: 'center',innerHtml: '<img src="loading.gif" style="border:0px; vertical-align:middle; margin-right:10px; display:inline;" />loading!'
		 ,innerHtmlStyle: {color:'#FFFFFF','text-align':'center'},themeName:'orange',selectable:true
		 ,themePath: 	'jquerybubblepopup-theme',tail:{align: 'left'},alwaysVisible: true,distance:'20px'});	
		$.ajax({url:'gamestable.php',context:this,data:({ profileid: pid,game:'wordscraper'}),success:	   
		function(data){
			 data = data.replace(/namepat/gi,name + "'s recent wordscraper games");
			 $('span.wsrating'+pid).SetBubblePopupInnerHtml(data, true);
	   }});
		return;
	}
	
	if(($(this).find('.loading').length==0) && ($(this).find('.moreinfo').length==0))
	{
	   $('td.name',this).append("<span class='loading'></br>Loading please wait....</span>");
	
	   $.ajax({url:'moreinfo.php',context:this,data:({ profileid: pid}),success:
	   function(data){
		 data = data.replace(/namepat/gi,name + " is ");
		 $('td.name',this).find('.loading').remove().end().append("<span class='moreinfo'><br/>" + data + "</span>");    
		 
	   }});
	   
	   /*if($(this).HasBubblePopup())
	   {
			$(this).RemoveBubblePopup();
	   }
	   else
	   {
		   $(this).CreateBubblePopup({position : 'left',align: 'center',innerHtml: '<div id="wsbub"><img src="loading.gif" style="border:0px; vertical-align:middle; margin-right:10px; display:inline;" />loading!</div>'
			 ,innerHtmlStyle: {color:'#FFFFFF','text-align':'center'},themeName:'orange',selectable:true
			 ,themePath: 	'jquerybubblepopup-theme',tail:{align: 'left'},alwaysVisible: true,distance:'20px'});	
			$.ajax({url:'gamestable.php',context:this,data:({ profileid: pid,game:'wordscraper'}),success:	   
			function(data){
				 data = data.replace(/namepat/gi,name + "'s recent wordscraper games");
				 //$(this).SetBubblePopupInnerHtml(data, true);
				  $('#wsbub').html(data);
		   }});
		   
		   $(this).CreateBubblePopup({position : 'right',align: 'center',innerHtml: '<div id="lexbub"><img src="loading.gif" style="border:0px; vertical-align:middle; margin-right:10px; display:inline;" />loading!</div>'
			,innerHtmlStyle: {color:'#FFFFFF','text-align':'center'},themeName:'azure',selectable:true
			,themePath: 	'jquerybubblepopup-theme',tail:{align: 'left'},alwaysVisible: true,distance:'20px'});	
			$.ajax({url:'gamestable.php',context:this,data:({ profileid: pid,game:'lexulous'}),success:
		   function(data){
			  data = data.replace(/namepat/gi,name + "'s recent lexulous games");		 
			  //$(this).SetBubblePopupInnerHtml(data, true);
			   $('#lexbub').html(data);
		   }});
	  }*/
	  
	}
	else
	{
		if($(this).find('.moreinfo').length>0)
		{
			$(this).find('.loading').remove().end().find('.moreinfo').remove();
		}
	}
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
table.mem
{	
	border-collapse:collapse;
	border:1px none black;
	width:786;
	
}
th.mem
{
	background-color:green;
	color:white;
}
td.mem
{
	border-style:solid;
	border-width:1px;
	border-top-style:solid;
	border-right-style:none;
	border-bottom-style:solid;
	border-left-style:none;
	border-color:green;
}

td
{
	text-align:left;
}

td.name
{
	width:584;
}
</style>
</head>
<body>

<div align='center'>
<form name="input">
Name: <input type="text" id="txtname" name="name" /><br/>
<input type="radio" name="type" value="cont" checked="1"/> Contains
&nbsp&nbsp&nbsp <input type="radio" name="type" value="str" /> Starts With
&nbsp&nbsp&nbsp <input type="radio" name="type" value="end" /> Ends With
&nbsp&nbsp&nbsp <input type="radio" name="type" value="eq" /> Equals
<br/>
<!--<input type="submit" value="Submit" /><br/>-->
<div class='srch'>
All searches are case insensitive
<div>
</form>
</div>
<?php
//echo "<table align='center' border='1' style='border-collapse:collapse'><tr><th>Name</th><th>Picture</th><th>Id (click id for more/less)</th><th>Lex Link</th><th>WS Link</th><th>Administrator?</th></tr>";
//echo "<table align='center' border='1' style='border-collapse:collapse'><tr><th>Name (hover for game links)</br></th><th>Picture</th><th>Id (click id for more/less)</th><th>Administrator?</th></tr>";
echo "<table align='center' class='mem'><thead><tr><th class='mem' colspan=2>Player Info (hover for game links. Click row for additional info)</br>After additional info is loaded, click rating for history.</th></tr></thead>";
echo "<tbody>";
foreach($obj as $row) {

	$lexlink = 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['id'];
	$wslink = 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['id'];
	if(array_key_exists('administrator', $row))
	{
		echo "<tr class='admin admin_leave memrow'>";	
		echo "<td class='pic mem'>" . "<img src='http://graph.facebook.com/" .  $row['id'] . "/picture?type=large' alt='" . $row['name'] ."'/>" . "</td>"; 
		echo "<td class='name mem' style='white-space:nowrap'><span class='pname'>" . "<a href='http://www.facebook.com/profile.php?id=" . $row['id'] . "' target='_blank'>" . $row['name'] . "</a></span></br>Profile Id: <span class='pid'>" . $row['id'] . "</span><span class='adminstat'></br>Administrator: Yes</span></td>";
		//echo "<td class='pid' style='white-space:nowrap;cursor:hand'><span class='pid'>" . $row['id'] . "</span></td>";
		//echo "<td>" . "<a href='" . $lexlink . "' target='_blank'>" . $lexlink . "</a>" . "</td>";
		//echo "<td>" . "<a href='" . $wslink . "' target='_blank'>" . $wslink . "</a>" . "</td>";
		//echo "<td>" . 'Yes' . "</td>";
	}
	else
	{
		echo "<tr class='reg reg_leave memrow'>";	
		echo "<td class='pic mem'>" . "<img class='small' src='http://graph.facebook.com/" .  $row['id'] . "/picture?type=large' alt='" . $row['name'] ."' width='50'/>" . "</td>"; 
		echo "<td class='name mem' style='white-space:nowrap'><span class='pname'>" . "<a href='http://www.facebook.com/profile.php?id=" . $row['id'] . "' target='_blank'>" . $row['name'] . "</a></span></br>Profile Id: <span class='pid'>" . $row['id'] . "</span><span class='adminstat'></br>Administrator: No</span></td>";
		//echo "<td class='pid' style='white-space:nowrap;cursor:hand'><span class='pid'>" . $row['id'] . "</span></td>";
		//echo "<td>" . "<a href='" . $lexlink . "' target='_blank'>" . $lexlink . "</a>" . "</td>";
		//echo "<td>" . "<a href='" . $wslink . "' target='_blank'>" . $wslink . "</a>" . "</td>";
		//echo "<td>" . 'No' . "</td>";
	}
	echo "</tr>";
	
}
echo "</tbody>";
echo "</table>";
echo "</body>";


?>