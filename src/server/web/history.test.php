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
if ($NATS_Session->userlevel<1) UL_Error("View Test History");

$tmode="";
switch($_REQUEST['testid'][0])
	{
	case "L": $tmode="local";
	break;
	case "N": $tmode="nodeside";
	break;
	default: $tmode="unknown";
	}
$stid=substr($_REQUEST['testid'],1,128);

// get test info
$tnode="";
$tname="";
$ttype="";
$tparam="";
$tlastrunx="";
$trecord=1;

$q="";
if ($tmode=="local") $q="SELECT * FROM fnlocaltest WHERE localtestid=".ss($stid);
else if ($tmode=="nodeside") $q="SELECT * FROM fnnstest WHERE nstestid=".ss($stid);
	
$r=$NATS->DB->Query($q);

if (!$row=$NATS->DB->Fetch_Array($r))
	{
	header("main.php?message=Error+opening+test+history");
	exit();
	}
	
	
// This bit handles up-front the test interval stuff i.e. which is the interval that
// the test "should run" at

$nodedata=$NATS->GetNode($row['nodeid']); // first load the data for the node
$show_expected_number=false; // show the expected number or not
$tinterval=0; // fallback zero
if ($tmode=="local")
	{
	$tinterval=$row['testinterval'];
	if ($nodedata['testinterval']>$tinterval) $tinvteral=$ndoedata['testinterval'];
	// for a local test use the test's interval or the node's, whichever is higher
	$show_expected_number=true;
	}
else if ($tmode=="nodeside")
	{
	if ( ($nodedata['nsenabled']==1) && ($nodedata['nspullenabled']==1) )
		{ // is nodedside and test is PULLED
		$tinterval=$nodedata['nsinterval'];
		$show_expected_number=true;
		}
	}
if ($tinterval<1) $tinterval=1; // default one minute assumption

// End of test interval bits

$tnode=$row['nodeid'];
$ttype=$row['testtype'];
if ($tmode=="local")
	{
	$tparam=$row['testparam'];
	$tlastrunx=$row['lastrunx'];
	$tname=lText($ttype);
	$tunit=lUnit($ttype);
	if ($tparam!="") $tname.=" (".$tparam.")";
	if ($row['testname']!="")
		{
		$subtname=$tname;
		$tname=$row['testname'];
		$usesubname=true;
		}
	else $usesubname=false;
	}
else if ($tmode=="nodeside")
	{
	$tlastrunx=$row['lastrunx'];
	if ($row['testname']!="") $tname=$row['testname'];
	else $tname=$row['testtype'];
	$subtname=$row['testdesc'];
	$usesubname=true;
	$tunit="";
	}
$NATS->DB->Free($r);
Screen_Header("History for ".$tname." on ".$tnode,1);
ob_end_flush();

$td_day=date("d");
$td_mon=date("m");
$td_yr=date("Y");

$start=array();
$finish=array();
if (isset($_REQUEST['st_day'])) $start['day']=$_REQUEST['st_day'];
else $start['day']=$td_day;
if (isset($_REQUEST['st_mon'])) $start['mon']=$_REQUEST['st_mon'];
else $start['mon']=$td_mon;
if (isset($_REQUEST['st_yr'])) $start['yr']=$_REQUEST['st_yr'];
else $start['yr']=$td_yr;
if (isset($_REQUEST['st_hour'])) $start['hour']=$_REQUEST['st_hour'];
else $start['hour']=0;
if (isset($_REQUEST['st_min'])) $start['min']=$_REQUEST['st_min'];
else $start['min']=0;
if (isset($_REQUEST['st_sec'])) $start['sec']=$_REQUEST['st_sec'];
else $start['sec']=0;

if (isset($_REQUEST['fi_day'])) $finish['day']=$_REQUEST['fi_day'];
else $finish['day']=$td_day;
if (isset($_REQUEST['fi_mon'])) $finish['mon']=$_REQUEST['fi_mon'];
else $finish['mon']=$td_mon;
if (isset($_REQUEST['fi_yr'])) $finish['yr']=$_REQUEST['fi_yr'];
else $finish['yr']=$td_yr;
if (isset($_REQUEST['fi_hour'])) $finish['hour']=$_REQUEST['fi_hour'];
else $finish['hour']=23;
if (isset($_REQUEST['fi_min'])) $finish['min']=$_REQUEST['fi_min'];
else $finish['min']=59;
if (isset($_REQUEST['fi_sec'])) $finish['sec']=$_REQUEST['fi_sec'];
else $finish['sec']=59;

