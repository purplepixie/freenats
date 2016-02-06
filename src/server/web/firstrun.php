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

require("include.php");
$sn=$_SERVER['SCRIPT_NAME'];
$script="";
for ($a=strlen($sn)-1; $a>0; $a--)
	{
	$c=$sn[$a];
	if ($c=="/") $a=-1;
	else $script=$c.$script;
	}
if ($script!="firstrun.php")
	{
	echo "<b>Error:</b> This script is not correctly named. In order to run it please rename &quot;".$script."&quot; to &quot;firstrun.php&quot;.";
	echo "<br><br>";
	exit();
	}
if (isset($_REQUEST['stage'])) $stage=$_REQUEST['stage'];
else $stage=0;

function sqlfile($file)
{
global $BaseDir;
$fn=$BaseDir."sql/".$file.".sql";
echo "Processing ".$fn."<br><br>";
$fp=fopen($fn,"r");
if ($fp<=0)
	{
	echo "<b>ERROR: Cannot Open File!</b><br><br>";
	return false;
	}
$q="";
$qc=0;
$qok=0;
$qerr=0;
while ($s=fgets($fp,1024))
	{
	if ($s[0]!="-")
		{
		for ($a=0; $a<strlen($s); $a++)
			{
			$c=$s[$a];
			if ($c==";")
				{
				mysql_query($q);
				$qc++;
				if (mysql_errno()!=0)
					{
					echo "<b>Warning/Error</b><br>";
					echo "<b>SQL:</b> ".$q."<br>";
					echo "<b>Error:</b> ".mysql_error()." (Code ".mysql_errno().")<br><br>";
					$qerr++;
					}
				else $qok++;
				$q="";
				}
			else
				$q.=$c;
			}
		}
	}
echo "Finished Processing: ".$qc." queries (".$qok." ok, ".$qerr." warnings/errors)<br><br>";
fclose($fp);
}

echo "<html><head><title>FreeNATS Setup</title></head><body>\n";
echo "<h1>First Run Setup: Stage ".$stage."</h1>";

echo "Testing database connectivity...<br>";
		$sql=mysql_connect($fnCfg['db.server'],$fnCfg['db.username'],$fnCfg['db.password'])
			or die("Failed to connect to database server ".$fnCfg['db.server']." with username ".$fnCfg['db.username']."<br>Details: ".mysql_error());
		mysql_select_db($fnCfg['db.database'])
			or die("Connected ok but failed to select database ".$fnCfg['db.database']."<br>Details: ".mysql_error());
		echo "Database connection succeeded!<br><br>";

switch($stage)
	{
	case 0:
		echo "<b style=\"color: red;\">Users Performing an Upgrade Please Note:</b><br>this will only update the database and not ";
		echo "the files which you must do manually or with the shell-install/vm-upgrade scripts.</b><br><br>";
		echo "<form action=firstrun.php method=post>";
		echo "<input type=hidden name=stage value=1>";
		echo "<b>Setup Database Schema and Defaults</b><br><br>";
		echo "Select installation/update type...<br><br>";
		echo "<table border=0><tr>";
		echo "<td align=left valign=center>";
		echo "<input type=radio name=insttype value=fresh checked>";
		echo "</td><td align=left valign=top>";
		echo "<b>Fresh Install/Upgrade</b><br>";
		echo "First time users should select this. For upgrades this is<br>";
		echo "the recommended choice and should work but will loose your<br>";
		echo "data and configuration.<br><br>";
		echo "</td></tr><tr><td align=left valign=center>";
		echo "<input type=radio name=insttype value=upgrade>";
		echo "</td><td align=left valign=top>";
		echo "<b>Database Upgrade (highly experimental)</b><br>";
		echo "Select this to attempt to update your schema (add new tables<br>";
		echo "and fields etc keeping your data and config intact. You may<br>";
		echo "need to come back and do a fresh update or update the schema<br>";
		echo "<i>by hand</i> if it fails.";
		echo "</td></tr></table><br>";
		echo "<input type=checkbox name=tracker value=1 checked> Submit Anonymous Usage Data Automatically<br>";
		echo "<i>This will help us track use of the product and aid future development.</i><br><br>";
		echo "<input type=checkbox name=example value=1 checked> Include Example Setup (Recommended) (n/a if upgrade selected)<br><br>";
		echo "<input type=submit value=\"Proceed With Setup\">";
		echo "</form>";
		exit();
		break;
		
	case 1:
		if ($_REQUEST['insttype']=="fresh")
			{
			echo "<b>Fresh Install or Clean Update</b><br><br>";
			echo "<b>Setting Up Schema...</b><br><br>";
			sqlfile("schema.drop");
			echo "<b>Setting Up Defaults...</b><br><br>";
			sqlfile("default");
			if (isset($_REQUEST['example']))
				{
				echo "<b>Setting Up Examples...</b><br><br>";
				sqlfile("example");
				}
			}
		else if ($_REQUEST['insttype']=="upgrade")
			{
			echo "<b>Experimental Upgrade... Expect to See Errors</b><br>";
			echo "Doesn't mean it hasn't worked if you see already exists/duplicate errors<br><br>";
			echo "<b style=\"color: red;\">Basically ignore errors: 1050, 1060, 1068 and 1054</b><br><br>";
			echo "<b>Importing New Schema (sans drop tables)</b><br><br>";
			sqlfile("schema");
			echo "<br><br><b>Doing Schema Upgrade</b><br><br>";
			sqlfile("schema.upgrade");
			echo "<br><br>";
			}
		else echo "<b>Error: Incorrect or Unknown Installation Type!</b><br><br>";
		
		if (isset($_REQUEST['tracker']))
			{
			echo "<b>Enabling Usage Tracker</b><br><br>";
			$q="INSERT INTO fnconfig(fnc_var,fnc_val) VALUES(\"freenats.tracker\",\"1\")";
			mysql_query($q);
			}
		echo "<br><br>";
		echo "<b>CONGRATULATIONS!!</b><br><br>";
		echo "Setup should now be complete. If you saw errors etc above please see the <a href=http://www.purplepixie.org/freenats/>";
		echo "project homepage</a> for help.<br><br>";
		echo "<b>RENAME THIS FILE &quot;firstrun.php&quot; TO SOMETHING ELSE LIKE &quot;firstrun-.php&quot; TO STOP OTHERS RUNNING IT</b>";
		echo "<br><br>";
		echo "<a href=./>Click here to continue</a> - login admin password admin (click settings once logged in to change)<br><br>";
		
		
		exit();
		break;
		
	default:
		echo "Sorry - unknown step in setup process!<br><br>";
		exit();
	}

?>