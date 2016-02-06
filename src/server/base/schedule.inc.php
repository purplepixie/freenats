<?php // schedule.inc.php
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

// Determines Schedule Information for node

// Period Array Format:
// 'dayofweek' - Mon/Tue or BLANK
// 'dayofmonth' - 1-31 or 0
// 'monthofyear' - 1-12 or 0
// 'year' - XXXX or 0
// 'starthour' - 0 to 23
// 'finishhour' - 0 to 23
// 'startmin' - 0 to 59
// 'finishmin' - 0 to 59

function is_x_in_period($timex,$period)
{
$year=date("Y",$timex);
$month=date("m",$timex);
$day=date("d",$timex);
$hour=date("H",$timex);
$min=date("i",$timex);
$dayofweek=date("D",$timex);

// check each non-period piece of info
if ($period['dayofweek']!="")
 if ($period['dayofweek'] != $dayofweek) return false;

if ($period['monthofyear']>0)
 if ($period['monthofyear'] != $month) return false;
 
if ($period['year']>0)
 if ($period['year'] != $year) return false;
 
if ($period['dayofmonth']>0)
 if ($period['dayofmonth'] != $day) return false;

// check against start/finish times 

if ($period['starthour'] > $hour) return false; // not yet the hour
if ( ($period['starthour']==$hour)&&($period['startmin']>$min) ) return false; // hour but not yet the minute

if ($period['finishhour'] < $hour) return false; // past the finish hour
if ( ($period['finishhour']==$hour)&&($period['finishmin']<$min) ) return false; // finish hour equal but mins past

// all non-range either match or universal - after or equal to start and equal or before finish so...
return true;
}

function run_x_in_schedule($timex,$scheduleid) // note this is not is_x_in_schedule as we want to take into account default action!
{
global $NATS;
if ($scheduleid==0) return true; // always run schedule
if ($scheduleid<0) return false; // never run schedule
$q="SELECT defaultaction FROM fnschedule WHERE scheduleid=".ss($scheduleid)." LIMIT 0,1";
$r=$NATS->DB->Query($q);
if ($NATS->DB->Num_Rows($r)<=0) return true; // illegal schedule id so YES run
$srow=$NATS->DB->Fetch_Array($r);
if ($srow['defaultaction']==0) $default=false;
else $default=true;
$NATS->DB->Free($r);

$q="SELECT * FROM fnscheditem WHERE scheduleid=".ss($scheduleid);
$match=false;

$r=$NATS->DB->Query($q);
while ( ($row=$NATS->DB->Fetch_Array($r))&&(!$match) )
	{
	$match=is_x_in_period($timex,$row);
	}
$NATS->DB->Free($r);
	
if ($match) // found in the schedule list so do the reverse of default
	{
	if ($default) return false;
	else return true;
	}
else // not found in schedule
	return $default; // so use the default
}

?>