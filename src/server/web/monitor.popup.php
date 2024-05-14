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

// This really should be handled by clever Data API (XML) calls but alas
// that is too much work hence the AJAH framework

require("include.php");
$NATS->Start();

// Timeskip check - means this page skips timecheck/reset if site.monitor.keepalive is 0
if ($NATS->Cfg->Get("site.monitor.keepalive", 1) == 0) $timeskip = true;
else $timeskip = false;

if (!$NATS_Session->Check($NATS->DB, $timeskip)) {
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
}
if ($NATS_Session->userlevel < 1) UL_Error("Monitor Popup");

if (isset($_REQUEST['type'])) $type = $_REQUEST['type'];
else $type = "";

echo "<div class=\"popup_inside\">";

switch ($type) {
	case "node": // a node details
		if (isset($_REQUEST['nodeid']) && $NATS->isUserAllowedNode($NATS_Session->username, $_REQUEST['nodeid'])) {
			$nodeid = ss($_REQUEST['nodeid']);
			$node = $NATS->GetNode($nodeid);
			if ($node === false) echo $NATS->Lang->Item("mon.popup.error");
			else {
				echo "<b class=\"al" . $node['alertlevel'] . "\">";
				echo $node['name'];
				echo "</b>";
				echo " (<a href=\"node.php?nodeid=" . $nodeid . "\">" . $NATS->Lang->Item("mon.goto.node") . "</a>)";

				echo "<br /><br />";
				$tests = $NATS->GetNodeTests($nodeid);
				echo "<table border=\"0\">";
				foreach ($tests as $testid) {
					echo "<tr><td align=\"left\" valign=\"top\">";
					$test = $NATS->GetTest($testid);
					echo "<b class=\"al" . $test['alertlevel'] . "\">";
					echo $test['name'];
					echo "</b>:";
					echo "</td><td align=\"left\" valign=\"top\">";
					if (is_numeric($test['lastvalue'])) echo round($test['lastvalue'], 2);
					else echo $test['lastvalue'];
					$c = $testid[0];
					if (is_numeric($c) || ($c == "L")) echo " " . lUnit($test['testtype']);
					echo "</td></tr>\n";
				}
				echo "</table><br />\n";

				echo "<a href=\"node.php?nodeid=" . $nodeid . "\">" . $NATS->Lang->Item("mon.goto.node.page") . "</a><br />";
			}
		} else echo $NATS->Lang->Item("mon.popup.error");
		break;
	default:
		echo "Incorrect or invalid request type to popup framework";
		break;
}

echo "</div>";
