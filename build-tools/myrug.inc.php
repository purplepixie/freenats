<?php
// myrug.inc.php - Copyright 2008 PurplePixie Systems, all rights reserved.
// http://www.purplepixie.org
// v4 07/05/2024
// MySQL Rough Upgrader
//
// Custom updated for FreeNATS 14/11/2018 to implement mysqli functionality
// Custom updated for FreeNATS 07/05/2024 to implement '' DEFAULT fields
function myrug($cfg)
{

$sql=mysqli_connect($cfg['host'],$cfg['username'],$cfg['password'],$cfg['database'])
 or die("MySQL Error: ".mysqli_error($sql)."\n");
/*
mysql_select_db($cfg['database'])
 or die("MySQL Error: ".mysql_error()."\n");
*/

if ($cfg['table']!="")
 $filter=" LIKE \"".mysqli_real_escape_string($sql,$cfg['table'])."%\"";
else
 $filter="";

function c($t="")
{
echo "-- ".$t."\n";
}

c("myrug -- PurplePixie Systems");
c("http://www.purplepixie.org/myrug");
c();

$q="SHOW TABLES".$filter;
echo "-- ".$q."\n";
$r=mysqli_query($sql,$q);
while ($row=mysqli_fetch_array($r))
	{
	$table=$row[0];
	echo "-- Table: ".$table."\n";

	$tq="DESCRIBE ".$table;
	c($tq);
	$tr=mysqli_query($sql,$tq);
	while ($trow=mysqli_fetch_array($tr))
		{
		// Field Type Null Key Default Extra

		$f="ALTER TABLE `".$table."` CHANGE `".$trow['Field']."` `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||(strtoupper($trow['Null'])=="NO")) $f.=" NOT NULL";
		if ($trow['Extra']!="") $f.=" ".$trow['Extra'];
		if ($trow['Default']!="NULL") $f.=" DEFAULT '".$trow['Default']."'";
		if ($cfg['alterfield']) echo $f.";\n";

		$f="ALTER TABLE `".$table."` ADD `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||(strtoupper($trow['Null'])=="NO")) $f.=" NOT NULL";
		if ($trow['Extra']!="") $f.=" ".$trow['Extra'];
		if ($trow['Default']!="NULL")
			{
			/*
			$typarr=explode("(",$trow['Type']);
			$type=$typarr[0];
			$quot=true;
			switch($type)
				{
				case "TINYINT": case "SMALLINT": case "MEDIUMINT": case "INT": case "INTEGER": case "BIGINT":
				case "FLOAT": case "DOUBLE":
				$quot=false;
				break;
				}
			*/
			$f.=" DEFAULT '".$trow['Default']."'";
			}

		if ($cfg['createfield']) echo $f.";\n";

		if ($trow['Key']!="")
			{
			if ($trow['Key']=="PRI")
				{
				if ($cfg['primarykey'])
					echo "ALTER TABLE `".$table."` ADD PRIMARY KEY( `".$trow['Field']."` );\n";
				}
			else if ($trow['Key']=="MUL")
				{
				// the one at a time way
				//echo "ALTER TABLE `".$table."` DROP INDEX `".$trow['Field']."` ;\n";
				//echo "ALTER TABLE `".$table."` ADD INDEX ( `".$trow['Field']."` );\n";
				if ($cfg['createindex'])
					echo "CREATE INDEX `".$trow['Field']."` ON `".$table."` ( `".$trow['Field']."` );\n";
				}
			else
				c("Unknown Key Type ".$trow['Key']);
			//else if ($trow['Key']=="MUL")
				// echo "ALTER TABLE `".$table."` ADD INDEX ( `".$trow['Field']."` );\n";
			}
		}
	mysqli_free_result($tr);
	if ($cfg['optimize']) echo "OPTIMIZE TABLE ".$table.";\n";
	c();
	}
mysqli_close($sql);
}
?>