if (isset($_REQUEST['startx']))
	{
	$start['day']=date("d",$_REQUEST['startx']);
	$start['mon']=date("m",$_REQUEST['startx']);
	$start['yr']=date("Y",$_REQUEST['startx']);
	$start['hour']=date("H",$_REQUEST['startx']);
	$start['min']=date("i",$_REQUEST['startx']);
	$start['sec']=date("s",$_REQUEST['startx']);
	$startx=$_REQUEST['startx'];
	}
else
	$startx=mktime($start['hour'],$start['min'],$start['sec'],$start['mon'],$start['day'],$start['yr']);

if (isset($_REQUEST['finishx']))
	{
	$finish['day']=date("d",$_REQUEST['finishx']);
	$finish['mon']=date("m",$_REQUEST['finishx']);
	$finish['yr']=date("Y",$_REQUEST['finishx']);
	$finish['hour']=date("H",$_REQUEST['finishx']);
	$finish['min']=date("i",$_REQUEST['finishx']);
	$finish['sec']=date("s",$_REQUEST['finishx']);
	$finishx=$_REQUEST['finishx'];
	}
else
	$finishx=mktime($finish['hour'],$finish['min'],$finish['sec'],$finish['mon'],$finish['day'],$finish['yr']);
//echo $startx." ".nicedt($startx)."<br>";
//echo $finishx." ".nicedt($finishx)."<br>";

$diffx=$finishx-$startx;
$periods=8;
$periodx=$diffx/$periods;
$hperiodx=round($periodx/2,0);
$periodx=round($periodx,0);
$iwid=700;
$ihei=150;
$istart=50;
$iend=$iwid-5;
$idwid=$iend-$istart;
$iscale=$idwid/$periods;

$zoom=array();
for ($a=0; $a<$periods; $a++)
	{
	$x=($a*$periodx)+$hperiodx+$startx;
	$zoom[$a]['startx']=$x-$periodx;
	$zoom[$a]['finishx']=$x+$periodx;
	$zoom[$a]['istart']=round($istart+($a*$iscale),0);
	$zoom[$a]['ifinish']=round($istart+($a*$iscale)+$iscale,0);
	}

echo "<br><table border=0>";
echo "<tr><td align=left valign=top>";

echo "<map id=\"zoommap\" name=\"zoommap\">\n";
for ($a=0; $a<$periods; $a++)
	{
	//echo $a." s ".nicedt($zoom[$a]['startx'])." f ".nicedt($zoom[$a]['finishx'])." x ".$zoom[$a]['istart']." - ".$zoom[$a]['ifinish']."<br>";
	$s="history.test.php?nodeid=".$tnode."&testid=".$_REQUEST['testid']."&startx=".$zoom[$a]['startx']."&finishx=".$zoom[$a]['finishx'];
	
	echo "<area shape=\"rect\" coords=\"".$zoom[$a]['istart'].",0,".$zoom[$a]['ifinish'].",".$ihei."\" href=\"".$s."\">\n";
	
	
	}
echo "</map>\n\n";
	
echo "<b class=\"subtitle\">".$tname."</b><br>";
if ($usesubname) echo "<b>".$subtname."</b><br>";

echo "<table class=\"nicetable\">";
echo "<tr><td align=right>Node :</td>";
echo "<td align=left><a href=node.php?nodeid=".$tnode.">".$tnode."</a>";
echo "</tr></tr>";
echo "<tr><td align=right valign=top>Last Run :</td>";
echo "<td align=left align=top>";
if ($tlastrunx>0)
	{
	echo nicedt($tlastrunx)."<br>";
	echo dtago($tlastrunx);
	}
else echo "Never";
echo "</td>";
echo "</tr></tr>";
echo "</table>";

echo "</td><td width=50>&nbsp;</td><td align=left valign=top>";

echo "<b><u>Reporting Period</u></b><br>";

echo "<table class=\"nicetable\">";
echo "<tr><td>&nbsp;</td><td>Hour</td><td>Min</td><td>Sec</td><td>Day</td><td>Mon</td><td>Year</td></tr>";
echo "<form action=history.test.php method=post>";
echo "<input type=hidden name=testid value=\"".$_REQUEST['testid']."\">";

