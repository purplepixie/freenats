<?php // nslast.inc.php -- Last nodeside data test
/* -------------------------------------------------------------
This file is part of FreeNATS

FreeNATS is (C) Copyright 2008-20117PurplePixie Systems

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

/* Get the last time nodeside data was received from the node,
 * alert if it's been so long we think the node isn't responding
*/

if (isset($NATS))
{
	class FreeNATS_NSLast_Test extends FreeNATS_Local_Test
	{
			
		function DoTest($testname,$param,$hostname,$timeout,$params)
		{ 
			global $NATS;
			
			$q="SELECT nsenabled,nslastx FROM fnnode WHERE nodeid=\"".ss($param)."\" LIMIT 0,1";
			$r=$NATS->DB->Query($q);

			if ($row=$NATS->DB->Fetch_Array($r))
			{
				if ($row['nsenabled'] != 1)
					return -2; // nodeside not enabled
				$last = $row['nslastx'];
				$elapsed = time() - $last;
				return $elapsed;
			}
			else
				return -1; // nodeid not found
		}
			
		function Evaluate($result) 
		{
			if ($result<0) return 2; // failure (test failed for reason)
			else if ($result>(60*60)) return 2; // an hour
			else if ($result>(20*60)) return 1; // over 20 mins warning
			else return 0; //passed
		}
		
		function DisplayForm(&$row)
		{
			echo "<table border=0>";
			echo "<tr><td align=left>";
			echo "Node ID :";
			echo "</td><td align=left>";
			echo "<input type=text name=testparam size=30 maxlength=128 value=\"".$row['nodeid']."\">";
			echo "</td></tr>";
			echo "<tr><td>&nbsp;</td><td align=\"left\">(will default to the nodeid of this node)</td></tr>";
		}
			
	}
		
	$params=array();
	$NATS->Tests->Register("nslast","FreeNATS_NSLast_Test",$params,"Nodelast Last Data",1,"FreeNATS Nodeside Data Tester");
	$NATS->Tests->SetUnits("nslast","Seconds","s");
}


?>