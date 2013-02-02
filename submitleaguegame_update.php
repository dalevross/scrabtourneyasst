<?php
session_start();
//session_unset();
//session_destroy();

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');


if (!isset($_SESSION['spread_token'])) {
    if (isset($_GET['token'])) {
        // You can convert the single-use token to a session token.
        $client = new Zend_Gdata_HttpClient();
		$client->setAuthSubPrivateKeyFile('dalevrossrsakey.pem', null, true);

		$session_token = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token'],$client);
        // Store the session token in our session.
        $_SESSION['spread_token'] = $session_token;
		
    } else {
        // Display link to generate single-use token
        $googleUri = Zend_Gdata_AuthSub::getAuthSubTokenUri(
            'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
            'https://spreadsheets.google.com/feeds/', 0, 1);
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Wordscraper/Lex League Assistant</title>
		<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">
		</head>
		<body>
		<div style='width:600;text-align:center;margin-left:auto;margin-right:auto;margin-top:200px'>
		<span>This application needs to be authorized before you will be able to submit results</span>
		<br/>
        <span>Click <a href=<? echo $googleUri;?>>here</a> to authorize this application.</span>	 
		</div>
		<?php
        exit();
    }
}




function printFeed($feed)
{
        $i = 0;
        foreach($feed->entries as $entry) {
            if ($entry instanceof Zend_Gdata_Spreadsheets_CellEntry) {
                echo $entry->title->text .' '. $entry->content->text . "</br>";
            } else if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                print $i .' '. $entry->title->text .' | '. $entry->content->text . "</br>";
            } else {
                print $i .' '. $entry->title->text . "</br>";
            }
            $i++;
        }
}


function getName($id)
{
	$facebookUrl = "https://graph.facebook.com/".$id;
	$str = file_get_contents($facebookUrl);
	$result = json_decode($str);
	return trim($result->name);
} 

function cmp_entries($a, $b)
{
    return strcmp($a->title->text, $b->title->text);
}


