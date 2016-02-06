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
if ($NATS_Session->userlevel<1) UL_Error("View Test Summary");

if (isset($_REQUEST['nodeid'])) $nodeid=$_REQUEST['nodeid'];
else $nodeid="";


Screen_Header("Summary for ".$nodeid,1);
ob_end_flush();

$td_day=date("d");
$td_mon=date("m");
$td_yr=date("Y");

function gtinfo($testid)
{
global $NATS;
$o="Error fetching test";

$test=$NATS->GetTest($testid);
if ($test['class']=="L")
	{
	$o=lText($test['testtype']);
	if ($test['testparam']!="") $o.=" (".$test['testparam'].")";
	
	if ($test['testname']!="") $o=$test['testname'];
	
	$u=lUnit($test['testtype']);
	if ($u!="") $o.=" (".$u.")";	
	}
else if ($test['name']!="") $o=$test['name'];
else $o=$test['testtype'];
/*
if ($testid[0]=="L")
	{ // local test
	$q="SELECT testtype,testparam,testname FROM fnlocaltest WHERE localtestid=".ss(substr($testid,1,128));
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r))
		{
		$o=lText($row['testtype']);
		if ($row['testparam']!="") $o.=" (".$row['testparam'].")";
		
		if ($row['testname']!="") $o=$row['testname'];
		
		$u=lUnit($row['testtype']);
		if ($u!="") $o.=" (".$u.")";
		}
	else $o="Error fetching test";
	}
*/
return $o;
}

function outTime($timex,$name,$start=true,$checked=false)
{
echo "<input type=radio name=";
if ($start) echo "startx";
else echo "finishx";
if ($checked) $c=" checked";
else $c="";
echo " value=".$timex.$c."> ".$name."<br>";
}

if (isset($_REQUEST['mode'])&&($_REQUEST['mode']=="custom"))
	{
	echo "<br><b class=\"subtitle\">Custom Summary</b><br><br>";
	echo "<table width=600 border=0>";
	echo "<form action=summary.test.php method=post>";
	echo "<input type=hidden name=nodeid value=\" custom\">"; // note the space - invalid normal nodeid
	echo "<tr><td align=left valign=top>";
	echo "<b>Nodes</b><br><br>";
	$q="SELECT nodeid,nodename FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<input type=checkbox name=nodelist[] value=\"".$row['nodeid']."\"> ";
		if ($row['nodename']=="") echo $row['nodeid'];
		else echo $row['nodename'];
		echo "<br>";
		}
	$NATS->DB->Free($r);
	echo "</td><td align=left valign=top>";
	echo "<b>Start Time</b><br><br>";
	outTime(mktime(0,0,0,$td_mon,$td_day,$td_yr),"0:00 Today",true,true);
	outTime(time()-(60*60*24),"24 Hours Ago");
	outTime(time()-(60*60),"1 Hour Ago");
	
	echo "<br><b>Finish Time</b><br><br>";
	outTime(mktime(23,59,59,$td_mon,$td_day,$td_yr),"23:59:59 Today",false,true);
	outTime(time(),"Now",false);
	
	echo "<br><input type=submit value=\"View Summary\">";
	echo "</td></tr>";
	echo "</form></table>";
	}

if (isset($_REQUEST['startx'])) $startx=$_REQUEST['startx'];
else $startx=mktime(0,0,0,$td_mon,$td_day,$td_yr);
if (isset($_REQUEST['finishx'])) $finishx=$_REQUEST['finishx'];
else $finishx=mktime(23,59,59,$td_mon,$td_day,$td_yr);

echo "<br><b>From </b>".nicedt($startx)." <b>to</b> ".nicedt($finishx)."<br><br>";

if ($nodeid=="*")
	{
	//
	$q="SELECT testid,nodeid FROM fnrecord WHERE recordx>=".ss($startx)." AND recordx<=".ss($finishx);
	$q.=" GROUP BY testid ORDER BY nodeid";
	}
else if ($nodeid==" custom") // use nodelist
	{
	$q="SELECT testid,nodeid FROM fnrecord WHERE recordx>=".ss($startx)." AND recordx<=".ss($finishx)." ";
	$q.="AND nodeid IN ( ";
	$first=true;
	foreach($_REQUEST['nodelist'] as $node)
		{
		if ($first) $first=false;
		else $q.=",";
		$q.="\"".ss($node)."\"";
		}
	$q.=" ) GROUP BY testid ORDER BY nodeid";
	//echo $q;
	//exit();
	}
else
	{
	$q="SELECT testid,nodeid FROM fnrecord WHERE nodeid=\"".ss($nodeid)."\" AND recordx>=".ss($startx)." AND recordx<=".ss($finishx);
	$q.=" GROUP BY testid";
	}
$r=$NATS->DB->Query($q);


$name="";
$first=true;

while ($row=$NATS->DB->Fetch_Array($r))
	{
	if ($name!=$row['nodeid'])
		{
		if ($first) $first=false;
		else echo "<br><br>";
		echo "<b class=\"subtitle\">Node: <a href=node.php?nodeid=".$row['nodeid'].">".$row['nodeid']."</a></b><br><br>";
		$name=$row['nodeid'];
		}
	echo "<b>".gtinfo($row['testid'])." on ".$row['nodeid']."</b><br>";
	//echo "img src=\"test.graph.php?testid=".$row['testid']."&startx=".$startx."&finishx=".$finishx."\"<br>";
	echo "<a href=history.test.php?nodeid=".$row['nodeid']."&testid=".$row['testid']."&startx=".$startx."&finishx=".$finishx.">";
	echo "<img src=\"test.graph.php?nodeid=".$row['nodeid']."&testid=".$row['testid']."&startx=".$startx."&finishx=".$finishx."\" border=0>";
	echo "</a>";
	echo "<br><br>&nbsp;<br>";
	}
	
$NATS->DB->Free($r);

Screen_Footer();
?>
