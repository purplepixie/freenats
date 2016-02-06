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
if ($NATS_Session->userlevel<1) UL_Error("View Link");
ob_end_flush();
Screen_Header("View Links",1);
$vid=$_REQUEST['viewid'];
echo "<b class=\"subtitle\">Linking to View # ".$vid."</b><br>";
echo "<a href=view.php?viewid=".$vid.">Preview View</a> | <a href=view.edit.php?viewid=".$vid.">Edit View Settings</a><br><br>";

$base=GetAbsolute("view.php?viewid=".$vid);
echo "<b>URL:</b> ".$base."<br>";
echo "<b>URL:</b> <input type=text size=80 value=\"".$base."\"><br><br>";

echo "<b>JavaScript Include Example</b> (Copy and paste into your page)<br>";
echo "<textarea cols=80 rows=8>";
echo "&lt;script type=\"text/javascript\" src=\"".$base."&mode=js\"&gt;\n";
echo "&lt;/script&gt;\n";
echo "</textarea>";
if (!isset($_REQUEST['jsdemo']))
	{
	echo "<br><br><a href=view.link.php?viewid=".$vid."&jsdemo=1>Demo this working</a><br><br>";
	}
else
	{
	echo "<br><br>";
	echo "<script type=\"text/javascript\" src=\"view.php?mode=js&viewid=".$vid."\"></script>\n";
	echo "<br><br>";
	}
/*
echo "<pre>";
var_dump($_SERVER);
echo "</pre>";
*/

echo "<br><br>";
Screen_Footer();
?>