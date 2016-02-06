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
$session=true;
if (!$NATS_Session->Check($NATS->DB))
	{
	$session=false;
	}

$abs=GetAbsolute();
	
if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else $mode="";
	
$items=array(); // items
$ob=array(); // output buffer

function show_output()
{
global $ob,$view,$mode;
foreach($ob as $oline)
	{
	echo $oline."<br>";
	}
ob_end_flush();
exit();
}

$q="SELECT * FROM fnview WHERE viewid=".ss($_REQUEST['viewid']);
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)<=0)
	{
	$ob[]="Invalid View ".$_REQUEST['viewid'];
	show_output();
	}
$view=$NATS->DB->Fetch_Array($r);
if ($view['vpublic']!=1) // requires auth
	{
	if (!$session)
		{
		$ob[]="Sorry not a public view";
		show_output();
		}
	}

if ($view['vcolon']>0) $colon=":";
else $colon="";
if ($view['vdashes']>0) $dash=" -";
else $dash="";
if ($view['vtimeago']>0) $ago=true;
else $ago=false;

function vdt($dt)
{
global $ago;
if ($ago) return dtago($dt);
return nicedt($dt);
}
	
function ViewNode($nodeid,$detail=true)
{
global $NATS;
$ret=array();
$q="SELECT * FROM fnnode WHERE nodeid=\"".ss($nodeid)."\"";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)<=0) return $ret;
$row=$NATS->DB->Fetch_Array($r);
if ($row['nodename']!="") $ret['name']=$row['nodename'];
else $ret['name']=$nodeid;
$ret['item']=$ret['name'];
$ret['nodeid']=$nodeid;
$ret['status']=$row['alertlevel'];
$ret['link']="node.php?nodeid=".$nodeid;
$NATS->DB->Free($r);

$ret['detail']=array();

if ($detail)
{
// get detail
$q="SELECT testtype,testparam,testname,alertlevel FROM fnlocaltest WHERE nodeid=\"".ss($nodeid)."\" ORDER BY alertlevel DESC";
$r=$NATS->DB->Query($q);
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
	{
	$an=$row['testtype'];
	if ($row['testparam']!="") $an.=" (".$row['testparam'].")";
	if ($row['testname']=="") $nn=$an; // textify!
	else $nn=$row['testname'];
	$ret['detail'][$a]['item']=$nn;
	$ret['detail'][$a]['status']=$row['alertlevel'];
	$ret['detail'][$a]['link']=$ret['link'];
	$a++;
	}
$NATS->DB->Free($r);
// get detail
$q="SELECT testtype,testdesc,testname,alertlevel FROM fnnstest WHERE nodeid=\"".ss($nodeid)."\" AND testenabled=1 ORDER BY alertlevel DESC";
$r=$NATS->DB->Query($q);
$a=0;
while ($row=$NATS->DB->Fetch_Array($r))
	{
	if ($row['testname']!="") $nn=$row['testname'];
	else if ($row['testdesc']!="") $nn=$row['testdesc'];
	else $nn=$row['testtype'];
	$ret['detail'][$a]['item']=$nn;
	$ret['detail'][$a]['status']=$row['alertlevel'];
	$ret['detail'][$a]['link']=$ret['link'];
	$a++;
	}
$NATS->DB->Free($r);
}
return $ret;
}

function ViewGroup($groupid,$detail=true)
{
global $NATS;
$ret=array();
// Get Group Info
$ret['status']=$NATS->GroupAlertLevel($groupid);
$gq="SELECT * FROM fngroup WHERE groupid=".ss($groupid)." LIMIT 0,1";
$gr=$NATS->DB->Query($gq);
$grow=$NATS->DB->Fetch_Array($gr);
$NATS->DB->Free($gr);
$ret['name']=$grow['groupname'];
$ret['desc']=$grow['groupdesc'];
$ret['icon']=$grow['groupicon'];
$ret['link']="group.php?groupid=".$groupid;

// Node Detail
if ($detail)
	{
	$ret['detail']=array();
	$a=0;
	$lq="SELECT nodeid FROM fngrouplink WHERE groupid=".ss($groupid);
	$lr=$NATS->DB->Query($lq);
	while ($link=$NATS->DB->Fetch_Array($lr))
		{
		$nq="SELECT nodename,alertlevel FROM fnnode WHERE nodeid=\"".$link['nodeid']."\" AND nodeenabled=1 ORDER BY alertlevel DESC, weight ASC";
		//$nq="SELECT nodename,alertlevel FROM fnnode";
		$nq.=" LIMIT 0,1";
		$nr=$NATS->DB->Query($nq);
		$node=$NATS->DB->Fetch_Array($nr);
		$ret['detail'][$a]['nodeid']=$link['nodeid'];
		$ret['detail'][$a]['nodename']=$node['nodename'];
		$ret['detail'][$a]['status']=$node['alertlevel'];
		$ret['detail'][$a]['link']="node.php?nodeid=".$link['nodeid'];
		$a++;
		$NATS->DB->Free($nr);
		}
	$NATS->DB->Free($lr);
	}
	
return $ret;
}
	
