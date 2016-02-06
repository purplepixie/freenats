#!/usr/bin/php -q
<?php
require("include.php");
$NATS->Start();

$day=60*60*24;
$nowx=time();

$ret_alert=$NATS->Cfg->Get("retain.alert",356);
if ($ret_alert==0) $ret_alert=356;
$ret_testrun=$NATS->Cfg->Get("retain.testrun",30);
if ($ret_testrun==0) $ret_testrun=30;
$ret_records=$NATS->Cfg->Get("retain.record",356);
if ($ret_records==0) $ret_records=356;
$ret_syslog=$NATS->Cfg->Get("retain.syslog",30);
if ($ret_syslog==0) $ret_syslog=30;

if ($ret_alert>0)
	{
	$q="SELECT alertid FROM fnalert WHERE closedx<".($nowx-($day*$ret_alert))." AND closedx>0";
	$r=$NATS->DB->Query($q);
	$del_alert=0;
	$del_aa=0;
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$alid=$row['alertid'];
		$q="DELETE FROM fnalertlog WHERE alertid=".$row['alertid'];
		$NATS->DB->Query($q);
		$del_aa+=$NATS->DB->Affected_Rows();
		$q="DELETE FROM fnalert WHERE alertid=".$row['alertid'];
		$NATS->DB->Query($q);
		$del_alert++;
		}
	echo "Deleted ".$del_alert." Alerts\n";
	echo "Deleted ".$del_aa." Alert Log Entries\n";
	}

if ($ret_testrun>0)
	{
	$q="DELETE FROM fntestrun WHERE startx<".($nowx-($day*$ret_testrun));
	$NATS->DB->Query($q);
	$del_testrun=$NATS->DB->Affected_Rows();
	echo "Deleted ".$del_testrun." Test Runs\n";
	}

if ($ret_records>0)
	{
	$q="DELETE FROM fnrecord WHERE recordx<".($nowx-($day*$ret_records));
	$NATS->DB->Query($q);
	$del_records=$NATS->DB->Affected_Rows();
	echo "Deleted ".$del_records." Result Records\n";
	}
	
if ($ret_syslog>0)
	{
	$q="DELETE FROM fnlog WHERE postedx<".($nowx-($day*$ret_syslog));
	$NATS->DB->Query($q);
	$del_syslog=$NATS->DB->Affected_Rows();
	echo "Deleted ".$del_syslog." System Log Entries\n";
	}

$q="DELETE FROM fnsession WHERE updatex<".($nowx-$day); // live unaffected
$NATS->DB->Query($q);
$del_sessions=$NATS->DB->Affected_Rows();
echo "Deleted ".$del_sessions." Stale Sessions\n";

$NATS->Stop();

?>

