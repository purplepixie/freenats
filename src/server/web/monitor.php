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

ob_start();
require("include.php");
$NATS->Start();

// Timeskip check - means this page skips timecheck/reset if site.monitor.keepalive is 0
if ($NATS->Cfg->Get("site.monitor.keepalive",1)==0) $timeskip=true;
else $timeskip=false;

if (!$NATS_Session->Check($NATS->DB,$timeskip))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<1) UL_Error($NATS->Lang->Item("live.monitor"));

if (isset($_REQUEST['style']))
	{
	$style=$_REQUEST['style'];
	setcookie("fn_monitorstyle",$style);
	}
else if (isset($_COOKIE['fn_monitorstyle']))
	{
	$style=$_COOKIE['fn_monitorstyle'];
	}
else $style="standard";

if ($style=="") $style="standard";

Screen_Header($NATS->Lang->Item("live.monitor"),1,1,"<meta http-equiv=\"refresh\" content=\"60\">");
?>
<br>

<div class="monitorviews" id="monitorviews_div">
<a href="javascript:showMonitorViews()"><?php echo $NATS->Lang->Item("mon.options"); ?></a>
</div>

<?php
/*
echo "<style type=\"text/css\">\n";
include("css/monitor.css");
echo "\n</style>\n";
*/
?>
<link rel="stylesheet" type="text/css" href="css/monitor.css" />
<?php
if ($NATS->Cfg->Get("site.monitor.popup",1)==0) $monpopup=false;
else $monpopup=true;

if ($monpopup)
{
echo "<div class=\"monitor_popup\" id=\"popup_div\"></div>";
echo "<script type=\"text/javascript\" src=\"js/monitor.popup.js\">\n";
echo "</script>\n";
}
?>

<script type="text/javascript">
var dispOptions=new Array();
var dispLink=new Array();

var optCount=0;
dispOptions[optCount]='standard';
dispLink[optCount++]='<?php echo $NATS->Lang->Item("mon.std"); ?>';
dispOptions[optCount]='groups';
dispLink[optCount++]='<?php echo $NATS->Lang->Item("mon.grp"); ?>';
dispOptions[optCount]='nodes';
dispLink[optCount++]='<?php echo $NATS->Lang->Item("mon.nodes"); ?>';
dispOptions[optCount]='alerting';
dispLink[optCount++]='<?php echo $NATS->Lang->Item("mon.alerting"); ?>';
dispOptions[optCount]='list';
dispLink[optCount++]='<?php echo $NATS->Lang->Item("mon.list"); ?>';

function showMonitorViews()
{
var content='';
for (var a=0; a<optCount; a++)
	{
	content=content+'<a href=monitor.php?style='+dispOptions[a]+'>';
	content=content+'<img src=images/monitor_thumb/'+dispOptions[a]+'.png border=0><br>'+dispLink[a]+'</a><br><br>';
	}
content=content+'<a href="javascript:hideMonitorViews()"><?php echo $NATS->Lang->Item("mon.hide"); ?></a>';
document.getElementById('monitorviews_div').innerHTML=content;
}

function hideMonitorViews()
{
document.getElementById('monitorviews_div').innerHTML='<a href="javascript:showMonitorViews()"><?php echo $NATS->Lang->Item("mon.options"); ?></a>';
}

<?php
 if (isset($_REQUEST['showviewoption'])) echo "showMonitorViews();\n";
?>
</script>

<?php


ob_end_flush();

if ($style=="standard")
{

$q="SELECT * FROM fngroup ORDER BY weight ASC";
$r=$NATS->DB->Query($q);

if ($NATS->DB->Num_Rows($r)>0)
	{
	echo "<table border=0>";
	$a=0;
	while ($row=$NATS->DB->Fetch_Array($r))
	{
		if ($NATS->isUserAllowedGroup($NATS_Session->username,$row['groupid']))
		{
			if ($a==0) echo "<tr>";
			echo "<td>";
			ng_big($row['groupid'],$row['groupname'],$row['groupdesc'],$row['groupicon']);
			echo "</td>";
			$a++;
			if ($a==2)
				{
				$a=0;
				echo "</tr>";
				}
		}
	}
	if ($a>0) echo "</tr>";
	echo "</table>";
	echo "<br><br>";
	}


$NATS->DB->Free($r);


$q="SELECT * FROM fnnode ORDER BY alertlevel DESC, weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
{
	if ($NATS->isUserAllowedNode($NATS_Session->username,$row['nodeid']))
	{
		if ($a==0) echo "<tr>";
		echo "<td>";
		np_tiny($row['nodeid'],true,$row['nodename'],$monpopup);
		echo "</td>";
		$a++;
		if ($a==5)
			{
			$a=0;
			echo "</tr>";
			}
	}
}
if ($a>0) echo "</tr>";
echo "</table>";
$NATS->DB->Free($r);
}

