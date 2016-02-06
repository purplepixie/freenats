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
if (!$NATS_Session->Check($NATS->DB))
	{
	header("Location: ./?login_msg=Invalid+Or+Expired+Session");
	exit();
	}
if ($NATS_Session->userlevel<5) UL_Error($NATS->Lang->Item("edit.view"));

function chs($var,$def=0)
{
if (isset($_REQUEST[$var])) return ss($_REQUEST[$var]);
else return $def;
}


if (isset($_REQUEST['action']))
	{
	switch ($_REQUEST['action'])
		{
		case "create":
			$q="INSERT INTO fnview(vtitle,vclick,vstyle) VALUES(\"".ss($_REQUEST['vtitle'])."\",\"standard\",\"standard\")";
			$NATS->DB->Query($q);
			$_REQUEST['viewid']=$NATS->DB->Insert_Id();
			$_REQUEST['show_options']=1;
			$msg="Created View";
			break;
			
		case "delete":
			if (!isset($_REQUEST['confirmed']))
				{
				$back=urlencode("view.edit.php?action=delete&confirmed=1&viewid=".$_REQUEST['viewid']);
				$link="confirm.php?action=Delete+View&back=".$back;
				header("Location: ".$link);
				exit();
				}
			// otherwise delete
			$qi="DELETE FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid']);
			$NATS->DB->Query($qi);
			$idel=$NATS->DB->Affected_Rows();
			$q="DELETE FROM fnview WHERE viewid=".ss($_REQUEST['viewid']);
			$NATS->DB->Query($q);
			$vdel=$NATS->DB->Affected_Rows();
			$msg="Deleted ".$vdel." Views (".$idel." Items)";
			header("Location: main.php?message=".urlencode($msg));
			exit();
			break;
			
		case "save_edit":
			if (isset($_REQUEST['vpublic'])) $public=1;
			else $public=0;
			if (isset($_REQUEST['vcolon'])) $colon=1;
			else $colon=0;
			if (isset($_REQUEST['vdashes'])) $dashes=1;
			else $dashes=0;
			if (isset($_REQUEST['vtimeago'])) $ago=1;
			else $ago=0;
			if (!is_numeric($_REQUEST['vrefresh'])) $vref=0;
			else $vref=ss($_REQUEST['vrefresh']);
			$q="UPDATE fnview SET vtitle=\"".ss($_REQUEST['vtitle'])."\",vstyle=\"".ss($_REQUEST['vstyle'])."\",";
			$q.="vclick=\"".ss($_REQUEST['vclick'])."\",vpublic=".$public.",vlinkv=".ss($_REQUEST['vlinkv']).",vrefresh=".$vref;
			$q.=",vcolumns=".ss($_REQUEST['vcolumns']).",vdashes=".$dashes.",vcolon=".$colon.",vtimeago=".$ago;
			$q.=" WHERE viewid=".ss($_REQUEST['viewid']);
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows()<=0) $msg=$NATS->Lang->Item("save.ok");
			else $msg=$NATS->Lang->Item("save.failed");
			break;
			
		case "create_item":
			$wq="SELECT iweight FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." ORDER BY iweight DESC LIMIT 0,1";
			$wr=$NATS->DB->Query($wq);
			if ($row=$NATS->DB->Fetch_Array($wr))
				{
				$iweight=$row['iweight']+10;
				}
			else $iweight=10;
			$NATS->DB->Free($wr);
		
			if (isset($_REQUEST['iname'])) $iname=ss($_REQUEST['iname']);
			else $iname="";
			
			$itype=ss($_REQUEST['itype']);
			if ($itype=="node") $ioption=ss($_REQUEST['ioption_node']);
			else if($itype=="group") $ioption=ss($_REQUEST['ioption_group']);
			else if($itype=="testgraph") $ioption=ss($_REQUEST['ioption_testgraph']."/".$_REQUEST['ioption_testgraph_time']);
			else if($itype=="testdetail") 
				{
				$ioption=ss($_REQUEST['ioption_testdetail']);
				$iname=ss($_REQUEST['iname_testdetail']);
				}
			else if($itype=="title") $ioption=ss($_REQUEST['ioption_title']);
			else $ioption="";
			$q="INSERT INTO fnviewitem(viewid,itype,ioption,iweight,iname) VALUES(".ss($_REQUEST['viewid']).",";
			$q.="\"".$itype."\",\"".$ioption."\",".$iweight.",\"".$iname."\")";
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows()>0) $msg="Created Item";
			else $msg="Create Item Failed";
			break;
			
		case "del_item":
			$q="DELETE FROM fnviewitem WHERE viewitemid=".ss($_REQUEST['viewitemid']);
			$NATS->DB->Query($q);
			if ($NATS->DB->Affected_Rows()>0) $msg=$NATS->Lang->Item("delete.ok");
			else $msg=$NATS->Lang->Item("delete.fail");
			break;
			
		case "save_view_item":
			$icol=chs("icolour",0);
			$itxt=chs("itextstatus",0);
			$isize=chs("isize",0);
			$igraphic=chs("igraphic",0);
			$idetail=chs("idetail",0);
			$q="UPDATE fnviewitem SET ";
			$q.="icolour=".$icol.",";
			$q.="itextstatus=".$itxt.",";
			$q.="isize=".$isize.",";
			$q.="igraphic=".$igraphic.",";
			$q.="idetail=".$idetail." ";
			$q.="WHERE viewitemid=".ss($_REQUEST['viewitemid']);
			$NATS->DB->Query($q);
			//echo $q;
			if ($NATS->DB->Affected_Rows()<=0) $msg=$NATS->Lang->Item("save.failed");
			else $msg=$NATS->Lang->Item("save.ok");
			break;
		
		case "move_item":
			if (isset($_REQUEST['dir'])) $dir=$_REQUEST['dir'];
			else $dir="up";
			
			$mywq="SELECT iweight FROM fnviewitem WHERE viewitemid=".ss($_REQUEST['viewitemid']);
			$mywr=$NATS->DB->Query($mywq);
			$row=$NATS->DB->Fetch_Array($mywr);
			$myweight=$row['iweight'];
			$NATS->DB->Free($mywr);
			
			if ($dir=="up") // get the next lowest one down
				{
				$q="SELECT viewitemid,iweight FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." AND iweight<".$myweight." ";
				$q.="ORDER BY iweight DESC LIMIT 0,1";
				$r=$NATS->DB->Query($q);
				if ($row=$NATS->DB->Fetch_Array($r)) // found one to swap with
					{
					$uq="UPDATE fnviewitem SET iweight=".$myweight." WHERE viewitemid=".$row['viewitemid'];
					$NATS->DB->Query($uq);
					$uq="UPDATE fnviewitem SET iweight=".$row['iweight']." WHERE viewitemid=".ss($_REQUEST['viewitemid']);
					$NATS->DB->Query($uq);
					//$msg="Moved Item";
					}
				//else $msg="Nowhere to Move Item To";
				}
			else if ($dir=="down") // get the next highest
				{
				$q="SELECT viewitemid,iweight FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." AND iweight>".$myweight." ";
				$q.="ORDER BY iweight DESC LIMIT 0,1";
				$r=$NATS->DB->Query($q);
				if ($row=$NATS->DB->Fetch_Array($r)) // found one to swap with
					{
					$uq="UPDATE fnviewitem SET iweight=".$myweight." WHERE viewitemid=".$row['viewitemid'];
					$NATS->DB->Query($uq);
					$uq="UPDATE fnviewitem SET iweight=".$row['iweight']." WHERE viewitemid=".ss($_REQUEST['viewitemid']);
					$NATS->DB->Query($uq);
					//$msg="Moved Item";
					}
				//else $msg="Nowhere to Move Item To";
				}
				
			break;
			
		case "moveitembefore":
			// viewitemid newweight
			$q="UPDATE fnviewitem SET iweight=iweight+1 WHERE viewid=".ss($_REQUEST['viewid'])." AND iweight>=".ss($_REQUEST['newweight']);
			$NATS->DB->Query($q);
			$q="UPDATE fnviewitem SET iweight=".ss($_REQUEST['newweight'])." WHERE viewitemid=".ss($_REQUEST['viewitemid']);
			//$NATS-DB->Query($q);
			$NATS->DB->Query($q);
			break;
		
		default: $msg=$NATS->Lang->Item("unknown.action");
		}
	}

