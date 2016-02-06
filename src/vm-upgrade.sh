#!/bin/bash
# FreeNATS vm-upgade.sh : VM Specific Upgrader
# Copyright 2008 PurplePixie Systems, All Rights Reserved
# Part of the FreeNATS Package released under the GNU GPL v3
# See http://www.purplepixie.org/freenats for more information
#

if [ "$1" == "dummy" ]; then
 VM_DUMMY="1"
else
 VM_DUMMY="0"
fi

echo "Upgrading Virtual Machine..."
if [ "$VM_DUMMY" == "1" ]; then
 echo "-- Dummy Run --"
fi

echo

if [ ! -f "shell-install.sh" ]; then
 echo "shell-install.sh not found - must be in the same directory"
 echo "and have the same working directory!"
 echo
 exit 0
fi

FN_WEB="/srv/www/html/"
export FN_WEB

echo
echo "**************** FreeNATS Virtual Appliance Upgrade ****************"
echo
echo "The upgrade process will now call the file upgrade script with values"
echo "pre-set for the FreeNATS rPath Virtual Appliance. You to just select"
echo "upgrade (press return) and accept the default directory locations"
echo "(by pressing return)."
echo 
echo "Once the file upgrade is complete you will be prompted to upgrade or"
echo "refresh the database schema and configuration."
echo
echo -n "Proceed with upgrade (y/N): "
read proccheck
if [ "$proccheck" != "y" ]; then
 echo
 echo "Upgrade Aborted at User Request"
 echo
 FN_WEB=""
 export FN_WEB
 exit 0
fi
echo
echo "**************** FreeNATS Virtual Appliance Upgrade ****************"
echo
echo "Proceeding with upgrade..."
echo
 
 
if [ "$VM_DUMMY" == "1" ]; then
 ./shell-install.sh dummy
else
 ./shell-install.sh
fi

FN_WEB=""
export FN_WEB

echo
echo "**************** FreeNATS Virtual Appliance Upgrade ****************"
echo
echo "File structure update completed."
echo

echo "You must now decide whether to update the database schema (recommended)"
echo "and how to do this."
echo
echo -n "Update database schema [Y/n]: "
read updatesch
if [ "$updatesch" == "n" ]; then
 echo "You have chosen not to update the schema. Please be aware this can"
 echo "cause adverse affects and failures."
 echo
 exit 0
fi
echo
echo "Please select the update method - fresh (recommended) will wipe all"
echo "existing data from the FreeNATS system. Alternatively you can try"
echo "the update method (experimental) to upgrade the schema with the data"
echo "and configuration kept intact."
echo

UDMETH=0
while [ "$UDMETH" == 0 ]; do
 echo -n "Update Method [Fresh/Upgrade]: "
 read udinp
 if [ "$udinp" == "f" ]; then
  UDMETH=1
 elif [ "$udinp" == "u" ]; then
  UDMETH=2
 else
  echo "Please enter f for fresh or u for upgrade (Control-C to abort)"
  echo
 fi
done

function mysql
 {
 if [ "$VM_DUMMY" == 1 ]; then
  echo "/usr/bin/mysql -u freenats -pfreenats -h localhost --force freenats < server/base/sql/$1"
 else
  /usr/bin/mysql -u freenats -pfreenats -h localhost --force freenats < server/base/sql/$1
 fi
 }

if [ "$UDMETH" == 1 ]; then
 echo "Importing Fresh Schema"
 mysql schema.drop.sql
 mysql default.sql
 mysql example.sql
else
 echo "Upgrading Database Schema - Please Ignore Errors"
 mysql schema.sql
 mysql schema.upgrade.sql
 mysql default.sql
fi

echo "Virtual Machine Configuration Complete"
echo
echo
echo "**************** FreeNATS Virtual Appliance Complete ****************"
echo
