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
if ($NATS_Session->userlevel<9) UL_Error("Admin Interface");

// Actions Here
if (isset($_REQUEST['action']))
	{
	switch($_REQUEST['action'])
		{ // create delete save_edit
		  // create_item delete_item save_item
		case "create":
		$q="INSERT INTO fnschedule(schedulename) VALUES(\"".ss($_REQUEST['schedulename'])."\")";
		$NATS->DB->Query($q);
		$_REQUEST['scheduleid']=$NATS->DB->Insert_Id();
		break;
		
		case "delete":
		if (!isset($_REQUEST['confirmed']))
			{
			$back="schedule.php?delscheduleid=".$_REQUEST['delscheduleid']."&action=delete&confirmed=1";
			$msg="Delete Schedule and All Associated Items";
			$u="confirm.php?action=".urlencode($msg)."&back=".urlencode($back);
			header("Location: ".$u);
			exit();
			}
		$q="DELETE FROM fnscheditem WHERE scheduleid=".ss($_REQUEST['delscheduleid']);
		$NATS->DB->Query($q);
		$q="UPDATE fnnode SET scheduleid=0 WHERE scheduleid=".ss($_REQUEST['delscheduleid']);
		$NATS->DB->Query($q);
		$q="DELETE FROM fnschedule WHERE scheduleid=".ss($_REQUEST['delscheduleid']);
		$NATS->DB->Query($q);
		break;
		
		case "save_edit":
		$q="UPDATE fnschedule SET schedulename=\"".ss($_REQUEST['schedulename'])."\",defaultaction=".ss($_REQUEST['defaultaction'])." ";
		$q.="WHERE scheduleid=".ss($_REQUEST['scheduleid']);
		$NATS->DB->Query($q);
		break;
		
		case "create_item":
		if ($_REQUEST['year']=="") $year=0;
		else $year=ss($_REQUEST['year']);
		$q="INSERT INTO fnscheditem(scheduleid,dayofweek,dayofmonth,monthofyear,year,starthour,startmin,finishhour,finishmin) VALUES(";
		$q.=ss($_REQUEST['scheduleid']).",\"".ss($_REQUEST['dayofweek'])."\",".ss($_REQUEST['dayofmonth']).",".ss($_REQUEST['monthofyear']).",".$year.",";
		$q.=ss($_REQUEST['starthour']).",".ss($_REQUEST['startmin']).",".ss($_REQUEST['finishhour']).",".ss($_REQUEST['finishmin']).")";
		$NATS->DB->Query($q);
		break;
		//echo $q;
		
		case "save_item":
		if ($_REQUEST['year']=="") $year=0;
		else $year=ss($_REQUEST['year']);
		$q="UPDATE fnscheditem SET ";
		//(scheduleid,dayofweek,dayofmonth,monthofyear,year,starthour,startmin,finishhour,finishmin) VALUES(";
		$q.="dayofweek=\"".ss($_REQUEST['dayofweek'])."\",";
		$q.="dayofmonth=".ss($_REQUEST['dayofmonth']).",monthofyear=".ss($_REQUEST['monthofyear']).",year=".$year.",";
		$q.="starthour=".ss($_REQUEST['starthour']).",startmin=".ss($_REQUEST['startmin']).",";
		$q.="finishhour=".ss($_REQUEST['finishhour']).",finishmin=".ss($_REQUEST['finishmin'])." WHERE scheditemid=".ss($_REQUEST['scheditemid']);
		$NATS->DB->Query($q);
		break;
		
		case "delete_item";
		$q="DELETE FROM fnscheditem WHERE scheditemid=".ss($_REQUEST['scheditemid']);
		$NATS->DB->Query($q);
		break;
		}
		
	}


Screen_Header("Schedule Management",1,1,"","main","admin");

echo "<br><b class=\"subtitle\"><a href=admin.php>System Settings</a> &gt; Schedule Manager</b><br><br>";
$q="SELECT scheduleid,schedulename FROM fnschedule";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)<=0) echo "<i>No Schedules</i><br>";
else
	{
	echo "<table class=\"nicetable\">";
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr><td><b><a href=schedule.php?scheduleid=".$row['scheduleid'].">".$row['schedulename']."</a></b>&nbsp;&nbsp;</td>";
		echo "<td><a href=schedule.php?scheduleid=".$row['scheduleid'].">Edit</a> | ";
		echo "<a href=schedule.php?delscheduleid=".$row['scheduleid']."&action=delete>Delete</a></td></tr>";
		}
	echo "</table>";
	}
