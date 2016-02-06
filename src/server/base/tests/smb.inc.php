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

if (!isset($smbclientBinary)) $smbclientBinary="/usr/bin/smbclient";



function smb_connect_time($share,$username,$password)
{
global $smbclientBinary,$NATS;
$timer=new TFNTimer();

$cmd=$smbclientBinary." ";
if ($password!="") $username=$username."%".$password;
if ($username!="") $cmd.="--user ".$username." ";
$cmd.="-N ";
$cmd.="-c exit";
$cmd.=" ".$share;
$timer->Start();
//echo $cmd;
$output=array();
$line=exec($cmd);
//echo $line;
$time=$timer->Stop();
// Domain=
if ( (strlen($line)>7) )
	{ 
		$dom=substr($line,0,7);
		if ($dom=="Domain=")
		{
		$result=true;
		}
		else $result=false;
	}
else if ($line=="")
	{
	$result=true; // blank weird output
	}
else
	{
	$result=false;
	if (isset($NATS))
		{
		$NATS->Event("SMB Failed: ".$line,2,"Test","SMB");
		}
	}


if (!$result) return -1; // connect failed
$time=round($time,4);
if ($time==0) $time=0.0001;
return $time;
}

if (isset($NATS))
{
class FreeNATS_SMB_Test extends FreeNATS_Local_Test
	{
	function DoTest($testname,$param,$hostname,$timeout,$params)
		{
		return smb_connect_time($params[0],$params[1],$params[2]);
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
		echo "Share :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['testparam']."\">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Use full share with forward slashes i.e. //server/sharename</i></td></tr>";
		echo "<tr><td align=left>";
		echo "Username :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam1 size=30 maxlength=128 value=\"".$row['testparam1']."\">";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave username blank to attempt anonymous authentication</i></td></tr>";
		echo "<tr><td align=left>";
		echo "Password :";
		echo "</td><td align=left>";
		echo "<input type=text name=testparam2 size=30 maxlength=128 value=\"\">";
		echo "<input type=hidden name=keepparam2 value=1>";
		echo "</td></tr>";
		echo "<tr><td colspan=2><i>Leave blank to not change or <input type=checkbox name=clearparam2 value=1> click to clear</i></td></tr>";
		echo "</table>";
		}
	
	function ProtectOutput(&$test)
		{
		$test['testparam2']="";
		}
	
	}
$params=array();
$NATS->Tests->Register("smb","FreeNATS_SMB_Test",$params,"SMB Connect",1,"FreeNATS SMB Tester");
$NATS->Tests->SetUnits("smb","Seconds","s");
}


?>