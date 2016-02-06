<?php // syslog.php version 0.01 17/08/2009
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
event types: alert_action

It also relies on the following FreeNATS class methods:
	TFreeNATS::Event, AddEventHandler

USAGE INSTRUCTIONS:

Place into the server/base/site/events directory being sure to keep a .php
extension on the end of the file. Enable the system variable site.include.events
(set to 1) to enable inclusion.

Create an alert action of the type message queue and with the name "_syslog"
(without the quotes).

Alerts to this alert action will be piped to syslog.

Please note this may well not work when using the "test action" option in
the configuration dependent on your system security setup.

*/




global $NATS;
if (isset($NATS))
{


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




} // end of isset($NATS) block
?>