function slist($var,$min,$max,$val)
{
echo "\n<select name=\"".$var."\">\n";
echo "<option value=\"".$val."\">".$val."</option>\n";
for ($a=$min; $a<=$max; $a++)
	echo "<option value=\"".$a."\">".$a."</option>\n";
echo "</select>\n";
}


echo "<tr><td><b>Start:</b></td>";
echo "<td>";
slist("st_hour",0,23,$start['hour']);
echo ":";
echo "</td>";

echo "<td>";
slist("st_min",0,59,$start['min']);
echo ":";
echo "</td>";

echo "<td>";
slist("st_sec",0,59,$start['sec']);
echo "&nbsp;&nbsp;";
echo "</td>";

echo "<td>";
slist("st_day",1,31,$start['day']);
echo "/";
echo "</td>";

echo "<td>";
slist("st_mon",1,12,$start['mon']);
echo "/";
echo "</td>";

echo "<td>";
echo "<input type=text name=st_yr value=\"".$start['yr']."\" size=5 maxlength=4>";

echo "&nbsp;<a href=history.test.php?testid=".$_REQUEST['testid'].">Today</a>";

echo "</td>";

echo "</tr>";

// ----- finish

echo "<tr><td><b>Finish:</b></td>";
echo "<td>";
slist("fi_hour",0,23,$finish['hour']);
echo ":";
echo "</td>";

echo "<td>";
slist("fi_min",0,59,$finish['min']);
echo ":";
echo "</td>";

echo "<td>";
slist("fi_sec",0,59,$finish['sec']);
echo "&nbsp;&nbsp;";
echo "</td>";

echo "<td>";
slist("fi_day",1,31,$finish['day']);
echo "/";
echo "</td>";

echo "<td>";
slist("fi_mon",1,12,$finish['mon']);
echo "/";
echo "</td>";

echo "<td>";
echo "<input type=text name=fi_yr value=\"".$finish['yr']."\" size=5 maxlength=4>";

echo "&nbsp;<input type=submit value=Go>";

echo "</td>";

echo "</tr>";

// 7
echo "<tr><td><b>Opts:</b></td>";
echo "<td colspan=\"6\">";
function cbd($var,$name)
{
if (isset($_REQUEST[$var])) $s=" checked";
else $s="";
echo "<input type=checkbox name=\"".$var."\" value=1".$s."> ".$name;
}
cbd("disp_hdata","Hide Data");
echo "&nbsp;";
cbd("disp_pass","Show Passed");
echo "&nbsp;";
cbd("hide_graph","Hide Graph");
echo "</td></tr>";

echo "</form>";
echo "</table>";

echo "</td></tr></table>";

echo "<br><br>";

echo "<b>".$tname." from ".nicedt($startx)." to ".nicedt($finishx);
if ($tunit!="") echo " (".$tunit.")";
// echo " (".nicediff($finishx-$startx).")";
echo "</b><br><br>";

// graph data
if (!isset($_REQUEST['hide_graph']))
	{
	mt_srand(microtime()*1000000);
	$i=mt_rand(1000,1000000);
	echo "<img src=\"test.graph.php?testid=".$_REQUEST['testid']."&startx=".$startx."&finishx=".$finishx."&nodeid=".$tnode."&i=".$i."\" border=0 usemap=\"#zoommap\"><br><br>";
	}

// table data


$q="SELECT alertlevel,testvalue,recordx FROM fnrecord WHERE ";
$q.="testid=\"".ss($_REQUEST['testid'])."\" AND recordx>=".ss($startx)." AND recordx<=".ss($finishx);
//if (!isset($_REQUEST['disp_pass'])) $q.=" AND alertlevel!=0";
$q.=" ORDER BY recordx DESC";
//echo $q;
$r=$NATS->DB->Query($q);

$testc=0;
$tested=0;
$untested=0;
$passc=0;
$warnc=0;
$failc=0;
$levelt=0;