$q="SELECT * FROM fnviewitem WHERE viewid=".ss($_REQUEST['viewid'])." ORDER BY iweight ASC";
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	$id=$row['viewitemid'];
	$items[$id]=$row;
	// get name (varying), status and detail dependent...
	switch ($row['itype'])
		{
		case "node": 
			$items[$id]['data']=ViewNode($row['ioption']);
			break;
			
		case "allnodes": case "alertnodes":
			$items[$id]['detail']=array();
			if ($row['itype']=="allnodes") $q="SELECT nodeid FROM fnnode WHERE nodeenabled=1 ORDER BY `weight` ASC";
			else if ($row['itype']=="alertnodes") $q="SELECT nodeid FROM fnnode WHERE nodeenabled=1 AND alertlevel>0 ORDER BY `weight` ASC";
			$ret=$NATS->DB->Query($q);
			//echo "!".$q."<br>";
			$a=0;
			while ($noderow=$NATS->DB->Fetch_Array($ret))
				{
				if ($row['idetail']==1) $det=true;
				else $det=false;
				$items[$id]['detail'][$a]['data']=ViewNode($noderow['nodeid'],$det);
				$items[$id]['detail'][$a]['icolour']=$row['icolour'];
				$items[$id]['detail'][$a]['idetail']=$row['idetail'];
				$items[$id]['detail'][$a]['igraphic']=$row['igraphic'];
				$items[$id]['detail'][$a++]['itextstatus']=$row['itextstatus'];
				}
			$NATS->DB->Free($ret);
			break;
			
		case "allgroups": case "alertgroups":
			$items[$id]['detail']=array();
			$q="SELECT groupid FROM fngroup ORDER BY weight ASC";
			$ret=$NATS->DB->Query($q);
			$a=0;
			while ($grouprow=$NATS->DB->Fetch_Array($ret))
				{
				if ( ($row['itype']=="allgroups") || ($NATS->GroupAlertLevel($grouprow['groupid'])>0) )
					{
					$items[$id]['detail'][$a]['data']=ViewGroup($grouprow['groupid'],$row['idetail']);
					$items[$id]['detail'][$a]['icolour']=$row['icolour'];
					$items[$id]['detail'][$a]['ioption']=$grouprow['groupid'];
					$items[$id]['detail'][$a]['idetail']=$row['idetail'];
					$items[$id]['detail'][$a]['igraphic']=$row['igraphic'];
					$items[$id]['detail'][$a++]['itextstatus']=$row['itextstatus'];
					}	
				}
			$NATS->DB->Free($ret);
			break;
			
			case "group":
				$items[$id]['data']=ViewGroup($row['ioption'],$row['idetail']);
				break;
			
			case "testgraph":
				// can't be arsed to do link here
				break;
				
			case "alerts":
				$c=0;
				$alev=0;
				$items[$id]['detail']=array();
				$alq="SELECT nodeid,openedx,alertlevel FROM fnalert WHERE closedx=0";
				$alr=$NATS->DB->Query($alq);
				while ($al=$NATS->DB->Fetch_Array($alr))
					{
					$items[$id]['detail'][$c]['nodeid']=$al['nodeid'];
					$items[$id]['detail'][$c]['link']="node.php?nodeid=".$al['nodeid'];
					if ($al['alertlevel']>$alev) $alev=$al['alertlevel'];
					$items[$id]['detail'][$c]['status']=$al['alertlevel'];
					$c++;
					}
				$items[$id]['data']['status']=$alev;
				$items[$id]['data']['alerts']=$c;
				$NATS->DB->Free($alr);
				break;
				
			case "testdetail":
				// localtest only thus far
				$tclass=$row['ioption'][0];
				//$tid=substr($row['ioption'],1,128);
				
				/*
				$tquery="SELECT * FROM fnlocaltest WHERE localtestid=\"".ss($tid)."\" LIMIT 0,1";
				$tres=$NATS->DB->Query($tquery);
				if ($trow=$NATS->DB->Fetch_Array($tres))
					{
					$items[$id]['status']=$trow['alertlevel'];
					$items[$id]['lastrunx']=$trow['lastrunx'];
					$items[$id]['dtago']=dtago($trow['lastrunx']);
					$items[$id]['vtime']=vdt($trow['lastrunx']);
					$items[$id]['nodeid']=$trow['nodeid'];
					}
				$NATS->DB->Free($tres);
				*/
				
				$test=$NATS->GetTest($row['ioption']);
				if ($test!==false)
					{
					$items[$id]['status']=$test['alertlevel'];
					$items[$id]['lastrunx']=$test['lastrunx'];
					$items[$id]['dtago']=dtago($test['lastrunx']);
					$items[$id]['vtime']=vdt($test['lastrunx']);
					$items[$id]['nodeid']=$test['nodeid'];
					}
				break;
				
		}
		
	}

