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
if ($NATS_Session->userlevel<9) UL_Error("Admin SQL Interface");
if ($NATS->Cfg->Get("site.enable.adminsql",0)!=1)
	{
	header("Location: main.php?message=Admin+SQL+Console+Disabled");
	exit();
	}



ob_end_flush();
Screen_Header("Admin SQL Interface",1);
echo "<b>WARNING: This is advanced and unprotected functionality - proceed with caution!</b><br><br>";

if (isset($_REQUEST['query'])) $query=$NATS->StripGPC($_REQUEST['query']);
else $query="";

echo "<form action=admin.sql.php method=post>";
echo "<input type=hidden name=action value=sql>";
if ($query!="") $t=htmlspecialchars($query);
else $t="SELECT * FROM fnnode LIMIT 0,10";
echo "<textarea cols=70 rows=3 name=query>".$t."</textarea><br>";
echo "<input type=submit value=\"Execute Query\"> <input type=checkbox name=show_data value=1 checked> Show Data | <a href=admin.php>Abandon / Return to Admin Page</a>";
echo "</form><br>";

if ( (isset($_REQUEST['action'])) && ($_REQUEST['action']=="sql") )
	{
	$q=$query;
	$type=strtoupper(substr($q,0,strpos($q," ")));
	echo "<b>Query: </b>".$q."<br>";
	
	// sod the NATS-specific DB stuff here...
	echo "<b>Executing: </b>";
	$res=mysql_query($q);
	if (mysql_errno()==0)
		{
		echo "Success";
		$ok=true;
		}
	else
		{
		echo "Error: ".mysql_error()." (".mysql_errno().")";
		$ok=false;
		}
	echo "<br><br>";


	if ($ok)
		{
		if (($type=="SELECT")||($type=="SHOW")||($type=="DESCRIBE"))
			{
			echo "<b>Returned: </b>";
			echo mysql_num_rows($res);
			echo " Rows<br><br>";
			if (isset($_REQUEST['show_data']))
				{
				// show the data here
				echo "<table width=100% border=1>";
				$first=true;
				$keys=array();
				while ($row=mysql_fetch_array($res))
					{
					if ($first)
						{
						echo "<tr>";
						foreach($row as $key => $value)
							{
							if (!is_numeric($key))
								{
								echo "<td><b>".$key."</b></td>";
								$keys[]=$key;
								}
							}
						echo "</tr>";
						$first=false;
						}
					echo "<tr>";
					foreach($keys as $key)
						{
						echo "<td>".$row[$key]."</td>";
						}
					echo "</tr>";
					}
				echo "</table>";
					
				}
			}
		else
			{
			echo "<b>Affected: </b>";
			echo mysql_affected_rows();
			echo " Rows<br><br>";
			}
		}
	
	}
Screen_Footer();
?>
