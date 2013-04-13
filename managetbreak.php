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

if(isset($_GET['clear']))
{
	setcookie ("spread_token", "", time() - (3600 * 50));
	header("Location: " . 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
	exit;	
}

if (!isset($_COOKIE['spread_token'])) {
    if (isset($_GET['token'])) {
        // You can convert the single-use token to a session token.
        $client = new Zend_Gdata_HttpClient();
	
		$client->setAuthSubPrivateKeyFile('dalevrossrsakey.pem', null, true);

		$session_token = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token'],$client);
        // Store the session token in our session.
        //$_SESSION['spread_token'] = $session_token;
        $_COOKIE['spread_token'] = $session_token;
        $expiry =  mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y")+10);
        setcookie('spread_token', $session_token, $expiry);
		
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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Wordscraper/Lex T-Break Assistant</title>
		<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
		</head>
		<body>
		<div style='width:600;text-align:center;margin-left:auto;margin-right:auto;margin-top:200px'>
		<span>This application needs to be authorized before you will be able to submit results</span>
		<br/>
        <span>Click <a href=<? echo $googleUri;?>>here</a> to authorize this application.</span>	 
		</div>
		</body>
		</html>
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

function cmp_entries2($a, $b)
{
    return strcmp($b->title->text, $a->title->text);
}

function displayStandings($round)
{
	global $gdClient;
    $feed = $gdClient->getSpreadsheetFeed();
    
	//echo "== Available Spreadsheets ==</br>";
	echo '<div id="standingstop"><a href="managetbreak.php">Submission Page</a><img src="owl.jpg"/>';
	echo '<br/><label for="txtsround">Round:</label>&nbsp&nbsp<input type="text" value="' . $round .'" title="Enter Round#" id="txtsround" name="stround"/>';
	echo '&nbsp&nbsp<a id="standingslink" href="managetbreak.php?standings=1">View Standings</a>';
	?>
	<br/>
	<br/>
	<form>
	Name Filter:&nbsp&nbsp<input type="text" id="srchtxtname" name="srchname" /><br/>
	<input type="radio" name="srchtype" value="cont" checked="1"/> Contains
	&nbsp&nbsp&nbsp <input type="radio" name="srchtype" value="str" /> Starts With
	&nbsp&nbsp&nbsp <input type="radio" name="srchtype" value="end" /> Ends With
	&nbsp&nbsp&nbsp <input type="radio" name="srchtype" value="eq" /> Equals
	</form>
	
	<?php 
	echo '</div><br/>';
	
	$entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries");
	
	foreach($entries as $entry) {
        
		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = explode('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8
		
		if((strpos($sheetname, 'Score Sheet')!==false) &&(strpos($sheetname, 'T-Break')!==false) && (strpos($link, 'full')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) && (strpos(strtolower($sheetname), 'sign')===false))
		{
		
			//$color = (strpos(strtolower($sheetname), 'lex'))?"#2BB0E8":"red";
			$color = "brown";
			?>
			<table class="mainsheettable" style="border-style:solid;border-width:1px;border-color:<?php echo $color;?>;padding:10px;margin-top:10px;width:500px">	
			<tr>
			<th style="background-color:<?php echo $color;?>;color:white;">
			<?php
			echo $sheetname . ' - Round '  . '<span class="currentround">' . $round . '</span>';
			?>
			</th>
			</tr>
			<tr>
			<td>	
			<?php
			processWorksheetStandings($currKey,$round);
			?>
			</td>
			</tr>
			</table>			
			<?php	

				
		}
    }
	
 }
 
function processWorksheetStandings($key,$round)
{
	global $gdClient;
    $query = new Zend_Gdata_Spreadsheets_DocumentQuery();
    $query->setSpreadsheetKey($key);
    $feed = $gdClient->getWorksheetFeed($query);
    //print "== Available Worksheets ==\n";
    
	$entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries2");
	
	foreach($entries as $entry) {
		$worksheetname = $entry->title->text;
		$currWkshtId = explode('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];
		$numRounds = getNumRounds($key,$currWkshtId);
		if($round <= $numRounds)
		{
			$color = (strpos(strtolower($worksheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($worksheetname), 'lex')!==false)?"#2BB0E8":"red");
			$wgame = (strpos(strtolower($worksheetname), 'scrabble')!==false)?"scrabble":((strpos(strtolower($worksheetname), 'lex')!==false)?"lexulous":"wordscraper");
			?>
			<table class='standingstable' data-ssheetid='<?php echo $key;?>' data-wsheetid='<?php echo $currWkshtId;?>' style="border-style:none;margin-top:10px;width:100%">	
			<tr>
			<th style="background-color:<?php echo $color;?>;color:white;text-align:center;"></th>
			<th style="background-color:<?php echo $color;?>;color:white;text-align:center;"><span class="bracket">
			<?php
			echo $worksheetname . "</span> - Round <span>" . $round;
			?>
			</span></th>
			<th class='piccell' title='Click here to hide leader' style="background-color:<?php echo $color;?>;color:white;text-align:center;"><span class='leaderpic' ></span></th>
			</tr>
			<?php
			
				processStandingsForWorskeet($key,$currWkshtId,$round,$numRounds,$wgame);
			
			?>
			</table>
			<?php
		}
			
	}
	
 }



 
function processStandingsForWorskeet($key,$currWkshtId,$round,$numRounds,$wgame)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
	$numPlayers = getNumPlayers($key,$currWkshtId);
    $query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($key);
	$query->setWorksheetId($currWkshtId);	
	
	
	$query->setMinCol(2);
	$query->setMaxCol(2);
	
	$minRow = 4 + $numPlayers + 6;
	$maxRow = $minRow + $numPlayers - 1;
	
	$query->setMinRow($minRow);
	$query->setMaxRow($maxRow);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		
		preg_match("/profileid=(?P<profileid>\d+)/",$val,$match);
		$profileid = $match['profileid'] ;
		$player_ids[] = $profileid;
		
    }
	
	$query->setMinCol(1);
	$query->setMaxCol(3+5*$round);
	$query->setMinRow(5);
	$query->setMaxRow(4 + $numPlayers);
	
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		//echo $column . '<br/>';
		$row = $cellEntry->cell->getRow();
		$i = $row - 4;
		if($column == 2)
			continue;
		$val = trim($cellEntry->cell->getText());
		$inputVal= trim($cellEntry->cell->getInputValue());
		if($val!="")
		{
			switch($column)
			{
				case 1:
					$player_names[$i] = $val;
					break;
				case 3:
					$total_vps[$i] = intval($val);
					break;
				case (3 + 5*$round):
					$this_round_vp[$i] = intval($val);					
				case (((($column - 3)%5)==0) && ($column != 3)):
					/*if(!isset($total_vps[$i]))
						$total_vps[$i] = 0;
					$total_vps[$i] = intval($val) + $total_vps[$i];
					*/
					$vps[$i][($column-3)/5] = intval($val);
					break;
				case (($column%5)==0):
					$links[$i][$column/5] = $inputVal;
					$for[$i][$column/5] = intval($val);
					break;
				case ((($column-1)%5)==0 &&($column!=1)):
					$against[$i][($column-1)/5] = intval($val);
					break;
				case (2 + 5*$round):
					$this_round_diff[$i] = intval($val);
					break;
				case (4 + 5*($round-1)):
					$this_round_opponent[$i] = $val;
				case ((($column-4)%5)===0):
					$opponents[$i][($column+1)/5] = $val;						
					break;
				default:
					break;
			
			}
		}			
    }
    
    if(!is_array($this_round_vp))
    {
    	$this_round_vp = array();
    	
    }
    
 	if(!is_array($total_vps))
    {
    	$total_vps = array();
    	
    }
	
    if(!is_array($vps))
    {
    	$vps = array();
    	
    }
    
	if(!is_array($links))
    {
    	$links = array();    	
    }
    
	if(!is_array($opponents))
    {
    	$opponents = array();    	
    }
    
	if(!is_array($for))
    {
    	$for = array();    	
    }
    
	if(!is_array($against))
    {
    	$against = array();    	
    }
    
    
	$roundStarted = is_array($this_round_opponent);
    
    
    $missing = array();
    
	$missingids = array();
	
    foreach ($player_names as $key => $value) {
    	if(!array_key_exists($key,$this_round_vp))
    	{
    		$this_round_vp[$key] = 0;    		
    		if(!in_array($player_names[$key],$missing))
    		{
    			$missing[$player_names[$key]]= $this_round_opponent[$key]; 
						
    		}
    		$missingids[$player_names[$key]]=$player_ids[$key-1];
    	}
    	
    	if(!array_key_exists($key,$total_vps))
    	{
    		$total_vps[$key] = 0;
    		$vps[$key] = array();
    	}
		
		if(!array_key_exists($key,$links))
    	{
    		$links[$key] = array();
    		$for[$key] = array();
    	}
    	else
		{
			array_walk($links[$key],'getlinkfromcell');
		
		}
    	
    	if(!array_key_exists($key,$against))
    	{
    		$against[$key] = array();    		
    	}
    	
    	if(!array_key_exists($key,$opponents))
    	{
    		$opponents[$key] = array();    		
    	}
		
    }
    
    ksort($this_round_vp);
  	ksort($total_vps);
	ksort($links);
	ksort($for);
	ksort($against);
	ksort($opponents);
	ksort($vps);
	
  	for($i=1;$i<=count($player_names);$i++)
	{
		$rank[$i] =  $i;
		$data[$i] = array('name' => $player_names[$i], 'total_vps' => $total_vps[$i],'id'=>$player_ids[$i-1],'links'=>$links[$i],'for'=>$for[$i],'against'=>$against[$i],'opponents'=>$opponents[$i],'vps'=>$vps[$i]);	
	}
	
	array_multisort($total_vps, SORT_DESC,$this_round_vp, SORT_DESC,$this_round_diff, SORT_DESC,$rank,SORT_ASC, $data);
	
	print_r($data);
	
	
	if($round == $numRounds)
	{
		$baseurl = "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER["REQUEST_URI"]);
		$crown = ($wgame=="scrabble")?"crownscrab.png":(($wgame=="wordscraper")?"crown.png":"crownlex.png");
		echo "<tr><td colspan='3' style='text-align:center;'><img src='$crown'></img><br/><img src='https://graph.facebook.com/{$data[0]['id']}/picture?type=large' width='180'></img></td></tr>";
		echo "<tr><td colspan='3' style='text-align:center;'>[img]$baseurl/$crown" . "[/img]<br/>[img]https://graph.facebook.com/{$data[0]['id']}/picture?type=large[/img]</td></tr>";			
	}
	for($i=0;$i<count($player_names);$i++)
	{
		$rank  = $i + 1;
		$class = (count($data[$i]['links'])==$round)?" finished":"";
		echo "<tr id='{$data[$i]['id']}' class='playerrow$class' ><td>$rank.</td><td class='playername'>{$data[$i]['name']}</td><td title='Click score to show game history' style='text-align:right;'>";
		
		foreach($data[$i]['links'] as $kround=>$link)
		{
			echo "<input type='hidden' value='$link,{$data[$i]['for'][$kround]},{$data[$i]['against'][$kround]},{$data[$i]['opponents'][$kround]},{$data[$i]['vps'][$kround]}' class='$kround' />";
		}
		echo "<a class='curscore' href='#'>{$data[$i]['total_vps']}</a></td></tr>";
	}
	$numCompleted  = ($numPlayers/2 - count($missing));
	$numCompleted =  ($numCompleted > 0)?$numCompleted:0;
	echo "<tr><td colspan='3' style='text-align:center;background-color:black;color:white'>Completed: " . $numCompleted . " of " . ($numPlayers/2) . "</td></tr>";
		
	if((count($missing)>0) && $roundStarted)
	{
		 
		echo "<tr class='hiderow'><td colspan='3' style='text-align:center;background-color:brown;color:white;'>Click here to <span class='hidestatus'>hide</span> missing games</td>";
			
		echo "<tr><td colspan='3' style='text-align:center;'>Missing Games</td></tr>";
		$ix = false;
		foreach ($missing as $key => $value) {
			$class = ($ix)?" evenmiss":"";
			echo "<tr class='playerrow$class' ><td class='forname' data-id='{$missingids[$key]}' >$key</td><td style='text-align:center;'><input class='forscore' size='4'/>&nbsp<a class='manualsubmit' href='#'>Submit</a>&nbsp<input class='againstscore' size='4'/></td><td class='againstname' data-id='{$missingids[$value]}'>$value</td></tr>";
			$ix = !$ix;
		}
	}



}

function getlinkfromcell(&$celldata, $key)
{
	$celldata = substr($celldata, strpos($celldata,"(") + 2);
	$celldata = substr($celldata, 0, strpos($celldata, "\""));
}


function getStatusForWorskeet($key,$currWkshtId,$round)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
	$numPlayers = getNumPlayers($key,$currWkshtId);
    $query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($key);
	$query->setWorksheetId($currWkshtId);	
	
	$query->setMinCol(2);
	$query->setMaxCol(2);
	
	$minRow = 4 + $numPlayers + 6;
	$maxRow = $minRow + $numPlayers - 1;
	
	$query->setMinRow($minRow);
	$query->setMaxRow($maxRow);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		
		preg_match("/profileid=(?P<profileid>\d+)/",$val,$match);
		$profileid = $match['profileid'] ;
		$player_ids[] = $profileid;
		
    }
	
	$query->setMinCol(1);
	$query->setMaxCol(3+5*$round);
	$query->setMinRow(5);
	$query->setMaxRow(4 + $numPlayers);
	
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		$i = $row - 4;
		if($column == 2)
			continue;
		$val = trim($cellEntry->cell->getText());
		if($val!="")
		{
			switch($column)
			{
				case 1:
					$player_names[$i] = $val;
					break;
				case (3 + 5*$round):
					$this_round_vp[$i] = intval($val);
				case (((($column - 3)%5)==0) && ($column != 3)):
					if(!isset($total_vps[$i]))
						$total_vps[$i] = 0;
					$total_vps[$i] = intval($val) + $total_vps[$i];
					break;				
				case (2 + 5*$round):
					$this_round_diff[$i] = intval($val);
					break;
				case ((($column-4)%5)===0):
					$opponents[$i][] = $val;
					break;	
				default:
					break;
			
			}
		}			
    }
    
    if(!is_array($this_round_vp))
    {
    	$this_round_vp = array();
    	
    }
    
	if(!is_array($total_vps))
    {
    	$total_vps = array();
    	
    }
    
    foreach ($player_names as $key => $value) {
    	if(!array_key_exists($key,$this_round_vp))
    	{
    		$this_round_vp[$key] = 0;    		
    	}
    	if(!array_key_exists($key,$total_vps))
    	{
    		$total_vps[$key] = 0;
    	}		
    }
    
    ksort($this_round_vp);
  	ksort($total_vps);
	for($i=1;$i<=count($player_names);$i++)
	{
		$rank[$i] =  $i;
		$data[$i] = array('index'=>$i,'name' => $player_names[$i],'id'=>$player_ids[$i-1], 'total_vps' => $total_vps[$i],'opponents'=>$opponents[$i],'address'=>'A' . ($i + 4) );	
	}
	
	array_multisort($total_vps, SORT_DESC,$this_round_vp, SORT_DESC,$this_round_diff, SORT_DESC,$rank,SORT_ASC, $data);
	return $data;
}


