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
if (!$NATS_Session->Check($NATS->DB)) {
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
}
if ($NATS_Session->userlevel < 9) UL_Error("Backup Manager");

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case "backup":

			header("Content-type: text/sql");
			if (isset($_REQUEST['filename'])) $filename = $_REQUEST['filename'];
			else $filename = "freenats-" . date("Ymd") . ".sql";
			header("Content-Disposition: attachment; filename=" . $filename);

			echo "-- FreeNATS SQL Data Backup\n";
			echo "-- " . date("Y-m-d H:i:s") . "\n\n";
			echo "-- Warning: Only use on FreeNATS " . $NATS->Version . " or later schemas!\n\n";
			foreach ($_REQUEST['table'] as $table) {
				echo "-- " . $table . "\n";
				if (isset($_REQUEST['truncate_' . $table]))
					echo "TRUNCATE TABLE `" . $table . "`;\n";
				$q = "SELECT * FROM " . ss($table);
				$r = $NATS->DB->Query($q);
				while ($row = $NATS->DB->Fetch_Array($r)) {
					$keys = array();
					$vals = array();
					foreach ($row as $key => $val) {
						if (!is_numeric($key)) {
							$keys[] = $key;
							$vals[] = $val;
						}
					}
					$uq = "INSERT INTO `" . $table . "`(";
					$first = true;
					foreach ($keys as $key) {
						if ($first) $first = false;
						else $uq .= ",";
						$uq .= "`" . $key . "`";
					}
					$uq .= ") VALUES(";
					$first = true;
					foreach ($vals as $val) {
						if ($first) $first = false;
						else $uq .= ",";
						$uq .= "\"" . $val . "\"";
					}
					$uq .= ");";
					echo $uq . "\n";
				}
				echo "\n";
			}
			echo "\n-- End of Backup\n";
			ob_end_flush();
			exit();
			break;

		case "restore":
			if (isset($_REQUEST['live_run'])) $live = true;
			else $live = false;
			Screen_Header("Restoration", 1, 1, "", "main", "admin");
			if ($live) echo "<b>Live Data Restore...</b><br><br>";
			else echo "<b>Dummy Run - Show What Will be Done</b><br><br>";

			$data = file_get_contents($_FILES['uploadfile']['tmp_name']);
			$lines = explode("\n", $data);
			$errctr = 0;
			foreach ($lines as $line) {
				$line = trim($line);
				if (($line != "") && ($line[0] != "-")) {
					if (!$live) echo $line . "<br>";
					else {
						$NATS->DB->Query($line);
						if ($NATS->DB->Error()) {
							echo "<br><b style=\"color: red;\">" . $line . "</b><br>";
							echo "Error: " . $NATS->DB->Error_String() . "<br><br>";
							$errctr++;
						} else
							echo "<b style=\"color: green;\">" . $line . "</b><br>";
					}
				}
			}
			echo "<br><br>";
			if ($live) {
				echo "<b>Import Complete: " . $errctr . " Errors</b><br>";
				echo "Please see the detail of any errors above<br><br>";
				echo "<a href=./>Click here to continue</a><br><br>";
			} else {
				echo "<b>Dummy Import Complete</b><br><br>";
				echo "<a href=admin.backup.php>Click here to continue</b><br><br>";
			}
			Screen_Footer();
			exit();
			break;

		default:
			echo "Unknown Action<br><br>";
			break;
	}
}

ob_end_flush();
Screen_Header("Backup and Restore", 1, 1, "", "main", "admin");

echo "<br><b class=\"subtitle\">Make a Backup</b><br><br>";
echo "<form action=admin.backup.php method=post>";
echo "<input type=hidden name=action value=backup>";
$q = "SHOW TABLE STATUS";
if (!isset($_REQUEST['show_all_tables'])) {
	echo "<a href=admin.backup.php?show_all_tables=1><i>Show All Tables in Database (not just fn*)</i></a><br><br>";
	$q .= " LIKE \"fn%\"";
}
$defs = array();
function addt($name, $def, $desc)
{
	global $defs;
	$defs[$name] = array("def" => $def, "desc" => $desc);
}

addt("fnalert", true, "Alert History for Nodes");
addt("fnalertaction", true, "Alert Actions e.g. Email Lists");
addt("fnalertlog", false, "Log Events for Alerts");
addt("fnconfig", true, "System-Wide Configuration");
addt("fneval", true, "Custom Test Evaluators");
addt("fngroup", true, "Node Groups");
addt("fngrouplink", true, "Links Nodes to Groups");
addt("fnlocaltest", true, "Configured Local/Server Tests");
addt("fnlog", false, "System Event Log");
addt("fnnalink", true, "Node to Alert Action Links");
addt("fnnode", true, "Nodes");
addt("fnnstest", true, "Nodeside Test Configurations");
addt("fnrecord", false, "Historic Test Information");
addt("fnreport", true, "Saved Availability Reports");
addt("fnscheditem", true, "Schedule Ranges");
addt("fnschedule", true, "Test Schedules");
addt("fnsession", false, "User Sessions");
addt("fntestrun", false, "Historic Test Runs");
addt("fnuser", true, "Users");
addt("fnview", true, "Views");
addt("fnviewitem", true, "View Items");

echo "<table class=\"nicetable\">";
echo "<tr>";
function tdc($t)
{
	echo "<td>" . $t . "</td>";
}
tdc("<b>Backup</b>");
tdc("<b>Clear " . hlink("Backup:Truncate", 12) . "</b>");
tdc("<b>Table</b>");
tdc("<b>Size</b>");
tdc("<b>Description</b>");
echo "</tr>";

$count = 0;
$r = $NATS->DB->Query($q);
while ($row = $NATS->DB->Fetch_Array($r)) {
	echo "<tr>";
	if (isset($defs[$row['Name']]) && ($defs[$row['Name']]['def'] === true))
		$s = " checked";
	else $s = "";
	tdc("<input type=checkbox name=\"table[" . $count . "]\" value=\"" . $row['Name'] . "\"" . $s . ">");
	tdc("<input type=checkbox name=\"truncate_" . $row['Name'] . "\" value=1 checked>");
	tdc($row['Name']);
	tdc($row['Rows'] . " Rows, " . (round($row['Data_length'] / 1024, 2)) . " KB");
	if (isset($defs[$row['Name']]))
		tdc($defs[$row['Name']]['desc']);
	else
		tdc("<i>Unknown</i>");
	echo "</tr>";
	$count++;
}

echo "</table>";
echo "<input type=submit value=\"Generate Backup File\">";
echo "&nbsp;&nbsp;";
echo "<input type=text name=filename value=\"freenats-" . date("Ymd") . ".sql\" size=30>";
echo "</form><br><br>";


echo "<b class=\"subtitle\">Restore from Backup</b><br><br>";
echo "<form enctype=\"multipart/form-data\" method=\"POST\" action=\"admin.backup.php\">";
echo "<input type=hidden name=action value=restore>";
echo "<b>Backup File: </b><input type=file name=uploadfile> <input type=submit value=Restore><br>";
echo "<input type=checkbox name=live_run value=1> Actually Perform Changes to Database (Live Import)";


Screen_Footer();
