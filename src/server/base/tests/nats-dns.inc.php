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
// requires that the phpdns API already be included

if (isset($NATS))
{
class FreeNATS_DNSAPI_Test extends FreeNATS_Local_Test
	{
	function DoTest($testname,$param,$hostname,$timeout,$params)
		{
		$timer=new TFNTimer();
		if ($param=="") $dnsserver=$hostname;
		else $dnsserver=$param;
		if ($dnsserver=="") return -3;
		if ($timeout<=0) $timeout=60;
		if ($params[4]==1) $udp=false; // use TCP
		else $udp=true;
		if (($params[3]=="")||($params[3]==0)) $port=53;
		else $port=$params[3];
		
		$dns_query=new DNSQuery($dnsserver,$port,$timeout,$udp,false); // run with debug off
		$type=$params[2];
		$query=$params[1];
		
		$timer->Start();
		$answer=$dns_query->Query($query,$type);
		$elapsedTime=$timer->Stop();
		
		if ( ($answer===false) || ($dns_query->error) ) return -2; // query error
		if ($answer->count<=0) return -1; // no records returned
		
		// otherwise we've got some results ok
		$elapsedTime=round($elapsedTime,4);
		if ($elapsedTime<=0) return 0.0001;
		return $elapsedTime;
		break;
		}
	function Evaluate($result) 
		{
		if ($result<0) return 2; // failed
		return 0; // passed
		}
	function DisplayForm(&$row)
		{
		echo "<table border=0>";
		echo "<tr><td align=left>";
		echo "Host/Domain/IP :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"".$row['testparam1']."\">";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Nameserver :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave blank to use the node's hostname</i></td></tr>";
		echo "<tr><td align=left>";
		echo "Query Type :";
		echo "</td><td align=left>";
		echo "<select name=testparam2>";
		if ($row['testparam2']!="") 
			echo "<option value=".$row['testparam2'].">".$row['testparam2']."</option>";
		echo "<option value=A>A</option>";
		echo "<option value=MX>MX</option>";
		echo "<option value=MX>NS</option>";
		echo "<option value=PTR>PTR</option>";
		echo "<option value=MX>SOA</option>";
		echo "</select>";
		echo "</td></tr>";
		echo "<tr><td align=left>";
		echo "Port :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam3 size=10 maxlength=128 value=\"".$row['testparam3']."\">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave blank to use protocol default port (53)</i></td></tr>";
		echo "<tr><td align=left>";
		echo "TCP :";
		echo "</td><td align=left>";
		if ($row['testparam4']==1) $s=" checked";
		else $s="";
		echo "<input type=checkbox name=testparam4 value=1".$s.">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Uses UDP if unchecked</i></td></tr>";
		echo "</table>";
		}
	
	
	}
	
$params=array();
$NATS->Tests->Register("dns","FreeNATS_DNSAPI_Test",$params,"DNS Query",2,"FreeNATS DNS Tester");
$NATS->Tests->SetUnits("dns","Seconds","s");
}

?>
