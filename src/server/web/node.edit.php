<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2011 PurplePixie Systems

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
if ($NATS_Session->userlevel < 5) UL_Error($NATS->Lang->Item("node.editor"));
ob_end_flush();
Screen_Header($NATS->Lang->Item("node.editor"), 1, 1, "", "main", "nodes");
echo "\n<script type=\"text/javascript\">\n";
echo "var iconDivText='";
ShowIcons();
echo "';\n\n";
echo "function showIcons()\n";
echo "{\n";
echo "if (document.getElementById('iconDiv').innerHTML=='') document.getElementById('iconDiv').innerHTML='<br><br>'+iconDivText+'<br><br>';\n";
echo "else document.getElementById('iconDiv').innerHTML='';\n";
echo "}\n\n";
echo "var optionContent='';\n";
echo "function showOptions()\n";
echo "{\n";
echo "if (document.getElementById('nodeoptions').innerHTML=='') document.getElementById('nodeoptions').innerHTML=optionContent;\n";
echo "else\n";
echo " {\n";
echo " if (optionContent=='') optionContent=document.getElementById('nodeoptions').innerHTML;\n";
echo " document.getElementById('nodeoptions').innerHTML='';\n";
echo " }\n";
echo "}\n";
echo "</script>\n\n";
?>
<br>
<?php
if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action'] == "save_edit") {
		$_REQUEST['showoptions'] = 1;
		if (isset($_REQUEST['nodeenabled'])) $nodeenabled = 1;
		else $nodeenabled = 0;
		if (isset($_REQUEST['pingtest'])) $pingtest = 1;
		else $pingtest = 0;
		if (isset($_REQUEST['pingfatal'])) $pingfatal = 1;
		else $pingfatal = 0;
		if (isset($_REQUEST['nodealert'])) $nodealert = 1;
		else $nodealert = 0;
		if (isset($_REQUEST['testinterval'])) $interval = $_REQUEST['testinterval'];
		else $interval = 0;
		if (!is_numeric($interval)) $interval = 0;

		if (isset($_REQUEST['nsenabled'])) $nsenabled = 1;
		else $nsenabled = 0;

		$q = "UPDATE fnnode SET ";
		$q .= "nodename=\"" . ss($NATS->StripGPC($_REQUEST['nodename'])) . "\",";
		$q .= "nodedesc=\"" . ss($NATS->StripGPC($_REQUEST['nodedesc'])) . "\",";
		$q .= "testinterval=" . ss($interval) . ",";
		if (isset($_REQUEST['scheduleid'])) $q .= "scheduleid=" . ss($_REQUEST['scheduleid']) . ",";
		$q .= "nodeicon=\"" . ss($_REQUEST['nodeicon']) . "\",";
		$q .= "nodealert=" . ss($nodealert) . ",";
		$q .= "nodeenabled=" . ss($nodeenabled) . ",pingtest=" . ss($pingtest) . ",pingfatal=" . ss($pingfatal) . ",";
		$q .= "nsenabled=" . ss($nsenabled) . ",";

		$q .= "masterid=\"" . ss($_REQUEST['masterid']) . "\",";
		$q .= "masterjustping=" . ss($_REQUEST['masterjustping']) . ",";

		if (isset($_REQUEST['nsurl'])) $q .= "nsurl=\"" . ss($NATS->StripGPC($_REQUEST['nsurl'])) . "\",";
		if (isset($_REQUEST['nskey'])) $q .= "nskey=\"" . ss($NATS->StripGPC($_REQUEST['nskey'])) . "\",";
		if (isset($_REQUEST['nspuship'])) $q .= "nspuship=\"" . ss($_REQUEST['nspuship']) . "\",";
		if (isset($_REQUEST['nsinterval'])) {
			$q .= "nsinterval=" . ss($_REQUEST['nsinterval']) . ",";
			// Also use this text field as the indication that ns data is provided at all
			if (isset($_REQUEST['nspullenabled'])) $set = 1;
			else $set = 0;
			$q .= "nspullenabled=" . $set . ",";
			if (isset($_REQUEST['nspushenabled'])) $set = 1;
			else $set = 0;
			$q .= "nspushenabled=" . $set . ",";
		}

		$q .= "hostname=\"" . ss($_REQUEST['hostname']) . "\"";

		//if ($NATS->Cfg->Get("dev.links",0)==1)
		//{
		// Utility Links
		for ($a = 0; $a < 3; $a++) {
			$ulink = "ulink" . $a;
			$utitle = $ulink . "_title";
			$uurl = $ulink . "_url";
			if (isset($_REQUEST[$ulink])) $ulinkv = 1;
			else $ulinkv = 0;
			$q .= "," . $ulink . "=" . $ulinkv . ",";
			$q .= $utitle . "=\"" . ss($_REQUEST[$utitle]) . "\",";
			$q .= $uurl . "=\"" . ss($_REQUEST[$uurl]) . "\"";
		}
		//}



		$q .= " WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\"";
		//echo $q;
		$NATS->DB->Query($q);

		if ($NATS->DB->Affected_Rows() <= 0) {
			echo "<b style=\"color: red;\">" . $NATS->Lang->Item("save.failed") . "</b><br>";
			$poplist[] = $NATS->Lang->Item("save.failed");
		} else {
			echo "<b style=\"color: green;\">" . $NATS->Lang->Item("save.ok") . "</b><br>";
			$poplist[] = $NATS->Lang->Item("save.ok");
		}

		// Update Interval If Changed
		if (
			isset($_REQUEST['testinterval']) && isset($_REQUEST['original_testinterval']) &&
			($_REQUEST['testinterval'] != $_REQUEST['original_testinterval'])
		)
			$NATS->InvalidateNode($_REQUEST['nodeid']);

		// Update Nodeside if Newly Enabled and empty...
		if (isset($_REQUEST['original_nsenabled']) && ($_REQUEST['original_nsenabled'] == 0) && ($nsenabled == 1)) {
			$q = "SELECT nskey FROM fnnode WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\"";
			$r = $NATS->DB->Query($q);
			if ($row = $NATS->DB->Fetch_Array($r)) {
				if ($row['nskey'] == "") // generate one
				{
					$allow = "abcdef0123456789";
					$len = 64;
					$alen = strlen($allow);
					mt_srand(microtime(true) * 1000000);
					$key = "";
					for ($a = 0; $a < $len; $a++)
						$key .= $allow[mt_rand(0, $alen - 1)];
					$uq = "UPDATE fnnode SET nskey=\"" . $key . "\" WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\"";
					$NATS->DB->Query($uq);
				}
				$NATS->DB->Free($r);
			}
		}
	} else if ($_REQUEST['action'] == "invalidate") {
		$_REQUEST['showoptions'] = 1;
		$NATS->InvalidateNode($_REQUEST['nodeid'], true);
	} else if ($_REQUEST['action'] == "nodesiderefresh") {
		$_REQUEST['showoptions'] = 1;
		$res = $NATS->NodeSide_Pull($_REQUEST['nodeid']);
		if ($res === false) {
			$poplist[] = $NATS->Lang->Item("nodeside.fetch.error");
			echo "<b style=\"color: red;\">" . $NATS->Lang->Item("nodeside.fetch.error") . "</b><br><br>";
		} else {
			$poplist[] = $NATS->Lang->Item("nodeside.refreshed");
			echo "<b style=\"color: green;\">" . $NATS->Lang->Item("nodeside.refreshed") . "</b><br><br>";
		}
	} else if ($_REQUEST['action'] == "save_actions") {
		// build the two lists...
		$cur = array();
		$cc = 0;
		$nl = array();
		$nc = 0;

		if (isset($_REQUEST['links'])) {
			foreach ($_REQUEST['links'] as $link) {
				$nl[$link]['proc'] = false; // not processed or existing i.e. outstanding
				$nl[$link]['aaid'] = $link;
				$nc++;
			}
		}

		$q = "SELECT nalid,aaid FROM fnnalink WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\"";
		$r = $NATS->DB->Query($q);
		while ($row = $NATS->DB->Fetch_Array($r)) {
			//echo $row['aaid'].":";
			$cur[$row['aaid']]['proc'] = false;
			$cur[$row['aaid']]['nalid'] = $row['nalid'];
			$cur[$row['aaid']]['aaid'] = $row['aaid'];
			$cc++;
		}

		// now we have the two lists lets process them
		foreach ($nl as $newone) {
			if (isset($cur[$newone['aaid']])) // exists - do nothing to both
			{
				//echo "<br>".$newone['aaid'].":";
				$cur[$newone['aaid']]['proc'] = true;
				$nl[$newone['aaid']]['proc'] = true;
			}
			// otherwise news are left false to insert and curs false to delete
		}

		// so lets do that
		foreach ($nl as $newone) {
			if ($newone['proc'] == false) {
				//echo $q;
				$q = "INSERT INTO fnnalink(aaid,nodeid) VALUES(" . ss($newone['aaid']) . ",\"" . ss($_REQUEST['nodeid']) . "\")";
				$NATS->DB->Query($q);
			}
		}
		foreach ($cur as $curone) {
			if ($curone['proc'] == false) {
				$q = "DELETE FROM fnnalink WHERE nalid=" . $curone['nalid'];
				$NATS->DB->Query($q);
				//echo $q;
			}
		}

		echo $NATS->Lang->Item("save.ok") . "<br><br>";
		$poplist[] = $NATS->Lang->Item("save.ok");
	}
}



