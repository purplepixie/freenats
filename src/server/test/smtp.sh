#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc<2)
 {
 echo "Usage: smtp.sh hostname [port] [timeout]\n";
 exit();
 }
echo "Connection Test\n";
if ($argc==2) $r=smtp_test_connect($argv[1]);
if ($argc==3) $r=smtp_test_connect($argv[1],$argv[2]);
if ($argc==4) $r=smtp_test_connect($argv[1],$argv[2],$argv[3]);
echo "Result: ".$r."\n\n";

echo "Connection Time\n";
if ($argc==2) $r=smtp_test_time($argv[1]);
if ($argc==3) $r=smtp_test_time($argv[1],$argv[2]);
if ($argc==4) $r=smtp_test_time($argv[1],$argv[2],$argv[3]);
echo "Result: ".$r."\n\n";
?>
