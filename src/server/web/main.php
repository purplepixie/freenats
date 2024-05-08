<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2024 PurplePixie Systems

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
ob_end_flush();

if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else if ($NATS->isUserRestricted($NATS_Session->username))
{
	$mode="nodes";
	$_REQUEST['mode']="nodes";
}
else if (!$NATS->isUserRestricted($NATS_Session->username))
{
	$mode="overview";
	$_REQUEST['mode']="overview";
}
else
{
	$mode="nodes";
	$_REQUEST['mode']="nodes";
}

if ($mode=="overview" && $NATS->isUserRestricted($NATS_Session->username))
{
	$mode="nodes";
	$_REQUEST['mode']="nodes";
}

Screen_Header($NATS->Lang->Item("overview.title"),1,0,"","main");

if (isset($_REQUEST['message']))
	{
	echo "<b>".$_REQUEST['message']."</b><br>";
	$poplist[]=$_REQUEST['message'];
	}

if (isset($_REQUEST['nodemove'])) $nm=true;
else $nm=false;

function dispyn($val)
{
if ($val==0) return "N";
else if ($val==1) return "Y";
else return $val."?";
}

?>
<br>
<?php
if (isset($_REQUEST['check_updates'])) $check_update=true;
else $check_update=false;
if (isset($_REQUEST['quiet_check'])) $quiet_check=true;
else $quiet_check=false;

if ($check_update)
{
	echo "<b>".$NATS->Lang->Item("checking.updates").":</b> ";
	echo "<span id=\"version-info\">&nbsp;<img src=\"images/loading/small-circle-lines.gif\" style=\"position: relative; top: 4px;\"></span><br>";
	if (!$quiet_check)
	{
		echo "<br>";
		echo $NATS->Lang->Item("update.desc")." ";
		echo $NATS->Version.$NATS->Release.".";
		echo "<br>";
	}
	echo "<br>\n";
}


