<?php // udp.inc.php -- UDP test module
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2011 PurplePixie Systems

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

/* UDP Test Updated to Check for Response
   Feedback and code basis from Marc Franquesa http://www.l3jane.net/wiki/factory:factory
*/

if (isset($NATS))
{
class FreeNATS_UDP_Test extends FreeNATS_Local_Test
	{
		
	function DoTest($testname,$param,$hostname,$timeout,$params)
		{ 
		global $NATS;
		
		if ($timeout<=0) $timeout=$NATS->Cfg->Get("test.udp.timeout",0); // if no test-specific param use sys default
		if ($timeout<=0) $timeout=20; // if sys default is <=0 then default to 60 seconds
		
		if ($params[1]!="") $package = $params[1];
		else $package="\x00";
		
		if ($params[2]==1) $reqresponse=true;
		else $reqresponse=false;
		
		$timer=new TFNTimer();
		
		$ip=ip_lookup($hostname);
		if ($ip=="0") return -2; // lookup failed
		
		$connstr="udp://".$ip;
		$errno=0;
		$errstr="";
		
		$timer->Start();
		
		$fp=@fsockopen($connstr,$param,$errno,$errstr,$timeout);
		if ($fp===false) return -1; // open failed
		
		stream_set_timeout($fp, $timeout);
		
		$write = fwrite($fp, $package); // send some data
		if (!$write) return -3; // failed to send data
		
		$read = fgets($fp);
		
		@fclose($fp);
		
		$elapsed = $timer->Stop();
		
		if (!$read)
		{
			if ($reqresponse) return -4; // no response and one was required
			else if (round($elapsed,0) < $timeout) 
			{
				return -5; // looks like a hard reject e.g. ICMP port unreachable
			}
		}
		
		if ($elapsed==0) $elapsed="0.001";
		return $elapsed;
		}
		
	function Evaluate($result) 
		{
		if ($result<=0) return 2; // failure
		return 0; // else success
		}
	
	function DisplayForm(&$row)
		{
		echo "<table border=0>";
		echo "<tr><td align=left>";
		echo "UDP Port :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Send Data :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"".$row['testparam1']."\">";
		echo "</td></tr>";
		echo "<tr><td>&nbsp;</td><td align=\"left\">(optional, blank for default)</td></tr>";
		echo "<tr><td align=left>";
		echo "Require Response :";
		echo "</td><td align=left>";
		if ($row['testparam2']==1) $s=" checked";
		else $s="";
		echo "<input type=checkbox name=testparam2 size=30 value=\"1\"".$s.">";
		echo "</td></tr>";
		echo "<tr><td>&nbsp;</td><td align=\"left\">Requires data response from the server (usually leave unchecked)</td></tr>";
		echo "</table>";
		}
		
	}
	
$params=array();
$NATS->Tests->Register("udp","FreeNATS_UDP_Test",$params,"UDP Connect",2,"FreeNATS UDP Tester");
$NATS->Tests->SetUnits("udp","Seconds","s");
}


?>