echo "<b class=\"subtitle\">" . $NATS->Lang->Item("editing") . " <a href=main.php?mode=nodes>" . $NATS->Lang->Item("node") . "</a>: ";
echo "<a href=node.php?nodeid=" . $_REQUEST['nodeid'] . ">" . $_REQUEST['nodeid'] . "</a></b><br><br>";


$q = "SELECT * FROM fnnode WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\" LIMIT 0,1";
$r = $NATS->DB->Query($q);
if (!$row = $NATS->DB->Fetch_Array($r)) {
	echo $NATS->Lang->Item("no.such.node") . "<br><br>";
	Screen_Footer();
	exit();
}
$NATS->DB->Free($r);

$title = "<b class=\"sectitle\">" . $NATS->Lang->Item("node.settings") . "</b>";
Start_Round($title, 600);
echo "<form action=node.edit.php method=post>";
echo "<div id=\"nodeoptions\">";
echo "<table border=0 width=100%>";

echo "<input type=hidden name=action value=save_edit>";
echo "<input type=hidden name=nodeid value=" . $_REQUEST['nodeid'] . ">";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("node.id");
echo " :</td><td align=left><b>";
echo $row['nodeid'];
echo "</b></td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("node.name");
echo " :</td><td align=left>";
echo "<input type=text name=nodename size=20 maxlength=128 value=\"" . $row['nodename'] . "\">";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("hostname");
echo " :</td><td align=left>";
echo "<input type=text name=hostname size=20 maxlength=128 value=\"" . $row['hostname'] . "\">";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("description");
echo " :</td><td align=left>";
echo "<input type=text name=nodedesc size=30 maxlength=200 value=\"" . $row['nodedesc'] . "\">";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("node.icon");
echo " :</td><td align=left>";
echo "<select name=nodeicon>";
if ($row['nodeicon'] != "") echo "<option value=\"" . $row['nodeicon'] . "\">" . $row['nodeicon'] . "</option>";
echo "<option value=\"\">" . $NATS->Lang->Item("default") . "</option>";
$iconList = GetIcons();
foreach ($iconList as $icon)
	echo "<option value=\"" . $icon . "\">" . $icon . "</option>";