if ($mode=="overview")
	{

	$t="<b class=\"subtitle\">".$NATS->Lang->Item("overview.subtitle")."</b>";
	Start_Round($t,600);
	echo "<table width=100% border=0><tr><td align=left width=50%>";
	$al=$NATS->GetAlerts();
	if (($al===false)||(count($al)==0))
		{
		echo "<b class=\"al0\">".$NATS->Lang->Item("no.monitor.alerts")."</b>";
		}
	else
		{
		echo "<a href=monitor.php>";
		echo "<b class=\"al2\">".$NATS->Lang->Item("monitor.alerts")."</b>";
		echo "</a>";
		}
	echo "</td><td align=right><b><a href=main.php?check_updates=1>".$NATS->Lang->Item("check.updates")."</a></b></td></tr>";

	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	$fx=time();
	$sx=$fx-(60*60*24);
	echo "<tr><td align=left valign=top>";
	 echo "<b>".$NATS->Lang->Item("monitoring")."</b><br><br>";
	$nq="SELECT COUNT(nodeid) FROM fnnode";
	$nr=$NATS->DB->Query($nq);
	if ($nrow=$NATS->DB->Fetch_Array($nr)) $nodecount=$nrow['COUNT(nodeid)'];
	else $nodecount=0;
	$NATS->DB->Free($nr);
	$gq="SELECT COUNT(groupid) FROM fngroup";
	$gr=$NATS->DB->Query($gq);
	if ($nrow=$NATS->DB->Fetch_Array($gr)) $groupcount=$nrow['COUNT(groupid)'];
	else $groupcount=0;
	$NATS->DB->Free($gr);
	 echo "<a href=main.php?mode=nodes>".$nodecount." ".$NATS->Lang->Item("nodes.configured")."</a><br><br>";
	 echo "<a href=main.php?mode=groups>".$groupcount." ".$NATS->Lang->Item("node.groups")."</a><br>";
	echo "</td><td align=right valign=top>";
	echo "<b>".$NATS->Lang->Item("common.tasks")."</b><br><br>";
	echo "<a href=main.php?mode=nodes>".$NATS->Lang->Item("add.nodes")."</a><br>";
	echo "<a href=admin.php?mode=alertactions>".$NATS->Lang->Item("email.alerting")."</a><br>";
	echo "<a href=main.php?mode=nodes>".$NATS->Lang->Item("configure.tests")."</a><br>";
	echo "</td></tr>";
	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	echo "<tr><td colspan=2><b>".$NATS->Lang->Item("test.summaries")."</b><br><br>";
	echo "<a href=summary.test.php?nodeid=*>".$NATS->Lang->Item("today")."</a> - ";
	echo "<a href=summary.test.php?nodeid=*&startx=".$sx."&finishx=".$fx.">".$NATS->Lang->Item("last.24h")."</a> - ";
	echo "<a href=summary.test.php?mode=custom>".$NATS->Lang->Item("custom")."</a>";
	echo "</td></tr>";
	echo "<tr><td colspan=2><hr class=\"nspacer\"></td></tr>";
	echo "<tr><td colspan=2>";
	/*
	echo "<b>Installed Test Modules</b><br><br>";
	echo "<table class=\"nicetable\" width=100%>";
	echo "<tr><td><b>Name</b></td><td><b>Provides</b></td><td><b>Revision</b></td><td><b>Additional</b></td></tr>";
	foreach($NATS->Tests->QuickList as $key => $val)
		{
		echo "<tr><td>";
		echo $NATS->Tests->Tests[$key]->name;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->type;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->revision;
		echo "</td><td>";
		echo $NATS->Tests->Tests[$key]->additional;
		echo "</td></tr>";
		}
	echo "</table>";
	*/
	echo "<b>".$NATS->Lang->Item("monitored.nodes")."</b><br><br>";
	$q="SELECT nodeid,nodename,alertlevel FROM fnnode WHERE nodeenabled=1 ORDER BY alertlevel DESC, weight ASC";
	$r=$NATS->DB->Query($q);
	$first=true;
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		if ($first) $first=false;
		else echo ", ";
		echo "<a href=node.php?nodeid=".$row['nodeid'].">";
		echo "<b class=\"al".$row['alertlevel']."\">";
		if ($row['nodename']!="") echo $row['nodename'];
		else if ($row['nodeid'] != "") echo $row['nodeid'];
		else echo $NATS->Lang->Item("node");
		echo "</b></a>";
		}
	echo "</td></tr>";
	echo "</table>";
	echo "<br>";
	End_Round();
	echo "<br><br>";

	if ($NATS->Cfg->Get("site.nonews",0)!=1)
	{
		$t="<b class=\"subtitle\">".$NATS->Lang->Item("freenats.news")."</b>";
		Start_Round($t,600);

		echo "<DIV ID=\"news-holder\" STYLE=\"padding: 4px;\">";
		echo "<IMG SRC=\"images/loading/small-circle-lines.gif\">";
		echo "</DIV>\n";

		echo "<I>Want to disable news? Set variable site.nonews to 1</I>";

		End_Round();

		echo "<SCRIPT TYPE=\"text/javascript\">\n";
		//echo "alert('check');\n";
		echo "var url='//www.purplepixie.org/freenats/newsfeed.js.php';\n";
		echo "var nf=document.createElement('script');\n";
		echo "nf.type='text/javascript';\n";
		echo "nf.src=url;\n";
		echo "nf.async=true;\n";
		echo "var fs=document.getElementsByTagName('script')[0];\n";
		echo "fs.parentNode.insertBefore(nf, fs);\n";
		echo "</SCRIPT>\n";

	}

	}

