<?php
// dbupg.sh - Copyright 2008-2016 PurplePixie Systems, all rights reserved.
// http://www.purplepixie.org
// v4 06/02/2016

// n.b. replaced with myrug tools -- NOT USED IN BUILD; kept for reference only

if (($argc<2)||($argc>3))
 {
 echo "Usage: dbupg.sh database [filter]\n";
 exit();
 }

if ($argc==3)
 $filter=" LIKE \"".mysql_escape_string($argv[2])."%\"";
else
 $filter="";

$sql=mysql_connect("localhost","root","marvin")
 or die("Couldn't connect to MySQL");
mysql_select_db($argv[1])
 or die("Couldn't select database");

function c($t="")
{
echo "-- ".$t."\n";
}

c("dbupg.sh -- PurplePixie Systems");
c();

$q="SHOW TABLES".$filter;
echo "-- ".$q."\n";
$r=mysql_query($q);
while ($row=mysql_fetch_array($r))
	{
	$table=$row[0];
	echo "-- Table: ".$table."\n";

	//echo "DROP INDEX FROM ".$table."\n";
	
	$tq="DESCRIBE ".$table;
	c($tq);
	$tr=mysql_query($tq);
	while ($trow=mysql_fetch_array($tr))
		{
		// Field Type Null Key Default Extra		

		$f="ALTER TABLE `".$table."` CHANGE `".$trow['Field']."` `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||($trow['Null']=="NO")) $f.=" NOT NULL";
		if ($trow['Extra']!="") $f.=" ".$trow['Extra'];
		if ($trow['Default']!="") $f.=" DEFAULT '".$trow['Default']."'";
		echo $f.";\n";

		$f="ALTER TABLE `".$table."` ADD `".$trow['Field']."` ".$trow['Type'];
		if (($trow['Null']=="")||($trow['Null']=="NO")) $f.=" NOT NULL";
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

		echo $f.";\n";

		if ($trow['Key']!="")
			{
			if ($trow['Key']=="PRI")
				echo "ALTER TABLE `".$table."` ADD PRIMARY KEY( `".$trow['Field']."` );\n";
			else if ($trow['Key']=="MUL")
				{
				// the one at a time way
				//echo "ALTER TABLE `".$table."` DROP INDEX `".$trow['Field']."` ;\n";
				//echo "ALTER TABLE `".$table."` ADD INDEX ( `".$trow['Field']."` );\n";
				echo "CREATE INDEX `".$trow['Field']."` ON `".$table."` ( `".$trow['Field']."` );\n";
				}
			else
				c("Unknown Key Type ".$trow['Key']);
			//else if ($trow['Key']=="MUL")
				// echo "ALTER TABLE `".$table."` ADD INDEX ( `".$trow['Field']."` );\n";
			}
		}
	mysql_free_result($tr);
	c();
	}
mysql_close($sql);
?>
