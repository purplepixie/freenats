<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008 PurplePixie Systems

FreeNATS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FreeNATS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FreeNATS.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */
require("include.php");
$NATS->Start();
$min_delay=2;
$first=true;
$lastlog=0;
$pull=20;
$continue=false;
$level=0;

for ($a=1; $a<$argc; $a++)
	{
	switch($argv[$a])
		{
		case "-f":
			$continue=true;
			break;
		case "-c":
			$pull=$argv[++$a];
			break;
		case "-l":
			$level=$argv[++$a];
			break;
		case "-d": case "-s":
			$min_delay=$argv[++$a];
			break;
		default:

echo "FreeNATS logwatch System Log Watcher Tool\n";
echo "Usage: php logwatch.php [-f] [-c count] [-l level] [-d delay]\n";
echo "\n";
echo "Displays the last system event log items and optionally will\n";
echo "continue to monitor the log for new events.\n";
echo "\n";
echo "Options:\n";
echo " -f follow - continue to display output\n";
echo " -c count - display this many previous entries initially\n";
echo "            defaults to 20\n";
echo " -l level - only displays log levels of this level or below\n";
echo " -d delay - pause this many seconds between database fetches\n";
echo "            when using -f (defaults to 2 seconds)\n\n";
            exit();
           break;

		}
	}
$loop=true;
while ($loop)
	{
	$start=time();
	$q="SELECT * FROM fnlog";
	$wc="";
	if ($lastlog>0)
		{
		$wc.="logid>".$lastlog;
		}
	if ($level>0)
		{
		if ($lastlog>0) $wc.=" AND ";
		$wc.="loglevel<=".ss($level);
		}
	if ($wc!="") $q.=" WHERE ".$wc;
	$q.=" ORDER BY logid";
	if ($first)
		{
		$q.=" DESC LIMIT 0,".$pull;
		//$first=false;
		}
	else $q.=" ASC";
	$r=$NATS->DB->Query($q);
	if ($first) $s=array();
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$line=nicedt($row['postedx'])."\t".$row['loglevel']."\t".$row['modid'].":".$row['catid']."\t".$row['logevent']."\n";
		if ($first) $s[]=$line;
		else echo $line;
		if ($row['logid']>$lastlog) $lastlog=$row['logid'];
		}
	$NATS->DB->Free($r);
	if ($first)
		{
		for ($a=count($s)-1; $a>=0; $a--)
			{
			echo $s[$a];
			}
		}
	if (!$continue) $loop=false;
	if ($first) $first=false;
	if ($loop) while ( (time()-$start)<$min_delay ) sleep(1);
	}
?>