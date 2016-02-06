<?php // eval.inc.php -- evaluation system
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

function nats_eval($testid,$value)
{
global $NATS;
if (!is_numeric($value)) return 2; // fails if not numeric!
$lvl=0;

$q="SELECT * FROM fneval WHERE testid=\"".ss($testid)."\"";
$r=$NATS->DB->Query($q);
//echo $q;
while ($row=$NATS->DB->Fetch_Array($r))
	{
	//echo "\n".$row['eoperator']."\n";
	$nl=0;
	switch ($row['eoperator'])
		{
		case "ET":
		if ($row['evalue']==$value) $nl=$row['eoutcome'];
		break;
		case "GT":
		if ($row['evalue']<$value) $nl=$row['eoutcome'];
		break;
		case "LT":
		if ($row['evalue']>$value) $nl=$row['eoutcome'];
		break;
		}
	if ($nl>$lvl) $lvl=$nl;
	}
$NATS->DB->Free($r);
return $lvl;
}

function eval_operator_text($operator)
{
switch($operator)
	{
	case "ET": return "Equal To";
	case "GT": return "Greater Than";
	case "LT": return "Less Than";
	
	default: return "Unknown";
	}
}

?>