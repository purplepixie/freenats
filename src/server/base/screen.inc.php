<?php // screen.inc.php -- web page screen library
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

$menu=array();
$menu[0]="<a href=http://www.purplepixie.org/freenats/>".$NATS->Lang->Item("homepage")."</a>";
$menu[1]="<a href=monitor.php>".$NATS->Lang->Item("live.monitor")."</a>&nbsp; &nbsp;<a href=main.php>".$NATS->Lang->Item("configuration")."</a>&nbsp; &nbsp;";
$menu[1].="<a href=http://www.purplepixie.org/freenats/support.php target=top>".$NATS->Lang->Item("help")."</a>&nbsp; &nbsp;<a href=logout.php>".$NATS->Lang->Item("logout")."</a>";
$menu[2]=$menu[1]."&nbsp; &nbsp;<a href=admin.php>Admin</a>";

$menu[3]="<a href=iphone.php>".$NATS->Lang->Item("iphone")."</a>&nbsp;  &nbsp;<a href=http://www.purplepixie.org/freenats/>".$NATS->Lang->Item("homepage")."</a>";
/*
$pagemenu['main']="<a href=main.php?mode=overview>Overview</a>&nbsp; &nbsp;<a href=main.php?mode=nodes>Nodes</a>&nbsp; &nbsp;";
$pagemenu['main'].="<a href=main.php?mode=groups>Groups</a>&nbsp; &nbsp;<a href=main.php?mode=views>Views &amp; Reports</a>&nbsp; &nbsp;";
$pagemenu['main'].="<a href=pref.php>User Options</a>";
*/
$pagemenu=array();
$pagemenu['main']=array(
	array("overview","main.php?mode=overview",$NATS->Lang->Item("overview")),
	array("nodes","main.php?mode=nodes",$NATS->Lang->Item("nodes")),
	array("groups","main.php?mode=groups",$NATS->Lang->Item("groups")),
	array("views","main.php?mode=views",$NATS->Lang->Item("views.reports")),
	array("pref","pref.php?mode=pref",$NATS->Lang->Item("user.options")),
	array("admin","admin.php",$NATS->Lang->Item("system.settings")) );
	
