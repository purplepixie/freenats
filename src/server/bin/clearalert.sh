#!/usr/bin/php -q
<?php
require("include.php");
$NATS->Start();

$q="UPDATE fnalert SET closedx=".time()." WHERE closedx=0";
$NATS->DB->Query($q);
echo "Closed ".$NATS->DB->Affected_Rows()." Alerts\n";

$q="UPDATE fnnode SET alertlevel=0 WHERE alertlevel>1";
$NATS->DB->Query($q);
echo "Closed ".$NATS->DB->Affected_Rows()." Node Alerts\n";

$NATS->Stop();

?>

