<?php
// myrug.cli.php
// Copyright 2008 PurplePixie Systems, All Rights Reserved
// http://www.purplepixie.org/myrug

// CLI:
// myrug [options] database
// Options:
// -u= --user= --username=
// -p= --pass= --password=
// -t= --table=
// -h= --host=
// On/Offs:
// --optimize --nooptimize (off)
// --createtables --nocreatetables (off)
// --primarykey --noprimarykey (on)
// --createindex --nocreateindex (on)
// --createfield --nocreatefield (on)
// --alterfield --noalterfield (on)

require(realpath(dirname(__FILE__))."/myrug.inc.php");

$cfg=array(
	'username' => "root",
	'password' => "",
	'database' => "",
	'host' => "127.0.0.1",
	'table' => "",
	'optimize' => false,
	//'createtables' => false, // NYI
	'primarykey' => true,
	'createindex' => true,
	'createfield' => true,
	'alterfield' => true	);

function display_help()
{
echo "MySQL Rough Upgrader - outputs a SQL script which can be forced to\n";
echo "upgrade a schema (with many errors). For more information please see\n";
echo "http://www.purplepixie.org/myrug/\n\n";
echo "Usage: myrug [options] database\n\n";
echo "Where options are as follows:\n\n";
echo "--username=X | -u=X     Set username (default root)\n";
echo "--password=X | -p=X     Set password (default blank)\n";
echo "--host=X | -h=X         Connect to host (default 127.0.0.1)\n";
echo "--table=X | -t=X        Table name (can use % wildcard)\n";
echo "database                Database to connect to\n\n";
echo "--primarykey | --noprimarykey\n";
echo "  Turns on or off PRIMARY KEY queries (default on)\n\n";
echo "--createindex | --nocreateindex\n";
echo "  Turns on or off CREATE INDEX queries (default on)\n\n";
echo "--createfield | --createfield\n";
echo "  Turns on or off new field queries (default on)\n\n";
echo "--alterfield | --noalterfield\n";
echo "  Turns on or off update field queries (default on)\n\n";
echo "--optimize | --nooptimize\n";
echo "  Turns on or off OPTIMIZE TABLE queries (default off)\n\n";
exit();
}

if ($argc<2) display_help();
if ($argv[1]=="help") display_help();

$cfg['database']=$argv[$argc-1];

for ($i=1; $i<($argc-1); $i++)
	{
	$opt=$argv[$i];
	if (strpos($opt,"=")!=false)
		{
		$cmd=substr($opt,0,strpos($opt,"="));
		$val=substr($opt,strpos($opt,"=")+1,128);
		}
	else $cmd=$opt;

	switch ($cmd)
		{
		case "-u": case "--username": case "--user":
			$cfg['username']=$val;
			break;

		case "-p": case "--password": case "--pass":
			$cfg['password']=$val;
			break;

		case "-t": case "--table":
			$cfg['table']=$val;
			break;

		case "-h": case "--host":
			$cfg['host']=$val;
			break;

		case "--optimize": case "--createtables": case "--primarykey": case "--createindex":
		case "--createfield": case "--alterfield":
			$name=substr($cmd,2,128);
			$cfg[$name]=true;
			break;

		case "--nooptimize": case "--nocreatetables": case "--noprimarykey": case "--nocreateindex":
		case "--nocreatefield": case "--noalterfield":
			$name=substr($cmd,4,128);
			$cfg[$name]=false;
			break;


		default:
			echo "Error parsing: ".$opt."\n";
			display_help();
		}

	}

//print_r($cfg);

myrug($cfg);
//print_r($cfg);

?>
