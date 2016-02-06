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
if ($NATS_Session->userlevel<1) UL_Error("View Report");

if (isset($_REQUEST['mode'])) $mode=$_REQUEST['mode'];
else $mode="";

if ($mode=="savereport")
	{
	if ($NATS_Session->userlevel<5) UL_Error("Save Report");
	$q="INSERT INTO fnreport(reportname,reporttests) VALUES(\"".ss($_REQUEST['reportname'])."\",\"".ss($_REQUEST['reporttests'])."\")";
	$NATS->DB->Query($q);
	header("Location: main.php?mode=views&message=Saved+Report");
	exit();
	}
	
if ($mode=="delete")
	{
	if ($NATS_Session->userlevel<5) UL_Error("Delete Report");
	if (!isset($_REQUEST['confirmed']))
		{
		$back="report.php?mode=delete&reportid=".$_REQUEST['reportid']."&confirmed=1";
		$back=urlencode($back);
		$msg=urlencode("Delete availability report");
		header("Location: confirm.php?action=".$msg."&back=".$back);
		exit();
		}
	$q="DELETE FROM fnreport WHERE reportid=".ss($_REQUEST['reportid']);
	$NATS->DB->Query($q);
	$message=urlencode("Report Deleted");
	header("Location: main.php?mode=views&message=".$message);
	exit();
	}
	
$testlist=array();
if (isset($_REQUEST['reportid']))
	{
	$q="SELECT * FROM fnreport WHERE reportid=".ss($_REQUEST['reportid'])." LIMIT 0,1";
	$r=$NATS->DB->Query($q);
	if ($rep=$NATS->DB->Fetch_Array($r))
		{
		$testlist=explode(":",$rep['reporttests']);
		}
	}
		
		
function slist($var,$min,$max,$val)
	{
	echo "\n<select name=\"".$var."\">\n";
	echo "<option value=\"".$val."\">".$val."</option>\n";
	for ($a=$min; $a<=$max; $a++)
		echo "<option value=\"".$a."\">".$a."</option>\n";
	echo "</select>\n";
	}
	
function cbd($var,$name)
	{
	if (isset($_REQUEST[$var])) $s=" checked";
	else $s="";
	echo "<input type=checkbox name=\"".$var."\" value=1".$s."> ".$name;
	}

function np($big,$part)
	{
	if ($big==0) return "n/a";
	if ($part==0) return "0%";
	
	$p=($part/$big)*100;
	$p=round($p,2);
	return $p."%";
	}

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


