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
require("include.php");
$NATS->Start();

// From Client - nodeid, nodekey, xml
// From Server - REMOTE_ADDR
$nodeid=$_REQUEST['nodeid'];
$nodekey=$_REQUEST['nodekey'];
$xml=$_REQUEST['xml'];
$remoteip=$_SERVER['REMOTE_ADDR'];

$q="SELECT nskey,nsenabled,nspushenabled,nspuship FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
$r=$NATS->DB->Query($q);
if (!$node=$NATS->DB->Fetch_Array($r))
	{
	echo "Invalid nodeid";
	exit();
	}
if ($node['nsenabled']!=1)
	{
	echo "Nodeside Disabled for Node";
	exit();
	}
if ($node['nspushenabled']!=1)
	{
	echo "Nodeside Push Disabled for Node";
	exit();
	}
if ( ($node['nspuship']!="") && ($node['nspuship'] != $remoteip) )
	{
	echo "Remote IP Not Allowed for Push";
	exit();
	}
if ( ($node['nskey']!="") && ($node['nskey'] != $nodekey) )
	{
	echo "Nodekey Failure";
	exit();
	}
	
// Got here so ok!

$xmlobj=new TNodeXML();

$xmlobj->Parse($xml);

if ($xmlobj->Error()!="")
	{
	echo "XML Error: ".$xmlobj->Error();
	exit();
	}

$eventdata=array("nodeid"=>$nodeid);
$NATS->EventHandler("nodeside_push",$eventdata);
	
$NATS->Nodeside_Process($nodeid,$xmlobj);

$uq="UPDATE fnnode SET nsfreshpush=1,nslastx=".time()." WHERE nodeid=\"".ss($nodeid)."\"";
$NATS->DB->Query($uq);

echo "1";
$NATS->Stop();
exit();
?>