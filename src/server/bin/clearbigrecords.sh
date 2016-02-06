#!/usr/bin/php -q
<?php
require("include.php");
$NATS->Start();

$q="SELECT testid,AVG(testvalue) FROM fnrecord GROUP BY testid";
$r=$NATS->DB->Query($q);

echo $q."\n";

while ($row=$NATS->DB->Fetch_Array($r))
{
$limit=100*$row['AVG(testvalue)'];
if ($limit>1)
	{	
	$q="DELETE FROM fnrecord WHERE testid=\"".$row['testid']."\" AND testvalue>".$limit;
	$NATS->DB->Query($q);
	echo $row['testid']." - deleted ".$NATS->DB->Affected_Rows()."\n";
	}
}

$NATS->Stop();

?>

