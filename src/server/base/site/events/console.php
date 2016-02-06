<?php // console.php version 0.01 17/08/2009
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
	TFreeNATS::AddEventHandler

USAGE INSTRUCTIONS:

Place into the server/base/site/events directory being sure to keep a .php
extension on the end of the file. Enable the system variable site.include.events
(set to 1) to enable inclusion.

Create an alert action of type message queue and the name "_console" (without
the quotes). Alerts to this action will be echo'd to the /dev/console device.

Note this will almost certainly never work for the "test" option owing to
security limitations but will work within a test cycle.

*/




global $NATS;
if (isset($NATS))
{

function alert_action_console($data)
{
	if ($data['name']!="_console") return false;
	$cmd="echo \"FreeNATS: ".$data['data']."\" > /dev/console";
	@exec($cmd);
	return true;
}

$NATS->AddEventHandler("alert_action","alert_action_console");





} // end of isset($NATS) block
?>