ob_end_flush();
Screen_Header($NATS->Lang->Item("edit.view"),1,1,"","main","views");
	

$q="SELECT * FROM fnview WHERE viewid=".ss($_REQUEST['viewid'])." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if (!$row=$NATS->DB->Fetch_Array($r))
	{
	echo $NATS->Lang->Item("no.such.view")."<br><br>";
	Screen_Footer();
	exit();
	}
	
$NATS->DB->Free($r);
if (isset($msg))
	{
	echo "<b>".$msg."</b><br><br>";
	$poplist[]=$msg;
	}
	
//echo "<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td align=left>";
echo "<b class=\"subtitle\">".$NATS->Lang->Item("editing.view").": <a href=view.php?viewid=".$_REQUEST['viewid'].">".$row['vtitle']."</a></b>";
//echo "</td><td align=right><b class=\"minortitle\"><a href=\"view.php?viewid=".$_REQUEST['viewid']."\" target=top>Preview View</a> / ";
//echo "<a href=view.link.php?viewid=".$_REQUEST['viewid'].">Link to View</a></b>";
//echo "</td></tr></table>";
echo " <b style=\"font-size: 13pt;\">[ <a href=\"view.php?viewid=".$_REQUEST['viewid']."\" target=top>".$NATS->Lang->Item("preview")."</a> | ";
echo "<a href=view.link.php?viewid=".$_REQUEST['viewid'].">".$NATS->Lang->Item("linking")."</a> ]</b>";
echo "<br>";

