<?php
//Connect To Database
$hostname='scrabtourneydb.db.6983215.hostedresource.com';
$username='scrabtourneydb';
$password='Scrab2lous';
$dbname='scrabtourneydb';
$usertable='GroupMember';

mysql_connect($hostname,$username, $password) OR DIE ('Unable to connect to database! Please try again later.');
mysql_select_db($dbname);

$query = 'SELECT Id as data, Name as label FROM ' . $usertable . ' ORDER BY Priority DESC,NAME ASC';
$result = mysql_query($query);
/*if($result) {
    while($row = mysql_fetch_array($result)){
        //$name = $row[$yourfield];
        echo 'Name: ' . $row['Name'] . ' Id : ' . $row['Id'];
		echo '<br/>';
    }
}
*/
// create a new XML document
$doc = new DomDocument('1.0','UTF-8');
// create root node
$root = $doc->createElement('Members');
$root = $doc->appendChild($root);

// process one row at a time
while($row = mysql_fetch_assoc($result)) {
 // add node for each row
	$occ = $doc->createElement($usertable);
	$occ = $root->appendChild($occ);
	foreach ($row as $fieldname => $fieldvalue) {
		$child = $doc->createElement($fieldname);
		$child = $occ->appendChild($child);
		$value = $doc->createTextNode($fieldvalue);
		$value = $child->appendChild($value);
	} // foreach
} // while

// get completed xml document
$xml_string = $doc->saveXML();
echo $xml_string;
?> 