<?php // detailed_alerts.php version 0.01 16/08/2009
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2009 PurplePixie Systems

FreeNATS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FreeNATS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FreeNATS.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/freenats
-------------------------------------------------------------- */

/* Description:

This is a custom event handler for FreeNATS v1 and relies on the following
event types: alert_open, alert_close, alert_action

It also relies on the following FreeNATS class methods:
	TFreeNATS::ActionAddData, GetNodeTests, GetTest, Event, AddEventHandler
	TNATS_DB::Query, Fetch_Array, Free

USAGE INSTRUCTIONS:

Place into the server/base/site/events directory being sure to keep a .php
extension on the end of the file. Enable the system variable site.include.events
(set to 1) to enable inclusion.

To use - first create two alert actions in FreeNATS, one of type message queue
with the name "_detailtrigger" and one of whichever type (email or URL) you
require for delivery with the name "_detailaction" (note you can configure these
names below and also use pre-existing queues of the right type.

Now simply point the nodes you wish to gain detail from to use the _detailtrigger
alert action. Detail trigger will pad the message with the additional data
required and then deliver it via the _detailaction alert action.

*/




global $NATS;
if (isset($NATS))
{


function detail_alert_handler($data)
{
	global $NATS;

	// CONFIGURATION
	$detail_alert_config=array(
		"trigger"		=>	"_detailtrigger",
		"alertaction"	=>	"_detailaction",
		
		"status_on_open"  =>	true, // on an alert open include node status
		"status_on_close" =>	true, // same for close
		
		"summary_on_open"	=>	true, // on open show summary of other alerts
		"summary_on_close"	=>	true 	); // same for close
	// END OF CONFIGURATION
	
	
	if ($data['event']=="alert_action")
		{
		if ($data['name']==$detail_alert_config["trigger"]) return true; // clear
		else return false; // propgate
		}
	else if ($data['event']=="alert_open")
		{
		$open=true;
		$close=false;
		}
	else if($data['event']=="alert_close") 
		{
		$open=false;
		$close=true;
		}
	else return false;
	
	// Does this node have the alert action of "trigger"
	
	// First what is the trigger aaid
	$q="SELECT aaid FROM fnalertaction WHERE aname=\"".ss($detail_alert_config["trigger"])."\" LIMIT 0,1";
	//echo $q."\n";
	$r=$NATS->DB->Query($q);
	if (!$aa=$NATS->DB->Fetch_Array($r))
		{
		$NATS->Event("No trigger action ".$detail_alert_config["trigger"],10,"Extras","Detail Alert");
		return false; // no such trigger alert action
		}
	$NATS->DB->Free($r);
	
	$aaid=$aa['aaid'];
	
	// Second does the node have this alert action
	$q="SELECT nalid FROM fnnalink WHERE aaid=".$aaid." AND nodeid=\"".ss($data['nodeid'])."\" LIMIT 0,1";
	//echo $q."\n";
	$r=$NATS->DB->Query($q);
	if (!$link=$NATS->DB->Fetch_Array($r))
		{
		$NATS->Event("Node does not have trigger action",10,"Extras","Detail Alert");
		return false; // no it does not
		}
	$NATS->DB->Free($r);
	
	
	$msg=$data['nodeid']." : Alert ";
	if ($open) $msg.="Opened";
	else $msg.="Closed";
	$msg.="\n\n";
	
	if ( ( $open && $detail_alert_config['status_on_open'] ) ||
		( $close && $detail_alert_config['status_on_close'] ) )
		{ // include node status
		
		$tests=$NATS->GetNodeTests($data['nodeid']);
		foreach($tests as $testid)
			{
			$test=$NATS->GetTest($testid);
			if ($test!==false) // specific comparitor
				{
				$msg.=$test['name'].": ".$test['alerttext']." (".$test['lastrunago']." ago)\n";
				}
			}
		
		$msg.="\n\n";
		}
		
	if ( ( $open && $detail_alert_config['summary_on_open'] ) ||
		( $close && $detail_alert_config['summary_on_close'] ) )
		{ // include alert summary
		
		$msg.="Current Alerts: ";
		$alerts=$NATS->GetAlerts();
		if ( ($alerts===false) || (count($alerts)<=0) ) $msg.="No Alerts";
		else
			{
			$first=true;
			foreach($alerts as $alert)
				{
				if ($first) $first=false;
				else $msg.=", ";
				$msg.=$alert['nodeid'];
				}
			}
		
		$msg.="\n\n";
		}
	
	// ok got this far so find output aaid and pass data
	$q="SELECT aaid FROM fnalertaction WHERE aname=\"".ss($detail_alert_config["alertaction"])."\" LIMIT 0,1";
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r))
		{
		$NATS->DB->Free($r);
		$NATS->ActionAddData($row['aaid'],$msg);
		}
	else 
		{
		$NATS->Event("Unable to find action ".$detail_alert_config["alertaction"],10,"Extras","Detail Alert");
		return false; // failed to find
		}
	
}

$NATS->AddEventHandler("alert_open","detail_alert_handler");
$NATS->AddEventHandler("alert_close","detail_alert_handler");
$NATS->AddEventHandler("alert_action","detail_alert_handler"); // to clear the trigger


} // end of NATS block

?>