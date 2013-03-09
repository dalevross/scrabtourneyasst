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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Wordscraper/Lex League Assistant</title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">

</head>
<body>
	<div
		style='width: 600; text-align: center; margin-left: auto; margin-right: auto; margin-top: 200px'>
		<span>This application needs to be authorized before you will be able
			to submit results</span> <br /> <span>Click <a
			href=<? echo $googleUri;?>>here</a> to authorize this application.</span>
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
	echo '<a href="manageleague.php">Submission Page</a>';
	?>
	<br />
	<br />
	<form>
		Name Filter:&nbsp&nbsp
		<input type="text" id="srchtxtname" name="srchname" />
		<br />
		<input type="radio" name="srchtype" value="cont" checked="1" />
		Contains &nbsp&nbsp&nbsp
		<input type="radio" name="srchtype" value="str" /> Starts With
		&nbsp&nbsp&nbsp
		<input type="radio" name="srchtype" value="end" /> Ends With
		&nbsp&nbsp&nbsp
		<input type="radio" name="srchtype" value="eq" /> Equals
	</form>

	<?php
	$entries = iterator_to_array ($feed->entries);

	usort($entries,"cmp_entries");

	foreach($entries as $entry) {

		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = explode('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8

		if((strpos($sheetname, 'Score Sheet')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) &&  (strpos(strtolower($sheetname), 'example')===false) &&  (strpos(strtolower($sheetname), 'break')===false))
		{
			$color = (strpos(strtolower($sheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($sheetname), 'lex')!==false)?"#2BB0E8":"red");
			$handicap = (strpos(strtolower($sheetname), 'handicap')!==false);
			?>
	<table style="border-style:solid;border-width:1px;border-color:<?echo $color;?>;padding:10px;margin-top:10px;width:500px">
		<tr>
			<th style="background-color:<?echo $color;?>;color:white;"><?
			echo $sheetname;
			?>
			</th>
		</tr>
		<td><?
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
		$currWkshtId = explode('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];
		$color = (strpos(strtolower($worksheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($worksheetname), 'lex')!==false)?"#2BB0E8":"red");

		?>
	<table class='standingstable' data-ssheetid='<?php echo $key;?>'
		data-wsheetid='<?php echo $currWkshtId;?>'
		style="border-style: none; margin-top: 10px; width: 100%">
		<tr>
			<th colspan="<? echo ($handicap)?"4":"3"; ?>" style="background-color:<?echo $color;?>;color:white;text-align:center;"><span
				class="division"> <?
				echo $worksheetname;
				?> </span></th>
			<th class='piccell' title='Click here to hide leader' style="background-color:<?php echo $color;?>;color:white;text-align:center;"><span
				class='leaderpic'></span></th>
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
					$player_names[] = $val;
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
					$pointsscored[] = floatval($val);
					break;
				case 19:
					$handicap[] = floatval($val);
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
	echo "<tr><td colspan='5' style='text-align:center;background-color:black;color:white'>$completed out of $total games completed</td></tr>";
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

	$numPlayers = 11;
	$query->setMinCol(3);
	$query->setMaxCol(3);
	$query->setMinRow(31);
	$query->setMaxRow(30+$numPlayers);
	$feed = $gdClient->getCellFeed($query);
	foreach($feed as $cellEntry) {
		$val = $cellEntry->cell->getText();

		preg_match("/profileid=(?P<profileid>\d+)/",$val,$match);
		$profileid = $match['profileid'] ;
		$player_ids[] = $profileid;

	}


	$query->setMinCol(1);
	$query->setMaxCol(29);
	$query->setMinRow(5);
	$query->setMaxRow(25);
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		$r = ((int)(round(($row - 4)/2))) - 1;
		$c = ($column-4);
		if((($column > 17) && ($column != 29)) )
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
				case (($column > 2) && ($column < 15) ):
					if((($row % 2)!=0) && ($r < $c))
					{
						$for[$r][$c] = intval($val);
						$against[$c][$r] = intval($val);
					}
					elseif ((($row % 2)==0) && ($r < $c))
					{
						$against[$r][$c] = intval($val);
						$for[$c][$r] = intval($val);
						$links[$r][$c] = $inputVal;
						$links[$c][$r] = $inputVal;
					}
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
				default:
					break;

			}
		}
	}
	
	 if(isset($_GET["debug"]))
	 {
	 	print_r($for);
	 	
	 }

	if(!is_array($for))
	{
		$for = array();
	}

	if(!is_array($against))
	{
		$against = array();
	}

	if(!is_array($links))
	{
		$links = array();
	}

	$missing = array();

	$missingids = array();

	for($i=0;$i<count($player_names);$i++)
	{
		for($j=$i + 1;$j<count($player_names);$j++)
		{
			if((!array_key_exists($j,$for[$i])) && ($i != $j))
			{

				$missing[$player_names[$i]]= $player_names[$j];
					
					
				$missingids[$player_names[$i]]=$player_ids[$j];
				$missingids[$player_names[$j]]=$player_ids[$i];
			}

		}






	}

	for($i=0;$i<count($player_names);$i++)
	{
		if(!array_key_exists($i,$links))
		{
			$links[$i] = array();
			$for[$i] = array();
		}
		else
		{
			array_walk($links[$i],'getlinkfromcell');

		}
			
		if(!array_key_exists($i,$against))
		{
			$against[$i] = array();
		}
	}

	ksort($links);
	ksort($for);
	ksort($against);




	for($i=0;$i<count($wins);$i++)
	{
		$data[] = array('name' => $player_names[$i],'id' => $player_ids[$i], winstoplayed => $wins[$i] . '/' . $played[$i],'spread' => (($pointspread[$i] > 0)?'+':'') . $pointspread[$i],'links'=>$links[$i],'for'=>$for[$i],'against'=>$against[$i]);
	}

	array_multisort($wins, SORT_DESC, $pointspread, SORT_DESC,$aggregate, SORT_DESC,$rank,SORT_ASC, $data);
	//print_r($data);
	for($i=0;$i<count($wins);$i++)
	{
		$class = (count($data[$i]['links'])==10)?" finished":"";
		$rank  = $i + 1;
		echo "<tr id='{$data[$i]['id']}' class='playerrow$class' ><td>$rank.</td><td class='playername'>{$data[$i]['name']}</td><td title='Click score to show game history' style='text-align:right;'>";
		foreach($data[$i]['links'] as $oppindex=>$link)
		{
			echo "<input type='hidden' data-link='$link' data-for='{$data[$i]['for'][$oppindex]}' data-against='{$data[$i]['against'][$oppindex]} data-opponent='{$player_names[$oppindex]}' class='$oppindex' />";
		}
		echo "<a class='curscore' href='#'>{$data[$i]['winstoplayed']}</a></td>";
		echo "<td style='text-align:right;'>{$data[$i]['spread']}</td></tr>";
	}
	$total = intval( count($wins) * (count($wins)-1) / 2);
	echo "<tr><td colspan='4' style='text-align:center;background-color:black;color:white'>$completed out of $total games completed</td></tr>";

	if((count($missing)>0))
	{
			
		echo "<tr class='hiderow'><td colspan='4' style='text-align:center;background-color:brown;color:white;'>Click here to <span class='hidestatus'>hide</span> missing games</td>";
			
		echo "<tr><td colspan='4' style='text-align:center;'>Missing Games</td></tr>";
		$ix = false;
		foreach ($missing as $p2 => $p1) {
			$class = ($ix)?" evenmiss":"";
			echo "<tr class='playerrow$class' ><td class='forname' data-id='{$missingids[$p1]}' >$p1</td><td colspan='2'  style='text-align:center;'><input class='forscore' size='4'/>&nbsp<a class='manualsubmit' href='#'>Submit</a>&nbsp<input class='againstscore' size='4'/></td><td class='againstname' data-id='{$missingids[$p2]}'>$p2</td></tr>";
			$ix = !$ix;
		}
	}



}