// begin the buffering of output...

// title and header
if ($view['vstyle']=="plain")
	{
	$ob[]="<html><head>";
	$ob[]="<style type=\"text/css\">";
	$fp=@fopen("css/mini.css","r");
	if ($fp)
		{
		while(!@feof($fp))
			$ob[]=@fgets($fp,1024);
		}
	@fclose($fp);
	$ob[]="</style>";
	$ob[]="</head><body>";
	}
else if ($view['vstyle']=="mobile")
	{
	$ob[]="<html><head>";
	if ($view['vrefresh']>0) $ob[]="<meta http-equiv=\"refresh\" content=\"".$view['vrefresh']."\">";
	$ob[]="<style type=\"text/css\">";
	$fp=@fopen("css/mobile.css","r");
	if ($fp)
		{
		while(!@feof($fp))
			$ob[]=@fgets($fp,1024);
		}
	@fclose($fp);
	$ob[]="</style>";
	$ob[]="</head><body>";
	}
else // standard and catch-all
	{
	$ob[]="<html><head>";
	if ($view['vrefresh']>0) $ob[]="<meta http-equiv=\"refresh\" content=\"".$view['vrefresh']."\">";
	$ob[]="<title>FreeNATS: ".$view['vtitle']."</title>";
	$ob[]="<style type=\"text/css\">";
	$fp=@fopen("css/main.css","r");
	if ($fp>0)
		{
		while(!@feof($fp))
			$ob[]=@fgets($fp,1024);
		}
	@fclose($fp);
	$ob[]="</style>";
	$ob[]="</head><body>";
	$ob[]="<table width=100% class=\"maintitle\">";
	$ob[]="<tr><td align=left valign=center>";
	$ob[]="<b class=\"maintitle\">FreeNATS: ".$view['vtitle']."</b></td>";
	$ob[]="</tr></table>";
	$ob[]="<br>";
	}

// now the items

function small_node($item)
{
global $abs,$view,$colon,$dashes;
$ob=array(); // our local copy
$uri=$abs.$item['data']['link'];
if ($view['vlinkv']!=0)
	{
	$uri=$abs."view.php?viewid=".$view['vlinkv'];
	}
$link=$view['vclick'];
if ($link=="disabled") $l="<a href=#>";
else if ($link=="standard") $l="<a href=\"".$uri."\">";
else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
else $l="<a href=#>";
			
// alert lights only as no fancy full-on tables etc yet
if ($item['igraphic']>0) // actually therefore should only be ==1 with 2 being the "proper" one
	{
	$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
	$ob[]=$is;
	}
			
$ob[]=$l;
			
if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
			
$out=$item['data']['name'];
if ($item['itextstatus']==1) $out.=$colon." ".oText($item['data']['status']);
$ob[]=$out."</a>";
	
if ($item['icolour']==1) $ob[]="</b>";

// detail like tests etc...
if ($item['idetail']>0)
	{
	$a=0;
	foreach($item['data']['detail'] as $dline)
		{
		$a++;
		$ob[]="<br>&nbsp;-&nbsp;";
		if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
		$out=$dline['item'];
		if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
		$ob[]=$out;
		if ($item['icolour']==1) $ob[]="</b>";
		}
	//if ($a>0) $ob[]="<br>";
	}

return $ob;
}

