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
along with Foobar.  If not, see www.gnu.org/licenses

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
if ($NATS_Session->userlevel<1) UL_Error("View Alert");
ob_end_flush();
Screen_Header("Alert History for ".$_REQUEST['nodeid'],1);
?>
<br>
<?php
echo "<b class=\"minortitle\">Node Alerts for: <a href=node.php?nodeid=".$_REQUEST['nodeid'].">".$_REQUEST['nodeid']."</a></b><br><br>";

function dal($arow)
{
global $NATS;
echo "<table border=0>";
echo "<tr><td align=right><b>Alert : </b></td>";
echo "<td align=left><b>".$arow['nodeid']."/".$arow['alertid']."</b></td></tr>";
echo "<tr><td align=right>Opened : </td>";
echo "<td align=left>".nicedt($arow['openedx'])."</td></tr>";

if ($arow['closedx']>0)
	{
	$st="<b class=\"al0\">Resolved</b>";
	$ct=nicedt($arow['closedx'])." (Open for ".nicediff($arow['closedx']-$arow['openedx']).")";
	}
else
	{
	$st="<b>Open</b>";
	$ct="n/a";
	}

echo "<tr><td align=right>Status : </td>";
echo "<td align=left>".$st."</td></tr>";
echo "<tr><td align=right>Closed : </td>";
echo "<td align=left>".$ct."</td></tr>";

echo "<tr><td align=right>Level : </td>";
echo "<td align=left><b class=\"al".$arow['alertlevel']."\">".aText($arow['alertlevel'])."</td></tr>";

echo "<tr><td colspan=2>&nbsp;<br><b><u>Alert Log</u></b></td></tr>";

$hq="SELECT * FROM fnalertlog WHERE alertid=".$arow['alertid']." ORDER BY postedx DESC";
$px=0;
$first=true;
$hr=$NATS->DB->Query($hq);
while ($hrow=$NATS->DB->Fetch_Array($hr))
	{
	if ($hrow['postedx']!=$px) // first entry for that px
		{
		if (!$first) echo "</td></tr>"; // first ever px or not
		else $first=false;
		echo "<tr><td align=right valign=top>";
		$px=$hrow['postedx'];
		echo nicedt($px);
		echo " : ";
		echo "</td><td align=left valign=top>";
		}
	echo $hrow['logentry']."<br>";
	}
if (!$first) echo "</td></tr>";
		
		
echo "</table>";
}

if (isset($_REQUEST['alertid']))
	{ // display this one
	$q="SELECT * FROM fnalert WHERE alertid=".ss($_REQUEST['alertid']);
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r)) dal($row);
	else echo "<b>Error Fetching AlertID</b><br><br>";
	}
else
	{ // see if one is open
	$q="SELECT * FROM fnalert WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" AND closedx=0";
	$r=$NATS->DB->Query($q);
	if ($row=$NATS->DB->Fetch_Array($r)) dal($row);
	// otherwise nothing open...
	}
	
echo "<br><br>";
echo "<b class=\"minortitle\">Alert History for ".$_REQUEST['nodeid']."</b><br><br>";
// display history

$hq="SELECT * FROM fnalert WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" ORDER BY alertid DESC";
$hr=$NATS->DB->Query($hq);
//echo $hq;

echo "<table border=0>";
while ($hrow=$NATS->DB->Fetch_Array($hr))
	{
	echo "<tr><td><a href=history.alert.php?alertid=".$hrow['alertid']."&nodeid=".$_REQUEST['nodeid'].">";
	echo $_REQUEST['nodeid']."/".$hrow['alertid'];
	echo "</td></td>";
	echo "<td>";
	if ($hrow['closedx']<=0) echo "<b>Open</b>";
	else echo "Resolved";
	echo "</td>";
	echo "<td>";
	echo nicedt($hrow['openedx'])." - ";
	if ($hrow['closedx']<=0) echo "n/a";
	else echo nicedt($hrow['closedx']);
	echo "</td>";
	
	echo "</tr>";
	}
echo "</table>";

?>


<?php
Screen_Footer();
?>
