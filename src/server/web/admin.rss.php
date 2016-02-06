<?php
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

ob_start();
require("include.php");
$NATS->Start();
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<9) UL_Error($NATS->Lang->Item("admin.interface"));

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case "create":
		$id=$NATS->RSS->Create($_REQUEST['feedname']);
		$_REQUEST['edit']=1;
		$_REQUEST['id']=$id;
		break;
		
		case "update":
		$id=$_REQUEST['feedid'];
		$opts=array();
		$opts['feedname']=$_REQUEST['feedname'];
		$opts['feedkey']=$_REQUEST['feedkey'];
		$opts['feedtype']=$_REQUEST['feedtype'];
		if ($_REQUEST['feedtype']=="node") $opts['typeopt']=$_REQUEST['nodeid'];
		else if ($_REQUEST['feedtype']=="group") $opts['typeopt']=$_REQUEST['groupid'];
		$opts['feedrange']=$_REQUEST['feedrange'];
		if (isset($_REQUEST[$_REQUEST['feedrange']])) $opts['rangeopt']=$_REQUEST[$_REQUEST['feedrange']];
		
		$NATS->RSS->SaveFeed($id,$opts);
		break;
		
		case "delete":
		$NATS->RSS->Delete($_REQUEST['id']);
		break;
	}
}

Screen_Header($NATS->Lang->Item("rss.feed"),1,1,"","main","admin");

echo "<br><b class=\"subtitle\"><a href=admin.php>".$NATS->Lang->Item("system.settings")."</a> &gt; ".$NATS->Lang->Item("rss.feed")."</b><br><br>";

$types = $NATS->RSS->GetTypes();
$ranges = $NATS->RSS->GetRanges();

if (isset($_REQUEST['edit']))
{
	$nodes=$NATS->GetNodes();
	$groups=$NATS->GetGroups();
	
	$feed = $NATS->RSS->GetFeed($_REQUEST['id']);
	if (count($feed)>0)
	{
		echo "<FORM ACTION=\"admin.rss.php\">\n";
		echo "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"update\">\n";
		echo "<INPUT TYPE=\"hidden\" NAME=\"feedid\" VALUE=\"".$feed['feedid']."\">\n";
		echo "<H2>".$NATS->Lang->Item("edit").": ".$feed['feedname']."</H2>\n";
		echo "<TABLE BORDER=\"0\">\n";
		
		echo "<TR><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo $NATS->Lang->Item("rss.feed.name");
		echo "</TD><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo "<INPUT TYPE=\"TEXT\" SIZE=\"30\" NAME=\"feedname\" VALUE=\"".$feed['feedname']."\">";
		echo "</TD></TR>\n";
		
		echo "<TR><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo $NATS->Lang->Item("rss.feed.key");
		echo "</TD><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo "<INPUT TYPE=\"TEXT\" SIZE=\"45\" NAME=\"feedkey\" VALUE=\"".$feed['feedkey']."\">";
		echo "</TD></TR>\n";
		
		echo "<TR><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo $NATS->Lang->Item("rss.type");
		echo "</TD>\n";
		echo "<TD ALIGN=\"left\" VALIGN=\"top\">";
		foreach($types as $type => $desc)
		{
			if ($type==$feed['feedtype']) $s=" CHECKED";
			else $s="";
			echo "<INPUT TYPE=\"RADIO\" NAME=\"feedtype\" VALUE=\"".$type."\"".$s."> ".$desc." ";
			
			if ($type=="node")
			{
				echo "<SELECT NAME=\"nodeid\">\n";
				foreach($nodes as $node)
				{
					if ($node['nodeid']==$feed['typeopt']) $s=" SELECTED";
					else $s="";
					echo "<OPTION VALUE=\"".$node['nodeid']."\"".$s.">".$node['name']."</OPTION>\n";
				}
				echo "</SELECT>\n";
			}
			else if ($type=="group")
			{
				echo "<SELECT NAME=\"groupid\">\n";
				foreach($groups as $group)
				{
					if ($group['groupid']==$feed['typeopt']) $s=" SELECTED";
					else $s="";
					echo "<OPTION VALUE=\"".$group['groupid']."\"".$s.">".$group['groupname']."</OPTION>\n";
				}
				echo "</SELECT>\n";
			}
			
			echo "<BR />\n";
		}
		echo "</TD></TR>\n";
		
		echo "<TR><TD ALIGN=\"left\" VALIGN=\"top\">";
		echo $NATS->Lang->Item("rss.range");
		echo "</TD>\n";
		echo "<TD ALIGN=\"left\" VALIGN=\"top\">";
		foreach($ranges as $range => $desc)
		{
			if ($range==$feed['feedrange']) $s=" CHECKED";
			else $s="";
			echo "<INPUT TYPE=\"RADIO\" NAME=\"feedrange\" VALUE=\"".$range."\"".$s."> ".$desc." ";
			
			if ($range[0]=="x")
			{
			$var=$range;
			if ($range==$feed['feedrange']) $val=$feed['rangeopt'];
			else $val="";
			echo "<INPUT TYPE=\"TEXT\" SIZE=\"4\" NAME=\"".$var."\" VALUE=\"".$val."\">";
			}
			
			echo "<BR />\n";
		}
		echo "</TD></TR>\n";
		
		echo "<TR><TD>&nbsp;</TD><TD ALIGN=\"LEFT\">";
		echo "<INPUT TYPE=\"SUBMIT\" VALUE=\"".$NATS->Lang->Item("save.changes")."\">";
		echo "</TD></TR>\n";
		
		echo "</TABLE>\n";
		echo "</FORM>\n";
	}
	else
	{
		echo "<B>".$NATS->Lang->Item("rss.error")."</B>";
	}
	echo "<BR /><BR />";
}

