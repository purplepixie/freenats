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
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<9) UL_Error($NATS->Lang->Item("test.run"));



ob_end_flush();
Screen_Header($NATS->Lang->Item("test.run").": test/".$_REQUEST['trid'],1);

if (isset($_REQUEST['message'])) echo "<b>".$_REQUEST['message']."</b><br>";
if (isset($amsg)) echo "<b>".$amsg."</b><br>";

if ( (isset($_REQUEST['action'])) && ($_REQUEST['action']=="finish") )
	{
	if (!isset($_REQUEST['confirmed']))
		{
		echo "<b>".$NATS->Lang->Item("testrun.manual.close")."</b><br>";
		echo $NATS->Lang->Item("testrun.manual.close.detail");
		echo "<br><br>";
		echo "<b>".$NATS->Lang->Item("confirm.action").":</b> ";
		echo "<a href=testrun.php?trid=".$_REQUEST['trid']."&action=finish&confirmed=1>".$NATS->Lang->Item("testrun.del.yes")."</a> | <a href=main.php>".$NATS->Lang->Item("testrun.del.cancel")."</a>";
		echo "<br><br>";
		}
	else
		{
		$q="UPDATE fntestrun SET finishx=".time()." WHERE trid=".ss($_REQUEST['trid']);
		$NATS->DB->Query($q);
		echo "<b>".$NATS->Lang->Item("testrun.session.closed")."</b><br><Br>";
		}
	}

echo "<br><b class=\"minortitle\">".$NATS->Lang->Item("test.run")." test/".$_REQUEST['trid']."</b><br><br>";

$q="SELECT * FROM fntestrun WHERE trid=".ss($_REQUEST['trid'])." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo "<b>".$NATS->Lang->Item("testrun.fetch.error")."</b><br><br>";
	Screen_Footer();
	exit();
	}
$NATS->DB->Free($r);

echo "<table border=0>";
echo "<tr><td>".$NATS->Lang->Item("started")." : </td>";
echo "<td>".nicedt($row['startx'])." (".dtago($row['startx']).")</td></tr>";
echo "<tr><td>".$NATS->Lang->Item("finished")." : </td>";
echo "<td>";
if ($row['finishx']>0) echo nicedt($row['finishx'])." (".dtago($row['finishx']).")";
else echo $NATS->Lang->Item("sessions.stillrunning")." (<a href=testrun.php?trid=".$_REQUEST['trid']."&action=finish>".$NATS->Lang->Item("testrun.manual.close")."</a>)";
echo "</td>";
echo "<tr><td>".$NATS->Lang->Item("node.filter")." :</td>";
echo "<td>";
if ($row['fnode']=="") echo $NATS->Lang->Item("allnodes");
else echo "<a href=node.php?nodeid=".$row['fnode'].">".$row['fnode']."</a>";
echo "</td></tr>";

echo "<tr><td>".$NATS->Lang->Item("event.log")." :</td>";
echo "<td><a href=log.php?f_entry=Tester+".$row['trid'].">Log Events for Tester ".$row['trid']."</a>";
echo "</td></tr>";

echo "<tr><td align=left valign=top>Output : </td>";
echo "<td align=left valign=top>";
echo $row['routput'];
echo "</td></tr>";

echo "</table>";
?>


<?php
Screen_Footer();
?>
