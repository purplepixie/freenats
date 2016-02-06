<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008 PurplePixie Systems

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
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<5) UL_Error("Action Node");
$msg="";

switch($_REQUEST['action'])
	{
	case "delete":
		if (!isset($_REQUEST['confirm']))
			{
			$go="confirm.php?action=Delete+Node+".$_REQUEST['nodeid']."+and+all+associated+data&back=";
			$go.=urlencode("node.action.php?action=delete&nodeid=".$_REQUEST['nodeid']."&confirm=1");
			header("Location: ".$go);
			exit();
			}
		// delete it and shit!
		
		// node + localtests + lt results
		
		$dnc=0;
		$dnt=0;
		$dnd=0;
		$q="SELECT localtestid FROM fnlocaltest WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$r=$NATS->DB->Query($q);
		while ($row=$NATS->DB->Fetch_Array($r))
			{
			$NATS->DeleteTest("L".$row['localtestid']);
			/*
			$dq="DELETE FROM fnrecord WHERE testid=\"L".$row['localtestid']."\"";
			$NATS->DB->Query($dq);
			$dnd+=$NATS->DB->Affected_Rows();
			$dq="DELETE FROM fnlocaltest WHERE localtestid=".$row['localtestid'];
			$NATS->DB->Query($dq);
			$dnt+=$NATS->DB->Affected_Rows();
			*/
			}
		$NATS->DB->Free($r);
		$q="SELECT nstestid FROM fnnstest WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$r=$NATS->DB->Query($q);
		while ($row=$NATS->DB->Fetch_Array($r))
			{
			$NATS->DeleteTest("N".$row['nstestid']);
			}
		$NATS->DB->Free($r);
		
		// node record
		$dq="DELETE FROM fnnode WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($dq);
		$dnc+=$NATS->DB->Affected_Rows();	
		
		// group links
		$q="DELETE FROM fngrouplink WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($q);
		
		// alerts
		$q="DELETE FROM fnalert WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($q);
		
		// test runs
		$q="DELETE FROM fntestrun WHERE fnode=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($q);
		
		// node to alert action links
		$q="DELETE FROM fnnalink WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($q);
		
		// remove from any other nodes with it as a parents
		$q="UPDATE fnnode SET masterid=\"\" WHERE masterid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($q);
		
		$msg="Node ".$_REQUEST['nodeid']." deleted (".$dnc." node)";
		break;
		
	case "create":
		// get highest weight
		$hw=0;
		$hq="SELECT weight FROM fnnode ORDER BY weight DESC LIMIT 0,1";
		$hr=$NATS->DB->Query($hq);
		if ($hrow=$NATS->DB->Fetch_Array($hr)) $hw=($hrow['weight'])+10;
		else $hw=10;
		$NATS->DB->Free($hr);
		$nodename=$NATS->StripGPC(strtolower($_REQUEST['nodeid']));
		$nodename=str_replace(" ","_",$nodename);
		if (strlen($nodename)>0)
			{
			$q="INSERT INTO fnnode(nodeid,weight) VALUES(\"".ss($nodename)."\",".$hw.")";
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows()>0)
				{
				header("Location: node.edit.php?nodeid=".$nodename."&showoptions=1");
				exit();
				}
			}
		$msg="Failed to Create Node";
		break;
		
	case "move":
	// get my weight
	$q="SELECT weight FROM fnnode WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
	$r=$NATS->DB->Query($q);
	$row=$NATS->DB->Fetch_Array($r);
	$myweight=$row['weight'];
	$NATS->DB->Free($r);
	
	// get next/prev one
	$q="SELECT nodeid,weight FROM fnnode WHERE ";
	if ($_REQUEST['dir']=="up") $q.="weight<".$myweight." ORDER BY weight DESC LIMIT 0,1";
	else $q.="weight>".$myweight." ORDER BY weight ASC LIMIT 0,1";
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r))
		{
		// swap 'em
		$uq="UPDATE fnnode SET weight=".$myweight." WHERE nodeid=\"".$row['nodeid']."\"";
		$NATS->DB->Query($uq);
		$uq="UPDATE fnnode SET weight=".$row['weight']." WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
		$NATS->DB->Query($uq);
		$msg="Updated Node Display Order";
		}
	else $msg="No Higher/Lower Node";
	break;
		
	case "move_before":
	// get nodeid of what to move before the and movebefore weight
	$q="UPDATE fnnode SET weight=weight+1 WHERE weight>=".ss($_REQUEST['move_before']);
	$msg=$q;
	$NATS->DB->Query($q);
	$q="UPDATE fnnode SET weight=".ss($_REQUEST['move_before'])." WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\"";
	$NATS->DB->Query($q);
	//$msg="Moved Node";
	break;
		
	case "reorderweight":
	$q="SELECT nodeid,weight FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	$p=1;
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$uq="UPDATE fnnode SET weight=".$p." WHERE nodeid=\"".$row['nodeid']."\"";
		$NATS->DB->Query($uq);
		$p++;
		}
	$msg="Reorder Completed";
	break;
	
	default: $msg="Unknown Node Action";
	}
header("Location: main.php?mode=nodes&message=".urlencode($msg));
exit();

?>
