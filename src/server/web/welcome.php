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
if ($NATS_Session->userlevel<5) UL_Error("Welcome Page");


$NATS->Cfg->Set("freenats.firstrun","0");

if (isset($_REQUEST['process']))
	{
	if (isset($_REQUEST['set_tracker'])) $NATS->Cfg->Set("freenats.tracker","1");
	else $NATS->Cfg->Set("freenats.tracker","0");
	if (isset($_REQUEST['send_confirm'])) $NATS->PhoneHome(0,"firstrun.conf");
	}

if (($NATS->Cfg->Get("freenats.tracker")!="")&&($NATS->Cfg->Get("freenats.tracker")>0))
	{
	$NATS->PhoneHome(0,"firstrun");
	}
	
if (isset($_REQUEST['process']))
	{
	$s="main.php?message=Ready+to+setup+nodes";
	if (isset($_REQUEST['check_updates'])) $s.="&check_updates=1";
	header("Location: ".$s);
	exit();
	}
ob_end_flush();
Screen_Header("Welcome to FreeNATS",1);

echo "<br><b>Please take a moment to complete setup of FreeNATS</b><br><br>";
echo "<form action=welcome.php method=post>";
echo "<input type=hidden name=process value=1>";
echo "<input type=checkbox name=check_updates value=1 checked> Check Now for Updates<br><br>";
echo "<input type=checkbox name=set_tracker value=1 checked> Participate in Automated Feedback Program<br><br>";
echo "<input type=checkbox name=send_confirm value=1 checked> Confirm FreeNATS Installation with PurplePixie Systems<br>";
echo "<br>";
echo "<input type=submit value=\"Complete Initial FreeNATS Configuration\">";
echo "</form><br><br>";

?>

<?php
Screen_Footer();
?>
