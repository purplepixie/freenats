<?php // tcp.inc.php -- TCP test module
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


if (isset($NATS))
{
class FreeNATS_TCP_Test extends FreeNATS_Local_Test
	{
		
	function DoTest($testname,$param,$hostname,$timeout,$params)
		{ 
		global $NATS;
		$timer=new TFNTimer();
		if ($timeout<=0) $timeout=$NATS->Cfg->Get("test.tcp.timeout",0); // if no test-specific param use sys default
		if ($timeout<=0) $timeout=60; // if sys default is <=0 then default to 60 seconds
		$ip=ip_lookup($hostname);
		if ($ip=="0") return -2; // lookup failed
		$errno=0;
		$errstr="";
		$timer->Start();
		$fp=@fsockopen($ip,$param,$errno,$errstr,$timeout);
		$elapsed=$timer->Stop();
		if ($fp===false) return -1; // open failed
		@fclose($fp);
		return $elapsed;
		}
		
	function Evaluate($result) 
		{
		if ($result<0) return 2; // failure
		return 0; // else success
		}
	
	function DisplayForm(&$row)
		{
		echo "<table border=0>";
		echo "<tr><td align=left>";
		echo "TCP Port :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
		echo "</td></tr>";
		echo "</table>";
		}
		
	}
	
$params=array();
$NATS->Tests->Register("tcp","FreeNATS_TCP_Test",$params,"TCP Connect",1,"FreeNATS TCP Tester");
$NATS->Tests->SetUnits("tcp","Seconds","s");
}


?>