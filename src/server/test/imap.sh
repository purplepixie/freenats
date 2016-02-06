#!/usr/bin/php -q
<?php
require("../base/tests.inc.php");
if ($argc<4)
 {
 echo "Usage: imap.sh host user pass [protocol] [port] [ssl]\n";
 exit();
 }

$prot="imap";
$port=143;
$ssl=false;

if ($argc>4) $prot=$argv[4];
if ($argc>5) $port=$argv[5];
if ($argc>6) $ssl=true;

echo "Host: ".$argv[1]."\n";
echo "User: ".$argv[2]."\n";
echo "Prot: ".$prot."\n";
echo "Port: ".$port."\n";
echo "SSL : ";
if ($ssl) echo "Yes";
else echo "No";
echo "\n\nConnection Test\n";

$r=imap_test_connect($argv[1],$argv[2],$argv[3],10,$prot,$port,$ssl,true);

echo "Result: ".$r."\n";

echo "\nConnection Timer\n";

$r=imap_test_time($argv[1],$argv[2],$argv[3],10,$prot,$port,$ssl,true);

echo "Result: ".$r."\n";
?>
