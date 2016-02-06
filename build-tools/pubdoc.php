<?php
date_default_timezone_set("Etc/GMT");
$fl=scandir("doc/txt");
foreach($fl as $q)
{
$cmd=array();
if (($q[0]!=".")&&($q[0]!="~"))
	{
	echo "File: ".$q."\n";
	$dp=strpos($q,".");
	$fn="doc/html/".substr($q,0,$dp).".html";
	
	$cmd[]="/bin/echo \"<p align=\\\"right\\\"><i>Updated: ".date("H:i:s d/m/Y")."</i></p>\" > ".$fn;
	$cmd[]="/bin/echo >> ".$fn;
	$cmd[]="/bin/cat doc/txt/".$q." >> ".$fn;
	foreach ($cmd as $c)
		{
		echo $c."\n";
		exec($c);
		}
	echo "\n";
	}
}
?>