echo "<form action=view.edit.php method=post>";
echo "<div id=\"view_edit_options\">";
echo "<table class=\"nicetable\">";

echo "<input type=hidden name=action value=save_edit>";
echo "<input type=hidden name=viewid value=".$_REQUEST['viewid'].">";

echo "<tr><td align=left valign=top><b>View Title";
echo "</b></td><td align=left>";
echo "<input type=text name=vtitle size=30 maxlength=64 value=\"".$row['vtitle']."\">";
echo "</td></tr>";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";

echo "<tr><td><b>Public View</b></td>";
echo "<td>";
if ($row['vpublic']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=vpublic value=1".$s."> ".hlink("View:Public");
echo "</td></tr>";
echo "<tr><td colspan=2>&nbsp;<br></td></tr>";

echo "<tr><td align=left valign=top><b>Page Style</b></td>";
echo "<td>";
if ($row['vstyle']=="standard") $s=" checked";
else $s="";
echo "<b><input type=radio name=vstyle value=standard".$s."> Standard</b><br>";
echo "Standard full-page headers and footers with &quot;local&quot; page and style includes<br><br>";
if ($row['vstyle']=="mobile") $s=" checked";
else $s="";
echo "<b><input type=radio name=vstyle value=mobile".$s."> Mobile/Minimal</b><br>";
echo "Very minimal and absolute page style suitable for mobile browser and/or inline JS usage<br><br>";
if ($row['vstyle']=="plain") $s=" checked";
else $s="";
echo "<b><input type=radio name=vstyle value=plain".$s."> Plain</b><br>";
echo "Totally plain output<br><br>";

echo "</td></tr>";

echo "<tr><td align=left valign=top><b>Link Types</b></td>";
echo "<td>";
if ($row['vclick']=="standard") $s=" checked";
else $s="";
echo "<b><input type=radio name=vclick value=standard".$s."> Standard</b><br>";
echo "Standard <i>a href</i> links for same window/frame<br><br>";
if ($row['vclick']=="frametop") $s=" checked";
else $s="";
echo "<b><input type=radio name=vclick value=frametop".$s."> Same Window (Frame Top)</b><br>";
echo "<i>a href</i> link to the window/frame top<br><br>";
if ($row['vclick']=="newwindow") $s=" checked";
else $s="";
echo "<b><input type=radio name=vclick value=newwindow".$s."> New Window</b><br>";
echo "Open a New Window<br><br>";
if ($row['vclick']=="disabled") $s=" checked";
else $s="";
echo "<b><input type=radio name=vclick value=disabled".$s."> Disabled</b><br>";
echo "No links (disabled)<br><br>";
if ($row['vlinkv']==0) $s=""; else $s=" selected";
$lq="SELECT viewid,vtitle FROM fnview WHERE viewid!=".ss($_REQUEST['viewid']);
$lr=$NATS->DB->Query($lq);
echo "<b>Links to Another View: </b>";
echo "<select name=vlinkv>";
echo "<option value=0".$s.">No (Go to FreeNATS)</option>";
while ($lrow=$NATS->DB->Fetch_Array($lr))
	{
	if ($lrow['viewid']==$row['vlinkv']) $s=" selected";
	else $s="";
	echo "<option value=\"".$lrow['viewid']."\"".$s.">".$lrow['vtitle']."</option>";
	}
echo "</select> ".hlink("View:LinkAnotherView")."<br><br>";
$NATS->DB->Free($lr);
echo "</td></tr>";

echo "<tr><td><b>Colons (before text status)</b></td>";
echo "<td>";
if ($row['vcolon']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=vcolon value=1".$s."> ".hlink("View:Colons");
echo "</td></tr>";

echo "<tr><td><b>Dashes (before times)</b></td>";
echo "<td>";
if ($row['vdashes']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=vdashes value=1".$s."> ".hlink("View:Dashes");
echo "</td></tr>";

echo "<tr><td><b>Times use XX:XX ago</b></td>";
echo "<td>";
if ($row['vtimeago']==1) $s=" checked";
else $s="";
echo "<input type=checkbox name=vtimeago value=1".$s."> ".hlink("View:TimeAgo");
echo "</td></tr>";

echo "<tr><td><b>Columns";
echo "</b></td><td align=left>";
echo "<input type=text name=vcolumns size=4 maxlength=2 value=\"".$row['vcolumns']."\"> ".hlink("View:Columns");
echo "</td></tr>";
echo "<tr><td><b>Refresh";
echo "</b></td><td align=left>";
echo "<input type=text name=vrefresh size=6 maxlength=6 value=\"".$row['vrefresh']."\"> ".hlink("View:Refresh");
echo "</td></tr>";
echo "<tr><td><b>Save</b></td><td><input type=submit value=\"Save View Settings\"> ";
echo "<a href=view.edit.php?viewid=".$_REQUEST['viewid'].">Cancel / Abandon Changes</a>";
echo "</td></tr>";
echo "</table>";
echo "</div>";
echo "</form>";
echo "\n<script type=\"text/javascript\">\n";
echo "var editData=document.getElementById('view_edit_options').innerHTML;\n";
echo "function show_edit_options()\n";
echo "{\n";
echo "document.getElementById('view_edit_options').innerHTML=editData;\n";
echo "}\n";
if (!isset($_REQUEST['show_options']))
 echo "document.getElementById('view_edit_options').innerHTML=\"<b>[ <a href=\\\"javascript:show_edit_options()\\\">".$NATS->Lang->Item("expand.view.options")."</a> ]</b>\";\n";
echo "</script>\n";

// movement list
$movs=array();
$movc=0;
$q="SELECT itype,ioption,iweight FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." ORDER BY iweight ASC";
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	$movs[$movc]['name']=substr(ViewItemTxt($row['itype'],$row['ioption']),0,32)."...";
	$movs[$movc]['weight']=$row['iweight'];
	$movc++;
	}
$NATS->DB->Free($r);

//echo "<br><br>";
$q="SELECT * FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." ORDER BY iweight ASC";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)<=0) echo "<i>No view objects yet defined.</i><br><br>";
else $l=$NATS->DB->Num_Rows($r);
$f=0;

$pitem=0;
echo "<script type=\"text/javascript\">\n";
echo "var viewOptions=new Array();\n";
echo "function toggleOptionsTable(id)\n";
echo "{\n";
echo "if (document.getElementById('options_'+id).innerHTML=='') // write out\n";
echo "document.getElementById('options_'+id).innerHTML=viewOptions[id];\n";
echo "else\n";
echo " {\n";
echo " viewOptions[id]=document.getElementById('options_'+id).innerHTML; // save back\n";
echo "document.getElementById('options_'+id).innerHTML='';\n";
echo " }\n";
echo "}\n";
echo "</script>\n";


while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<table class=\"viewtable\" cellspacing=0 cellpadding=0><tr><td align=left valign=top>";
	echo "<a name=\"".$row['viewitemid']."\"></a>";

	echo "<b>";
	echo ViewItemTxt($row['itype'],$row['ioption']);
	echo "</b>";
	//echo " (<a href=\"javascript:toggleOptionsTable('".$pitem."')\">show/hide options</a>)";
	//echo "<br>";
	echo "<form action=view.edit.php#".$row['viewitemid']." method=post>";
	//echo "<table width=600 class=\"hidenicetable\" id=\"options_".$row['viewitemid']."\">";
	echo "<div id=\"options_".$pitem."\">";
	echo "<table width=600 class=\"nicetable\">";

	echo "<input type=hidden name=viewid value=".$_REQUEST['viewid'].">";
	echo "<input type=hidden name=action value=save_view_item>";
	echo "<input type=hidden name=viewitemid value=".$row['viewitemid'].">";
	echo "<tr><td align=left valign=top colspan=4>";
	echo "<tr><td width=25% align=right>";
	echo "Use Colour :";
	echo "</td><td align=left width=25%>";
	if ($row['icolour']==1) $s=" checked";
	else $s="";
	echo "<input type=checkbox name=icolour value=1".$s.">";
	echo " ".hlink("View:UseColour");
	echo "</td>";
	
	echo "<td align=right width=25%>";
	echo "Text Status :";
	echo "</td><td align=left width=25%>";
	if ($row['itextstatus']==1) $s=" checked";
	else $s="";
	echo "<input type=checkbox name=itextstatus value=1".$s.">";
	echo " ".hlink("View:TextStatus");
	echo "</td></tr>";
	
	echo "<tr><td align=right>";
	echo "Item Size :";
	echo "</td><td align=left>";
	echo "<select name=isize>";
	if ($row['isize']>0) echo "<option value=1 checked>Large</option>";
	else echo "<option value=0 checked>Small</option>";
	echo "<option value=1>";
	echo "Large</option>";
	echo "<option value=0>";
	echo "Small</option>";
	echo "</select>";
	echo "</td>";
	echo "<td align=right>";
	echo "Graphics :";
	echo "</td><td align=left>";
	echo "<select name=igraphic>";
	if ($row['igraphic']==2) echo "<option value=2 checked>Node/Group</option>";
	else if ($row['igraphic']==1) echo "<option value=1 checked>Status Light</option>";
	else echo "<option value=0 checked>No Graphic</option>";
	echo "<option value=2>";
	echo "Node/Group</option>";
	echo "<option value=1>";
	echo "Status Light</option>";
	echo "<option value=0>";
	echo "No Graphic</option>";
	echo "</select>";
	echo "</td></tr>";
	
	echo "<tr><td width=25% align=right>";
	echo "Show Detail :";
	echo "</td><td align=left width=25%>";
	if ($row['idetail']>0) $s=" checked";
	else $s="";
	echo "<input type=checkbox name=idetail value=1".$s.">";
	echo " ".hlink("View:ShowDetail");
	echo "</td>";
	
	echo "<td align=center colspan=2><input type=submit value=\"Save Changes to Item\"></td></tr>";
	
	echo "</table></div><table width=600 border=0>";
	echo "</form>";
	echo "<form action=view.edit.php method=post>";
	echo "<input type=hidden name=viewid value=".$_REQUEST['viewid'].">";
	echo "<input type=hidden name=viewitemid value=".$row['viewitemid'].">";
	echo "<input type=hidden name=action value=moveitembefore>";
	
	echo "<tr><td colspan=2 align=left>Move: ";
	
	if ($f==0) echo "<img src=images/arrows/off/arrow_top.png style=\"vertical-align: -3;\">";
	else 
		{
		echo "<a href=view.edit.php?viewid=".$_REQUEST['viewid']."&action=move_item&dir=up&viewitemid=".$row['viewitemid'].">";
		echo "<img src=\"images/arrows/on/arrow_top.png\"  style=\"vertical-align: -3;\" border=0>";
		echo "</a>";
		}
	
	if ($f>=($l-1)) echo "<img src=images/arrows/off/arrow_down.png style=\"vertical-align: -3;\">";
	else 
		{
		echo "<a href=view.edit.php?viewid=".$_REQUEST['viewid']."&action=move_item&dir=down&viewitemid=".$row['viewitemid'].">";
		echo "<img src=\"images/arrows/on/arrow_down.png\" border=0 style=\"vertical-align: -3;\">";
		echo "</a>";
		}
		
	$f++;
	
	echo "&nbsp;Before: <select name=newweight style=\"font-size: 8pt;\">";
	foreach($movs as $mov)
		echo "<option value=\"".$mov['weight']."\">".$mov['name']."</option>";
	echo "</select> <input type=submit value=Move style=\"font-size: 8pt;\">";
	
	echo "</td><td colspan=2 align=right>";
	echo "<a href=\"javascript:toggleOptionsTable('".$pitem."')\">item options</a>&nbsp;&nbsp;";
	echo "<a href=view.edit.php?viewid=".$_REQUEST['viewid']."&action=del_item&viewitemid=".$row['viewitemid'].">";
	echo "<img src=images/options/action_delete.png border=0 style=\"vertical-align: -4;\"></a>";
	echo "</td></tr>";
	echo "</form>";
	echo "</table>";
	echo "<script type=\"text/javascript\">\n";
	echo "viewOptions[".$pitem."]=document.getElementById('options_".$pitem."').innerHTML;\n";
	echo "document.getElementById('options_".$pitem."').innerHTML=\"\";\n";
	$pitem++;
	echo "</script>\n";
	echo "</td></tr></table>";
	//echo "<hr class=\"viewtable\">";
	echo "<hr align=\"left\" width=\"600\" style=\"height: 1px; color: #000000; background-color: #a0a0a0; border: none; margin: 0px;\" noshade>";
	}
	
$NATS->DB->Free($r);


echo "\n<script type=\"text/javascript\">\n";
echo "function view_radio_select( itemtype )\n";
echo "{\n";
echo "document.getElementById('newitem_'+itemtype).checked=true;\n";
echo "//\n";
echo "}\n";
echo "</script>\n";

echo "<br><br>";
echo "<table width=600 class=\"nicetable\">";
echo "<form action=view.edit.php method=post name=viewcreateform>";
echo "<input type=hidden name=viewid value=".$_REQUEST['viewid'].">";
echo "<input type=hidden name=action value=create_item>";
echo "<tr><td colspan=4><b>Create New Item</b></td></tr>";

echo "<tr><td width=25% align=center>";
echo "<input type=radio name=itype value=node checked id=\"newitem_node\">";
echo "</td><td colspan=3 align=left>";
echo "<b>Individual Node: </b>";
echo "<select name=ioption_node onchange=\"view_radio_select('node')\">";
$nq="SELECT nodeid,nodename FROM fnnode";
$nr=$NATS->DB->Query($nq);
while ($node=$NATS->DB->Fetch_Array($nr))
	{
	echo "<option value=".$node['nodeid'].">".$node['nodename']." (".$node['nodeid'].")</option>";
	}
$NATS->DB->Free($nr);
echo "</select>";
echo "</td></tr>";
/*
 id=\"newitem_node\"
 onchange=\"view_radio_select('node')\" 
*/
echo "<tr><td width=25% align=center>";
echo "<input type=radio name=itype value=group id=\"newitem_group\">";
echo "</td><td colspan=3 align=left>";
echo "<b>Individual Group: </b>";
echo "<select name=ioption_group onchange=\"view_radio_select('group')\">";
$nq="SELECT groupid,groupname FROM fngroup";
$nr=$NATS->DB->Query($nq);
while ($group=$NATS->DB->Fetch_Array($nr))
	{
	echo "<option value=".$group['groupid'].">".$group['groupname']." (".$group['groupid'].")</option>";
	}
$NATS->DB->Free($nr);
echo "</select>";
echo "</td></tr>";

$tests=array();
$tq="SELECT localtestid,nodeid,testname,testtype,testparam,testrecord FROM fnlocaltest ORDER BY nodeid";
$tr=$NATS->DB->Query($tq);
while ($test=$NATS->DB->Fetch_Array($tr))
	{
	$tid="L".$test['localtestid'];
	$tests[$tid]=$test;
	$tests[$tid]['testid']=$tid;
	}
$NATS->DB->Free($tr);

$tq="SELECT nstestid,nodeid,testname,testtype,testdesc,testrecord FROM fnnstest WHERE testenabled=1 ORDER BY nodeid";
$tr=$NATS->DB->Query($tq);
while ($test=$NATS->DB->Fetch_Array($tr))
	{
	$tid="N".$test['nstestid'];
	$tests[$tid]=$test;
	$tests[$tid]['testid']=$tid;
	$tests[$tid]['testparam']=$test['testdesc'];
	if ($test['testname']=="")
		{
		if ($test['testdesc']=="") $tests[$tid]['testname']=$test['testtype'];
		else $tests[$tid]['testname']=$test['testdesc'];
		}
	}
$NATS->DB->Free($tr);


echo "<tr><td align=center>";
echo "<input type=radio name=itype value=allnodes>";
echo "</td><td colspan=3 align=left><b>";
echo "List All (Enabled) Nodes";
echo "</b></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=allgroups>";
echo "</td><td colspan=3 align=left><b>";
echo "List All Groups";
echo "</b></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=alertnodes>";
echo "</td><td colspan=3 align=left><b>";
echo "List Alerting Nodes";
echo "</b></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=alertgroups>";
echo "</td><td colspan=3 align=left><b>";
echo "List Alerting Groups";
echo "</b></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=alerts>";
echo "</td><td colspan=3 align=left><b>";
echo "List All Current Alerts";
echo "</b></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=testdetail id=\"newitem_testdetail\">";
echo "</td><td colspan=3 align=left><b>";
echo "Detail for Test ";
echo "<select name=ioption_testdetail onchange=\"view_radio_select('testdetail')\">";
foreach($tests as $test)
	{
	echo "<option value=".$test['testid'].">";
	if ($test['testname']!="") $tn=$test['testname'];
	else 
		{
		$tn=lText($test['testtype']);
		if ($test['testparam']!="") $tn.=" (".$test['testparam'].")";
		}
	echo $test['nodeid']." &gt; ".$tn;
	echo "</option>";
	}
echo "</select>";
echo "</b><br>";
echo "Display Name: <input type=text name=iname_testdetail size=30 maxlength=64 onclick=\"view_radio_select('testdetail')\"></td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=testgraph id=\"newitem_testgraph\">";
echo "</td><td colspan=3 align=left><b>";
echo "Graph for Test </b>";
echo "<select name=ioption_testgraph onchange=\"view_radio_select('testgraph')\">";
foreach($tests as $test)
	{
	if (($test['testrecord']>0)||($test['testtype']=="ICMP"))
		{
		echo "<option value=".$test['testid'].">";
		if ($test['testname']!="") $tn=$test['testname'];
		else 
			{
			$tn=lText($test['testtype']);
			if ($test['testparam']!="") $tn.=" (".$test['testparam'].")";
			}
		echo $test['nodeid']." &gt; ".$tn;
		echo "</option>";
		}
	}
echo "</select><br>";
echo "Show Last <input type=text size=4 maxlength=4 name=ioption_testgraph_time value=24 onclick=\"view_radio_select('testgraph')\"> Hours";
echo "</td></tr>";

echo "<tr><td align=center>";
echo "<input type=radio name=itype value=title id=\"newitem_title\">";
echo "</td><td colspan=3 align=left><b>";
echo "Title: </b><input type=text name=ioption_title size=30 maxlength=120 onclick=\"view_radio_select('title')\">";
echo "</td></tr>";

echo "<tr><td>&nbsp;</td><td colspan=3>";
echo "<input type=submit value=\"Create New Item\"> ";
//echo "<select name=create_position><option value=end checked>At the End</option><option value=start>At the Top</option></select>";
echo "</td></tr>";
/* TODO - if ok don't be lazy and put the def options in here */

echo "</form></table>";
?>


<?php
Screen_Footer();
?>
