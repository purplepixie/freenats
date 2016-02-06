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
if ($NATS_Session->userlevel<5) UL_Error("Action Test Eval");

function BackIfSet()
{
if (isset($_REQUEST['back']))
	{
	header("Location: ".$_REQUEST['back']);
	exit();
	}
}

switch ($_REQUEST['action'])
	{
	case "create":
		// screw this for the moment
		// get the highest weight for this testid
		//$q="SELECT weight FROM fneval WHERE testid=\"".ss($_REQUEST['testid'])."
		$q="INSERT INTO fneval(testid,eoperator,evalue,eoutcome) VALUES(\"".ss($_REQUEST['testid'])."\",";
		$q.="\"".ss($_REQUEST['eoperator'])."\",\"".ss($_REQUEST['evalue'])."\",\"".ss($_REQUEST['eoutcome'])."\")";
		$NATS->DB->Query($q);
		//echo $q;
		//exit();
		if ($_REQUEST['testid'][0]=="L")
			{
			$ltid=substr($_REQUEST['testid'],1,128);
			header("Location: localtest.edit.php?localtestid=".$ltid);
			exit();
			}
		BackIfSet();
		header("Location: main.php");
		exit();
	case "delete":
		$q="DELETE FROM fneval WHERE evalid=".ss($_REQUEST['evalid']);
		$NATS->DB->Query($q);
		BackIfSet();
		if (isset($_REQUEST['back'])) header("Location: ".$_REQUEST['back']);
		else header("Location: main.php?message=Evaluator+Deleted");
		exit();
	default:
		header("Location: main.php?message=Unknown+Test+Eval+Action");
		exit();
	}

?>