//echo "</select> [ <a href=node.edit.php?nodeid=".$_REQUEST['nodeid']."&show_icons=1>Show Icons</a> ]";
echo "</select> [ <a href=\"javascript:showIcons()\">" . $NATS->Lang->Item("show.hide.icons") . "</a> ]";
echo "</td></tr>";

echo "<tr><td colspan=2>";
echo "<div id=\"iconDiv\"></div>";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("master.node");
echo " :</td><td align=left>";
echo "<select name=masterid>\n";
if ($row['masterid'] == "") $s = " selected";
else $s = "";
echo "<option value=\"\"" . $s . ">" . $NATS->Lang->Item("no.master") . "</option>\n";

$nlq = "SELECT nodeid,nodename FROM fnnode ORDER BY weight ASC";
$nlr = $NATS->DB->Query($nlq);
while ($noderow = $NATS->DB->Fetch_Array($nlr)) {
	if ($noderow['nodeid'] != $row['nodeid']) // not this node
	{
		if ($noderow['nodeid'] == $row['masterid']) // this is the master
			$s = " selected";
		else
			$s = "";
		echo "<option value=\"" . $noderow['nodeid'] . "\"" . $s . ">";
		if ($noderow['nodename'] != "") echo $noderow['nodename'] . " (" . $noderow['nodeid'] . ")";
		else echo $noderow['nodeid'];
		echo "</option>\n";
	}
}
$NATS->DB->Free($nlr);


