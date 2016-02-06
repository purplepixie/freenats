#!/usr/bin/php -q
<?php
// wspeed.sh URI iterations [delay]
// FreeNATS WebSpeed Dirty Debug Checker

$cfgReadSize=1024;
$cfgQuiet=false;

$argc=$_SERVER['argc'];
$argv=$_SERVER['argv'];

if ( ($argc<3) || ($argc>4) )
	{
	echo "FreeNATS wspeed.sh Debug Checker\n";
	echo "Usage: wspeed.sh URI iterations [delay]\n";
	echo "Example: wspeed.sh http://www.google.co.uk/ 100 60\n";
	echo " - get google 100 times with a 60 second sleep each time\n";
	echo "(default delay is 60 seconds if not specified)\n\n";
	exit();
	}
	
$uri=$argv[1];
$count=$argv[2];
if ($argc>3) $delay=$argv[3];
else $delay=60;

function fge($fp)
{
global $cfgQuiet,$cfgReadSize;
if ($cfgQuiet) return @fgets($fp,$cfgReadSize);
return fgets($fp,$cfgReadSize);
}

$tries=0;
$opened=0;
$unopened=0;
$readok=0;
$readfail=0;

echo "FreeNATS Web Speed Debug Checker - ".$uri."\n";
echo "Iterations: ".$count."      Delay: ".$delay."s\n\n";


for ($i=0; $i<$count; $i++)
	{
	$startTime=microtime(true);
	$tries++;
	echo $i.". fopen: ";
	if ($cfgQuiet) $fp=@fopen($uri,"r");
	else $fp=fopen($uri,"r");
	if ($fp>0) // opened ok
		{
		$opened++;
		$size=0;
		echo round(microtime(true)-$startTime,4)." ";
		echo "ok, fgets: ";
		while ($body=fge($fp))
			{
			echo ".";
			$size+=strlen($body);
			}
		echo " ".$size.", closing: ";
		if ($size>0) $readok++;
		else $readfail++;
		if ($cfgQuiet) @fclose($fp);
		else fclose($fp);
		echo "ok";
		}
	else
		{
		echo "FAILED";
		$unopened++;
		}
	echo " ".round(microtime(true)-$startTime,4);
	echo "\n";
	
	if ($i<($count-1))
		sleep($delay);
	}
	
echo "Finished.\n\n";
echo "Tries: ".$tries."\n";
echo $opened." Opened Ok, ".$unopened." FAILED to fopen()\n";
echo $readok." Read Ok, ".$readfail." FAILED to fgets()\n";
?>