function displaySpreadsheets()
{
	global $gdClient;
    $feed = $gdClient->getSpreadsheetFeed();
    
	//echo "== Available Spreadsheets ==</br>";
	echo '<div><label for="txtsround">Round:</label>&nbsp&nbsp<input type="text" title="Enter Round#" id="txtsround" name="stround"/>&nbsp&nbsp<a id="standingslink" href="managetbreak.php?standings=1">View Standings</a><img src="owl.jpg"/></div>';
	$entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries");
	foreach($entries as $entry) {
        
		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = explode('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E
		
		
		if((strpos($sheetname, 'Score Sheet')!==false) && (strpos($sheetname, 'T-Break')!==false) && (strpos($link, 'full')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) && (strpos(strtolower($sheetname), 'sign')===false))
		{
			//$color = (strpos(strtolower($sheetname), 'lex'))?"#2BB0E8":"red";
			$color = "brown";
			?>
			<table style="border-style:solid;border-width:1px;border-color:<?php echo $color;?>;padding:10px;margin-top:10px">	
			<tr>
			<th style="background-color:<?php echo $color;?>;color:white;text-align:center">
			<?php
			echo $sheetname;
			?>
			</th>
			</tr>
			<td>	
			<?php
			displayWorksheets($currKey);
			?>
			</td>
			</tr>
			</table>			
			<?php	

				
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
    //echo '<ul>';
    $entries = iterator_to_array ($feed->entries);
	
	usort($entries,"cmp_entries2");
	
	foreach($entries as $entry) {
		$worksheetname = $entry->title->text;
		//if((strpos(strtolower($worksheetname), 'lex')!==false) || (strpos(strtolower($worksheetname), 'ws')!==false) || (strpos(strtolower($worksheetname), 'wordscraper')!==false) )
		if((strpos(strtolower($worksheetname), 'lex')!==false) || (strpos(strtolower($worksheetname), 'ws')!==false) || (strpos(strtolower($worksheetname), 'wordscraper')!==false) || (strpos(strtolower($worksheetname), 'scrabble')!==false)  )
		{	
			$currWkshtId = explode('/', $entry->id->text);
			$currWkshtId = $currWkshtId[8];
			$color = (strpos(strtolower($worksheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($worksheetname), 'lex')!==false)?"#2BB0E8":"red");
			$numplayers = getNumRounds($key,$currWkshtId);
			echo "<table style='border-style:solid;border-width:1px;border-color:$color;padding:10px;margin-top:10px'>";
			echo '<tr><th colspan="4" style="text-align:center;background-color:' . $color .  ';color:white"><span id="wsname">'. $worksheetname . "</span></td></tr>";
			echo  "<tr><td><img class='imgQueryBingos' src='q.jpeg' title='Click here to query bingos' width='20' height='20' /></td><td><a id='submitlink' href='$key,$currWkshtId'>Submit Game</a><span id='loading' style='display:none'><img src='ajax-loader.gif' />Processing...</span></td><td colspan='2'><input type='text' autocomplete='off' class='txtname' name='name' title='Enter game link here and click the Submit Game link to the left' size='50'/></td></tr>";
			echo  "<tr><td colspan='2'><a id='generatematchups' href='$key,$currWkshtId,$numplayers'>Generate Matchups</a></td><td><input type='text' title='Enter Round#' class='txtround' name='round'/></td><td><label for='updates'>Update Sheet</label><input type='checkbox' id='updates' value='1'/></td></tr></table>";
		}
	}
	//echo '</ul>';    
 }
 
 
 function getNumRounds($ssheetid,$wsheetid)
 {
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);	
	$query->setMinCol(2);
	$query->setMaxCol(2);
	$query->setMinRow(2);
	$query->setMaxRow(2);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		if($row==2 && $column==2)
		{
			$val = trim($cellEntry->cell->getText());
			return intval($val);
		}
	
	}
	return 0;
 
 }
 
 
 function getNumPlayers($ssheetid,$wsheetid)
 {
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);	
	$query->setMinCol(2);
	$query->setMaxCol(2);
	$query->setMinRow(1);
	$query->setMaxRow(1);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		if($row==1 && $column==2)
		{
			$val = trim($cellEntry->cell->getText());
			return intval($val);
		}
	
	}
	return 0;
 
 }
 
 function getSheetName($ssheetid,$wsheetid)
 {
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);	
	$query->setMinCol(2);
	$query->setMaxCol(2);
	$query->setMinRow(1);
	$query->setMaxRow(1);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		if($row==1 && $column==2)
		{
			$val = trim($cellEntry->cell->getText());
			return intval($val);
		}
	
	}
	return 0;
 
 }

