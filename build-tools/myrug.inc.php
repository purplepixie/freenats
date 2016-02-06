<?php
// myrug.inc.php - Copyright 2008 PurplePixie Systems, all rights reserved.
// http://www.purplepixie.org
// v3 26/05/2009
// MySQL Rough Upgrader
function myrug($cfg)
{
if ($cfg['table']!="")
 $filter=" LIKE \"".mysql_escape_string($cfg['table'])."%\"";
else
 $filter="";


 
$sql=mysql_connect($cfg['host'],$cfg['username'],$cfg['password'])
 or die("MySQL Error: ".mysql_error()."\n");
mysql_select_db($cfg['database'])
 or die("MySQL Error: ".mysql_error()."\n");

function c($t="")
{
echo "-- ".$t."\n";
}

c("myrug -- PurplePixie Systems");
c("http://www.purplepixie.org/myrug");
c();

$q="SHOW TABLES".$filter;
echo "-- ".$q."\n";
$r=mysql_query($q);
while ($row=mysql_fetch_array($r))
	{
	$table=$row[0];
	echo "-- Table: ".$table."\n";
	
	$tq="DESCRIBE ".$table;
	c($tq);
	$tr=mysql_query($tq);
	while ($trow=mysql_fetch_array($tr))
		{
		// Field Type Null Key Default Extra		
		
		$f="ALTER TABLE `".$table."` CHANGE `".$trow['Field']."` `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||(strtoupper($trow['Null'])=="NO")) $f.=" NOT NULL";
		if ($trow['Extra']!="") $f.=" ".$trow['Extra'];
		if ($trow['Default']!="") $f.=" DEFAULT '".$trow['Default']."'";
		if ($cfg['alterfield']) echo $f.";\n";

		$f="ALTER TABLE `".$table."` ADD `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||(strtoupper($trow['Null'])=="NO")) $f.=" NOT NULL";
		if ($trow['Extra']!="") $f.=" ".$trow['Extra'];
		if ($trow['Default']!="") 
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
	mysql_free_result($tr);
	if ($cfg['optimize']) echo "OPTIMIZE TABLE ".$table.";\n";
	c();
	}
mysql_close($sql);
}
?>
