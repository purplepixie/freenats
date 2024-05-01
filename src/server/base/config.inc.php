<?php // config.inc.php - FreeNATS Non-DB Stored Configuration
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

$fnCfg=array();

$fnCfg['db.server']	=	"localhost";
$fnCfg['db.username']	=	"freenats";
$fnCfg['db.password']	=	"freenats";
$fnCfg['db.database']	=	"freenats";

/* -- ok from version 1.01.9 onwards you can just drop new icons into the server/web/icons directory and they will
      appear. This is left in for the DEFAULTS only - if you want to change them just change the FILE NAMES. Rather
      than making this soft configed it was done this way to avoid breaking existing installs.
*/
// Icons!
$fnIcons=array();
$fnIcons[0]="default_node.gif";
$fnIcons[1]="default_group.gif";

/* That's it - ignore this legacy stuff below

$fnIcons[2]="globe.gif";
$fnIcons[3]="monitor.gif";
$fnIcons[4]="personfile.gif";
$fnIcons[5]="special.gif";
$fnIcons[6]="msw.png";
$fnIcons[7]="workgroup.png";
$fnIcons[8]="atom.png";
$fnIcons[9]="globe2.png";
$fnIcons[10]="g_alarm.png";
$fnIcons[11]="g_pda.png";
$fnIcons[12]="g_bino.png";
$fnIcons[13]="g_cam.png";
*/

// These indexes are still required though
$fnIcon_DefNode=0;
$fnIcon_DefGroup=1;

// Safety Includes for site/ includes

// $fnSkipSiteIncludes=true;


?>