echo "</select> " . hlink("Node:Master") . "\n";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("master.skip");
echo " :</td><td align=left>";
echo "<select name=masterjustping>\n";

if ($row['masterjustping'] == 1) $s = " selected";
else $s = "";

echo "<option value=1" . $s . ">" . $NATS->Lang->Item("fails.ping") . "</option>\n";
if ($s == "") $s = " selected";
else $s = "";
echo "<option value=0" . $s . ">" . $NATS->Lang->Item("fails.any.test") . "</option>\n";

echo "</select> " . hlink("MasterNode:Ping") . "\n";
echo "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("node.enabled");
echo " :</td><td align=left>";
if ($row['nodeenabled'] == 1) $s = " checked";
else $s = "";
echo "<input type=checkbox value=1 name=nodeenabled" . $s . ">";
echo " " . hlink("Node:Enabled");
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("test.schedule");
echo " :</td><td align=left>";
echo "<select name=scheduleid>";
echo "<option value=0>At All Times</option>";
$sq = "SELECT scheduleid,schedulename FROM fnschedule";
$sr = $NATS->DB->Query($sq);
while ($sched = $NATS->DB->Fetch_Array($sr)) {
	if ($sched['scheduleid'] == $row['scheduleid']) $s .= " selected";
	else $s = "";
	echo "<option value=" . $sched['scheduleid'] . $s . ">" . $sched['schedulename'] . "</option>";
}
echo "</select>";
$NATS->DB->Free($sr);
echo " " . hlink("Schedule");
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("test.interval");
echo " :</td><td align=left>";
echo "<input type=text name=testinterval size=2 maxlength=8 value=\"" . $row['testinterval'] . "\"> ";
echo $NATS->Lang->Item("minutes") . " " . hlink("Node:TestInterval");
echo "</td></tr>";
echo "<input type=hidden name=original_testinterval value=\"" . $row['testinterval'] . "\"";

echo "<tr><td align=right valign=top>";
echo $NATS->Lang->Item("test.due");
echo " :</td><td align=left>";
if ($row['nextrunx'] > 0) echo nicedt($row['nextrunx']);
else echo $NATS->Lang->Item("now");
echo "<br>";
echo nicenextx($row['nextrunx']);
echo " <a href=node.edit.php?nodeid=" . $_REQUEST['nodeid'] . "&action=invalidate>" . $NATS->Lang->Item("check.asap") . "</a> ";
echo hlink("Node:CheckASAP", 12);
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("alerts.active");
echo " :</td><td align=left>";
if ($row['nodealert'] == 1) $s = " checked";
else $s = "";
echo "<input type=checkbox value=1 name=nodealert" . $s . ">";
echo " " . hlink("Node:AlertActive");
echo "</td></tr>";

echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("ping.test");
echo " :</td><td align=left>";
if ($row['pingtest'] == 1) $s = " checked";
else $s = "";
echo "<input type=checkbox value=1 name=pingtest" . $s . ">";
echo " " . hlink("Node:PingTest");
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("require.ping");
echo " :</td><td align=left>";
if ($row['pingfatal'] == 1) $s = " checked";
else $s = "";
echo "<input type=checkbox value=1 name=pingfatal" . $s . ">";
echo " " . hlink("Node:RequirePing");
echo "</td></tr>";

echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("nodeside.testing");
echo " :</td><td align=left>";
if ($row['nsenabled'] == 1) $nodeside = true;
else $nodeside = false;
if ($nodeside) $s = " checked";
else $s = "";
echo "<input type=checkbox value=1 name=nsenabled" . $s . ">";
echo " " . hlink("Nodeside");
echo "</td></tr>";
echo "<input type=hidden name=original_nsenabled value=" . $row['nsenabled'] . ">";