if ($mode=="")
	{
	Screen_Header("Service Availability Report",1);
	ob_end_flush();
	

	
	
	
	
	
	
	
	echo "<b class=\"subtitle\">Reporting Period</b><br><br>";
	echo "<br><table border=0>";
	echo "<form action=report.php method=get>";
	echo "<tr><td align=left valign=center>";
	echo "<input type=radio name=period value=custom checked > <b>Custom Period</b>";
	echo "</td><td align=left valign=top>";
	echo "<table class=\"nicetable\">";
	echo "<tr><td>&nbsp;</td><td>Hour</td><td>Min</td><td>Sec</td><td>Day</td><td>Mon</td><td>Year</td></tr>";
	
	

	
	
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
	
	echo "&nbsp;";
	
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
	
	echo "&nbsp;";
	
	echo "</td>";
	
	echo "</tr>";
	
	

	echo "</table>";
	
	
	echo "</td></tr><tr><td align=left valign=top>";
	echo "<b>Pre-defined </b>";
	echo "</td><td align=left valign=top>";
	$now=time();
	/*
	// td_day mon yr
	
	// mktime h mi s mo d y
	
	// last 30 days
	$fx=mktime(0,0,0,$td_mon,$td_day,$td_yr);
	$sx=$fx-(60*60*24*30);
	echo "<a href=report.php?startx=".$sx."&finishx=".$fx.">";
	echo "Last 30 Days";
	echo "</a>&nbsp;";
	
	// last 28 days
	$fx=mktime(0,0,0,$td_mon,$td_day,$td_yr);
	$sx=$fx-(60*60*24*28);
	echo "<a href=report.php?startx=".$sx."&finishx=".$fx.">";
	echo "Last 28 Days";
	echo "</a>&nbsp;";
	
	// last calendar month
	$prev_mon=$td_mon-1;
	$prev_yr=$td_yr;
	if ($prev_mon<1)
		{
		$prev_mon=12;
		$prev_yr--;
		}
	$fx=mktime(0,0,0,$td_mon,1,$td_yr);
	$sx=mktime(0,0,0,$prev_mon,1,$prev_yr);
	echo "<a href=report.php?startx=".$sx."&finishx=".$fx.">";
	echo "Previous Month";
	echo "</a>&nbsp;";
	
	
	// this month
	$sx=mktime(0,0,0,$td_mon,1,$td_yr);
	$sx=$now;
	echo "<a href=report.php?startx=".$sx."&finishx=".$fx.">";
	echo "This Month";
	echo "</a>&nbsp;";
	*/
	
	echo "<input type=radio name=period value=last30days> Last 30 Days<br>";
	echo "<input type=radio name=period value=last28days> Last 28 Days<br>";
	echo "<input type=radio name=period value=lastcalmonth> Last Calendar Month<br>";
	echo "<input type=radio name=period value=thiscalmonth> This Month<br>";
	
	echo "</td></tr></table>";	
	echo "<br><br>";

	echo "<b class=\"subtitle\">Reported Tests</b><br>";
	
	$q="SELECT localtestid,nodeid,testname,testtype,testparam,testrecord FROM fnlocaltest WHERE testrecord>0 OR testtype=\"ICMP\" ORDER BY nodeid";
	$r=$NATS->DB->Query($q);
	$lastnode="";
	while ($row=$NATS->DB->Fetch_Array($r))
		{
		if ($row['nodeid']!=$lastnode)
			{
			echo "<br><b>".$row['nodeid']."</b><br>";
			$lastnode=$row['nodeid'];
			
			// Bodge in node-side tests here
			$nq="SELECT nstestid,testname,testtype,testdesc FROM fnnstest WHERE testrecord>0 AND nodeid=\"".ss($row['nodeid'])."\"";
			$nr=$NATS->DB->Query($nq);
			while ($nrow=$NATS->DB->Fetch_Array($nr))
				{
				if (in_array("N".$nrow['nstestid'],$testlist)) $s=" checked";
				else $s="";
				echo "<input type=checkbox name=testlist[] value=N".$nrow['nstestid'].$s."> ";
				if ($nrow['testname']!="") echo $nrow['testname'];
				else if ($nrow['testdesc']!="") echo $nrow['testdesc'];
				else echo $nrow['testtype'];
				echo " on ".$row['nodeid']."<br>";
				}
			$NATS->DB->Free($nr);
			
			
			}
		if (in_array("L".$row['localtestid'],$testlist)) $s=" checked";
		else $s="";
		echo "<input type=checkbox name=testlist[] value=L".$row['localtestid'].$s."> ";
		if ($row['testname']!="") echo $row['testname'];
		else
			{
			echo lText($row['testtype']);
			if ($row['testparam']!="") echo " (".$row['testparam'].")";
			}
		echo " on ".$row['nodeid'];
		echo "<br>";
		}
	$NATS->DB->Free($r);
	
	echo "<br><br>";
	echo "<input type=hidden name=mode value=report>";
	echo "<input type=submit value=\"View Availability Report\"><br>";
	echo "<input type=checkbox name=showdetail value=1> Show breakdown details<br>";
	echo "</form>";
	Screen_Footer();
	exit();
		
		
	}

if ($mode!="report")
	{
	Screen_Header("Error");
	echo "Sorry - illegal mode specified<br><br>";
	Screen_Footer();
	exit();
	}





// the actual run

Screen_Header("Availability Report");
ob_end_flush();

if (isset($_REQUEST['period'])) $period=$_REQUEST['period'];
else $period="";
$now=time();
$nowx=$now;
switch ($period)
	{
	// td_day mon yr
	
	// mktime h mi s mo d y
	
	case "last30days":
	// last 30 days
	$finishx=mktime(0,0,0,$td_mon,$td_day,$td_yr);
	$startx=$finishx-(60*60*24*30);
	break;
	
	case "last28days":
	$fx=mktime(0,0,0,$td_mon,$td_day,$td_yr);
	$sx=$finishx-(60*60*24*28);
	break;
	
	case "lastcalmonth":
	// last calendar month
	$prev_mon=$td_mon-1;
	$prev_yr=$td_yr;
	if ($prev_mon<1)
		{
		$prev_mon=12;
		$prev_yr--;
		}
	$finishx=mktime(0,0,0,$td_mon,1,$td_yr);
	$startx=mktime(0,0,0,$prev_mon,1,$prev_yr);
	break;
	
	case "thiscalmonth": case "thismonth":
	// this month
	$startx=mktime(0,0,0,$td_mon,1,$td_yr);
	$finishx=$now;
	break;
	}
	
