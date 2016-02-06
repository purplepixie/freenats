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

if ($NATS->Cfg->Get("site.auth","")=="http") // HTTP-AUTH
{
	if (!isset($_SERVER['PHP_AUTH_USER']))
	{
		$realm="FreeNATS ".date("Y-m-d H:i:s");
		header("WWW-Authenticate: Basic realm=\"".$realm."\"");
		header("HTTP/1.0 401 Unauthorized");
		echo $NATS->Lang->Item("msg.loginfailed");
		exit();
	}
	else
	{
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
	}
}
else
{
	if (isset($_REQUEST['naun'])) $username=$_REQUEST['naun'];
	else $username="";
	if (isset($_REQUEST['napw'])) $password=$_REQUEST['napw'];
	else $password="";
}

if ($NATS_Session->Create($NATS->DB,$username,$password))
	{
	if ( isset($_REQUEST['nala']) && ($_REQUEST['nala']!="") )
		setcookie("fn_lang",$_REQUEST['nala']);
		
	$loc="main.php";
	if ($NATS->Cfg->Get("site.login.nocheck",0)!="1")
		$loc.="?check_updates=1&quiet_check=1";
	if (isset($_REQUEST['url'])) $loc=$_REQUEST['url'];
	
	if ($NATS->Cfg->Get("freenats.firstrun")=="1") $loc="welcome.php";
	
	header("Location: ".$loc);
	exit();
	}
	
header("Location: ./?msg=2");
exit();
?>