function displayStandings()
{
	global $gdClient;
    $feed = $gdClient->getSpreadsheetFeed();
    
	//echo "== Available Spreadsheets ==</br>";
	echo '<a href="submitleaguegame.php">Submission Page</a>';
	$entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries");
	
	foreach($entries as $entry) {
        
		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = split('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8
		
		if((strpos($sheetname, 'Score Sheet')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) &&  (strpos(strtolower($sheetname), 'example')===false))
		{
			$color = (strpos(strtolower($sheetname), 'lex'))?"#2BB0E8":"red";
			$handicap = (strpos(strtolower($sheetname), 'handicap')!==false);
			?>
			<table style="border-style:solid;border-width:1px;border-color:<?echo $color;?>;padding:10px;margin-top:10px;width:500px">	
			<tr>
			<th style="background-color:<?echo $color;?>;color:white;">
			<?
			echo $sheetname;
			?>
			</th>
			</tr>
			<td>	
			<?
			processWorksheetStandings($currKey,$handicap);
			?>
			</td>
			</tr>
			</table>			
			<?	

				
		}
    }
	
 }
 
function processWorksheetStandings($key,$handicap)
{
	global $gdClient;
    $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $query->setSpreadsheetKey($key);
    $feed = $gdClient->getWorksheetFeed($query);
    //print "== Available Worksheets ==\n";
   
	
	foreach($feed->entries as $entry) {
		$worksheetname = $entry->title->text;
		$currWkshtId = split('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];
		$color = (strpos(strtolower($worksheetname), 'lex')!==false)?"#2BB0E8":"red";
		
		?>
		<table style="border-style:none;margin-top:10px;width:100%">	
		<tr>
		<th colspan="<? echo ($handicap)?"5":"4"; ?>" style="background-color:<?echo $color;?>;color:white;text-align:center;">
		<?
		echo $worksheetname;
		?>
		</th>
		</tr>
		<?
		if($handicap)
		{
			processStandingsForHandicapWorskeet($key,$currWkshtId);
		}
		else
		{
			processStandingsForWorskeet($key,$currWkshtId);
		}
		echo '</table>';
			
	}
	
 }

function processStandingsForHandicapWorskeet($key,$currWkshtId)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
    $query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($key);
	$query->setWorksheetId($currWkshtId);	
	$query->setMinCol(1);
	$query->setMaxCol(31);
	$query->setMinRow(5);
	$query->setMaxRow(25);
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		if((($column > 2) && ($column < 15)) || (($column > 20) && ($column != 31)) )
			continue;
		$val = trim($cellEntry->cell->getText());
		if($val!="")
		{
			switch($column)
			{
				case 1:
					$rank[] = intval(trim($val,"#"));
					break;
				case 2:
					$player_names[] = $cellEntry->cell->getXML();
					break;
				case 18:
					$wins[] = floatval($val);
					$completed = $completed + floatval($val);
					break;
				case 15:
					$played[] = intval($val);
					break;
				case 17:
					$pointspread[] = intval($val);
					break;
				case 16:
					$pointsscored[] = floatval($cellEntry->cell->getInputValue());
					break;
				case 19:
					$handicap[] = floatval($cellEntry->cell->getInputValue());
					break;
				case 31:
					$aggregate[] = intval($val);
					break;
			
			}
		}			
    }

	for($i=0;$i<count($wins);$i++)
	{
		$data[] = array('name' => $player_names[$i] . ' (' . $handicap[$i] . ')', winstoplayed => $wins[$i] . '/' . $played[$i],'spread' => (($pointspread[$i] > 0)?'+':'') . $pointspread[$i],'pointsscored'=> $pointsscored[$i]);	
	}
	
	array_multisort($pointsscored, SORT_DESC, $pointspread, SORT_DESC,$aggregate, SORT_DESC,$rank,SORT_ASC, $data);
	
	for($i=0;$i<count($wins);$i++)
	{
		$rank  = $i + 1;
		echo "<tr><td>$rank.</td><td>{$data[$i]['name']}</td><td style='text-align:right;'>{$data[$i]['pointsscored']}</td><td style='text-align:right;'>{$data[$i]['winstoplayed']}</td><td style='text-align:right;'>{$data[$i]['spread']}</td></tr>";
	}
	$total = intval( count($wins) * (count($wins)-1) / 2);
	echo "<tr><td colspan='5' style='text-align:center;'>$completed out of $total games completed</td></tr>";
	//echo "<tr><td colspan='4' style='text-align:center;'>Not yet implemented!</td></tr>";




}

 
function processStandingsForWorskeet($key,$currWkshtId)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
    $query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($key);
	$query->setWorksheetId($currWkshtId);	
	$query->setMinCol(1);
	$query->setMaxCol(29);
	$query->setMinRow(5);
	$query->setMaxRow(25);
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		if((($column > 2) && ($column < 15)) || (($column > 17) && ($column != 29)) )
			continue;
		$val = trim($cellEntry->cell->getText());
		if($val!="")
		{
			switch($column)
			{
				case 1:
					$rank[] = intval(trim($val,"#"));
					break;
				case 2:
					$player_names[] = $val;
					break;
				case 15:
					$wins[] = floatval($val);
					$completed = $completed + floatval($val);
					break;
				case 16:
					$played[] = intval($val);
					break;
				case 17:
					$pointspread[] = intval($val);
					break;
				case 29:
					$aggregate[] = intval($val);
					break;
			
			}
		}			
    }

	for($i=0;$i<count($wins);$i++)
	{
		$data[] = array('name' => $player_names[$i], winstoplayed => $wins[$i] . '/' . $played[$i],'spread' => (($pointspread[$i] > 0)?'+':'') . $pointspread[$i]);	
	}
	
	array_multisort($wins, SORT_DESC, $pointspread, SORT_DESC,$aggregate, SORT_DESC,$rank,SORT_ASC, $data);
	//print_r($data);
	for($i=0;$i<count($wins);$i++)
	{
		$rank  = $i + 1;
		echo "<tr><td>$rank.</td><td>{$data[$i]['name']}</td><td style='text-align:right;'>{$data[$i]['winstoplayed']}</td><td style='text-align:right;'>{$data[$i]['spread']}</td></tr>";
	}
	$total = intval( count($wins) * (count($wins)-1) / 2);
	echo "<tr><td colspan='4' style='text-align:center;'>$completed out of $total games completed</td></tr>";




}

