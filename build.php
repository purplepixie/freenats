<?php
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-2016 PurplePixie Systems

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

/*

Build a Packaged Release

Standard for mac build is: php build.php -dc -u
build, dotclean and upload

*/
function UsageMessage()
{
echo "FreeNATS PHP Build Script

Usage: php build.php [options]

Options:
 Tag    --tag -t X
        Add the tag to the release e.g. freenats-X.YY.ZZ-tag.tar.gz
 Prefix --prefix -p X
        Prefix the release with X e.g. prefix-freenats-X.YY.ZZ.tar.gz
 Zip    --zip -z
        Use Zip rather than TAR+GZIP (if archiving)
 Dummy  --dummy -d
        Dummy - do not compress e.g. creates folder structure in release/ but does not compress
 Upload --upload -u
        Upload to release server (as specified in configuration)
 Dir    --dir
        Directory to put release into (defaults to ./release/)
 Yes    --yes -y
        Say yes to all prompts (dangerous!)
 Clean  --noclean -nc
        No cleaning (don't remove directory after compression)
 Dots   --dotclean -dc
        Dot clean (Mac OSX)
";
}


include_once("build-config.php");
date_default_timezone_set("Etc/GMT");

$Config_Tag = "";
$Config_Prefix = "";
$Config_Zip = false;
$Config_Dummy = false;
$Config_Upload = false;
$Config_Dir = "./release/";
$Config_Yes = false;
$Config_Exec = true;
$Config_Clean = true;
$Config_Dot = false;

$BaseDir = $_FREENATS_BASE;
//require_once($_FREENATS_NATS);
require_once($_FREENATS_BASE."nats.tests.inc.php");
require_once($_FREENATS_BASE."freenats.inc.php");
require_once($_FREENATS_BASE."config.inc.php");

if ($_FREENATS_DB_CONFIG === false)
{
	$_FREENATS_DB_SERVER = $fnCfg['db.server'];
	$_FREENATS_DB_DATABASE = $fnCfg['db.database'];
	$_FREENATS_DB_USERNAME = $fnCfg['db.username'];
	$_FREENATS_DB_PASSWORD = $fnCfg['db.password'];
}

$_FREENATS = new TFreeNATS();
$CompoundVersion = $_FREENATS->Version.$_FREENATS->Release;

for ($i=1; $i<count($_SERVER['argv']); ++$i)
{
	$arg=$_SERVER['argv'][$i];
	switch($arg)
	{
		case "--tag": case "-t":
			$Config_Tag = $_SERVER['argv'][++$i];
			break;
		case "--prefix": case "-p":
			$Config_Prefix = $_SERVER['argv'][++$i];
			break;
		case "--zip": case "-z":
			$Config_Zip = true;
			$Config_Upload = false; // no ZIP files for distribution upload
			echo "Zip files: upload disabled\n";
			break;
		case "--upload": case "-u":
			$Config_Upload = true;
			break;
		case "--dummy": case "-d":
			$Config_Dummy = true;
			break;
		case "--dir":
			$Config_Dir = $_SERVER['argv'][++$i];
			break;
		case "--yes": case "-y":
			$Config_Yes = true;
			break;
		case "--noexec": case "-ne":
			$Config_Exec=false;
			break;
		case "--noclean": case "-nc":
			$Config_Clean=false;
			break;
		case "--dotclean": case "-dc":
			$Config_Dot=true;
			break;
		default:
			UsageMessage();
			exit();
	}
}

if ($Config_Dummy === true)
{
	echo "Dummy Flag Set so disabling upload and setting nolog\n";
	$Config_Upload=false;
	$Config_LogRelease=false;
	$Config_Clean=false;
}

echo "Building FreeNATS Version: ".$CompoundVersion."\n";

$Build_ID = ($Config_Prefix == "" ? "" : $Config_Prefix."-")."freenats-".$CompoundVersion.($Config_Tag == "" ? "" : "-".$Config_Tag);

echo "FreeNATS Build ID        : ".$Build_ID."\n\n";

echo "Dummy Build              : ".($Config_Dummy === true ? "Yes" : "No")."\n";
echo "Compression Method       : ".($Config_Zip === true ? "Zip" : "Tar+Gzip")."\n";
echo "Upload                   : ".($Config_Upload === true ? "Yes" : "No")."\n";
echo "Release Directory        : ".$Config_Dir."\n";
echo "Database Connnection     : mysql://".$_FREENATS_DB_USERNAME."@".$_FREENATS_DB_SERVER."/".$_FREENATS_DB_DATABASE."\n";
echo "Actually Execute Commands: ".($Config_Exec === true ? "Yes" : "No")."\n";

if ($Config_Yes !== true)
{
	echo "\nProceed (y/N)? ";

	$in=trim(fgets(STDIN));
	if ($in!="y" && $in!="Y")
	{
		echo "User chosen not to proceed - aborting\n";
		exit();
	}
}

$BuildDir = $Config_Dir.$Build_ID;

if (!file_exists($Config_Dir))
{
	echo "Configured build target directory does not exist [".$Config_Dir."] - aborting\n";
	exit();
}

if (file_exists($BuildDir))
{
	echo "Build directory [".$BuildDir."] exists - aborting\n";
	exit();
}

if (file_exists($BuildDir.".tar.gz") || file_exists($BuildDir.".zip"))
{
	echo "Existing .zip or .tar.gz exists for build target [".$BuildDir."] - aborting\n";
	exit();
}

$cmd=array();

$cmd[]="mkdir ".$BuildDir;
$cmd[]="cp -Rf src/* ".$BuildDir."/";
$cmd[]="rm -Rf ".$BuildDir."/server/base/site/*";
$cmd[]="mkdir ".$BuildDir."/server/base/site";
$cmd[]="mkdir ".$BuildDir."/server/base/site/tests";
$cmd[]="mkdir ".$BuildDir."/server/base/site/events";

$cmd[]="php build-tools/pubdoc.php";
$cmd[]="cp -Rf doc/html/* ".$BuildDir."/";
$cmd[]="cp -Rf pub/* ".$BuildDir."/";

$cmd[]="echo \"-- FreeNATS ".$Build_ID." Schema\" > ".$BuildDir."/server/base/sql/schema.sql";
$cmd[]="echo \"-- No DROP TABLES - suitable for upgrade\" >> ".$BuildDir."/server/base/sql/schema.sql";
$cmd[]="./build-tools/dbdump.sh ".$_FREENATS_DB_SERVER." ".$_FREENATS_DB_DATABASE." ".$_FREENATS_DB_USERNAME." ".$_FREENATS_DB_PASSWORD." >> ".$BuildDir."/server/base/sql/schema.sql";

$cmd[]="echo \"-- FreeNATS ".$Build_ID." Schema\" > ".$BuildDir."/server/base/sql/schema.drop.sql";
$cmd[]="echo \"-- With DROP TABLES - will clean database\" > ".$BuildDir."/server/base/sql/schema.drop.sql";
$cmd[]="./build-tools/dbdump.sh ".$_FREENATS_DB_SERVER." ".$_FREENATS_DB_DATABASE." ".$_FREENATS_DB_USERNAME." ".$_FREENATS_DB_PASSWORD." drop >> ".$BuildDir."/server/base/sql/schema.drop.sql";

$cmd[]="echo \"-- FreeNATS ".$Build_ID." Schema\" > ".$BuildDir."/server/base/sql/schema.upgrade.sql";
$cmd[]="echo \"-- Experimental Upgrade SQL - run after schema.sql (not drop!)\" >> ".$BuildDir."/server/base/sql/schema.upgrade.sql";
$cmd[]="echo \"-- Both will generate many many errors - run with --force, ignore errors\" >> ".$BuildDir."/server/base/sql/schema.upgrade.sql";
//$cmd[]="./dbupg.sh freenats fn >> ".$BuildDir."/server/base/sql/schema.upgrade.sql";
$cmd[]="php build-tools/myrug.cli.php -h=".$_FREENATS_DB_SERVER." -u=".$_FREENATS_DB_USERNAME." -p=".$_FREENATS_DB_PASSWORD." ".$_FREENATS_DB_DATABASE." >> ".$BuildDir."/server/base/sql/schema.upgrade.sql";

$cmd[]="cp -Rf ".$BuildDir."/server/base/sql/* ./sql/latest/";

if ($Config_Dot)
{
	$cmd[]="dot_clean -m -v ".$BuildDir;
}

$Build_File = "";
if (!$Config_Dummy) // Compress File
{
	if ($Config_Zip)
	{
		$Build_File = $BuildDir.".zip";
		$cmd[]="cd ".$Config_Dir." && zip -r ".$Build_ID.".zip ".$Build_ID;
	}
	else
	{
		$Build_File = $BuildDir.".tar";
		$cmd[]="COPYFILE_DISABLE=1 tar --no-xattrs -c -C ".$Config_Dir." ".$Build_ID." > ".$Build_File;
		$cmd[]="gzip ".$Build_File;
		$Build_File.=".gz";
	}
}

if ($Config_Clean)
{
	$cmd[]="rm -Rf ".$BuildDir;
}

if ($Config_Upload)
{
	$relscript="php build-tools/release.php ".$CompoundVersion." ".$Build_File;
	if ($_FREENATS_UPLOAD_URL != "")
		$relscript.=" ".$_FREENATS_UPLOAD_URL;
	$cmd[]=$relscript;
}

foreach($cmd as $c)
{
	echo $c."\n";
	if ($Config_Exec === true) passthru($c);
}
?>