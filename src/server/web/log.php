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
if ($NATS_Session->userlevel<9) UL_Error($NATS->Lang->Item("event.log"));


ob_end_flush();
Screen_Header($NATS->Lang->Item("event.log"),1);
?>

<br>
<b class="subtitle"><?php echo $NATS->Lang->Item("event.log"); ?></b><br><br>

<?php

if (isset($_REQUEST['f_mod'])) $f_mod=$_REQUEST['f_mod'];
else $f_mod="";
if (isset($_REQUEST['f_cat'])) $f_cat=$_REQUEST['f_cat'];
else $f_cat="";
if (isset($_REQUEST['f_lvl'])) $f_lvl=$_REQUEST['f_lvl'];
else $f_lvl="";
if (isset($_REQUEST['f_entry'])) $f_entry=$_REQUEST['f_entry'];
else $f_entry="";
if (isset($_REQUEST['d_show'])) $d_show=$_REQUEST['d_show'];
else $d_show="30";
if (isset($_REQUEST['d_from'])) $d_from=$_REQUEST['d_from'];
else $d_from="0";


$wc=array();
if ($f_mod!="") $wc[]="modid=\"".ss($f_mod)."\"";
if ($f_cat!="") $wc[]="catid=\"".ss($f_cat)."\"";
if ($f_lvl!="") $wc[]="loglevel<=".ss($f_lvl);
if ($f_entry!="") $wc[]="logevent LIKE \"%".ss($f_entry)."%\"";

if (count($wc)==0) $wc[]="1";

$q="SELECT * FROM fnlog WHERE";
$first=true;
foreach($wc as $c)
	{
	if ($first) $first=false;
	else $q.=" AND";
	$q.=" ".$c;
	}

$q.=" ORDER BY logid DESC";
$q.=" LIMIT ".ss($d_from).",".ss($d_show);

echo "<table class=\"nicetable\">";
echo "<form action=log.php method=post>";
// posted mod cat lvl entry
echo "<tr>";
echo "<td><b>".$NATS->Lang->Item("filter").":</b></td>";
echo "<td><input type=text value=\"".$f_mod."\" name=f_mod size=10></td>";
echo "<td><input type=text value=\"".$f_cat."\" name=f_cat size=10></td>";
echo "<td><input type=text value=\"".$f_lvl."\" name=f_lvl size=2></td>";
echo "<td><input type=text value=\"".$f_entry."\" name=f_entry size=40></td>";
echo "</tr>";

echo "<tr><td><b>".$NATS->Lang->Item("show").":</b></td>";
echo "<td colspan=3 align=left>";
echo "<input type=text value=\"".$d_show."\" size=4 name=d_show> entries from ";
echo "<input type=text value=\"".$d_from."\" size=6 name=d_from>";
echo "</td><td>";
echo "<input type=submit value=\"".$NATS->Lang->Item("filter.log")."\"> <a href=log.php>Reset</a>";
echo "</td></tr>";

echo "<tr><td colspan=3>";
$dto=$d_from-$d_show;
if ($dto<0) $dto=0;
echo "<a href=log.php?d_from=".$dto."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
echo "&lt;&lt; ".$NATS->Lang->Item("prev");
echo "</a></td>";
echo "<td colspan=2 align=right>";
$dto=$d_from+$d_show;
if ($dto<0) $dto=0;
echo "<a href=log.php?d_from=".$dto."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
echo $NATS->Lang->Item("next")." &gt;&gt;";
echo "</a></td></tr>";

//echo "<tr><td colspan=5>".$q."</td></tr>";
// <a href=log.php?d_from=".$d_from."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".$fentry.">
$r=$NATS->DB->Query($q);
while ($row=$NATS->DB->Fetch_Array($r))
	{
	echo "<tr><td>".nicedt($row['postedx'])."</td>";
	echo "<td>";
	echo "<a href=log.php?d_from=".$d_from."&d_show=".$d_show."&f_mod=".$row['modid']."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
	echo $row['modid']."</a></td>";
	echo "<td>";
	echo "<a href=log.php?d_from=".$d_from."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$row['catid']."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
	echo $row['catid']."</a></td>";
	echo "<td>";
	echo "<a href=log.php?d_from=".$d_from."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$row['loglevel']."&f_entry=".urlencode($f_entry).">";
	echo $row['loglevel']."</a></td>";
	echo "<td>".$row['logevent']."</td>";
	echo "</tr>";
	}
$NATS->DB->Free($r);

echo "<tr><td colspan=3>";
$dto=$d_from-$d_show;
if ($dto<0) $dto=0;
echo "<a href=log.php?d_from=".$dto."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
echo "&lt;&lt; ".$NATS->Lang->Item("prev");
echo "</a></td>";
echo "<td colspan=2 align=right>";
$dto=$d_from+$d_show;
if ($dto<0) $dto=0;
echo "<a href=log.php?d_from=".$dto."&d_show=".$d_show."&f_mod=".$f_mod."&f_cat=".$f_cat."&f_lvl=".$f_lvl."&f_entry=".urlencode($f_entry).">";
echo $NATS->Lang->Item("next")." &gt;&gt;";
echo "</a></td></tr>";


echo "</table>";

//echo $q;

?>


<?php
Screen_Footer();
?>