if ($nodeside) {
	echo "<tr><td align=right>" . $NATS->Lang->Item("node.key") . " :</td><td align=left>";
	echo "<input type=text name=nskey value=\"" . $row['nskey'] . "\" size=30 maxlength=120>";
	echo " " . hlink("Nodeside:Key");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("pull.enabled") . " :</td>";
	if ($row['nspullenabled'] == 1) $s = " checked";
	else $s = "";
	echo "<td align=left><input type=checkbox name=nspullenabled value=1" . $s . "> ";
	echo hlink("Nodeside:PullEnabled");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("pull.url") . " :</td><td align=left>";
	echo "<input type=text name=nsurl value=\"" . $row['nsurl'] . "\" size=30 maxlength=250>";
	if ($row['nsurl'] != "") {
		$uri = $row['nsurl'] . "?nodekey=" . $row['nskey'] . "&noupdates=1";
		echo " <a href=\"" . $uri . "\" target=top><i>" . $NATS->Lang->Item("debug.raw") . "</i></a>";
	}
	echo " " . hlink("Nodeside:URL");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("pull.interval") . " :</td><td align=left>";
	echo "<input type=text name=nsinterval value=\"" . $row['nsinterval'] . "\" size=2 maxlength=10>";
	echo " " . $NATS->Lang->Item("minutes") . " " . hlink("Nodeside:Interval");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("push.enabled") . " :</td>";
	if ($row['nspushenabled'] == 1) $s = " checked";
	else $s = "";
	echo "<td align=left><input type=checkbox name=nspushenabled value=1" . $s . "> ";
	echo hlink("Nodeside:PushEnabled");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("push.ip") . " :</td><td align=left>";
	echo "<input type=text name=nspuship value=\"" . $row['nspuship'] . "\" size=20 maxlength=120>";
	echo " " . hlink("Nodeside:PushIP");
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("last.data") . " :</td><td align=left>";
	echo nicedt($row['nslastx']) . " - " . dtago($row['nslastx']) . "</td></tr>";

	if ($row['nspullenabled'] == 1) {
		echo "<tr><td align=right>" . $NATS->Lang->Item("next.pull.due") . " :</td><td align=left>";
		if ($row['nsnextx'] > 0) echo nicedt($row['nsnextx']) . " - " . nicenextx($row['nsnextx']);
		else echo $NATS->Lang->Item("now");
		echo "</td></tr>";
	}

	echo "<tr><td align=right>" . $NATS->Lang->Item("catalogue") . " :</td><td align=left>";
	$nsq = "SELECT COUNT(nstestid) FROM fnnstest WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\"";
	$nsr = $NATS->DB->Query($nsq);
	if ($nsrow = $NATS->DB->Fetch_Array($nsr)) {
		echo $nsrow['COUNT(nstestid)'] . " " . $NATS->Lang->Item("nodeside.monitors");
	}
	$NATS->DB->Free($nsr);
	echo "</td></tr>";

	echo "<tr><td align=right>" . $NATS->Lang->Item("refresh.now") . ":</td><td align=left>";
	if ($row['nspullenabled'] == 1) echo "<a href=node.edit.php?nodeid=" . $_REQUEST['nodeid'] . "&action=nodesiderefresh>" . $NATS->Lang->Item("nodeside.pull.now") . "</a>";
	else echo "<i>" . $NATS->Lang->Item("nodeside.pull.disabled") . "</i>";
	echo "</td></tr>";
}

echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";