function matchupsExists($round,$ssheetid,$wsheetid)
{
	global $gdClient;
	
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);
	
	$numPlayers = getNumPlayers($ssheetid,$wsheetid);
	
	$oppCol = 4 + ($round-1)*5;
	$query->setMinCol($oppCol);
	$query->setMaxCol($oppCol);
	
	$query->setMinRow(5);
	$query->setMaxRow(4 + $numPlayers);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		$opponents[] = $val;
		
	}
	
	return (count($opponents)==$numPlayers);
	 
	
}

function generateMatchups($round,$ssheetid,$wsheetid,$updatesheet,$game)
{
	global $gdClient;
	/*if(matchupsExists($round,$ssheetid,$wsheetid))
	{
		?>
			<script type="text/javascript">
				alert("Matchups have already been generated.\nPlease clear to regenerate.");
			</script>
		<?	
		
	}
	
	else
	{
	*/
	echo '<a href="managetbreak.php">Submission Page</a><img src="owl.jpg"/><br/>';
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);
	
	$nump = getNumPlayers($ssheetid,$wsheetid);
	
	$query->setMinCol(1);
	$query->setMaxCol(2);
	
	$minRow = 4 + $nump + 6;
	$maxRow = $minRow + $nump - 1;
	
	$query->setMinRow($minRow);
	$query->setMaxRow($maxRow);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		if($cellEntry->cell->getColumn() == 1)
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
	
	if($round==1)
	{
		  
		$updatesheet = 0;
		for($i=0;$i<$nump;$i++)
		{
			$playerref[] = array('address'=>"=A" . ($i+5),'id'=>$player_ids[$i],'index'=>($i+1),'name'=>$player_names[$i],'opponents'=>array(),'rematch'=>false,'rematchlist'=>'');		
		}
		
		shuffle($playerref);
		while(count($playerref) > 0)
		{		
			$p1 = array_shift($playerref);
			$p2 = array_pop($playerref);
			$matchups[] = array('p1'=>$p1,'p2'=>$p2);
			$pairs[$p1['index']] = $p2['address'];
			$pairs[$p2['index']] = $p1['address'];			
		}
	}
	else 
	{
		if(!matchupsExists($round-1,$ssheetid,$wsheetid))
		{
			?>
				<script type="text/javascript">
					alert("No matches exist for previous round!");
				</script>
			<?php	
			
		}
		else		
		{
			
			$data = getStatusForWorskeet($ssheetid,$wsheetid,$round-1);
			//print_r($data);

			for($i=0;$i<$nump;$i++)
			{
				$playerref[] = array('address'=>'=' . $data[$i]['address'],'id'=>$data[$i]['id'],'index'=>$data[$i]['index'],'name'=>$data[$i]['name'],'opponents'=>$data[$i]['opponents'],'rematch'=>false,'rematchlist'=>'');		
			}
			
			while(count($playerref) > 0)
			{
				$head = array();		
				$p1 = array_shift($playerref);
				$p2 = array_shift($playerref);
				while(in_array($p2['name'],$p1['opponents']))
				{
					$p2['rematch'] = true;
					$p1['rematch'] = true;
					$p1['rematchlist'] = $p1['rematchlist'] . "," . $p2['name'];
					$p2['rematchlist'] = $p2['rematchlist'] . "," . $p1['name']; 					
					$head[] = $p2;
					$p2 = array_shift($playerref);
					 						
				}
				$p1['rematchlist'] = ltrim($p1['rematchlist'],",");
				$p2['rematchlist'] = trim($p2['rematchlist'],",");
				$playerref = array_merge($head,$playerref);
				$matchups[] = array('p1'=>$p1,'p2'=>$p2);
				$pairs[$p1['index']] = $p2['address'];
				$pairs[$p2['index']] = $p1['address'];			
			}
			
		}
	}
	if(is_array($matchups))
	{
		$count = 1;
		echo '<table><tr><td>';
		foreach($matchups as $arr)
		{
			$p1attr = ($arr['p1']['rematch'])?' class="rematch" title="Potential rematch averted with ' . $arr['p1']['rematchlist'] .'" ' :'';
			$p2attr = ($arr['p2']['rematch'])?' class="rematch" title="Potential rematch averted with ' . $arr['p2']['rematchlist'] .'" ' :'';
			?>
				<span<?php echo $p1attr;?>><?php echo $count . " " . $arr['p1']['name'] . " "; ?><a class="<?php echo $game; ?>" href="http://apps.facebook.com/<?php echo $game; ?>/?action=newgame&with=<?php echo $arr['p1']['id'];?>" target="_blank">http://apps.facebook.com/<?php echo $game; ?>/?action=newgame&with=<?php echo $arr['p1']['id'];?></a>
				</span>
				<?php if($game != "scrabble"){?>
					<br/>
				<?php } ?>
				<span<?php echo $p2attr;?>>
				vs. <?php echo $arr['p2']['name'] . " "?><a class="<?php echo $game; ?>" href="http://apps.facebook.com/<?php echo $game; ?>/?action=newgame&with=<?php echo $arr['p2']['id'];?>" target="_blank">http://apps.facebook.com/<?php echo $game; ?>/?action=newgame&with=<?php echo $arr['p2']['id'];?></a>
				</span>
				<br/>
				<br/>
				
			<?php
			$count++;
		}
		echo '</td></tr></table>';
		if($updatesheet==1)
		{
			foreach($pairs as $key=>$value)
			{
				$updatedCell = $gdClient->updateCell($key + 4,4 + ($round-1) * 5,$value,$ssheetid,$wsheetid);
			}
		}
	}
}
 
