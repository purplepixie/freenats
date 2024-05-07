#!/bin/bash
if [ "$5" == "drop" ]; then
mysqldump -h $1 -u $3 -p$4 --add-drop-table --no-tablespaces -n -d $2
else
mysqldump -h $1 -u $3 -p$4 --skip-add-drop-table --no-tablespaces -n -d $2
fi
