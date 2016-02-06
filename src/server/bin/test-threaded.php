<?php
require("include.php");
$NATS->Start();

$nodelist=array();
for ($a=1; $a<$argc; $a++)
	$nodelist[]=$argv[$a];


echo "test-threaded.sh: spawning node tester processes\n";

$q="SELECT nodeid FROM fnnode WHERE nodeenabled>0";
if (count($nodelist)>0) 
	{
	$q.=" AND nodeid IN(";
	$first=true;
	foreach($nodelist as $node)
		{
		if ($first) $first=false;
		else $q.=",";
		$q.="\"".ss($node)."\"";
		}
	$q.=")";
	}
else $q.=" AND masterid=\"\"";

$q.=" ORDER BY weight ASC";

$r=$NATS->DB->Query($q);
$spawn_delay=$NATS->Cfg->Get("test.spawndelay",0);
//echo "Delay: ".$spawn_delay."\n";
if ($spawn_delay>0) $spawn_delay=$spawn_delay*1000000; // convert to us (microseconds / millionths)
//echo "Delay: ".$spawn_delay."\n";
$first=true;
while ($row=$NATS->DB->Fetch_Array($r))
	{
	if ($first) $first=false;
	else
		{
		// Test Execution Delay / test.spawndelay
		if ($spawn_delay>0) usleep($spawn_delay);
		}
	$cmd="php ./tester.php ".$row['nodeid']." > /tmp/nr.".$row['nodeid']." &";
	echo $cmd."\n";
	exec($cmd);
	}
$NATS->DB->Free($r);
$NATS->Stop();
?>

