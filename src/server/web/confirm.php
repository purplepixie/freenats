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

ob_end_flush();
Screen_Header("Confirm ".$_REQUEST['action'],1);

echo "<br><b class=\"minortitle\">Please Confirm You Wish to Continue</b><br><br>";
echo "Action: <b>".$_REQUEST['action']."</b><br>";
echo "<b><a href=".$_REQUEST['back'].">Confirm Action</a></b> | <a href=main.php>Abort Action</a><br><br>";
Screen_Footer();
?>
