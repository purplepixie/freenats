<?php // imap.inc.php -- IMAP and POP3 test
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

function imap_test_connect($host,$user,$pass,$timeout=-1,$protocol="imap",$port=-1,$ssl=false,$debug=false)
{
global $NATS;
if ($timeout>0) $timeout=$timeout; // use specific for test if set
else
	{
	// otherwise use system if available
	if (isset($NATS)) $timeout=$NATS->Cfg->Get("test.imap.timeout",0);
	if ($timeout<=0) $timeout=0; // unset specifically or in environment
	}
	
if ($timeout>0) imap_timeout(IMAP_OPENTIMEOUT,$timeout);

if ($port<=0)
	{
	$port=143; // default
	if ( ($protocol=="imap") && ($ssl) ) $port=993;
	else if ($protocol=="pop3")
		{
		if ($ssl) $port=995;
		else $port=110;
		}
	}

$mailbox="{".$host.":".$port."/service=".$protocol;
if ($ssl) $mailbox.="/ssl";
$mailbox.="/novalidate-cert";
$mailbox.="}INBOX";
if ($debug) echo $user.":".$pass."@".$mailbox."\n";
$imap=@imap_open($mailbox,$user,$pass);
if ($imap===false) return -1; // failed to connect/open

@imap_close($imap);
return 1;
}

function imap_test_time($host,$user,$pass,$timeout=-1,$protocol="imap",$port=-1,$ssl=false,$debug=false)
{
$timer=new TFNTimer();
$timer->Start();
$res=imap_test_connect($host,$user,$pass,$timeout,$protocol,$port,$ssl,$debug);
$time=$timer->Stop();
if ($res<=0) return $res; // test failed to connect
$time=round($time,4);
if ($time==0) $time=0.0001;
return $time;
}

if (isset($NATS))
{
class FreeNATS_IMAP_Test extends FreeNATS_Local_Test
	{
	function DoTest($testname,$param,$hostname="",$timeout=-1,$params=false)
		{ // 0: host, 1: user, 2: pass, 3: protocol, 4: port, 5: ssl (1/0)
		if ($params[5]==1) $ssl=true;
		else $ssl=false;
		
		$ip=ip_lookup($params[0]);
		if ($ip=="0") return -1;
		
		return imap_test_time($ip,$params[1],$params[2],$timeout,$params[3],$params[4],$ssl);	
		
		}
	function Evaluate($result) 
		{
		if ($result<=0) return 2; // failed
		return 0; // passed
		}
	
	function ProtectOutput(&$test)
		{
		$test['testparam2']="";
		}
		
	function DisplayForm(&$row)
		{
		echo "<table border=0>";
		echo "<tr><td align=left>";
		echo "Hostname :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Username :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"".$row['testparam1']."\">";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Password :";
		echo "</td><td align=left>";
		//echo "<input type=password name=testparam2 size=30 maxlength=128 value=\"".$row['testparam2']."\">"; // debug
		echo "<input type=text name=testparam2 size=30 maxlength=128 value=\"\">";
		echo "<input type=hidden name=keepparam2 value=1>";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave blank to not change or <input type=checkbox name=clearparam2 value=1> click to clear</i></td></tr>";
		echo "<tr><td align=left>";
		echo "Protocol :";
		echo "</td><td align=left>";
		if ($row['testparam3']=="") $protocol="imap";
		else $protocol=$row['testparam3'];
		echo "<select name=testparam3>";
		echo "<option value=".$protocol.">".$protocol."</option>";
		echo "<option value=imap>imap</option>";
		echo "<option value=pop3>pop3</option>";
		echo "</select>";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Port :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam4 size=10 maxlength=128 value=\"".$row['testparam4']."\">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave blank use protocol default port (110, 143 etc)</i></td></tr>";
		echo "<tr><td align=left>";
		echo "SSL :";
		echo "</td><td align=left>";
		if ($row['testparam5']==1) $s=" checked";
		else $s="";
		echo "<input type=checkbox name=testparam5 value=1".$s.">";
		echo "</td></tr>";
		echo "</table>";
		}
		
	}
$params=array();
$NATS->Tests->Register("imap","FreeNATS_IMAP_Test",$params,"IMAP Connect",2,"FreeNATS IMAP/POP Tester");
$NATS->Tests->SetUnits("imap","Seconds","s");
}


?>