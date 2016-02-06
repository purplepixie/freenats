#!/usr/bin/php -q
<?php
// Usage logrel.php version file [URL]
if ($argc!=3 && $argc!=4)
 {
 echo "Usage: php release.php version filename [remote release URL]\n";
 exit();
 }

 $remoteURL = "http://www.purplepixie.org/freenats/release_upload.php";
 if ($argc==3)
 	$remoteURL = $_SERVER['argv'][3];
 
 
$v=$argv[1];
echo "Version: ".$v."\n";
$f=$argv[2];
echo "File   : ".$f."\n";


echo "Type: (R)elease or (d)evelopment: ";
$type=trim(fgets(STDIN));

$rel=true;
if ($type=="d") $rel=false;

echo "Public: (Y)es or (n)o: ";
$pub=trim(fgets(STDIN));
$public=true;
if ($pub=="n") $public=false;

$current=false;
$news=false;

if ($public)
{
	echo "Current: (Y)es or (n)o: ";
	$pub=trim(fgets(STDIN));
	$current=true;
	if ($pub=="n") $current=false;
	
	echo "News Item: (Y)es or (n)o: ";
	$pub=trim(fgets(STDIN));
	$news=true;
	if ($pub=="n") $news=false;
}

$rnotes="";
$changelog="";

echo "Release Notes (. on a single line to quit):\n";

$ins="";
while ($ins!=".")
{
	$ins=trim(fgets(STDIN));
	if ($ins!=".") $rnotes.=$ins."\n";
}
	
echo "Change Log (. on a single line to quit):\n";

$ins="";
while ($ins!=".")
{
	$ins=trim(fgets(STDIN));
	if ($ins!=".") $changelog.=$ins."\n";
}

echo "Version: ".$v."\n";
echo "Type   : ";
if ($rel) echo "Release";
else echo "Development";
echo "\n";
echo "Public : ";
if ($public) echo "Yes";
else echo "No";
echo "\n";
echo "News   : ";
if ($news) echo "Yes";
else echo "No";
echo "\n";
echo "Current: ";
if ($current) echo "Yes";
else echo "No";
echo "\n";
echo "Release Notes:\n";
echo $rnotes;
echo "\nChange Log:\n";
echo $changelog;

echo "\n\n";

echo "Proceed? (y/N): ";
$proc=trim(fgets(STDIN));
if ($proc != "y" && $proc != "Y")
{
	echo "Aborted\n";
	exit();
}


?>

