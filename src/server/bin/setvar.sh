#!/usr/bin/php -q
<?php
require("include.php");
$NATS->Start();


if ($argc!=3)
	{
	echo "Usage: setvar.sh var.name value\n";
	echo " set var.name to var.value (create if new)\n";
	echo "Usage: setvar.sh -d var.name\n";
	echo " delete var.name\n";
	$NATS->Stop();
	exit();
	}

if ($argv[1]=="-d") // delete
	{
	$q="DELETE FROM fnconfig WHERE fnc_var=\"".ss($argv[2])."\"";
	$r=$NATS->DB->Query($q);
	echo "Deleted ".$NATS->DB->Affected_Rows()." Variables\n";
	}
else // update/create
	{
	$q="UPDATE fnconfig SET fnc_val=\"".ss($argv[2])."\" WHERE fnc_var=\"".ss($argv[1])."\"";
	$r=$NATS->DB->Query($q);
	$res=$NATS->DB->Affected_Rows();
	if ($res>0) echo "Updated ".$res." Variables\n";
	else
		{ // doesn't exist or the same value
		$q="INSERT INTO fnconfig(fnc_var,fnc_val) VALUES(\"".ss($argv[1])."\",\"".ss($argv[2])."\")";
		$r=$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()>0) echo "Inserted ".$NATS->DB->Affected_Rows()." Variables\n";
		else echo "Failed to update - value the same?\n";
		}
	}

$NATS->Stop();

?>
