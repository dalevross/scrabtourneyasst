<?php
set_include_path('.:/usr/local/php5/lib/php:/home/content/15/6983215/html/PEAR');
require_once 'Spreadsheet/Excel/Writer.php';
require_once './src/class.phpmailer.php';


$hostname='scrabtourneydb.db.6983215.hostedresource.com';
$username='scrabtourneydb';
$password='Scrab2lous';
$dbname='scrabtourneydb';
$usertable='GroupMember';

mysql_connect($hostname,$username, $password) OR DIE ('Unable to connect to database! Please try again later.');
mysql_select_db($dbname);

$query = 'SELECT Id, Name FROM ' . $usertable . ' WHERE Priority <> 1 ORDER BY Priority DESC,NAME ASC';
$result = mysql_query($query);

// We give the path to our file here
$workbook = new Spreadsheet_Excel_Writer('Members.xls');

$worksheet =& $workbook->addWorksheet('Members');

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

/*
$worksheet->write(1, 0, 'John Smith');
$worksheet->write(1, 1, 30);
$worksheet->write(2, 0, 'Johann Schmidt');
$worksheet->write(2, 1, 31);
$worksheet->write(3, 0, 'Juan Herrera');
$worksheet->write(3, 1, 32);
*/
$count = 1;

while($row = mysql_fetch_assoc($result)) {
	$worksheet->write($count, 0, $row['Name']);
	$worksheet->write($count, 1,"'" . $row['Id']);
	$worksheet->writeUrl($count, 2, 'http://apps.facebook.com/lexulous/?action=profile&profileid=' . $row['Id']);
	$worksheet->writeUrl($count, 3, 'http://apps.facebook.com/wordscraper/?action=profile&profileid=' . $row['Id']);
	$count++;
}
/*// We still need to explicitly close the workbook
$worksheet2 =& $workbook->addWorksheet('testing colors and patterns');
$worksheet2->setRow(1, 30);
$worksheet2->setRow(2, 30);
$worksheet2->setRow(3, 30);

// valid patterns are 0 to 18
for ($i = 0; $i <= 18; $i++)
{
    // green in different patterns
    $another_format1 =& $workbook->addFormat();
    $another_format1->setBgColor('green');
    $another_format1->setPattern($i);
    $worksheet2->write(1, $i, "pattern $i", $another_format1);

    // red in different patterns
    $another_format2 =& $workbook->addFormat();
    $another_format2->setFgColor('red');
    $another_format2->setPattern($i);
    $worksheet2->write(2, $i, "pattern $i", $another_format2);

    // mixed red and green according to pattern
    $another_format3 =& $workbook->addFormat();
    $another_format3->setBgColor('green');
    $another_format3->setFgColor('red');
    $another_format3->setPattern($i);
    $worksheet2->write(3, $i, "pattern $i", $another_format3);
}
*/

//$workbook->send('setPattern.xls');
$workbook->close();
echo "File Generated OK\n";

$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
//$mail->IsSMTP(); // telling the class to use SMTP

try {
	
	
	/*
	$mail->Host       = "smtpout.secureserver.net"; // SMTP server
	$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	//$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
	$mail->Port       = 465;
	//$mail->Username   = "kingdale20@gmail.com";  // GMAIL username
	//$mail->Password   = "dalessor";            // GMAIL password
	$mail->Username   = "admin@dalevross.com";  // GMAIL username
	$mail->Password   = "R3cursion";            // GMAIL password
	*/
  
	$mail->AddReplyTo('kingdale16@hotmail.com', 'Dale Ross');
	$mail->AddAddress('wendymidge@hotmail.com', 'Midge Midgley');
	$mail->AddAddress('andrewsmomma63@sbcglobal.net', 'Lori Martinez');
	$mail->AddAddress('biddleco@usc.edu', 'Susan Biddlecom');
	
	//$mail->AddBCC('jenneil.jacobs@gmail.com', 'Jenneil Jacobs');	
	$mail->SetFrom('scrabtourneyasst@dalevross.com', 'Scrabulous Tournament Assistant');
	//$mail->AddReplyTo('name@yourdomain.com', 'First Last');
	$mail->Subject = 'Scrabulous Tournament Group Members generated at ' . gmdate(DATE_RFC822);
	$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
	$mail->MsgHTML(file_get_contents('contents.html'));
	$mail->AddAttachment('tourneyasst75x75.png');      // attachment
	$mail->AddAttachment('Members.xls'); // attachment
	$mail->Send();
	echo "Message Sent OK\n";
} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage(); //Boring error messages from anything else!
}


?> 