function processResults($pid1,$pscore1,$pid2,$pscore2,$ssheetid,$wsheetid)
{
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);
	
	$nump = getNumPlayers($ssheetid,$wsheetid);
	
	$query->setMinCol(1);
	$query->setMaxCol(2);
	
	$minRow = 4 + $nump + 6;
	$maxRow = $minRow + $nump - 1;
	
	$query->setMinRow($minRow);
	$query->setMaxRow($maxRow);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		if($cellEntry->cell->getColumn() == 1)
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
	
	$player1_index = array_search($pid1, $player_ids);
	$player2_index = array_search($pid2, $player_ids);
	//echo "<br/>Player 1 index: $player1_index\nPlayer 2 index: $player2_index\n";
	$nrounds = getNumRounds($ssheetid,$wsheetid);
	
	$query->setMinCol(4);
	$query->setMaxCol(4+($nrounds-1) * 5);
	$query->setMinRow(5+$player1_index);
	$query->setMaxRow(5+$player1_index);
	
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();
		$column = $cellEntry->cell->getColumn();
		
		if( (($column-4)%5)==0)
		{
			$r = ((int)(($column-4)/5)) + 1;
			$opponents[$r] = $val;
		}	
		
    }
	
	$round = end(array_keys($opponents,$player_names[$player2_index]));
	
	//echo "Round: $round<br/>";
	
	if(($player1_index!==false) && ($player2_index!==false) && ($round!==false) )
	{
	
		//$player1_fbname = getName($pid1);
		//$player2_fbname = getName($pid2);
		
		
		
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
		
		
		$p1row = 5 +  $player1_index;
		$p2row = 5 +  $player2_index;
		$for = 5 +($round-1) * 5;
		$against = 6 +($round-1) * 5;
		
		$p1_score_text = ($_POST["game"]=='unknown')?$pscore1:'=hyperlink("http://apps.facebook.com/' . $_POST["game"] . '/?action=viewboard&gid=' . $_POST["gameid"] . '&pid=1&lang=EN";' . $pscore1 . ')';
		$p2_score_text = ($_POST["game"]=='unknown')?$pscore2:'=hyperlink("http://apps.facebook.com/' . $_POST["game"] . '/?action=viewboard&gid=' . $_POST["gameid"] . '&pid=1&lang=EN";' . $pscore2 . ')';
		$p1for = $gdClient->updateCell($p1row,$for,$p1_score_text,$ssheetid,$wsheetid);
		$p1against = $gdClient->updateCell($p1row,$against,$pscore2,$ssheetid,$wsheetid);
		$p2for = $gdClient->updateCell($p2row,$for,$p2_score_text,$ssheetid,$wsheetid);
		$p2against = $gdClient->updateCell($p2row,$against,$pscore1,$ssheetid,$wsheetid);	
												
		if(($p1for->cell->getText() == $pscore1) && ($p1against->cell->getText() == $pscore2) && ($p2for->cell->getText() == $pscore2) && ($p2against->cell->getText() == $pscore1))
		{
			?>
			<script type="text/javascript">
				alert('Round <?php echo $round; ?> game between players\n<?php echo ($player1_index + 1) . ". " . $player_names[$player1_index]; ?>\nand\n<?php echo ($player2_index + 1) . ". " . $player_names[$player2_index]; ?>\nwas successfully updated.');
			</script>
			<?php
		
		}
		else
		{
			?>
				<script type="text/javascript">
					alert("Error updating game between players\n<?php echo ($player1_index + 1) . ". " . $player_names[$player1_index]; ?>\nand\n<?php echo ($player2_index + 1) . ". " . $player_names[$player2_index]; ?>. \nPlease retry!");
				</script>
			<?php
		} 
	}
	else
	{
		?>
		<script type="text/javascript">
			alert("This game does not belong to this bracket!");
		</script>
		<?php
	
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
<script type="text/javascript" src="jquery-1.7.min.js"></script>
<script src="jquery.alerts.js" type="text/javascript"></script>
<link href="jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
		
		
<script type="text/javascript">
	
	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}
	
	function isInt(n) {
		return (isNumber(n) && (n % 1 == 0));
	}


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
	
		  var divcontent = $("div#content");
		  if(divcontent.find("table").length==0)
		  {
			 divcontent.find("a").remove();
			 divcontent.find("div#standingstop").remove();
			 divcontent.append('<p>No active T-Break games were found in your documents.<br/>Care to watch a video?</p><iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/m8skAMI03HI" frameborder="0"></iframe>');
				
		  }
		  else
		  {
			  $("div#content > table").css('background','url("http://www.dalevross.com/scrabtourneyasst/tourneyasst75x75.png")');				  
			  if($("div#content table").not("div#content > table").length > 0)
			  {
				  $("div#content table").not("div#content > table").css('background-color','white');
			  }
			  else
			  {
				  $("div#content tr").css('background-color','white');
				  $("div#content").animate({'width':'750px'},"2000","swing");
                  $("div#content > table").css('padding','20px');				  
			  }
			  
			  
			  $(".standingstable").each(function(){
				   var leaderrow =  $(this).find('tr.playerrow').eq(0);
			       var id = leaderrow.attr('id');
				   var pic = 'https://graph.facebook.com/' + id + '/picture';
				   var name = leaderrow.find('td.playername').text();
				   $(this).find('span.leaderpic').append('Current Leader: <br/><img title="'+ name +'" src="'+ pic +'" />');				   
			  });

			  if($(".standingstable").length > 0)
			  {
				  //$('table').css('border-collapse','collapse');
				  liveFilter = function(){ 
						var name = $.trim( $('#srchtxtname').val().toLowerCase() ); 
						//console.log(name);
						var type = $("input:radio[name=srchtype]:checked").val();
						//console.log(type);
						
						if( !name ) 
							return $('tr.playerrow').show(); 
						else 
							return $('tr.playerrow').each(function(){ 
								var row = $(this);
								if(row.find('.playername').length > 0 )
								{
									var text = row.find('.playername').text().toLowerCase();
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
								}
								else
								{
									var forname = row.find('.forname').text().toLowerCase();
									var againstname = row.find('.againstname').text().toLowerCase();

									if( ((forname.indexOf(name) == -1)&&(againstname.indexOf(name) == -1)) && type == "cont")
										row.hide();
									else if( ((forname.indexOf(name) != 0) && (againstname.indexOf(name) != 0)) && type == "str")
										row.hide();
									else if( (!((forname.lastIndexOf(name) == (forname.length-name.length)) && (forname.lastIndexOf(name)!= -1))) 
											&& (!((againstname.lastIndexOf(name) == (againstname.length-name.length)) && (againstname.lastIndexOf(name)!= -1)))
										&& type == "end")
										row.hide();
									else if((type == "eq") && (!((forname.indexOf(name) == 0) && (name.length==forname.length)))
											&& (!((againstname.indexOf(name) == 0) && (name.length==againstname.length))))
										row.hide();
									else
										row.show();

								} 
							});
						};
				$('#srchtxtname').keydown(function(){ setTimeout(liveFilter, 5); });
				$("input:radio[name=srchtype]").change(liveFilter);


				
				/*$('tr.playerrow').each(function(){
						if($(this).find('.playername').length > 0 )
						{
							var currentRound = $(this).parents('table.mainsheettable').find('span.currentround').text();
							var roundsCompleted = $(this).find('input:hidden').length;
							if(currentRound == roundsCompleted)
							{
								//$(this).find('.playername').css('background-color','#1FDB02');
								$(this).css('background-color','#93FC8F');
							}
							
						}					

					});*/
			  }
			  
		  
		  }
		  
		  $("a#submitlink").click(function(event) {
			if(event.preventDefault) 
				event.preventDefault();
			else
				event.returnValue = false; 
			
			var name = $.trim( $(this).parent().parent().find('.txtname').val().toLowerCase() ); 
			var wsname = $.trim( $(this).parent().parent().parent().find('span#wsname').text().toLowerCase() );
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
			
			var pid = /pid=(\d)/g.exec(name);
			var password = /password=(\w+)/g.exec(name);
			
			var loader = $(this).siblings('span#loading');
			
			
			if((pid==null)||(password==null))
			{
				params = { gid:gid[1],game:game[0]};
				
			}
			else
			{
				params = { gid:gid[1],game:game[0],pid:pid[1],password:password[1]};
			}
			loader.show();
			$.ajax({url:'gameresultjson.php',context:this,data:(params),dataType: "json",success:	   
				function(data){
					var count = data['count'];
					var dictionary = data['dictionary'];
					dictionary = ((dictionary == "sow")?"UK":"US");
					if(count == 0)
					{
						loader.hide();
						alert('Invalid game/Incomplete game!');
						return;
					}
					
					if(count != 2)
					{
						loader.hide();
						alert('Invalid game!\nGame has more than 2 players.');
						return;
					}
					
					var pname = Array();
					var ppic = Array();
					
					
					var pids = data['players'][1]['pemail']  + ',' + data['players'][2]['pemail'] ;
					var pscores = data['players'][1]['pscore']  + ',' + data['players'][2]['pscore'];
	               

					for (i=1;i<=count;i++)
					{
						pname[i]= data['players'][i]['pname']  + ((data['players'][i]['winner']  == 'yes')?'&nbsp<img src="star.png" />':'');
						ppic[i] = (data['players'][i]['winner']  == 'no')?'<img style="opacity:0.4;filter:alpha(opacity=40);" src="' + data['players'][i]['pic'] + '" />':'<img src="' + data['players'][i]['pic']  + '" />';
					}				
					
					loader.hide();
					
					if((data['players'][1]['pracklen'] !=0)&&(data['players'][2]['pracklen']!=0))
					{
						jConfirm('This game is still in progress, would you like to check this game?','Incomplete Game link', function(resp) {
							if(!resp)
							{
								return;
							}
							else
							{
								var msg = '<div style="margin-left:auto;margin-right:auto;width:200px"><table style="border-style:none;margin-left:auto;margin-right:auto;">';
								msg = msg + '<tr><td>' + pname[1] + '</td><td>&nbsp</td><td>' + pname[2] + '</td></tr>';
								msg = msg + '<tr><td>' + ppic[1] + '</td><td>&nbsp</td><td>' + ppic[2] + '</td></tr>';
								msg = msg + '<tr><td>' + data['players'][1]['pscore'] + '</td><td>&nbsp</td><td>' + data['players'][2]['pscore'] + '</td></tr>';
								msg = msg + '</table></div>';
								jConfirm(msg + '<br/>Dictionary: ' + dictionary + '<br/><br/>Would you like to submit this game to the selected bracket?','Game Result', function(resp) {
									if(resp)
									{										
										postwith("managetbreak.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1]});															
									}
								});
							
							}
						});		
					}
					else
					{
						var msg = '<div style="margin-left:auto;margin-right:auto;width:200px"><table style="border-style:none;margin-left:auto;margin-right:auto;">';
						msg = msg + '<tr><td>' + pname[1] + '</td><td>&nbsp</td><td>' + pname[2] + '</td></tr>';
						msg = msg + '<tr><td>' + ppic[1] + '</td><td>&nbsp</td><td>' + ppic[2] + '</td></tr>';
						msg = msg + '<tr><td>' + data['players'][1]['pscore'] + '</td><td>&nbsp</td><td>' + data['players'][2]['pscore'] + '</td></tr>';
						msg = msg + '</table></div>';
						jConfirm(msg + '<br/>Dictionary: ' + dictionary + '<br/><br/>Would you like to submit this game to the selected bracket?','Game Result', function(resp) {
							if(resp)
							{
								postwith("managetbreak.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1]});
													
							}
						});
					}
					
				}});
				
				return false;
			 });
		 
		 $("a#generatematchups").click(function(event) {
			if(event.preventDefault) 
				event.preventDefault();
			else
				event.returnValue = false; 
			
			var round = $.trim( $(this).parent().parent().find('.txtround').val().toLowerCase() ); 
			var updatesheet = $(this).parent().parent().find('#updates').is(':checked') ? 1:0;
			var wsname = $.trim( $(this).parent().parent().parent().find('span#wsname').text().toLowerCase() );

			wsname = wsname.match(/(lex|wordscraper|ws|scrabble)/g);
			wsname = wsname[0];

			wsname = (wsname=='scrabble')?'scrabble':((wsname=='lex')?'lexulous':'wordscraper');

			var ids = $(this).attr('href').split(',');
			var maxrounds = ids[2];			
			var wsheetid = ids[1];
			var ssheetid = ids[0];
			if(round=="")
			{
				alert('Please submit a round number!');
				return;
			}
			if(!isInt(round))
			{
				alert('Please submit a valid round number!');
			}
			else if((round > maxrounds) || round <= 0)
			{
				alert('Round number must be between 1 and ' + maxrounds + '!');
			
			}
			else
			{
			
				postwith("managetbreak.php",{generate:1,round:round,ssheetid:ssheetid,wsheetid:wsheetid,game:wsname,updatesheet:updatesheet});					
			
			}
			
			
	
			return false;
		 });


		 $("a#standingslink").click(function(event) {
				if(event.preventDefault) 
					event.preventDefault();
				else
					event.returnValue = false; 
				
				var round = $.trim( $(this).parent().find('#txtsround').val().toLowerCase() ); 
				var ids = $(this).attr('href').split(',');
				var maxrounds = ids[2];
				
				if(round=="")
				{
					alert('Please submit a round number!');
					return;
				}
				if(!isInt(round))
				{
					alert('Please submit a valid round number!');
				}
				else if((round > maxrounds) || round <= 0)
				{
					alert('Round number must be between 1 and ' + maxrounds + '!');
				
				}
				else
				{
				
					postwith("managetbreak.php",{standings:1,round:round});					
				
				}
				
				
		
				return false;
			 });


		 $("tr.hiderow").click(function(event) {
				var missingHead = $(this).next();
				missingHead.nextAll('tr').andSelf().toggle(!(missingHead.is(":visible")));			 								
				$(this).find('.hidestatus').html(missingHead.is(":visible")?'hide':'show');
			    if(missingHead.is(":visible"))
			    {
			    	liveFilter();
				}
				/*$(this).nextAll('tr').fadeToggle("slow",$.proxy (function () {
			 	
			 		$(this).find('.hidestatus').html($(this).next().is(":visible")?'hide':'show');
						if($(this).next().is(":visible"))
						{
							liveFilter();
						}
				 	},this));*/			 	
				return false;
			 });
		 

		 $(".imgQueryBingos").click(function(event){
				 if(event.preventDefault) 
					event.preventDefault();
				else
					event.returnValue = false; 
				
				var name = $.trim( $(this).parent().parent().find('.txtname').val().toLowerCase() ); 
				var wsname = $.trim( $(this).parent().parent().parent().find('span#wsname').text().toLowerCase() );
				
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
				
				var pid = /pid=(\d)/g.exec(name);
				var password = /password=(\w+)/g.exec(name);
				
				var loader = $(this).parent().parent().find('span#loading');
				
				
				if((pid==null)||(password==null))
				{
					params = { gid:gid[1],game:game[0]};
					
				}
				else
				{
					params = { gid:gid[1],game:game[0],pid:pid[1],password:password[1]};
				}
				loader.show();
				$.ajax({url:'gameresults_bingos.php',context:this,data:(params),dataType: "html",
				success:	   
					function(data){
						jAlert(data,'Bingos retrieved');
						loader.hide();
					},
				failure:
					function(jqXHR, textStatus, errorThrown){
						jAlert(textStatus,'An error occured.!');
						loader.hide();
					}				
				});
				
			    return false;
			 });

	    $("th.piccell").click(function(event){
			$(this).find('span').fadeToggle("slow","swing",$.proxy (function () {
				var isvisible = $(this).find('span').is(":visible");
				var orig = isvisible?'show':'hide';
				var next = isvisible?'hide':'show';		
				var newTitle = $(this).attr('title').replace(orig,next);
		 		$(this).attr('title',newTitle);
		 	  },this));
		});
		
		$("a.curscore").click(function(event){
		
			 if(event.preventDefault) 
				event.preventDefault();
			 else
				event.returnValue = false; 

			var hiddenvals = $(this).siblings('input');
			var cid = $(this).parent().parent().attr('id');
			var cname = $(this).parent().siblings().eq(1).text();
			var cpos = parseInt($(this).parent().siblings().eq(0).text());
			var totalvp = $(this).text();
			var bracket = $(this).parent().parent().siblings().eq(0).find('span.bracket').text();
			if (hiddenvals.length == 0)
			{
				jAlert('No games were found for this player','Alert');				
			}
			else
			{ 
				var bold = 'style="font-weight:bold;"';
				var html = '<div style="text-align:center;width:100;margin-left:auto;margin-right:auto"><img src="https://graph.facebook.com/' + cid + '/picture?type=square" />';
				html = html + '<br/><span ' + bold + '>Rank: ' + cpos + '<br/>Total Victory Points: ' + totalvp +  '</span>';
				html = html + '</div><table style="border-style:solid;border-width:1px;border-color:brown;">';
				html = html + '<tr style="text-align:center;background-color:brown;color:white;"><th>Round</th><th>Opponent</th><th>Game Link</th><th>For</th><th>Against</th><th>Victory Points</th></tr>';
				hiddenvals.each(function(){
					  var params = $(this).val().split(",");
					 var ifbold = (params[1]>params[2])?bold:'';
					  html = html + '<tr '+ ifbold + '><td>' + $(this).attr('class') + '</td><td>' + params[3] + '</td><td><a href="' + params[0] + '" target="_blank">' + params[0] + '</a></td><td>' + params[1] + '</td><td>' + params[2] + '</td><td>' + params[4] + '</td></tr>';
					});
				html = html + '</table>';
				jAlert(html,bracket + ' Game History for ' + cname);
			} 			
			
		});


		$("a.manualsubmit").click(function(event){
			
			 if(event.preventDefault) 
				event.preventDefault();
			 else
				event.returnValue = false;

			 var pname1;
			 var pscore1;
			 var pscore2;
			 var ppic1;
			 var ppic2;
			 var pid1;
			 var pid2;
			 var ssheetid;
			 var wsheetid;

			 pids = new Array();
			 pscores = new Array();

			 
			 ssheetid = $(this).parents(".standingstable").data("ssheetid");
			 wsheetid = $(this).parents(".standingstable").data("wsheetid");
			 pname1 = $(this).parent().siblings(".forname").text();
			 pname2 = $(this).parent().siblings(".againstname").text();
			 pscore1 = $.trim($(this).siblings(".forscore").val());
			 pscore2 = $.trim($(this).siblings(".againstscore").val());
			 if((pscore1.match(/^\d+$/) == null) || (pscore2.match(/^\d+$/) == null))
			 {
				 jAlert('Please enter valid scores for both players','Alert');
				 return;
			 }
			 pid1 = $(this).parent().siblings(".forname").data("id");
			 pid2 = $(this).parent().siblings(".againstname").data("id");
			 ppic1 = 'https://graph.facebook.com/' + pid1 + '/picture';
			 ppic2 = 'https://graph.facebook.com/' + pid2 + '/picture';
			 //ppic1 = '<img src="' + ppic1 + '" />';
			 //ppic2 = '<img src="' + ppic2 + '" />';


			 pname1 = pname1 + ((pscore1 > pscore2)?'&nbsp<img src="star.png" />':'');
			 ppic1 = (pscore1 < pscore2)?'<img style="opacity:0.4;filter:alpha(opacity=40);" src="' + ppic1 + '" />':'<img src="' + ppic1 + '" />';
			 pname2 = pname2 + ((pscore2 > pscore1)?'&nbsp<img src="star.png" />':'');
			 ppic2 = (pscore2 < pscore1)?'<img style="opacity:0.4;filter:alpha(opacity=40);" src="' + ppic2 + '" />':'<img src="' + ppic2 + '" />';

			 pids = new Array();
			 pids.push(pid1);
			 pids.push(pid2);
			 
			 pscores = new Array();
			 pscores.push(pscore1);
			 pscores.push(pscore2);
			 
			 var msg = '<div style="margin-left:auto;margin-right:auto;width:200px"><table style="border-style:none;margin-left:auto;margin-right:auto;">';
				msg = msg + '<tr><td>' + pname1 + '</td><td>&nbsp</td><td>' + pname2 + '</td></tr>';
				msg = msg + '<tr><td>' + ppic1 + '</td><td>&nbsp</td><td>' + ppic2 + '</td></tr>';
				msg = msg + '<tr><td>' + pscore1 + '</td><td>&nbsp</td><td>' + pscore2 + '</td></tr>';
				msg = msg + '</table></div>';
				jConfirm(msg + '<br/><br/>Would you like to submit this game for the selected bracket?','Game Result', function(resp) {
					if(resp)
					{
						//jAlert('Not yet implemented','Alert');
						postwith("managetbreak.php",{pids:pids.join(),pscores:pscores.join(),ssheetid:ssheetid,wsheetid:wsheetid,game:'unknown',gameid:1,standings:1,round:<?php echo isset($_POST['round'])?$_POST['round']:"0"; ?>});					
					}
				});

			//jAlert('Not yet implemented','Alert'); 

			
		});

	});
