<?php // help.inc.php
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

/* // LEGACY WAY
$NATS_Help=array();

function ha($id,$text)
{
global $NATS_Help;
$NATS_Help[strtoupper($id)]=$text;
}

ha("FreeNATS","FreeNATS is a network monitoring package");

ha("Node","A node is a system or device which you are monitoring. Note that nodes can have tests pointing to different physical devices and vice-versa");
ha("Node:Create","Create a new {node} with this {node:id|NodeID}");
ha("Node:ID","The NodeID is a unique text name for a {node}. Maximum length 60 chars and only normal characters allowed.");
ha("Node:Enabled","The node is enabled for tests to be performed (and displayed in all applicable views)");
ha("Node:AlertActive","Alerts will be generated for this node and alert actions performed if selected");
ha("Node:PingTest","Do a ping (ICMP) test first before other tests - will always record results if enabled");
ha("Node:RequirePing","Require the ping to pass for other tests to even be tried - will always fail if {Node:PingTest|ping test} is disabled");
ha("Node:Master","Master Node to be Tested First");
ha("MasterNode:Ping","Tests required to pass on the {Node:Master|master node} for this node to be tested");
ha("Node:TestInterval","Interval (minutes) between tests on this node being carried out");
ha("Nodeside","Enable support for node-side scripts to gather data from this node");
ha("Node:CheckASAP","Schedule node for immediate text in next test cycle");

ha("UtilLinks","Links to services on remote note for system administration");

ha("Nodeside:Key","Key used to authenticate nodeside testing");
ha("Nodeside:PullEnabled","Enable nodeside collection via http 'pull' from the node");
ha("Nodeside:URL","URL to 'pull' nodeside data from on the node");
ha("Nodeside:Interval","Interval between 'pulling' (polling) data from the node");
ha("Nodeside:PushEnabled","Allow node to 'push' (post) data");
ha("Nodeside:PushIP","Limit node 'push' to this specific IP address for security");

ha("Group","A group is a collection of one or more {node|nodes}. Note that nodes can be members of more than one group.");
ha("Group:Create","Create a new {group} with this name");

ha("Test:Name","A custom name can be assigned to the test for use in displays and alerts. If blank an automated name will be generated.");
ha("Test:Attempts","Number of times to attempt a test (will always try once). Defaults to 1 if zero or unset.");
ha("Test:Timeout","Timeout value for the test - alters the defaults or system-wide variable settings for the relevant test (seconds)");
ha("Test:SimpleEvaluation","Use simple pass/fail evaluation (i.e. if a web test returns any data it passes else it fails)");
ha("Test:Recorded","Record the results of this test for historic reports");
ha("Test:Enabled","Perform the test or not (will update nothing if disabled)");
ha("Test:Interval","Interval (minutes) between this test being performed. Will only ever be tested as quickly as the {Node:TestInterval|node interval} is set to");
ha("Test:TestAlerts","Test will generate an alert on failure");

ha("History:Should","A rough calculation based on configured polling times (i.e. test interval or node interval) from the start to finish (or now if sooner)");

ha("View","A view is a custom set of nodes/groups/tests which can be used in a variety of ways");
ha("View:Create","Creates a {view} of the specified name");
ha("View:Public","The view can be seen by people not logged into FreeNATS");
ha("View:Colons","If text status is selected for a view item a colon will be displayed before it");
ha("View:Dashes","Display a dash before a test last run time where applicable");
ha("View:TimeAgo","Use XX:XX ago or normal datetime for test details");
ha("View:Columns","Display list-type elements in this number of columns (0 for off)");
ha("View:Refresh","Send a http-equv to refresh the page after this many seconds (0 for off)");
ha("View:UseColour","Use colour for status display as in normal FreeNATS display");
ha("View:ShowDetail","Show details of the test/object(s) such as node tests");
ha("View:TextStatus","Textual status such as passed, failed etc");

ha("Report","Reports show the %age of service availability in a specified period");

ha("Backup:Truncate","Clears tables first when restored back (recommended) otherwise duplicates may fail");

ha("Schedule","A schedule controls what times a node is tested and are managed through the admin page");
ha("Schedule:DefaultAction","What will happen if no exceptions are matched (the opposite will happen if one is matched)");

ha("AlertAction","An alert action is performed when a node fails a test, they are configured through the admin page");
ha("AAction:Warnings","The action is triggered for warning level events as well as failures (including downgrading from failure to warning if {AAction:Decreases|decreases} is set)");
ha("AAction:Decreases","The action is triggered when the level goes down to closed (or {AAction:Warnings|warnings} if set)");
ha("AAction:Limit","Max times this action can run in any one day (0 = unlimited) to avoid flooding");
ha("AAction:Counter","Number of times this action has run in the day shown - you can manually reset this here");

ha("Variable","System variables are used to control the system environment. To delete a variable just save it with a blank name.");
ha("Var:log.level","The system log level - 10 is everything, 0 is fatal only. 5-6-ish is probably a good balance.");

ha("Var:api.public","The API interface is available to public users if 1 (also see {Var:api.key|api.key})");
ha("Var:api.key","Require this key from public users as apikey when accessing the API ({Var:api.public|api.public} must be set)");

ha("Var:alert.body.footer","Footer to go at the end of system generated alerts");
ha("Var:alert.body.header","Header to go at the start of system generated alerts");
ha("Var:alert.subject.long","Subject for email alerts of long format");
ha("Var:alert.subject.short","Subject for email alerts of short format");
ha("Var:freenats.firstrun","System variable to indicate first use of FreeNATS");
ha("Var:site.enable.tests","Enable testing");
ha("Var:site.enable.web","Enable web interface");
ha("Var:site.enable.tester","Enable Tester to Run");

ha("Var:site.graph.public","Graphs are available to public users for views etc if 1 (also see {Var:site.graph.key|site.graph.key})");
ha("Var:site.graph.key","Require this key from public users as graphkey ({Var:site.graph.public|site.graph.public} must be set)");

ha("Var:site.enable.interactive","Must be set (1) to allow interactive web sessions (like this one)");
ha("Var:site.popupmessage","If set (1) will show a popup message when saving changes etc");
ha("Var:site.dtformat","Site-wide custom date time format (in PHP format) i.e. Y-m-d H:i:s");

ha("Var:site.text.failed","Text to show for a failed status (defaults to Failed if unset)");
ha("Var:site.text.passed","Text to show for a passed status (defaults to Passed if unset)");
ha("Var:site.text.untested","Text to show for untested items (defaults to Untested if unset)");
ha("Var:site.text.warning","Text to show for a warning status (defaults to Warning if unset)");
ha("Var:site.text.closed","Text to indicate alert closed (defaults to Alert Closed if unset)");
ha("Var:site.include.events","Include *.php in the server/site/events directory if 1");
ha("Var:site.include.tests","Include *.php in the server/site/tests directory if 1");
ha("Var:site.enable.adminsql","Enables the admin SQL console if 1");
ha("Var:site.links.newwindow","Opens {UtilLinks|utility links} in new window if 1");

ha("Var:site.monitor.popups","Opens a popup status window in live monitor unless set to 0");

ha("Var:mail.fromname","Textual from name to be used in SMTP mail (defaults to FreeNATS if unset)");
ha("Var:mail.smtpserver","SMTP server to relay mail through (can be ; seperated list) uses internal mail() if unset or blank");
ha("Var:mail.smtpusername","Username for SMTP AUTH (AUTH only used if username is set or provided)");
ha("Var:mail.smtppassword","Password for SMTP AUTH");
ha("Var:mail.smtphostname","Hostname for HELO in SMTP transactions (may be required for strict mail servers)");

ha("Var:test.icmp.timeout","Timeout in seconds to wait for an ICMP response before declaring a failure in seconds (default 10)");
ha("Var:test.icmp.trytwice","Depreciated - replaced with {Var:test.icmp.attempts|test.icmp.attempts}");
ha("Var:test.icmp.attempts","Number of times the main &quot;require ping&quot; test will attempt before failing (default 2)");
ha("Var:test.icmp.returnms","If set (1) returns ping results in MilliSeconds (ms) (default 0)");
ha("Var:test.http.timeout","Default timeout for HTTP/s streams used in web tests (uses system default if unset or &lt;1)");
ha("Var:test.imap.timeout","Default timeout for IMAP tests in seconds (uses environment default if unset or 0)");
ha("Var:test.smtp.timeout","Default timeout for SMTP tests in seconds (uses 20 seconds if 0 or unset)");
ha("Var:test.mysql.timeout","Default timeout for MySQL tests in seconds (uses environmental default if unset or 0)");
ha("Var:test.spawndelay","Delay in seconds between node test threads being spawned by test-threaded.sh (no delay if 0 or unset), can be a decimal");
ha("Var:test.interval","Delay in seconds between tests on a node (no delay if 0 or unset), can be a decimal");
ha("Var:test.tcp.timeout","Default timeout for TCP tests (system default if 0 or unset) (seconds)");
ha("Var:test.udp.timeout","Default timeout for UDP tests (system default if 0 or unset) (seconds)");

ha("Var:retain.alert","Days to retain alert records for (default 356 if 0 or unset). Retain forever with value -1.");
ha("Var:retain.record","Days to retain test result records for use in history and graphs (default 356 if 0 or unsert). Retain forever with value -1.");
ha("Var:retain.testrun","Days to retain test run records for (default 30 if 0 or unset). Retain forever with value -1.");
ha("Var:retain.syslog","Days to retain log entries for (default 30 if 0 or unset). Retain forever with value -1.");

ha("Var:freenats.tracker","Participate in the automated feedback process - 1 for yes low level, 2 for detailed, 0 for disabled (default 0)");
ha("Var:freenats.tracker.usid","Unique Site ID for FreeNATS tracker to anonymise data capture (only if {Var:freenats.tracker|freenats.tracker} set to 1");
*/
function hdisp($id, $html = true)
{
	global $NATS;
	$t = $NATS->Lang->Item("help." . strtoupper($id));
	$o = "";
	$mode = "text";
	$linktxt = "";
	$linkid = "";
	$linktext = false;
	for ($a = 0; $a < strlen($t); $a++) {
		$c = $t[$a];

		if ($c == "{") // start of a link
		{
			$mode = "link";
			$linktext = false;
		} else if (($mode == "link") && ($c == "|")) // in a link and move into text mode...
		{
			$linktext = true;
		} else if (($mode == "link") && ($c == "}")) // in a link and the end of the link
		{
			if (!$linktext) $linktxt = $linkid;
			if ($html) $o .= "<a href=\"help.php?id=" . $linkid . "\">";
			$o .= $linktxt;
			if ($html) $o .= "</a>";
			$mode = "text";
		} else if ($mode == "link") // in a link
		{
			if ($linktext) $linktxt .= $c;
			else $linkid .= $c;
		} else // in text
			$o .= $c;
	}
	return $o;
}

function hlink($id, $size = 16)
{
	global $NATS;
	if (isset($NATS->Lang->items["help." . strtoupper($id)]))
		return "<a href=\"javascript:freenats_help('" . $id . "')\"><img src=\"images/info" . $size . ".gif\" title=\"" . hdisp($id, false) . "\" border=0></a>";

	return "<img src=\"images/info" . $size . "g.gif\" border=0 title=\"" . $NATS->Lang->Item("nohelp") . " (" . $id . ")\">";
}

function ph($id, $size = 16)
{
	echo hlink($id, $size);
}