$NATS->DB->Free($r);
echo "<form action=schedule.php method=post><input type=hidden name=action value=create>";
echo "<br><b>Create: </b><input type=text name=schedulename size=30 maxlength=64 value=\"Test Schedule\"> ";
echo "<input type=submit value=\"Create\"></form><br>";

$days_of_week=array( '', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' );

function tt($a) // "to ten"
{
if ($a<10) return "0".$a;
else return $a;
}

// view a specific schedule
if (isset($_REQUEST['scheduleid']))
	{
	$q="SELECT * FROM fnschedule WHERE scheduleid=".ss($_REQUEST['scheduleid'])." LIMIT 0,1";
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<b class=\"subtitle\">Editing Schedule: ".$row['schedulename']."</b><br><br>";
		echo "<table class=\"nicetable\">";
		echo "<form action=schedule.php method=post><input type=hidden name=action value=save_edit>";
		echo "<input type=hidden name=scheduleid value=".$_REQUEST['scheduleid'].">";
		echo "<tr><td><b>Schedule Name :</b></td>";
		echo "<td><input type=text name=schedulename size=30 maxlength=64 value=\"".$row['schedulename']."\">";
		echo "</td></tr>";
		$o_on="<option value=1>Run Test/Alert (Enabled)</option>";
		$o_off="<option value=0> Don't Run Test/Alert (Disabled)</option>";
		echo "<tr><td><b>Default Action :</b></td>";
		echo "<td><select name=defaultaction>";
		if ($row['defaultaction']==0) echo $o_off.$o_on;
		else echo $o_on.$o_off;
		echo "</select> ".hlink("Schedule:DefaultAction")."</td></tr>";
		echo "<tr><td>&nbsp;</td><td><input type=submit value=\"Save Settings\"></td></tr>";
		echo "</form></table><br>";
		
		echo "<b class=\"subtitle\">Exceptions</b><br><br>";
		echo "Exceptions are the times when your schedule <b>will not</b> perform its default action i.e. not test the node or send the alert.<br>";
		echo "See the <a href=http://www.purplepixie.org/freenats/support.php>documentation</a> for more information.<br><br>";
		
		echo "<table class=\"nicetable\">";
		echo "<tr><td><b>Weekday</b></td><td><b>Day</b></td><td><b>Month</b></td><td><b>Year</b>&nbsp;&nbsp;</td><td><b>From (HH:MM) =&gt;</b></td>";
		echo "<td><b>To HH:MM</td></td><td>&nbsp;</td></tr>";

		$q="SELECT * FROM fnscheditem WHERE scheduleid=".ss($_REQUEST['scheduleid']);
		$r=$NATS->DB->Query($q);
		while ($row=$NATS->DB->Fetch_Array($r))
			{
			echo "<tr><td colspan=8>&nbsp;</td></tr>";
		
			echo "<form action=schedule.php method=post>";
			echo "<input type=hidden name=scheduleid value=".$_REQUEST['scheduleid'].">";
			echo "<input type=hidden name=scheditemid value=".$row['scheditemid'].">";
			echo "<input type=hidden name=action value=save_item>";
			
			echo "<tr><td>";
			echo "<select name=dayofweek>";
			//echo "<option value=\"\">*</option>";
			echo "<option value=\"".$row['dayofweek']."\">".$row['dayofweek']."</option>";
			foreach($days_of_week as $day)
				echo "<option value=\"".$day."\">".$day."</option>";
			echo "</select></td>";
			echo "<td><select name=dayofmonth>";
			echo "<option value=\"".$row['dayofmonth']."\">".$row['dayofmonth']."</option>";
			echo "<option value=0> </option>";
			for ($a=1; $a<32; $a++) echo "<option value=".$a.">".$a."</option>";
			echo "</select></td>";
			echo "<td><select name=monthofyear>";
			echo "<option value=\"".$row['monthofyear']."\">".$row['monthofyear']."</option>";
			echo "<option value=0> </option>";
			for ($a=1; $a<13; $a++) echo "<option value=".$a.">".$a."</option>";
			echo "</select></td>";
			if ($row['year']==0) $year="";
			else $year=$row['year'];
			echo "<td><input type=text name=year size=4 maxlength=4 value=\"".$year."\"></td>";
		
			echo "<td><select name=starthour>";
			echo "<option value=\"".$row['starthour']."\">".tt($row['starthour'])."</option>";
			for ($a=0; $a<24; $a++) echo "<option value=".$a.">".tt($a)."</option>";
			echo "</select>:<select name=startmin>";
			echo "<option value=\"".$row['startmin']."\">".tt($row['startmin'])."</option>";
			for ($a=0; $a<60; $a++) echo "<option value=".$a.">".tt($a)."</option>";
			echo "</select></td>";
			
			echo "<td><select name=finishhour>";
			echo "<option value=\"".$row['finishhour']."\">".tt($row['finishhour'])."</option>";;
			for ($a=0; $a<24; $a++) echo "<option value=".$a.">".tt($a)."</option>";
			echo "</select>:<select name=finishmin>";
			echo "<option value=\"".$row['finishmin']."\">".tt($row['finishmin'])."</option>";
			for ($a=0; $a<60; $a++) echo "<option value=".$a.">".tt($a)."</option>";
			echo "</select></td>";
			echo "<td><input type=submit value=\"Save\"> ";
			echo "<a href=schedule.php?scheduleid=".$_REQUEST['scheduleid']."&action=delete_item&scheditemid=".$row['scheditemid'].">";
			echo "Delete</a>";
			echo "</td>";
			echo "</form>";
			echo "</tr>";
			}
		
		echo "<tr><td colspan=8>&nbsp;</td></tr>";
		
		echo "<form action=schedule.php method=post>";
		echo "<input type=hidden name=scheduleid value=".$_REQUEST['scheduleid'].">";
		echo "<input type=hidden name=action value=create_item>";
		
		echo "<tr><td>";
		echo "<select name=dayofweek>";
		//echo "<option value=\"\">*</option>";
		foreach($days_of_week as $day)
			echo "<option value=\"".$day."\">".$day."</option>";
		echo "</select></td>";
		echo "<td><select name=dayofmonth>";
		echo "<option value=0> </option>";
		for ($a=1; $a<32; $a++) echo "<option value=".$a.">".$a."</option>";
		echo "</select></td>";
		echo "<td><select name=monthofyear>";
		echo "<option value=0> </option>";
		for ($a=1; $a<13; $a++) echo "<option value=".$a.">".$a."</option>";
		echo "</select></td>";
		echo "<td><input type=text name=year size=4 maxlength=4></td>";
	
		echo "<td><select name=starthour>";
		for ($a=0; $a<24; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select>:<select name=startmin>";
		for ($a=0; $a<60; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select></td>";
		
		echo "<td><select name=finishhour>";
		for ($a=0; $a<24; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select>:<select name=finishmin>";
		for ($a=0; $a<60; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select></td>";
		echo "<td><input type=submit value=\"Create\"></td>";
		echo "</form>";
		echo "</tr></table>";
		
		echo "<br><br>";
		echo "<a name=testdate></a><b class=\"subtitle\">Test a Date and Time</b><br><br>";
		echo "<form action=schedule.php#testdate method=post>";
		echo "<input type=hidden name=scheduleid value=".$_REQUEST['scheduleid'].">";
		echo "<input type=hidden name=test_schedule value=1>";
		echo "<b>Test: </b>";
		
		// persist tested date/time
		if (isset($_REQUEST['test_schedule']))
			{
			$day=$_REQUEST['testday'];
			$month=$_REQUEST['testmonth'];
			$year=$_REQUEST['testyear'];
			$hour=$_REQUEST['testhour'];
			$min=$_REQUEST['testmin'];
			}
		else
			{
			$day=date("d");
			$month=date("m");
			$year=date("Y");
			$hour=date("H");
			$min=date("i");
			}
		
		echo "<select name=testday>";
		echo "<option value=".$day.">".$day."</option>";
		for ($a=1; $a<32; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select><select name=testmonth>";
		echo "<option value=".$month.">".$month."</option>";
		for ($a=1; $a<13; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select>";
		echo "<input type=text name=testyear size=4 value=\"".$year."\">";
		echo "&nbsp;&nbsp;";
		echo "<select name=testhour>";
		echo "<option value=".$hour.">".$hour."</option>";
		for ($a=0; $a<24; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select>:<select name=testmin>";
		echo "<option value=".$min.">".$min."</option>";
		for ($a=0; $a<60; $a++) echo "<option value=".$a.">".tt($a)."</option>";
		echo "</select>";
		echo " <input type=submit value=\"Test\">";
		echo "</form><br>";
		
		// mktime(hr mi se mo do yr
		if (isset($_REQUEST['test_schedule']))
			{
			$tx=mktime($_REQUEST['testhour'],$_REQUEST['testmin'],0,$_REQUEST['testmonth'],$_REQUEST['testday'],$_REQUEST['testyear']);
			echo "Testing ".date("Y-m-d H:i",$tx)."... ";
			$wouldrun=run_x_in_schedule($tx,$_REQUEST['scheduleid']);
			echo "<b>";
			if ($wouldrun) echo "Yes - TESTS WOULD RUN";
			else echo "No - TESTS WOULD NOT RUN";
			echo "</b><br>";
			}

		}
	else echo "Error fetching schedule information";
	}

Screen_Footer();
?>