function large_node($item)
{
global $abs,$view,$colon,$dashes;
$ob=array(); // our local copy
$uri=$abs.$item['data']['link'];
if ($view['vlinkv']!=0)
	{
	$uri=$abs."view.php?viewid=".$view['vlinkv'];
	}
$link=$view['vclick'];
if ($link=="disabled") $l="<a href=#>";
else if ($link=="standard") $l="<a href=\"".$uri."\">";
else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
else $l="<a href=#>";

if ($item['icolour']==1) $col=true;
else $col=false;

if ($col)
	{
	switch($item['data']['status'])
		{
		case -1: $c="#a0a0a0"; break;
		case 0: $c="green"; break;
		case 1: $c="orange"; break;
		case 2: $c="red"; break;
		default: $c="black"; break;
		}
	}
else $c="#a0a0a0";			
$ss="width: 250; border: dotted 1px ".$c.";";

$ob[]="<table style=\"".$ss."\">";

if ($item['igraphic']==1)
	{
	$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
	}			
else if ($item['igraphic']>0)
	{
	$is="<img src=\"".$abs."icons/".NodeIcon($item['data']['nodeid'])."\">&nbsp;";
	}
else $is="&nbsp;";

$ob[]="<tr><td align=left valign=center>";
$ob[]=$l;
if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
			
$out=$item['data']['name'];
if ($item['itextstatus']==1) $out.=$colon." ".oText($item['data']['status']);
$ob[]=$out."</a>";
	
if ($item['icolour']==1) $ob[]="</b>";
$ob[]="</td><td align=right valign=center>";
$ob[]=$is;
$ob[]="</td></tr>";
// detail like tests etc...
if ($item['idetail']>0)
	{
	$ob[]="<tr><td colspan=2 align=left valign=top>";
	$a=0;
	foreach($item['data']['detail'] as $dline)
		{
		$a++;
		$ob[]="&nbsp;-&nbsp;";
		if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
		$out=$dline['item'];
		if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
		$ob[]=$out;
		if ($item['icolour']==1) $ob[]="</b>";
		$ob[]="<br>";
		}
	$ob[]="</td></tr>";
	//if ($a>0) $ob[]="<br>";
	}

	
$ob[]="</table>";
return $ob;
}