// New Utility Links
//if ($NATS->Cfg->Get("dev.links",0)==1) // ABOVE AS WELL!!
//{ 
echo "<tr><td align=right valign=top>" . $NATS->Lang->Item("utility.links") . " " . hlink("UtilLinks", 12) . " :</td><td align=left>";
echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";
echo "<tr><td>&nbsp;</td><td>" . $NATS->Lang->Item("title") . "</td><td>" . $NATS->Lang->Item("url") . " </td></tr>";
for ($a = 0; $a < 3; $a++) // auto loop through them
{
	$ulink = "ulink" . $a;
	$utitle = $ulink . "_title";
	$uurl = $ulink . "_url";
	echo "<tr><td>";
	if ($row[$ulink]) $s = " checked";
	else $s = "";
	echo "<input type=checkbox name=" . $ulink . " value=1" . $s . ">";
	echo "</td><td>";
	echo "<input type=text name=" . $utitle . " value=\"" . $row[$utitle] . "\" size=10>";
	echo "</td><td>";
	echo "<input type=text name=" . $uurl . " value=\"" . $row[$uurl] . "\" size=25>";
	echo "</td></tr>\n";
}
echo "</table>";
echo "</td></tr>";


echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
//}


echo "<tr><td colspan=2>";
echo "<input type=submit value=\"" . $NATS->Lang->Item("save.settings") . "\"> <a href=main.php>" . $NATS->Lang->Item("cancel") . "</a>";
echo "<br><br>";
echo "</td></tr>";
echo "</table>";
echo "</div></form>";
echo "<i><a href=\"javascript:showOptions()\">" . $NATS->Lang->Item("show.hide.options") . "</a></i>";
echo "<script type=\"text/javascript\">\n";
if (!isset($_REQUEST['showoptions'])) echo "showOptions();\n";
echo "</script>\n";
End_Round();

if (isset($_REQUEST['show_icons'])) {
	echo "<br><br>";
	ShowIcons();
	echo "<br><br>";
}

//echo "<div id=\"iconDiv\"></div>";

echo "<br><br>";
$title = "<b class=\"sectitle\">" . $NATS->Lang->Item("node.tests") . "</b>";
Start_Round($title, 600);
echo "<table class=\"nicetable\" width=100%>";
$q = "SELECT * FROM fnlocaltest WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\" AND testtype!=\"ICMP\" ORDER BY localtestid ASC";
$r = $NATS->DB->Query($q);
while ($row = $NATS->DB->Fetch_Array($r)) {
	echo "<tr><td>";

	echo "<b class=\"al" . $row['alertlevel'] . "\">";
	if (strlen($row['testparam']) > 10) $tp = substr($row['testparam'], 0, 8) . "..";
	else $tp = $row['testparam'];
	if ($row['testname'] == "") echo lText($row['testtype']) . " (" . $tp . ")";
	else echo $row['testname'];
	echo "</b>";
	echo "</td>";

	echo "<td>&nbsp;<a href=localtest.edit.php?localtestid=" . $row['localtestid'] . ">";
	echo "<img src=images/options/application.png border=0 title=\"" . $NATS->Lang->Item("edit.options") . "\"></a>&nbsp;";
	echo "<a href=localtest.action.php?action=delete&localtestid=" . $row['localtestid'] . ">";
	echo "<img src=images/options/action_delete.png border=0 title=\"" . $NATS->Lang->Item("delete") . "\"></a>";

	echo "&nbsp;&nbsp;<i>" . $NATS->Lang->Item("last.tested") . ": " . dtago($row['lastrunx']) . "</i>";
	echo "</td></tr>";
}

if ($nodeside) {
	$q = "SELECT * FROM fnnstest WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\" AND testenabled=1 ORDER BY testtype";
	$r = $NATS->DB->Query($q);
	while ($row = $NATS->DB->Fetch_Array($r)) {
		echo "<tr><td>";

		echo "<b class=\"al" . $row['alertlevel'] . "\">";
		if ($row['testname'] == "") echo $row['testdesc'];
		else echo $row['testname'];
		echo "</b>";
		echo "</td>";

		echo "<td>&nbsp;<a href=nodeside.edit.php?nstestid=" . $row['nstestid'] . ">";
		echo "<img src=images/options/application.png border=0 title=\"" . $NATS->Lang->Item("edit.options") . "\"></a>&nbsp;";
		echo "<a href=nodeside.edit.php?action=disable&nstestid=" . $row['nstestid'] . "&nodeid=" . $_REQUEST['nodeid'] . ">";
		echo "<img src=images/options/action_delete.png border=0 title=\"" . $NATS->Lang->Item("delete") . "\"></a>";

		echo "&nbsp;&nbsp;<i>" . $NATS->Lang->Item("last.tested") . ": " . dtago($row['lastrunx']) . "</i>";
		echo "</td></tr>";
	}
	$NATS->DB->Free($r);
}

