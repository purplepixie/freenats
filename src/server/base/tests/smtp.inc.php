<?php // smtp.inc.php -- SMTP test
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

function smtp_test_connect($hostname,$port=25,$timeout=-1,$debug=false)
{
global $NATS;
if ($timeout>0) $timeout=$timeout; // use specific for test if set
else
	{
	// otherwise use system if available
	if (isset($NATS)) $timeout=$NATS->Cfg->Get("test.smtp.timeout",0);
	if ($timeout<=0) $timeout=20; // unset specifically or in environment
	}
	
if ($port=="") $port=25;
$errno=0;
$errstr=0;
$fp=@fsockopen($hostname,$port,$errno,$errstr,$timeout);
if ($errno!=0) return -1; // socket error on open
if ($fp<=0) return -2; // socket failed to open but no errno
stream_set_timeout($fp,$timeout);
$header=@fgets($fp,128);
if ($debug) echo $header."\n";
if (substr($header,0,3)!="220") return -3; // incorrect SMTP response
if (@fputs($fp,"HELO freenats\n")===false) return -4; // unable to write HELO
$body=@fgets($fp,128);
if ($debug) echo $body."\n";
if ($body=="") return -5; // nothing back from the server
if (@fputs($fp,"QUIT\n")===false) return -6; // cannot write QUIT
@fclose($fp);
return 1;
}

function smtp_test_time($hostname,$port=25,$timeout=-1,$debug=false)
{
$timer=new TFNTimer();
$timer->Start();
$res=smtp_test_connect($hostname,$port,$timeout,$debug);
$time=$timer->Stop();
if ($res<=0) return $res; // connect failed
$time=round($time,4);
if ($time==0) $time=0.0001;
return $time;
}

if (isset($NATS))
{
class FreeNATS_SMTP_Test extends FreeNATS_Local_Test
	{
	function DoTest($testname,$param,$hostname="",$timeout=-1,$params=false)
		{
		// Pre-resolve DNS
		$ip=ip_lookup($params[0]);
		if ($ip=="0") return -1;
		// Do the test
		return smtp_test_time($ip,$params[1],$timeout);
		}
	function Evaluate($result) 
		{
		if ($result<=0) return 2; // failed
		return 0; // passed
		}
	/* // -- this was put in as a test, code left to easily retest with if required - denies host to API	
	function ProtectOutput(&$test)
		{
		$test['testparam']="";
		}
	*/
	}
$params=array( "Hostname", "Port/Defaults to 25 if 0 or unset" );
$NATS->Tests->Register("smtp","FreeNATS_SMTP_Test",$params,"SMTP Connect",2,"FreeNATS SMTP Tester");
$NATS->Tests->SetUnits("smtp","Seconds","s");
}


?>