#!/bin/bash
if [ "$5" == "drop" ]; then
/usr/bin/mysqldump -h $1 -u $3 -p$4 --add-drop-table -n -d $2
else
/usr/bin/mysqldump -h $1 -u $3 -p$4 --skip-add-drop-table -n -d $2
fi