function large_group($item)
	{
	global $abs,$view,$colon,$dashes;
	$ob=array(); // our local copy
	$uri=$abs.$item['data']['link'];
	if ($view['vlinkv']!=0)
		{
		$uri=$abs."view.php?viewid=".$view['vlinkv'];
		}
	$link=$view['vclick'];
	if ($link=="disabled") $l="<a href=#>";
	else if ($link=="standard") $l="<a href=\"".$uri."\">";
	else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
	else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
	else $l="<a href=#>";
	
	if ($item['icolour']==1) $col=true;
	else $col=false;
	
	if ($col)
		{
		switch($item['data']['status'])
			{
			case -1: $c="#a0a0a0"; break;
			case 0: $c="green"; break;
			case 1: $c="orange"; break;
			case 2: $c="red"; break;
			default: $c="black"; break;
			}
		}
	else $c="#a0a0a0";			
	$ss="width: 250; border: dotted 1px ".$c.";";
	
	$ob[]="<table style=\"".$ss."\">";
	
	if ($item['igraphic']==1)
		{
		$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
		}			
	else if ($item['igraphic']>0)
		{
		$is="<img src=\"".$abs."icons/".GroupIcon($item['ioption'])."\">&nbsp;";
		}
	else $is="&nbsp;";
	
	$ob[]="<tr><td align=left valign=center>";
	$ob[]=$l;
	if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
				
	$out=$item['data']['name'];
	if ($item['itextstatus']==1) $out.=$colon." ".oText($item['data']['status']);
	$ob[]=$out."</a>";
		
	if ($item['icolour']==1) $ob[]="</b>";
	$ob[]="</td><td align=right valign=center>";
	$ob[]=$is;
	$ob[]="</td></tr>";
	
	if ($item['data']['desc']!="") $ob[]="<tr><td colspan=2><i>".$item['data']['desc']."</i></td></tr>";
	
	// detail like tests etc...
	if ($item['idetail']>0)
		{
		$ob[]="<tr><td colspan=2 align=left valign=top>";
		$a=0;
		foreach($item['data']['detail'] as $dline)
			{
			$a++;
			$ob[]="&nbsp;-&nbsp;";
			
			$uri=$abs.$item['data']['link'];
			if ($view['vlinkv']!=0)
				{
				$uri=$abs."view.php?viewid=".$view['vlinkv'];
				}
			$link=$view['vclick'];
			if ($link=="disabled") $l="<a href=#>";
			else if ($link=="standard") $l="<a href=\"".$uri."\">";
			else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
			else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
			else $l="<a href=#>";
			$ob[]=$l;
			if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
			if ($dline['nodename']!="") $out=$dline['nodename'];
			else $out=$dline['nodeid'];
			if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
			$ob[]=$out."</a>";
			if ($item['icolour']==1) $ob[]="</b>";
			$ob[]="<br>";
			}
		$ob[]="</td></tr>";
		//if ($a>0) $ob[]="<br>";
		}
	
		
	$ob[]="</table>";
	return $ob;
}

function small_group($item)
	{
	global $abs,$view,$colon,$dashes;
	$ob=array(); // our local copy
	$uri=$abs.$item['data']['link'];
	if ($view['vlinkv']!=0)
		{
		$uri=$abs."view.php?viewid=".$view['vlinkv'];
		}
	$link=$view['vclick'];
	if ($link=="disabled") $l="<a href=#>";
	else if ($link=="standard") $l="<a href=\"".$uri."\">";
	else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
	else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
	else $l="<a href=#>";
				
	// alert lights only as no fancy full-on tables etc yet
	if ($item['igraphic']>0) // actually therefore should only be ==1 with 2 being the "proper" one
		{
		$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
		$ob[]=$is;
		}
				
	$ob[]=$l;
				
	if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
				
	$out=$item['data']['name'];
	if ($item['itextstatus']==1) $out.=$colon." ".oText($item['data']['status']);
	$ob[]=$out."</a>";
		
	if ($item['icolour']==1) $ob[]="</b>";
	
	// detail like tests etc...
	if ($item['idetail']>0)
		{
		$a=0;
		foreach($item['data']['detail'] as $dline)
			{
			$a++;
			$uri=$abs.$item['data']['link'];
			if ($view['vlinkv']!=0)
				{
				$uri=$abs."view.php?viewid=".$view['vlinkv'];
				}
			$link=$view['vclick'];
			if ($link=="disabled") $l="<a href=#>";
			else if ($link=="standard") $l="<a href=\"".$uri."\">";
			else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
			else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
			else $l="<a href=#>";
			$ob[]="<br>&nbsp;-&nbsp;";
			$ob[]=$l;
			if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
			if ($dline['nodename']=="") $out=$dline['nodeid'];
			else $out=$dline['nodename'];
			if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
			$ob[]=$out."</a>";
			if ($item['icolour']==1) $ob[]="</b>";
			}
		//if ($a>0) $ob[]="<br>";
		}
	
	return $ob;
	}

