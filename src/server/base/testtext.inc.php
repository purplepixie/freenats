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

function lText($test)
{
global $NATS;
switch ($test)
	{
	case "web": return $NATS->Lang->Item("web.size");
	//case "tcp": return "TCP Port Test";
	case "testloop": return $NATS->Lang->Item("test.loop");
	case "wtime": return $NATS->Lang->Item("web.time");
	case "ICMP": return $NATS->Lang->Item("node.ping");
	case "ping": return $NATS->Lang->Item("remote.ping");
	case "testrand": return $NATS->Lang->Item("test.random");
	//case "mysql": return "MySQL";
	//case "mysqlrows": return "MySQL Rows";
	//case "imap": return "IMAP";
	//case "smtp": return "SMTP";
	case "host": return $NATS->Lang->Item("dns.host");
	//case "dns": return "DNS Query";
	default:
	if (isset($NATS))
		{
		if (isset($NATS->Tests->QuickList[$test])) return $NATS->Tests->QuickList[$test];
		}
	return $test;
	}
}

function lUnit($test)
{
global $NATS;
switch ($test)
	{
	case "web": case "wsize": return "KiloBytes";
	//case "tcp": return "Pass/Fail";
	case "testloop": return "";
	case "wtime": return "Seconds";
	case "ICMP": case "ping": 
				if ($NATS->Cfg->Get("test.icmp.returnms",0)==1) return "Milliseconds";
				else return "Seconds";

	case "testrand": return "";
	//case "imap": return "Seconds";
	//case "smtp": return "Seconds";
	//case "mysql": return "Seconds";
	//case "mysqlrows": return "Rows";
	case "host": return "Seconds";
	//case "dns": return "Seconds";
	default: 
	if (isset($NATS))
   		return $NATS->Tests->Units($test);
	return "";
	}
}

function oText($val)
{
global $NATS;
if (isset($NATS)) $n=true;
else $n=false;
switch ($val)
	{
	case -1: 
		if ($n) return $NATS->Cfg->Get("site.text.untested",$NATS->Lang->Item("untested"));
		return $NATS->Lang->Item("untested");
	case 0:
		if ($n) return $NATS->Cfg->Get("site.text.passed",$NATS->Lang->Item("passed")); 
		return $NATS->Lang->Item("passed");
	case 1: 
		if ($n) return $NATS->Cfg->Get("site.text.warning",$NATS->Lang->Item("warning"));
		return $NATS->Lang->Item("warning");
	case 2: 
		if ($n) return $NATS->Cfg->Get("site.text.failed",$NATS->Lang->Item("failed"));
		return $NATS->Lang->Item("failed");
	default:
		if ($n) return $NATS->Cfg->Get("site.text.unknown",$NATS->Lang->Item("unknown")); 
		return $NATS->Lang->Item("unknown");
	}
}	
?>