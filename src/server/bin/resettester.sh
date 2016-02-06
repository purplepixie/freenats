#!/usr/bin/php -q
<?php
require("include.php");
$NATS->Start();
if ( ($argc>1) && ($argv[1]=="-f") )
 $startx=time();
else $startx=time()-(5*60);

$q="UPDATE fntestrun SET finishx=1 WHERE finishx=0 AND startx<".$startx;
$NATS->DB->Query($q);
echo "Reset ".$NATS->DB->Affected_Rows()." Test Runs\n";

$NATS->Stop();

?>

