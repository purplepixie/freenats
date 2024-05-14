<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2010 PurplePixie Systems

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

ob_start();
require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB)) {
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
}
if ($NATS_Session->userlevel < 5) UL_Error("Action Group");
$msg = "";

switch ($_REQUEST['action']) {
	case "delete":
		if (!isset($_REQUEST['confirm'])) {
			$go = "confirm.php?action=Delete+Group&back=";
			$go .= urlencode("group.action.php?action=delete&groupid=" . $_REQUEST['groupid'] . "&confirm=1");
			header("Location: " . $go);
			exit();
		}
		// delete it and shit!

		// group + links + lt results
		$q = "DELETE FROM fngroup WHERE groupid=" . ss($_REQUEST['groupid']);
		$NATS->DB->Query($q);
		$q = "DELETE FROM fngrouplink WHERE groupid=" . ss($_REQUEST['groupid']);
		$NATS->DB->Query($q);
		$msg = "Group Deleted";
		break;

	case "create":
		// get highest weight
		$wq = "SELECT weight FROM fngroup ORDER BY weight DESC LIMIT 0,1";
		$wr = $NATS->DB->Query($wq);
		if ($wrow = $NATS->DB->Fetch_Array($wr)) $we = ($wrow['weight']) + 10;
		else $we = 10;

		$q = "INSERT INTO fngroup(groupname,weight) VALUES(\"" . ss($_REQUEST['groupname']) . "\"," . $we . ")";
		if ($_REQUEST['groupname'] != "") {
			$NATS->DB->Query($q);
			$msg = "Created New Group";
		} else $msg = "Invalid Group Name";
		break;

	case "save_edit":
		$q = "UPDATE fngroup SET ";
		$q .= "groupname=\"" . ss($_REQUEST['groupname']) . "\",";
		$q .= "groupdesc=\"" . ss($_REQUEST['groupdesc']) . "\",";
		$q .= "groupicon=\"" . ss($_REQUEST['groupicon']) . "\"";
		$q .= " WHERE groupid=" . ss($_REQUEST['groupid']);
		$NATS->DB->Query($q);
		$msg = "Saved Group Changes";
		break;

	case "save_members":

		// da two list nonsense again
		/*
	$nl=array();
	$nc=0;
	$cur=array();
	$cc=0;
	
	foreach($_REQUEST['members'] as $newmem)
		{
		$nl[$newmem]['proc']=false;
		$nl[$newmem]['nodeid']=$newmem;
		$nl++;
		}
	*/ // no let's try this and see if we get any errors and stuff

		$q = "DELETE FROM fngrouplink WHERE groupid=" . ss($_REQUEST['groupid']);
		$NATS->DB->Query($q);
		foreach ($_REQUEST['members'] as $newmem) {
			$q = "INSERT INTO fngrouplink(groupid,nodeid) VALUES(" . ss($_REQUEST['groupid']) . ",\"" . ss($newmem) . "\")";
			$NATS->DB->Query($q);
		}
		$msg = "Updated Group Membership";
		break;

	case "move":
		// get my weight
		$q = "SELECT weight FROM fngroup WHERE groupid=\"" . ss($_REQUEST['groupid']) . "\"";
		$r = $NATS->DB->Query($q);
		$row = $NATS->DB->Fetch_Array($r);
		$myweight = $row['weight'];
		$NATS->DB->Free($r);

		// get next/prev one
		$q = "SELECT groupid,weight FROM fngroup WHERE ";
		if ($_REQUEST['dir'] == "up") $q .= "weight<" . $myweight . " ORDER BY weight DESC LIMIT 0,1";
		else $q .= "weight>" . $myweight . " ORDER BY weight ASC LIMIT 0,1";
		$r = $NATS->DB->Query($q);
		if ($row = $NATS->DB->Fetch_Array($r)) {
			// swap 'em
			$uq = "UPDATE fngroup SET weight=" . $myweight . " WHERE groupid=" . $row['groupid'];
			$NATS->DB->Query($uq);
			$uq = "UPDATE fngroup SET weight=" . $row['weight'] . " WHERE groupid=" . ss($_REQUEST['groupid']);
			$NATS->DB->Query($uq);
			$msg = "Updated Group Display Order";
		} else $msg = "No Higher/Lower Group";
		break;



	default:
		$msg = "Unknown Group Action";
}
header("Location: main.php?mode=groups&message=" . urlencode($msg));
exit();
