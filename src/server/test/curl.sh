#!/usr/bin/php -q
<?php
require("../base/timer.inc.php");
require("../base/tests.inc.php");

if ($argc!=2)
	{
	echo "Usage: curl.sh url\n";
	exit();
	}

$timer=new TFNTimer();

$cmd="curl -o \"curl.txt\" -s -S --insecure --connect-timeout 10 ";
$cmd.="-w 'CURL Time: %{time_total} ' ".$argv[1];
echo $cmd."\n\n";
$timer->Start();
passthru($cmd);
$curl=$timer->Stop();
echo "\n";
echo "FN Timer : ".$curl." (".($curl*1000)." ms)\n\n";

$timer->Start();
$wtime=DoTest("wtime",$argv[1]);
$wtimer=$timer->Stop();
echo "WTime    : ".$wtime."\n";
echo "Timer    : ".$wtimer."\n";
echo "\n";


$timer->Start();
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$argv[1]);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
if (!$output=curl_exec($ch))
	{
	echo "CURL Error\n";
	$size=0;
	}
else $size=curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD);
curl_close($ch);
$curltime=$timer->Stop();

echo "PHP CURL  : ".$curltime."\n";
echo "Size      : ".$size."\n";
echo "\n";

$timer->Start();
$fp=fopen($argv[1],"r")
 or die("fopen failed");
$fopen=$timer->Stop();
while (!feof($fp))
	{
	$s=fgets($fp,1024);
	}
$fgets=$timer->Stop();
fclose($fp);

echo "fopen     : ".$fopen."\n";
echo "fopen Tot : ".$fgets."\n";
echo "\n";

?>