echo "<b class=\"subtitle\">Reporting from ".nicedt($startx)." to ".nicedt($finishx)."</b><br><br>";

foreach($_REQUEST['testlist'] as $testid)
{

$tmode="";
switch($testid[0])
	{
	case "L": $tmode="local";
	break;
	case "N": $tmode="nodeside";
	break;
	default: $tmode="unknown";
	}
$stid=substr($testid,1,128);

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
	
	
	
$tnode=$row['nodeid'];
$ttype=$row['testtype'];
if (isset($row['testparam'])) $tparam=$row['testparam'];
else $tparam="";
$tlastrunx=$row['lastrunx'];
$tunit="";

if ($tmode=="local")
	{
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
	if ($row['testname']!="") $tname=$row['testname'];
	else $tname=$row['testtype'];
	$subtname=$row['testdesc'];
	$usesubname=true;
	}
$NATS->DB->Free($r);




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



	
echo "<b>".$tname."</b>";
if ($usesubname) echo "<b> - ".$subtname."</b>";
echo "<b> on ".$tnode."</b>";
echo "<br>";

//echo "<i>node ";
//echo "<a href=node.php?nodeid=".$tnode.">".$tnode."</a>";
//echo " - ";
echo "<i>";
echo "Last Run : ";

if ($tlastrunx>0)
	{
	echo nicedt($tlastrunx)." - ";
	echo dtago($tlastrunx);
	}
else echo "Never";
echo "</i><br>";


//echo "<b>".$tname." from ".nicedt($startx)." to ".nicedt($finishx);
//if ($tunit!="") echo " (".$tunit.")";
// echo " (".nicediff($finishx-$startx).")";
//echo "</b><br><br>";



// table data


$q="SELECT alertlevel,testvalue,recordx FROM fnrecord WHERE ";
$q.="testid=\"".ss($testid)."\" AND recordx>=".ss($startx)." AND recordx<=".ss($finishx);
//if (!isset($_REQUEST['disp_pass'])) $q.=" AND alertlevel!=0";
$q.=" ORDER BY recordx ASC";

$firstx=0;
$lastx=0;
$records=0;

//echo $q;
$r=$NATS->DB->Query($q);

$testc=0;
$tested=0;
$untested=0;
$passc=0;
$warnc=0;
$failc=0;
$levelt=0;

while ($row=$NATS->DB->Fetch_Array($r))
	{
	$testc++;
	$records++;
	if ($firstx==0) $firstx=$row['recordx'];
	$lastx=$row['recordx'];
	switch ($row['alertlevel'])
		{
		case 0:
			$passc++;
			$tested++;
			$levelt+=$row['testvalue'];
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
	

echo "<table border=0>";

if (isset($_REQUEST['showdetail']))
	{
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
	
	$nowx=time();
	if ($finishx>$nowx) $fx=$nowx;
	else $fx=$finishx;
	$p=$fx-$startx;
	$five_min=60*5;
	$shouldhave=floor($p/$five_min);
	
	}

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
echo "<tr><td>First Test Record";
echo "</td><td>&nbsp;</td><td>";
echo nicedt($firstx);
echo "</td></tr>";

echo "<tr><td>Last Test Record";
echo "</td><td>&nbsp;</td><td>";
echo nicedt($lastx);
echo "</td></tr>";

echo "<tr><td colspan=3>&nbsp;<br></td></tr>";
echo "<tr><td><b>Service Availability</b>";
echo "</td><td>&nbsp;</td><td><b>";
// Service level is tested-failed
echo np($tested,$tested-$failc);
echo "</b></td></tr>";

echo "</table><br>";

/* - zoom period debugging
echo "Period: $p s (".($p/$five_min).")<br>";
echo "sx: ".$startx." fx: ".$finishx."<br>";
echo "diffx: ".($finishx-$startx)."<br>";
echo "fx2: ".$fx." p: ".$p."<br>";
*/


echo "<br><hr style=\"width: 400px; border: 0 solid #a0a0a0; height: 1px; background-color: #a0a0a0; align: left;\" align=\"left\"><br>";
}

$savestring="";
$first=true;
foreach($_REQUEST['testlist'] as $testid)
	{
	if ($first) $first=false;
	else $savestring.=":";
	$savestring.=$testid;
	}
echo "<form action=report.php method=post>";
echo "<input type=hidden name=reporttests value=\"".$savestring."\">";
echo "<input type=hidden name=mode value=savereport>";
echo "<b>Save Report As </b><input type=text size=30 name=reportname maxlength=128> <input type=submit value=Save>";
echo "</form><br><br>";

Screen_Footer();
?>
