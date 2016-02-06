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
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<9) UL_Error("Admin DNS Console");

Screen_Header("DNS Console",1,1);

echo "<br><b class=\"subtitle\">Simple DNS Query</b><br><br>";

echo "<table class=\"nicetable\">";

if (isset($_REQUEST['question'])) $hostname=$_REQUEST['question'];
else $hostname="";

echo "<tr>";
echo "<form action=admin.dns.php method=get>";
echo "<input type=hidden name=gethostbyname value=1>";
echo "<td>Get host by <b>name</b> :</td>";
echo "<td><input type=text name=question size=30 value=\"".$hostname."\"> ";
echo "<input type=submit value=\"Go\">";
echo "</td></tr></form>";

echo "<tr>";
echo "<form action=admin.dns.php method=get>";
echo "<input type=hidden name=gethostbyaddr value=1>";
echo "<td>Get host by <b>address</b> :</td>";
echo "<td><input type=text name=question size=30 value=\"".$hostname."\"> ";
echo "<input type=submit value=\"Go\">";
echo "</td></tr></form>";

echo "</table>";

if (isset($_REQUEST['gethostbyname']))
	{
	$result=gethostbyname($hostname);
	echo "<br>".$hostname." =&gt; ".$result;
	if ($result==$hostname) echo " -- Lookup Failed";
	}
	
if (isset($_REQUEST['gethostbyaddr']))
	{
	$result=gethostbyaddr($hostname);
	echo "<br>".$hostname." =&gt; ".$result;
	if ($result=="") echo " -- Lookup Failed";
	}
	

echo "<br><br><b class=\"subtitle\">Complex DNS Query</b><br><br>";
// ** IGNORE THIS - It's just the web form ** //
if (isset($_REQUEST['server'])) $server=$_REQUEST['server'];
else $server="127.0.0.1";
if (isset($_REQUEST['port'])) $port=$_REQUEST['port'];
else $port=53;
if (isset($_REQUEST['timeout'])) $timeout=$_REQUEST['timeout'];
else $timeout=60;
if (isset($_REQUEST['tcp'])) $udp=false;
else $udp=true;
if (isset($_REQUEST['debug'])) $debug=true;
else $debug=false;
if (isset($_REQUEST['binarydebug'])) $binarydebug=true;
else $binarydebug=false;
if (isset($_REQUEST['extendanswer'])) $extendanswer=true;
else $extendanswer=false;
if (isset($_REQUEST['type'])) $type=$_REQUEST['type'];
else $type="A";
if (isset($_REQUEST['question'])) $question=$_REQUEST['question'];
else $question="www.purplepixie.org";

echo "<table class=\"nicetable\">";
echo "<form action=admin.dns.php method=get>";
echo "<input type=hidden name=doquery value=1>";
echo "<tr><td>";
echo "Query :";
echo "</td><td>";
echo "<input type=text name=question size=50 value=\"".$question."\"> ";
echo "<select name=type>";
echo "<option value=".$type.">".$type."</option>";
echo "<option value=A>A</option>";
echo "<option value=MX>MX</option>";
echo "<option value=PTR>PTR</option>";
echo "<option value=SOA>SOA</option>";
echo "<option value=NS>NS</option>";
echo "<option value=ANY>ANY</option>";
echo "<option value=SMARTA>SmartA</option>";
echo "</select>";
echo "</td></tr><tr><td>";
echo "Nameserver :";
echo "</td><td>";
echo "<input type=text name=server size=30 value=\"".$server."\"> ";
echo "port <input type=text name=port size=4 value=\"".$port."\">";
echo "</td></tr><tr><td align=left valign=top>Options :</td>";
echo "<td valign=top>";

if (!$udp) $s=" checked";
else $s="";
echo "<input type=checkbox name=tcp value=1".$s."> Use TCP<br>";

if ($debug) $s=" checked";
else $s="";
echo "<input type=checkbox name=debug value=1".$s."> Debug Data<br>";

if ($binarydebug) $s=" checked";
else $s="";
echo "<input type=checkbox name=binarydebug value=1".$s."> Binary Debug<br>";

if ($extendanswer) $s=" checked";
else $s="";
echo "<input type=checkbox name=extendanswer value=1".$s."> Show Detail<br>";
echo "</td></tr><tr><td>&nbsp;</td><td>";
echo "<input type=submit value=\"Perform DNS Query\"><br>";
echo "</td></tr></table>";

// ** HERE IS THE QUERY SECTION ** //

if (isset($_REQUEST['doquery']))
{
echo "<pre>";
$query=new DNSQuery($server,$port,$timeout,$udp,$debug);
if ($binarydebug) $query->binarydebug=true;

if ($type=="SMARTA")
	{
	echo "Smart A Lookup for ".$question."\n\n";
	$hostname=$query->SmartALookup($question);
	echo "Result: ".$hostname."\n\n";
	echo "</pre>";
	Screen_Footer();
	exit();
	}

echo "Querying: ".$question." -t ".$type." @".$server."\n";

$result=$query->Query($question,$type);

if ($query->error)
	{
	echo "\nQuery Error: ".$query->lasterror."\n\n";
	exit();
	}
echo "Returned ".$result->count." Answers\n\n";

function ShowSection($result)
{
global $extendanswer;
for ($i=0; $i<$result->count; $i++)
	{
	echo $i.". ";
	if ($result->results[$i]->string=="") 
		echo $result->results[$i]->typeid."(".$result->results[$i]->type.") => ".$result->results[$i]->data;
	else echo $result->results[$i]->string;
	echo "\n";
	if ($extendanswer) 
		{
		echo " - record type = ".$result->results[$i]->typeid." (# ".$result->results[$i]->type.")\n";
		echo " - record data = ".$result->results[$i]->data."\n";
		echo " - record ttl = ".$result->results[$i]->ttl."\n";
		if (count($result->results[$i]->extras)>0) // additional data
			{
			foreach($result->results[$i]->extras as $key => $val)
				{
				echo " + ".$key." = ".$val."\n";
				}
			}
		}
	echo "\n";
	}
}
ShowSection($result);

if ($extendanswer)
	{
	echo "\nNameserver Records: ".$query->lastnameservers->count."\n";
	ShowSection($query->lastnameservers);
	
	echo "\nAdditional Records: ".$query->lastadditional->count."\n";
	ShowSection($query->lastadditional);
	}

echo "</pre>";
}


Screen_Footer();
?>
