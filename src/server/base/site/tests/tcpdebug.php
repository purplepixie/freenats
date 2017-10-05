<?php // tcp.debug.php -- Debug TCP test module
global $NATS;

if (isset($NATS))
{
class FreeNATS_TCP_Debug extends FreeNATS_Local_Test
	{
		
	function DoTest($testname,$param,$hostname="",$timeout=-1,$params=false)
		{ 
		echo "Called for ".$hostname." port ".$param." timeout ".$timeout."\n";
		$timer=new TFNTimer();
		$ip=ip_lookup($hostname);
		echo $hostname." => ".$ip."\n";
		if ($ip=="0") return -2; // lookup failed
		echo "Lookup Successful\n";
		$errno=0;
		$errstr="";
		$timer->Start();
		echo "Doing fsockopen()\n";
		$fp=@fsockopen($ip,$param,$errno,$errstr,$timeout);
		$elapsed=$timer->Stop();
		echo "FP is : ";
		echo $fp;
		echo "\n";
		if ($fp===false) return -1; // open failed
		echo "Closing\n";
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
$NATS->Tests->Register("tcpdebug","FreeNATS_TCP_Debug",$params,"TCP Debug Connect",1,"FreeNATS TCP Debug");
$NATS->Tests->SetUnits("tcpdebug","Seconds","s");
}


?>