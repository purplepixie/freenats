<?php // file.php version 0.01 17/08/2009
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

Create an alert action of type message queue with the name of "_fileoutput"
(without the quotes).

Alerts sent to this action will be written to a file (/tmp/fndebug).

*/




global $NATS;
if (isset($NATS))
{


function alert_action_file($data)
{
	if ($data['name']=="_fileoutput") $file="/tmp/fndebug";
	else return false;
	$fp=fopen($file,"a");
	fputs($fp,"-- ".date("Y-m-d H:i:s")." --\n");
	fputs($fp,$data['data']);
	fputs($fp,"\n-- ENDS --\n");
	fclose($fp);
	return true;
}

$NATS->AddEventHandler("alert_action","alert_action_file");




} // end of isset($NATS) block
?>