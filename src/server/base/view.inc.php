<?php // view.inc.php -- evaluation system
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

function LocalTestDesc($ltid)
{
global $NATS;
$q="SELECT nodeid,testtype,testparam,testname FROM fnlocaltest WHERE localtestid=".ss($ltid);
$r=$NATS->DB->Query($q);
if ($row=$NATS->DB->Fetch_Array($r))
	{
	if ($row['testname']!="") $ret=$row['testname'];
	else
		{
		$ret=lText($row['testtype']);
		//if ($row['testparam']!="") $ret.=" (".substr($row['testparam'],0,20).")";
		if ($row['testparam']!="") $ret.=" (".$row['testparam'].")";
		}
	$ret.=" on ".$row['nodeid'];
	}
else $ret="Unknown2 on Unknown".$ltid;
return $ret;
}

function GetTestDesc($tid)
{
global $NATS;
$class=$tid[0];
if (is_numeric($class)) $class="L";
else $tid=substr($tid,1);

if ($class=="L") return LocalTestDesc($tid);
else if ($class=="N")
	{
	$q="SELECT nodeid,testtype,testname,testdesc FROM fnnstest WHERE nstestid=".ss($tid)." LIMIT 0,1";
	$r=$NATS->DB->Query($q);
	if (!$row=$NATS->DB->Fetch_Array($r)) return "Unknown on Unknown";
	$t="";
	if ($row['testname']!="") $t=$row['testname'];
	else if ($row['testdesc']!="") $t=$row['testdesc'];
	else $t=$row['testtype'];
	$t.=" on ".$row['nodeid'];
	return $t;
	}
	
return "Unknown on Unknown";
}

function ViewItemTxt($type,$option)
{
global $NATS; // must be set if you're using a view!
switch ($type)
	{
	case "node": return "Node - ".$option;
	case "allnodes": return "All Active Nodes";
	case "alertnodes": return "All Alerting Nodes";
	
	case "group":
		$q="SELECT groupname FROM fngroup WHERE groupid=".ss($option)." LIMIT 0,1";
		$r=$NATS->DB->Query($q);
		if ($row=$NATS->DB->Fetch_Array($r)) $ret="Group - ".$row['groupname'];
		else $ret="Group (".$option.")";
		$NATS->DB->Free($r);
		return $ret;
		
	case "allgroups": return "All Groups";
	case "alertgroups": return "All Alerting Groups";
	case "alerts": return "Current Alerts";
	case "title": return "Title (".$option.")";
	case "testdetail":
		//$tt=substr($option,0,1);
		//$tid=substr($option,1,128);
		return "Test Detail </b>for<b> ".GetTestDesc($option);
		//return "Detail for Test (".$option.")";
	case "testgraph": 
		
		$tid=$option;
		
		$sl=strpos($tid,"/");
		if ($sl===false) return "Test Graph </b>for<b> ".GetTestDesc($tid)." (default)"; // will break on non-local sets, refine logic here
		// otherwise as a return
		$hrs=substr($tid,$sl+1,32);
		$tid=substr($tid,0,$sl);
		if ($hrs=="") $hrs="default";
		return "Test Graph <b>for</b> ".GetTestDesc($tid)." (".$hrs." hours)";
		//return "History Graph for Test (".$option.")";
	default: return "Unknown Item Type (".$type."/".$option.")";
	}
}



?>