else if ($mode=="nodes")
{

	if ($nm)
	{
	$q="SELECT nodeid,weight FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	$nml="<span style=\"font-size: 8pt;\">".$NATS->Lang->Item("move.before")." </span><select name=move_before style=\"font-size: 8pt;\">";
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$nml.="<option value=".$row['weight'].">".$row['nodeid']."</option>";
		}
	$nml.="</select>";
	$NATS->DB->Free($r);
	}

	Start_Round("<b class=\"subtitle\">".$NATS->Lang->Item("nodes")."</b> ".hlink("Node",12),600);
	$q="SELECT nodeid,nodename,alertlevel,weight FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);

	echo "<table class=\"nicetablehov\" width=100%>";
	echo "<tr><td><b>".$NATS->Lang->Item("node")."</b></td><td><b>".$NATS->Lang->Item("options")."</b></td><td><a href=main.php?mode=nodes&nodemove=1>";
	echo "<b>".$NATS->Lang->Item("move")."</a></b></td></tr>";
	$f=0;
	$l=$NATS->DB->Num_Rows($r);
	while ($row=$NATS->DB->Fetch_Array($r))
	{
		if ($NATS->isUserAllowedNode($NATS_Session->username, $row['nodeid']))
		{
			//echo "<tr class=\"nicetablehov\" id=\"noderow_".$row['nodeid']."\" onmouseover=\"highlightrow('noderow_".$row['nodeid']."')\"><td align=left>";
			echo "<tr class=\"nicetablehov\"><td align=left>";
			echo "<a href=node.php?nodeid=".$row['nodeid'].">";

			echo "<b class=\"al".$row['alertlevel']."\">";
			if ($row['nodename']!="") echo $row['nodename'];
			else if ($row['nodeid'] != "") echo $row['nodeid'];
			else echo $NATS->Lang->Item("node");
			echo "</b>";

			echo "</a> ";
			echo "(".$row['nodeid'].")";
			echo "</td><td align=left>";
			echo "&nbsp;<a href=node.edit.php?nodeid=".$row['nodeid']."><img src=images/options/application.png border=0 title=\"".$NATS->Lang->Item("edit")."\"></a>";
			echo "&nbsp;";
			echo "<a href=node.action.php?action=delete&nodeid=".$row['nodeid']."><img src=images/options/action_delete.png border=0 title=\"".$NATS->Lang->Item("delete")."\"></a> ";
			echo "</td>";

			if ($nm)
				{
				echo "<form action=node.action.php method=post>";
				echo "<input type=hidden name=nodeid value=".$row['nodeid'].">";
				echo "<input type=hidden name=action value=move_before>";
				}

			echo "<td>";
			if ($f==0) echo "<img src=images/arrows/off/arrow_top.png>";
			else
				{
				echo "<a href=node.action.php?nodeid=".$row['nodeid']."&action=move&dir=up>";
				echo "<img src=\"images/arrows/on/arrow_top.png\" border=0>";
				echo "</a>";
				}

			if ($f>=($l-1)) echo "<img src=images/arrows/off/arrow_down.png>";
			else
				{
				echo "<a href=node.action.php?nodeid=".$row['nodeid']."&action=move&dir=down>";
				echo "<img src=\"images/arrows/on/arrow_down.png\" border=0>";
				echo "</a>";
				}

			if ($nm)
				{
				echo "<span style=\"font-size: 8pt;\">&nbsp;[".$row['weight']."]&nbsp;</span>";
				echo $nml;
				echo " <input type=submit value=\"Go\" style=\"font-size: 8pt;\">";
				}

			echo "</td>";

			if ($nm) echo "</form>";
			$f++;

			echo "</tr>";
		}
	}

	echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
	echo "<form action=node.action.php><input type=hidden name=action value=create>";
	echo "<tr><td><input type=text name=nodeid size=20 maxlenth=32></td><td colspan=2><input type=submit value=\"".$NATS->Lang->Item("create.node")."\"> ";
	echo hlink("Node:Create");
	if ($nm) echo " <a href=node.action.php?action=reorderweight>".$NATS->Lang->Item("refresh.weight")."</a>";
	echo "</td></tr></form>";

	$fx=time();
	$sx=$fx-(60*60*24);
	echo "<tr><td colspan=3><b>Summary: </b><a href=summary.test.php?nodeid=*>".$NATS->Lang->Item("today")."</a> - ";
	echo "<a href=summary.test.php?nodeid=*&startx=".$sx."&finishx=".$fx.">".$NATS->Lang->Item("last.24h")."</a> - ";
	echo "<a href=summary.test.php?mode=custom>".$NATS->Lang->Item("custom")."</a> - ";
	echo "<a href=main.php?mode=configsummary>".$NATS->Lang->Item("config")."</a></td></tr>";

	echo "</table>";
	End_Round();
	}