echo "<form action=localtest.action.php method=post><input type=hidden name=action value=create>";
echo "<input type=hidden name=nodeid value=\"" . $_REQUEST['nodeid'] . "\">\n";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
echo "<tr><td><b>" . $NATS->Lang->Item("add.test") . " :</b></td>";
echo "<td><select name=testtype>";
echo "<option value=wtime>" . $NATS->Lang->Item("web.time") . "</option>";

echo "<option value=wsize>" . $NATS->Lang->Item("web.size") . "</option><option value=ping>" . $NATS->Lang->Item("remote.ping") . "</option>";

echo "<option value=host>" . $NATS->Lang->Item("dns.host") . "</option>";

// New Test Manager List
foreach ($NATS->Tests->QuickList as $key => $val) {
	echo "<option value=" . $key . ">" . $val . "</option>";
}
echo "<option value=testrand>" . $NATS->Lang->Item("test.random") . "</option><option value=testloop>" . $NATS->Lang->Item("test.loop") . "</option>";
echo "</select> ";

// one day will do fancy JS option here but for now moved to the edit
//echo "<input type=text name=testparam size=20 maxlength=128> ";
echo "<input type=hidden name=testparam value=\"\">";
echo "<input type=hidden name=testcreatedisabled value=1>";

echo "<input type=submit value=\"" . $NATS->Lang->Item("create.test") . "\">";
echo "</td></tr></form>";

if ($nodeside) {
	echo "<form action=nodeside.edit.php method=post><input type=hidden name=action value=enable>";
	echo "<input type=hidden name=nodeid value=" . $_REQUEST['nodeid'] . ">";
	echo "<tr><td><b>" . $NATS->Lang->Item("add.nodeside") . " :</b></td>";
	echo "<td><select name=nstestid>";
	$nsq = "SELECT nstestid,testtype,testdesc FROM fnnstest WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\" AND testenabled=0 ORDER BY testtype";
	$nsr = $NATS->DB->Query($nsq);
	while ($nsrow = $NATS->DB->Fetch_Array($nsr)) {
		echo "<option value=" . $nsrow['nstestid'] . ">";
		if ($nsrow['testdesc'] == "") echo $nsrow['testtype'];
		else echo $nsrow['testdesc'] . "</option>";
	}
	echo "</select>";
	echo " <input type=submit value=\"" . $NATS->Lang->Item("add") . "\">";
	echo "</td></tr></form>";
}

echo "</table>";
End_Round();
echo "<br><br>";

$title = "<b class=\"sectitle\">" . $NATS->Lang->Item("alert.actions") . "</b> " . hlink("AlertAction", 12);
Start_Round($title, 600);
echo "<table border=0><form action=node.edit.php>";
echo "<input type=hidden name=nodeid value=" . $_REQUEST['nodeid'] . ">";
echo "<input type=hidden name=action value=save_actions>";
$q = "SELECT aaid,aname FROM fnalertaction";
$r = $NATS->DB->Query($q);
$c = 0;
while ($row = $NATS->DB->Fetch_Array($r)) {
	// has link?
	$lq = "SELECT nalid FROM fnnalink WHERE nodeid=\"" . ss($_REQUEST['nodeid']) . "\" AND aaid=" . ss($row['aaid']) . " LIMIT 0,1";
	$lr = $NATS->DB->Query($lq);
	if ($NATS->DB->Num_Rows($lr) > 0) $s = " checked";
	else $s = "";
	$NATS->DB->Free($lr);
	echo "<tr><td><input type=checkbox name=\"links[" . $c++ . "]\" value=\"" . $row['aaid'] . "\"" . $s . ">";
	echo "</td><td>" . $row['aaid'] . " - " . $row['aname'] . "</td></tr>";
}
$NATS->DB->Free($r);
echo "<tr><td colspan=\"2\"><input type=submit value=\"" . $NATS->Lang->Item("update.alert.actions") . "\"></td></tr>";
echo "</form></table>";
End_Round();
?>
<br><br>

<?php
Screen_Footer();
?>