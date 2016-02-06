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
if ($NATS_Session->userlevel<5) UL_Error("Action Local Test");

switch ($_REQUEST['action'])
	{
	case "create":
		if (isset($_REQUEST['testcreatedisabled'])) $tenable=0;
		else $tenable=1;
		$q="INSERT INTO fnlocaltest(nodeid,testtype,testparam,testenabled) VALUES(";
		$q.="\"".ss($_REQUEST['nodeid'])."\",\"".ss($_REQUEST['testtype'])."\",\"".ss($_REQUEST['testparam'])."\",".$tenable.")";
		//echo $q;
		//exit();
		$NATS->DB->Query($q);
		//echo $q;
		//exit();
		// Following user feedback (added to 0.02.35a)
		//$loc="localtest.edit.php?localtestid=".$NATS->DB->Insert_Id();

/*		-- the old code just to appear in the list, now opens like a node create for the options
		$loc="node.edit.php?nodeid=".$_REQUEST['nodeid']; // no message - just appears in list like evals
*/
		$loc="localtest.edit.php?localtestid=".$NATS->DB->Insert_Id();
		header("Location: ".$loc);
		exit();

		break;
	case "save_form":
		if (isset($_REQUEST['testrecord'])) $tr=$_REQUEST['testrecord'];
		else $tr=0;
		if (isset($_REQUEST['simpleeval'])) $se=$_REQUEST['simpleeval'];
		else $se=0;
		if (isset($_REQUEST['testenabled'])) $te=1;
		else $te=0;
		if (isset($_REQUEST['clearparam'])) $_REQUEST['testparam']="";
		if (isset($_REQUEST['testinterval'])) $interval=$_REQUEST['testinterval'];
		else $interval=0;
		if (!is_numeric($interval)) $interval=0;
		$q="UPDATE fnlocaltest SET testparam=\"".ss($_REQUEST['testparam'])."\",testrecord=".ss($tr).",simpleeval=".ss($se).",testenabled=".$te.",";
		$q.="testname=\"".ss($_REQUEST['testname'])."\",attempts=".ss($_REQUEST['attempts']).",timeout=".ss($_REQUEST['timeout']);
		$q.=",testinterval=".ss($interval);
		
		// get and update parameters if available
		for ($a=1; $a<10; $a++) // 1 to 9 - "0" taken care of
			{
			$paramstr="testparam".$a;
			if (isset($_REQUEST[$paramstr])) $val=ss($_REQUEST[$paramstr]);
			else $val="";
			$clearstr="clearparam".$a;
			$keepstr="keepparam".$a;
			if ( (isset($_REQUEST[$clearstr]))&&($_REQUEST[$clearstr]==1) )
				{ // wants cleared so do for sure
				$val="";
				$q.=",".$paramstr."=\"".$val."\"";
				}
			else if (isset($_REQUEST[$keepstr]))
				{
				// keep if new input is blank
				if ($val!="") $q.=",".$paramstr."=\"".$val."\"";	
				}
			else 
				{ // otherwise update unless cleared
				$q.=",".$paramstr."=\"".$val."\"";
				}
			}
		
		$q.=" WHERE localtestid=".ss($_REQUEST['localtestid']);
		//echo $q;
		//exit();
		$NATS->DB->Query($q);
		if ($NATS->DB->Affected_Rows()<=0) $msg="Save+Failed+or+Nothing+Changed";
		else $msg="Changes+Saved";
		
		// Handle Invalidation
		if ( isset($_REQUEST['testinterval']) && isset($_REQUEST['original_testinterval']) &&
			($_REQUEST['testinterval'] != $_REQUEST['original_testinterval']) )
				$NATS->InvalidateTest("L".$_REQUEST['localtestid']);
		
		header("Location: localtest.edit.php?localtestid=".$_REQUEST['localtestid']."&message=".$msg);
		exit();
	case "invalidate":
		$tid="L".$_REQUEST['localtestid'];
		$NATS->InvalidateTest($tid,true);
		$msg="Test+Scheduled+for+ASAP";
		header("Location: localtest.edit.php?localtestid=".$_REQUEST['localtestid']."&message=".$msg);
		exit();
	case "delete":
		if (!isset($_REQUEST['confirmed']))
			{
			$back="localtest.action.php?action=delete&localtestid=".$_REQUEST['localtestid']."&confirmed=1";
			$back=urlencode($back);
			$url="confirm.php?action=Delete+test+and+all+historical+data&back=".$back;
			header("Location: ".$url);
			exit();
			}
		// history
		$q="DELETE FROM fnrecord WHERE testid=\"L".ss($_REQUEST['localtestid'])."\"";
		$NATS->DB->Query($q);
		$hdel=$NATS->DB->Affected_Rows();
		// test itself
		$q="DELETE FROM fnlocaltest WHERE localtestid=".ss($_REQUEST['localtestid']);
		$NATS->DB->Query($q);
		$m="Deleted ".$NATS->DB->Affected_Rows()." test and ".$hdel." history items";
		header("Location: main.php?message=".urlencode($m));
		exit();
		
	default:
		header("Location: main.php?message=Unknown+Local+Test+Action");
		exit();
	}

?>
