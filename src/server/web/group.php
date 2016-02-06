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
if ($NATS_Session->userlevel<1) UL_Error("View Group");
ob_end_flush();
Screen_Header("Viewing Group",1);
?>
<br>
<?php

$q="SELECT * FROM fngroup WHERE groupid=\"".ss($_REQUEST['groupid'])."\" LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo "No such group.<br><br>";
	Screen_Footer();
	exit();
	}
$NATS->DB->Free($r);

echo "<table border=0><tr><td align=left valign=top>";

echo "<b class=\"minortitle\">Group: ".$row['groupname']."</b><br><br>";

echo "<table class=\"nicetable\">";

echo "<tr><td align=right>
Group Name
:</td><td align=left>";
echo $row['groupname'];
echo "</td></tr>";

echo "<tr><td align=right>
Description
:</td><td align=left>";
echo $row['groupdesc'];
echo "</td></tr>";

echo "<tr><td align=right>
Status
:</td><td align=left>";
$al=$NATS->GroupAlertLevel($_REQUEST['groupid']);
echo "<b class=\"al".$al."\">";
echo oText($al);
echo "</b>";
echo "</td></tr>";

if ($NATS_Session->userlevel>4) 
	echo "<tr><td align=right>Settings :</td><td align=left><a href=group.edit.php?groupid=".$_REQUEST['groupid'].">Group Options</a></td></tr>";

echo "</table>";
echo "</td><td style=\"width: 50px;\">&nbsp;</td><td align=left valign=top>";
ng_tiny($_REQUEST['groupid'],$row['groupname']);
echo "</td></tr></table>";

echo "<br><br>";
echo "<table border=0>";
$a=0;
$q="SELECT nodeid FROM fngrouplink WHERE groupid=".ss($_REQUEST['groupid']);
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	if ($a==0) echo "<tr>";
	echo "<td>";
	np_tiny($row['nodeid']);
	echo "</td>";
	$a++;
	if ($a>=5) 
		{
		echo "</tr>";
		$a=0;
		}
	}
if ($a>0) echo "</tr>";
/*
if ($a>0) // otherwise at the first row anyway
	{
	while ($a<5)
		{
		echo "<td>&nbsp;</td>";
		$a++;
		}
	echo "</tr>";
	}
*/
echo "</table>";

?>


<?php
Screen_Footer();
?>