else if ($mode=="groups")
	{

	$t="<b class=\"subtitle\">".$NATS->Lang->Item("node.groups")."</b> ".hlink("Group",12);
	Start_Round($t,600);

	$q="SELECT groupid,groupname FROM fngroup ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	$f=0;
	echo "<table class=\"nicetablehov\" width=100%>";
	$l=$NATS->DB->Num_Rows($r);
	while ($row=$NATS->DB->Fetch_Array($r))
	{
		if ($NATS->isUserAllowedGroup($NATS_Session->username,$row['groupid']))
		{
			echo "<tr class=\"nicetablehov\">";
			echo "<td><a href=group.php?groupid=".$row['groupid']."><b class=\"al".$NATS->GroupAlertLevel($row['groupid'])."\">".$row['groupname']."</b></a></td>";
			echo "<td><a href=group.edit.php?groupid=".$row['groupid']."><img src=images/options/application.png border=0 title=\"".$NATS->Lang->Item("edit")."\"></a>";
			echo "&nbsp;";
			echo "<a href=group.action.php?action=delete&groupid=".$row['groupid']."><img src=images/options/action_delete.png border=0 title=\"".$NATS->Lang->Item("delete")."\"></a></td>";
			echo "<td>";

			if ($f==0) echo "<img src=images/arrows/off/arrow_top.png>";
			else
				{
				echo "<a href=group.action.php?groupid=".$row['groupid']."&action=move&dir=up>";
				echo "<img src=\"images/arrows/on/arrow_top.png\" border=0>";
				echo "</a>";
				}

			if ($f>=($l-1)) echo "<img src=images/arrows/off/arrow_down.png>";
			else
				{
				echo "<a href=group.action.php?groupid=".$row['groupid']."&action=move&dir=down>";
				echo "<img src=\"images/arrows/on/arrow_down.png\" border=0>";
				echo "</a>";
				}

			echo "</td>";
			$f++;

			echo "</tr>";
		}
	}
	echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
	echo "<form action=group.action.php method=post>";
	echo "<input type=hidden name=action value=create>";
	echo "<tr><td><input type=text size=20 name=groupname maxlength=120></td><td colspan=2><input type=submit value=\"".$NATS->Lang->Item("create.group")."\">";
	echo " ".hlink("Group:Create")."</td></tr></form>";
	echo "</table>";
	End_Round();
	}

else if ($mode=="views")
	{
	$t="<b class=\"subtitle\">".$NATS->Lang->Item("views")."</b> ".hlink("View",12);
	Start_Round($t,600);
	echo "<table class=\"nicetablehov\" width=100%>";
	// get views...
	$q="SELECT viewid,vtitle FROM fnview";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		echo "<tr class=\"nicetablehov\"><td>";
		echo "<a href=view.php?viewid=".$row['viewid'].">".$row['vtitle']."</a>";
		echo "</td><td>";
		echo "<a href=view.edit.php?viewid=".$row['viewid']."><img src=images/options/application.png border=0 title=\"".$NATS->Lang->Item("edit")."\"></a>";
		echo "&nbsp;";
		echo "<a href=view.edit.php?viewid=".$row['viewid']."&action=delete><img src=images/options/action_delete.png border=0 title=\"".$NATS->Lang->Item("delete")."\"></a>";
		echo "</td></tr>";
		}

	echo "<tr><td colspan=2>&nbsp;<br></td></tr>";
	echo "<form action=view.edit.php method=post><input type=hidden name=action value=create>";
	echo "<tr><td><input type=text name=vtitle size=20 maxlength=64></td><td><input type=submit value=\"".$NATS->Lang->Item("create.view")."\"> ";
	echo hlink("View:Create")."</td></tr></form>";
	echo "</table>";
	End_Round();

	echo "<br><br>";
	$t="<b class=\"subtitle\">".$NATS->Lang->Item("reports")." ".hlink("Report",12)."</b>";
	Start_Round($t,600);
	echo "<b><a href=report.php>".$NATS->Lang->Item("create.report")."</a></b> ".hlink("Report",12);
	echo "<br><br>";

	// reports in here
	$rq="SELECT reportid,reportname FROM fnreport";
	$rr=$NATS->DB->Query($rq);
	if ($NATS->DB->Num_Rows($rr)>0)
		{
		echo "<table class=\"nicetablehov\" width=100%>";
		while ($rep=$NATS->DB->Fetch_Array($rr))
			{
			echo "<tr class=\"nicetablehov\">";
			echo "<td align=left>";
			echo "<a href=report.php?reportid=".$rep['reportid'].">".$rep['reportname']."</a>";
			echo "</td><td align=right>";
			echo "<a href=report.php?mode=delete&reportid=".$rep['reportid'].">";
			echo "<img src=images/options/action_delete.png border=0 title=\"".$NATS->Lang->Item("delete").": ".$rep['reportname']."\">";
			echo "</a>";
			echo "&nbsp;&nbsp;";
			echo "</td></tr>";
			}
		echo "</table>";
		}

	End_Round();

	}