echo "<H2>".$NATS->Lang->Item("rss.feeds")."</H2>\n";

$feeds = $NATS->RSS->GetFeeds();
if (count($feeds)<=0) echo "<B>".$NATS->Lang->Item("no.feeds")."</B><BR /><BR />\n";
else
{
	echo "<TABLE CLASS=\"nicetablehov\">\n";
	echo "<TR><TD><B>".$NATS->Lang->Item("rss.feed.name")."</B></TD>\n";
	echo "<TD><B>".$NATS->Lang->Item("options")."</B></TD>\n";
	echo "<TD><B>".$NATS->Lang->Item("feed.url")."</B></TD></TD>\n";
	foreach($feeds as $feed)
	{
		echo "<TR CLASS=\"nicetablehov\">\n";
		echo "<TD>".$feed['feedname']."</TD>\n";
		echo "<TD>";
		echo "<A HREF=\"admin.rss.php?edit=1&id=".$feed['feedid']."\">";
		echo "<IMG SRC=\"images/options/file.png\" BORDER=\"0\">";
		echo "</A>";
		echo "&nbsp;&nbsp;<A HREF=\"javascript:confirmGo('".$NATS->Lang->Item("delete")."?','admin.rss.php?action=delete&id=".$feed['feedid']."');\">";
		echo "<IMG SRC=\"images/options/action_delete.png\" BORDER=\"0\">";
		echo "</A>";
		echo "</TD>\n";
		$url=$NATS->RSS->GetURL($feed['feedid'],$feed['feedkey']);
		echo "<TD>";
		echo "<A HREF=\"".$url."\">".$url."</A>";
		echo "</TD></TR>\n";
	}
	echo "</TABLE>\n";
	echo "<BR /><BR />";
}
echo "<FORM ACTION=\"admin.rss.php\" METHOD=\"POST\">\n";
echo "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"create\">\n";
echo "<INPUT TYPE=\"TEXT\" NAME=\"feedname\" SIZE=\"20\"> ";
echo "<INPUT TYPE=\"SUBMIT\" VALUE=\"".$NATS->Lang->Item("feed.create")."\">\n";
echo "</FORM>";

	

Screen_Footer();
?>