function displaySpreadsheets()
{
	global $gdClient;
    $feed = $gdClient->getSpreadsheetFeed();
    
	//echo "== Available Spreadsheets ==</br>";
	echo '<a href="submitleaguegame.php?standings=1">View Standings</a>';
	$entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries");
	foreach($entries as $entry) {
        
		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = split('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8
		
		if((strpos($sheetname, 'Score Sheet')!==false) && (strpos($link, 'full')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false))
		{
			$color = (strpos(strtolower($sheetname), 'lex'))?"#2BB0E8":"red";
			?>
			<table style="border-style:solid;border-width:1px;border-color:<?echo $color;?>;padding:10px;margin-top:10px">	
			<tr>
			<th style="background-color:<?echo $color;?>;color:white;">
			<?
			echo $sheetname;
			?>
			</th>
			</tr>
			<td>	
			<?
			displayWorksheets($currKey);
			?>
			</td>
			</tr>
			</table>			
			<?	

				
		}
    }
	
 }
 
function displayWorksheets($key)
{
	global $gdClient;
    $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $query->setSpreadsheetKey($key);
    $feed = $gdClient->getWorksheetFeed($query);
    //print "== Available Worksheets ==\n";
    echo '<ul>';
	foreach($feed->entries as $entry) {
		$worksheetname = $entry->title->text;
		$currWkshtId = split('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];		
		echo '<li><span id="wsname">'. $worksheetname . "</span>   <input type='text' class='txtname' name='name' /> <a id='submitlink' href='$key,$currWkshtId'>Submit Game Link</a><span id='loading' style='display:none'><img src='ajax-loader.gif' />Processing...</span></li>";
			
	}
	echo '</ul>';    
 }
 
 
function processResults($pid1,$pscore1,$pid2,$pscore2,$ssheetid,$wsheetid)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
    $query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);	
	$query->setMinCol(2);
	$query->setMaxCol(3);
	$query->setMinRow(31);
	$query->setMaxRow(41);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		if($cellEntry->cell->getColumn() == 2)
		{
			$player_names[] = $val;
		}	
		else
		{
			preg_match("/profileid=(?P<profileid>\d+)/",$val,$match);
			$profileid = $match['profileid'] ;
			$player_ids[] = $profileid;
		}
    }

	// $query->setMinCol(2);
	// $query->setMaxCol(2);
	// $query->setMinRow(31);
	// $query->setMaxRow(41);
	// $feed = $gdClient->getCellFeed($query);
	// foreach($feed as $cellEntry) {
      // $val = $cellEntry->cell->getText();
	  // $player_names[] = trim($val);
    // }
	
	$player1_index = array_search($pid1, $player_ids);
	$player2_index = array_search($pid2, $player_ids);
	
	
	
	if(($player1_index!==false) && ($player2_index!==false))
	{
	
		$player1_fbname = getName($pid1);
		$player2_fbname = getName($pid2);
	
		/*if($player1_fbname != $player_names[$player1_index])
		{			
			$updatedName = $gdClient->updateCell(31 + $player1_index,2,$player1_fbname,$ssheetid,$wsheetid);
			$p1change = (($updatedName->cell->getText() == $player1_fbname)?("\\n\\nPlayer " . ($player1_index + 1) . "'s name was changed from {$player_names[$player1_index]} to $player1_fbname"):"");		
		}
		
		if($player2_fbname != $player_names[$player2_index])
		{			
			$updatedName = $gdClient->updateCell(31 + $player2_index,2,$player2_fbname,$ssheetid,$wsheetid);
			$p2change = (($updatedName->cell->getText() == $player2_fbname)?("\\n\\nPlayer " . ($player2_index + 1) . "'s name was changed from {$player_names[$player2_index]}  to $player2_fbname"):"");
		}
		*/
		
	
		$lower_index = min($player1_index,$player2_index);
		$lower_index_score = ($lower_index  == $player1_index)?$pscore1:$pscore2;
		$higher_index = max($player1_index,$player2_index);
		$higher_index_score = ($higher_index  == $player1_index)?$pscore1:$pscore2;
		
		$row_to_update = 5 + 2 * $higher_index;
		$col_to_update = 4 + $lower_index;
		$higher_index_score_text = '=hyperlink("http://apps.facebook.com/' . $_POST["game"] . '/?action=viewboard&gid=' . $_POST["gameid"] . '&pid=1&lang=EN";' . $higher_index_score . ')';
		$updatedCell = $gdClient->updateCell($row_to_update,$col_to_update,$higher_index_score_text,$ssheetid,$wsheetid);
		$updatedCell2 = $gdClient->updateCell($row_to_update + 1,$col_to_update,$lower_index_score,$ssheetid,$wsheetid);										   
													
		if(($updatedCell->cell->getText() == $higher_index_score) && ($updatedCell2->cell->getText() == $lower_index_score))
		{
			?>
			<script type="text/javascript">
				alert("Game between players\n<? echo ($player1_index + 1) . ". " . $player_names[$player1_index]; ?>\nand\n<? echo ($player2_index + 1) . ". " . $player_names[$player2_index]; ?>\nwas successfully updated.<?echo "$p1change$p2change";?>");
			</script>
			<?
		
		}
		else
		{
			?>
				<script type="text/javascript">
					alert("Error updating game between players\n<? echo ($player1_index + 1) . ". " . $player_names[$player1_index]; ?>\nand\n<? echo ($player2_index + 1) . ". " . $player_names[$player2_index]; ?>. \nPlease retry!");
				</script>
			<?
		}
	}
	else
	{
		?>
		<script type="text/javascript">
			alert("This game does not belong to this division!");
		</script>
		<?
	
	}
	
 }
 
 

