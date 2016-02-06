#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc<4)
 {
 echo "Usage: mysql.sh host user pass [database] [\"query\"] [timeout]\n";
 exit();
 }

$timeout=-1;
$query="";
$database="";

if ($argc>4) $database=$argv[4];
if ($argc>5) $query=$argv[5];
if ($argc>6) $timeout=$argv[6];

echo "Host: ".$argv[1]."\n";
echo "User: ".$argv[2]."\n";
echo "DB  : ".$database."\n";
echo "Qry : ".$query."\n";
echo "Time: ".$timeout."s\n";

echo "\nRows Test:\n";

$res=mysql_test_rows($argv[1],$argv[2],$argv[3],$database,$timeout,$query,true);

echo "\nResult: ".$res."\n";

echo "\nTimer Test:\n";

$time=mysql_test_time($argv[1],$argv[2],$argv[3],$database,$timeout,$query,true);

echo "\nResult: ".$time."\n";
?>