else if ($style=="alerting")
{
$q="SELECT * FROM fngroup ORDER BY weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
	{
		if ($NATS->GroupAlertLevel($row['groupid'])>0 && $NATS->isUserAllowedGroup($NATS_Session->username,$row['groupid']))
		{
		if ($a==0) echo "<tr>";
		echo "<td>";
		ng_big($row['groupid'],$row['groupname'],$row['groupdesc'],$row['groupicon']);
		echo "</td>";
		$a++;
		if ($a==2)
			{
			$a=0;
			echo "</tr>";
			}
		}
	}
if ($a>0) echo "</tr>";
echo "</table>";


$NATS->DB->Free($r);

echo "<br><br>";

$q="SELECT * FROM fnnode WHERE alertlevel!=0 ORDER BY alertlevel DESC, weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
{
	if ($NATS->isUserAllowedNode($NATS_Session->username,$row['nodeid']))
	{
		if ($a==0) echo "<tr>";
		echo "<td>";
		np_tiny($row['nodeid'],true,$row['nodename'],$monpopup);
		echo "</td>";
		$a++;
		if ($a==5)
			{
			$a=0;
			echo "</tr>";
			}
	}
}
if ($a>0) echo "</tr>";
echo "</table>";
$NATS->DB->Free($r);
}

else if ($style=="groups")
{
$q="SELECT * FROM fngroup ORDER BY weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
{
	if ($NATS->isUserAllowedGroup($NATS_Session->username,$row['groupid']))
	{
		if ($a==0) echo "<tr>";
		echo "<td>";
		ng_big($row['groupid'],$row['groupname'],$row['groupdesc'],$row['groupicon'],$monpopup);
		echo "</td>";
		$a++;
		if ($a==2)
			{
			$a=0;
			echo "</tr>";
			}
	}
}
if ($a>0) echo "</tr>";
echo "</table>";


$NATS->DB->Free($r);
}

else if ($style=="nodes")
{
$q="SELECT * FROM fnnode ORDER BY alertlevel DESC, weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
{
	if ($NATS->isUserAllowedNode($NATS_Session->username,$row['nodeid']))
	{
		if ($a==0) echo "<tr>";
		echo "<td>";
		np_big($row['nodeid'],$row['nodename'],$row['nodedesc'],$row['nodeicon'],$monpopup);
		echo "</td>";
		$a++;
		if ($a==2)
			{
			$a=0;
			echo "</tr>";
			}
	}
}
if ($a>0) echo "</tr>";
echo "</table>";


$NATS->DB->Free($r);
}
else if ($style=="list")
{
$q="SELECT * FROM fnnode ORDER BY alertlevel DESC, weight ASC";
$r=$NATS->DB->Query($q);

echo "<table border=0>";
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
{
	if ($NATS->isUserAllowedNode($NATS_Session->username,$row['nodeid']))
	{
		if ($a==0) {
            echo "<tr>";
            echo '<td align="left">'.$NATS->Lang->Item("node.name").'</td><td align="left">'.$NATS->Lang->Item("description").'</td><td align="center">'.$NATS->Lang->Item("status").'</td><td align="center">'.$NATS->Lang->Item("node.enabled").'</td>';
            echo "</tr>";
        }
		np_list_item($row['nodeid'],$row['nodename'],$row['nodedesc'],$row['nodeenabled'],$monpopup);
		$a++;
	}
}
echo "</table>";


$NATS->DB->Free($r);
}

else
{
echo "<b>Sorry - unknown display style type</b><br><br>";
}

mt_srand(microtime(true)*1000000);
if (mt_rand(0,100)==50) $track_if_enabled=true;
else $track_if_enabled=false;

Screen_Footer($track_if_enabled);
?>