else if ($mode=="configsummary")
	{
	$scheds=array();
	$q="SELECT scheduleid,schedulename FROM fnschedule";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		$scheds[$row['scheduleid']]=$row['schedulename'];
		}
	$NATS->DB->Free($r);

	echo "<b class=\"subtitle\">".$NATS->Lang->Item("config.summary")."</b><br><br>";
	echo "<table width=100% border=1>";
	echo "<tr>";
	echo "<td><b>";
	echo $NATS->Lang->Item("nodeid");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("name");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("hostname");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("schedule");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("enabled");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("ping")." / ".$NATS->Lang->Item("required");;
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("interval");
	echo "</b></td>";
	echo "<td><b>";
	echo $NATS->Lang->Item("nodeside");
	echo "</b></td>";
	echo "</tr>";
	$q="SELECT * FROM fnnode ORDER BY weight ASC";
	$r=$NATS->DB->Query($q);
	while ($row=$NATS->DB->Fetch_Array($r))
	{
		if ($NATS->isUserAllowedNode($NATS_Session->username,$row['nodeid']))
		{
			echo "<tr><td>";
			echo $row['nodeid'];
			echo "</td><td>";
			echo $row['nodename'];
			echo "</td><td>";
			echo $row['hostname'];
			echo "</td><td>";
			if ($row['scheduleid']==0) $s="All Times";
			else if (isset($scheds[$row['scheduleid']])) $s=$scheds[$row['scheduleid']];
			else $s="UNKNOWN";
			echo $s;
			echo "</td><td>";
			echo dispyn($row['nodeenabled']);
			echo "</td><td>";
			echo dispyn($row['pingtest'])." / ".dispyn($row['pingfatal']);
			echo "</td><td>";
			echo $row['testinterval'];
			echo "</td><td>";
			echo dispyn($row['nsenabled']);
			echo "</td>";

			echo "</tr>";
		}
	}
	$NATS->DB->Free($r);
	echo "</table><br><br>";

	}

else
	{
	echo "Sorry - unknown mode for main.php";
	}


echo "<br><br>";

?>


<?php
if ($check_update)
{
	echo "<SCRIPT TYPE=\"text/javascript\">\n";
	//echo "alert('check');\n";
	echo "var url='//www.purplepixie.org/freenats/download.php?CheckVersion2=".$NATS->Version."';\n";
	echo "var us=document.createElement('script');\n";
	echo "us.type='text/javascript';\n";
	echo "us.src=url;\n";
	echo "us.async=true;\n";
	echo "var fs=document.getElementsByTagName('script')[0];\n";
	echo "fs.parentNode.insertBefore(us, fs);\n";
	echo "</SCRIPT>\n";
}
Screen_Footer();
/* old PhoneHome Ping Tracker - now in screen as a png
$t=$NATS->Cfg->Get("freenats.tracker");
if ( ($t!="") && ($t>0) )
	$NATS->PhoneHome();
*/
?>
