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
if ($NATS_Session->userlevel < 5) UL_Error($NATS->Lang->Item("edit.nodeside.test"));

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case "enable":
			$q = "UPDATE fnnstest SET testenabled=1 WHERE nstestid=" . ss($_REQUEST['nstestid']);
			//echo $q;
			$NATS->DB->Query($q);
			//exit();
			header("Location: node.edit.php?nodeid=" . $_REQUEST['nodeid']);
			exit();
		case "disable":
			$q = "UPDATE fnnstest SET testenabled=0 WHERE nstestid=" . ss($_REQUEST['nstestid']);
			//echo $q;
			$NATS->DB->Query($q);
			//exit();
			header("Location: node.edit.php?nodeid=" . $_REQUEST['nodeid']);
			exit();
		case "save_form":
			if (isset($_REQUEST['testalerts'])) $testalerts = 1;
			else $testalerts = 0;
			if (isset($_REQUEST['simpleeval'])) $simpleeval = 1;
			else $simpleeval = 0;
			if (isset($_REQUEST['testrecord'])) $testrecord = 1;
			else $testrecord = 0;
			$q = "UPDATE fnnstest SET testalerts=" . $testalerts . ",simpleeval=" . $simpleeval . ",testname=\"" . ss($_REQUEST['testname']) . "\",testrecord=" . $testrecord . " ";
			$q .= "WHERE nstestid=" . ss($_REQUEST['nstestid']);
			//echo $q;
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows() > 0) $_REQUEST['message'] = $NATS->Lang->Item("save.ok");
			else $_REQUEST['message'] = $NATS->Lang->Item("save.fail");
			break;
	}
}

ob_end_flush();
Screen_Header($NATS->Lang->Item("edit.nodeside.test"), 1, 1, "", "main", "nodes");
if (isset($_REQUEST['message'])) echo "<br><b>" . $_REQUEST['message'] . "</b><br>";

$q = "SELECT * FROM fnnstest WHERE nstestid=" . ss($_REQUEST['nstestid']) . " LIMIT 0,1";
$r = $NATS->DB->Query($q);

if (!$row = $NATS->DB->Fetch_Array($r)) {
	echo $NATS->Lang->Item("no.test");
	Screen_Footer();
	exit();
}
$NATS->DB->Free($r);

echo "<br>";
echo "<b class=\"subtitle\">" . $NATS->Lang->Item("editing.test") . ": <a href=node.edit.php?nodeid=" . $row['nodeid'] . ">" . $row['nodeid'] . "</a> &gt; ";
if ($row['testname'] == "") echo $row['testtype'];
else echo $row['testname'];
echo "</b><br><br>";

$t = "<b class=\"sectitle\">" . $NATS->Lang->Item("nodeside.test") . " " . $row['testtype'] . " on " . $row['nodeid'] . "</b>";
Start_Round($t, 600);

echo "<table width=100% border=0>";
echo "<form action=nodeside.edit.php method=post>";
echo "<input type=hidden name=action value=save_form>";
echo "<input type=hidden name=nstestid value=" . $row['nstestid'] . ">";
echo "<tr><td align=right>" . $NATS->Lang->Item("ns.type") . " :</td>";
echo "<td align=left>" . $row['testtype'] . "</td></tr>";
echo "<tr><td align=right>" . $NATS->Lang->Item("description") . " :</td>";
echo "<td align=left>" . $row['testdesc'] . "</td></tr>";
echo "<tr><td align=right>" . $NATS->Lang->Item("custom.name") . " :</td>";
echo "<td align=left><input type=text name=testname value=\"" . $row['testname'] . "\" size=30 maxlength=128></td></tr>";

if ($row['testalerts'] == 1) $s = " checked";
else $s = "";
echo "<tr><td align=right>" . $NATS->Lang->Item("test.alerts") . " :</td>";
echo "<td align=left>";
echo "<input type=checkbox name=testalerts value=1" . $s . "> " . hlink("Test:TestAlerts");
echo "</td></tr>";

if ($row['testrecord'] == 1) $s = " checked";
else $s = "";
echo "<tr><td align=right>" . $NATS->Lang->Item("recorded") . " :</td>";
echo "<td align=left>";
echo "<input type=checkbox name=testrecord value=1" . $s . "> " . hlink("Test:Recorded");
echo "</td></tr>";

if ($row['simpleeval'] == 1) $s = " checked";
else $s = "";
echo "<tr><td align=right>" . $NATS->Lang->Item("simple.eval") . " :</td>";
echo "<td align=left>";
echo "<input type=checkbox name=simpleeval value=1" . $s . "> " . hlink("Test:SimpleEvaluation");
echo "</td></tr>";

echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
echo "<tr><td align=right>" . $NATS->Lang->Item("last.checked") . " :</td>";
echo "<td align=left>" . nicedt($row['lastrunx']) . " - " . dtago($row['lastrunx']) . "</td></tr>";
echo "<tr><td align=right>" . $NATS->Lang->Item("last.result") . " :</td>";
echo "<td align=left>";
echo "<b class=\"al" . $row['alertlevel'] . "\">";
echo oText($row['alertlevel']);
echo "</b>";
echo "</td></tr>";
echo "<tr><td align=right>" . $NATS->Lang->Item("last.value") . " :</td>";
echo "<td align=left>" . $row['lastvalue'] . "</td></tr>";
echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";

