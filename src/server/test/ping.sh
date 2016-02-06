#!/usr/bin/php -q
<?php
require("../base/timer.inc.php");
require("../base/tests.inc.php");
if ($argc!=2)
	{
	echo "Usage: ping.sh hostname\n";
	exit();
	}
$host=$argv[1];
while (1==1)
	{
	echo "Pinging ".$host.": ";
	$ptr=PingTest($host);
	//printf("%f\n",$ptr);
	echo $ptr."\n";
	sleep(1);
	}
?>
