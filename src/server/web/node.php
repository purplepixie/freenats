<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2017 PurplePixie Systems

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
	$p = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on" ? "https" : "http";
	$url = $p."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	header("Location: ./?login_msg=Invalid+Or+Expired+Session&url=".urlencode($url));
	exit();
	}
if ($NATS_Session->userlevel<1) UL_Error($NATS->Lang->Item("viewing.node"));

if (!$NATS->isUserAllowedNode($NATS_Session->username,$_REQUEST['nodeid']))
	UL_Error($NATS->Lang->Item("viewing.node"));

ob_end_flush();
Screen_Header($NATS->Lang->Item("viewing.node")." ".$_REQUEST['nodeid'],1);
?>
<br>
<?php
//echo "<b class=\"minortitle\">Node: ".$_REQUEST['nodeid']."</b><br><br>";

echo "<table border=0><tr><td align=left valign=top>";

$q="SELECT * FROM fnnode WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo $NATS->Lang->Item("no.node")."<br><br>";
	Screen_Footer();
	exit();
	}
$NATS->DB->Free($r);
$node=$row;

echo "<table class=\"nicetable\">";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("node.name");
echo " :</td><td align=left>";
echo $row['nodename'];
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("status");
echo " :</td><td align=left>";
echo "<b class=\"al".$row['alertlevel']."\">";
echo oText($row['alertlevel']);
echo "</b>";
echo "</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("hostname");
echo ": </td><td align=left>";
echo $row['hostname'];
echo "&nbsp;</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("description");
echo " :</td><td align=left>";
echo $row['nodedesc'];
echo "&nbsp;</td></tr>";

echo "<tr><td align=right>";
echo $NATS->Lang->Item("enabled");
echo ": </td><td align=left>";
if ($row['nodeenabled']==1) $s=$NATS->Lang->Item("yes");
else $s=$NATS->Lang->Item("no");
echo $s;
echo "</td></tr>";

$aq="SELECT alertid,alertlevel,openedx FROM fnalert WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" AND closedx=0 LIMIT 0,1";
//echo $aq;
$ar=$NATS->DB->Query($aq);
echo "<tr><td align=right>
Alert 
:</td><td align=left>";
if ($arow=$NATS->DB->Fetch_Array($ar))
	{
	echo "<a href=history.alert.php?nodeid=".$_REQUEST['nodeid']."><b class=\"al".$arow['alertlevel']."\">".$NATS->Lang->Item("yes")." - ".oText($arow['alertlevel'])."</b></a> ";
	echo "(".$NATS->Lang->Item("opened")." ".dtago($arow['openedx']).")";
	}
else echo "<b>".$NATS->Lang->Item("no")."</b> [ <a href=history.alert.php?nodeid=".$_REQUEST['nodeid'].">".$NATS->Lang->Item("alert.history")."</a> ]";
echo "</td></tr>";

if ($NATS_Session->userlevel>4) echo "<tr><td align=right>".$NATS->Lang->Item("edit")." :</td>";
echo "<td align=left><a href=node.edit.php?nodeid=".$_REQUEST['nodeid'].">".$NATS->Lang->Item("edit.node.options")."</a></td></tr>";

echo "<tr><td align=right>".$NATS->Lang->Item("summary")." :</td><td align=left>";
$finishx=time();
$startx=$finishx-(60*60*24);
echo "<a href=summary.test.php?nodeid=".$_REQUEST['nodeid']."&startx=".$startx."&finishx=".$finishx.">".$NATS->Lang->Item("last.24h")."</a> | ";
echo "<a href=summary.test.php?nodeid=".$_REQUEST['nodeid'].">".$NATS->Lang->Item("today")."</a>";
echo "</td></tr>";

// Utility Links
//if ($NATS->Cfg->Get("dev.links",0)==1)
//{
$shown_header=false;
for ($a=0; $a<3; $a++)
	{
	$ulink="ulink".$a;
	$utitle=$ulink."_title";
	$uurl=$ulink."_url";
	if ($row[$ulink]==1)
		{ // link is enabled
		if (!$shown_header) // first one
			{
			echo "<tr><td align=right>".$NATS->Lang->Item("links")." :</td><td align=left>";
			$shown_header=true;
			}
		$url=$row[$uurl];
		$url=str_replace("{HOSTNAME}",$row['hostname'],$url);
		$url=str_replace("{NODENAME}",$row['nodename'],$url);
		$url=str_replace("{NODEID}",$row['nodeid'],$url);
		if ($NATS->Cfg->Get("site.links.newwindow",0)==1) $tgt=" target=\"top\"";
		else $tgt="";
		echo "<a href=\"".$url."\"".$tgt.">".$row[$utitle]."</a> ";
		}
	}
if ($shown_header) echo "</td></tr>"; // if shown any
//}

echo "</table>";

echo "</td><td style=\"width: 50px;\">&nbsp;</td><td align=left valign=top>";
np_tiny($_REQUEST['nodeid']);
echo "</td></tr></table>";

echo "<br><br>";

echo "<b class=\"subtitle\">".$NATS->Lang->Item("local.tests")."</b><br><br>";

echo "<table class=\"nicetable\">";

$q="SELECT * FROM fnlocaltest WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" ORDER BY localtestid ASC";
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td>";
	
	if ($row['testname']=="")
		{
		if (strlen($row['testparam'])>10) $tp=substr($row['testparam'],0,8)."..";
		else $tp=$row['testparam'];
		echo lText($row['testtype']);
		if ($tp!="") echo " (".$tp.")";
		}
	else echo $row['testname'];
	echo "</td>";

	echo "<td><b class=\"al".$row['alertlevel']."\">".oText($row['alertlevel'])."</b></td>";
	
	echo "<td>(".dtago($row['lastrunx']).")</td>";
	
	echo "<td>";
	if (($row['testrecord']==1)||($row['testtype']=="ICMP")) 
		echo "[ <a href=\"history.test.php?testid=L".$row['localtestid']."\">".$NATS->Lang->Item("history")."</a> ]";
	else echo "&nbsp;";
	echo "</td>";
	
	echo "</tr>";
	}

echo "</table>";
echo "<br><br>";
if ($node['nsenabled']==1)
	{
	echo "<b class=\"subtitle\">".$NATS->Lang->Item("nodeside.tests")."</b><br><br>";
	
	echo "<table class=\"nicetable\">";
	
	$q="SELECT * FROM fnnstest WHERE nodeid=\"".ss($_REQUEST['nodeid'])."\" AND testenabled=1 ORDER BY testtype";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr><td>";
		
		if ($row['testname']=="")
			{
			echo $row['testdesc'];
			}
		else echo $row['testname'];
		echo "</td>";
	
		echo "<td><b class=\"al".$row['alertlevel']."\">".oText($row['alertlevel'])."</b></td>";
		
		echo "<td>(".dtago($row['lastrunx']).")</td>";
		
		echo "<td>";
		if (($row['testrecord']==1)) 
			echo "[ <a href=\"history.test.php?testid=N".$row['nstestid']."\">".$NATS->Lang->Item("history")."</a> ]";
		else echo "&nbsp;";
		echo "</td>";
		
		echo "</tr>";
		}
	
	echo "</table>";
	}
?>


<?php
Screen_Footer();
?>