function PageMenu($name,$mode="")
{
global $pagemenu;
if (($mode=="")&&isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
$out="";
$first=true;
foreach($pagemenu[$name] as $opt)
	{
	if ($first) $first=false;
	else $out.=" ";
	if ($mode!=$opt[0]) $out.="&nbsp;<a href=".$opt[1].">";
	else $out.="<b style=\"background-color: #ffffff;\">&nbsp;<a href=\"".$opt[1]."\" style=\"color: black; text-decoration: none;\">";
	$out.=$opt[2];
	if ($mode!=$opt[0]) $out.="</a>&nbsp; ";
	else $out.="</a>&nbsp;</b>";
	}
return $out;
}
	
$poplist=array();

function Screen_Header($title,$menuindex=0,$alertpane=0,$ah="",$pagemenu="",$pagemenumode="")
{
global $menu,$NATS,$NATS_Session;
if ($NATS->Cfg->Get("site.enable.interactive")!=1)
	{
	echo "Sorry but FreeNATS interactive is disabled.<br>";
	echo "<i>site.enable.interactive</i> != 1<br><br>";
	echo "To resolve this issue your system administrator needs to set the FreeNATS system variable site.enable.interactive to 1.<br><br>";
	$NATS->Stop();
	exit();
	}
if ($menuindex==1) $alertpane=1; // bodge

if ( ($menuindex==1) && ($pagemenu=="main") ) $secCheckFirstrun=true;
else $secCheckFirstrun=false; // KLUDGE

//if ($NATS_Session->userlevel>9) $menuindex=2; // further bodge!
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
echo "<html><head><title>FreeNATS: ".$title."</title>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/main.css\">\n";
/*
echo "<style type=\"text/css\">\n";
require("css/main.css");
echo "\n</style>\n";
*/
if ($ah!="") echo $ah;
echo "<script type=\"text/javascript\" src=\"js/freenats.js\"></script>\n";
echo "</head>\n";

echo "<body>";

echo "<table class=\"maintitle\" cellspacing=0 cellpadding=0>\n";
echo "<tr><td align=left valign=center class=\"mainleft\">\n";
echo "<b class=\"maintitle\">".$title."</b>";
if ($pagemenu!="") echo "<br>&nbsp;&nbsp;<b>".PageMenu($pagemenu,$pagemenumode)."</b>";
echo "</td>\n";
echo "<td class=\"titlelink\" align=right valign=center>\n";
echo $menu[$menuindex];
echo "&nbsp;</td></tr>\n";
//echo "<tr><td align=left valign=bottom><img src=images/e0e0ff.10px.bl.jpg></td>";
//echo "</tr>\n";
echo "</table>\n";

if ($secCheckFirstrun && ( $NATS->Cfg->Get("site.firstrun.ignore",0) != 1 ) )
	{
	if (file_exists("firstrun.php"))
		{
		echo "<div class=\"sec_warning\">";
		echo "<b>".$NATS->Lang->Item("sec.firstrun")."</b><br />";
		echo $NATS->Lang->Item("sec.firstrun.text")."<br />";
		echo $NATS->Lang->Item("sec.firstrun.ignore")."<BR />";
		echo "<a href=\"http://www.purplepixie.org/freenats/wiki/Firstrun_Warning\" target=\"top\">http://www.purplepixie.org/freenats/wiki/Firstrun_Warning</a>";
		
		echo "</div>";
		}
	}


if ($alertpane==1)
	{
	$alerts=$NATS->GetAlerts();
	if (is_array($alerts))
		{
		echo "<div class=\"alertpane\" id=\"fn_alertpane\">";
		echo "<b><u>".$NATS->Lang->Item("nats.alerts")."</u></b><br><br>";
		foreach($alerts as $alert)
			{
			echo "&nbsp;<a href=node.php?nodeid=".$alert['nodeid'].">";
			echo "<b class=\"al".$alert['alertlevel']."\">".$alert['nodeid']."</b></a><br>";
			}
		echo "<br>";
		echo "</div>";
		}
	}

}

function Start_Round($title,$width="")
{
if ($width!="") $w=" width=".$width;
else $w="";
echo "<table border=0".$w." cellspacing=0 cellpadding=0>\n";
echo "<tr><td valign=top align=left width=11 style=\"background-color: #e0e0ff;\"><img src=images/e0e0ff.10px.tl.jpg></td>\n";
echo "<td align=left valign=center style=\"background-color: #e0e0ff;\">\n";
echo $title;
echo "\n</td><td align=right valign=top style=\"background-color: #e0e0ff;\"><img src=images/e0e0ff.10px.tr.jpg></td></tr>\n";
echo "<tr><td colspan=3 style=\"border-left: solid 1px #e0e0ff; border-bottom: solid 1px #e0e0ff; border-right: solid 1px #e0e0ff; padding: 5px;\">\n";
}

function End_Round()
{
echo "\n</td></tr>\n";

echo "</table>";
}

function Screen_Footer($track_if_enabled=true)
{
global $NATS,$poplist;
echo "<br><br>\n";
//$NATS->Cfg->DumpToScreen();
echo "<div class=\"nfooter\">";
echo "<div class=\"bl\"><div class=\"br\"><div class=\"tl\"><div class=\"tr\">";

echo "<div align=\"left\" class=\"nfootleft\"><a href=http://www.purplepixie.org/freenats/>FreeNATS</a>; &copy; ".$NATS->Lang->Item("copyright")." 2008-2016 ";
echo "<a href=http://www.purplepixie.org/>PurplePixie Systems</a>";
echo "</div><div class=\"nfootright\">";
echo $NATS->Lang->Item("version").": ".$NATS->Version;
if ($NATS->Release!="") echo "/".$NATS->Release;
echo "&nbsp;&nbsp;</div>";

//echo "Hello";
echo "</div></div></div></div>";
echo "</div>";
if (ini_get("freenats.rpath")==1)
	{
	echo "<i>FreeNATS Virtual Server Powered By <a href=http://www.rpath.org/>rPath</a> LAMP Appliance</i><br>";
	}
//echo "<i>This is alpha-test software - we would very much value your ";
//echo "<a href=http://www.purplepixie.org/freenats/feedback.php>feedback</a></i><br>";

$autofeedback=false;
if ($track_if_enabled)
	{
	$t=$NATS->Cfg->Get("freenats.tracker");
	if ( ($t!="") && ($t>0) )
		{
		$autofeedback=true;
		$usid=$NATS->Cfg->Get("freenats.tracker.usid","");
		if ($usid=="")
			{ // generate usid if not already set
			// usid form XYZ-XYZ-XYZ...
			$allow="abcdef0123456789";
			$allow_len=strlen($allow);
			mt_srand(microtime()*1000000);
			$first_set=1;
			for ($a=0; $a<5; $a++) // blocks
				{
				if ($first_set==1) $first_set=0;
				else $usid.="-";
				for ($b=0; $b<10; $b++)
					{
					$c=mt_rand(0,$allow_len-1);
					$usid.=$allow[$c];
					}
				}
			$NATS->Cfg->Set("freenats.tracker.usid",$usid);
			}
			
		// get some more data
		$sn=explode("/",$_SERVER['SCRIPT_NAME']);
		$script=$sn[count($sn)-1];
		
		// show tracking image
		echo "<img src=\"http://www.purplepixie.org/freenats/report/ping.png.php?data=v=".$NATS->Version."+p=".$script."&type=ping&usid=".$usid."\" width=1 height=1>\n";
		}
	}

//$NATS->PageError("fish","swim in the sea");
//$NATS->PageError("dogs","walk on the land");
//$autofeedback=true;

if (count($NATS->PageErrors)>0) // page errors reported
	{
	echo "<div class=\"page_error\">\n";
	echo "Errors detected on page: ";
	$url="";
	$counter=0;
	foreach($NATS->PageErrors as $PageError)
		{
		if ($counter>0) $url.="&";
		$url.="code[".$counter."]=".urlencode($PageError['code'])."&desc[".$counter."]=".urlencode($PageError['desc']);
		$counter++;
		}
	$url.="&ver=".$NATS->Version;
		
	echo "<script type=\"text/javascript\">\n";
	echo "function report_error()\n";
	echo "{\n";
	echo "document.getElementById('error_report_result').innerHTML = 'Reporting Now...';\n";
	echo "var s = document.createElement(\"script\");\n";
	echo "s.src = \"http://www.purplepixie.org/freenats/report/error.php?".$url."\";\n";
	echo "document.body.appendChild(s);\n";
	echo "}\n";
	echo "</script>";
		
	echo "<span id=\"error_report_result\">";
	if ($autofeedback)
		{
		echo "Reporting...";
		}
	else
		{
		echo "<a href=\"javascript:report_error();\">Click to report to PurplePixie</a> | ";
		//echo "<a href=\"javascript:displayToggle('error_detail');\">Click to show/hide details</a>";
		}
	echo "</span>&nbsp; ";
	echo "<a href=\"javascript:displayToggle('error_detail');\">Click to show/hide details</a>";
	echo "<div id=\"error_detail\" style=\"display: none;\">\n";
	foreach($NATS->PageErrors as $PageError)
		{
		echo "# ".$PageError['code']." : ".$PageError['desc']."<BR />\n";
		}
	echo "</div>";
	echo "</div>\n";
	
	if ($autofeedback)
		{
		//echo "<script src=\"http://www.purplepixie.org/freenats/report/error.php?".$url."\" type=\"text/javascript\"></script>\n";
		echo "<script type=\"text/javascript\">\n";
		echo "report_error();\n";
		echo "</script>\n";
		}
		
	}
		
if ($NATS->Cfg->Get("site.popupmessage")=="1")
	{
	if (count($poplist)>0)
		{
		echo "\n<script type=\"text/javascript\">\n";
		
		foreach($poplist as $pop)
			{
			echo "alert('".$pop."');\n";
			}
			
		echo "</script>\n";
		}
	}
echo "\n</body></html>\n";
}

function UL_Error($task="")
{
global $NATS;
Screen_Header($NATS->Lang->Item("access.error"));
echo "<br>".$NATS->Lang->Item("access.error.detail")." (".$task.").<br><br>";
echo "<a href=main.php>".$NATS->Lang->Item("click.continue")."</a><br><br>";
Screen_Footer();
exit();
}

function Session_Error()
{
global $REQUEST_URI;
header("Location: ./?login_msg=Invalid+or+Expired+Session&url=".urlencode($REQUEST_URI));
exit();
}

function nicedt($ts)
{
global $NATS;
$form="H:i:s d/m/Y";
if (isset($NATS)) $form=$NATS->Cfg->Get("site.dtformat","H:i:s d/m/Y");
if ($ts<=0) return $NATS->Lang->Item("never");
return date($form,$ts);
}

function enicedt($ts)
{
echo nicedt($ts);
}

function nicediff($diff)
{
$hr=0;
$mn=0;
$se=0;
if ($diff>59)
	{
	$mn=round($diff/60,0);
	$se=$diff%60;
	if ($mn>59)
		{
		$hr=round($mn/60,0);
		$mn=$mn%60;
		}
	}
else $se=$diff;
$s="";
if ($hr<10) $s="0";
$s.=$hr.":";
if ($mn<10) $s.="0";
$s.=$mn.":";
if ($se<10) $s.="0";
$s.=$se;
return $s;
}

function dtago($ts,$sayago=true)
{
global $NATS;
if ($ts<=0) return $NATS->Lang->Item("never");
$now=time();
$diff=$now-$ts;
$s=nicediff($diff);
if ($sayago) $s.=" ".$NATS->Lang->Item("ago");
return $s;
}

function nicenextx($nextx)
{
if ($nextx<=0) return "Now";
$diff=$nextx-time();
if ($diff<0)
	{
	$sign=" ago";
	$diff=0-$diff;
	}
else $sign="";
return nicediff($diff).$sign;
}

function edtago($ts)
{
echo dtago($ts);
}

function smartx($x) // smart handling of unixtime x variables
{
if ($x==0) return time();	// 0 = now
else if ($x<0)				// -z = now - z seconds
	return (time()+$x);		// negative number so +
else						// positive number so is a unixtime
	return $x;
}

$allowed="00123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@_-.,:&/~%=+(){}[]#?$";

function nices($s)
{
global $allowed;
$o="";
for ($a=0; $a<strlen($s); $a++)
	{
	$c=$s[$a];
	if (strpos($allowed,$c)===false)
		{
		// skip it
		}
	else $o.=$c;
	}
return $o;
}
/* // Old Static List Function
function ShowIcons()
{
global $fnIcons,$fnIcon_DefNode,$fnIcon_DefGroup;
$c=0;
echo "<table border=0>";
$cc=0;
for ($a=0; $a<count($fnIcons); $a++)
	{
	if ($cc==0) echo "<tr>";
	echo "<td valign=top align=center><img src=icons/".$fnIcons[$a]."><br>".$fnIcons[$a]."<br>";
	if ($a==$fnIcon_DefNode) echo "<i>Node Default</i> ";
	if ($a==$fnIcon_DefGroup) echo "<i>Group Default</i>";
	echo "</td>";
	$cc++;
	if ($cc>=5)
		{
		echo "</tr>";
		$cc=0;
		}
	}
if ($cc>0) echo "</tr>";
echo "</table>";
}
*/

function GetIcons()
{
$iconFiles=glob("icons/{*.gif,*.GIF,*.jpg,*.JPG,*.jpeg,*.JPEG,*.png,*.PNG}", GLOB_BRACE);
for($a=0;$a<count($iconFiles);$a++)
	$iconFiles[$a]=substr($iconFiles[$a],6);
return $iconFiles;
}


function ShowIcons()
{
global $fnIcons,$fnIcon_DefNode,$fnIcon_DefGroup,$NATS;
$c=0;
echo "<table border=0>";
$cc=0;
$iconFiles=GetIcons();
for ($a=0; $a<count($iconFiles); $a++)
	{
	$icon=$iconFiles[$a];
	if ($cc==0) echo "<tr>";
	echo "<td valign=top align=center><img src=icons/".$icon."><br>".$icon."<br>";
	if ($icon==$fnIcons[$fnIcon_DefNode]) echo "<i>".$NATS->Lang->Item("node.default")."</i> ";
	if ($a==$fnIcons[$fnIcon_DefGroup]) echo "<i>".$NATS->Lang->Item("group.default")."</i>";
	echo "</td>";
	$cc++;
	if ($cc>=5)
		{
		echo "</tr>";
		$cc=0;
		}
	}
if ($cc>0) echo "</tr>";
echo "</table>";
}



function NodeIcon($nodeid)
{
global $NATS,$fnIcons,$fnIcon_DefNode;
$q="SELECT nodeicon FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
$r=$NATS->DB->Query($q);
if ($row=$NATS->DB->Fetch_Array($r)) 
	{
	if ($row['nodeicon']!="") return $row['nodeicon'];
	}
return $fnIcons[$fnIcon_DefNode];
}

function GroupIcon($groupid)
{
global $NATS,$fnIcons,$fnIcon_DefGroup;
$q="SELECT groupicon FROM fngroup WHERE groupid=\"".ss($groupid)."\"";
$r=$NATS->DB->Query($q);
if ($row=$NATS->DB->Fetch_Array($r)) 
	{
	if ($row['groupicon']!="") return $row['groupicon'];
	}
return $fnIcons[$fnIcon_DefGroup];
}

function np_tiny($nodeid,$text=true,$nodename="",$jslink=false)
{
global $NATS;
$al=$NATS->NodeAlertLevel($nodeid);
echo "<table class=\"nptiny-al".$al."\">";
echo "<tr><td valign=center align=center>";
if ($jslink) echo "<a href=\"javascript:nodeClick('".$nodeid."');\">";
else echo "<a href=node.php?nodeid=".$nodeid.">";
echo "<img src=\"icons/".NodeIcon($nodeid)."\" border=0>";
echo "</a>";
if ($text)
	{
	if ($nodename=="") $nodename=$nodeid;
	
	//$words=explode(" ",$nodename);
	
	// messy but there you go...
	
	$max_on_line=7;
	$max_lines=2;
	$len=strlen($nodename);
	$out="";
	$linecount=0;
	$charcount=0;
	for ($a=0; $a<$len; $a++)
		{
			
		$char=$nodename[$a];
		
		if ($char==" ")
			{
			$linecount++;
			$charcount=0;
			}
		else $charcount++;
		
		if ($charcount>=$max_on_line) 
			{
			$a=$len+10;
			$out.="..";
			}
		else if ($linecount>=$max_lines) 
			{
			$a=$len+10;
			$out.="..";
			}
		else $out.=$char;
		
		}
	//if ($a==($len+10)) $out.="..";
	$nodename=$out;
	
	$size=10;
		
/* -- size-based	
	$len=strlen($nodename);
	if ($len<9) $size=10;
	else if ($len<15) $size=8;
	else if ($len<20) $size=7;
	else
		{
		$size=6;
		$nodename=substr($nodename,0,18)."..";
		}
*/
	
	echo "<br><b class=\"al".$al."\" style=\"font-size: ".$size."pt;\">".$nodename."</b>";
	}
echo "</td></tr></table>";
}

function ng_tiny($groupid,$groupname="",$text=true)
{
global $NATS;
// to do - get groupname if not sent but F--- it for now
$al=$NATS->GroupAlertLevel($groupid);
echo "<table class=\"nptiny-al".$al."\">";
echo "<tr><td valign=center align=center>";
echo "<a href=group.php?groupid=".$groupid.">";
echo "<img src=\"icons/".GroupIcon($groupid)."\" border=0>";
echo "</a>";
if ($text)
	{
	echo "<br><b class=\"al".$al."\">".$groupname."</b>";
	}
echo "</td></tr></table>";
}

function ng_big($groupid,$groupname="",$groupdesc="",$groupicon="")
{
global $NATS;
if ($groupicon=="") $groupicon=GroupIcon($groupid);
$al=$NATS->GroupAlertLevel($groupid);
echo "<table class=\"npbig-al".$al."\">";
echo "<tr><td align=left valign=top>";
echo "<table class=\"nicetable\" width=300>";
echo "<tr><td align=right>".$NATS->Lang->Item("group.name")." :";
echo "</td><td align=left><a href=group.php?groupid=".$groupid.">".$groupname."</a></td></tr>";
echo "<tr><td align=right>".$NATS->Lang->Item("description")." :";
echo "</td><td align=left>".$groupdesc."</td></tr>";
echo "<tr><td align=right>".$NATS->Lang->Item("status")."</td><td align=left>";
echo "<b class=\"al".$al."\">".oText($al)."</b></td></tr>";
echo "</table></td>";
//echo "<td align=left valign=top align=right width=60>";
//echo "<img src=icons/".GroupIcon($groupid).">";
//echo "</td>";
echo "</tr>";
echo "</table>";
}

function np_big($nodeid,$nodename="",$nodedesc="",$nodeicon="",$jslink=false)
{
global $NATS;
if ($nodedesc=="") $nodedesc="&nbsp;";
if ($nodeicon=="") $nodeicon=NodeIcon($nodeid);
$al=$NATS->NodeAlertLevel($nodeid);
echo "<table class=\"npbig-al".$al."\">";
echo "<tr><td align=left valign=top>";
echo "<table class=\"nicetable\" width=300>";
echo "<tr><td align=right>".$NATS->Lang->Item("node.name")." :";
if ($nodename=="") $nodename=$nodeid;
echo "</td><td align=left>";
if ($jslink) echo "<a href=\"javascript:nodeClick('".$nodeid."');\">";
else echo "<a href=node.php?nodeid=".$nodeid.">";
echo $nodename."</a></td></tr>";
echo "<tr><td align=right>".$NATS->Lang->Item("description")." :";
echo "</td><td align=left>".$nodedesc."</td></tr>";
echo "<tr><td align=right>".$NATS->Lang->item("status")." :</td><td align=left>";
echo "<b class=\"al".$al."\">".oText($al)."</b></td></tr>";
echo "</table></td>";
//echo "<td align=left valign=top align=right width=60>";
//echo "<img src=icons/".GroupIcon($groupid).">";
//echo "</td>";
echo "</tr>";
echo "</table>";
}

function GetAbsolute($filename="")
{ // sooooooooo messy but looks like the ONLY FRICKIN' WAY!
if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']!="")) $uri="https://";
else $uri="http://";
$uri.=$_SERVER['HTTP_HOST'];
$uri.=$_SERVER['REQUEST_URI'];
$pos=strripos($uri,"/");
$rdir=substr($uri,0,$pos+1);
return $rdir.$filename;
}


?>