function getlinkfromcell(&$celldata, $key)
{
	$celldata = substr($celldata, strpos($celldata,"(") + 2);
	$celldata = substr($celldata, 0, strpos($celldata, "\""));
}


function fixLinks()
{
	global $gdClient;
	$feed = $gdClient->getSpreadsheetFeed();

	//echo "== Available Spreadsheets ==</br>";
	//echo '<a href="manageleague.php">Submission Page</a>';
	$entries = iterator_to_array ($feed->entries);

	usort($entries,"cmp_entries");

	foreach($entries as $entry) {

		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = explode('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8

		if((strpos($sheetname, 'Word')!==false)  &&  (strpos($sheetname, 'Score Sheet')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) &&  (strpos(strtolower($sheetname), 'example')===false) &&  (strpos(strtolower($sheetname), 'break')===false))
		{
			fixWorksheetLinks($currKey);

		}
	}

}

function fixWorksheetLinks($key)
{
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
	$query->setSpreadsheetKey($key);
	$feed = $gdClient->getWorksheetFeed($query);
	//print "== Available Worksheets ==\n";


	foreach($feed->entries as $entry) {
		$worksheetname = $entry->title->text;
		$currWkshtId = explode('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];


		echo $worksheetname;
		if((strpos($worksheetname, $_GET["div"])!==false))
		{
			processFixesForWorskeet($key,$currWkshtId);
		}

			
	}

}

function processFixesForWorskeet($key,$currWkshtId)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($key);
	$query->setWorksheetId($currWkshtId);
	$query->setMinCol(4);
	$query->setMaxCol(14);
	$query->setMinRow(5);
	$query->setMaxRow(26);
	$feed = $gdClient->getCellFeed($query);
	$completed = 0;
	foreach($feed as $cellEntry) {
		$column = $cellEntry->cell->getColumn();
		$row = $cellEntry->cell->getRow();
		$val = trim($cellEntry->cell->getText());
		$inputVal= trim($cellEntry->cell->getInputValue());
		if(strlen($inputVal) < 15)
		{
			if(startsWith($inputVal,"=+"))
			{
				//echo "<br/><span style='font-weight:bold'>$inputVal</span><br/>";
			}
			else if(startsWith($inputVal,"="))
			{
				$newInputVal = str_replace("=","=+",$inputVal);
				$gdClient->updateCell($row,$column,$newInputVal,$key,$currWkshtId);
				//echo "<br/>$inputVal<br/>";

			}
		}
			
	}


}

function startsWith($haystack,$needle,$case=true)
{
	if($case)
	return strpos($haystack, $needle, 0) === 0;

	return stripos($haystack, $needle, 0) === 0;
}


function displaySpreadsheets($type="normal")
{
	global $gdClient;
	$feed = $gdClient->getSpreadsheetFeed();

	//echo "== Available Spreadsheets ==</br>";
	echo '<a href="manageleague.php?standings=1">View League Standings</a>';
	if($type=="olympics")
	{
		echo '<br/><a href="manageleague.php">League Submissions</a>';
	}
	$entries = iterator_to_array ($feed->entries);

	usort($entries,"cmp_entries");
	foreach($entries as $entry) {

		$sheetname = $entry->title->text;
		$link = $entry->getLink("http://schemas.google.com/spreadsheets/2006#worksheetsfeed")->href;
		$currKey = explode('/', $entry->id->text);
		$currKey = $currKey[5];
		//#2BB0E8

		if($type=="olympics")
		{

			$condition = ((strpos($sheetname, 'Olympic')!==false) && (strpos($link, 'full')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false));// &&  (strpos(strtolower($sheetname), 'Results')!==false));
		}
		else
		{
			$condition = ((strpos($sheetname, 'Score Sheet')!==false) && (strpos($link, 'full')!==false)  &&  (strpos(strtolower($sheetname), 'closed')===false) &&  (strpos(strtolower($sheetname), 'break')===false)) &&  (strpos(strtolower($sheetname), 'scrabble')===false);
		}

		if($condition)
		{
			$color = ($type=="olympics")?"yellow":((strpos(strtolower($sheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($sheetname), 'lex')!==false)?"#2BB0E8":"red"));

			?>
		<table style="border-style:solid;border-width:1px;border-color:<?echo $color;?>;padding:10px;margin-top:10px">
			<tr>
				<th style="background-color:<?echo $color;?>;color:white;"><?
				echo $sheetname;
				?>
				</th>
			</tr>
			<td><?
			displayWorksheets($currKey,$type);
			?>
			</td>
			</tr>
		</table>
		<?


		}
	}

}

function displayWorksheets($key,$type)
{
	global $gdClient;
	$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
	$query->setSpreadsheetKey($key);
	$feed = $gdClient->getWorksheetFeed($query);
	//print "== Available Worksheets ==\n";
	//echo '<ul>';
	foreach($feed->entries as $entry) {
		$worksheetname = $entry->title->text;
		$currWkshtId = explode('/', $entry->id->text);
		$currWkshtId = $currWkshtId[8];
		//echo '<li><span id="wsname">'. $worksheetname . "</span>   <input type='text' autocomplete='off' class='txtname' name='name' /> <a id='submitlink' href='$key,$currWkshtId'>Submit Game</a><span id='loading' style='display:none'><img src='ajax-loader.gif' />Processing...</span></li>";

		if((strpos(strtolower($worksheetname), 'lex') !==false) ||(strpos(strtolower($worksheetname), 'ws') !==false) || (strpos(strtolower($worksheetname), 'wordscraper') !==false) || (strpos(strtolower($worksheetname), 'scrabble')!==false)  )
		{
			$color = (strpos(strtolower($worksheetname), 'scrabble')!==false)?"#036A4D":((strpos(strtolower($worksheetname), 'lex')!==false)?"#2BB0E8":"red");

			echo "<table style='border-style:solid;border-width:1px;border-color:$color;padding:10px;margin-top:10px'>";
			echo '<tr><th colspan="4" style="text-align:center;background-color:' . $color .  ';color:white"><span id="wsname">'. $worksheetname . "</span></td></tr>";
			echo  "<tr><td><img class='imgQueryBingos' src='q.jpeg' title='Click here to query bingos' width='20' height='20' /></td><td><a id='submitlink' class='$type' href='$key,$currWkshtId'>Submit Game</a><span id='loading' style='display:none'><img src='ajax-loader.gif' />Processing...</span></td><td colspan='2'><input type='text' autocomplete='off' class='txtname' name='name' title='Enter game link here and click the Submit Game link to the left' size='50'/></td></tr></table>";
		}
	}
	//echo '</ul>';
}


function processResults($pid1,$pscore1,$pid2,$pscore2,$ssheetid,$wsheetid,$type)
{
	global $gdClient;
	$player_ids = array();
	$player_names = array();
	$query = new Zend_Gdata_Spreadsheets_CellQuery();
	$query->setSpreadsheetKey($ssheetid);
	$query->setWorksheetId($wsheetid);

	$numPlayers = ($type=="olympics")?9:11;
	$query->setMinCol(2);
	$query->setMaxCol(3);
	$query->setMinRow(31);
	$query->setMaxRow(30+$numPlayers);
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
	<title>Wordscraper/Lex League Assistant</title> <script
		type="text/javascript" src="jquery-1.7.min.js"></script>
	<script src="jquery.alerts.js" type="text/javascript"></script>
	<link href="jquery.alerts.css" rel="stylesheet" type="text/css"
		media="screen" />
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
	      
		  var divcontent = $("div#content");
		  if(divcontent.find("table").length==0)
		  {
			 divcontent.find("a").remove();
			 divcontent.append('<p>No active league games were found in your documents.<br/>Care to watch a video?</p><iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/m8skAMI03HI" frameborder="0"></iframe>');
				
		  }
		  else
		  {
		 	  $("div#content > table").css('background','url("http://www.dalevross.com/scrabtourneyasst/tourneyasst75x75.png")');
			  $("div#content table").not("div#content > table").css('background-color','white');
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
			var type = $(this).attr('class');
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
							jConfirm(msg + '<br/>Dictionary: ' + dictionary + '<br/><br/>Would you like to submit this game to the selected division?','Game Result', function(resp) {
								if(resp)
								{
									if(type=='olympics')
									{
										postwith("manageleague.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1],olympics:1});
									}
									else
									{
										postwith("manageleague.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1]});
									}					
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
					jConfirm(msg + '<br/>Dictionary: ' + dictionary + '<br/><br/>Would you like to submit this game to the selected division?','Game Result', function(resp) {
						if(resp)
						{
							if(type=='olympics')
							{
								postwith("manageleague.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1],olympics:1});
							}
							else
							{
								postwith("manageleague.php",{pids:pids,pscores:pscores,ssheetid:ssheetid,wsheetid:wsheetid,game:game[0],gameid:gid[1]});
							}
												
						}
					});
				}
				
			}});
			
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
			var division = $(this).parent().parent().siblings().eq(0).find('span.division').text();
			if (hiddenvals.length == 0)
			{
				jAlert('No games were found for this player','Alert');				
			}
			else
			{ 
				var bold = 'style="font-weight:bold;"';
				var html = '<div style="text-align:center;width:100;margin-left:auto;margin-right:auto"><img src="https://graph.facebook.com/' + cid + '/picture?type=square" />';
				html = html + '<br/><span ' + bold + '>Rank: ' + cpos + '<br/>Win/Total: ' + totalvp +  '</span>';
				html = html + '</div><table style="border-style:solid;border-width:1px;border-color:brown;">';
				html = html + '<tr style="text-align:center;background-color:brown;color:white;"><th>Opponent #</th><th>Opponent</th><th>Game Link</th><th>For</th><th>Against</th><th>Margin</th></tr>';
				hiddenvals.each(function(){
					  var margin = $(this).data("for") - $(this).data("against");
					  margin = (margin > 0) ? "+" + margin : margin; 
					 var ifbold = ($(this).data('for')> $(this).data('against'))?bold:'';
					  html = html + '<tr '+ ifbold + '><td>' + $(this).attr('class') + '</td><td>' + this.data('opponent') + '</td><td><a href="' + $(this).data('link') + '" target="_blank">' + $(this).data('link') + '</a></td><td>' + $(this).data('for') + '</td><td>' + $(this).data('against') + '</td><td>' + margin + '</td></tr>';
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
				jConfirm(msg + '<br/><br/>Would you like to submit this game for the selected division?','Game Result', function(resp) {
					if(resp)
					{
						//jAlert('Not yet implemented','Alert');
						postwith("manageleague.php",{pids:pids.join(),pscores:pscores.join(),ssheetid:ssheetid,wsheetid:wsheetid,game:'unknown',gameid:1,standings:1});					
					}
				});

			//jAlert('Not yet implemented','Alert'); 

			
		});
	});
</script>
		<style type="text/css">
tr.finished {
	background-color: #93FC8F;
}

span.rematch {
	color: #F70909;
}

tr.evenmiss {
	background-color: #939393;
}

table.standingstable {
	border-collapse: collapse;
}

a.scrabble {
	display: none;
}
</style>

</head>
<body>
	<div
		style='width: 600px; text-align: center; margin-left: auto; margin-right: auto'>
		<a href='manageleague.php?clear=true'>Logout</a> <br /> <img
			src="owl.jpg" /> <br /> <a href='manageleague.php?olympics=true'>Olympics
			Submissions</a> <br />
	</div>
	<div id='content'
		style='width: 600px; text-align: left; margin-left: auto; margin-right: auto'>
		<?
		$client = Zend_Gdata_AuthSub::getHttpClient($_COOKIE['spread_token']);
		$gdClient = new Zend_Gdata_Spreadsheets($client);
		if(isset($_GET['standings']) || isset($_POST['standings']))
		{
			displayStandings();
		}
		else if(isset($_GET['fixLinks']))
		{
			fixLinks();
		}
		else if(isset($_GET['olympics']) || isset($_POST['olympics']))
		{
			displaySpreadsheets("olympics");
		}
		else
		{
			displaySpreadsheets();
		}

		if(isset($_POST['pids']) && isset($_POST['pscores']) && isset($_POST['ssheetid'])&& isset($_POST['wsheetid']) && isset($_POST['game'])&& isset($_POST['gameid']))
		{
			$type = (isset($_POST['olympics']))?"olympics":"normal";
			$pids = explode(',',$_POST['pids']);
			$pscores = explode(',',$_POST['pscores']);
			$ssheetid = $_POST['ssheetid'];
			$wsheetid = $_POST['wsheetid'];
			processResults($pids[0],$pscores[0],$pids[1],$pscores[1],$ssheetid,$wsheetid,$type);
		}
		?>
	</div>
</body>
		</html>