function small_alerts($item)
	{
	global $abs,$view,$colon,$dashes;
	$ob=array(); // our local copy
	$uri=$abs."monitor.php";
	if ($view['vlinkv']!=0)
		{
		$uri=$abs."view.php?viewid=".$view['vlinkv'];
		}
	$link=$view['vclick'];
	if ($link=="disabled") $l="<a href=#>";
	else if ($link=="standard") $l="<a href=\"".$uri."\">";
	else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
	else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
	else $l="<a href=#>";
				
	// alert lights only as no fancy full-on tables etc yet
	if ($item['igraphic']>0) // actually therefore should only be ==1 with 2 being the "proper" one
		{
		$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
		//$ob[]=$is;
		}
				
	$ob[]=$l;
				
	if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
				
	if ($item['data']['alerts']==0) $out="No System Alerts";
	else $out="System Alerts (".$item['data']['alerts'].")";
	$ob[]=$out."</a>";
		
	if ($item['icolour']==1) $ob[]="</b>";
	
	// detail like tests etc...
	if ($item['idetail']>0)
		{
		$a=0;
		if ($item['data']['alerts']==0)
			{
			$ob[]="<br>&nbsp;-&nbsp;<i>There are no alerts</i>";
			}
		else
			{
			foreach($item['detail'] as $dline)
				{
				$a++;
				$ob[]="<br>&nbsp;-&nbsp;";
				$uri=$abs.$dline['link'];
				if ($view['vlinkv']!=0)
					{
					$uri=$abs."view.php?viewid=".$view['vlinkv'];
					}
				$link=$view['vclick'];
				if ($link=="disabled") $l="<a href=#>";
				else if ($link=="standard") $l="<a href=\"".$uri."\">";
				else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
				else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
				else $l="<a href=#>";
				$ob[]=$l;	

				if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
				$out=$dline['nodeid'];
				if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
				$ob[]=$out."</a>";
				if ($item['icolour']==1) $ob[]="</b>";
				}
			//if ($a>0) $ob[]="<br>";
			}
		}
	$ob[]="<br>";
	
	return $ob;
}


function large_alerts($item)
	{
	global $abs,$view,$colon,$dashes;
	$ob=array(); // our local copy
	$uri=$abs."monitor.php";
	if ($view['vlinkv']!=0)
		{
		$uri=$abs."view.php?viewid=".$view['vlinkv'];
		}
	$link=$view['vclick'];
	if ($link=="disabled") $l="<a href=#>";
	else if ($link=="standard") $l="<a href=\"".$uri."\">";
	else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
	else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
	else $l="<a href=#>";

	if ($item['icolour']==1) $col=true;
	else $col=false;
	
	if ($col)
		{
		switch($item['data']['status'])
			{
			case -1: $c="#a0a0a0"; break;
			case 0: $c="green"; break;
			case 1: $c="orange"; break;
			case 2: $c="red"; break;
			default: $c="black"; break;
			}
		}
	else $c="#a0a0a0";			
	$ss="width: 250; border: dotted 1px ".$c.";";
	
	$ob[]="<table style=\"".$ss."\">";
	


	// alert lights only as no fancy full-on tables etc yet
	if ($item['igraphic']>0) // actually therefore should only be ==1 with 2 being the "proper" one
		{
		$is="<img src=\"".$abs."images/lights/a".$item['data']['status'].".png\">&nbsp;";
		//$ob[]=$is;
		}
	else $is="&nbsp;";		
	
	$ob[]="<tr><td align=left valign=center>";
				
	if ($item['icolour']==1) $ob[]="<b class=\"al".$item['data']['status']."\">";
				
	if ($item['data']['alerts']==0) $out="No System Alerts";
	else $out="System Alerts (".$item['data']['alerts'].")";
	$ob[]=$out."</a>";
		
	if ($item['icolour']==1) $ob[]="</b>";
	
	$ob[]="</td><td align=right valign=center>";
	$ob[]=$is;
	$ob[]="</td></tr>";
	
	// detail like tests etc...
	if ($item['idetail']>0)
		{
		$a=0;
		if ($item['data']['alerts']==0)
			{
			$ob[]="<tr><td colspan=2 align=left>&nbsp;-&nbsp;<i>There are no alerts</i></td></tr>";
			}
		else
			{
			foreach($item['detail'] as $dline)
				{
				$a++;
				$ob[]="<tr><td colspan=2>&nbsp;-&nbsp;";
				$uri=$abs.$dline['link'];
				if ($view['vlinkv']!=0)
					{
					$uri=$abs."view.php?viewid=".$view['vlinkv'];
					}
				$link=$view['vclick'];
				if ($link=="disabled") $l="<a href=#>";
				else if ($link=="standard") $l="<a href=\"".$uri."\">";
				else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
				else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
				else $l="<a href=#>";
				$ob[]=$l;	

				if ($item['icolour']==1) $ob[]="<b class=\"al".$dline['status']."\">";
				$out=$dline['nodeid'];
				if ($item['itextstatus']==1) $out.=$colon." ".oText($dline['status']);
				$ob[]=$out."</a>";
				if ($item['icolour']==1) $ob[]="</b>";
				$ob[]="</td></tr>";
				}
			//if ($a>0) $ob[]="<br>";
			}
		}
	$ob[]="</table>";
	
	return $ob;



	}