if (isset($_GET['token'])) {
	header("Location: " . 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Wordscraper/Lex League Assistant</title>
<script type="text/javascript" src="jquery-1.4.3.min.js"></script>
<script src="jquery.alerts.js" type="text/javascript"></script>
<link href="jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">
		
		
<script type="text/javascript">
	
	function postwith (to,p) {
	  var myForm = document.createElement("form");
	  myForm.method="post" ;
	  myForm.action = to ;
	  for (var k in p) {
		var myInput = document.createElement("input") ;
		myInput.setAttribute("name", k) ;
		myInput.setAttribute("value", p[k]);
		myForm.appendChild(myInput) ;
	  }
	  document.body.appendChild(myForm) ;
	  myForm.submit() ;
	  document.body.removeChild(myForm) ;
	}

	$(document).ready(function(){
		  $("a#submitlink").click(function(event) {
			if(event.preventDefault) 
				event.preventDefault();
			else
				event.returnValue = false; 
			
			var name = $.trim( $(this).siblings('.txtname').val().toLowerCase() ); 
			var wsname = $.trim( $(this).siblings('span#wsname').text().toLowerCase() );
			var ids = $(this).attr('href').split(',');
			var wsheetid = ids[1];
			var ssheetid = ids[0];
			if(name=="")
			{
				alert('Game link required!');
				return;
			}
			wsname = wsname.match(/(lex|wordscraper|ws)/g);
			wsname = wsname[0];
			var game = name.match(/(lexulous|wordscraper)/g);
			if(game==null)
			{
				alert('Invalid game link!');
				return;
			}
			if((game[0] != 'lexulous') && (wsname == 'lex'))
			{
				alert('This is not a valid Lexulous game!');
				return;			
			}
			
			if((game[0] != 'wordscraper') && ((wsname == 'wordscraper')||(wsname == 'ws')))
			{
				alert('This is not a valid Wordscraper game!');
				return;			
			}
			
			var gid = /gid=(\d+)/g.exec(name);
			if(gid==null)
			{
				alert('Invalid game link!');
				return;
			}
			var loader = $(this).siblings('span#loading');
			loader.show();
			$.ajax({url:'gameresult.php',context:this,data:({ gid:gid[1],game:game[0]}),dataType: "xml",success:	   
			function(xml){
				var count = $(xml).find('count').text();
				var dictionary = $(xml).find('dictionary').text();
				dictionary = ((dictionary == "sow")?"UK":"US");
				if(count != 2)
				{
					alert('Invalid game!\nGame has more than 2 players.');
					return;
				}
				var pid1;
				var pid2;
				var pname1;
				var pname1;
				var pscore1;
				var pscore2;
				var ppic1;
				var ppic2;
				pids = new Array();
				pscores = new Array();
                $(xml).find('player').each(function(){
					pnum = $(this).find('pid').text();
					pid = $(this).find('pemail').text();
					pscore = $(this).find('pscore').text();
					pname = $(this).find('pname').text();
					ppic = $(this).find('pic').text();
					if(pnum=="1")
					{
						pid1 = pid;
						pname1 = pname + (($(this).find('winner').text() == 'yes')?'&nbsp<img src="star.png" />':'');
						pscore1 = pscore;
						ppic1 = ($(this).find('winner').text() == 'no')?'<img style="opacity:0.4;filter:alpha(opacity=40);" src="' + ppic + '" />':'<img src="' + ppic + '" />';
					}
					else
					{
						pid2 = pid;
						pname2 = pname + (($(this).find('winner').text() == 'yes')?'&nbsp<img src="star.png" />':'');
						pscore2 = pscore;
						ppic2 = ($(this).find('winner').text() == 'no')?'<img style="opacity:0.4;filter:alpha(opacity=40);" src="' + ppic + '" />':'<img src="' + ppic + '" />';
					
					}					
					pids.push(pid);
					pscores.push(pscore);
					
				});
				loader.hide();
				var msg = '<div style="margin-left:auto;margin-right:auto;width:200px"><table style="border-style:none;margin-left:auto;margin-right:auto;">';
				msg = msg + '<tr><td>' + pname1 + '</td><td>&nbsp</td><td>' + pname2 + '</td></tr>';
				msg = msg + '<tr><td>' + ppic1 + '</td><td>&nbsp</td><td>' + ppic2 + '</td></tr>';
				msg = msg + '<tr><td>' + pscore1 + '</td><td>&nbsp</td><td>' + pscore2 + '</td></tr>';
				msg = msg + '</table></div>';
				jConfirm(msg + '<br/>Dictionary: ' + dictionary + '<br/><br/>Would you like to submit this game to the selected division?','Game Result', function(resp) {
					if(resp)
					{
						postwith("submitleaguegame.php",{pids:pids.join(),pscores:pscores.join(),ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1]});					
					}
				});
				
			}});
			
			return false;
		 });

	});
</script>
</head>
<body>
<div style='width:600px;text-align:left;margin-left:auto;margin-right:auto'>
<?
$client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['spread_token']);
$gdClient = new Zend_Gdata_Spreadsheets($client);
if(isset($_GET['standings']))
{
	displayStandings();
}
else
{
	displaySpreadsheets();
}

if(isset($_POST['pids']) && isset($_POST['pscores']) && isset($_POST['ssheetid'])&& isset($_POST['wsheetid']) && isset($_POST['game'])&& isset($_POST['gameid']))
{
	$pids = explode(',',$_POST['pids']);
	$pscores = explode(',',$_POST['pscores']);
	$ssheetid = $_POST['ssheetid'];
	$wsheetid = $_POST['wsheetid'];
	processResults($pids[0],$pscores[0],$pids[1],$pscores[1],$ssheetid,$wsheetid);
}
?>
</div>
</body>
</html>