echo "<table class=\"nicetable\">";
while ($row=$NATS->DB->Fetch_Array($r))
	{
	if ( (isset($_REQUEST['disp_pass']) || ($row['alertlevel']!=0) ) && (!isset($_REQUEST['disp_hdata'])) )
		{
		echo "<tr><td>".nicedt($row['recordx'])."</td>";
		echo "<td><b class=\"al".$row['alertlevel']."\">".oText($row['alertlevel'])."</b></td>";
		echo "<td>".$row['testvalue']."</td>";
		echo "</tr>";
		}
	$testc++;
	switch ($row['alertlevel'])
		{
		case 0:
			$passc++;
			$tested++;
			$levelt+=$row['testvalue'];  // for passed only
			break;
		case 1:
			$warnc++;
			$tested++;
			//$levelt+=$row['testvalue'];
			break;
		case 2:
			$failc++;
			$tested++;
			//$levelt+=$row['testvalue'];
			break;
		case -1:
			$untested++;
			break;
		}
	}
	
echo "</tr>";
echo "</table>";

echo "<br><br>";
echo "<table border=0>";

function np($big,$part)
{
if ($big==0) return "n/a";
if ($part==0) return "0%";

$p=($part/$big)*100;
$p=round($p,2);
return $p."%";
}


echo "<tr><td>".$passc." passed out of ".$tested." valid tests";
echo "</td><td>&nbsp;</td><td>";
echo np($tested,$passc);
echo "</td></tr>";

$notpass=$tested-$passc;

echo "<tr><td>".$notpass." did not pass out of ".$tested." valid tests";
echo "</td><td>&nbsp;</td><td>";
echo np($tested,$notpass);
echo "</td></tr>";

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";

echo "<tr><td>".$warnc." generated warnings out of ".$tested." valid tests";
echo "</td><td>&nbsp;</td><td>";
echo np($tested,$warnc);
echo "</td></tr>";

echo "<tr><td>".$failc." generated failures out of ".$tested." valid tests";
echo "</td><td>&nbsp;</td><td>";
echo np($tested,$failc);
echo "</td></tr>";

echo "<tr><td>".$warnc." generated warnings out of ".$notpass." unpassed tests";
echo "</td><td>&nbsp;</td><td>";
echo np($notpass,$warnc);
echo "</td></tr>";

echo "<tr><td>".$failc." generated failures out of ".$notpass." unpassed tests";
echo "</td><td>&nbsp;</td><td>";
echo np($notpass,$failc);
echo "</td></tr>";

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";

echo "<tr><td>the average test value returned by passed tests was</td>";
echo "<td>&nbsp;</td><td>";
if ($passc<=0) echo "n/a";
else if ($levelt==0) echo "0";
else echo round($levelt/$passc,4);
if ($tunit!="") echo " ".$tunit;
echo "</td></tr>";

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";

$utt=$untested+$tested;
echo "<tr><td>".$untested." tests were untested (of ".$utt.")</td>";
echo "<td>&nbsp;</td><td>";
echo np($utt,$untested);
echo "</td></tr>";

$npt=$untested+$notpass;
echo "<tr><td>of these (".$utt.") ".$npt." did not pass</td>";
echo "<td>&nbsp;</td><td>";
echo np($utt,$npt);
echo "</td></tr>";

echo "<tr><td>of these (".$utt.") ".$passc." did pass</td>";
echo "<td>&nbsp;</td><td>";
echo np($utt,$passc);
echo "</td></tr>";

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";

echo "<tr><td>".$testc." records of which ".$tested." returned a valid alert level";
echo "</td><td>&nbsp;</td><td>";
echo np($testc,$tested);
echo "</td></tr>";

if ($show_expected_number)
	{
	$nowx=time();
	if ($finishx>$nowx) $fx=$nowx;
	else $fx=$finishx;
	$p=$fx-$startx;
	$interval_min=60*$tinterval;
	$shouldhave=floor($p/$interval_min);


	echo "<tr><td>".$testc." records and you <i>should have</i> ".hlink("History:Should",12)." ".$shouldhave;
	echo "</td><td>&nbsp;</td><td>";
	echo np($shouldhave,$testc);
	echo "</td></tr>";
	}

echo "</table><br>";

/* - zoom period debugging
echo "Period: $p s (".($p/$five_min).")<br>";
echo "sx: ".$startx." fx: ".$finishx."<br>";
echo "diffx: ".($finishx-$startx)."<br>";
echo "fx2: ".$fx." p: ".$p."<br>";
*/
Screen_Footer();
?>