if ($view['vcolumns']>1)
	{
	$usecols=true;
	$colcount=$view['vcolumns'];
	}
else $usecols=false;

foreach($items as $item)
	{
	switch ($item['itype'])
		{
		case "node":
			if ($item['isize']>0) $output=large_node($item);
			else $output=small_node($item);
			foreach($output as $line) $ob[]=$line;
			
			break;
			
		case "group":
			if ($item['isize']>0) $output=large_group($item);
			else $output=small_group($item);
			foreach($output as $line) $ob[]=$line;
			break;
			
		case "alerts":
			if ($item['isize']>0) $output=large_alerts($item);
			else $output=small_alerts($item);
			foreach($output as $line) $ob[]=$line;
			break;
			
		case "allnodes": case "alertnodes":
			$c=0;
			if ($usecols) $ob[]="<table border=0>";
			foreach($item['detail'] as $node)
				{
				if ($usecols)
					{
					if ($c==0) $ob[]="<tr>";
					$ob[]="<td align=left valign=top>";
					}
				if ($item['isize']==1) $output=large_node($node);
				else $output=small_node($node);
				foreach($output as $line) $ob[]=$line;
				
				if ($usecols)
					{
					$ob[]="</td>";
					$c++;
					if ($c>=$colcount)
						{
						$ob[]="</tr>";
						$ob[]="<tr><td colspan=".$colcount.">&nbsp;</td></tr>";
						$c=0;
						}
					}
				else $ob[]="<br>";
				}
			if (($usecols) && ($c<3)) $ob[]="</tr>";
			if ($usecols) $ob[]="</table>";
			break;
			
		case "allgroups": case "alertgroups":
			$c=0;
			if ($usecols) $ob[]="<table border=0>";
			foreach($item['detail'] as $group)
				{
				if ($usecols)
					{
					if ($c==0) $ob[]="<tr>";
					$ob[]="<td align=left valign=top>";
					}
				if ($item['isize']==1) $output=large_group($group);
				else $output=small_group($group);
				foreach($output as $line) $ob[]=$line;
				
				if ($usecols)
					{
					$ob[]="</td>";
					$c++;
					if ($c>=$colcount)
						{
						$ob[]="</tr>";
						$ob[]="<tr><td colspan=".$colcount.">&nbsp;</td></tr>";
						$c=0;
						}
					}
				else $ob[]="<br>";
				}
			if (($usecols) && ($c<3)) $ob[]="</tr>";
			if ($usecols) $ob[]="</table>";
			break;
			
		case "title":
			if ($item['isize']>0) $ob[]="<b style=\"font-size: 14pt;\">";
			else if ($item['icolour']==1) $ob[]="<b>";
			$ob[]=$item['ioption'];
			if ( ($item['isize']>0) || ($item['icolour']==1) ) $ob[]="</b>";
			break;
			
		case "testdetail":
		
			$is="";
			if ($item['igraphic']>0)
				{
				$is="<img src=\"".$abs."images/lights/a".$item['status'].".png\">&nbsp;";
				}
				
			if ($is!="") $ob[]=$is;
		
			$uri=$abs."node.php?nodeid=".$item['nodeid'];
			if ($view['vlinkv']!=0)
				{
				$uri=$abs."view.php?viewid=".$view['vlinkv'];
				}
			$link=$view['vclick'];
			if ($link=="disabled") $l="<a href=#>";
			else if ($link=="standard") $l="<a href=\"".$uri."\">";
			else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
			else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
			else $l="<a href=#>";
			$ob[]=$l;
			
			if ($item['icolour']==1) $ob[]="<b class=\"al".$item['status']."\">";
			else $ob[]="<b>";
			
			$s=$item['iname'];
			if ($item['itextstatus']==1) $s.=$colon." ".oText($item['status']);
			$s.="</a>";
			$ob[]=$s;
			if ($item['idetail']>0)
				{
				if ($item['isize']>0) $ob[]="<br>&nbsp;".$dash."&nbsp;tested ";
				else $ob[]=$dash." ";
				$ob[]=$item['vtime'];
				}
			$ob[]="</b>";
				
			break;
			
		case "testgraph":
			$p=strpos($item['ioption'],"/");
			if ($p===false)
				{
				$hrs=24;
				$tid=$item['ioption'];
				}
			else
				{
				$tid=substr($item['ioption'],0,$p);
				$hrs=substr($item['ioption'],$p+1,20);
				if ($hrs=="") $hrs=24;
				}
			if ($item['isize']>0)
				{
				$width=700;
				$height=150;
				}
			else
				{
				$width=350;
				$height=100;
				}
			// colours eventually...
			
			$uri=$abs."history.test.php?testid=".$tid;
			if ($view['vlinkv']!=0)
				{
				$uri=$abs."view.php?viewid=".$view['vlinkv'];
				}
			$link=$view['vclick'];
			if ($link=="disabled") $l="<a href=#>";
			else if ($link=="standard") $l="<a href=\"".$uri."\">";
			else if ($link=="frametop") $l="<a href=\"".$uri."\" target=_top>";
			else if ($link=="newwindow") $l="<a href=\"".$uri."\" target=top>";
			else $l="<a href=#>";
			
			
			$i=$abs."test.graph.php?testid=".$tid;
			$graphkey=$NATS->Cfg->Get("site.graph.key","");
			if ($graphkey!="") $i.="&graphkey=".$graphkey;
			$i.="&startx=";
			$now=time();
			$startx=$now-($hrs*60*60);
			$i.=$startx."&finishx=".$now."&width=".$width."&height=".$height;
			$ob[]=$l."<img src=\"".$i."\" border=0></a>";
			break;
			
		}
		$ob[]="<br>";
		
	}
	
