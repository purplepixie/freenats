<?php
/*
global $NATS;
//$NATS->Event("Included Event Tester",10);

function my_node_test_start_handler($data)
{
	global $NATS;
	if ($data['in_schedule']) $s=$data['nodeid']." is in the schedule and will be tested";
	else $s=$data['nodeid']." is NOT in the schedule and so won't be tested";
	$NATS->Event($s,10);
}

$NATS->AddEventHandler("node_test_start","my_node_test_start_handler");

function my_alert_action($data)
{
	global $NATS;
	if ($data['name']!="bob") return false; // only want my handler
	
	$NATS->Event("MyAction: ".$data['data'],10);
	return true;
}

$NATS->AddEventHandler("alert_action","my_alert_action");


function alert_action_syslog($data)
{
	global $NATS;
	$NATS->Event("Syslog AA Called for ".$data['name'],10,"Syslog","Start");
	if ($data['name']!="_syslog") return false;
	$lvl=LOG_ERR;
	
	define_syslog_variables();
	openlog("FreeNATS", LOG_PID | LOG_PERROR, LOG_LOCAL0);

	if (syslog($lvl,$data['data'])===false)
		$NATS->Event("Syslog Failed for ".$data['data'],2,"Syslog","Write");
	else
		$NATS->Event("Syslog Succeeded for ".$data['data'],10,"Syslog","Write");
	closelog();
	
	return true;
}

$NATS->AddEventHandler("alert_action","alert_action_syslog");

function alert_action_console($data)
{
	if ($data['name']!="_console") return false;
	$cmd="echo \"FreeNATS: ".$data['data']."\" > /dev/console";
	@exec($cmd);
	return true;
}

$NATS->AddEventHandler("alert_action","alert_action_console");

function alert_action_file($data)
{
	if ($data['name']=="mq") $file="/tmp/fndebug";
	else if ($data['name']=="_detailaction") $file="/tmp/fndetail";
	else return false;
	$fp=fopen($file,"a");
	fputs($fp,"-- ".date("Y-m-d H:i:s")." --\n");
	fputs($fp,$data['data']);
	fputs($fp,"\n-- ENDS --\n");
	fclose($fp);
	return true;
}

$NATS->AddEventHandler("alert_action","alert_action_file");
*/
?>