</script>
<style type="text/css">
tr.finished
{
  background-color:#93FC8F;
}
span.rematch
{
  color:#F70909;
}
tr.evenmiss
{
  background-color:#939393;
}
table.standingstable
{
	border-collapse:collapse;
}
a.scrabble
{
	display:none;
}
</style>
</head>
<body>
<div style='width:600px;text-align:center;margin-left:auto;margin-right:auto'>
<a href='managetbreak.php?clear=true'>Logout</a>
<br/>
</div>
<div id='content' style='width:600px;text-align:left;margin-left:auto;margin-right:auto'>
<?php
$client = Zend_Gdata_AuthSub::getHttpClient($_COOKIE['spread_token']);
$gdClient = new Zend_Gdata_Spreadsheets($client);

if(isset($_POST['pids']) && isset($_POST['pscores']) && isset($_POST['ssheetid'])&& isset($_POST['wsheetid']) && isset($_POST['game'])&& isset($_POST['gameid']))
{
	$pids = explode(',',$_POST['pids']);
	$pscores = explode(',',$_POST['pscores']);
	$ssheetid = $_POST['ssheetid'];
	$wsheetid = $_POST['wsheetid'];
	processResults($pids[0],$pscores[0],$pids[1],$pscores[1],$ssheetid,$wsheetid);
}

if(isset($_POST['standings']) && isset($_POST['round']))
{
	$round = $_POST['round'];
	displayStandings($round);
}
else if(isset($_POST['generate']) && isset($_POST['round']) && isset($_POST['ssheetid'])&& isset($_POST['wsheetid'])&& isset($_POST['game'])&& isset($_POST['updatesheet']))
{
	$round = $_POST['round'];
	$ssheetid = $_POST['ssheetid'];
	$wsheetid = $_POST['wsheetid'];
	$updatesheet = $_POST['updatesheet'];
	$game = $_POST['game'];
	generateMatchups($round,$ssheetid,$wsheetid,$updatesheet,$game);
}
else
{
	displaySpreadsheets();
}


?>
</div>
</body>
</html>