// footer
if ($view['vstyle']=="mobile")
	{
	$ob[]="</html>";
	}
else if ($view['vstyle']=="plain")
	{
	$ob[]="</html>";
	}
else // standard and catch-all
	{
	$ob[]="<table class=\"nfooter\" width=100%>";
	$ob[]="<tr><td align=left>Powered by <a href=http://www.purplepixie.org/freenats/>FreeNATS</a>";
	$ob[]="</td><td align=right>";
	$ob[]="<a href=".$abs.">Login to System</a>";
	$ob[]="</td></tr>";
	$ob[]="</table>";
	}
	
	
	
// finally the output
switch ($mode)
	{
	case "debug":	
	echo "<pre>";
	var_dump($items);
	echo "<br><br>";
	foreach($ob as $l)
		echo htmlspecialchars($l)."\n";
	break;
	
	case "js":
	//echo "<script type=\"text/javascript\">\n";
	
	/* DO NOT USE THE DOM ELEMENT POINTER -
	Will not work with any CSS or other elements - needs reworked using proper DOM elements
	*/
	
	if (isset($_REQUEST['element'])&&($_REQUEST['element']!="")) // write into a DOM element rather than just to the screen
		{
		$first=true;
		foreach($ob as $l)
			{
			if ($first)
				{
				$first=false;
				echo "var fnwrite = document.getElementById('".$_REQUEST['element']."');\n";
				echo "fnwrite.innerHTML='';\n";
				}
			$line = addslashes(trim($l));
			//if (strlen($line)>0)&&($line[strlen($line)-1]=="\n")) $line=substr($line,0,strlen($line)-2);
			echo "fnwrite.innerHTML = fnwrite.innerHTML + \"".$line."\";\n";
			}
		}
	else
		{
		foreach($ob as $l)
			{
			//if ($l[strlen($l)-1]=='\n') $l=substr($l,0,strlen($l)-1);
			$l=trim($l);
			echo "document.write(\"".addslashes($l)."\");\n";
			}
		}
	//echo "</script>\n";
	echo "\n";
	break;
	
	default:
	foreach($ob as $l)
		{
		echo $l;
		echo "\n";
		}
	}

?>