#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc!=2)
 {
 echo "Usage: url_lookup.sh url\n";
 echo "i.e. https://bob.domain.com:123/?monkey=yes\n";
 exit();
 }

echo $argv[1]." => ";
echo url_lookup($argv[1]);
echo "\n";


?>
