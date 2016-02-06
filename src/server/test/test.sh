#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc<3)
	{
	echo "Usage: test.sh test host/param [param]\n";
	exit();
	}
$test=$argv[1];
$host=$argv[2];
if ($argc==4) $param=$argv[3];
else $param=$host;
echo "Doing ".$test."(".$param.") to ".$host.": ";
$ptr=DoTest($test,$param,$host);
echo $ptr."\n";

?>
