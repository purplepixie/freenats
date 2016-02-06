#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc!=2)
 {
 echo "Usage: ip_lookup.sh hostname\n";
 exit();
 }

echo $argv[1]." => ";
echo ip_lookup($argv[1]);
echo "\n";


?>
