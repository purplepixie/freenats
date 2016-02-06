#!/bin/bash

# FreeNATS shell-install.sh : Installer/Upgrader
# Version 0.00
# Copyright 2008 PurplePixie Systems, All Rights Reserved
# Part of the FreeNATS Package released under the GNU GPL v3
# See http://www.purplepixie.org/freenats for more information
#

if [ "$1" == "dummy" ]; then
 FN_DUMMY="1"
else
 FN_DUMMY="0"
fi

if [ "$FN_BASE" == "" ]; then
 FN_BASE="/opt/freenats/server/base/"
fi
if [ "$FN_BIN" == "" ]; then
 FN_BIN="/opt/freenats/server/bin/"
fi
if [ "$FN_WEB" == "" ]; then
 FN_WEB="/opt/freenats/server/web/"
fi

# FN_BASE="/tmp/freenats/server/base/"
# FN_BIN="/tmp/freenats/server/bin/"
# FN_WEB="/tmp/freenats/server/web/"

echo
echo FreeNATS Shell Install/Upgrade 0.00
echo http://www.purplepixie.org/freenats
echo
if [ "$FN_DUMMY" == "1" ]; then
 echo "Dummy Run"
 echo
fi

function domv
 {
 echo "/bin/mv $1 $2"
 if [ "$FN_DUMMY" != "1" ]; then
  /bin/mv $1 $2
 fi
 }

function docp
 {
 echo "/bin/cp -Rf -v $1 $2"
 if [ "$FN_DUMMY" != "1" ]; then
 /bin/cp -Rf -v $1 $2
 fi
 }

function docpa
 {
 echo "/bin/cp -Rf -v $1* $2"
 if [ "$FN_DUMMY" != "1" ]; then
 /bin/cp -Rf -v $1* $2
 fi
 }

function domkdir
 {
 echo "/bin/mkdir -p $1"
 if [ "$FN_DUMMY" != "1" ]; then
 /bin/mkdir -p $1
 fi
 }



if [ ! -f "server/base/nats.php" ]; then
 echo "Error: server/base/nats.php not found"
 echo
 echo "You must run this script from within the freenats directory i.e."
 echo "the working directory is /downloads/freenats-xxx/"
 echo
 exit 0
fi

echo -n "Is this an Upgrade or Install [U/i]: "
read itype

if [ "$itype" == "i" ]; then
 echo "Performing Fresh Installation"


else
 echo "Performing System Upgrade"

fi

echo
echo "Directory locations - INCLUDE TRAILING SLASH!"
echo -n "Base [$FN_BASE]: "
read ubase
echo -n "Bin [$FN_BIN]: "
read ubin
echo -n "Web [$FN_WEB]: "
read uweb

if [ "$ubase" != "" ]; then
 FN_BASE="$ubase"
fi
if [ "$ubin" != "" ]; then
 FN_BIN="$ubin"
fi
if [ "$uweb" != "" ]; then
 FN_WEB="$uweb"
fi

echo
echo "Using Installation Directories:"
echo "Base: $FN_BASE"
echo "Bin : $FN_BIN"
echo "Web : $FN_WEB"

if [ "$itype" != "i" ]; then
 if [ ! -d "$FN_BASE" ]; then
  echo "Upgrade Error: $FN_BASE does not exist"
  exit 0
 fi
 if [ ! -d "$FN_WEB" ]; then
  echo "Upgrade Error: $FN_WEB does not exist"
  exit 0
 fi
 if [ ! -d "$FN_BIN" ]; then
  echo "Upgrade Error: $FN_BIN does not exist"
  exit 0
 fi
else
 if [ -d "$FN_BASE" ]; then
  echo "Install Error: $FN_BASE exists"
  exit 0
 fi
 if [ -d "$FN_WEB" ]; then
  echo "Install Error: $FN_WEB exists"
  exit 0
 fi
 if [ -d "$FN_BIN" ]; then
  echo "Install Error: $FN_BIN exists"
  exit 0
 fi
fi

echo
echo "Installing/Upgrading ---"

function cleanup
{
echo "- Cleaning Up"
if [ -f "server/web/firstrun.php" ]; then
CMD="server/web/firstrun.php server/web/firstrun-.php"
domv $CMD
fi
if [ -f "server/web/include-.php" ]; then
CMD="server/web/include-.php server/web/include.php"
domv $CMD
fi
if [ -f "server/bin/include-.php" ]; then
CMD="server/bin/include-.php server/bin/include.php"
domv $CMD
fi
if [ -f "server/base/config-.inc.php" ]; then
CMD="server/base/config-.inc.php server/base/config.inc.php"
domv $CMD
fi
}

cleanup


if [ "$itype" == "i" ]; then
echo "- Fresh Install"
echo "- Creating Directories"

CMD="$FN_BASE"
domkdir $CMD
CMD="$FN_WEB"
domkdir $CMD
CMD="$FN_BIN"
domkdir $CMD

echo "- Enabling First Run Script"
CMD="server/web/firstrun-.php server/web/firstrun.php";
domv $CMD

else

echo "- Upgrade"
echo "- Protecting Existing Config"
CMD="server/web/include.php server/web/include-.php"
domv $CMD
CMD="server/bin/include.php server/bin/include-.php"
domv $CMD
CMD="server/base/config.inc.php server/base/config-.inc.php"
domv $CMD


fi

echo "- Copying Files"
CMD="server/base/ $FN_BASE"
docpa $CMD
CMD="server/web/ $FN_WEB"
docpa $CMD
CMD="server/bin/ $FN_BIN"
docpa $CMD


cleanup
echo "--- Complete"
echo

echo "**** THE NEXT STEP ****"
echo

if [ "$itype" == "i" ]; then
 echo "If you have moved the directories in relation to each other"
 echo "i.e. base DOESN\'T lie at ../base from bin and/or web you will"
 echo "need to edit the following files and change the BaseDir value:"
 echo -n "$FN_WEB"
 echo "include.php"
 echo -n "$FN_BIN"
 echo "include.php"
 echo
 echo "You will also need to edit the following file:"
 echo -n "$FN_BASE"
 echo "config.inc.php"
 echo "and put in your MySQL connection information"
 echo
 echo "Once that is done you should browse to http://WEBINSTALL/firstrun.php"
 echo "to complete database schema setup etc.."
 echo
 echo "You will also need to setup the CRON system processes to run the various"
 echo "scripts as described in the install.html document and online at"
 echo "http://www.purplepixie.org/freenats"
else
 echo "The files should now have been upgraded and your include"
 echo "and config.inc files not changed."
 echo
 echo "If you need to update the database schema (almost certainly) there are"
 echo "three ways of doing this - creating fresh (recommended), manually"
 echo "updating the or you can try the new (pre-pre-pre-alpha) update method."
 echo
 echo "To create fresh (reset the database to the current schema) you should"
 echo "rename the web/firstrun-.php file to web/firstrun.php and browse to"
 echo "it. Follow the instructions for a clean setup. (You can also manually"
 echo "import the files see install.html for details)."
 echo
 echo "To try the experimental upgrade process see the install.html file."
 echo
 echo "If you need to update the schema manually the current schema is in:"
 echo -n "$FN_BASE"
 echo "sql/schema.sql"
fi

echo