echo "<tr><td>&nbsp;</td><td align=left><input type=submit value=\"" . $NATS->Lang->Item("save.changes") . "\"> ";
echo "<a href=node.edit.php?nodeid=" . $row['nodeid'] . ">" . $NATS->Lang->Item("abandon.changes") . "</a>";
echo "</td></tr></form>";
echo "</table>";

End_Round();


echo "<br><br>";


/*
$t="<b class=\"sectitle\">Test Evaluators</b>";
Start_Round($t,600);


echo "<table border=0 width=100%>";
echo "<tr><td colspan=2>&nbsp;<br>";
if ($row['simpleeval']==1)
	{
	echo "<i>Custom evaluators will not be processed as<br>Simple Evaluation is checked (above)</i><br>";
	}
echo "</td></tr>";

$q="SELECT * FROM fneval WHERE testid=\"N".ss($_REQUEST['nstestid'])."\" ORDER BY weight ASC";
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td colspan=2>";
	echo "<a href=eval.action.php?action=delete&back=".urlencode("nodeside.edit.php?nstestid=".$_REQUEST['nstestid']."&message=Evaluator+Deleted")."&evalid=".$row['evalid'].">";
	echo "<img src=images/options/action_delete.png border=0 style=\"vertical-align: bottom;\"></a>&nbsp;&nbsp;";	
	echo "Result ".eval_operator_text($row['eoperator'])." ".$row['evalue']." =&gt; ".oText($row['eoutcome'])."";
	//echo " | <a href=eval.action.php?action=move&dir=up&evalid=".$row['evalid'].">Up</a>/<a href=eval.action.php?action=move&dir=dn&evalid=".$row['evalid'].">Down</a>";
	echo "</td></tr>";
	//echo "<tr><td colspan=2>&nbsp;</td></tr>";
	}

echo "<form action=eval.action.php>";
echo "<input type=hidden name=action value=create>";
echo "<input type=hidden name=testid value=N".$_REQUEST['nstestid'].">";
echo "<input type=hidden name=back value=\"nodeside.edit.php?nstestid=".$_REQUEST['nstestid']."\">";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
echo "<tr><td><b>Add New :</b></td>";
echo "<td><select name=eoperator>";
echo "<option value=ET>Equal To</option><option value=LT>Less Than</option><option value=GT>Greater Than</option>";
echo "</select> <input type=text name=evalue size=4 value=0> =&gt; ";
echo "<select name=eoutcome>";
echo "<option value=1>Warning</option>";
echo "<option value=2>Failure</option>";
echo "</select> <input type=submit value=Add></td></tr>";
echo "</form>";


echo "</table>";
End_Round();
*/


$t = "<b class=\"sectitle\">" . $NATS->Lang->Item("test.evals") . "</b>";
Start_Round($t, 600);


echo "<table border=0 width=100%>";
echo "<tr><td colspan=2>&nbsp;<br>";
if ($row['simpleeval'] == 1) {
	echo "<i>" . $NATS->Lang->Item("test.evals.simple") . "</i><br>";
}
echo "</td></tr>";

$q = "SELECT * FROM fneval WHERE testid=\"N" . ss($_REQUEST['nstestid']) . "\" ORDER BY weight ASC";
$r = $NATS->DB->Query($q);
while ($row = $NATS->DB->Fetch_Array($r)) {
	echo "<tr><td colspan=2>";
	echo "<a href=\"eval.action.php?action=delete&back=" . urlencode("nodeside.edit.php?nstestid=" . $_REQUEST['nstestid'] . "&message=" . $NATS->Lang->Item("eval.deleted")) . "&evalid=" . $row['evalid'] . "\">";
	echo "<img src=images/options/action_delete.png border=0 style=\"vertical-align: bottom;\"></a>&nbsp;&nbsp;";
	echo $NATS->Lang->Item("result") . " " . eval_operator_text($row['eoperator']) . " " . $row['evalue'] . " =&gt; " . oText($row['eoutcome']) . "";
	//echo " | <a href=eval.action.php?action=move&dir=up&evalid=".$row['evalid'].">Up</a>/<a href=eval.action.php?action=move&dir=dn&evalid=".$row['evalid'].">Down</a>";
	echo "</td></tr>";
	//echo "<tr><td colspan=2>&nbsp;</td></tr>";
}

echo "<form action=eval.action.php>";
echo "<input type=hidden name=action value=create>";
echo "<input type=hidden name=testid value=N" . $_REQUEST['nstestid'] . ">";
echo "<input type=hidden name=back value=\"nodeside.edit.php?nstestid=" . $_REQUEST['nstestid'] . "\">";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
echo "<tr><td><b>" . $NATS->Lang->Item("add.eval") . " :</b></td>";
echo "<td><select name=eoperator>";
echo "<option value=ET>" . $NATS->Lang->Item("eval.equal") . "</option><option value=LT>" . $NATS->Lang->Item("eval.lt") . "</option>";
echo "<option value=GT>" . $NATS->Lang->Item("eval.gt") . "</option>";
echo "</select> <input type=text name=evalue size=4 value=0> =&gt; ";
echo "<select name=eoutcome>";
echo "<option value=1>" . $NATS->Lang->Item("warning") . "</option>";
echo "<option value=2>" . $NATS->Lang->Item("failure") . "</option>";
echo "</select> <input type=submit value=\"" . $NATS->Lang->Item("add") . "\"></td></tr>";
echo "</form>";


echo "</table>";
End_Round();


Screen_Footer();
