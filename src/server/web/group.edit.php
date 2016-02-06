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
if ($NATS_Session->userlevel<5) UL_Error($NATS->Lang->Item("group.editor"));
ob_end_flush();
Screen_Header($NATS->Lang->Item("group.editor"),1,1,"","main","groups");
echo "\n<script type=\"text/javascript\">\n";
echo "var iconDivText='";
ShowIcons();
echo "';\n\n";
echo "function showIcons()\n";
echo "{\n";
echo "document.getElementById('iconDiv').innerHTML='<br><br>'+iconDivText+'<br><br>';\n";
echo "}\n";
echo "</script>\n\n";
?>
<br>
<?php

$q="SELECT * FROM fngroup WHERE groupid=".ss($_REQUEST['groupid'])." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo $NATS->Lang->Item("no.group")."<br><br>";
	Screen_Footer();
	exit();
	}
$NATS->DB->Free($r);
echo "<b class=\"subtitle\">".$NATS->Lang->Item("editing")." <a href=main.php?mode=groups>".$NATS->Lang->Item("editing")."</a>:";
echo " <a href=group.php?groupid=".$_REQUEST['groupid'].">".$row['groupname']."</a></b><br><br>";

$t="<b class=\"sectitle\">".$NATS->Lang->Item("group.settings")."</b>";
Start_Round($t,600);
echo "<table border=0 width=100%>";
echo "<form action=group.action.php method=post>";
echo "<input type=hidden name=action value=save_edit>";
echo "<input type=hidden name=groupid value=".$_REQUEST['groupid'].">";


echo "<tr><td align=right>";
echo $NATS->Lang->Item("group.id");
echo " :</td><td align=left>";
echo $row['groupid'];
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("group.name");
echo " :</td><td align=left>";
echo "<input type=text name=groupname size=20 maxlength=120 value=\"".$row['groupname']."\">";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("description");
echo " :</td><td align=left>";
echo "<input type=text name=groupdesc size=30 maxlength=200 value=\"".$row['groupdesc']."\">";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("group.icon");
echo " :</td><td align=left>";
echo "<select name=groupicon>";
if ($row['groupicon']!="") echo "<option value=\"".$row['groupicon']."\">".$row['groupicon']."</option>";
echo "<option value=\"\">Default</option>";
$iconList=GetIcons();
foreach($iconList as $icon)
	echo "<option value=\"".$icon."\">".$icon."</option>";
//echo "</select> [ <a href=group.edit.php?groupid=".$_REQUEST['groupid']."&show_icons=1>Show Icons</a> ]";
echo "</select> [ <a href=\"javascript:showIcons()\">".$NATS->Lang->Item("show.icons")."</a> ]";
echo "</td></tr>";


echo "<tr><td colspan=2>";
echo "<input type=submit value=\"".$NATS->Lang->Item("group.save")."\"> <a href=main.php>".$NATS->Lang->Item("cancel")."</a>";
echo "<br><br>";
echo "</td></tr>";
echo "</form>";
echo "</table>";
End_Round();

if (isset($_REQUEST['show_icons']))
	{
	echo "<tr><td colspan=2 align=left valign=top><br><br>";
	ShowIcons();
	echo "<br><br></td></tr>";
	}
	
echo "<div id=\"iconDiv\"></div>";

echo "<br>";
$t="<b class=\"sectitle\">".$NATS->Lang->Item("group.members")."</b>";
Start_Round($t,600);

echo "<table border=0 width=100%><form action=group.action.php>";
echo "<input type=hidden name=groupid value=".$_REQUEST['groupid'].">";
echo "<input type=hidden name=action value=save_members>";
$q="SELECT nodeid,nodename FROM fnnode ORDER BY weight ASC";
$r=$NATS->DB->Query($q);
$c=0;
while ($row=$NATS->DB->Fetch_Array($r))
	{
	// has link?
	$lq="SELECT glid FROM fngrouplink WHERE groupid=\"".ss($_REQUEST['groupid'])."\" AND nodeid=\"".ss($row['nodeid'])."\" LIMIT 0,1";
	$lr=$NATS->DB->Query($lq);
	if ($NATS->DB->Num_Rows($lr)>0) $s=" checked";
	else $s="";
	$NATS->DB->Free($lr);
	echo "<tr><td><input type=checkbox name=\"members[".$c++."]\" value=\"".$row['nodeid']."\"".$s.">";
	echo "</td><td>".$row['nodeid']." - ".$row['nodename']."</td></tr>";
	}
$NATS->DB->Free($r);
echo "<tr><td colspan=\"2\"><input type=submit value=\"".$NATS->Lang->Item("group.update")."\"></td></tr>";
echo "</form></table>";
End_Round();
?>


<?php
Screen_Footer();
?>
